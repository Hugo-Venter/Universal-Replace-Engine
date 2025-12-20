<?php
/**
 * Logger Class
 *
 * Handles logging of operations and rollback functionality.
 * Manages the custom database table for operation history.
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class for managing operation history and rollback.
 */
class URE_Logger {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ure_logs';

	/**
	 * Maximum number of logs to keep in FREE version.
	 *
	 * @var int
	 */
	const FREE_LOG_LIMIT = 5;

	/**
	 * Get the full table name with WordPress prefix.
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Log an operation.
	 *
	 * @param int    $user_id User ID who performed the action.
	 * @param string $summary Human-readable summary.
	 * @param array  $details Detailed data (will be JSON encoded).
	 * @return int|false Log ID on success, false on failure.
	 */
	public function log_operation( $user_id, $summary, $details = array() ) {
		// Check if logging is enabled.
		if ( ! URE_Settings::get( 'enable_logging', true ) ) {
			return false;
		}

		global $wpdb;

		$table = $this->get_table_name();

		$inserted = $wpdb->insert(
			$table,
			array(
				'timestamp'    => current_time( 'mysql' ),
				'user_id'      => absint( $user_id ),
				'summary'      => sanitize_text_field( $summary ),
				'details_json' => wp_json_encode( $details ),
			),
			array( '%s', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		$log_id = $wpdb->insert_id;

		// Clean up old logs (keep only last FREE_LOG_LIMIT).
		$this->cleanup_old_logs();

		return $log_id;
	}

	/**
	 * Get all logs (most recent first).
	 *
	 * @param int $limit Number of logs to retrieve.
	 * @return array
	 */
	public function get_logs( $limit = null ) {
		global $wpdb;

		// Allow Pro version to override limit.
		if ( null === $limit ) {
			/**
			 * Filter the history log limit.
			 * Pro version can show more history.
			 *
			 * @since 1.0.0
			 * @param int $limit History limit.
			 */
			$limit = apply_filters( 'ure_history_limit', URE_Settings::get( 'history_limit', self::FREE_LOG_LIMIT ) );
		}

		$table = $this->get_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY timestamp DESC LIMIT %d",
				absint( $limit )
			),
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		// Decode JSON details.
		foreach ( $results as &$log ) {
			$log['details'] = json_decode( $log['details_json'], true );

			// Add user display name.
			$user = get_userdata( $log['user_id'] );
			$log['user_name'] = $user ? $user->display_name : __( 'Unknown', 'universal-replace-engine' );
		}

		return $results;
	}

	/**
	 * Get a single log by ID.
	 *
	 * @param int $log_id Log ID.
	 * @return array|null
	 */
	public function get_log( $log_id ) {
		global $wpdb;

		$table = $this->get_table_name();

		$log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				absint( $log_id )
			),
			ARRAY_A
		);

		if ( ! $log ) {
			return null;
		}

		// Decode JSON details.
		$log['details'] = json_decode( $log['details_json'], true );

		// Add user display name.
		$user = get_userdata( $log['user_id'] );
		$log['user_name'] = $user ? $user->display_name : __( 'Unknown', 'universal-replace-engine' );

