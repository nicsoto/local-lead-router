<?php
/**
 * Plugin activation tasks.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles install-time database and option setup.
 */
class LLR_Activator {
	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_tables();

		if ( false === get_option( LLR_OPTION, false ) ) {
			add_option( LLR_OPTION, LLR_Plugin::default_settings() );
		}

		update_option( 'llr_db_version', LLR_VERSION );
	}

	/**
	 * Create or update custom tables.
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$table_name = LLR_DB::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'new',
			name varchar(190) NOT NULL DEFAULT '',
			email varchar(190) NOT NULL DEFAULT '',
			phone varchar(100) NOT NULL DEFAULT '',
			service varchar(190) NOT NULL DEFAULT '',
			message text NOT NULL,
			source_url text NULL,
			referrer text NULL,
			utm_source varchar(190) NOT NULL DEFAULT '',
			utm_medium varchar(190) NOT NULL DEFAULT '',
			utm_campaign varchar(190) NOT NULL DEFAULT '',
			recipient_email varchar(190) NOT NULL DEFAULT '',
			ip_hash varchar(64) NOT NULL DEFAULT '',
			user_agent text NULL,
			meta longtext NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY service (service),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
