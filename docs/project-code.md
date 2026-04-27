# Local Lead Router - Codigo Completo

Este archivo consolida la estructura del proyecto y el contenido de los archivos fuente/configuracion relevantes.

Generado desde el workspace local. No incluye `.git`, `dist/`, zips generados ni archivos locales de Codex.

## Estructura Del Proyecto

```text
.editorconfig
.github/workflows/ci.yml
.github/workflows/release.yml
.gitignore
README.md
docker-compose.yml
local-lead-router/README.md
local-lead-router/admin/css/lead-router-admin.css
local-lead-router/admin/js/lead-router-admin.js
local-lead-router/admin/views/diagnostics-page.php
local-lead-router/admin/views/leads-page.php
local-lead-router/admin/views/settings-page.php
local-lead-router/includes/class-activator.php
local-lead-router/includes/class-admin.php
local-lead-router/includes/class-db.php
local-lead-router/includes/class-mailer.php
local-lead-router/includes/class-plugin.php
local-lead-router/includes/class-privacy.php
local-lead-router/includes/class-public.php
local-lead-router/includes/class-router.php
local-lead-router/languages/local-lead-router.pot
local-lead-router/local-lead-router.php
local-lead-router/public/css/lead-router.css
local-lead-router/public/views/form.php
local-lead-router/readme.txt
local-lead-router/uninstall.php
scripts/build-zip.sh
scripts/lint.sh
```

## Archivos

### `.editorconfig`

```text
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
indent_style = tab
indent_size = 4
trim_trailing_whitespace = true

[*.{md,txt,yml,yaml,json}]
indent_style = space
indent_size = 2

```

### `.github/workflows/ci.yml`

```yaml
name: CI

on:
  push:
    branches:
      - main
  pull_request:

jobs:
  lint:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Set up Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Lint PHP and JS
        run: bash scripts/lint.sh

      - name: Build plugin zip
        run: bash scripts/build-zip.sh

  plugin-check:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Run WordPress Plugin Check
        uses: wordpress/plugin-check-action@v1
        with:
          build-dir: './local-lead-router'
          ignore-warnings: true

```

### `.github/workflows/release.yml`

```yaml
name: Release

on:
  push:
    tags:
      - 'v*'

permissions:
  contents: write

jobs:
  release:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Set up Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Lint
        run: bash scripts/lint.sh

      - name: Build plugin zip
        run: bash scripts/build-zip.sh

      - name: Publish GitHub release
        env:
          GH_TOKEN: ${{ github.token }}
        run: |
          VERSION="${GITHUB_REF_NAME#v}"
          gh release create "${GITHUB_REF_NAME}" "dist/local-lead-router-${VERSION}.zip" \
            --title "Local Lead Router ${VERSION}" \
            --notes "Release ${GITHUB_REF_NAME}"

```

### `.gitignore`

```text
.codex
*.zip
dist/
.DS_Store
Thumbs.db

```

### `README.md`

```markdown
# Local Lead Router

Local Lead Router is a lightweight WordPress plugin for local service businesses that need to capture leads, route them to the right inbox, and manage follow-up inside WordPress.

The plugin code lives in [`local-lead-router`](local-lead-router).

## Local Development

Start WordPress with Docker:

` ` `bash
docker compose up -d
` ` `

Then open `http://localhost:8080`, finish the WordPress install, activate Local Lead Router, and add this shortcode to a page:

` ` `text
[lead_router_form]
` ` `

## Checks

` ` `bash
bash scripts/lint.sh
` ` `

## Build

` ` `bash
bash scripts/build-zip.sh
` ` `

The generated ZIP is written to `dist/`.

## Release

Push a version tag to build and publish a GitHub release artifact:

` ` `bash
git tag v0.4.0
git push origin v0.4.0
` ` `

```

### `docker-compose.yml`

```yaml
services:
  db:
    image: mysql:8.0
    container_name: llr-wordpress-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - llr_db_data:/var/lib/mysql

  wordpress:
    image: wordpress:latest
    container_name: llr-wordpress
    restart: unless-stopped
    depends_on:
      - db
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - llr_wordpress_data:/var/www/html
      - ./local-lead-router:/var/www/html/wp-content/plugins/local-lead-router

volumes:
  llr_db_data:
  llr_wordpress_data:

```

### `local-lead-router/README.md`

```markdown
# Local Lead Router

Local Lead Router is a lightweight WordPress plugin MVP for local service businesses that need to capture leads, route them to the right email inbox, and manage basic follow-up inside WordPress.

## MVP scope

- Shortcode form: `[lead_router_form]`
- Service-based email routing
- Lead storage in a custom WordPress table
- Lead inbox with simple statuses
- CSV export from the lead inbox
- Email delivery logs and diagnostics
- UTM source, medium, and campaign capture
- Honeypot spam protection
- Basic rate limiting
- Optional consent checkbox
- WordPress personal data export/erase integration
- Suggested WordPress privacy policy content
- Diagnostic test email from the admin screen

## Target niche

The first niche is local service businesses and small agencies: plumbers, repair services, contractors, clinics, studios, and service teams that need different request types to reach different inboxes.

This niche is intentionally simpler than a full CRM and easier to validate than a generic form builder.

## Install locally

1. Copy the `local-lead-router` folder into `wp-content/plugins/`.
2. Activate the plugin in WordPress.
3. Go to **Lead Router > Settings**.
4. Configure service routes and recipient emails.
5. Add `[lead_router_form]` to a page.

## Notes

Email delivery depends on `wp_mail()` and the hosting provider. For production sites, pair this with a reliable SMTP plugin.

## Development

This repository includes a Docker Compose setup for local testing:

` ` `bash
docker compose up -d
` ` `

Then visit `http://localhost:8080`, install WordPress, activate the plugin, and add `[lead_router_form]` to a page.

Run local lint checks:

` ` `bash
bash scripts/lint.sh
` ` `

Build an installable plugin ZIP:

` ` `bash
bash scripts/build-zip.sh
` ` `

```

### `local-lead-router/admin/css/lead-router-admin.css`

```css
.llr-routes-table td {
	vertical-align: middle;
}

.llr-route-actions {
	width: 120px;
}

.llr-diagnostics-table {
	max-width: 760px;
}

.llr-diagnostics-table th {
	width: 220px;
}

.llr-test-email-form {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	align-items: center;
	margin: 0 0 16px;
}

```

### `local-lead-router/admin/js/lead-router-admin.js`

```javascript
(function () {
	'use strict';

	function createRouteRow() {
		var labels = window.LLRAdmin || {};
		var servicePlaceholder = labels.servicePlaceholder || '';
		var emailPlaceholder = labels.emailPlaceholder || '';
		var removeRoute = labels.removeRoute || 'Remove';
		var row = document.createElement('tr');

		row.setAttribute('data-llr-route-row', '');
		row.innerHTML = [
			'<td><input class="regular-text" type="text" name="route_label[]" placeholder="' + escapeAttribute(servicePlaceholder) + '"></td>',
			'<td><input class="regular-text" type="email" name="route_email[]" placeholder="' + escapeAttribute(emailPlaceholder) + '"></td>',
			'<td class="llr-route-actions"><button type="button" class="button" data-llr-remove-route>' + escapeHtml(removeRoute) + '</button></td>'
		].join('');
		return row;
	}

	function escapeAttribute(value) {
		return String(value).replace(/[&<>"']/g, function (character) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			}[character];
		});
	}

	function escapeHtml(value) {
		return escapeAttribute(value);
	}

	document.addEventListener('click', function (event) {
		var addButton = event.target.closest('[data-llr-add-route]');
		var removeButton = event.target.closest('[data-llr-remove-route]');
		var tableBody = document.querySelector('[data-llr-routes]');

		if (addButton && tableBody) {
			event.preventDefault();
			tableBody.appendChild(createRouteRow());
		}

		if (removeButton) {
			event.preventDefault();

			var row = removeButton.closest('[data-llr-route-row]');
			var rows = document.querySelectorAll('[data-llr-route-row]');

			if (row && rows.length > 1) {
				row.remove();
			}
		}
	});
}());

