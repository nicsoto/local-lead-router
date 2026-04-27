<?php
/**
 * Database access layer.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and retrieves lead records.
 */
class LLR_DB {
	/**
	 * Get the custom leads table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'llr_leads';
	}

	/**
	 * Get the email logs table name.
	 *
	 * @return string
	 */
	public static function email_logs_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'llr_email_logs';
	}

	/**
	 * Insert a lead.
	 *
	 * @param array $lead Lead data.
	 * @return int|false
	 */
	public static function insert_lead( $lead ) {
		global $wpdb;

		$now = current_time( 'mysql' );
		$data = wp_parse_args(
			$lead,
			array(
				'created_at'      => $now,
				'updated_at'      => $now,
				'status'          => 'new',
				'name'            => '',
				'email'           => '',
				'phone'           => '',
				'service'         => '',
				'message'         => '',
				'source_url'      => '',
				'referrer'        => '',
				'utm_source'      => '',
				'utm_medium'      => '',
				'utm_campaign'    => '',
				'recipient_email' => '',
				'ip_hash'         => '',
				'user_agent'      => '',
				'meta'            => '',
			)
		);

		$inserted = $wpdb->insert(
			self::table_name(),
			$data,
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get a paginated list of leads.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public static function get_leads( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'page'     => 1,
				'per_page' => 20,
				'status'   => '',
				'search'   => '',
			)
		);

		$page = max( 1, absint( $args['page'] ) );
		$per_page = min( 100, max( 1, absint( $args['per_page'] ) ) );
		$offset = ( $page - 1 ) * $per_page;
		$where = array( '1=1' );
		$params = array();

		if ( '' !== $args['status'] ) {
			$where[] = 'status = %s';
			$params[] = LLR_Plugin::normalize_status( $args['status'] );
		}

		if ( '' !== $args['search'] ) {
			$like = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR service LIKE %s OR message LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$params[] = $per_page;
		$params[] = $offset;

		$sql = 'SELECT * FROM ' . self::table_name() . ' WHERE ' . implode( ' AND ', $where ) . ' ORDER BY created_at DESC LIMIT %d OFFSET %d';

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Get leads for export without pagination.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public static function get_leads_for_export( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status' => '',
				'search' => '',
			)
		);

		$where = array( '1=1' );
		$params = array();

		if ( '' !== $args['status'] ) {
			$where[] = 'status = %s';
			$params[] = LLR_Plugin::normalize_status( $args['status'] );
		}

		if ( '' !== $args['search'] ) {
			$like = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR service LIKE %s OR message LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$sql = 'SELECT * FROM ' . self::table_name() . ' WHERE ' . implode( ' AND ', $where ) . ' ORDER BY created_at DESC';

		if ( empty( $params ) ) {
			return $wpdb->get_results( $sql );
		}

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Count leads matching filters.
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	public static function count_leads( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status' => '',
				'search' => '',
			)
		);

		$where = array( '1=1' );
		$params = array();

		if ( '' !== $args['status'] ) {
			$where[] = 'status = %s';
			$params[] = LLR_Plugin::normalize_status( $args['status'] );
		}

		if ( '' !== $args['search'] ) {
			$like = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR service LIKE %s OR message LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$sql = 'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE ' . implode( ' AND ', $where );

		if ( empty( $params ) ) {
			return (int) $wpdb->get_var( $sql );
		}

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Update lead status.
	 *
	 * @param int    $lead_id Lead ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public static function update_status( $lead_id, $status ) {
		global $wpdb;

		$updated = $wpdb->update(
			self::table_name(),
			array(
				'status'     => LLR_Plugin::normalize_status( $status ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => absint( $lead_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Delete a lead.
	 *
	 * @param int $lead_id Lead ID.
	 * @return bool
	 */
	public static function delete_lead( $lead_id ) {
		global $wpdb;

		$wpdb->delete(
			self::email_logs_table_name(),
			array( 'lead_id' => absint( $lead_id ) ),
			array( '%d' )
		);

		$deleted = $wpdb->delete(
			self::table_name(),
			array( 'id' => absint( $lead_id ) ),
			array( '%d' )
		);

		return false !== $deleted;
	}

	/**
	 * Count leads by status.
	 *
	 * @return array
	 */
	public static function status_counts() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT status, COUNT(*) AS total FROM ' . self::table_name() . ' GROUP BY status' );
		$counts = array_fill_keys( array_keys( LLR_Plugin::statuses() ), 0 );

		foreach ( $rows as $row ) {
			$status = LLR_Plugin::normalize_status( $row->status );
			$counts[ $status ] = (int) $row->total;
		}

		return $counts;
	}

	/**
	 * Insert an email delivery log.
	 *
	 * @param array $log Log data.
	 * @return int|false
	 */
	public static function insert_email_log( $log ) {
		global $wpdb;

		$data = wp_parse_args(
			$log,
			array(
				'lead_id'         => 0,
				'created_at'      => current_time( 'mysql' ),
				'recipient_email' => '',
				'subject'         => '',
				'status'          => 'unknown',
				'error_message'   => '',
			)
		);

		$inserted = $wpdb->insert(
			self::email_logs_table_name(),
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get recent email logs.
	 *
	 * @param int $limit Number of logs to fetch.
	 * @return array
	 */
	public static function get_email_logs( $limit = 20 ) {
		global $wpdb;

		$limit = min( 100, max( 1, absint( $limit ) ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::email_logs_table_name() . ' ORDER BY created_at DESC LIMIT %d',
				$limit
			)
		);
	}

	/**
	 * Count email logs by status.
	 *
	 * @return array
	 */
	public static function email_log_counts() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT status, COUNT(*) AS total FROM ' . self::email_logs_table_name() . ' GROUP BY status' );
		$counts = array(
			'sent'   => 0,
			'failed' => 0,
		);

		foreach ( $rows as $row ) {
			$status = sanitize_key( $row->status );

			if ( isset( $counts[ $status ] ) ) {
				$counts[ $status ] = (int) $row->total;
			}
		}

		return $counts;
	}

	/**
	 * Check whether a custom table exists.
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	public static function table_exists( $table_name ) {
		global $wpdb;

		return $table_name === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
	}
}
