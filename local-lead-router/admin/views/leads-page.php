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
