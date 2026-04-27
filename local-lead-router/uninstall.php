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

$settings = get_option( 'llr_settings', array() );
$delete_data = is_array( $settings ) && ! empty( $settings['delete_data_on_uninstall'] );

if ( ! $delete_data ) {
	return;
}

$leads_table = $wpdb->prefix . 'llr_leads';
$email_logs_table = $wpdb->prefix . 'llr_email_logs';

$wpdb->query( "DROP TABLE IF EXISTS {$leads_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$email_logs_table}" );

delete_option( 'llr_settings' );
delete_option( 'llr_db_version' );
