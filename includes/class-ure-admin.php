<?php
/**
 * Admin Interface
 *
 * Handles the admin UI, form processing, and user interactions.
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class for managing the plugin's admin interface.
 */
class URE_Admin {

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
	 * Preview data (stored in transient temporarily).
	 *
	 * @var array|null
	 */
	private $preview_data = null;

	/**
	 * Constructor.
	 *
	 * @param URE_Search_Replace $search_replace Search/Replace instance.
	 * @param URE_Logger         $logger         Logger instance.
	 */
	public function __construct( $search_replace, $logger ) {
		$this->search_replace = $search_replace;
		$this->logger         = $logger;

		// Register admin menu.
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// Handle form submissions.
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
	}

	/**
	 * Register admin menu under Tools.
	 */
	public function register_admin_menu() {
		// Main page with tabs.
		add_management_page(
			__( 'Universal Replace Engine', 'universal-replace-engine' ),
			__( 'Universal Replace Engine', 'universal-replace-engine' ),
			'manage_options',
			'universal-replace-engine',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Handle form submissions.
	 */
		public function handle_form_submission() {
			// Only process on our admin pages.
			if ( ! isset( $_GET['page'] ) ) {
				return;
			}

			$allowed_pages = array(
				'universal-replace-engine',
				'ure-database-search',
				'ure-backup',
				'ure-settings',
				'ure-help',
			);

			if ( ! in_array( $_GET['page'], $allowed_pages, true ) ) {
				return;
			}

			// Check if form was submitted.
			if ( ! isset( $_POST['ure_action'] ) ) {
				return;
			}
	
			$action = sanitize_key( $_POST['ure_action'] );
	
			// Determine which nonce to check based on the action.
			$nonce_name = 'ure_nonce';
			$nonce_action = 'ure_action';
	
			if ( in_array( $action, array( 'save_settings', 'reset_settings' ), true ) ) {
				$nonce_name = 'ure_settings_nonce';
				$nonce_action = 'ure_settings_action';
			}
	
			// Verify nonce.
			if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
				wp_die( esc_html__( 'Security check failed.', 'universal-replace-engine' ) );
			}
	
			// Check capabilities.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'universal-replace-engine' ) );
			}
	
			switch ( $action ) {
				case 'preview':
					$this->handle_preview();
					break;
	
				case 'apply':
					$this->handle_apply();
					break;
	
				case 'undo':
					$this->handle_undo();
					break;
	
				case 'create_backup':
					$this->handle_create_backup();
					break;
	
				case 'restore_backup':
					$this->handle_restore_backup();
					break;
	
				case 'delete_backup':
					$this->handle_delete_backup();
					break;

				case 'download_backup':
					$this->handle_download_backup();
					break;

				case 'database_preview':
					$this->handle_database_preview();
					break;
	
				case 'database_apply':
					$this->handle_database_apply();
					break;
	
				case 'save_profile':
					$this->handle_save_profile();
					break;
	
				case 'load_profile':
					$this->handle_load_profile();
					break;
	
				case 'delete_profile':
					$this->handle_delete_profile();
					break;
	
				case 'save_settings':
					$this->handle_save_settings();
					break;
	
				case 'reset_settings':
					$this->handle_reset_settings();
					break;
			}
		}