```

### `local-lead-router/admin/views/diagnostics-page.php`

```php
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

	<?php if ( 'test_email_sent' === $notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Test email sent. Check the recent email logs below for delivery details.', 'local-lead-router' ); ?></p>
		</div>
	<?php elseif ( 'test_email_failed' === $notice ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Test email failed. Check the recent email logs below for details.', 'local-lead-router' ); ?></p>
		</div>
	<?php endif; ?>

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
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="llr-test-email-form">
		<input type="hidden" name="action" value="llr_send_test_email">
		<?php wp_nonce_field( 'llr_send_test_email' ); ?>
		<label for="llr_test_email"><?php esc_html_e( 'Send test email to', 'local-lead-router' ); ?></label>
		<input id="llr_test_email" type="email" class="regular-text" name="test_email" value="<?php echo esc_attr( $settings['default_recipient'] ); ?>">
		<?php submit_button( __( 'Send test email', 'local-lead-router' ), 'secondary', '', false ); ?>
	</form>
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

```

### `local-lead-router/admin/views/leads-page.php`

```php
<?php
/**
 * Lead inbox screen template.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notice = isset( $_GET['llr_notice'] ) ? sanitize_key( wp_unslash( $_GET['llr_notice'] ) ) : '';
$base_url = admin_url( 'admin.php?page=llr-leads' );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Lead Inbox', 'local-lead-router' ); ?></h1>

	<?php if ( 'lead_deleted' === $notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Lead deleted.', 'local-lead-router' ); ?></p>
		</div>
	<?php endif; ?>

	<ul class="subsubsub">
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'status', 'all', $base_url ) ); ?>" class="<?php echo '' === $current_status ? 'current' : ''; ?>">
				<?php esc_html_e( 'All', 'local-lead-router' ); ?>
			</a> |
		</li>
		<?php foreach ( $statuses as $status_key => $status_label ) : ?>
			<li>
				<a href="<?php echo esc_url( add_query_arg( 'status', $status_key, $base_url ) ); ?>" class="<?php echo $current_status === $status_key ? 'current' : ''; ?>">
					<?php echo esc_html( $status_label ); ?>
					<span class="count">(<?php echo esc_html( $status_counts[ $status_key ] ); ?>)</span>
				</a>
				<?php if ( array_key_last( $statuses ) !== $status_key ) : ?> | <?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<form method="get" style="clear: both; margin: 16px 0;">
		<input type="hidden" name="page" value="llr-leads">
		<?php if ( '' !== $current_status ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $current_status ); ?>">
		<?php endif; ?>
		<p class="search-box" style="float: none;">
			<label class="screen-reader-text" for="llr-search-input"><?php esc_html_e( 'Search leads', 'local-lead-router' ); ?></label>
			<input id="llr-search-input" type="search" name="s" value="<?php echo esc_attr( $search ); ?>">
			<?php submit_button( __( 'Search leads', 'local-lead-router' ), '', '', false ); ?>
		</p>
	</form>

	<p>
		<?php
		$export_args = array(
			'action' => 'llr_export_leads',
		);

		if ( '' !== $current_status ) {
			$export_args['status'] = $current_status;
		}

		if ( '' !== $search ) {
			$export_args['s'] = $search;
		}

		$export_url = wp_nonce_url( add_query_arg( $export_args, admin_url( 'admin-post.php' ) ), 'llr_export_leads' );
		?>
		<a class="button button-secondary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'local-lead-router' ); ?></a>
	</p>

	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Lead', 'local-lead-router' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Service', 'local-lead-router' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Message', 'local-lead-router' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Source', 'local-lead-router' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'local-lead-router' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'local-lead-router' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $leads ) ) : ?>
				<tr>
					<td colspan="6"><?php esc_html_e( 'No leads found yet.', 'local-lead-router' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $leads as $lead ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $lead->name ); ?></strong><br>
						<a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a><br>
						<?php if ( '' !== $lead->phone ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $lead->phone ) ); ?>"><?php echo esc_html( $lead->phone ); ?></a>
						<?php endif; ?>
						<div class="row-actions">
							<span class="trash">
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=llr_delete_lead&lead_id=' . absint( $lead->id ) ), 'llr_delete_lead_' . absint( $lead->id ) ) ); ?>" onclick="return confirm('<?php echo esc_attr__( 'Delete this lead?', 'local-lead-router' ); ?>');">
									<?php esc_html_e( 'Delete', 'local-lead-router' ); ?>
								</a>
							</span>
						</div>
					</td>
					<td>
						<?php echo esc_html( $lead->service ); ?><br>
						<small><?php echo esc_html( $lead->recipient_email ); ?></small>
					</td>
					<td>
						<details>
							<summary><?php echo esc_html( wp_trim_words( $lead->message, 14 ) ); ?></summary>
							<p><?php echo nl2br( esc_html( $lead->message ) ); ?></p>
						</details>
					</td>
					<td>
						<?php if ( '' !== $lead->source_url ) : ?>
							<a href="<?php echo esc_url( $lead->source_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Open page', 'local-lead-router' ); ?>
							</a><br>
						<?php endif; ?>
						<?php if ( '' !== $lead->utm_source ) : ?>
							<small><?php echo esc_html( $lead->utm_source . ' / ' . $lead->utm_medium . ' / ' . $lead->utm_campaign ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="llr_update_lead_status">
							<input type="hidden" name="lead_id" value="<?php echo esc_attr( $lead->id ); ?>">
							<?php wp_nonce_field( 'llr_update_lead_status_' . absint( $lead->id ) ); ?>
							<select name="lead_status">
								<?php foreach ( $statuses as $status_key => $status_label ) : ?>
									<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $lead->status, $status_key ); ?>>
										<?php echo esc_html( $status_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php submit_button( __( 'Update', 'local-lead-router' ), 'small', '', false ); ?>
						</form>
					</td>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lead->created_at ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					printf(
						/* translators: %s: number of leads. */
						esc_html__( '%s items', 'local-lead-router' ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</span>
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%', $base_url ),
							'format'    => '',
							'current'   => $page,
							'total'     => $total_pages,
							'prev_text' => __( '&laquo;', 'local-lead-router' ),
							'next_text' => __( '&raquo;', 'local-lead-router' ),
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>

```

### `local-lead-router/admin/views/settings-page.php`

```php
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

```

### `local-lead-router/includes/class-activator.php`

```php
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
	 * Run database upgrades when plugin files have changed.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'llr_db_version' ) !== LLR_VERSION ) {
			self::activate();
		}
	}

	/**
	 * Create or update custom tables.
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$table_name = LLR_DB::table_name();
		$email_logs_table = LLR_DB::email_logs_table_name();
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

		$email_logs_sql = "CREATE TABLE {$email_logs_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			lead_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			recipient_email varchar(190) NOT NULL DEFAULT '',
			subject varchar(255) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT '',
			error_message text NULL,
			PRIMARY KEY  (id),
			KEY lead_id (lead_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		dbDelta( $email_logs_sql );
	}
}

```

### `local-lead-router/includes/class-admin.php`

```php
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_llr_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_llr_update_lead_status', array( $this, 'update_lead_status' ) );
		add_action( 'admin_post_llr_delete_lead', array( $this, 'delete_lead' ) );
		add_action( 'admin_post_llr_export_leads', array( $this, 'export_leads' ) );
		add_action( 'admin_post_llr_send_test_email', array( $this, 'send_test_email' ) );
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

		add_submenu_page(
			'llr-leads',
			__( 'Diagnostics', 'local-lead-router' ),
			__( 'Diagnostics', 'local-lead-router' ),
			'manage_options',
			'llr-diagnostics',
			array( $this, 'render_diagnostics_page' )
		);
	}

	/**
	 * Load admin assets on plugin pages.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'llr-' ) ) {
			return;
		}

		wp_enqueue_style( 'llr-admin', LLR_URL . 'admin/css/lead-router-admin.css', array(), LLR_VERSION );
		wp_enqueue_script( 'llr-admin', LLR_URL . 'admin/js/lead-router-admin.js', array(), LLR_VERSION, true );
		wp_localize_script(
			'llr-admin',
			'LLRAdmin',
			array(
				'removeRoute'        => __( 'Remove', 'local-lead-router' ),
				'servicePlaceholder' => __( 'Emergency plumbing', 'local-lead-router' ),
				'emailPlaceholder'   => __( 'team@example.com', 'local-lead-router' ),
			)
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
	 * Render diagnostics page.
	 *
	 * @return void
	 */
	public function render_diagnostics_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = LLR_Plugin::settings();
		$notice = isset( $_GET['llr_notice'] ) ? sanitize_key( wp_unslash( $_GET['llr_notice'] ) ) : '';
		$lead_count = LLR_DB::count_leads();
		$status_counts = LLR_DB::status_counts();
		$email_log_counts = LLR_DB::email_log_counts();
		$email_logs = LLR_DB::get_email_logs( 20 );
		$leads_table_exists = LLR_DB::table_exists( LLR_DB::table_name() );
		$email_logs_table_exists = LLR_DB::table_exists( LLR_DB::email_logs_table_name() );

		include LLR_DIR . 'admin/views/diagnostics-page.php';
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

	/**
	 * Export filtered leads as CSV.
	 *
	 * @return void
	 */
	public function export_leads() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export leads.', 'local-lead-router' ) );
		}

		check_admin_referer( 'llr_export_leads' );

		$status = isset( $_GET['status'] ) && 'all' !== sanitize_key( wp_unslash( $_GET['status'] ) ) ? LLR_Plugin::normalize_status( wp_unslash( $_GET['status'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$leads = LLR_DB::get_leads_for_export(
			array(
				'status' => $status,
				'search' => $search,
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=local-lead-router-' . gmdate( 'Y-m-d-His' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		fputcsv(
			$output,
			array(
				'id',
				'created_at',
				'status',
				'name',
				'email',
				'phone',
				'service',
				'message',
				'recipient_email',
				'source_url',
				'referrer',
				'utm_source',
				'utm_medium',
				'utm_campaign',
			)
		);

		foreach ( $leads as $lead ) {
			fputcsv(
				$output,
				array(
					$lead->id,
					$lead->created_at,
					$lead->status,
					$lead->name,
					$lead->email,
					$lead->phone,
					$lead->service,
					$lead->message,
					$lead->recipient_email,
					$lead->source_url,
					$lead->referrer,
					$lead->utm_source,
					$lead->utm_medium,
					$lead->utm_campaign,
				)
			);
		}

		exit;
	}

	/**
	 * Send a diagnostic test email.
	 *
	 * @return void
	 */
	public function send_test_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to send test emails.', 'local-lead-router' ) );
		}

		check_admin_referer( 'llr_send_test_email' );

		$settings = LLR_Plugin::settings();
		$to = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : $settings['default_recipient'];
		$sent = LLR_Mailer::send_test_email( $to );
		$notice = $sent ? 'test_email_sent' : 'test_email_failed';

		wp_safe_redirect( add_query_arg( 'llr_notice', $notice, admin_url( 'admin.php?page=llr-diagnostics' ) ) );
		exit;
	}
}

