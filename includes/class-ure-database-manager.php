<?php
/**
 * Database Manager Class
 *
 * Handles database-level operations, table management, and backup/restore.
 * Provides enterprise-grade functionality for all WordPress tables.
 *
 * @package UniversalReplaceEngine
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Manager class for advanced database operations.
 */
class URE_Database_Manager {

	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Logger instance.
	 *
	 * @var URE_Logger
	 */
	private $logger;

	/**
	 * Page size for batch processing.
	 *
	 * @var int
	 */
	private $page_size = 5000;

	/**
	 * Protected tables that should be handled with care.
	 *
	 * @var array
	 */
	private $protected_tables = array( 'users', 'usermeta', 'options' );

	/**
	 * Constructor.
	 *
	 * @param URE_Logger $logger Logger instance.
	 */
	public function __construct( $logger ) {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->logger = $logger;
	}

	/**
	 * Get all WordPress tables with metadata.
	 *
	 * @param bool $include_multisite Whether to include all multisite tables.
	 * @return array Array of table data with name, size, rows, type.
	 */
	public function get_tables( $include_multisite = false ) {
		$tables = array();

		// Get table list based on multisite status.
		if ( is_multisite() ) {
			if ( $include_multisite && is_main_site() ) {
				// Network admin on main site: get all tables.
				$table_names = $this->wpdb->get_col( 'SHOW TABLES' );
			} else {
				// Subsite or non-network: get only current site tables.
				$blog_id     = get_current_blog_id();
				$prefix      = $this->wpdb->get_blog_prefix( $blog_id );
				$table_names = $this->wpdb->get_col(
					$this->wpdb->prepare(
						'SHOW TABLES LIKE %s',
						$this->wpdb->esc_like( $prefix ) . '%'
					)
				);
			}
		} else {
			// Single site: get all tables.
			$table_names = $this->wpdb->get_col( 'SHOW TABLES' );
		}

		// Get table status (size and row count).
		$table_status = $this->wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
		$status_map   = array();

		foreach ( $table_status as $status ) {
			$status_map[ $status['Name'] ] = $status;
		}

		// Build table metadata.
		foreach ( $table_names as $table_name ) {
			$status = isset( $status_map[ $table_name ] ) ? $status_map[ $table_name ] : null;

			$tables[] = array(
				'name'      => $table_name,
				'rows'      => $status ? (int) $status['Rows'] : 0,
				'size'      => $status ? (int) $status['Data_length'] : 0,
				'size_mb'   => $status ? round( $status['Data_length'] / 1024 / 1024, 2 ) : 0,
				'type'      => $this->get_table_type( $table_name ),
				'protected' => $this->is_protected_table( $table_name ),
			);
		}

		// Sort by size (largest first).
		usort( $tables, function( $a, $b ) {
			return $b['size'] - $a['size'];
		});

		return $tables;
	}