	/**
	 * Handle preview action.
	 */
	private function handle_preview() {
		$search = isset( $_POST['ure_search'] ) ? wp_unslash( $_POST['ure_search'] ) : '';
		$replace = isset( $_POST['ure_replace'] ) ? wp_unslash( $_POST['ure_replace'] ) : '';
		$post_types = isset( $_POST['ure_post_types'] ) ? (array) $_POST['ure_post_types'] : array( 'post', 'page' );
		$case_sensitive = isset( $_POST['ure_case_sensitive'] );
		$regex_mode = isset( $_POST['ure_regex_mode'] );
		$scope = isset( $_POST['ure_scope'] ) ? sanitize_key( $_POST['ure_scope'] ) : 'post_content';


		if ( empty( $search ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please enter a search term.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Run preview.
		$this->preview_data = $this->search_replace->run_preview(
			$search,
			$replace,
			$post_types,
			$case_sensitive,
			$regex_mode,
			$scope
		);

		if ( isset( $this->preview_data['error'] ) && ! empty( $this->preview_data['error'] ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$this->preview_data['error'],
				'error'
			);
			return;
		}

		// Store in transient for apply action (expires in 1 hour).
		set_transient(
			'ure_preview_data_' . get_current_user_id(),
			array(
				'search'         => $search,
				'replace'        => $replace,
				'post_types'     => $post_types,
				'case_sensitive' => $case_sensitive,
				'regex_mode'     => $regex_mode,
				'scope'          => $scope,
				'preview_data'   => $this->preview_data,
			),
			HOUR_IN_SECONDS
		);

		if ( empty( $this->preview_data['results'] ) ) {
			add_settings_error(
				'ure_messages',
				'ure_info',
				__( 'No matches found.', 'universal-replace-engine' ),
				'info'
			);
		} else {
			$message = sprintf(
				/* translators: %d: number of matches */
				__( 'Found %d match(es). Review below and click "Apply Changes" to proceed.', 'universal-replace-engine' ),
				$this->preview_data['total']
			);

			add_settings_error(
				'ure_messages',
				'ure_success',
				$message,
				'success'
			);
		}
	}

	/**
	 * Handle apply action.
	 */
	private function handle_apply() {
		// Get preview data from transient.
		$transient_key = 'ure_preview_data_' . get_current_user_id();
		$preview_data = get_transient( $transient_key );

		if ( ! $preview_data ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Preview data expired. Please run preview again.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Apply replacements.
		$scope = isset( $preview_data['scope'] ) ? $preview_data['scope'] : 'post_content';

		$result = $this->search_replace->apply_replacements(
			$preview_data['search'],
			$preview_data['replace'],
			$preview_data['post_types'],
			$preview_data['case_sensitive'],
			$preview_data['regex_mode'],
			get_current_user_id(),
			$scope
		);

		// Delete transient.
		delete_transient( $transient_key );

		if ( $result['success'] ) {
			$message = sprintf(
				/* translators: %d: number of posts modified */
				__( 'Successfully modified %d post(s).', 'universal-replace-engine' ),
				$result['modified_count']
			);

			add_settings_error(
				'ure_messages',
				'ure_success',
				$message,
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$result['message'],
				'error'
			);
		}
	}

	/**
	 * Handle undo action.
	 */
	private function handle_undo() {
		if ( ! isset( $_POST['ure_log_id'] ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Invalid log ID.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$log_id = absint( $_POST['ure_log_id'] );

		// Perform rollback.
		$result = $this->logger->rollback_operation( $log_id, get_current_user_id() );

		if ( $result['success'] ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				$result['message'],
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$result['message'],
				'error'
			);
		}
	}

		/**
		 * Render the admin page.
		 */
		public function render_admin_page() {
			// Check if we have preview data from form submission.
			$saved_form_data = null;
			if ( null === $this->preview_data ) {
				$transient_key = 'ure_preview_data_' . get_current_user_id();
				$transient_data = get_transient( $transient_key );
				if ( $transient_data && isset( $transient_data['preview_data'] ) ) {
					$this->preview_data = $transient_data['preview_data'];
					// Also save the form data for repopulating form fields
					$saved_form_data = $transient_data;
				}
			}

			// Check if Pro is active.
			$is_pro = apply_filters( 'ure_is_pro', false );

			// Determine active tab.
			$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'main';
	
			?>
			<div class="wrap ure-admin-wrap">
				<h1>
					<?php echo esc_html( get_admin_page_title() ); ?>
					<?php if ( $is_pro ) : ?>
						<span class="ure-pro-badge" style="background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
							color: white; padding: 5px 12px; border-radius: 3px; font-size: 14px;
							margin-left: 10px; vertical-align: middle;">PRO</span>
					<?php endif; ?>
				</h1>
	
				<?php settings_errors( 'ure_messages' ); ?>
	
				<h2 class="nav-tab-wrapper">
					<a href="?page=universal-replace-engine&tab=main" class="nav-tab <?php echo ( 'main' === $active_tab || '' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
						<?php esc_html_e( 'Search & Replace', 'universal-replace-engine' ); ?>
					</a>
			<?php if ( $is_pro ) : ?>
			<a href="?page=universal-replace-engine&tab=database" class="nav-tab <?php echo ( 'database' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Database Search', 'universal-replace-engine' ); ?>
				<span style="color: #4ade80; font-weight: 600; font-size: 11px; margin-left: 5px;">PRO</span>
			</a>
			<a href="?page=universal-replace-engine&tab=backup" class="nav-tab <?php echo ( 'backup' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Backup', 'universal-replace-engine' ); ?>
				<span style="color: #4ade80; font-weight: 600; font-size: 11px; margin-left: 5px;">PRO</span>
			</a>
			<?php else : ?>
			<a href="?page=universal-replace-engine&tab=database" class="nav-tab <?php echo ( 'database' === $active_tab ) ? 'nav-tab-active' : ''; ?>" style="position: relative;">
				<?php esc_html_e( 'Database Search', 'universal-replace-engine' ); ?>
				<span style="color: #6b7280; font-weight: 600; font-size: 11px; margin-left: 5px;">🔒 PRO</span>
			</a>
			<a href="?page=universal-replace-engine&tab=backup" class="nav-tab <?php echo ( 'backup' === $active_tab ) ? 'nav-tab-active' : ''; ?>" style="position: relative;">
				<?php esc_html_e( 'Backup', 'universal-replace-engine' ); ?>
				<span style="color: #6b7280; font-weight: 600; font-size: 11px; margin-left: 5px;">🔒 PRO</span>
			</a>
			<?php endif; ?>
					<a href="?page=universal-replace-engine&tab=settings" class="nav-tab <?php echo ( 'settings' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
						<?php esc_html_e( 'Settings', 'universal-replace-engine' ); ?>
					</a>
					<a href="?page=universal-replace-engine&tab=help" class="nav-tab <?php echo ( 'help' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
						<?php esc_html_e( 'Help', 'universal-replace-engine' ); ?>
					</a>
				</h2>
	
				<div class="ure-tab-content">
					<?php if ( 'main' === $active_tab || '' === $active_tab ) : ?>
						<div id="ure-tab-main" class="ure-container">
							<?php
							// Check for loaded profile data.
							$loaded_profile = get_transient( 'ure_loaded_profile_' . get_current_user_id() );
							if ( $loaded_profile ) {
								// Clear the transient after reading.
								delete_transient( 'ure_loaded_profile_' . get_current_user_id() );
							}
							?>
							<!-- Search and Replace Form -->
							<div class="ure-section ure-search-section">
								<h2><?php esc_html_e( 'Search and Replace', 'universal-replace-engine' ); ?></h2>
	
								<form method="post" action="" class="ure-form">
									<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
	
									<table class="form-table">
										<tr>
											<th scope="row">
												<label for="ure_search"><?php esc_html_e( 'Search for', 'universal-replace-engine' ); ?></label>
											</th>
											<td>
												<?php
												$search_value = '';
												if ( $loaded_profile && isset( $loaded_profile['search'] ) ) {
													$search_value = $loaded_profile['search'];
												} elseif ( isset( $_POST['ure_search'] ) ) {
													$search_value = wp_unslash( $_POST['ure_search'] );
												} elseif ( $saved_form_data && isset( $saved_form_data['search'] ) ) {
													$search_value = $saved_form_data['search'];
												}
												?>
												<input type="text"
													   name="ure_search"
													   id="ure_search"
													   class="regular-text"
													   value="<?php echo esc_attr( $search_value ); ?>"
													   required>
												<p class="description">
													<?php esc_html_e( 'Enter the text you want to search for.', 'universal-replace-engine' ); ?>
												</p>
											</td>
										</tr>
	
										<tr>
											<th scope="row">
												<label for="ure_replace"><?php esc_html_e( 'Replace with', 'universal-replace-engine' ); ?></label>
											</th>
											<td>
												<?php
												$replace_value = '';
												if ( $loaded_profile && isset( $loaded_profile['replace'] ) ) {
													$replace_value = $loaded_profile['replace'];
												} elseif ( isset( $_POST['ure_replace'] ) ) {
													$replace_value = wp_unslash( $_POST['ure_replace'] );
												} elseif ( $saved_form_data && isset( $saved_form_data['replace'] ) ) {
													$replace_value = $saved_form_data['replace'];
												}
												?>
												<input type="text"
													   name="ure_replace"
													   id="ure_replace"
													   class="regular-text"
													   value="<?php echo esc_attr( $replace_value ); ?>">
												<p class="description">
													<?php esc_html_e( 'Enter the replacement text (leave empty to only search).', 'universal-replace-engine' ); ?>
												</p>
											</td>
										</tr>
	
										<tr>
											<th scope="row">
												<?php esc_html_e( 'Search Scope', 'universal-replace-engine' ); ?>
											</th>
											<td>
												<?php
												$selected_scope = 'post_content'; // default
												if ( $loaded_profile && isset( $loaded_profile['scope'] ) ) {
													$selected_scope = $loaded_profile['scope'];
												} elseif ( isset( $_POST['ure_scope'] ) ) {
													$selected_scope = sanitize_key( $_POST['ure_scope'] );
												} elseif ( $saved_form_data && isset( $saved_form_data['scope'] ) ) {
													$selected_scope = $saved_form_data['scope'];
												}
												?>
												<fieldset>
													<label>
														<input type="radio" name="ure_scope" value="post_content" <?php checked( $selected_scope, 'post_content' ); ?>>
														<strong><?php esc_html_e( 'Post Content', 'universal-replace-engine' ); ?></strong>
														<span class="description"><?php esc_html_e( '(post_content field only)', 'universal-replace-engine' ); ?></span>
													</label>
													<br>
													<label>
														<input type="radio" name="ure_scope" value="postmeta"
															<?php checked( $selected_scope, 'postmeta' ); ?>
														<strong><?php esc_html_e( 'Post Meta', 'universal-replace-engine' ); ?></strong>
														<span class="description"><?php esc_html_e( '(custom fields, excluding Elementor)', 'universal-replace-engine' ); ?></span>
													<br>
													<label>
														<input type="radio" name="ure_scope" value="elementor"
															<?php checked( $selected_scope, 'elementor' ); ?>
														<strong><?php esc_html_e( 'Elementor Data', 'universal-replace-engine' ); ?></strong>
														<span class="description"><?php esc_html_e( '(_elementor_data JSON field)', 'universal-replace-engine' ); ?></span>
													<br>
													<label>
														<input type="radio" name="ure_scope" value="all"
															<?php checked( $selected_scope, 'all' ); ?>
														<strong><?php esc_html_e( 'All Locations', 'universal-replace-engine' ); ?></strong>
														<span class="description"><?php esc_html_e( '(content + postmeta + Elementor)', 'universal-replace-engine' ); ?></span>
														<span style="color: #d97706; font-weight: 600;">⚠ USE WITH CAUTION</span>
													</label>
												</fieldset>
												<p class="description">
													<?php esc_html_e( 'Choose where to search and replace.', 'universal-replace-engine'); ?>
												</p>
											</td>
										</tr>

										<tr>
											<th scope="row">
												<?php esc_html_e( 'Post Types', 'universal-replace-engine' ); ?>
											</th>
											<td>
												<?php $this->render_post_type_checkboxes( $loaded_profile, $saved_form_data ); ?>
												<p class="description">
													<?php esc_html_e( 'Select which post types to search in.', 'universal-replace-engine' ); ?>
												</p>
											</td>
										</tr>
	
										<tr>
											<th scope="row">
												<?php esc_html_e( 'Options', 'universal-replace-engine' ); ?>
											</th>
											<td>
												<fieldset>
													<label>
														<input type="checkbox"
															   name="ure_case_sensitive"
															   value="1"
															   <?php
															   $is_case_sensitive = false;
															   if ( $loaded_profile && isset( $loaded_profile['case_sensitive'] ) ) {
																   $is_case_sensitive = $loaded_profile['case_sensitive'];
															   } elseif ( isset( $_POST['ure_case_sensitive'] ) ) {
																   $is_case_sensitive = true;
															   } elseif ( $saved_form_data && isset( $saved_form_data['case_sensitive'] ) ) {
																   $is_case_sensitive = $saved_form_data['case_sensitive'];
															   }
															   checked( $is_case_sensitive );
															   ?>>
														<?php esc_html_e( 'Case sensitive', 'universal-replace-engine' ); ?>
													</label>
													<br>
													<label>
														<input type="checkbox"
															   name="ure_regex_mode"
															   value="1"
															   <?php
															   $is_regex_mode = false;
															   if ( $loaded_profile && isset( $loaded_profile['regex_mode'] ) ) {
																   $is_regex_mode = $loaded_profile['regex_mode'];
															   } elseif ( isset( $_POST['ure_regex_mode'] ) ) {
																   $is_regex_mode = true;
															   } elseif ( $saved_form_data && isset( $saved_form_data['regex_mode'] ) ) {
																   $is_regex_mode = $saved_form_data['regex_mode'];
															   }
															   checked( $is_regex_mode );
															   disabled( ! $is_pro );
															   ?>>
														<?php
														if ( $is_pro ) {
															esc_html_e( 'Regex mode', 'universal-replace-engine' );
															echo ' <span style="color: #4ade80; font-weight: 600;">✓ PRO</span>';
														} else {
															esc_html_e( 'Regex mode (Pro feature)', 'universal-replace-engine' );
														}
														?>
													</label>
												</fieldset>
											</td>
										</tr>
									</table>
	
									<input type="hidden" name="ure_action" id="ure_action_field" value="">
									<p class="submit">
										<button type="submit" class="button button-primary" onclick="document.getElementById('ure_action_field').value='preview'; return true;">
											<?php esc_html_e( 'Run Preview', 'universal-replace-engine' ); ?>
										</button>
	
										<?php if ( $this->preview_data && !empty( $this->preview_data['results'] ) ) : ?>
											<button type="submit" class="button button-secondary ure-apply-btn" onclick="document.getElementById('ure_action_field').value='apply'; return true;">
												<?php esc_html_e( 'Apply Changes', 'universal-replace-engine' ); ?>
											</button>
										<?php endif; ?>
									</p>
								</form>
							</div>
	
							<!-- Preview Results -->
							<?php if ( $this->preview_data && ! empty( $this->preview_data['results'] ) ) : ?>
								<div class="ure-section ure-preview-section">
									<h2><?php esc_html_e( 'Preview Results', 'universal-replace-engine' ); ?></h2>
	


									<?php if ( $this->preview_data['limited'] ) : ?>
										<div class="notice notice-info inline">
											<p>
												<?php
												printf(
													/* translators: 1: shown count, 2: total count */
													esc_html__( 'Showing %1$d of %2$d matches (Free version limit).', 'universal-replace-engine' ),
													count( $this->preview_data['results'] ),
													absint( $this->preview_data['total'] )
												);
												?>
												<br>
												<strong><?php esc_html_e( 'Note:', 'universal-replace-engine' ); ?></strong>
												<?php
												printf(
													/* translators: %d: total matches */
													esc_html__( 'Clicking "Apply Changes" will replace all %d matches found, not just the ones shown in this preview.', 'universal-replace-engine' ),
													absint( $this->preview_data['total'] )
												);
												?>
											</p>
										</div>
									<?php elseif ( $is_pro && $this->preview_data['total'] > 20 ) : ?>
										<div class="notice notice-success inline">
											<p>
												<strong>✓ Pro Feature Active:</strong>
												<?php
												printf(
													/* translators: %d: total matches */
													esc_html__( 'Showing all %d matches (unlimited preview in Pro version).', 'universal-replace-engine' ),
													absint( $this->preview_data['total'] )
												);
												?>
											</p>
										</div>
									<?php endif; ?>
	
									<table class="wp-list-table widefat fixed striped ure-preview-table">
										<thead>
											<tr>
												<th style="width: 18%;"><?php esc_html_e( 'Location', 'universal-replace-engine' ); ?></th>
												<th style="width: 15%;"><?php esc_html_e( 'Scope', 'universal-replace-engine' ); ?></th>
												<th style="width: 33%;"><?php esc_html_e( 'Before', 'universal-replace-engine' ); ?></th>
												<th style="width: 34%;"><?php esc_html_e( 'After', 'universal-replace-engine' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $this->preview_data['results'] as $result ) : ?>
												<tr>
													<td>
														<strong><?php echo esc_html( $result['post_title'] ); ?></strong>
														<br>
														<small>
															<?php
															printf(
																/* translators: 1: post type, 2: post ID */
																esc_html__( '%1$s (ID: %2$d)', 'universal-replace-engine' ),
																esc_html( $result['post_type'] ),
																absint( $result['post_id'] )
															);
															?>
														</small>
													</td>
													<td>
														<code style="font-size: 11px;">
															<?php echo esc_html( $result['location'] ); ?>
														</code>
													</td>
													<td><?php echo wp_kses_post( $result['before'] ); ?></td>
													<td>
														<?php
														if ( $result['after'] ) {
															echo wp_kses_post( $result['after'] );
														} else {
															echo '<em>' . esc_html__( 'Search only', 'universal-replace-engine' ) . '</em>';
														}
														?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>

							<!-- History Section -->
							<div class="ure-section ure-history-section">
								<h2>
									<?php
									if ( $is_pro ) {
										$history_limit = apply_filters( 'ure_history_limit', 5 );
										printf(
											/* translators: %d: history limit */
											esc_html__( 'History (Last %d Operations)', 'universal-replace-engine' ),
											absint( $history_limit )
										);
									} else {
										esc_html_e( 'History (Last 5 Operations)', 'universal-replace-engine' );
									}
									?>
								</h2>
	
								<?php $this->render_history(); ?>
							</div>
						</div>
	
						<!-- Saved Profiles Section -->
						<?php require_once URE_PLUGIN_DIR . 'templates/admin-profiles.php'; ?>
	
	
						<!-- Pro Features Section -->
						<?php
						/**
						 * Filter to check if Pro version is active.
						 *
						 * @since 1.0.0
						 * @param bool $is_pro Whether Pro version is active.
						 */
						$is_pro = apply_filters( 'ure_is_pro', false );
	
						if ( ! $is_pro ) :
							// Show upgrade teaser for free version.
							?>
							<div class="ure-section ure-pro-teaser">
								<h3><?php esc_html_e( 'Unlock Pro Features', 'universal-replace-engine' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Advanced Database Mode - Direct table-level search and replace', 'universal-replace-engine' ); ?></li>
									<li><?php esc_html_e( 'Full regex mode with pattern validation', 'universal-replace-engine' ); ?></li>
									<li><?php esc_html_e( 'Priority support from our expert team', 'universal-replace-engine' ); ?></li>
								</ul>
								<?php
								/**
								 * Hook for Pro version to add upgrade button.
								 *
								 * @since 1.0.0
								 */
								do_action( 'ure_pro_upgrade_button' );
								?>
							</div>
						<?php else : ?>
							<!-- Pro Version Active Badge -->
							<?php
							/**
							 * Hook for Pro version to display Pro badge.
							 *
							 * @since 1.0.0
							 */
							do_action( 'ure_pro_badge' );
							?>
						<?php endif; ?>
	
					<?php endif; // End main tab. ?>
	

					<?php if ( 'database' === $active_tab ) : ?>
						<div id="ure-tab-database">
							<?php if ( $is_pro ) : ?>
								<?php require_once URE_PLUGIN_DIR . 'templates/admin-db-search.php'; ?>
							<?php else : ?>
								<div class="ure-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 4px; text-align: center; border: none;">
									<h2 style="color: white; margin: 0 0 20px 0; font-size: 32px;">
										🔒 Database Search & Replace
									</h2>
									<p style="margin: 0 0 30px 0; font-size: 18px; opacity: 0.95; line-height: 1.6;">
										Advanced database-level operations are available in the Pro version.
									</p>
									<div style="background: rgba(255,255,255,0.1); padding: 30px; border-radius: 4px; margin-bottom: 30px;">
										<h3 style="color: white; margin: 0 0 20px 0; font-size: 20px;">Pro Features Include:</h3>
										<ul style="list-style: none; padding: 0; margin: 0; text-align: left; display: inline-block;">
											<li style="margin: 10px 0; font-size: 16px;">✓ Search and replace in ANY database table</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Direct table-level access (wp_options, wp_postmeta, etc.)</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Preview changes before applying</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ GUID protection for safe URL changes</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Batch processing for large databases</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Case-sensitive search option</li>
										</ul>
									</div>
									<a href="https://xtech.red/" class="button button-primary button-hero" style="background: white; color: #667eea; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
										Upgrade to Pro
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( 'backup' === $active_tab ) : ?>
						<div id="ure-tab-backup">
							<?php if ( $is_pro ) : ?>
								<?php require_once URE_PLUGIN_DIR . 'templates/admin-backup.php'; ?>
							<?php else : ?>
								<div class="ure-section" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 40px; border-radius: 4px; text-align: center; border: none;">
									<h2 style="color: white; margin: 0 0 20px 0; font-size: 32px;">
										🔒 Database Backup & Restore
									</h2>
									<p style="margin: 0 0 30px 0; font-size: 18px; opacity: 0.95; line-height: 1.6;">
										Enterprise-grade backup and restore features are available in the Pro version.
									</p>
									<div style="background: rgba(255,255,255,0.1); padding: 30px; border-radius: 4px; margin-bottom: 30px;">
										<h3 style="color: white; margin: 0 0 20px 0; font-size: 20px;">Pro Features Include:</h3>
										<ul style="list-style: none; padding: 0; margin: 0; text-align: left; display: inline-block;">
											<li style="margin: 10px 0; font-size: 16px;">✓ Create SQL backups of any table</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ One-click restore from backup files</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Batch processing for large tables</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Backup retention management</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Download backup files</li>
											<li style="margin: 10px 0; font-size: 16px;">✓ Backup before dangerous operations</li>
										</ul>
									</div>
									<a href="https://xtech.red/" class="button button-primary button-hero" style="background: white; color: #f5576c; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
										Upgrade to Pro
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( 'settings' === $active_tab ) : ?>
						<div id="ure-tab-settings">
							<?php require_once URE_PLUGIN_DIR . 'templates/admin-settings.php'; ?>
						</div>
					<?php endif; ?>
	
					<?php if ( 'help' === $active_tab ) : ?>
						<div id="ure-tab-help">
							<?php require_once URE_PLUGIN_DIR . 'templates/admin-help.php'; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
	
			<?php
		}
	/**
	 * Get human-readable label for scope.
	 *
	 * @param string $scope The scope identifier.
	 * @return string The human-readable label.
	 */
	private function get_scope_label( $scope ) {
		$labels = array(
			'postmeta'  => __( 'Post Meta (Custom Fields)', 'universal-replace-engine' ),
			'elementor' => __( 'Elementor Data', 'universal-replace-engine' ),
			'all'       => __( 'All Locations', 'universal-replace-engine' ),
		);

		return isset( $labels[ $scope ] ) ? $labels[ $scope ] : $scope;
	}

	/**
	 * Render post type checkboxes.
	 */
	private function render_post_type_checkboxes( $loaded_profile = null, $saved_form_data = null ) {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$selected = array( 'post', 'page' ); // default
		if ( $loaded_profile && isset( $loaded_profile['post_types'] ) ) {
			$selected = $loaded_profile['post_types'];
		} elseif ( isset( $_POST['ure_post_types'] ) ) {
			$selected = (array) $_POST['ure_post_types'];
		} elseif ( $saved_form_data && isset( $saved_form_data['post_types'] ) ) {
			$selected = $saved_form_data['post_types'];
		}

		foreach ( $post_types as $post_type ) {
			// Skip attachments.
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			printf(
				'<label><input type="checkbox" name="ure_post_types[]" value="%s" %s> %s</label><br>',
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $selected, true ), true, false ),
				esc_html( $post_type->label )
			);
		}
	}

	/**
	 * Render history table.
	 */
	private function render_history() {
		$logs = $this->logger->get_logs();

		if ( empty( $logs ) ) {
			echo '<p>' . esc_html__( 'No operations recorded yet.', 'universal-replace-engine' ) . '</p>';
			return;
		}

		?>
		<table class="wp-list-table widefat fixed striped ure-history-table">
			<thead>
				<tr>
					<th style="width: 15%;"><?php esc_html_e( 'Date/Time', 'universal-replace-engine' ); ?></th>
					<th style="width: 15%;"><?php esc_html_e( 'User', 'universal-replace-engine' ); ?></th>
					<th style="width: 50%;"><?php esc_html_e( 'Summary', 'universal-replace-engine' ); ?></th>
					<th style="width: 20%;"><?php esc_html_e( 'Actions', 'universal-replace-engine' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $logs as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log['timestamp'] ); ?></td>
						<td><?php echo esc_html( $log['user_name'] ); ?></td>
						<td><?php echo esc_html( $log['summary'] ); ?></td>
						<td>
							<?php if ( isset( $log['details']['operation_data'] ) && ! empty( $log['details']['operation_data'] ) ) : ?>
								<form method="post" action="" style="display: inline;">
									<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
									<input type="hidden" name="ure_log_id" value="<?php echo absint( $log['id'] ); ?>">
									<button type="submit"
											name="ure_action"
											value="undo"
											class="button button-small ure-undo-btn"
											onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to undo this operation?', 'universal-replace-engine' ) ); ?>');">
										<?php esc_html_e( 'Undo', 'universal-replace-engine' ); ?>
									</button>
								</form>
							<?php else : ?>
								<em><?php esc_html_e( 'N/A', 'universal-replace-engine' ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Handle backup creation.
	 */
	private function handle_create_backup() {
		$plugin         = URE_Plugin::get_instance();
		$backup_manager = $plugin->backup_manager;

		$tables  = isset( $_POST['ure_backup_tables'] ) ? array_map( 'sanitize_text_field', $_POST['ure_backup_tables'] ) : array();
		$comment = isset( $_POST['ure_backup_comment'] ) ? sanitize_text_field( $_POST['ure_backup_comment'] ) : '';

		if ( empty( $tables ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please select at least one table to backup.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$result = $backup_manager->create_backup( $tables, $comment );

		if ( $result['success'] ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				sprintf(
					/* translators: 1: table count, 2: file size */
					__( 'Backup created successfully! %1$d tables backed up (%2$.2f MB).', 'universal-replace-engine' ),
					$result['table_count'],
					$result['file_size'] / 1024 / 1024
				),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$result['message'],
				'error'
			);
		}
	}

	/**
	 * Handle backup restoration.
	 */
	private function handle_restore_backup() {
		$plugin         = URE_Plugin::get_instance();
		$backup_manager = $plugin->backup_manager;

		$filename = isset( $_POST['ure_backup_file'] ) ? sanitize_file_name( $_POST['ure_backup_file'] ) : '';

		if ( empty( $filename ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Invalid backup filename.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$result = $backup_manager->restore_backup( $filename );

		if ( $result['success'] ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				$result['message'],
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$result['message'],
				'error'
			);
		}
	}

	/**
	 * Handle backup deletion.
	 */
	private function handle_delete_backup() {
		$plugin         = URE_Plugin::get_instance();
		$backup_manager = $plugin->backup_manager;

		$filename = isset( $_POST['ure_backup_file'] ) ? sanitize_file_name( $_POST['ure_backup_file'] ) : '';

		if ( empty( $filename ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Invalid backup filename.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$result = $backup_manager->delete_backup( $filename );

		if ( $result['success'] ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				$result['message'],
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				$result['message'],
				'error'
			);
		}
	}

	/**
	 * Handle backup download.
	 */
	private function handle_download_backup() {
		$plugin         = URE_Plugin::get_instance();
		$backup_manager = $plugin->backup_manager;

		$filename = isset( $_POST['ure_backup_file'] ) ? sanitize_file_name( $_POST['ure_backup_file'] ) : '';

		if ( empty( $filename ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Invalid backup filename.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Download the backup file (this will exit).
		$backup_manager->download_backup( $filename );

		// If we get here, download failed.
		add_settings_error(
			'ure_messages',
			'ure_error',
			__( 'Failed to download backup file.', 'universal-replace-engine' ),
			'error'
		);
	}

	/**
	 * Handle database preview (advanced mode).
	 */
	private function handle_database_preview() {
		$plugin           = URE_Plugin::get_instance();
		$database_manager = $plugin->database_manager;

		$search         = isset( $_POST['ure_db_search'] ) ? wp_unslash( $_POST['ure_db_search'] ) : '';
		$replace        = isset( $_POST['ure_db_replace'] ) ? wp_unslash( $_POST['ure_db_replace'] ) : '';
		$tables         = isset( $_POST['ure_db_tables'] ) ? array_map( 'sanitize_text_field', $_POST['ure_db_tables'] ) : array();
		$case_sensitive = isset( $_POST['ure_db_case_sensitive'] );
		$regex_mode     = isset( $_POST['ure_db_regex_mode'] ) && apply_filters( 'ure_is_pro', false );
		$skip_guids     = isset( $_POST['ure_db_skip_guids'] );

		if ( empty( $search ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please enter a search term.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		if ( empty( $tables ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please select at least one table.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$total_changes = 0;
		$table_results = array();

		foreach ( $tables as $table_name ) {
			$result = $database_manager->table_search_replace(
				$table_name,
				$search,
				$replace,
				$case_sensitive,
				$regex_mode,
				true, // dry run
				$skip_guids
			);

			if ( isset( $result['success'] ) && ! $result['success'] ) {
				continue;
			}

			$total_changes       += $result['changes'];
			$table_results[]      = $result;
		}

		if ( $total_changes > 0 ) {
			// Store preview data in transient.
			set_transient(
				'ure_db_preview_' . get_current_user_id(),
				array(
					'search'         => $search,
					'replace'        => $replace,
					'tables'         => $tables,
					'case_sensitive' => $case_sensitive,
					'regex_mode'     => $regex_mode,
					'skip_guids'     => $skip_guids,
					'results'        => $table_results,
				),
				HOUR_IN_SECONDS
			);

			add_settings_error(
				'ure_messages',
				'ure_success',
				sprintf(
					/* translators: 1: changes count, 2: tables count */
					__( 'Found %1$d potential changes across %2$d tables. Review below.', 'universal-replace-engine' ),
					$total_changes,
					count( $table_results )
				),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_info',
				__( 'No matches found in selected tables.', 'universal-replace-engine' ),
				'info'
			);
		}
	}

	/**
	 * Handle database apply (advanced mode).
	 */
	private function handle_database_apply() {
		$plugin           = URE_Plugin::get_instance();
		$database_manager = $plugin->database_manager;

		// Get preview data.
		$preview_data = get_transient( 'ure_db_preview_' . get_current_user_id() );

		if ( ! $preview_data ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Preview data expired. Please run preview again.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$search         = $preview_data['search'];
		$replace        = $preview_data['replace'];
		$tables         = $preview_data['tables'];
		$case_sensitive = $preview_data['case_sensitive'];
		$regex_mode     = $preview_data['regex_mode'];
		$skip_guids     = $preview_data['skip_guids'];

		$total_updates = 0;
		$table_results = array();

		foreach ( $tables as $table_name ) {
			$result = $database_manager->table_search_replace(
				$table_name,
				$search,
				$replace,
				$case_sensitive,
				$regex_mode,
				false, // not dry run
				$skip_guids
			);

			if ( isset( $result['success'] ) && ! $result['success'] ) {
				continue;
			}

			$total_updates   += $result['updates'];
			$table_results[]  = $result;
		}

		if ( $total_updates > 0 ) {
			// Log the operation.
			$summary = sprintf(
				/* translators: 1: search, 2: replace, 3: updates count, 4: tables count */
				__( 'Database operation: Replaced "%1$s" with "%2$s" in %3$d rows across %4$d tables.', 'universal-replace-engine' ),
				$search,
				$replace,
				$total_updates,
				count( $table_results )
			);

			$this->logger->log_operation(
				get_current_user_id(),
				$summary,
				array(
					'type'           => 'database',
					'search'         => $search,
					'replace'        => $replace,
					'tables'         => $tables,
					'case_sensitive' => $case_sensitive,
					'regex_mode'     => $regex_mode,
					'total_updates'  => $total_updates,
					'table_results'  => $table_results,
				)
			);

			// Clear preview data.
			delete_transient( 'ure_db_preview_' . get_current_user_id() );

			add_settings_error(
				'ure_messages',
				'ure_success',
				sprintf(
					/* translators: 1: updates count, 2: tables count */
					__( 'Successfully updated %1$d rows across %2$d tables.', 'universal-replace-engine' ),
					$total_updates,
					count( $table_results )
				),
				'success'
			);
		} else {
			$this->add_notice(
				'ure_messages',
				'ure_warning',
				__( 'No changes were made.', 'universal-replace-engine' ),
				'warning'
			);
		}
	}



	/**
	 * Handle save settings action.
	 */
	private function handle_save_settings() {
		if ( ! isset( $_POST['ure_settings'] ) || ! is_array( $_POST['ure_settings'] ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'No settings data received.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Validate and sanitize settings.
		$new_settings = URE_Settings::validate( wp_unslash( $_POST['ure_settings'] ) );

		// Save settings.
		if ( URE_Settings::update_multiple( $new_settings ) ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				__( 'Settings saved successfully.', 'universal-replace-engine' ),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Failed to save settings. Please try again.', 'universal-replace-engine' ),
				'error'
			);
		}
	}

	/**
	 * Handle reset settings action.
	 */
	private function handle_reset_settings() {
		if ( URE_Settings::reset() ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				__( 'Settings have been reset to defaults.', 'universal-replace-engine' ),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Failed to reset settings. Please try again.', 'universal-replace-engine' ),
				'error'
			);
		}
	}

	/**
	 * Handle save profile action.
	 */
	private function handle_save_profile() {
		// DEBUG: Log all POST data
		
		$profile_name = isset( $_POST['ure_new_profile_name'] ) ? sanitize_text_field( $_POST['ure_new_profile_name'] ) : '';

		if ( empty( $profile_name ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please enter a profile name.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Get current form values.
		$profile_data = array(
			'search'         => isset( $_POST['ure_search'] ) ? sanitize_text_field( $_POST['ure_search'] ) : '',
			'replace'        => isset( $_POST['ure_replace'] ) ? sanitize_text_field( $_POST['ure_replace'] ) : '',
			'post_types'     => isset( $_POST['ure_post_types'] ) ? array_map( 'sanitize_text_field', $_POST['ure_post_types'] ) : array( 'post', 'page' ),
			'case_sensitive' => isset( $_POST['ure_case_sensitive'] ),
			'regex_mode'     => isset( $_POST['ure_regex_mode'] ),
			'scope'          => isset( $_POST['ure_scope'] ) ? sanitize_key( $_POST['ure_scope'] ) : 'post_content',
		);
		

		if ( URE_Profiles::save( $profile_name, $profile_data ) ) {
			// Store in transient so form shows what was just saved
			set_transient( 'ure_loaded_profile_' . get_current_user_id(), $profile_data, HOUR_IN_SECONDS );
			
			add_settings_error(
				'ure_messages',
				'ure_success',
				sprintf(
					/* translators: %s: profile name */
					__( 'Profile "%s" saved successfully.', 'universal-replace-engine' ),
					$profile_name
				),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Failed to save profile. Please try again.', 'universal-replace-engine' ),
				'error'
			);
		}
	}

	/**
	 * Handle load profile action.
	 */
	private function handle_load_profile() {
		$profile_name = isset( $_POST['ure_profile_name'] ) ? sanitize_text_field( $_POST['ure_profile_name'] ) : '';

		if ( empty( $profile_name ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please select a profile to load.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		$profile = URE_Profiles::get( $profile_name );

		if ( null === $profile ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Profile not found.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		// Store loaded profile data in transient for form pre-fill.
		set_transient( 'ure_loaded_profile_' . get_current_user_id(), $profile, HOUR_IN_SECONDS );

		add_settings_error(
			'ure_messages',
			'ure_success',
			sprintf(
				/* translators: %s: profile name */
				__( 'Profile "%s" loaded successfully. Review the settings below and click Preview or Apply.', 'universal-replace-engine' ),
				$profile_name
			),
			'success'
		);
	}

	/**
	 * Handle delete profile action.
	 */
	private function handle_delete_profile() {
		$profile_name = isset( $_POST['ure_profile_name'] ) ? sanitize_text_field( $_POST['ure_profile_name'] ) : '';

		if ( empty( $profile_name ) ) {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Please select a profile to delete.', 'universal-replace-engine' ),
				'error'
			);
			return;
		}

		if ( URE_Profiles::delete( $profile_name ) ) {
			add_settings_error(
				'ure_messages',
				'ure_success',
				sprintf(
					/* translators: %s: profile name */
					__( 'Profile "%s" deleted successfully.', 'universal-replace-engine' ),
					$profile_name
				),
				'success'
			);
		} else {
			add_settings_error(
				'ure_messages',
				'ure_error',
				__( 'Failed to delete profile. Please try again.', 'universal-replace-engine' ),
				'error'
			);
		}
	}

	/**
	 * Render Database Search & Replace page.
	 */
	public function render_db_search_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Database Search & Replace', 'universal-replace-engine' ); ?></h1>
			<?php settings_errors( 'ure_messages' ); ?>
			<?php require_once URE_PLUGIN_DIR . 'templates/admin-db-search.php'; ?>
		</div>
		<?php
	}

	/**
	 * Render Backup & Restore page.
	 */
	public function render_backup_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Backup & Restore', 'universal-replace-engine' ); ?></h1>
			<?php settings_errors( 'ure_messages' ); ?>
			<?php require_once URE_PLUGIN_DIR . 'templates/admin-backup.php'; ?>
		</div>
		<?php
	}

	/**
	 * Render Settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'URE Settings', 'universal-replace-engine' ); ?></h1>
			<?php settings_errors( 'ure_messages' ); ?>
			<?php require_once URE_PLUGIN_DIR . 'templates/admin-settings.php'; ?>
		</div>
		<?php
	}

	/**
	 * Render Help page.
	 */
	public function render_help_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'URE Help', 'universal-replace-engine' ); ?></h1>
			<?php require_once URE_PLUGIN_DIR . 'templates/admin-help.php'; ?>
		</div>
		<?php
	}

	/**
	 * Add a settings error/notice with optional warning suppression.
	 *
	 * @param string $setting Setting slug.
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param string $type    Message type (error, success, warning, info).
	 */
	private function add_notice( $setting, $code, $message, $type = 'error' ) {
		// If type is 'warning' and show_warnings is disabled, don't show it.
		if ( 'warning' === $type && ! URE_Settings::get( 'show_warnings', true ) ) {
			return;
		}

		add_settings_error( $setting, $code, $message, $type );
	}
}
