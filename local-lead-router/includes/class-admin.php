<?php
/**
 * WordPress admin screens.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin menus, settings, and lead management actions.
 */
class LLR_Admin {
	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_llr_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_llr_update_lead_status', array( $this, 'update_lead_status' ) );
		add_action( 'admin_post_llr_delete_lead', array( $this, 'delete_lead' ) );
		add_action( 'admin_post_llr_export_leads', array( $this, 'export_leads' ) );
	}

	/**
	 * Add plugin admin pages.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Local Lead Router', 'local-lead-router' ),
			__( 'Lead Router', 'local-lead-router' ),
			'manage_options',
			'llr-leads',
			array( $this, 'render_leads_page' ),
			'dashicons-randomize',
			58
		);

		add_submenu_page(
			'llr-leads',
			__( 'Lead Inbox', 'local-lead-router' ),
			__( 'Lead Inbox', 'local-lead-router' ),
			'manage_options',
			'llr-leads',
			array( $this, 'render_leads_page' )
		);

		add_submenu_page(
			'llr-leads',
			__( 'Settings', 'local-lead-router' ),
			__( 'Settings', 'local-lead-router' ),
			'manage_options',
			'llr-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'llr-leads',
			__( 'Diagnostics', 'local-lead-router' ),
			__( 'Diagnostics', 'local-lead-router' ),
			'manage_options',
			'llr-diagnostics',
			array( $this, 'render_diagnostics_page' )
		);
	}

	/**
	 * Load admin assets on plugin pages.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'llr-' ) ) {
			return;
		}

		wp_enqueue_style( 'llr-admin', LLR_URL . 'admin/css/lead-router-admin.css', array(), LLR_VERSION );
		wp_enqueue_script( 'llr-admin', LLR_URL . 'admin/js/lead-router-admin.js', array(), LLR_VERSION, true );
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = LLR_Plugin::settings();
		include LLR_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render diagnostics page.
	 *
	 * @return void
	 */
	public function render_diagnostics_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = LLR_Plugin::settings();
		$lead_count = LLR_DB::count_leads();
		$status_counts = LLR_DB::status_counts();
		$email_log_counts = LLR_DB::email_log_counts();
		$email_logs = LLR_DB::get_email_logs( 20 );
		$leads_table_exists = LLR_DB::table_exists( LLR_DB::table_name() );
		$email_logs_table_exists = LLR_DB::table_exists( LLR_DB::email_logs_table_name() );

		include LLR_DIR . 'admin/views/diagnostics-page.php';
	}

	/**
	 * Render leads page.
	 *
	 * @return void
	 */
	public function render_leads_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_status = isset( $_GET['status'] ) ? LLR_Plugin::normalize_status( wp_unslash( $_GET['status'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = 20;

		if ( isset( $_GET['status'] ) && 'all' === sanitize_key( wp_unslash( $_GET['status'] ) ) ) {
			$current_status = '';
		}

		$query_args = array(
			'page'     => $page,
			'per_page' => $per_page,
			'status'   => $current_status,
			'search'   => $search,
		);

		$leads = LLR_DB::get_leads( $query_args );
		$total = LLR_DB::count_leads(
			array(
				'status' => $current_status,
				'search' => $search,
			)
		);
		$total_pages = max( 1, (int) ceil( $total / $per_page ) );
		$status_counts = LLR_DB::status_counts();
		$statuses = LLR_Plugin::statuses();

		include LLR_DIR . 'admin/views/leads-page.php';
	}

	/**
	 * Save admin settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to edit these settings.', 'local-lead-router' ) );
		}

		check_admin_referer( 'llr_save_settings' );

		$settings = LLR_Plugin::sanitize_settings( $_POST );
		update_option( LLR_OPTION, $settings );

		wp_safe_redirect( add_query_arg( 'llr_notice', 'settings_saved', admin_url( 'admin.php?page=llr-settings' ) ) );
		exit;
	}

	/**
	 * Update a lead status from the inbox.
	 *
	 * @return void
	 */
	public function update_lead_status() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage leads.', 'local-lead-router' ) );
		}

		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;
		check_admin_referer( 'llr_update_lead_status_' . $lead_id );

		$status = isset( $_POST['lead_status'] ) ? LLR_Plugin::normalize_status( wp_unslash( $_POST['lead_status'] ) ) : 'new';

		if ( $lead_id > 0 ) {
			LLR_DB::update_status( $lead_id, $status );
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=llr-leads' ) );
		exit;
	}

	/**
	 * Delete a lead from the inbox.
	 *
	 * @return void
	 */
	public function delete_lead() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage leads.', 'local-lead-router' ) );
		}

		$lead_id = isset( $_GET['lead_id'] ) ? absint( $_GET['lead_id'] ) : 0;
		check_admin_referer( 'llr_delete_lead_' . $lead_id );

		if ( $lead_id > 0 ) {
			LLR_DB::delete_lead( $lead_id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=llr-leads&llr_notice=lead_deleted' ) );
		exit;
	}

	/**
	 * Export filtered leads as CSV.
	 *
	 * @return void
	 */
	public function export_leads() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export leads.', 'local-lead-router' ) );
		}

		check_admin_referer( 'llr_export_leads' );

		$status = isset( $_GET['status'] ) && 'all' !== sanitize_key( wp_unslash( $_GET['status'] ) ) ? LLR_Plugin::normalize_status( wp_unslash( $_GET['status'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$leads = LLR_DB::get_leads_for_export(
			array(
				'status' => $status,
				'search' => $search,
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=local-lead-router-' . gmdate( 'Y-m-d-His' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		fputcsv(
			$output,
			array(
				'id',
				'created_at',
				'status',
				'name',
				'email',
				'phone',
				'service',
				'message',
				'recipient_email',
				'source_url',
				'referrer',
				'utm_source',
				'utm_medium',
				'utm_campaign',
			)
		);

		foreach ( $leads as $lead ) {
			fputcsv(
				$output,
				array(
					$lead->id,
					$lead->created_at,
					$lead->status,
					$lead->name,
					$lead->email,
					$lead->phone,
					$lead->service,
					$lead->message,
					$lead->recipient_email,
					$lead->source_url,
					$lead->referrer,
					$lead->utm_source,
					$lead->utm_medium,
					$lead->utm_campaign,
				)
			);
		}

		fclose( $output );
		exit;
	}
}
