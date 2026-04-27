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
}
