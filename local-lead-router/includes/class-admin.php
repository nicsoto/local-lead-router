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
		add_action( 'admin_post_llr_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_llr_update_lead_status', array( $this, 'update_lead_status' ) );
		add_action( 'admin_post_llr_delete_lead', array( $this, 'delete_lead' ) );
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
}
