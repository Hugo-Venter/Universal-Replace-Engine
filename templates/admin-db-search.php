<?php
/**
 * Advanced Database Mode Template - Database Search & Replace
 *
 * Template for advanced database-level search and replace operations.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin           = URE_Plugin::get_instance();
$database_manager = $plugin->database_manager;

// Get tables.
$all_tables = $database_manager->get_tables();

/**
 * Get translatable table type label.
 *
 * @param string $type Table type identifier.
 * @return string Translated label.
 */
function ure_get_table_type_label( $type ) {
	$labels = array(
		'core'            => __( 'Core', 'universal-replace-engine' ),
		'woocommerce'     => __( 'WooCommerce', 'universal-replace-engine' ),
		'edd'             => __( 'Easy Digital Downloads', 'universal-replace-engine' ),
		'yoast'           => __( 'Yoast SEO', 'universal-replace-engine' ),
		'aioseo'          => __( 'All in One SEO', 'universal-replace-engine' ),
		'rank-math'       => __( 'Rank Math', 'universal-replace-engine' ),
		'elementor'       => __( 'Elementor', 'universal-replace-engine' ),
		'divi'            => __( 'Divi', 'universal-replace-engine' ),
		'gravity-forms'   => __( 'Gravity Forms', 'universal-replace-engine' ),
		'contact-form-7'  => __( 'Contact Form 7', 'universal-replace-engine' ),
		'formidable'      => __( 'Formidable Forms', 'universal-replace-engine' ),
		'wpforms'         => __( 'WPForms', 'universal-replace-engine' ),
		'acf'             => __( 'Advanced Custom Fields', 'universal-replace-engine' ),
		'wpml'            => __( 'WPML', 'universal-replace-engine' ),
		'wordfence'       => __( 'Wordfence', 'universal-replace-engine' ),
		'wp-rocket'       => __( 'WP Rocket', 'universal-replace-engine' ),
		'akismet'         => __( 'Akismet', 'universal-replace-engine' ),
		'memberpress'     => __( 'MemberPress', 'universal-replace-engine' ),
		'learndash'       => __( 'LearnDash', 'universal-replace-engine' ),
		'lifterlms'       => __( 'LifterLMS', 'universal-replace-engine' ),
		'bbpress'         => __( 'bbPress', 'universal-replace-engine' ),
		'buddypress'      => __( 'BuddyPress', 'universal-replace-engine' ),
		'jetpack'         => __( 'Jetpack', 'universal-replace-engine' ),
		'wp-all-import'   => __( 'WP All Import', 'universal-replace-engine' ),
		'redirection'     => __( 'Redirection', 'universal-replace-engine' ),
		'wp-staging'      => __( 'WP Staging', 'universal-replace-engine' ),
		'duplicator'      => __( 'Duplicator', 'universal-replace-engine' ),
		'plugin'          => __( 'Plugin', 'universal-replace-engine' ),
		'custom'          => __( 'Custom', 'universal-replace-engine' ),
	);

	return isset( $labels[ $type ] ) ? $labels[ $type ] : ucfirst( str_replace( '-', ' ', $type ) );
}
?>