```

### `local-lead-router/includes/class-db.php`

```php
<?php
/**
 * Database access layer.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table names and SQL fragments are generated internally; user input is still passed through $wpdb->prepare().

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
	 * Get leads for a privacy export request.
	 *
	 * @param string $email Email address.
	 * @param int    $page Page number.
	 * @param int    $per_page Items per page.
	 * @return array
	 */
	public static function get_leads_by_email( $email, $page = 1, $per_page = 50 ) {
		global $wpdb;

		$email = sanitize_email( $email );
		$page = max( 1, absint( $page ) );
		$per_page = min( 100, max( 1, absint( $per_page ) ) );
		$offset = ( $page - 1 ) * $per_page;

		if ( ! is_email( $email ) ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table_name() . ' WHERE email = %s ORDER BY id ASC LIMIT %d OFFSET %d',
				$email,
				$per_page,
				$offset
			)
		);
	}

	/**
	 * Count leads matching an email address.
	 *
	 * @param string $email Email address.
	 * @return int
	 */
	public static function count_leads_by_email( $email ) {
		global $wpdb;

		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return 0;
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE email = %s',
				$email
			)
		);
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
	 * Delete leads matching an email address.
	 *
	 * @param string $email Email address.
	 * @param int    $limit Maximum records to delete.
	 * @return int
	 */
	public static function delete_leads_by_email( $email, $limit = 50 ) {
		global $wpdb;

		$email = sanitize_email( $email );
		$limit = min( 100, max( 1, absint( $limit ) ) );

		if ( ! is_email( $email ) ) {
			return 0;
		}

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT id FROM ' . self::table_name() . ' WHERE email = %s ORDER BY id ASC LIMIT %d',
				$email,
				$limit
			)
		);

		foreach ( $ids as $lead_id ) {
			self::delete_lead( $lead_id );
		}

		return count( $ids );
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

```

### `local-lead-router/includes/class-mailer.php`

```php
<?php
/**
 * Email notification layer.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends lead notifications.
 */
