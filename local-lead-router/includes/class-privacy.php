<?php
/**
 * WordPress privacy tools integration.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers personal data exporters and erasers for stored leads.
 */
class LLR_Privacy {
	/**
	 * Number of leads processed per privacy request page.
	 *
	 * @var int
	 */
	private const PER_PAGE = 50;

	/**
	 * Register privacy hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Register the lead data exporter.
	 *
	 * @param array $exporters Registered exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['local-lead-router'] = array(
			'exporter_friendly_name' => __( 'Local Lead Router leads', 'local-lead-router' ),
			'callback'               => array( $this, 'export_personal_data' ),
		);

		return $exporters;
	}

	/**
	 * Register the lead data eraser.
	 *
	 * @param array $erasers Registered erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['local-lead-router'] = array(
			'eraser_friendly_name' => __( 'Local Lead Router leads', 'local-lead-router' ),
			'callback'             => array( $this, 'erase_personal_data' ),
		);

		return $erasers;
	}

	/**
	 * Export lead data for a requester email address.
	 *
	 * @param string $email_address Requester email.
	 * @param int    $page Page number.
	 * @return array
	 */
	public function export_personal_data( $email_address, $page = 1 ) {
		$email_address = sanitize_email( $email_address );

		if ( ! is_email( $email_address ) ) {
			return array(
				'data' => array(),
				'done' => true,
			);
		}

		$leads = LLR_DB::get_leads_by_email( $email_address, $page, self::PER_PAGE );
		$data = array();

		foreach ( $leads as $lead ) {
			$data[] = array(
				'group_id'    => 'local-lead-router-leads',
				'group_label' => __( 'Local Lead Router Leads', 'local-lead-router' ),
				'item_id'     => 'local-lead-router-lead-' . absint( $lead->id ),
				'data'        => $this->format_lead_export_item( $lead ),
			);
		}

		return array(
			'data' => $data,
			'done' => count( $leads ) < self::PER_PAGE,
		);
	}

	/**
	 * Erase leads for a requester email address.
	 *
	 * @param string $email_address Requester email.
	 * @param int    $page Page number.
	 * @return array
	 */
	public function erase_personal_data( $email_address, $page = 1 ) {
		unset( $page );

		$email_address = sanitize_email( $email_address );

		if ( ! is_email( $email_address ) ) {
			return array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);
		}

		$deleted_count = LLR_DB::delete_leads_by_email( $email_address, self::PER_PAGE );
		$remaining_count = LLR_DB::count_leads_by_email( $email_address );

		return array(
			'items_removed'  => $deleted_count > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => 0 === $remaining_count,
		);
	}

	/**
	 * Format a lead as WordPress personal data export fields.
	 *
	 * @param object $lead Lead row.
	 * @return array
	 */
	private function format_lead_export_item( $lead ) {
		$statuses = LLR_Plugin::statuses();
		$status = LLR_Plugin::normalize_status( $lead->status );

		return array(
			array(
				'name'  => __( 'Lead ID', 'local-lead-router' ),
				'value' => absint( $lead->id ),
			),
			array(
				'name'  => __( 'Submitted at', 'local-lead-router' ),
				'value' => $lead->created_at,
			),
			array(
				'name'  => __( 'Status', 'local-lead-router' ),
				'value' => isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status,
			),
			array(
				'name'  => __( 'Name', 'local-lead-router' ),
				'value' => $lead->name,
			),
			array(
				'name'  => __( 'Email', 'local-lead-router' ),
				'value' => $lead->email,
			),
			array(
				'name'  => __( 'Phone', 'local-lead-router' ),
				'value' => $lead->phone,
			),
			array(
				'name'  => __( 'Service', 'local-lead-router' ),
				'value' => $lead->service,
			),
			array(
				'name'  => __( 'Message', 'local-lead-router' ),
				'value' => $lead->message,
			),
			array(
				'name'  => __( 'Source URL', 'local-lead-router' ),
				'value' => $lead->source_url,
			),
			array(
				'name'  => __( 'Referrer', 'local-lead-router' ),
				'value' => $lead->referrer,
			),
			array(
				'name'  => __( 'UTM Source', 'local-lead-router' ),
				'value' => $lead->utm_source,
			),
			array(
				'name'  => __( 'UTM Medium', 'local-lead-router' ),
				'value' => $lead->utm_medium,
			),
			array(
				'name'  => __( 'UTM Campaign', 'local-lead-router' ),
				'value' => $lead->utm_campaign,
			),
		);
	}
}