<div class="ure-advanced-mode">
	<div class="ure-notice ure-notice-warning">
		<h3><?php esc_html_e( 'Advanced Mode - Use With Caution!', 'universal-replace-engine' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Advanced mode allows you to search and replace across any database table, including wp_options, usermeta, and custom plugin tables. This is powerful but potentially dangerous.',
				'universal-replace-engine'
			);
			?>
		</p>
		<p style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 10px; margin: 15px 0;">
			<strong style="color: #d63638;">⚠️ <?php esc_html_e( 'IMPORTANT: Database operations CANNOT be rolled back!', 'universal-replace-engine' ); ?></strong><br>
			<?php esc_html_e( 'Unlike post content changes, database-level operations cannot be undone through the History section. You must create a backup before applying changes.', 'universal-replace-engine' ); ?>
		</p>
		<ul>
			<li><strong><?php esc_html_e( 'ALWAYS create a backup before proceeding', 'universal-replace-engine' ); ?></strong></li>
			<li><?php esc_html_e( 'Test on a staging site first', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Be very specific with your search terms', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Understand what you\'re changing', 'universal-replace-engine' ); ?></li>
		</ul>
	</div>

	<!-- Database Search & Replace -->
	<div class="ure-card">
		<h2><?php esc_html_e( 'Database Search & Replace', 'universal-replace-engine' ); ?></h2>

		<form method="post" action="" class="ure-database-form">
			<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
			<input type="hidden" name="ure_action" value="database_preview" />

			<div class="ure-form-row">
				<label for="ure_db_search">
					<?php esc_html_e( 'Search For', 'universal-replace-engine' ); ?>
					<span class="required">*</span>
				</label>
				<input
					type="text"
					id="ure_db_search"
					name="ure_db_search"
					class="regular-text"
					required
					placeholder="<?php esc_attr_e( 'Text to search for...', 'universal-replace-engine' ); ?>"
				/>
			</div>

			<div class="ure-form-row">
				<label for="ure_db_replace"><?php esc_html_e( 'Replace With', 'universal-replace-engine' ); ?></label>
				<input
					type="text"
					id="ure_db_replace"
					name="ure_db_replace"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Replacement text...', 'universal-replace-engine' ); ?>"
				/>
			</div>

			<div class="ure-form-row">
				<label><?php esc_html_e( 'Select Tables', 'universal-replace-engine' ); ?></label>
				<div class="ure-table-selector">
					<div class="ure-select-helpers">
						<button type="button" class="button ure-select-all-db"><?php esc_html_e( 'Select All', 'universal-replace-engine' ); ?></button>
						<button type="button" class="button ure-select-none-db"><?php esc_html_e( 'Deselect All', 'universal-replace-engine' ); ?></button>
					</div>

					<div class="ure-table-list-wrapper">
						<table class="ure-database-tables widefat striped">
							<thead>
								<tr>
									<th class="check-column">
										<input type="checkbox" id="ure-select-all-tables" />
									</th>
									<th><?php esc_html_e( 'Table Name', 'universal-replace-engine' ); ?></th>
									<th><?php esc_html_e( 'Type', 'universal-replace-engine' ); ?></th>
									<th><?php esc_html_e( 'Rows', 'universal-replace-engine' ); ?></th>
									<th><?php esc_html_e( 'Size', 'universal-replace-engine' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $all_tables as $table ) : ?>
									<tr class="ure-table-row" data-type="<?php echo esc_attr( $table['type'] ); ?>">
										<td class="check-column">
											<input
												type="checkbox"
												name="ure_db_tables[]"
												value="<?php echo esc_attr( $table['name'] ); ?>"
												class="ure-table-checkbox"
											/>
										</td>
										<td class="ure-table-name-cell">
											<strong><?php echo esc_html( $table['name'] ); ?></strong>
											<?php if ( $table['protected'] ) : ?>
												<span class="ure-badge ure-badge-warning"><?php esc_html_e( 'Protected', 'universal-replace-engine' ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<span class="ure-table-type <?php echo esc_attr( 'ure-type-' . $table['type'] ); ?>">
												<?php echo esc_html( ure_get_table_type_label( $table['type'] ) ); ?>
											</span>
										</td>
										<td><?php echo esc_html( number_format_i18n( $table['rows'] ) ); ?></td>
										<td><?php echo esc_html( $table['size_mb'] ); ?> MB</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="ure-form-row">
				<label><?php esc_html_e( 'Options', 'universal-replace-engine' ); ?></label>
				<label>
					<input type="checkbox" name="ure_db_case_sensitive" value="1" />
					<?php esc_html_e( 'Case sensitive', 'universal-replace-engine' ); ?>
				</label>
				<label>
					<input type="checkbox" name="ure_db_skip_guids" value="1" checked />
					<?php esc_html_e( 'Skip GUIDs (recommended)', 'universal-replace-engine' ); ?>
				</label>
				<?php if ( apply_filters( 'ure_is_pro', false ) ) : ?>
					<label>
						<input type="checkbox" name="ure_db_regex_mode" value="1" />
						<?php esc_html_e( 'Regex mode', 'universal-replace-engine' ); ?>
						<span class="ure-badge ure-badge-pro">PRO</span>
					</label>
				<?php endif; ?>
			</div>

			<div class="ure-form-actions">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Preview Changes', 'universal-replace-engine' ); ?>
				</button>
			</div>
		</form>
	</div>

	<?php
	// Check for preview data from database search
	$db_preview_data = get_transient( 'ure_db_preview_' . get_current_user_id() );

	if ( $db_preview_data && isset( $db_preview_data['results'] ) && ! empty( $db_preview_data['results'] ) ) :
		?>
		<!-- Database Preview Results -->
		<div class="ure-card">
			<h2><?php esc_html_e( 'Preview Results', 'universal-replace-engine' ); ?></h2>

			<div class="ure-notice ure-notice-warning">
				<p>
					<strong><?php esc_html_e( 'Review the changes below carefully before applying.', 'universal-replace-engine' ); ?></strong>
				</p>
			</div>

			<!-- Summary Table -->
			<h3><?php esc_html_e( 'Summary', 'universal-replace-engine' ); ?></h3>
			<table class="wp-list-table widefat fixed striped" style="margin-bottom: 30px;">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Table', 'universal-replace-engine' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Rows Scanned', 'universal-replace-engine' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Changes Found', 'universal-replace-engine' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Time', 'universal-replace-engine' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_changes = 0;
					$total_previews = 0;
					foreach ( $db_preview_data['results'] as $result ) :
						if ( isset( $result['changes'] ) && $result['changes'] > 0 ) :
							$total_changes += $result['changes'];
							if ( isset( $result['previews'] ) ) {
								$total_previews += count( $result['previews'] );
							}
							?>
							<tr>
								<td><strong><?php echo esc_html( $result['table'] ); ?></strong></td>
								<td><?php echo esc_html( number_format_i18n( $result['rows'] ) ); ?></td>
								<td><strong style="color: #d63638;"><?php echo esc_html( number_format_i18n( $result['changes'] ) ); ?></strong></td>
								<td><?php echo esc_html( isset( $result['time_elapsed'] ) ? $result['time_elapsed'] . 's' : 'N/A' ); ?></td>
							</tr>
						<?php
						endif;
					endforeach;
					?>
				</tbody>
			</table>

			<!-- Detailed Preview of Changes -->
			<h3>
				<?php esc_html_e( 'Preview of Changes', 'universal-replace-engine' ); ?>
				<small style="font-weight: normal; color: #666;">
					<?php
					printf(
						/* translators: 1: number shown, 2: total changes */
						esc_html__( '(Showing up to 20 examples out of %d total changes)', 'universal-replace-engine' ),
						number_format_i18n( $total_changes )
					);
					?>
				</small>
			</h3>
			<table class="wp-list-table widefat fixed striped ure-preview-table">
				<thead>
					<tr>
						<th style="width: 20%;"><?php esc_html_e( 'Location', 'universal-replace-engine' ); ?></th>
						<th style="width: 40%;"><?php esc_html_e( 'Before', 'universal-replace-engine' ); ?></th>
						<th style="width: 40%;"><?php esc_html_e( 'After', 'universal-replace-engine' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$preview_count = 0;
					$max_previews = 20;

					foreach ( $db_preview_data['results'] as $result ) :
						if ( ! isset( $result['previews'] ) || empty( $result['previews'] ) ) {
							continue;
						}

						foreach ( $result['previews'] as $preview ) :
							if ( $preview_count >= $max_previews ) {
								break 2; // Break out of both loops
							}

							$preview_count++;
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $result['table'] ); ?></strong>
									<br>
									<small style="color: #666;">
										<?php
										printf(
											/* translators: 1: column name, 2: primary key value */
											esc_html__( 'Column: %1$s (ID: %2$s)', 'universal-replace-engine' ),
											esc_html( $preview['column'] ),
											esc_html( $preview['pk_value'] )
										);
										?>
									</small>
								</td>
								<td>
									<code style="display: block; background: #f9f9f9; padding: 8px; border-radius: 3px; word-break: break-all; font-size: 12px;">
										<?php
										$before = $preview['before'];
										$search = $db_preview_data['search'];

										// Highlight search term
										if ( ! empty( $search ) ) {
											$case_sensitive = isset( $db_preview_data['case_sensitive'] ) && $db_preview_data['case_sensitive'];
											if ( $case_sensitive ) {
												$before = str_replace(
													$search,
													'<mark style="background: #ffff00; padding: 2px 4px; border-radius: 2px;">' . esc_html( $search ) . '</mark>',
													esc_html( $before )
												);
											} else {
												$before = preg_replace(
													'/' . preg_quote( $search, '/' ) . '/i',
													'<mark style="background: #ffff00; padding: 2px 4px; border-radius: 2px;">$0</mark>',
													esc_html( $before )
												);
											}
											echo wp_kses( $before, array( 'mark' => array( 'style' => array() ) ) );
										} else {
											echo esc_html( $before );
										}
										?>
									</code>
								</td>
								<td>
									<code style="display: block; background: #f0f6fc; padding: 8px; border-radius: 3px; word-break: break-all; font-size: 12px;">
										<?php
										$after = $preview['after'];
										$replace = $db_preview_data['replace'];

										// Highlight replacement term
										if ( ! empty( $replace ) ) {
											$case_sensitive = isset( $db_preview_data['case_sensitive'] ) && $db_preview_data['case_sensitive'];
											if ( $case_sensitive ) {
												$after = str_replace(
													$replace,
													'<mark style="background: #4ade80; padding: 2px 4px; border-radius: 2px;">' . esc_html( $replace ) . '</mark>',
													esc_html( $after )
												);
											} else {
												$after = preg_replace(
													'/' . preg_quote( $replace, '/' ) . '/i',
													'<mark style="background: #4ade80; padding: 2px 4px; border-radius: 2px;">$0</mark>',
													esc_html( $after )
												);
											}
											echo wp_kses( $after, array( 'mark' => array( 'style' => array() ) ) );
										} else {
											echo esc_html( $after );
										}
										?>
									</code>
								</td>
							</tr>
						<?php
						endforeach;
					endforeach;

					if ( $preview_count === 0 ) :
						?>
						<tr>
							<td colspan="3" style="text-align: center; padding: 20px; color: #666;">
								<?php esc_html_e( 'No preview examples available. Changes will be applied based on the summary above.', 'universal-replace-engine' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<form method="post" action="" style="margin-top: 20px;">
				<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
				<input type="hidden" name="ure_action" value="database_apply" />

				<p class="submit">
					<button type="submit" class="button button-primary button-large" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to apply these changes to the database? This action cannot be easily undone. Make sure you have a backup!', 'universal-replace-engine' ) ); ?>');">
						<?php
						printf(
							/* translators: %d: number of changes */
							esc_html__( 'Apply %d Changes', 'universal-replace-engine' ),
							$total_changes
						);
						?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=universal-replace-engine&tab=database' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Cancel', 'universal-replace-engine' ); ?>
					</a>
				</p>
			</form>
		</div>
	<?php endif; ?>
