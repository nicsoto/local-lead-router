<?php
/**
 * Public lead form template.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$source_url = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
$utm_source = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_source'] ) ) : '';
$utm_medium = isset( $_GET['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) ) : '';
$utm_campaign = isset( $_GET['utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_campaign'] ) ) : '';
?>

<div class="llr-form-wrap">
	<?php if ( 'success' === $status ) : ?>
		<div class="llr-notice llr-notice-success" role="status">
			<?php echo esc_html( $settings['success_message'] ); ?>
		</div>
	<?php elseif ( '' !== $error ) : ?>
		<div class="llr-notice llr-notice-error" role="alert">
			<?php echo esc_html( $error_message ); ?>
		</div>
	<?php endif; ?>

	<form class="llr-form" method="post" action="">
		<?php if ( '' !== $title ) : ?>
			<h3 class="llr-form-title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<input type="hidden" name="llr_action" value="submit_lead">
		<input type="hidden" name="llr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'llr_submit_lead' ) ); ?>">
		<input type="hidden" name="llr_source_url" value="<?php echo esc_url( $source_url ); ?>">
		<input type="hidden" name="llr_referrer" value="<?php echo esc_url( $referrer ); ?>">
		<input type="hidden" name="llr_utm_source" value="<?php echo esc_attr( $utm_source ); ?>">
		<input type="hidden" name="llr_utm_medium" value="<?php echo esc_attr( $utm_medium ); ?>">
		<input type="hidden" name="llr_utm_campaign" value="<?php echo esc_attr( $utm_campaign ); ?>">

		<div class="llr-field llr-honeypot" aria-hidden="true">
			<label for="llr_company"><?php esc_html_e( 'Company', 'local-lead-router' ); ?></label>
			<input id="llr_company" type="text" name="llr_company" tabindex="-1" autocomplete="off">
		</div>

		<div class="llr-field">
			<label for="llr_name"><?php esc_html_e( 'Name', 'local-lead-router' ); ?> <span aria-hidden="true">*</span></label>
			<input id="llr_name" type="text" name="llr_name" value="<?php echo esc_attr( $posted['name'] ); ?>" required autocomplete="name">
		</div>

		<div class="llr-field">
			<label for="llr_email"><?php esc_html_e( 'Email', 'local-lead-router' ); ?> <span aria-hidden="true">*</span></label>
			<input id="llr_email" type="email" name="llr_email" value="<?php echo esc_attr( $posted['email'] ); ?>" required autocomplete="email">
		</div>

		<div class="llr-field">
			<label for="llr_phone"><?php esc_html_e( 'Phone', 'local-lead-router' ); ?></label>
			<input id="llr_phone" type="tel" name="llr_phone" value="<?php echo esc_attr( $posted['phone'] ); ?>" autocomplete="tel">
		</div>

		<div class="llr-field">
			<label for="llr_service"><?php esc_html_e( 'Service', 'local-lead-router' ); ?> <span aria-hidden="true">*</span></label>
			<select id="llr_service" name="llr_service" required>
				<option value=""><?php esc_html_e( 'Select an option', 'local-lead-router' ); ?></option>
				<?php foreach ( $routes as $route ) : ?>
					<option value="<?php echo esc_attr( $route['label'] ); ?>" <?php selected( $posted['service'], $route['label'] ); ?>><?php echo esc_html( $route['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="llr-field">
			<label for="llr_message"><?php esc_html_e( 'Message', 'local-lead-router' ); ?> <span aria-hidden="true">*</span></label>
			<textarea id="llr_message" name="llr_message" rows="5" required><?php echo esc_textarea( $posted['message'] ); ?></textarea>
		</div>

		<?php if ( ! empty( $settings['show_consent'] ) ) : ?>
			<div class="llr-field llr-consent">
				<label>
					<input type="checkbox" name="llr_consent" value="1" <?php checked( ! empty( $posted['consent'] ) ); ?> required>
					<?php echo esc_html( $settings['consent_text'] ); ?>
				</label>
			</div>
		<?php endif; ?>

		<button class="llr-submit" type="submit"><?php esc_html_e( 'Send request', 'local-lead-router' ); ?></button>
	</form>
</div>
