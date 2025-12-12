<?php
/**
 * Plugin Installer
 *
 * Handles plugin activation and database table creation.
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installer class for plugin activation.
 */
class URE_Installer {

	/**
	 * Run activation tasks.
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'ure_logs';

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			summary text NOT NULL,
			details_json longtext NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY timestamp (timestamp)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Store database version for future upgrades.
		update_option( 'ure_db_version', '1.0.0' );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		// Add default options if needed.
		$defaults = array(
			'ure_version' => URE_VERSION,
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}

		// Initialize settings (requires class-ure-settings.php).
		if ( class_exists( 'URE_Settings' ) ) {
			URE_Settings::initialize();
		}
	}

	/**
	 * Check if database needs upgrade.
	 *
	 * @return bool
	 */
	public static function needs_upgrade() {
		$current_version = get_option( 'ure_db_version', '0.0.0' );
		return version_compare( $current_version, '1.0.0', '<' );
	}

	/**
	 * Upgrade database if needed.
	 */
	public static function maybe_upgrade() {
		if ( self::needs_upgrade() ) {
			self::create_tables();
		}
	}
}
