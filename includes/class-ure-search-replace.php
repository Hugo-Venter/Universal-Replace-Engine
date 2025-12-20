<?php
/**
 * Search and Replace Engine
 *
 * Core functionality for searching and replacing content across WordPress.
 * Handles serialized data safely and provides preview functionality.
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search and Replace Engine class.
 */
class URE_Search_Replace {

	/**
	 * Logger instance.
	 *
	 * @var URE_Logger
	 */
	private $logger;

	/**
	 * Maximum preview results in free version.
	 *
	 * @var int
	 */
	const FREE_PREVIEW_LIMIT = 20;

	/**
	 * Snippet context length (characters before/after match).
	 *
	 * @var int
	 */
	const SNIPPET_CONTEXT = 50;

	/**
	 * Constructor.
	 *
	 * @param URE_Logger $logger Logger instance.
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Run a preview search.
	 *
	 * @param string $search        The search term.
	 * @param string $replace       The replacement term (optional).
	 * @param array  $post_types    Post types to search.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode    Regex mode (future use).
	 * @param string $scope         Scope: 'post_content', 'postmeta', 'elementor', or 'all'.
	 * @return array Preview results.
	 */
	public function run_preview( $search, $replace = '', $post_types = array( 'post', 'page' ), $case_sensitive = false, $regex_mode = false, $scope = 'post_content' ) {
		$results = array();
		$total_matches = 0;

		// Sanitize inputs.
		$search = (string) $search;
		$replace = (string) $replace;
		$post_types = array_map( 'sanitize_key', $post_types );

		// Validate post types.
		$post_types = $this->validate_post_types( $post_types );
		if ( empty( $post_types ) ) {
			return array(
				'results' => array(),
				'total'   => 0,
				'limited' => false,
				'error'   => __( 'No valid post types selected.', 'universal-replace-engine' ),
			);
		}

		if ( empty( $search ) ) {
			return array(
				'results' => array(),
				'total' => 0,
				'limited' => false,
				'error' => '',
			);
		}

		// Validate regex pattern if regex mode is enabled.
		if ( $regex_mode ) {
			$regex_validation = $this->validate_regex( $search );
			if ( ! $regex_validation['valid'] ) {
				return array(
					'results' => array(),
					'total' => 0,
					'limited' => false,
					'error' => $regex_validation['error'],
				);
			}
		}

		/**
		 * Filter the preview limit.
		 * Pro version can return unlimited results.
		 *
		 * @since 1.0.0
		 * @param int $limit Preview result limit.
		 */
		$preview_limit = apply_filters( 'ure_preview_limit', URE_Settings::get( 'max_preview_results', self::FREE_PREVIEW_LIMIT ) );

		// Batch processing for preview to prevent memory exhaustion.
		$batch_size = 100;
		$page = 1;

		do {
			// Query posts in batches.
			$args = array(
				'post_type'      => $post_types,
				'posts_per_page' => $batch_size,
				'paged'          => $page,
				'post_status'    => 'any',
				'fields'         => 'ids', // Only get IDs for efficiency.
				'no_found_rows'  => false,
			);

			/**
			 * Filter the query args for preview.
			 * Allows Pro version to modify the query.
			 *
			 * @since 1.0.0
			 * @param array $args WP_Query arguments.
			 */
			$args = apply_filters( 'ure_preview_query_args', $args );

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
				$post = get_post( $post_id );

				if ( ! $post ) {
					continue;
				}

				// Search based on scope.
				if ( 'post_content' === $scope || 'all' === $scope ) {
					// Search in post_content.
					$content = $post->post_content;
					$matches = $this->find_matches_in_content( $content, $search, $case_sensitive, $regex_mode );

					if ( ! empty( $matches ) ) {
						foreach ( $matches as $match ) {
							$total_matches++;

							// Only store up to preview_limit for display.
							if ( count( $results ) < $preview_limit ) {
								$match_length = strlen( $match['text'] );

								$results[] = array(
									'post_id'    => $post_id,
									'post_type'  => $post->post_type,
									'post_title' => $post->post_title,
									'location'   => 'post_content',
									'before'     => $this->get_snippet( $content, $match['position'], $match_length, false ),
									'after'      => $replace ? $this->get_snippet(
										$this->replace_in_content( $content, $search, $replace, $case_sensitive, $regex_mode ),
										$match['position'],
										$regex_mode ? strlen( $replace ) : $match_length,
										true
									) : '',
									'match_text' => $match['text'],
								);
							}
						}
					}
				}

				// Search in postmeta.
				if ( 'postmeta' === $scope || 'all' === $scope ) {
					$meta_results = $this->search_in_postmeta( $post_id, $post, $search, $replace, $case_sensitive, $regex_mode, $preview_limit - count( $results ) );
					$results = array_merge( $results, $meta_results['results'] );
					$total_matches += $meta_results['total'];
				}

				// Search in Elementor data.
				if ( 'elementor' === $scope || 'all' === $scope ) {
					$elementor_results = $this->search_in_elementor_data( $post_id, $post, $search, $replace, $case_sensitive, $regex_mode, $preview_limit - count( $results ) );
					$results = array_merge( $results, $elementor_results['results'] );
					$total_matches += $elementor_results['total'];
				}

				// Stop processing if we have enough preview results (but continue counting total).
				if ( count( $results ) >= $preview_limit ) {
					break;
				}
			}

			wp_reset_postdata();
		}

