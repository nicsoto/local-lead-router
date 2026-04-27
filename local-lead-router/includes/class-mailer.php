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
