<?php
/**
 * Plugin Name: Universal Replace Engine
 * Plugin URI: https://xtech.red/
 * Description: Enterprise-grade search and replace for WordPress. Content & database operations, SQL backups, GUID protection, saved profiles, and Pro regex mode.
 * Version: 1.6.0
 * Author: Xtech Red
 * Author URI: https://xtech.red/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: universal-replace-engine
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 *
 * @package UniversalReplaceEngine
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'URE_VERSION', '1.6.0' );
define( 'URE_PLUGIN_FILE', __FILE__ );
define( 'URE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'URE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'URE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include the installer for activation.
require_once URE_PLUGIN_DIR . 'includes/installer.php';

// Include the main plugin class.
require_once URE_PLUGIN_DIR . 'includes/class-ure-plugin.php';

/**
 * Plugin activation hook.
 * Creates database tables and sets up plugin options.
 */
function ure_activate_plugin() {
	URE_Installer::activate();
}
register_activation_hook( __FILE__, 'ure_activate_plugin' );

/**
 * Plugin deactivation hook.
 * Clean up transients if needed.
 */
function ure_deactivate_plugin() {
	// Clean up any transients or temporary data.
	delete_transient( 'ure_preview_data' );
}
register_deactivation_hook( __FILE__, 'ure_deactivate_plugin' );

/**
 * Initialize the plugin.
 */
function ure_init_plugin() {
	return URE_Plugin::get_instance();
}

// Start the plugin.
add_action( 'plugins_loaded', 'ure_init_plugin' );

/**
 * Register WP-CLI commands if WP-CLI is available.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once URE_PLUGIN_DIR . 'includes/class-ure-cli.php';
	WP_CLI::add_command( 'ure', 'URE_CLI' );
}