		$page++;

		} while ( $query->have_posts() && $page <= $query->max_num_pages && count( $results ) < $preview_limit );

		return array(
			'results' => $results,
			'total'   => $total_matches,
			'limited' => $total_matches > $preview_limit,
		);
	}

	/**
	 * Find matches in content.
	 *
	 * @param string $content       The content to search.
	 * @param string $search        The search term or regex pattern.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode    Use regex matching.
	 * @return array Array of matches with positions.
	 */
	private function find_matches_in_content( $content, $search, $case_sensitive = false, $regex_mode = false ) {
		$matches = array();

		if ( $regex_mode ) {
			// Use regex matching.
			$pattern = $this->prepare_regex_pattern( $search, $case_sensitive );

			// Use preg_match_all with PREG_OFFSET_CAPTURE to get positions.
			$preg_matches = array();
			if ( @preg_match_all( $pattern, $content, $preg_matches, PREG_OFFSET_CAPTURE ) ) {
				foreach ( $preg_matches[0] as $match ) {
					$matches[] = array(
						'position' => $match[1],
						'text'     => $match[0],
					);
				}
			}
		} else {
			// Use standard string matching.
			$offset = 0;

			// Handle case sensitivity.
			if ( $case_sensitive ) {
				while ( ( $pos = strpos( $content, $search, $offset ) ) !== false ) {
					$matches[] = array(
						'position' => $pos,
						'text'     => substr( $content, $pos, strlen( $search ) ),
					);
					$offset = $pos + 1;
				}
			} else {
				while ( ( $pos = stripos( $content, $search, $offset ) ) !== false ) {
					$matches[] = array(
						'position' => $pos,
						'text'     => substr( $content, $pos, strlen( $search ) ),
					);
					$offset = $pos + 1;
				}
			}
		}

		return $matches;
	}

	/**
	 * Get a snippet of text around a match.
	 *
	 * @param string $content    The full content.
	 * @param int    $position   Position of the match.
	 * @param int    $match_len  Length of the matched text.
	 * @param bool   $is_after   Whether this is the "after" snippet.
	 * @return string The snippet with highlighted match.
	 */
	private function get_snippet( $content, $position, $match_len, $is_after = false ) {
		$start = max( 0, $position - self::SNIPPET_CONTEXT );
		$end = min( strlen( $content ), $position + $match_len + self::SNIPPET_CONTEXT );

		$snippet = substr( $content, $start, $end - $start );

		// Add ellipsis if truncated.
		if ( $start > 0 ) {
			$snippet = '...' . $snippet;
		}
		if ( $end < strlen( $content ) ) {
			$snippet .= '...';
		}

		// Highlight the match.
		$relative_pos = $position - $start;
		if ( $start > 0 ) {
			$relative_pos += 3; // Account for ellipsis.
		}

		$before_match = substr( $snippet, 0, $relative_pos );
		$match = substr( $snippet, $relative_pos, $match_len );
		$after_match = substr( $snippet, $relative_pos + $match_len );

		$highlighted = esc_html( $before_match ) .
					   '<mark class="ure-highlight">' . esc_html( $match ) . '</mark>' .
					   esc_html( $after_match );

		return $highlighted;
	}

	/**
	 * Replace in content, handling serialized data safely.
	 *
	 * @param string $content       The content.
	 * @param string $search        Search term or regex pattern.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Use regex replacement.
	 * @return string Modified content.
	 */
	private function replace_in_content( $content, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		// Check if content is serialized.
		if ( $this->is_serialized( $content ) ) {
			return $this->replace_in_serialized( $content, $search, $replace, $case_sensitive, $regex_mode );
		}

		// Regex replace.
		if ( $regex_mode ) {
			$pattern = $this->prepare_regex_pattern( $search, $case_sensitive );
			return @preg_replace( $pattern, $replace, $content );
		}

		// Regular string replace.
		if ( $case_sensitive ) {
			return str_replace( $search, $replace, $content );
		} else {
			return str_ireplace( $search, $replace, $content );
		}
	}

	/**
	 * Check if a string is serialized.
	 *
	 * @param string $data The data to check.
	 * @return bool
	 */
	private function is_serialized( $data ) {
		// Trim whitespace.
		$data = trim( $data );

		// Not serialized if not a string.
		if ( ! is_string( $data ) ) {
			return false;
		}

		// Empty string is not serialized.
		if ( '' === $data ) {
			return false;
		}

		// Serialized data patterns.
		if ( preg_match( '/^(a|O|s|b|i|d):[0-9]+:/s', $data ) ) {
			return @unserialize( $data ) !== false;
		}

		return false;
	}

	/**
	 * Replace in serialized data safely.
	 *
	 * @param string $data          Serialized data.
	 * @param string $search        Search term or regex pattern.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Use regex replacement.
	 * @return string Modified serialized data.
	 */
	private function replace_in_serialized( $data, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		// Try to unserialize.
		$unserialized = @unserialize( $data );

		if ( false === $unserialized ) {
			// Fallback to regular replace if unserialize fails.
			return $this->safe_str_replace( $search, $replace, $data, $case_sensitive, $regex_mode );
		}

		// Recursively replace in unserialized data.
		$replaced = $this->recursive_replace( $unserialized, $search, $replace, $case_sensitive, $regex_mode );

		// Serialize back.
		$new_serialized = maybe_serialize( $replaced );

		return $new_serialized;
	}

	/**
	 * Recursively replace in arrays/objects.
	 *
	 * @param mixed  $data          The data to search.
	 * @param string $search        Search term or regex pattern.
	 * @param string $replace       Replacement term.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Use regex replacement.
	 * @return mixed Modified data.
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
			$data = $this->safe_str_replace( $search, $replace, $data, $case_sensitive, $regex_mode );
		}

		return $data;
	}

	/**
	 * Safe string replace (handles case sensitivity and regex).
	 *
	 * @param string $search        Search term or regex pattern.
	 * @param string $replace       Replacement term.
	 * @param string $subject       Subject string.
	 * @param bool   $case_sensitive Case sensitive.
	 * @param bool   $regex_mode    Use regex replacement.
	 * @return string
	 */
	private function safe_str_replace( $search, $replace, $subject, $case_sensitive = false, $regex_mode = false ) {
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
	 * Apply replacements to the database.
	 *
	 * @param string $search        The search term.
	 * @param string $replace       The replacement term.
	 * @param array  $post_types    Post types to modify.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode    Regex mode (future use).
	 * @param int    $user_id       User ID performing the action.
	 * @param string $scope         Scope: 'post_content', 'postmeta', 'elementor', or 'all'.
	 * @return array Results of the operation.
	 */
	public function apply_replacements( $search, $replace, $post_types = array( 'post', 'page' ), $case_sensitive = false, $regex_mode = false, $user_id = 0, $scope = 'post_content' ) {
		global $wpdb;

		$modified_posts = array();
		$operation_data = array();

		// Sanitize inputs.
		$search = (string) $search;
		$replace = (string) $replace;
		$post_types = array_map( 'sanitize_key', $post_types );

		// Validate post types.
		$post_types = $this->validate_post_types( $post_types );
		if ( empty( $post_types ) ) {
			return array(
				'success' => false,
				'message' => __( 'No valid post types selected.', 'universal-replace-engine' ),
			);
		}

		if ( empty( $search ) ) {
			return array(
				'success' => false,
				'message' => __( 'Search term cannot be empty.', 'universal-replace-engine' ),
			);
		}

		// Validate regex pattern if regex mode is enabled.
		if ( $regex_mode ) {
			$regex_validation = $this->validate_regex( $search );
			if ( ! $regex_validation['valid'] ) {
				return array(
					'success' => false,
					'message' => $regex_validation['error'],
				);
			}
		}

		// Increase execution time limit to prevent timeouts.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@set_time_limit( 300 ); // 5 minutes.

		// Suspend cache addition to reduce memory usage.
		wp_suspend_cache_addition( true );

		// Batch processing to prevent memory exhaustion.
		$batch_size = 100;
		$page = 1;
		$total_processed = 0;

		do {
			// Query posts in batches.
			$args = array(
				'post_type'      => $post_types,
				'posts_per_page' => $batch_size,
				'paged'          => $page,
				'post_status'    => 'any',
				'no_found_rows'  => false, // We need total count for batching.
			);

			/**
			 * Filter the query args for applying changes.
			 *
			 * @since 1.0.0
			 * @param array $args WP_Query arguments.
			 */
			$args = apply_filters( 'ure_apply_query_args', $args );

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post = get_post();

				// Apply replacements based on scope.
				if ( 'post_content' === $scope || 'all' === $scope ) {
					$original_content = $post->post_content;
					$new_content = $this->replace_in_content( $original_content, $search, $replace, $case_sensitive, $regex_mode );

					// Only update if content actually changed.
					if ( $original_content !== $new_content ) {
						// Update the post.
						$updated = wp_update_post(
							array(
								'ID'           => $post->ID,
								'post_content' => $new_content,
							),
							true
						);

						if ( ! is_wp_error( $updated ) ) {
							$modified_posts[] = $post->ID;

							// Store old data for rollback.
							$operation_data[] = array(
								'post_id'          => $post->ID,
								'post_type'        => $post->post_type,
								'post_title'       => $post->post_title,
								'old_content'      => $original_content,
								'new_content'      => $new_content,
								'location'         => 'post_content',
							);
						}
					}
				}

				// Apply replacements in postmeta.
				if ( 'postmeta' === $scope || 'all' === $scope ) {
					$meta_updates = $this->apply_replacements_in_postmeta( $post->ID, $post, $search, $replace, $case_sensitive, $regex_mode );
					$operation_data = array_merge( $operation_data, $meta_updates );
					if ( ! empty( $meta_updates ) && ! in_array( $post->ID, $modified_posts, true ) ) {
						$modified_posts[] = $post->ID;
					}
				}

				// Apply replacements in Elementor data.
				if ( 'elementor' === $scope || 'all' === $scope ) {
					$elementor_updates = $this->apply_replacements_in_elementor_data( $post->ID, $post, $search, $replace, $case_sensitive, $regex_mode );
					$operation_data = array_merge( $operation_data, $elementor_updates );
					if ( ! empty( $elementor_updates ) && ! in_array( $post->ID, $modified_posts, true ) ) {
						$modified_posts[] = $post->ID;
					}
				}

				$total_processed++;
			}

			wp_reset_postdata();

			// Free up memory after each batch.
			wp_cache_flush();
		}

		$page++;

		} while ( $query->have_posts() && $page <= $query->max_num_pages );

		// Re-enable cache addition.
		wp_suspend_cache_addition( false );

		// Clear Elementor cache if Elementor data was modified.
		if ( ( 'elementor' === $scope || 'all' === $scope ) && ! empty( $modified_posts ) ) {
			$this->clear_elementor_cache();
		}

		// Log the operation.
		if ( ! empty( $modified_posts ) ) {
			$log_data = array(
				'search'          => $search,
				'replace'         => $replace,
				'post_types'      => $post_types,
				'case_sensitive'  => $case_sensitive,
				'modified_count'  => count( $modified_posts ),
				'modified_posts'  => $modified_posts,
				'operation_data'  => $operation_data,
			);

			$summary = sprintf(
				/* translators: 1: number of posts, 2: search term, 3: replace term */
				__( 'Replaced "%2$s" with "%3$s" in %1$d post(s).', 'universal-replace-engine' ),
				count( $modified_posts ),
				$search,
				$replace
			);

			$this->logger->log_operation( $user_id, $summary, $log_data );
		}

		return array(
			'success'        => true,
			'modified_count' => count( $modified_posts ),
			'modified_posts' => $modified_posts,
		);
	}

	/**
	 * Validate a regex pattern.
	 *
	 * @param string $pattern The regex pattern to validate.
	 * @return array Array with 'valid' (bool) and 'error' (string) keys.
	 */
	private function validate_regex( $pattern ) {
		// Suppress errors and try to execute the pattern.
		set_error_handler( function() {} );
		$result = @preg_match( $pattern, '' );
		restore_error_handler();

		if ( false === $result ) {
			$error = error_get_last();
			$error_message = isset( $error['message'] ) ? $error['message'] : __( 'Invalid regex pattern.', 'universal-replace-engine' );

			return array(
				'valid' => false,
				'error' => sprintf(
					/* translators: %s: error message */
					__( 'Regex error: %s', 'universal-replace-engine' ),
					$error_message
				),
			);
		}

		return array(
			'valid' => true,
			'error' => '',
		);
	}

	/**
	 * Prepare regex pattern with proper delimiters and flags.
	 *
	 * @param string $pattern        The regex pattern.
	 * @param bool   $case_sensitive Whether to use case-sensitive matching.
	 * @return string The prepared pattern with delimiters and flags.
	 */
	private function prepare_regex_pattern( $pattern, $case_sensitive = false ) {
		// If pattern already has delimiters, use as-is.
		if ( $this->has_regex_delimiters( $pattern ) ) {
			// Add case-insensitive flag if needed.
			if ( ! $case_sensitive && strpos( $pattern, 'i' ) === false ) {
				$pattern .= 'i';
			}
			return $pattern;
		}

		// Add delimiters and flags.
		$delimiter = '/';
		$flags = $case_sensitive ? '' : 'i';

		// Escape delimiter if it appears in the pattern.
		$pattern = str_replace( $delimiter, '\\' . $delimiter, $pattern );

		return $delimiter . $pattern . $delimiter . $flags;
	}

	/**
	 * Check if a string has regex delimiters.
	 *
	 * @param string $pattern The pattern to check.
	 * @return bool
	 */
	private function has_regex_delimiters( $pattern ) {
		if ( empty( $pattern ) || strlen( $pattern ) < 2 ) {
			return false;
		}

		$first_char = $pattern[0];
		$last_char = $pattern[ strlen( $pattern ) - 1 ];

		// Common regex delimiters.
		$delimiters = array( '/', '#', '~', '@', '!', '%', '`' );

		// Check if starts and ends with same delimiter (ignoring flags).
		if ( in_array( $first_char, $delimiters, true ) ) {
			// Find the last occurrence of the delimiter (before flags).
			$last_delimiter_pos = strrpos( $pattern, $first_char );
			if ( $last_delimiter_pos > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply replacements in postmeta for a given post.
	 *
	 * @param int    $post_id        Post ID.
	 * @param object $post           Post object.
	 * @param string $search         Search term.
	 * @param string $replace        Replacement term.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode     Regex mode.
	 * @return array Operation data for logging.
	 */
	private function apply_replacements_in_postmeta( $post_id, $post, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		$operation_data = array();

		// Get all postmeta for this post.
		$all_meta = get_post_meta( $post_id );

		if ( empty( $all_meta ) ) {
			return $operation_data;
		}

		foreach ( $all_meta as $meta_key => $meta_values ) {
			// Skip Elementor data (handled in its own scope).
			if ( '_elementor_data' === $meta_key || '_elementor_css' === $meta_key || '_elementor_page_settings' === $meta_key ) {
				continue;
			}

			// Process each meta value.
			foreach ( $meta_values as $meta_value ) {
				$original_meta = $meta_value;

				// Handle serialized data.
				$unserialized = maybe_unserialize( $meta_value );
				$was_serialized = ( $unserialized !== $meta_value );

				// Perform replacement.
				$new_meta = $this->replace_in_content( $meta_value, $search, $replace, $case_sensitive, $regex_mode );

				// Only update if changed.
				if ( $original_meta !== $new_meta ) {
					// Update the meta.
					update_post_meta( $post_id, $meta_key, $new_meta );

					// Store old data for rollback.
					$operation_data[] = array(
						'post_id'          => $post_id,
						'post_type'        => $post->post_type,
						'post_title'       => $post->post_title,
						'old_content'      => $original_meta,
						'new_content'      => $new_meta,
						'location'         => 'postmeta',
						'meta_key'         => $meta_key,
					);
				}
			}
		}

		return $operation_data;
	}

	/**
	 * Apply replacements in Elementor data for a given post.
	 *
	 * @param int    $post_id        Post ID.
	 * @param object $post           Post object.
	 * @param string $search         Search term.
	 * @param string $replace        Replacement term.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode     Regex mode.
	 * @return array Operation data for logging.
	 */
	private function apply_replacements_in_elementor_data( $post_id, $post, $search, $replace, $case_sensitive = false, $regex_mode = false ) {
		$operation_data = array();

		// Get Elementor data.
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $elementor_data ) ) {
			return $operation_data;
		}

		$original_data = $elementor_data;

		// Elementor data is stored as JSON string.
		// Perform replacement.
		$new_data = $this->replace_in_content( $elementor_data, $search, $replace, $case_sensitive, $regex_mode );

		// Only update if changed.
		if ( $original_data !== $new_data ) {
			// Validate JSON before updating.
			$decoded = json_decode( $new_data, true );
			if ( null !== $decoded || 'null' === strtolower( $new_data ) ) {
				// Valid JSON, update it.
				update_post_meta( $post_id, '_elementor_data', $new_data );

				// Clear Elementor cache.
				if ( class_exists( '\Elementor\Plugin' ) ) {
					\Elementor\Plugin::$instance->files_manager->clear_cache();
				}

				// Store old data for rollback.
				$operation_data[] = array(
					'post_id'          => $post_id,
					'post_type'        => $post->post_type,
					'post_title'       => $post->post_title,
					'old_content'      => $original_data,
					'new_content'      => $new_data,
					'location'         => 'elementor',
					'meta_key'         => '_elementor_data',
				);
			}
		}

		return $operation_data;
	}

	/**
	 * Search in postmeta for a given post.
	 *
	 * @param int    $post_id        Post ID.
	 * @param object $post           Post object.
	 * @param string $search         Search term.
	 * @param string $replace        Replacement term.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode     Regex mode.
	 * @param int    $limit          Maximum results to return.
	 * @return array Array with 'results' and 'total' keys.
	 */
	private function search_in_postmeta( $post_id, $post, $search, $replace, $case_sensitive = false, $regex_mode = false, $limit = 20 ) {
		$results = array();
		$total_matches = 0;

		// Get all postmeta for this post (excluding Elementor data which is handled separately).
		$all_meta = get_post_meta( $post_id );

		if ( empty( $all_meta ) ) {
			return array(
				'results' => array(),
				'total'   => 0,
			);
		}

		foreach ( $all_meta as $meta_key => $meta_values ) {
			// Skip Elementor data (handled in its own scope).
			if ( '_elementor_data' === $meta_key || '_elementor_css' === $meta_key || '_elementor_page_settings' === $meta_key ) {
				continue;
			}

			// Skip WordPress internal meta (starting with _).
			// Uncomment the line below if you want to skip all internal meta fields.
			// if ( '_' === substr( $meta_key, 0, 1 ) ) {
			//     continue;
			// }

			foreach ( $meta_values as $meta_value ) {
				// Convert to string for searching.
				$meta_value_string = maybe_unserialize( $meta_value );

				if ( is_array( $meta_value_string ) || is_object( $meta_value_string ) ) {
					$meta_value_string = wp_json_encode( $meta_value_string );
				}

				$meta_value_string = (string) $meta_value_string;

				// Find matches.
				$matches = $this->find_matches_in_content( $meta_value_string, $search, $case_sensitive, $regex_mode );

				if ( ! empty( $matches ) ) {
					foreach ( $matches as $match ) {
						$total_matches++;

						// Only store up to limit for display.
						if ( count( $results ) < $limit ) {
							$match_length = strlen( $match['text'] );

							$results[] = array(
								'post_id'    => $post_id,
								'post_type'  => $post->post_type,
								'post_title' => $post->post_title,
								'location'   => 'postmeta: ' . $meta_key,
								'meta_key'   => $meta_key,
								'before'     => $this->get_snippet( $meta_value_string, $match['position'], $match_length, false ),
								'after'      => $replace ? $this->get_snippet(
									$this->replace_in_content( $meta_value_string, $search, $replace, $case_sensitive, $regex_mode ),
									$match['position'],
									$regex_mode ? strlen( $replace ) : $match_length,
									true
								) : '',
								'match_text' => $match['text'],
							);
						}
					}
				}
			}
		}

		return array(
			'results' => $results,
			'total'   => $total_matches,
		);
	}

	/**
	 * Search in Elementor data for a given post.
	 *
	 * @param int    $post_id        Post ID.
	 * @param object $post           Post object.
	 * @param string $search         Search term.
	 * @param string $replace        Replacement term.
	 * @param bool   $case_sensitive Case sensitive search.
	 * @param bool   $regex_mode     Regex mode.
	 * @param int    $limit          Maximum results to return.
	 * @return array Array with 'results' and 'total' keys.
	 */
	private function search_in_elementor_data( $post_id, $post, $search, $replace, $case_sensitive = false, $regex_mode = false, $limit = 20 ) {
		$results = array();
		$total_matches = 0;

		// Get Elementor data.
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $elementor_data ) ) {
			return array(
				'results' => array(),
				'total'   => 0,
			);
		}

		// Elementor data is stored as JSON.
		$elementor_string = $elementor_data;

		// If it's an array, encode it.
		if ( is_array( $elementor_data ) ) {
			$elementor_string = wp_json_encode( $elementor_data );
		}

		// Find matches.
		$matches = $this->find_matches_in_content( $elementor_string, $search, $case_sensitive, $regex_mode );

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $match ) {
				$total_matches++;

				// Only store up to limit for display.
				if ( count( $results ) < $limit ) {
					$match_length = strlen( $match['text'] );

					$results[] = array(
						'post_id'    => $post_id,
						'post_type'  => $post->post_type,
						'post_title' => $post->post_title,
						'location'   => 'Elementor: _elementor_data',
						'meta_key'   => '_elementor_data',
						'before'     => $this->get_snippet( $elementor_string, $match['position'], $match_length, false ),
						'after'      => $replace ? $this->get_snippet(
							$this->replace_in_content( $elementor_string, $search, $replace, $case_sensitive, $regex_mode ),
							$match['position'],
							$regex_mode ? strlen( $replace ) : $match_length,
							true
						) : '',
						'match_text' => $match['text'],
					);
				}
			}
		}

		return array(
			'results' => $results,
			'total'   => $total_matches,
		);
	}

	/**
	 * Validate post types against registered post types.
	 *
	 * @since 1.2.0
	 * @param array $post_types Post types to validate.
	 * @return array Valid post types only.
	 */
	private function validate_post_types( $post_types ) {
		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			return array( 'post', 'page' ); // Default fallback.
		}

		$registered_post_types = get_post_types( array(), 'names' );
		$valid_post_types = array();

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, $registered_post_types, true ) ) {
				$valid_post_types[] = $post_type;
			}
		}

		// If no valid post types, return default.
		if ( empty( $valid_post_types ) ) {
			return array( 'post', 'page' );
		}

		return $valid_post_types;
	}

	/**
	 * Clear Elementor cache after modifications.
	 *
	 * @since 1.2.0
	 */
	private function clear_elementor_cache() {
		// Check if Elementor is active.
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		try {
			// Clear Elementor CSS cache.
			\Elementor\Plugin::$instance->files_manager->clear_cache();

			// Regenerate CSS if method exists (Elementor 3.0+).
			if ( method_exists( \Elementor\Plugin::$instance->posts_css_manager, 'clear_cache' ) ) {
				\Elementor\Plugin::$instance->posts_css_manager->clear_cache();
			}
		} catch ( \Exception $e ) {
			// Silent fail - don't break operation if Elementor cache clear fails.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'URE: Elementor cache clear failed - ' . $e->getMessage() );
		}
	}
}
