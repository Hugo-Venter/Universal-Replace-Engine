<?php
/**
 * Backup Template
 *
 * Template for database backup and restore operations.
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
$backup_manager   = $plugin->backup_manager;

// Get tables.
$all_tables = $database_manager->get_tables();

// Get backups.
$backups = $backup_manager->get_backups();
?>

<div class="ure-advanced-mode">
	<div class="ure-notice ure-notice-warning">
		<h3><?php esc_html_e( 'Backup & Restore - Use With Caution!', 'universal-replace-engine' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'Creating backups and restoring your database are critical operations. Always ensure you have a recent working backup before making major changes.',
				'universal-replace-engine'
			);
			?>
		</p>
		<ul>
			<li><strong><?php esc_html_e( 'ALWAYS create a backup before proceeding', 'universal-replace-engine' ); ?></strong></li>
			<li><?php esc_html_e( 'Test on a staging site first', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Understand what you\'re restoring', 'universal-replace-engine' ); ?></li>
		</ul>
	</div>

	<!-- Backup Section -->
	<div class="ure-card">
		<h2><?php esc_html_e( 'Create Backup (Recommended)', 'universal-replace-engine' ); ?></h2>

		<form method="post" action="" class="ure-backup-form">
			<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
			<input type="hidden" name="ure_action" value="create_backup" />

			<div class="ure-form-row">
				<label><?php esc_html_e( 'Tables to Backup', 'universal-replace-engine' ); ?></label>
				<div class="ure-table-selector">
					<div class="ure-select-helpers">
						<button type="button" class="button ure-select-all"><?php esc_html_e( 'Select All', 'universal-replace-engine' ); ?></button>
						<button type="button" class="button ure-select-none"><?php esc_html_e( 'Deselect All', 'universal-replace-engine' ); ?></button>
						<button type="button" class="button ure-select-core"><?php esc_html_e( 'Core Tables Only', 'universal-replace-engine' ); ?></button>
					</div>

					<div class="ure-table-list">
						<?php foreach ( $all_tables as $table ) : ?>
							<label class="ure-table-item" data-type="<?php echo esc_attr( $table['type'] ); ?>">
								<input
									type="checkbox"
									name="ure_backup_tables[]"
									value="<?php echo esc_attr( $table['name'] ); ?>"
									<?php checked( in_array( $table['type'], array( 'core' ), true ) ); ?>
								/>
								<span class="ure-table-name">
									<?php echo esc_html( $table['name'] ); ?>
									<?php if ( $table['protected'] ) : ?>
										<span class="ure-badge ure-badge-warning"><?php esc_html_e( 'Protected', 'universal-replace-engine' ); ?></span>
									<?php endif; ?>
								</span>
								<span class="ure-table-meta">
									<span class="ure-table-type <?php echo esc_attr( 'ure-type-' . $table['type'] ); ?>">
										<?php echo esc_html( ucfirst( $table['type'] ) ); ?>
									</span>
									<span class="ure-table-rows"><?php echo esc_html( number_format_i18n( $table['rows'] ) ); ?> rows</span>
									<span class="ure-table-size"><?php echo esc_html( $table['size_mb'] ); ?> MB</span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="ure-form-row">
				<label for="ure_backup_comment"><?php esc_html_e( 'Comment (Optional)', 'universal-replace-engine' ); ?></label>
				<input
					type="text"
					id="ure_backup_comment"
					name="ure_backup_comment"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'E.g., Before domain migration', 'universal-replace-engine' ); ?>"
				/>
			</div>

			<div class="ure-form-actions">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Create Backup', 'universal-replace-engine' ); ?>
				</button>
			</div>
		</form>

		<!-- Existing Backups -->
		<?php if ( ! empty( $backups ) ) : ?>
			<div class="ure-backups-list">
				<h3><?php esc_html_e( 'Available Backups', 'universal-replace-engine' ); ?></h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Created', 'universal-replace-engine' ); ?></th>
							<th><?php esc_html_e( 'User', 'universal-replace-engine' ); ?></th>
							<th><?php esc_html_e( 'Tables', 'universal-replace-engine' ); ?></th>
							<th><?php esc_html_e( 'Size', 'universal-replace-engine' ); ?></th>
							<th><?php esc_html_e( 'Comment', 'universal-replace-engine' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'universal-replace-engine' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $backups as $backup ) : ?>
							<tr>
								<td>
									<?php
									echo esc_html(
										wp_date(
											get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
											strtotime( $backup['created'] )
										)
								);
									?>
								</td>
								<td><?php echo esc_html( $backup['user_name'] ); ?></td>
								<td><?php echo esc_html( $backup['table_count'] . ' tables' ); ?></td>
								<td><?php echo esc_html( $backup['file_size_mb'] . ' MB' ); ?></td>
								<td><?php echo esc_html( $backup['comment'] ); ?></td>
								<td>
									<form method="post" action="" style="display: inline;">
										<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
										<input type="hidden" name="ure_action" value="restore_backup" />
										<input type="hidden" name="ure_backup_file" value="<?php echo esc_attr( $backup['filename'] ); ?>" />
										<button
											type="submit"
											class="button button-small"
											onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to restore this backup? This will overwrite current data!', 'universal-replace-engine' ); ?>');"
										>
											<?php esc_html_e( 'Restore', 'universal-replace-engine' ); ?>
										</button>
									</form>

									<form method="post" action="" style="display: inline;">
										<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
										<input type="hidden" name="ure_action" value="delete_backup" />
										<input type="hidden" name="ure_backup_file" value="<?php echo esc_attr( $backup['filename'] ); ?>" />
										<button
											type="submit"
											class="button button-small button-link-delete"
											onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this backup?', 'universal-replace-engine' ); ?>');"
										>
											<?php esc_html_e( 'Delete', 'universal-replace-engine' ); ?>
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
/* CSS specific to backup mode */
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

.ure-table-list {
	max-height: 400px;
	overflow-y: auto;
	background: #fff;
	border: 1px solid #ddd;
	padding: 8px;
}

.ure-table-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 8px;
	border-bottom: 1px solid #f0f0f0;
	cursor: pointer;
}

.ure-table-item:hover {
	background: #f0f0f0;
}

.ure-table-name {
	flex: 1;
	display: flex;
	align-items: center;
	gap: 8px;
}

.ure-table-meta {
	display: flex;
	gap: 12px;
	font-size: 12px;
	color: #666;
}

.ure-table-type {
	padding: 2px 8px;
	border-radius: 3px;
	background: #f0f0f0;
}

.ure-type-core {
	background: #4CAF50;
	color: white;
}

.ure-type-woocommerce {
	background: #96588a;
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
	// Backup table selection helpers
	$('.ure-select-all').on('click', function() {
		$('input[name="ure_backup_tables[]"]').prop('checked', true);
	});

	$('.ure-select-none').on('click', function() {
		$('input[name="ure_backup_tables[]"]').prop('checked', false);
	});

	$('.ure-select-core').on('click', function() {
		$('input[name="ure_backup_tables[]"]').prop('checked', false);
		$('.ure-table-item[data-type="core"] input').prop('checked', true);
	});
});
</script>