	/**
	 * Get table type (core, plugin, theme, custom).
	 *
	 * @param string $table_name Table name.
	 * @return string Table type.
	 */
	private function get_table_type( $table_name ) {
		$prefix = $this->wpdb->prefix;

		// Core WordPress tables.
		$core_tables = array(
			$prefix . 'posts',
			$prefix . 'postmeta',
			$prefix . 'comments',
			$prefix . 'commentmeta',
			$prefix . 'users',
			$prefix . 'usermeta',
			$prefix . 'terms',
			$prefix . 'term_taxonomy',
			$prefix . 'term_relationships',
			$prefix . 'termmeta',
			$prefix . 'options',
			$prefix . 'links',
		);

		if ( in_array( $table_name, $core_tables, true ) ) {
			return 'core';
		}

		// Check for common plugin tables.
		// E-commerce plugins.
		if ( strpos( $table_name, 'woocommerce' ) !== false || strpos( $table_name, 'wc_' ) !== false ) {
			return 'woocommerce';
		}

		if ( strpos( $table_name, 'edd_' ) !== false ) {
			return 'edd';
		}

		// SEO plugins.
		if ( strpos( $table_name, 'yoast' ) !== false ) {
			return 'yoast';
		}

		if ( strpos( $table_name, 'aioseo' ) !== false ) {
			return 'aioseo';
		}

		if ( strpos( $table_name, 'rank_math' ) !== false ) {
			return 'rank-math';
		}

		// Page builders.
		if ( strpos( $table_name, 'elementor' ) !== false ) {
			return 'elementor';
		}

		if ( strpos( $table_name, 'divi' ) !== false ) {
			return 'divi';
		}

		// Forms.
		if ( strpos( $table_name, 'gf_' ) !== false || strpos( $table_name, 'gravity' ) !== false ) {
			return 'gravity-forms';
		}

		if ( strpos( $table_name, 'cf7' ) !== false || strpos( $table_name, 'contact_form' ) !== false ) {
			return 'contact-form-7';
		}

		if ( strpos( $table_name, 'frm_' ) !== false || strpos( $table_name, 'formidable' ) !== false ) {
			return 'formidable';
		}

		if ( strpos( $table_name, 'wpforms' ) !== false ) {
			return 'wpforms';
		}

		// Custom fields.
		if ( strpos( $table_name, 'acf' ) !== false ) {
			return 'acf';
		}

		// Multilingual.
		if ( strpos( $table_name, 'wpml' ) !== false || strpos( $table_name, 'icl_' ) !== false ) {
			return 'wpml';
		}

		// Security & Performance.
		if ( strpos( $table_name, 'wfls_' ) !== false || strpos( $table_name, 'wf_' ) !== false || strpos( $table_name, 'wordfence' ) !== false ) {
			return 'wordfence';
		}

		if ( strpos( $table_name, 'wpr_' ) !== false || strpos( $table_name, 'rocket' ) !== false ) {
			return 'wp-rocket';
		}

		if ( strpos( $table_name, 'akismet' ) !== false ) {
			return 'akismet';
		}

		// Membership & LMS.
		if ( strpos( $table_name, 'mepr_' ) !== false || strpos( $table_name, 'memberpress' ) !== false ) {
			return 'memberpress';
		}

		if ( strpos( $table_name, 'learndash' ) !== false || strpos( $table_name, 'ld_' ) !== false ) {
			return 'learndash';
		}

		if ( strpos( $table_name, 'lifterlms' ) !== false || strpos( $table_name, 'liftlms' ) !== false ) {
			return 'lifterlms';
		}

		// Community.
		if ( strpos( $table_name, 'bbpress' ) !== false || strpos( $table_name, 'bb_' ) !== false ) {
			return 'bbpress';
		}

		if ( strpos( $table_name, 'bp_' ) !== false || strpos( $table_name, 'buddypress' ) !== false ) {
			return 'buddypress';
		}

		// Utilities.
		if ( strpos( $table_name, 'jetpack' ) !== false ) {
			return 'jetpack';
		}

		if ( strpos( $table_name, 'pmxi_' ) !== false || strpos( $table_name, 'pmxe_' ) !== false ) {
			return 'wp-all-import';
		}

		if ( strpos( $table_name, 'redirection' ) !== false ) {
			return 'redirection';
		}

		if ( strpos( $table_name, 'wpstg' ) !== false ) {
			return 'wp-staging';
		}

		if ( strpos( $table_name, 'duplicator' ) !== false ) {
			return 'duplicator';
		}

		// Generic plugin table (has WordPress prefix but not identified above).
		if ( strpos( $table_name, $prefix ) === 0 ) {
			return 'plugin';
		}

		return 'custom';
	}

