<?php
/**
 * Plugin Uninstaller
 *
 * Cleans up all plugin data when the plugin is deleted.
 * This file is called automatically by WordPress when the user
 * deletes the plugin from the Plugins screen.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly or if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only proceed if user has proper permissions.
if ( ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

/**
 * Clean up plugin data.
 */
function ure_uninstall_cleanup() {
	global $wpdb;

	// Delete plugin options.
	delete_option( 'ure_settings' );
	delete_option( 'ure_version' );
	delete_option( 'ure_db_version' );

	// Delete all user meta (saved profiles).
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
			'ure_profiles'
		)
	);

	// Drop custom database table.
	$table_name = $wpdb->prefix . 'ure_logs';
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name uses WordPress prefix, safe.
	$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );

	// Delete all transients.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_ure_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_ure_' ) . '%'
		)
	);

	// For multisite, clean up site options if on main site.
	if ( is_multisite() ) {
		delete_site_option( 'ure_network_settings' );
	}

	// Optional: Delete backup files (uncomment if you want to remove backups).
	/*
	$upload_dir = wp_upload_dir();
	$backup_dir = $upload_dir['basedir'] . '/ure-backups/';
	if ( is_dir( $backup_dir ) ) {
		ure_recursive_delete( $backup_dir );
	}
	*/
}

/**
 * Recursively delete a directory and its contents.
 *
 * @param string $dir Directory path.
 * @return bool True on success.
 */
function ure_recursive_delete( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return false;
	}

	$files = array_diff( scandir( $dir ), array( '.', '..' ) );

	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		if ( is_dir( $path ) ) {
			ure_recursive_delete( $path );
		} else {
			unlink( $path );
		}
	}

	return rmdir( $dir );
}

// Run cleanup.
ure_uninstall_cleanup();
