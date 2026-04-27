<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'llr_leads';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

delete_option( 'llr_settings' );
delete_option( 'llr_db_version' );
