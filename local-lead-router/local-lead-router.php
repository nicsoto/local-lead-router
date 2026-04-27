<?php
/**
 * Plugin Name: Local Lead Router
 * Plugin URI: https://example.com/local-lead-router
 * Description: Capture local service leads, route them to the right inbox, and manage follow-up inside WordPress.
 * Version: 0.3.0
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

define( 'LLR_VERSION', '0.3.0' );
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