		return $log;
	}

	/**
	 * Perform rollback/undo of an operation.
	 *
	 * @param int $log_id  Log ID to rollback.
	 * @param int $user_id User ID performing the rollback.
	 * @return array Result with success status and message.
	 */
	public function rollback_operation( $log_id, $user_id ) {
		$log = $this->get_log( $log_id );

		if ( ! $log ) {
			return array(
				'success' => false,
				'message' => __( 'Log entry not found.', 'universal-replace-engine' ),
			);
		}

		$details = $log['details'];

		if ( empty( $details['operation_data'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'No rollback data available.', 'universal-replace-engine' ),
			);
		}

		$restored_count = 0;
		$rollback_data = array();

		// Restore each post.
		foreach ( $details['operation_data'] as $item ) {
			$post_id = $item['post_id'];
			$old_content = $item['old_content'];
			$location = isset( $item['location'] ) ? $item['location'] : 'post_content';
			$meta_key = isset( $item['meta_key'] ) ? $item['meta_key'] : '';

			$updated = false;

			// Handle different location types.
			if ( 'post_content' === $location ) {
				// Update post with old content.
				$result = wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $old_content,
					),
					true
				);
				$updated = ! is_wp_error( $result );
			} elseif ( 'postmeta' === $location && ! empty( $meta_key ) ) {
				// Restore postmeta.
				$result = update_post_meta( $post_id, $meta_key, $old_content );
				$updated = ( false !== $result );
			} elseif ( 'elementor' === $location && ! empty( $meta_key ) ) {
				// Restore Elementor data.
				$result = update_post_meta( $post_id, $meta_key, $old_content );
				$updated = ( false !== $result );

				// Clear Elementor cache.
				if ( class_exists( '\Elementor\Plugin' ) ) {
					\Elementor\Plugin::$instance->files_manager->clear_cache();
				}
			}

			if ( $updated ) {
				$restored_count++;

				// Store rollback info.
				$rollback_data[] = array(
					'post_id'          => $post_id,
					'post_type'        => $item['post_type'],
					'post_title'       => $item['post_title'],
					'restored_content' => $old_content,
					'location'         => $location,
					'meta_key'         => $meta_key,
				);
			}
		}

		// Log the rollback operation.
		if ( $restored_count > 0 ) {
			$summary = sprintf(
				/* translators: 1: number of posts, 2: original log ID */
				__( 'Rolled back %1$d post(s) from operation #%2$d.', 'universal-replace-engine' ),
				$restored_count,
				$log_id
			);

			$rollback_log_data = array(
				'operation_type'  => 'rollback',
				'original_log_id' => $log_id,
				'restored_count'  => $restored_count,
				'rollback_data'   => $rollback_data,
			);

			$this->log_operation( $user_id, $summary, $rollback_log_data );
		}

		return array(
			'success'        => true,
			'restored_count' => $restored_count,
			'message'        => sprintf(
				/* translators: %d: number of posts */
				__( 'Successfully rolled back %d post(s).', 'universal-replace-engine' ),
				$restored_count
			),
		);
	}

	/**
	 * Delete old logs, keeping only the most recent limit.
	 */
	private function cleanup_old_logs() {
		global $wpdb;

		$table = $this->get_table_name();

		// Get limit (allow Pro version to override).
		$log_limit = apply_filters( 'ure_history_limit', URE_Settings::get( 'history_limit', self::FREE_LOG_LIMIT ) );

		// Get the ID of the nth most recent log.
		$keep_from_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} ORDER BY timestamp DESC LIMIT 1 OFFSET %d",
				$log_limit - 1
			)
		);

		if ( $keep_from_id ) {
			// Delete all logs older than the nth most recent.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table} WHERE id < %d",
					$keep_from_id
				)
			);
		}
	}

	/**
	 * Delete a specific log entry.
	 *
	 * @param int $log_id Log ID to delete.
	 * @return bool
	 */
	public function delete_log( $log_id ) {
		global $wpdb;

		$table = $this->get_table_name();

		$deleted = $wpdb->delete(
			$table,
			array( 'id' => absint( $log_id ) ),
			array( '%d' )
		);

		return false !== $deleted;
	}

	/**
	 * Clear all logs.
	 *
	 * @return bool
	 */
	public function clear_all_logs() {
		global $wpdb;

		$table = $this->get_table_name();

		$result = $wpdb->query( "TRUNCATE TABLE {$table}" );

		return false !== $result;
	}

	/**
	 * Get log count.
	 *
	 * @return int
	 */
	public function get_log_count() {
		global $wpdb;

		$table = $this->get_table_name();

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}
}