class LLR_Mailer {
	/**
	 * Send a diagnostic email from the admin screen.
	 *
	 * @param string $to Recipient email.
	 * @return bool
	 */
	public static function send_test_email( $to ) {
		$to = sanitize_email( $to );

		if ( ! is_email( $to ) ) {
			LLR_DB::insert_email_log(
				array(
					'lead_id'         => 0,
					'recipient_email' => $to,
					'subject'         => '',
					'status'          => 'failed',
					'error_message'   => __( 'Test email recipient is invalid.', 'local-lead-router' ),
				)
			);

			return false;
		}

		$subject = __( 'Local Lead Router test email', 'local-lead-router' );
		$body = sprintf(
			/* translators: %s: site name. */
			__( "This is a Local Lead Router delivery test from %s.\n\nIf you received this email, WordPress mail delivery is working for this recipient.", 'local-lead-router' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);
		$sent = wp_mail( $to, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );

		LLR_DB::insert_email_log(
			array(
				'lead_id'         => 0,
				'recipient_email' => $to,
				'subject'         => $subject,
				'status'          => $sent ? 'sent' : 'failed',
				'error_message'   => $sent ? '' : __( 'wp_mail() returned false during the test email.', 'local-lead-router' ),
			)
		);

		return $sent;
	}

	/**
	 * Send admin notification for a lead.
	 *
	 * @param array $lead Lead data.
	 * @return bool
	 */
	public static function send_lead_notification( $lead, $lead_id = 0 ) {
		$settings = LLR_Plugin::settings();
		$to = isset( $lead['recipient_email'] ) ? sanitize_email( $lead['recipient_email'] ) : $settings['default_recipient'];

		if ( ! is_email( $to ) ) {
			LLR_DB::insert_email_log(
				array(
					'lead_id'         => absint( $lead_id ),
					'recipient_email' => $to,
					'subject'         => '',
					'status'          => 'failed',
					'error_message'   => __( 'Recipient email is invalid.', 'local-lead-router' ),
				)
			);

			return false;
		}

		$subject = self::replace_tokens( $settings['email_subject'], $lead );
		$body = self::build_body( $lead );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		if ( ! empty( $lead['email'] ) && is_email( $lead['email'] ) ) {
			$headers[] = 'Reply-To: ' . sanitize_text_field( $lead['name'] ) . ' <' . sanitize_email( $lead['email'] ) . '>';
		}

		$sent = wp_mail( $to, $subject, $body, $headers );

		LLR_DB::insert_email_log(
			array(
				'lead_id'         => absint( $lead_id ),
				'recipient_email' => $to,
				'subject'         => $subject,
				'status'          => $sent ? 'sent' : 'failed',
				'error_message'   => $sent ? '' : __( 'wp_mail() returned false.', 'local-lead-router' ),
			)
		);

		return $sent;
	}

	/**
	 * Build plain text notification body.
	 *
	 * @param array $lead Lead data.
	 * @return string
	 */
	private static function build_body( $lead ) {
		$lines = array(
			sprintf( 'Name: %s', $lead['name'] ),
			sprintf( 'Email: %s', $lead['email'] ),
			sprintf( 'Phone: %s', $lead['phone'] ),
			sprintf( 'Service: %s', $lead['service'] ),
			'',
			'Message:',
			$lead['message'],
			'',
			sprintf( 'Source URL: %s', $lead['source_url'] ),
			sprintf( 'Referrer: %s', $lead['referrer'] ),
			sprintf( 'UTM source: %s', $lead['utm_source'] ),
			sprintf( 'UTM medium: %s', $lead['utm_medium'] ),
			sprintf( 'UTM campaign: %s', $lead['utm_campaign'] ),
		);

		return implode( "\n", $lines );
	}

	/**
	 * Replace message tokens.
	 *
	 * @param string $template Subject template.
	 * @param array  $lead Lead data.
	 * @return string
	 */
	private static function replace_tokens( $template, $lead ) {
		$tokens = array(
			'{name}'    => isset( $lead['name'] ) ? $lead['name'] : '',
			'{email}'   => isset( $lead['email'] ) ? $lead['email'] : '',
			'{phone}'   => isset( $lead['phone'] ) ? $lead['phone'] : '',
			'{service}' => isset( $lead['service'] ) ? $lead['service'] : '',
		);

		return strtr( $template, $tokens );
	}
}

```

### `local-lead-router/includes/class-plugin.php`

```php
<?php
/**
 * Core plugin bootstrap and shared settings helpers.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates the public and admin plugin layers.
 */
class LLR_Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var LLR_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return LLR_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'init', array( $this, 'load_textdomain' ), 1 );
		add_action( 'init', array( 'LLR_Activator', 'maybe_upgrade' ), 5 );

		$public = new LLR_Public();
		$public->register_hooks();

		$privacy = new LLR_Privacy();
		$privacy->register_hooks();

		if ( is_admin() ) {
			$admin = new LLR_Admin();
			$admin->register_hooks();
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'local-lead-router', false, dirname( plugin_basename( LLR_FILE ) ) . '/languages' );
	}

	/**
	 * Default plugin settings.
	 *
	 * @return array
	 */
	public static function default_settings() {
		$admin_email = get_option( 'admin_email' );

		return array(
			'form_title'        => __( 'Request a quote', 'local-lead-router' ),
			'default_recipient' => $admin_email,
			'success_message'   => __( 'Thanks. Your message was sent successfully.', 'local-lead-router' ),
			'email_subject'     => __( 'New lead: {service}', 'local-lead-router' ),
			'show_consent'      => 1,
			'consent_text'      => __( 'I agree to be contacted about this request.', 'local-lead-router' ),
			'rate_limit_minutes' => 2,
			'delete_data_on_uninstall' => 0,
			'routes'            => array(
				array(
					'label' => __( 'New project or quote', 'local-lead-router' ),
					'email' => $admin_email,
				),
				array(
					'label' => __( 'Emergency service', 'local-lead-router' ),
					'email' => $admin_email,
				),
				array(
					'label' => __( 'Maintenance or repair', 'local-lead-router' ),
					'email' => $admin_email,
				),
			),
		);
	}

	/**
	 * Read merged plugin settings.
	 *
	 * @return array
	 */
	public static function settings() {
		$saved = get_option( LLR_OPTION, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = wp_parse_args( $saved, self::default_settings() );

		if ( empty( $settings['routes'] ) || ! is_array( $settings['routes'] ) ) {
			$settings['routes'] = self::default_settings()['routes'];
		}

		return $settings;
	}

	/**
	 * Sanitise plugin settings from an admin request.
	 *
	 * @param array $raw Raw request data.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		$defaults = self::default_settings();
		$settings = array(
			'form_title'        => isset( $raw['form_title'] ) ? sanitize_text_field( wp_unslash( $raw['form_title'] ) ) : $defaults['form_title'],
			'default_recipient' => isset( $raw['default_recipient'] ) ? sanitize_email( wp_unslash( $raw['default_recipient'] ) ) : $defaults['default_recipient'],
			'success_message'   => isset( $raw['success_message'] ) ? sanitize_text_field( wp_unslash( $raw['success_message'] ) ) : $defaults['success_message'],
			'email_subject'     => isset( $raw['email_subject'] ) ? sanitize_text_field( wp_unslash( $raw['email_subject'] ) ) : $defaults['email_subject'],
			'show_consent'      => empty( $raw['show_consent'] ) ? 0 : 1,
			'consent_text'      => isset( $raw['consent_text'] ) ? sanitize_text_field( wp_unslash( $raw['consent_text'] ) ) : $defaults['consent_text'],
			'rate_limit_minutes' => isset( $raw['rate_limit_minutes'] ) ? min( 60, max( 0, absint( $raw['rate_limit_minutes'] ) ) ) : $defaults['rate_limit_minutes'],
			'delete_data_on_uninstall' => empty( $raw['delete_data_on_uninstall'] ) ? 0 : 1,
			'routes'            => array(),
		);

		if ( ! is_email( $settings['default_recipient'] ) ) {
			$settings['default_recipient'] = get_option( 'admin_email' );
		}

		$labels = isset( $raw['route_label'] ) && is_array( $raw['route_label'] ) ? $raw['route_label'] : array();
		$emails = isset( $raw['route_email'] ) && is_array( $raw['route_email'] ) ? $raw['route_email'] : array();

		foreach ( $labels as $index => $label ) {
			if ( count( $settings['routes'] ) >= 25 ) {
				break;
			}

			$label = sanitize_text_field( wp_unslash( $label ) );
			$email = isset( $emails[ $index ] ) ? sanitize_email( wp_unslash( $emails[ $index ] ) ) : '';

			if ( '' === $label ) {
				continue;
			}

			if ( ! is_email( $email ) ) {
				$email = $settings['default_recipient'];
			}

			$settings['routes'][] = array(
				'label' => $label,
				'email' => $email,
			);
		}

		if ( empty( $settings['routes'] ) ) {
			$settings['routes'] = array(
				array(
					'label' => __( 'New project or quote', 'local-lead-router' ),
					'email' => $settings['default_recipient'],
				),
			);
		}

		return $settings;
	}

	/**
	 * Allowed lead statuses.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'new'       => __( 'New', 'local-lead-router' ),
			'contacted' => __( 'Contacted', 'local-lead-router' ),
			'won'       => __( 'Won', 'local-lead-router' ),
			'lost'      => __( 'Lost', 'local-lead-router' ),
		);
	}

	/**
	 * Normalise a status string.
	 *
	 * @param string $status Raw status.
	 * @return string
	 */
	public static function normalize_status( $status ) {
		$status = sanitize_key( $status );
		$allowed = array_keys( self::statuses() );

		return in_array( $status, $allowed, true ) ? $status : 'new';
	}

	/**
	 * Public form error messages.
	 *
	 * @return array
	 */
	public static function form_error_messages() {
		return array(
			'security'     => __( 'Security check failed. Please refresh the page and try again.', 'local-lead-router' ),
			'consent'      => __( 'Please confirm that you agree to be contacted.', 'local-lead-router' ),
			'name'         => __( 'Please enter your name.', 'local-lead-router' ),
			'email'        => __( 'Please enter a valid email address.', 'local-lead-router' ),
			'service'      => __( 'Please choose a service.', 'local-lead-router' ),
			'message'      => __( 'Please enter a message.', 'local-lead-router' ),
			'rate_limited' => __( 'Please wait a moment before sending another request.', 'local-lead-router' ),
			'storage'      => __( 'We could not save your request. Please try again.', 'local-lead-router' ),
		);
	}
}

