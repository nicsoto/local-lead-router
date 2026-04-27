<?php
/**
 * Core plugin bootstrap and shared settings helpers.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates the public and admin plugin layers.
 */
class LLR_Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var LLR_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return LLR_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		$public = new LLR_Public();
		$public->register_hooks();

		if ( is_admin() ) {
			$admin = new LLR_Admin();
			$admin->register_hooks();
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'local-lead-router', false, dirname( plugin_basename( LLR_FILE ) ) . '/languages' );
	}

	/**
	 * Default plugin settings.
	 *
	 * @return array
	 */
	public static function default_settings() {
		$admin_email = get_option( 'admin_email' );

		return array(
			'form_title'        => __( 'Request a quote', 'local-lead-router' ),
			'default_recipient' => $admin_email,
			'success_message'   => __( 'Thanks. Your message was sent successfully.', 'local-lead-router' ),
			'email_subject'     => __( 'New lead: {service}', 'local-lead-router' ),
			'show_consent'      => 1,
			'consent_text'      => __( 'I agree to be contacted about this request.', 'local-lead-router' ),
			'routes'            => array(
				array(
					'label' => __( 'New project or quote', 'local-lead-router' ),
					'email' => $admin_email,
				),
				array(
					'label' => __( 'Emergency service', 'local-lead-router' ),
					'email' => $admin_email,
				),
				array(
					'label' => __( 'Maintenance or repair', 'local-lead-router' ),
					'email' => $admin_email,
				),
			),
		);
	}

	/**
	 * Read merged plugin settings.
	 *
	 * @return array
	 */
	public static function settings() {
		$saved = get_option( LLR_OPTION, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = wp_parse_args( $saved, self::default_settings() );

		if ( empty( $settings['routes'] ) || ! is_array( $settings['routes'] ) ) {
			$settings['routes'] = self::default_settings()['routes'];
		}

		return $settings;
	}

	/**
	 * Sanitise plugin settings from an admin request.
	 *
	 * @param array $raw Raw request data.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		$defaults = self::default_settings();
		$settings = array(
			'form_title'        => isset( $raw['form_title'] ) ? sanitize_text_field( wp_unslash( $raw['form_title'] ) ) : $defaults['form_title'],
			'default_recipient' => isset( $raw['default_recipient'] ) ? sanitize_email( wp_unslash( $raw['default_recipient'] ) ) : $defaults['default_recipient'],
			'success_message'   => isset( $raw['success_message'] ) ? sanitize_text_field( wp_unslash( $raw['success_message'] ) ) : $defaults['success_message'],
			'email_subject'     => isset( $raw['email_subject'] ) ? sanitize_text_field( wp_unslash( $raw['email_subject'] ) ) : $defaults['email_subject'],
			'show_consent'      => empty( $raw['show_consent'] ) ? 0 : 1,
			'consent_text'      => isset( $raw['consent_text'] ) ? sanitize_text_field( wp_unslash( $raw['consent_text'] ) ) : $defaults['consent_text'],
			'routes'            => array(),
		);

		if ( ! is_email( $settings['default_recipient'] ) ) {
			$settings['default_recipient'] = get_option( 'admin_email' );
		}

		$labels = isset( $raw['route_label'] ) && is_array( $raw['route_label'] ) ? $raw['route_label'] : array();
		$emails = isset( $raw['route_email'] ) && is_array( $raw['route_email'] ) ? $raw['route_email'] : array();

		foreach ( $labels as $index => $label ) {
			if ( count( $settings['routes'] ) >= 25 ) {
				break;
			}

			$label = sanitize_text_field( wp_unslash( $label ) );
			$email = isset( $emails[ $index ] ) ? sanitize_email( wp_unslash( $emails[ $index ] ) ) : '';

			if ( '' === $label ) {
				continue;
			}

			if ( ! is_email( $email ) ) {
				$email = $settings['default_recipient'];
			}

			$settings['routes'][] = array(
				'label' => $label,
				'email' => $email,
			);
		}

		if ( empty( $settings['routes'] ) ) {
			$settings['routes'] = array(
				array(
					'label' => __( 'New project or quote', 'local-lead-router' ),
					'email' => $settings['default_recipient'],
				),
			);
		}

		return $settings;
	}

	/**
	 * Allowed lead statuses.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'new'       => __( 'New', 'local-lead-router' ),
			'contacted' => __( 'Contacted', 'local-lead-router' ),
			'won'       => __( 'Won', 'local-lead-router' ),
			'lost'      => __( 'Lost', 'local-lead-router' ),
		);
	}

	/**
	 * Normalise a status string.
	 *
	 * @param string $status Raw status.
	 * @return string
	 */
	public static function normalize_status( $status ) {
		$status = sanitize_key( $status );
		$allowed = array_keys( self::statuses() );

		return in_array( $status, $allowed, true ) ? $status : 'new';
	}
}
