<?php
/**
 * Settings Manager Class
 *
 * Handles plugin settings and configuration.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Manager class for plugin configuration.
 */
class URE_Settings {

	/**
	 * Option name for settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'ure_settings';

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private static $defaults = array(
		'content_batch_size'    => 100,    // Posts per batch for content operations.
		'database_batch_size'   => 5000,   // Rows per batch for database operations.
		'backup_batch_size'     => 1000,   // Rows per batch for backups.
		'max_preview_results'   => 20,     // Maximum preview results (Free).
		'history_limit'         => 5,      // Operation history limit (Free).
		'backup_retention_days' => 7,      // Days to keep backups.
		'enable_logging'        => true,   // Enable operation logging.
		'ajax_processing'       => true,   // Enable AJAX progress bar.
		'show_warnings'         => true,   // Show safety warnings.
	);

	/**
	 * Get a setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed Setting value.
	 */
	public static function get( $key, $default = null ) {
		$settings = get_option( self::OPTION_NAME, self::$defaults );

		// Merge with defaults to ensure all keys exist.
		$settings = wp_parse_args( $settings, self::$defaults );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		return $default !== null ? $default : ( isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : null );
	}

	/**
	 * Get all settings.
	 *
	 * @return array All settings.
	 */
	public static function get_all() {
		$settings = get_option( self::OPTION_NAME, self::$defaults );
		return wp_parse_args( $settings, self::$defaults );
	}

	/**
	 * Update a setting value.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True on success.
	 */
	public static function update( $key, $value ) {
		$settings         = self::get_all();
		$settings[ $key ] = $value;
		return update_option( self::OPTION_NAME, $settings );
	}

	/**
	 * Update multiple settings at once.
	 *
	 * @param array $new_settings Array of settings to update.
	 * @return bool True on success.
	 */
	public static function update_multiple( $new_settings ) {
		$settings = self::get_all();
		$settings = array_merge( $settings, $new_settings );
		return update_option( self::OPTION_NAME, $settings );
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @return bool True on success.
	 */
	public static function reset() {
		return update_option( self::OPTION_NAME, self::$defaults );
	}

	/**
	 * Delete all settings.
	 *
	 * @return bool True on success.
	 */
	public static function delete() {
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings.
	 */
	public static function get_defaults() {
		return self::$defaults;
	}

	/**
	 * Validate and sanitize settings.
	 *
	 * @param array $settings Settings to validate.
	 * @return array Validated settings.
	 */
	public static function validate( $settings ) {
		$validated = array();

		// Validate content batch size (10-1000).
		if ( isset( $settings['content_batch_size'] ) ) {
			$validated['content_batch_size'] = max( 10, min( 1000, absint( $settings['content_batch_size'] ) ) );
		}

		// Validate database batch size (100-10000).
		if ( isset( $settings['database_batch_size'] ) ) {
			$validated['database_batch_size'] = max( 100, min( 10000, absint( $settings['database_batch_size'] ) ) );
		}

		// Validate backup batch size (100-5000).
		if ( isset( $settings['backup_batch_size'] ) ) {
			$validated['backup_batch_size'] = max( 100, min( 5000, absint( $settings['backup_batch_size'] ) ) );
		}

		// Validate max preview results (1-100).
		if ( isset( $settings['max_preview_results'] ) ) {
			$is_pro = apply_filters( 'ure_is_pro', false );
			$max    = $is_pro ? 100 : 50;
			$validated['max_preview_results'] = max( 1, min( $max, absint( $settings['max_preview_results'] ) ) );
		}

		// Validate history limit (1-100).
		if ( isset( $settings['history_limit'] ) ) {
			$is_pro = apply_filters( 'ure_is_pro', false );
			$max    = $is_pro ? 50 : 10;
			$validated['history_limit'] = max( 1, min( $max, absint( $settings['history_limit'] ) ) );
		}

		// Validate backup retention days (1-30).
		if ( isset( $settings['backup_retention_days'] ) ) {
			$validated['backup_retention_days'] = max( 1, min( 30, absint( $settings['backup_retention_days'] ) ) );
		}

		// Validate boolean settings.
		// Note: Checkboxes send no data when unchecked, so we must explicitly set false if missing.
		$boolean_settings = array( 'enable_logging', 'ajax_processing', 'show_warnings' );
		foreach ( $boolean_settings as $key ) {
			$validated[ $key ] = isset( $settings[ $key ] ) ? (bool) $settings[ $key ] : false;
		}

		return $validated;
	}

	/**
	 * Initialize default settings on plugin activation.
	 */
	public static function initialize() {
		if ( false === get_option( self::OPTION_NAME ) ) {
			add_option( self::OPTION_NAME, self::$defaults );
		}
	}
}
