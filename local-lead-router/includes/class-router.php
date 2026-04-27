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
