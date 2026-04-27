<?php
/**
 * Diagnostics screen template.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Local Lead Router Diagnostics', 'local-lead-router' ); ?></h1>

	<h2><?php esc_html_e( 'System', 'local-lead-router' ); ?></h2>
	<table class="widefat striped llr-diagnostics-table">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Plugin version', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( LLR_VERSION ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Stored DB version', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( get_option( 'llr_db_version', __( 'Not set', 'local-lead-router' ) ) ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'WordPress version', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'PHP version', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Leads table', 'local-lead-router' ); ?></th>
				<td><?php echo $leads_table_exists ? esc_html__( 'OK', 'local-lead-router' ) : esc_html__( 'Missing', 'local-lead-router' ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Email logs table', 'local-lead-router' ); ?></th>
				<td><?php echo $email_logs_table_exists ? esc_html__( 'OK', 'local-lead-router' ) : esc_html__( 'Missing', 'local-lead-router' ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Fallback recipient', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( $settings['default_recipient'] ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Privacy tools', 'local-lead-router' ); ?></th>
				<td><?php esc_html_e( 'Personal data export and erasure hooks are registered.', 'local-lead-router' ); ?></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Lead Summary', 'local-lead-router' ); ?></h2>
	<table class="widefat striped llr-diagnostics-table">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Total leads', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( number_format_i18n( $lead_count ) ); ?></td>
			</tr>
			<?php foreach ( LLR_Plugin::statuses() as $status_key => $status_label ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $status_label ); ?></th>
					<td><?php echo esc_html( number_format_i18n( $status_counts[ $status_key ] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Email Delivery', 'local-lead-router' ); ?></h2>
	<table class="widefat striped llr-diagnostics-table">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sent', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( number_format_i18n( $email_log_counts['sent'] ) ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Failed', 'local-lead-router' ); ?></th>
				<td><?php echo esc_html( number_format_i18n( $email_log_counts['failed'] ) ); ?></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Recent Email Logs', 'local-lead-router' ); ?></h2>
	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Date', 'local-lead-router' ); ?></th>
				<th><?php esc_html_e( 'Lead ID', 'local-lead-router' ); ?></th>
				<th><?php esc_html_e( 'Recipient', 'local-lead-router' ); ?></th>
				<th><?php esc_html_e( 'Subject', 'local-lead-router' ); ?></th>
				<th><?php esc_html_e( 'Status', 'local-lead-router' ); ?></th>
				<th><?php esc_html_e( 'Error', 'local-lead-router' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $email_logs ) ) : ?>
				<tr>
					<td colspan="6"><?php esc_html_e( 'No email logs yet.', 'local-lead-router' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $email_logs as $log ) : ?>
				<tr>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $log->created_at ) ); ?></td>
					<td><?php echo esc_html( $log->lead_id ); ?></td>
					<td><?php echo esc_html( $log->recipient_email ); ?></td>
					<td><?php echo esc_html( $log->subject ); ?></td>
					<td><?php echo esc_html( ucfirst( $log->status ) ); ?></td>
					<td><?php echo esc_html( $log->error_message ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
