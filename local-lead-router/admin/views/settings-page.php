<?php
/**
 * Settings screen template.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notice = isset( $_GET['llr_notice'] ) ? sanitize_key( wp_unslash( $_GET['llr_notice'] ) ) : '';
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Local Lead Router Settings', 'local-lead-router' ); ?></h1>

	<?php if ( 'settings_saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'local-lead-router' ); ?></p>
		</div>
	<?php endif; ?>

	<p>
		<?php esc_html_e( 'Use this shortcode on any page or post:', 'local-lead-router' ); ?>
		<code>[lead_router_form]</code>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="llr_save_settings">
		<?php wp_nonce_field( 'llr_save_settings' ); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="llr_form_title"><?php esc_html_e( 'Form title', 'local-lead-router' ); ?></label>
					</th>
					<td>
						<input id="llr_form_title" class="regular-text" type="text" name="form_title" value="<?php echo esc_attr( $settings['form_title'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="llr_default_recipient"><?php esc_html_e( 'Fallback recipient', 'local-lead-router' ); ?></label>
					</th>
					<td>
						<input id="llr_default_recipient" class="regular-text" type="email" name="default_recipient" value="<?php echo esc_attr( $settings['default_recipient'] ); ?>">
						<p class="description"><?php esc_html_e( 'Used when no route matches or a route email is invalid.', 'local-lead-router' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="llr_success_message"><?php esc_html_e( 'Success message', 'local-lead-router' ); ?></label>
					</th>
					<td>
						<input id="llr_success_message" class="large-text" type="text" name="success_message" value="<?php echo esc_attr( $settings['success_message'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="llr_email_subject"><?php esc_html_e( 'Email subject', 'local-lead-router' ); ?></label>
					</th>
					<td>
						<input id="llr_email_subject" class="large-text" type="text" name="email_subject" value="<?php echo esc_attr( $settings['email_subject'] ); ?>">
						<p class="description"><?php esc_html_e( 'Available tokens: {name}, {email}, {phone}, {service}.', 'local-lead-router' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Consent checkbox', 'local-lead-router' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="show_consent" value="1" <?php checked( ! empty( $settings['show_consent'] ) ); ?>>
							<?php esc_html_e( 'Show and require consent checkbox', 'local-lead-router' ); ?>
						</label>
						<br>
						<input class="large-text" type="text" name="consent_text" value="<?php echo esc_attr( $settings['consent_text'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="llr_rate_limit_minutes"><?php esc_html_e( 'Rate limit', 'local-lead-router' ); ?></label>
					</th>
					<td>
						<input id="llr_rate_limit_minutes" class="small-text" type="number" min="0" max="60" name="rate_limit_minutes" value="<?php echo esc_attr( $settings['rate_limit_minutes'] ); ?>">
						<?php esc_html_e( 'minutes between accepted submissions from the same visitor. Use 0 to disable.', 'local-lead-router' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Uninstall behavior', 'local-lead-router' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked( ! empty( $settings['delete_data_on_uninstall'] ) ); ?>>
							<?php esc_html_e( 'Delete leads, email logs, and settings when the plugin is uninstalled.', 'local-lead-router' ); ?>
						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Routing rules', 'local-lead-router' ); ?></h2>
		<p><?php esc_html_e( 'Each service appears as an option in the public form. Leads are sent to the matching email address.', 'local-lead-router' ); ?></p>

		<table class="widefat striped llr-routes-table" style="max-width: 900px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Service option', 'local-lead-router' ); ?></th>
					<th><?php esc_html_e( 'Recipient email', 'local-lead-router' ); ?></th>
					<th class="llr-route-actions"><?php esc_html_e( 'Actions', 'local-lead-router' ); ?></th>
				</tr>
			</thead>
			<tbody data-llr-routes>
				<?php
				$routes = $settings['routes'];
				foreach ( $routes as $route ) :
					?>
					<tr data-llr-route-row>
						<td>
							<input class="regular-text" type="text" name="route_label[]" value="<?php echo esc_attr( $route['label'] ); ?>" placeholder="<?php esc_attr_e( 'Emergency plumbing', 'local-lead-router' ); ?>">
						</td>
						<td>
							<input class="regular-text" type="email" name="route_email[]" value="<?php echo esc_attr( $route['email'] ); ?>" placeholder="<?php esc_attr_e( 'team@example.com', 'local-lead-router' ); ?>">
						</td>
						<td class="llr-route-actions">
							<button type="button" class="button" data-llr-remove-route><?php esc_html_e( 'Remove', 'local-lead-router' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button" data-llr-add-route><?php esc_html_e( 'Add route', 'local-lead-router' ); ?></button>
		</p>

		<?php submit_button( __( 'Save settings', 'local-lead-router' ) ); ?>
	</form>
</div>
