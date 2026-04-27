<?php
/**
 * Public form and submission handling.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles shortcode rendering and lead submissions.
 */
class LLR_Public {
	/**
	 * Register public hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'handle_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'lead_router_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style( 'llr-public', LLR_URL . 'public/css/lead-router.css', array(), LLR_VERSION );
	}

	/**
	 * Render lead form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'title' => '',
			),
			$atts,
			'lead_router_form'
		);

		wp_enqueue_style( 'llr-public' );

		$settings = LLR_Plugin::settings();
		$routes = $settings['routes'];
		$title = '' !== $atts['title'] ? sanitize_text_field( $atts['title'] ) : $settings['form_title'];
		$status = isset( $_GET['llr_status'] ) ? sanitize_key( wp_unslash( $_GET['llr_status'] ) ) : '';
		$error = isset( $_GET['llr_error'] ) ? sanitize_key( wp_unslash( $_GET['llr_error'] ) ) : '';

		ob_start();
		include LLR_DIR . 'public/views/form.php';
		return ob_get_clean();
	}

	/**
	 * Process posted lead forms.
	 *
	 * @return void
	 */
	public function handle_submission() {
		if ( empty( $_POST['llr_action'] ) || 'submit_lead' !== sanitize_text_field( wp_unslash( $_POST['llr_action'] ) ) ) {
			return;
		}

		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		$redirect = remove_query_arg( array( 'llr_status', 'llr_error' ), $redirect );

		if ( empty( $_POST['llr_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['llr_nonce'] ) ), 'llr_submit_lead' ) ) {
			wp_safe_redirect( add_query_arg( 'llr_error', 'security', $redirect ) );
			exit;
		}

		if ( ! empty( $_POST['llr_company'] ) ) {
			wp_safe_redirect( add_query_arg( 'llr_status', 'success', $redirect ) );
			exit;
		}

		$settings = LLR_Plugin::settings();

		if ( ! empty( $settings['show_consent'] ) && empty( $_POST['llr_consent'] ) ) {
			wp_safe_redirect( add_query_arg( 'llr_error', 'consent', $redirect ) );
			exit;
		}

		$lead = $this->sanitize_submission();
		$errors = $this->validate_submission( $lead );

		if ( ! empty( $errors ) ) {
			wp_safe_redirect( add_query_arg( 'llr_error', reset( $errors ), $redirect ) );
			exit;
		}

		$lead['recipient_email'] = LLR_Router::recipient_for_service( $lead['service'] );
		$lead_id = LLR_DB::insert_lead( $lead );

		if ( false === $lead_id ) {
			wp_safe_redirect( add_query_arg( 'llr_error', 'storage', $redirect ) );
			exit;
		}

		LLR_Mailer::send_lead_notification( $lead );

		wp_safe_redirect( add_query_arg( 'llr_status', 'success', $redirect ) );
		exit;
	}

	/**
	 * Sanitise request fields.
	 *
	 * @return array
	 */
	private function sanitize_submission() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		return array(
			'name'         => isset( $_POST['llr_name'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_name'] ) ) : '',
			'email'        => isset( $_POST['llr_email'] ) ? sanitize_email( wp_unslash( $_POST['llr_email'] ) ) : '',
			'phone'        => isset( $_POST['llr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_phone'] ) ) : '',
			'service'      => isset( $_POST['llr_service'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_service'] ) ) : '',
			'message'      => isset( $_POST['llr_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['llr_message'] ) ) : '',
			'source_url'   => isset( $_POST['llr_source_url'] ) ? esc_url_raw( wp_unslash( $_POST['llr_source_url'] ) ) : '',
			'referrer'     => isset( $_POST['llr_referrer'] ) ? esc_url_raw( wp_unslash( $_POST['llr_referrer'] ) ) : '',
			'utm_source'   => isset( $_POST['llr_utm_source'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_source'] ) ) : '',
			'utm_medium'   => isset( $_POST['llr_utm_medium'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_medium'] ) ) : '',
			'utm_campaign' => isset( $_POST['llr_utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_campaign'] ) ) : '',
			'ip_hash'      => '' !== $ip ? hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ) : '',
			'user_agent'   => $user_agent,
			'meta'         => '',
		);
	}

	/**
	 * Validate required fields.
	 *
	 * @param array $lead Lead data.
	 * @return array
	 */
	private function validate_submission( $lead ) {
		$errors = array();

		if ( '' === $lead['name'] ) {
			$errors[] = 'name';
		}

		if ( ! is_email( $lead['email'] ) ) {
			$errors[] = 'email';
		}

		if ( '' === $lead['service'] ) {
			$errors[] = 'service';
		}

		if ( '' === $lead['message'] ) {
			$errors[] = 'message';
		}

		return $errors;
	}
}