	/**
	 * Check if table is protected and requires special handling.
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	private function is_protected_table( $table_name ) {
		$prefix = $this->wpdb->prefix;

		foreach ( $this->protected_tables as $protected ) {
			if ( $table_name === $prefix . $protected ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get columns for a table.
	 *
	 * @param string $table_name Table name.
	 * @return array Array with 'primary_key' and 'columns' keys.
	 */
	public function get_table_columns( $table_name ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$fields = $this->wpdb->get_results( 'DESCRIBE ' . esc_sql( $table_name ) );

		$primary_key = null;
		$columns     = array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column->Field;
				if ( 'PRI' === $column->Key ) {
					$primary_key = $column->Field;
				}
			}
		}

		return array(
			'primary_key' => $primary_key,
			'columns'     => $columns,
		);
	}

	/**
	 * Search and replace in a specific table.
	 *
	 * @param string $table_name    Table name.
	 * @param string $search        Search term.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode    Regex mode.
	 * @param bool   $dry_run       Dry run (preview only).
	 * @param bool   $skip_guids    Skip GUID columns.
	 * @return array Results with changes, updates, errors.
	 */
	public function table_search_replace( $table_name, $search, $replace, $case_sensitive = false, $regex_mode = false, $dry_run = true, $skip_guids = true ) {
		// Validate table exists.
		$tables = $this->wpdb->get_col( 'SHOW TABLES' );
		if ( ! in_array( $table_name, $tables, true ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Table does not exist.', 'universal-replace-engine' ),
			);
		}

		// Get table structure.
		$table_info  = $this->get_table_columns( $table_name );
		$primary_key = $table_info['primary_key'];
		$columns     = $table_info['columns'];

		if ( null === $primary_key ) {
			return array(
				'success' => false,
				'error'   => __( 'Table has no primary key.', 'universal-replace-engine' ),
			);
		}

		$results = array(
			'table'       => $table_name,
			'rows'        => 0,
			'changes'     => 0,
			'updates'     => 0,
			'errors'      => array(),
			'previews'    => array(),
			'time_start'  => microtime( true ),
		);

		// Process in batches.
		$page  = 0;
		$total_rows = $this->wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$this->wpdb->prepare( 'SELECT COUNT(*) FROM `%s`', $table_name )
		);
		$pages = ceil( $total_rows / $this->page_size );

		while ( $page < $pages ) {
			$offset = $page * $this->page_size;

			// Get batch of rows.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT * FROM `{$table_name}` LIMIT %d OFFSET %d",
					$this->page_size,
					$offset
				),
				ARRAY_A
			);

			foreach ( $rows as $row ) {
				$results['rows']++;
				$update_data = array();
				$where_data  = array( $primary_key => $row[ $primary_key ] );

				foreach ( $columns as $column ) {
					// Skip primary key.
					if ( $column === $primary_key ) {
						continue;
					}

					// Skip GUIDs if requested (WordPress best practice).
					if ( $skip_guids && 'guid' === strtolower( $column ) ) {
						continue;
					}

					$original_value = $row[ $column ];

					// Skip if null or empty.
					if ( null === $original_value || '' === $original_value ) {
						continue;
					}

					// Perform replacement.
					$new_value = $this->perform_replacement(
						$original_value,
						$search,
						$replace,
						$case_sensitive,
						$regex_mode
					);

					// Check if changed.
					if ( $original_value !== $new_value ) {
						$results['changes']++;
						$update_data[ $column ] = $new_value;

						// Store preview (limited).
						if ( count( $results['previews'] ) < 20 ) {
							$results['previews'][] = array(
								'row'      => $results['rows'],
								'column'   => $column,
								'pk_value' => $row[ $primary_key ],
								'before'   => $this->truncate_for_preview( $original_value ),
								'after'    => $this->truncate_for_preview( $new_value ),
							);
						}
					}
				}

				// Update row if changes found and not dry run.
				if ( ! empty( $update_data ) && ! $dry_run ) {
					$updated = $this->wpdb->update(
						$table_name,
						$update_data,
						$where_data
					);

					if ( false !== $updated ) {
						$results['updates']++;
					} else {
						$results['errors'][] = sprintf(
							/* translators: %d: row number */
							__( 'Error updating row %d', 'universal-replace-engine' ),
							$results['rows']
						);
					}
				}
			}

			$page++;
		}

		$results['time_end']     = microtime( true );
		$results['time_elapsed'] = round( $results['time_end'] - $results['time_start'], 2 );

		return $results;
	}

	/**
	 * Perform replacement with serialization support.
	 *
	 * @param mixed  $value         Value to search in.
	 * @param string $search        Search term.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Regex mode.
	 * @return mixed Modified value.
	 */
	private function perform_replacement( $value, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		// Check if serialized.
		if ( is_serialized( $value ) ) {
			$unserialized = @unserialize( $value );
			if ( false !== $unserialized ) {
				$replaced = $this->recursive_replace( $unserialized, $search, $replace, $case_sensitive, $regex_mode );
				return maybe_serialize( $replaced );
			}
		}

		// Handle JSON.
		if ( $this->is_json( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( null !== $decoded ) {
				$replaced = $this->recursive_replace( $decoded, $search, $replace, $case_sensitive, $regex_mode );
				return wp_json_encode( $replaced );
			}
		}

		// Regular string replacement.
		return $this->string_replace( $value, $search, $replace, $case_sensitive, $regex_mode );
	}

	/**
	 * Recursively replace in arrays/objects.
	 *
	 * @param mixed  $data          Data to process.
	 * @param string $search        Search term.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Regex mode.
	 * @return mixed
	 */
	private function recursive_replace( $data, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->recursive_replace( $value, $search, $replace, $case_sensitive, $regex_mode );
			}
		} elseif ( is_object( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data->$key = $this->recursive_replace( $value, $search, $replace, $case_sensitive, $regex_mode );
			}
		} elseif ( is_string( $data ) ) {
			$data = $this->string_replace( $data, $search, $replace, $case_sensitive, $regex_mode );
		}

		return $data;
	}

	/**
	 * String replacement.
	 *
	 * @param string $subject       Subject string.
	 * @param string $search        Search term.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Regex mode.
	 * @return string
	 */
	private function string_replace( $subject, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		if ( ! is_string( $subject ) ) {
			return $subject;
		}

		if ( $regex_mode ) {
			$pattern = $this->prepare_regex_pattern( $search, $case_sensitive );
			return @preg_replace( $pattern, $replace, $subject );
		}

		if ( $case_sensitive ) {
			return str_replace( $search, $replace, $subject );
		} else {
			return str_ireplace( $search, $replace, $subject );
		}
	}

	/**
	 * Prepare regex pattern.
	 *
	 * @param string $pattern        Pattern.
	 * @param bool   $case_sensitive Case sensitive.
	 * @return string
	 */
	private function prepare_regex_pattern( $pattern, $case_sensitive = false ) {
		// Check if pattern has delimiters.
		if ( preg_match( '/^[\/\#\~\@\!\%\`].+[\/\#\~\@\!\%\`][imsxu]*$/', $pattern ) ) {
			return $pattern;
		}

		// Add delimiters.
		$delimiter = '/';
		$flags     = $case_sensitive ? '' : 'i';
		$pattern   = str_replace( $delimiter, '\\' . $delimiter, $pattern );

		return $delimiter . $pattern . $delimiter . $flags;
	}

	/**
	 * Check if string is JSON.
	 *
	 * @param string $string String to check.
	 * @return bool
	 */
	private function is_json( $string ) {
		if ( ! is_string( $string ) ) {
			return false;
		}

		json_decode( $string );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Truncate string for preview.
	 *
	 * @param string $string String to truncate.
	 * @param int    $length Length limit.
	 * @return string
	 */
	private function truncate_for_preview( $string, $length = 100 ) {
		$string = (string) $string;

		if ( strlen( $string ) > $length ) {
			return substr( $string, 0, $length ) . '...';
		}

		return $string;
	}

	/**
	 * Special handling for wp_options table.
	 *
	 * @param string $search        Search term.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Regex mode.
	 * @param bool   $dry_run       Dry run.
	 * @return array Results.
	 */
	public function options_table_replace( $search, $replace, $case_sensitive = false, $regex_mode = false, $dry_run = true ) {
		$options_table = $this->wpdb->options;

		// Protected options that should never be changed.
		$protected_options = array(
			'ure_logs',
			'cron',
			'_transient',
			'_site_transient',
		);

		$results = array(
			'changes'  => 0,
			'updates'  => 0,
			'errors'   => array(),
			'previews' => array(),
			'deferred' => array(), // Options to update at the end.
		);

		// Get all options (in batches).
		$page       = 0;
		$batch_size = 1000;

		do {
			$offset = $page * $batch_size;

			$options = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT option_id, option_name, option_value
					FROM {$options_table}
					LIMIT %d OFFSET %d",
					$batch_size,
					$offset
				),
				ARRAY_A
			);

			foreach ( $options as $option ) {
				$option_name  = $option['option_name'];
				$option_value = $option['option_value'];

				// Skip protected options.
				$skip = false;
				foreach ( $protected_options as $protected ) {
					if ( strpos( $option_name, $protected ) !== false ) {
						$skip = true;
						break;
					}
				}

				if ( $skip ) {
					continue;
				}

				// Defer critical options to the end.
				if ( 'siteurl' === $option_name || 'home' === $option_name ) {
					$new_value = $this->perform_replacement(
						$option_value,
						$search,
						$replace,
						$case_sensitive,
						$regex_mode
					);

					if ( $option_value !== $new_value ) {
						$results['deferred'][] = array(
							'option_name'  => $option_name,
							'option_value' => $new_value,
							'old_value'    => $option_value,
						);
					}
					continue;
				}

				// Perform replacement.
				$new_value = $this->perform_replacement(
					$option_value,
					$search,
					$replace,
					$case_sensitive,
					$regex_mode
				);

				// Check if changed.
				if ( $option_value !== $new_value ) {
					$results['changes']++;

					// Store preview.
					if ( count( $results['previews'] ) < 20 ) {
						$results['previews'][] = array(
							'option_name' => $option_name,
							'before'      => $this->truncate_for_preview( $option_value ),
							'after'       => $this->truncate_for_preview( $new_value ),
						);
					}

					// Update if not dry run.
					if ( ! $dry_run ) {
						$updated = update_option( $option_name, $new_value );
						if ( $updated ) {
							$results['updates']++;
						}
					}
				}
			}

			$page++;
		} while ( count( $options ) === $batch_size );

		return $results;
	}

	/**
	 * Update deferred options (siteurl, home) at the end.
	 *
	 * @param array $deferred_options Deferred options from options_table_replace.
	 * @return bool Success.
	 */
	public function update_deferred_options( $deferred_options ) {
		if ( empty( $deferred_options ) ) {
			return true;
		}

		foreach ( $deferred_options as $option ) {
			update_option( $option['option_name'], $option['option_value'] );
		}

		return true;
	}
}