</div>

<style>
/* CSS specific to advanced mode, will keep it here for now */
.ure-advanced-mode {
	max-width: 1200px;
}

.ure-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	margin: 20px 0;
	padding: 20px;
}

.ure-notice {
	border-left: 4px solid #d63638;
	background: #fff;
	padding: 12px;
	margin: 20px 0;
}

.ure-notice-warning {
	border-left-color: #dba617;
}

.ure-notice h3 {
	margin-top: 0;
}

.ure-form-row {
	margin-bottom: 20px;
}

.ure-form-row label {
	display: block;
	font-weight: 600;
	margin-bottom: 8px;
}

.ure-table-selector {
	border: 1px solid #ddd;
	background: #f9f9f9;
	padding: 12px;
	border-radius: 4px;
}

.ure-select-helpers {
	margin-bottom: 12px;
	padding-bottom: 12px;
	border-bottom: 1px solid #ddd;
}

.ure-select-helpers button {
	margin-right: 8px;
}

.ure-table-list-wrapper {
	max-height: 500px;
	overflow-y: auto;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.ure-database-tables {
	margin: 0;
	border: none;
}

.ure-database-tables thead th {
	background: #f9f9f9;
	font-weight: 600;
	position: sticky;
	top: 0;
	z-index: 10;
	border-bottom: 2px solid #ddd;
}

.ure-database-tables th.check-column,
.ure-database-tables td.check-column {
	width: 40px;
	text-align: center;
	vertical-align: middle;
}

.ure-database-tables th.check-column input[type="checkbox"],
.ure-database-tables td.check-column input[type="checkbox"] {
	margin: 0;
	vertical-align: middle;
}

.ure-database-tables th:nth-child(2) {
	width: 40%;
}

.ure-database-tables th:nth-child(3) {
	width: 20%;
}

.ure-database-tables th:nth-child(4),
.ure-database-tables th:nth-child(5) {
	width: 15%;
	text-align: right;
}

.ure-database-tables td:nth-child(4),
.ure-database-tables td:nth-child(5) {
	text-align: right;
}

.ure-table-row {
	cursor: pointer;
}

.ure-table-row:hover {
	background: #f0f6fc !important;
}

.ure-table-name-cell {
	display: flex;
	align-items: center;
	gap: 8px;
}

.ure-table-type {
	display: inline-block;
	padding: 3px 10px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
	background: #f0f0f0;
	color: #333;
	text-transform: capitalize;
}

.ure-type-core {
	background: #4CAF50;
	color: white;
}

.ure-type-woocommerce {
	background: #96588a;
	color: white;
}

.ure-type-yoast {
	background: #a4286a;
	color: white;
}

.ure-type-elementor {
	background: #d30c5c;
	color: white;
}

.ure-type-acf {
	background: #00d4aa;
	color: white;
}

.ure-type-gravity-forms {
	background: #2271b1;
	color: white;
}

.ure-type-wpml {
	background: #7f54b3;
	color: white;
}

.ure-type-edd {
	background: #2794da;
	color: white;
}

.ure-type-jetpack {
	background: #069e08;
	color: white;
}

.ure-type-wordfence {
	background: #00709e;
	color: white;
}

.ure-badge {
	display: inline-block;
	padding: 2px 6px;
	font-size: 11px;
	border-radius: 3px;
	font-weight: 600;
}

.ure-badge-warning {
	background: #ffd60a;
	color: #000;
}

.ure-badge-pro {
	background: #2196F3;
	color: white;
}

.ure-backups-list {
	margin-top: 30px;
	padding-top: 30px;
	border-top: 1px solid #ddd;
}

.ure-form-actions {
	margin-top: 20px;
}

.required {
	color: #d63638;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Database table selection helpers
	$('.ure-select-all-db, #ure-select-all-tables').on('click', function() {
		var isHeaderCheckbox = $(this).attr('id') === 'ure-select-all-tables';
		var checked = isHeaderCheckbox ? $(this).prop('checked') : true;
		$('.ure-table-checkbox').prop('checked', checked);
	});

	$('.ure-select-none-db').on('click', function() {
		$('.ure-table-checkbox').prop('checked', false);
		$('#ure-select-all-tables').prop('checked', false);
	});

	// Click row to toggle checkbox
	$('.ure-table-row').on('click', function(e) {
		// Don't toggle if clicking directly on checkbox
		if ($(e.target).is('input[type="checkbox"]')) {
			return;
		}

		var $checkbox = $(this).find('.ure-table-checkbox');
		$checkbox.prop('checked', !$checkbox.prop('checked'));
		updateSelectAllCheckbox();
	});

	// Update "select all" checkbox state based on individual checkboxes
	$('.ure-table-checkbox').on('change', function() {
		updateSelectAllCheckbox();
	});

	function updateSelectAllCheckbox() {
		var total = $('.ure-table-checkbox').length;
		var checked = $('.ure-table-checkbox:checked').length;
		var $selectAll = $('#ure-select-all-tables');

		if (checked === 0) {
			$selectAll.prop('checked', false).prop('indeterminate', false);
		} else if (checked === total) {
			$selectAll.prop('checked', true).prop('indeterminate', false);
		} else {
			$selectAll.prop('checked', false).prop('indeterminate', true);
		}
	}
});
</script>
