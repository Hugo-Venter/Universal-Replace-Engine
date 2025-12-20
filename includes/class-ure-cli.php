<?php
/**
 * WP-CLI Commands for Universal Replace Engine
 *
 * Provides command-line interface for search/replace operations.
 *
 * @package UniversalReplaceEngine
 * @since 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP-CLI commands for Universal Replace Engine.
 */
class URE_CLI {

	/**
	 * Search/Replace engine instance.
	 *
	 * @var URE_Search_Replace
	 */
	private $search_replace;

	/**
	 * Logger instance.
	 *
	 * @var URE_Logger
	 */
	private $logger;

	/**
	 * Database Manager instance.
	 *
	 * @var URE_Database_Manager
	 */
	private $database_manager;

	/**
	 * Backup Manager instance.
	 *
	 * @var URE_Backup_Manager
	 */
	private $backup_manager;

	/**
	 * Profiles Manager instance.
	 *
	 * @var URE_Profiles
	 */
	private $profiles;

	/**
	 * Settings Manager instance.
	 *
	 * @var URE_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$plugin                 = URE_Plugin::get_instance();
		$this->search_replace   = $plugin->search_replace;
		$this->logger           = $plugin->logger;
		$this->database_manager = $plugin->database_manager;
		$this->backup_manager   = $plugin->backup_manager;
		$this->profiles         = new URE_Profiles();
		$this->settings         = new URE_Settings();
	}

	/**
	 * Search for text in post content.
	 *
	 * ## OPTIONS
	 *
	 * <search>
	 * : The text to search for.
	 *
	 * [--post-type=<post-type>]
	 * : Post type to search in. Default: post,page
	 *
	 * [--case-sensitive]
	 * : Enable case-sensitive search.
	 *
	 * [--regex]
	 * : Enable regex mode (Pro only).
	 *
	 * [--limit=<limit>]
	 * : Maximum number of results to show. Default: 20
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure search "old-domain.com"
	 *     wp ure search "old-domain.com" --post-type=post,page,product
	 *     wp ure search "\d{3}-\d{3}-\d{4}" --regex
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function search( $args, $assoc_args ) {
		list( $search ) = $args;

		$post_types      = isset( $assoc_args['post-type'] ) ? explode( ',', $assoc_args['post-type'] ) : array( 'post', 'page' );
		$case_sensitive  = isset( $assoc_args['case-sensitive'] );
		$regex_mode      = isset( $assoc_args['regex'] );
		$limit           = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : 20;

		// Check Pro status for regex.
		$is_pro = apply_filters( 'ure_is_pro', false );
		if ( $regex_mode && ! $is_pro ) {
			WP_CLI::error( 'Regex mode requires Pro version.' );
			return;
		}

		WP_CLI::log( sprintf( 'Searching for: %s', $search ) );
		WP_CLI::log( sprintf( 'Post types: %s', implode( ', ', $post_types ) ) );

		// Run preview.
		$preview = $this->search_replace->run_preview(
			$search,
			'', // No replacement for search-only.
			$post_types,
			$case_sensitive,
			$regex_mode,
			'post_content'
		);

		if ( empty( $preview['results'] ) ) {
			WP_CLI::success( 'No matches found.' );
			return;
		}

		$count = $preview['total'];
		WP_CLI::log( sprintf( 'Found %d match(es).', $count ) );
		WP_CLI::log( '' );

		// Display results.
		$displayed = 0;
		foreach ( $preview['results'] as $result ) {
			if ( $displayed >= $limit ) {
				WP_CLI::log( sprintf( '...and %d more (use --limit to see more)', $count - $limit ) );
				break;
			}

			WP_CLI::log( sprintf( 'Post ID: %d - %s', $result['post_id'], $result['post_title'] ) );
			WP_CLI::log( sprintf( 'Before: %s', $result['before'] ) );
			WP_CLI::log( '---' );

			$displayed++;
		}

		WP_CLI::success( sprintf( 'Displayed %d of %d match(es).', min( $displayed, $count ), $count ) );
	}

	/**
	 * Replace text in post content.
	 *
	 * ## OPTIONS
	 *
	 * <search>
	 * : The text to search for.
	 *
	 * <replace>
	 * : The replacement text.
	 *
	 * [--post-type=<post-type>]
	 * : Post type to search in. Default: post,page
	 *
	 * [--case-sensitive]
	 * : Enable case-sensitive search.
	 *
	 * [--regex]
	 * : Enable regex mode (Pro only).
	 *
	 * [--dry-run]
	 * : Preview changes without applying them.
	 *
	 * [--yes]
	 * : Skip confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure replace "old-domain.com" "new-domain.com" --dry-run
	 *     wp ure replace "old-domain.com" "new-domain.com" --post-type=post,page --yes
	 *     wp ure replace "http://" "https://" --case-sensitive
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function replace( $args, $assoc_args ) {
		list( $search, $replace ) = $args;

		$post_types      = isset( $assoc_args['post-type'] ) ? explode( ',', $assoc_args['post-type'] ) : array( 'post', 'page' );
		$case_sensitive  = isset( $assoc_args['case-sensitive'] );
		$regex_mode      = isset( $assoc_args['regex'] );
		$dry_run         = isset( $assoc_args['dry-run'] );
		$skip_confirm    = isset( $assoc_args['yes'] );

		// Check Pro status for regex.
		$is_pro = apply_filters( 'ure_is_pro', false );
		if ( $regex_mode && ! $is_pro ) {
			WP_CLI::error( 'Regex mode requires Pro version.' );
			return;
		}

		WP_CLI::log( sprintf( 'Search: %s', $search ) );
		WP_CLI::log( sprintf( 'Replace: %s', $replace ) );
		WP_CLI::log( sprintf( 'Post types: %s', implode( ', ', $post_types ) ) );

		// Run preview first.
		$preview = $this->search_replace->run_preview(
			$search,
			$replace,
			$post_types,
			$case_sensitive,
			$regex_mode,
			'post_content'
		);

		if ( empty( $preview['results'] ) ) {
			WP_CLI::success( 'No matches found. Nothing to replace.' );
			return;
		}

		$count = $preview['total'];
		WP_CLI::log( sprintf( 'Found %d match(es).', $count ) );

		// Show preview samples.
		WP_CLI::log( '' );
		WP_CLI::log( 'Preview (first 5):' );
		$preview_count = 0;
		foreach ( $preview['results'] as $result ) {
			if ( $preview_count >= 5 ) {
				break;
			}
			WP_CLI::log( sprintf( 'Post ID %d: "%s" → "%s"', $result['post_id'], $result['before'], $result['after'] ) );
			$preview_count++;
		}

		if ( $dry_run ) {
			WP_CLI::success( sprintf( 'Dry run complete. %d post(s) would be updated.', $count ) );
			return;
		}

		// Confirm before applying.
		if ( ! $skip_confirm ) {
			WP_CLI::confirm( sprintf( 'Apply changes to %d post(s)?', $count ) );
		}

		// Apply changes.
		WP_CLI::log( 'Applying changes...' );
		$applied = $this->search_replace->apply_replacements(
			$search,
			$replace,
			$post_types,
			$case_sensitive,
			$regex_mode,
			get_current_user_id(),
			'post_content'
		);

		WP_CLI::success( sprintf( 'Successfully updated %d post(s).', $applied ) );
	}

	/**
	 * Create a backup of database tables.
	 *
	 * ## OPTIONS
	 *
	 * [--tables=<tables>]
	 * : Comma-separated list of table names. Default: all WordPress core tables
	 *
	 * [--comment=<comment>]
	 * : Optional comment for the backup.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure backup
	 *     wp ure backup --tables=wp_posts,wp_postmeta
	 *     wp ure backup --comment="Before domain migration"
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function backup( $args, $assoc_args ) {
		global $wpdb;

		$tables = isset( $assoc_args['tables'] ) ? explode( ',', $assoc_args['tables'] ) : array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->options,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
		);

		$comment = isset( $assoc_args['comment'] ) ? $assoc_args['comment'] : '';

		WP_CLI::log( sprintf( 'Creating backup of %d table(s)...', count( $tables ) ) );

		$result = $this->backup_manager->create_backup( $tables, $comment );

		if ( isset( $result['success'] ) && ! $result['success'] ) {
			WP_CLI::error( $result['message'] );
			return;
		}

		WP_CLI::success( sprintf( 'Backup created: %s', $result['file'] ) );
		WP_CLI::log( sprintf( 'Size: %.2f MB', $result['file_size'] / 1024 / 1024 ) );
		WP_CLI::log( sprintf( 'Tables: %d', $result['table_count'] ) );
	}

	/**
	 * List all available backups.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure backup list
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function backup_list( $args, $assoc_args ) {
		$backups = $this->backup_manager->get_backups();

		if ( empty( $backups ) ) {
			WP_CLI::log( 'No backups found.' );
			return;
		}

		$table = array();
		foreach ( $backups as $backup ) {
			$table[] = array(
				'Filename'  => $backup['filename'],
				'Date'      => $backup['created_gmt'],
				'Size'      => size_format( $backup['file_size'] ),
				'Tables'    => is_array( $backup['tables'] ) ? implode( ', ', $backup['tables'] ) : $backup['tables'],
				'User'      => isset( $backup['user_name'] ) ? $backup['user_name'] : 'Unknown',
			);
		}

		WP_CLI\Utils\format_items( 'table', $table, array( 'Filename', 'Date', 'Size', 'Tables', 'User' ) );
	}

	/**
	 * Restore a database backup.
	 *
	 * ## OPTIONS
	 *
	 * <filename>
	 * : The backup filename to restore.
	 *
	 * [--yes]
	 * : Skip confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure restore backup_2024-01-15_123456.sql
	 *     wp ure restore backup_2024-01-15_123456.sql --yes
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function restore( $args, $assoc_args ) {
		list( $filename ) = $args;
		$skip_confirm = isset( $assoc_args['yes'] );

		// Confirm before restoring.
		if ( ! $skip_confirm ) {
			WP_CLI::warning( 'This will overwrite current database data!' );
			WP_CLI::confirm( sprintf( 'Restore backup: %s?', $filename ) );
		}

		WP_CLI::log( 'Restoring backup...' );

		$result = $this->backup_manager->restore_backup( $filename );

		if ( isset( $result['success'] ) && ! $result['success'] ) {
			WP_CLI::error( $result['message'] );
			return;
		}

		WP_CLI::success( 'Backup restored successfully.' );
	}

	/**
	 * Manage saved profiles.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : Action to perform: list, load, delete
	 *
	 * [<name>]
	 * : Profile name (required for load and delete).
	 *
	 * [--user-id=<user-id>]
	 * : User ID for profile management. Default: 1
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure profile list
	 *     wp ure profile load "Domain Migration"
	 *     wp ure profile delete "Old Profile"
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function profile( $args, $assoc_args ) {
		$action  = isset( $args[0] ) ? $args[0] : '';
		$name    = isset( $args[1] ) ? $args[1] : '';
		$user_id = isset( $assoc_args['user-id'] ) ? intval( $assoc_args['user-id'] ) : 1;

		switch ( $action ) {
			case 'list':
				$profiles = URE_Profiles::get_all( $user_id );
				if ( empty( $profiles ) ) {
					WP_CLI::log( 'No profiles found.' );
					return;
				}

				$table = array();
				foreach ( $profiles as $profile_name => $profile ) {
					$table[] = array(
						'Name'        => $profile_name,
						'Search'      => substr( $profile['search'], 0, 30 ),
						'Replace'     => substr( $profile['replace'], 0, 30 ),
						'Post Types'  => implode( ',', $profile['post_types'] ),
						'Created'     => $profile['created_at'],
					);
				}

				WP_CLI\Utils\format_items( 'table', $table, array( 'Name', 'Search', 'Replace', 'Post Types', 'Created' ) );
				break;

			case 'load':
				if ( empty( $name ) ) {
					WP_CLI::error( 'Profile name is required.' );
					return;
				}

				$profile = URE_Profiles::get( $name, $user_id );
				if ( ! $profile ) {
					WP_CLI::error( sprintf( 'Profile "%s" not found.', $name ) );
					return;
				}

				WP_CLI::log( sprintf( 'Profile: %s', $name ) );
				WP_CLI::log( sprintf( 'Search: %s', $profile['search'] ) );
				WP_CLI::log( sprintf( 'Replace: %s', $profile['replace'] ) );
				WP_CLI::log( sprintf( 'Post Types: %s', implode( ', ', $profile['post_types'] ) ) );
				WP_CLI::log( sprintf( 'Case Sensitive: %s', $profile['case_sensitive'] ? 'Yes' : 'No' ) );
				WP_CLI::log( sprintf( 'Regex Mode: %s', $profile['regex_mode'] ? 'Yes' : 'No' ) );
				break;

			case 'delete':
				if ( empty( $name ) ) {
					WP_CLI::error( 'Profile name is required.' );
					return;
				}

				$result = URE_Profiles::delete( $name, $user_id );
				if ( $result ) {
					WP_CLI::success( sprintf( 'Profile "%s" deleted.', $name ) );
				} else {
					WP_CLI::error( sprintf( 'Failed to delete profile "%s".', $name ) );
				}
				break;

			default:
				WP_CLI::error( sprintf( 'Unknown action: %s. Use: list, load, or delete.', $action ) );
		}
	}

	/**
	 * View or update plugin settings.
	 *
	 * ## OPTIONS
	 *
	 * [<setting>]
	 * : Setting name to view or update.
	 *
	 * [<value>]
	 * : New value for the setting.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure settings
	 *     wp ure settings content_batch_size
	 *     wp ure settings content_batch_size 50
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function settings( $args, $assoc_args ) {
		$setting = isset( $args[0] ) ? $args[0] : '';
		$value   = isset( $args[1] ) ? $args[1] : null;

		$all_settings = get_option( 'ure_settings', array() );

		// List all settings.
		if ( empty( $setting ) ) {
			WP_CLI::log( 'Current Settings:' );
			WP_CLI::log( '' );
			foreach ( $all_settings as $key => $val ) {
				WP_CLI::log( sprintf( '%s: %s', $key, is_array( $val ) ? json_encode( $val ) : $val ) );
			}
			return;
		}

		// View specific setting.
		if ( null === $value ) {
			if ( isset( $all_settings[ $setting ] ) ) {
				WP_CLI::log( sprintf( '%s: %s', $setting, $all_settings[ $setting ] ) );
			} else {
				WP_CLI::error( sprintf( 'Setting "%s" not found.', $setting ) );
			}
			return;
		}

		// Update setting.
		$all_settings[ $setting ] = $value;
		update_option( 'ure_settings', $all_settings );
		WP_CLI::success( sprintf( 'Updated %s to: %s', $setting, $value ) );
	}

	/**
	 * View operation history.
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<limit>]
	 * : Number of history entries to show. Default: 10
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure history
	 *     wp ure history --limit=20
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function history( $args, $assoc_args ) {
		$limit = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : 10;

		$logs = $this->logger->get_logs( $limit );

		if ( empty( $logs ) ) {
			WP_CLI::log( 'No operation history found.' );
			return;
		}

		$table = array();
		foreach ( $logs as $log ) {
			$table[] = array(
				'ID'        => $log['id'],
				'Date'      => $log['timestamp'],
				'User ID'   => $log['user_id'],
				'Summary'   => substr( $log['summary'], 0, 60 ),
			);
		}

		WP_CLI\Utils\format_items( 'table', $table, array( 'ID', 'Date', 'User ID', 'Summary' ) );
	}

	/**
	 * Rollback an operation.
	 *
	 * ## OPTIONS
	 *
	 * <log-id>
	 * : The log ID to rollback.
	 *
	 * [--yes]
	 * : Skip confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ure rollback 123
	 *     wp ure rollback 123 --yes
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function rollback( $args, $assoc_args ) {
		list( $log_id ) = $args;
		$skip_confirm = isset( $assoc_args['yes'] );

		$log = $this->logger->get_log( $log_id );
		if ( ! $log ) {
			WP_CLI::error( sprintf( 'Log ID %d not found.', $log_id ) );
			return;
		}

		WP_CLI::log( sprintf( 'Log ID: %d', $log_id ) );
		WP_CLI::log( sprintf( 'Summary: %s', $log['summary'] ) );
		WP_CLI::log( sprintf( 'Date: %s', $log['timestamp'] ) );

		// Confirm before rolling back.
		if ( ! $skip_confirm ) {
			WP_CLI::confirm( 'Rollback this operation?' );
		}

		WP_CLI::log( 'Rolling back...' );

		$result = $this->logger->rollback( $log_id );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
			return;
		}

		WP_CLI::success( sprintf( 'Successfully rolled back %d post(s).', $result ) );
	}
}