```

### `local-lead-router/includes/class-privacy.php`

```php
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
		add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
	}

	/**
	 * Add suggested text to the WordPress privacy policy guide.
	 *
	 * @return void
	 */
	public function add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = wp_kses_post(
			wpautop(
				__(
					'Local Lead Router stores contact form submissions so site administrators can respond to service requests and manage follow-up. Stored data may include name, email address, phone number, selected service, message, source URL, referrer, UTM campaign fields, a hashed IP address, browser user agent, recipient email, lead status, and email delivery logs. This data is kept in this WordPress site database and is not sent to an external service by the plugin. Site administrators can export or erase stored lead data using the WordPress personal data tools.',
					'local-lead-router'
				)
			)
		);

		wp_add_privacy_policy_content( __( 'Local Lead Router', 'local-lead-router' ), $content );
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

```

### `local-lead-router/includes/class-public.php`

```php
<?php
/**
 * Public form and submission handling.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles shortcode rendering and lead submissions.
 */
class LLR_Public {
	/**
	 * Register public hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'handle_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'lead_router_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style( 'llr-public', LLR_URL . 'public/css/lead-router.css', array(), LLR_VERSION );
	}

	/**
	 * Render lead form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'title' => '',
			),
			$atts,
			'lead_router_form'
		);

		wp_enqueue_style( 'llr-public' );

		$settings = LLR_Plugin::settings();
		$routes = $settings['routes'];
		$title = '' !== $atts['title'] ? sanitize_text_field( $atts['title'] ) : $settings['form_title'];
		$status = isset( $_GET['llr_status'] ) ? sanitize_key( wp_unslash( $_GET['llr_status'] ) ) : '';
		$error = isset( $_GET['llr_error'] ) ? sanitize_key( wp_unslash( $_GET['llr_error'] ) ) : '';
		$error_messages = LLR_Plugin::form_error_messages();
		$error_message = isset( $error_messages[ $error ] ) ? $error_messages[ $error ] : __( 'Please check the form fields and try again.', 'local-lead-router' );
		$posted = $this->get_stored_form_data();

		ob_start();
		include LLR_DIR . 'public/views/form.php';
		return ob_get_clean();
	}

	/**
	 * Process posted lead forms.
	 *
	 * @return void
	 */
	public function handle_submission() {
		if ( empty( $_POST['llr_action'] ) || 'submit_lead' !== sanitize_text_field( wp_unslash( $_POST['llr_action'] ) ) ) {
			return;
		}

		$redirect = $this->redirect_url();
		$redirect = remove_query_arg( array( 'llr_status', 'llr_error', 'llr_token' ), $redirect );

		if ( empty( $_POST['llr_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['llr_nonce'] ) ), 'llr_submit_lead' ) ) {
			wp_safe_redirect( add_query_arg( 'llr_error', 'security', $redirect ) );
			exit;
		}

		if ( ! empty( $_POST['llr_company'] ) ) {
			wp_safe_redirect( add_query_arg( 'llr_status', 'success', $redirect ) );
			exit;
		}

		$settings = LLR_Plugin::settings();

		if ( ! empty( $settings['show_consent'] ) && empty( $_POST['llr_consent'] ) ) {
			$token = $this->store_form_data( $this->sanitize_submission() );
			wp_safe_redirect( add_query_arg( array( 'llr_error' => 'consent', 'llr_token' => $token ), $redirect ) );
			exit;
		}

		$lead = $this->sanitize_submission();
		$errors = $this->validate_submission( $lead );

		if ( ! empty( $errors ) ) {
			$token = $this->store_form_data( $lead );
			wp_safe_redirect( add_query_arg( array( 'llr_error' => reset( $errors ), 'llr_token' => $token ), $redirect ) );
			exit;
		}

		if ( $this->is_rate_limited( $lead['ip_hash'] ) ) {
			$token = $this->store_form_data( $lead );
			wp_safe_redirect( add_query_arg( array( 'llr_error' => 'rate_limited', 'llr_token' => $token ), $redirect ) );
			exit;
		}

		$lead['recipient_email'] = LLR_Router::recipient_for_service( $lead['service'] );
		$lead_id = LLR_DB::insert_lead( $lead );

		if ( false === $lead_id ) {
			wp_safe_redirect( add_query_arg( 'llr_error', 'storage', $redirect ) );
			exit;
		}

		$this->mark_rate_limited( $lead['ip_hash'] );
		LLR_Mailer::send_lead_notification( $lead, $lead_id );

		wp_safe_redirect( add_query_arg( 'llr_status', 'success', $redirect ) );
		exit;
	}

	/**
	 * Sanitise request fields.
	 *
	 * @return array
	 */
	private function sanitize_submission() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		return array(
			'name'         => isset( $_POST['llr_name'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_name'] ) ) : '',
			'email'        => isset( $_POST['llr_email'] ) ? sanitize_email( wp_unslash( $_POST['llr_email'] ) ) : '',
			'phone'        => isset( $_POST['llr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_phone'] ) ) : '',
			'service'      => isset( $_POST['llr_service'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_service'] ) ) : '',
			'message'      => isset( $_POST['llr_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['llr_message'] ) ) : '',
			'source_url'   => isset( $_POST['llr_source_url'] ) ? esc_url_raw( wp_unslash( $_POST['llr_source_url'] ) ) : '',
			'referrer'     => isset( $_POST['llr_referrer'] ) ? esc_url_raw( wp_unslash( $_POST['llr_referrer'] ) ) : '',
			'utm_source'   => isset( $_POST['llr_utm_source'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_source'] ) ) : '',
			'utm_medium'   => isset( $_POST['llr_utm_medium'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_medium'] ) ) : '',
			'utm_campaign' => isset( $_POST['llr_utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['llr_utm_campaign'] ) ) : '',
			'ip_hash'      => '' !== $ip ? hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ) : '',
			'user_agent'   => $user_agent,
			'meta'         => '',
		);
	}

	/**
	 * Resolve a safe redirect URL for form feedback.
	 *
	 * @return string
	 */
	private function redirect_url() {
		$candidates = array();

		if ( ! empty( $_POST['llr_source_url'] ) ) {
			$candidates[] = esc_url_raw( wp_unslash( $_POST['llr_source_url'] ) );
		}

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$candidates[] = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		}

		foreach ( $candidates as $candidate ) {
			$validated = wp_validate_redirect( $candidate, '' );

			if ( '' !== $validated ) {
				return $validated;
			}
		}

		return home_url( '/' );
	}

	/**
	 * Validate required fields.
	 *
	 * @param array $lead Lead data.
	 * @return array
	 */
	private function validate_submission( $lead ) {
		$errors = array();

		if ( '' === $lead['name'] ) {
			$errors[] = 'name';
		}

		if ( ! is_email( $lead['email'] ) ) {
			$errors[] = 'email';
		}

		if ( '' === $lead['service'] ) {
			$errors[] = 'service';
		}

		if ( '' === $lead['message'] ) {
			$errors[] = 'message';
		}

		return $errors;
	}

	/**
	 * Store submitted fields briefly so the form can be repopulated after an error.
	 *
	 * @param array $lead Sanitised lead data.
	 * @return string
	 */
	private function store_form_data( $lead ) {
		$token = sanitize_key( wp_generate_password( 20, false, false ) );
		$data = array(
			'name'    => isset( $lead['name'] ) ? $lead['name'] : '',
			'email'   => isset( $lead['email'] ) ? $lead['email'] : '',
			'phone'   => isset( $lead['phone'] ) ? $lead['phone'] : '',
			'service' => isset( $lead['service'] ) ? $lead['service'] : '',
			'message' => isset( $lead['message'] ) ? $lead['message'] : '',
			'consent' => empty( $_POST['llr_consent'] ) ? 0 : 1,
		);

		set_transient( 'llr_form_' . $token, $data, 10 * MINUTE_IN_SECONDS );

		return $token;
	}

	/**
	 * Retrieve stored form data from a redirect token.
	 *
	 * @return array
	 */
	private function get_stored_form_data() {
		$defaults = array(
			'name'    => '',
			'email'   => '',
			'phone'   => '',
			'service' => '',
			'message' => '',
			'consent' => 0,
		);

		if ( empty( $_GET['llr_token'] ) ) {
			return $defaults;
		}

		$token = sanitize_key( wp_unslash( $_GET['llr_token'] ) );
		$data = get_transient( 'llr_form_' . $token );
		delete_transient( 'llr_form_' . $token );

		return is_array( $data ) ? wp_parse_args( $data, $defaults ) : $defaults;
	}

	/**
	 * Check whether the current visitor has posted too recently.
	 *
	 * @param string $ip_hash Visitor IP hash.
	 * @return bool
	 */
	private function is_rate_limited( $ip_hash ) {
		$settings = LLR_Plugin::settings();
		$minutes = isset( $settings['rate_limit_minutes'] ) ? absint( $settings['rate_limit_minutes'] ) : 0;

		if ( 0 === $minutes || '' === $ip_hash ) {
			return false;
		}

		return (bool) get_transient( 'llr_rate_' . substr( $ip_hash, 0, 32 ) );
	}

	/**
	 * Mark the current visitor as recently submitted.
	 *
	 * @param string $ip_hash Visitor IP hash.
	 * @return void
	 */
	private function mark_rate_limited( $ip_hash ) {
		$settings = LLR_Plugin::settings();
		$minutes = isset( $settings['rate_limit_minutes'] ) ? absint( $settings['rate_limit_minutes'] ) : 0;

		if ( 0 === $minutes || '' === $ip_hash ) {
			return;
		}

		set_transient( 'llr_rate_' . substr( $ip_hash, 0, 32 ), 1, $minutes * MINUTE_IN_SECONDS );
	}
}

```

### `local-lead-router/includes/class-router.php`

```php
<?php
/**
 * Lead routing rules.
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chooses the best recipient for a lead.
 */
class LLR_Router {
	/**
	 * Find recipient email for a selected service.
	 *
	 * @param string $service Selected service.
	 * @return string
	 */
	public static function recipient_for_service( $service ) {
		$settings = LLR_Plugin::settings();
		$service_key = self::normalise_route_key( $service );

		foreach ( $settings['routes'] as $route ) {
			$label = isset( $route['label'] ) ? $route['label'] : '';
			$email = isset( $route['email'] ) ? $route['email'] : '';

			if ( $service_key === self::normalise_route_key( $label ) && is_email( $email ) ) {
				return $email;
			}
		}

		return is_email( $settings['default_recipient'] ) ? $settings['default_recipient'] : get_option( 'admin_email' );
	}

	/**
	 * Normalise labels for matching.
	 *
	 * @param string $value Raw label.
	 * @return string
	 */
	private static function normalise_route_key( $value ) {
		return strtolower( trim( sanitize_text_field( $value ) ) );
	}
}

```

### `local-lead-router/languages/local-lead-router.pot`

```text
# Copyright (C) 2026 Local Lead Router
# This file is distributed under the same license as the Local Lead Router package.
# Translators:
#
msgid ""
msgstr ""
"Project-Id-Version: Local Lead Router 0.4.0\n"
"Report-Msgid-Bugs-To: https://github.com/nicsoto/local-lead-router/issues\n"
"POT-Creation-Date: 2026-04-27 16:05-0400\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"
"Language-Team: LANGUAGE <LL@li.org>\n"
"Language: \n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"

#: local-lead-router/includes/class-plugin.php:76
msgid "Request a quote"
msgstr ""

#: local-lead-router/includes/class-plugin.php:78
msgid "Thanks. Your message was sent successfully."
msgstr ""

#: local-lead-router/includes/class-plugin.php:79
msgid "New lead: {service}"
msgstr ""

#: local-lead-router/includes/class-plugin.php:81
msgid "I agree to be contacted about this request."
msgstr ""

#: local-lead-router/includes/class-plugin.php:86
#: local-lead-router/includes/class-plugin.php:174
msgid "New project or quote"
msgstr ""

#: local-lead-router/includes/class-plugin.php:90
msgid "Emergency service"
msgstr ""

#: local-lead-router/includes/class-plugin.php:94
msgid "Maintenance or repair"
msgstr ""

#: local-lead-router/includes/class-plugin.php:190
msgid "New"
msgstr ""

#: local-lead-router/includes/class-plugin.php:191
msgid "Contacted"
msgstr ""

#: local-lead-router/includes/class-plugin.php:192
msgid "Won"
msgstr ""

#: local-lead-router/includes/class-plugin.php:193
msgid "Lost"
msgstr ""

#: local-lead-router/includes/class-plugin.php:217
msgid "Security check failed. Please refresh the page and try again."
msgstr ""

#: local-lead-router/includes/class-plugin.php:218
msgid "Please confirm that you agree to be contacted."
msgstr ""

#: local-lead-router/includes/class-plugin.php:219
msgid "Please enter your name."
msgstr ""

#: local-lead-router/includes/class-plugin.php:220
msgid "Please enter a valid email address."
msgstr ""

#: local-lead-router/includes/class-plugin.php:221
msgid "Please choose a service."
msgstr ""

#: local-lead-router/includes/class-plugin.php:222
msgid "Please enter a message."
msgstr ""

#: local-lead-router/includes/class-plugin.php:223
msgid "Please wait a moment before sending another request."
msgstr ""

#: local-lead-router/includes/class-plugin.php:224
msgid "We could not save your request. Please try again."
msgstr ""

#: local-lead-router/includes/class-mailer.php:32
msgid "Test email recipient is invalid."
msgstr ""

#: local-lead-router/includes/class-mailer.php:39
msgid "Local Lead Router test email"
msgstr ""

#. translators: %s: site name.
#: local-lead-router/includes/class-mailer.php:42
#, php-format
msgid ""
"This is a Local Lead Router delivery test from %s.\n"
"\n"
"If you received this email, WordPress mail delivery is working for this "
"recipient."
msgstr ""

#: local-lead-router/includes/class-mailer.php:53
msgid "wp_mail() returned false during the test email."
msgstr ""

#: local-lead-router/includes/class-mailer.php:77
msgid "Recipient email is invalid."
msgstr ""

#: local-lead-router/includes/class-mailer.php:100
msgid "wp_mail() returned false."
msgstr ""

#: local-lead-router/includes/class-privacy.php:47
msgid ""
"Local Lead Router stores contact form submissions so site administrators can "
"respond to service requests and manage follow-up. Stored data may include "
"name, email address, phone number, selected service, message, source URL, "
"referrer, UTM campaign fields, a hashed IP address, browser user agent, "
"recipient email, lead status, and email delivery logs. This data is kept in "
"this WordPress site database and is not sent to an external service by the "
"plugin. Site administrators can export or erase stored lead data using the "
"WordPress personal data tools."
msgstr ""

#: local-lead-router/includes/class-privacy.php:53
#: local-lead-router/includes/class-admin.php:38
msgid "Local Lead Router"
msgstr ""

#: local-lead-router/includes/class-privacy.php:64
#: local-lead-router/includes/class-privacy.php:79
msgid "Local Lead Router leads"
msgstr ""

#: local-lead-router/includes/class-privacy.php:109
msgid "Local Lead Router Leads"
msgstr ""

#: local-lead-router/includes/class-privacy.php:165
#: local-lead-router/admin/views/diagnostics-page.php:106
msgid "Lead ID"
msgstr ""

#: local-lead-router/includes/class-privacy.php:169
msgid "Submitted at"
msgstr ""

#: local-lead-router/includes/class-privacy.php:173
#: local-lead-router/admin/views/leads-page.php:80
#: local-lead-router/admin/views/diagnostics-page.php:109
msgid "Status"
msgstr ""

#: local-lead-router/includes/class-privacy.php:177
#: local-lead-router/public/views/form.php:49
msgid "Name"
msgstr ""

#: local-lead-router/includes/class-privacy.php:181
#: local-lead-router/public/views/form.php:54
msgid "Email"
msgstr ""

#: local-lead-router/includes/class-privacy.php:185
#: local-lead-router/public/views/form.php:59
msgid "Phone"
msgstr ""

#: local-lead-router/includes/class-privacy.php:189
#: local-lead-router/public/views/form.php:64
#: local-lead-router/admin/views/leads-page.php:77
msgid "Service"
msgstr ""

#: local-lead-router/includes/class-privacy.php:193
#: local-lead-router/public/views/form.php:74
#: local-lead-router/admin/views/leads-page.php:78
msgid "Message"
msgstr ""

#: local-lead-router/includes/class-privacy.php:197
msgid "Source URL"
msgstr ""

#: local-lead-router/includes/class-privacy.php:201
msgid "Referrer"
msgstr ""

#: local-lead-router/includes/class-privacy.php:205
msgid "UTM Source"
msgstr ""

#: local-lead-router/includes/class-privacy.php:209
msgid "UTM Medium"
msgstr ""

#: local-lead-router/includes/class-privacy.php:213
msgid "UTM Campaign"
msgstr ""

#: local-lead-router/includes/class-public.php:59
msgid "Please check the form fields and try again."
msgstr ""

#: local-lead-router/includes/class-admin.php:39
msgid "Lead Router"
msgstr ""

#: local-lead-router/includes/class-admin.php:49
#: local-lead-router/includes/class-admin.php:50
#: local-lead-router/admin/views/leads-page.php:17
msgid "Lead Inbox"
msgstr ""

#: local-lead-router/includes/class-admin.php:58
#: local-lead-router/includes/class-admin.php:59
msgid "Settings"
msgstr ""

#: local-lead-router/includes/class-admin.php:67
#: local-lead-router/includes/class-admin.php:68
msgid "Diagnostics"
msgstr ""

#: local-lead-router/includes/class-admin.php:92
#: local-lead-router/admin/views/settings-page.php:125
msgid "Remove"
msgstr ""

#: local-lead-router/includes/class-admin.php:93
#: local-lead-router/admin/views/settings-page.php:119
msgid "Emergency plumbing"
msgstr ""

#: local-lead-router/includes/class-admin.php:94
#: local-lead-router/admin/views/settings-page.php:122
msgid "team@example.com"
msgstr ""

#: local-lead-router/includes/class-admin.php:182
msgid "You do not have permission to edit these settings."
msgstr ""

#: local-lead-router/includes/class-admin.php:201
#: local-lead-router/includes/class-admin.php:224
msgid "You do not have permission to manage leads."
msgstr ""

#: local-lead-router/includes/class-admin.php:245
msgid "You do not have permission to export leads."
msgstr ""

#: local-lead-router/includes/class-admin.php:318
msgid "You do not have permission to send test emails."
msgstr ""

#: local-lead-router/public/views/form.php:44
msgid "Company"
msgstr ""

#: local-lead-router/public/views/form.php:66
msgid "Select an option"
msgstr ""

#: local-lead-router/public/views/form.php:87
msgid "Send request"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:16
msgid "Local Lead Router Settings"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:20
msgid "Settings saved."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:25
msgid "Use this shortcode on any page or post:"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:37
msgid "Form title"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:45
#: local-lead-router/admin/views/diagnostics-page.php:54
msgid "Fallback recipient"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:49
msgid "Used when no route matches or a route email is invalid."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:54
msgid "Success message"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:62
msgid "Email subject"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:66
msgid "Available tokens: {name}, {email}, {phone}, {service}."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:70
msgid "Consent checkbox"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:74
msgid "Show and require consent checkbox"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:82
msgid "Rate limit"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:86
msgid ""
"minutes between accepted submissions from the same visitor. Use 0 to disable."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:90
msgid "Uninstall behavior"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:94
msgid "Delete leads, email logs, and settings when the plugin is uninstalled."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:101
msgid "Routing rules"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:102
msgid ""
"Each service appears as an option in the public form. Leads are sent to the "
"matching email address."
msgstr ""

#: local-lead-router/admin/views/settings-page.php:107
msgid "Service option"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:108
msgid "Recipient email"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:109
msgid "Actions"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:133
msgid "Add route"
msgstr ""

#: local-lead-router/admin/views/settings-page.php:136
msgid "Save settings"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:21
msgid "Lead deleted."
msgstr ""

#: local-lead-router/admin/views/leads-page.php:28
msgid "All"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:48
#: local-lead-router/admin/views/leads-page.php:50
msgid "Search leads"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:70
msgid "Export CSV"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:76
msgid "Lead"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:79
msgid "Source"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:81
#: local-lead-router/admin/views/diagnostics-page.php:105
msgid "Date"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:87
msgid "No leads found yet."
msgstr ""

#: local-lead-router/admin/views/leads-page.php:101
msgid "Delete this lead?"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:102
msgid "Delete"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:120
msgid "Open page"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:139
msgid "Update"
msgstr ""

#. translators: %s: number of leads.
#: local-lead-router/admin/views/leads-page.php:155
#, php-format
msgid "%s items"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:168
msgid "&laquo;"
msgstr ""

#: local-lead-router/admin/views/leads-page.php:169
msgid "&raquo;"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:14
msgid "Local Lead Router Diagnostics"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:18
msgid ""
"Test email sent. Check the recent email logs below for delivery details."
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:22
msgid "Test email failed. Check the recent email logs below for details."
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:26
msgid "System"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:30
msgid "Plugin version"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:34
msgid "Stored DB version"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:35
msgid "Not set"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:38
msgid "WordPress version"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:42
msgid "PHP version"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:46
msgid "Leads table"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:47
#: local-lead-router/admin/views/diagnostics-page.php:51
msgid "OK"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:47
#: local-lead-router/admin/views/diagnostics-page.php:51
msgid "Missing"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:50
msgid "Email logs table"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:58
msgid "Privacy tools"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:59
msgid "Personal data export and erasure hooks are registered."
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:64
msgid "Lead Summary"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:68
msgid "Total leads"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:80
msgid "Email Delivery"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:84
msgid "Send test email to"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:86
msgid "Send test email"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:91
msgid "Sent"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:95
msgid "Failed"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:101
msgid "Recent Email Logs"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:107
msgid "Recipient"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:108
msgid "Subject"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:110
msgid "Error"
msgstr ""

#: local-lead-router/admin/views/diagnostics-page.php:116
msgid "No email logs yet."
msgstr ""

```

### `local-lead-router/local-lead-router.php`

```php
<?php
/**
 * Plugin Name: Local Lead Router
 * Plugin URI: https://github.com/nicsoto/local-lead-router
 * Description: Capture local service leads, route them to the right inbox, and manage follow-up inside WordPress.
 * Version: 0.4.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Local Lead Router
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: local-lead-router
 * Domain Path: /languages
 *
 * @package LocalLeadRouter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LLR_VERSION', '0.4.0' );
define( 'LLR_FILE', __FILE__ );
define( 'LLR_DIR', plugin_dir_path( __FILE__ ) );
define( 'LLR_URL', plugin_dir_url( __FILE__ ) );
define( 'LLR_OPTION', 'llr_settings' );

require_once LLR_DIR . 'includes/class-plugin.php';
require_once LLR_DIR . 'includes/class-activator.php';
require_once LLR_DIR . 'includes/class-db.php';
require_once LLR_DIR . 'includes/class-router.php';
require_once LLR_DIR . 'includes/class-mailer.php';
require_once LLR_DIR . 'includes/class-privacy.php';
require_once LLR_DIR . 'includes/class-public.php';
require_once LLR_DIR . 'includes/class-admin.php';

register_activation_hook( __FILE__, array( 'LLR_Activator', 'activate' ) );

add_action(
	'plugins_loaded',
	static function () {
		LLR_Plugin::instance()->run();
	}
);

```

### `local-lead-router/public/css/lead-router.css`

```css
.llr-form-wrap {
	max-width: 680px;
}

.llr-form {
	display: grid;
	gap: 14px;
}

.llr-form-title {
	margin: 0 0 4px;
}

.llr-field {
	display: grid;
	gap: 6px;
}

.llr-field label {
	font-weight: 600;
}

.llr-field input,
.llr-field select,
.llr-field textarea {
	box-sizing: border-box;
	width: 100%;
	max-width: 100%;
	padding: 10px 12px;
	border: 1px solid #c9d1d9;
	border-radius: 6px;
	font: inherit;
}

.llr-field textarea {
	resize: vertical;
}

.llr-honeypot {
	position: absolute;
	left: -10000px;
	width: 1px;
	height: 1px;
	overflow: hidden;
}

.llr-consent label {
	display: flex;
	gap: 8px;
	align-items: flex-start;
	font-weight: 400;
}

.llr-consent input {
	width: auto;
	margin-top: 0.25em;
}

.llr-submit {
	justify-self: start;
	padding: 10px 16px;
	border: 0;
	border-radius: 6px;
	background: #1d2327;
	color: #fff;
	font: inherit;
	font-weight: 700;
	cursor: pointer;
}

.llr-submit:hover,
.llr-submit:focus {
	background: #2c3338;
}

.llr-notice {
	margin: 0 0 16px;
	padding: 12px 14px;
	border-left: 4px solid;
	background: #fff;
}

.llr-notice-success {
	border-color: #008a20;
}

.llr-notice-error {
	border-color: #d63638;
}

```

### `local-lead-router/public/views/form.php`

```php
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

```

### `local-lead-router/readme.txt`

```text
=== Local Lead Router ===
Contributors: localleadrouter
Tags: leads, lead routing, contact form, local business, crm
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Capture local service leads, route them to the right inbox, and manage follow-up inside WordPress.

== Description ==

Local Lead Router is a lightweight lead routing plugin for local service businesses, small agencies, and teams that need a simple way to send website requests to the right person.

The MVP includes:

* A shortcode lead form: `[lead_router_form]`
* Service-based email routing
* Lead storage inside WordPress
* A lead inbox with status tracking
* CSV export
* Email delivery logs
* Diagnostics screen
* UTM source, medium, and campaign capture
* Honeypot spam protection
* Basic rate limiting
* Consent checkbox option
* WordPress personal data export and erasure support
* Suggested privacy policy content
* Diagnostic test email

No external service is required.

== Installation ==

1. Upload the `local-lead-router` folder to `/wp-content/plugins/`.
2. Activate Local Lead Router from the Plugins screen.
3. Go to Lead Router > Settings.
4. Configure service options and recipient emails.
5. Add `[lead_router_form]` to any page.

== Frequently Asked Questions ==

= Does this replace a full CRM? =

No. It is intentionally focused on lead capture, routing, and basic follow-up status.

= Does this use a paid API? =

No. Emails are sent through WordPress using `wp_mail()`.

= Why did my email not arrive? =

`wp_mail()` depends on the hosting email configuration. If delivery is unreliable, use a trusted SMTP plugin.

== Changelog ==

= 0.4.0 =
* Added suggested privacy policy content for WordPress privacy settings.
* Added diagnostic test email action.
* Added Plugin Check and release workflows.

= 0.3.0 =
* Added WordPress personal data exporter and eraser integration.
* Added translation template.
* Added local lint/build scripts.
* Added GitHub Actions CI workflow.

= 0.2.0 =
* Added CSV export.
* Added email delivery logs.
* Added diagnostics screen.
* Added rate limiting and improved form error handling.
* Added dynamic route rows in settings.

= 0.1.0 =
* Initial MVP release.

```

### `local-lead-router/uninstall.php`

```php
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

```

### `scripts/build-zip.sh`

```bash
#!/usr/bin/env bash
set -euo pipefail

VERSION="$(grep -E "^[[:space:]]*\\* Version:" local-lead-router/local-lead-router.php | awk '{print $3}')"
OUTPUT_DIR="dist"
OUTPUT_FILE="${OUTPUT_DIR}/local-lead-router-${VERSION}.zip"

mkdir -p "${OUTPUT_DIR}"

if [ -f "${OUTPUT_FILE}" ]; then
	rm "${OUTPUT_FILE}"
fi

zip -r "${OUTPUT_FILE}" local-lead-router \
	-x 'local-lead-router/.DS_Store' \
	-x 'local-lead-router/**/.DS_Store'

printf 'Built %s\n' "${OUTPUT_FILE}"

```

### `scripts/lint.sh`

```bash
#!/usr/bin/env bash
set -euo pipefail

find local-lead-router -name '*.php' -print0 | xargs -0 -n1 php -l
node --check local-lead-router/admin/js/lead-router-admin.js

```

