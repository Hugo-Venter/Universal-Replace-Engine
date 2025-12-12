<?php
/**
 * Settings Page Template
 *
 * Template for plugin settings and configuration.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = URE_Settings::get_all();
$is_pro   = apply_filters( 'ure_is_pro', false );
?>

<div class="wrap ure-settings-page">
	<h1><?php esc_html_e( 'Universal Replace Engine - Settings', 'universal-replace-engine' ); ?></h1>

	<?php settings_errors( 'ure_messages' ); ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'ure_settings_action', 'ure_settings_nonce' ); ?>
		<input type="hidden" name="ure_action" value="save_settings" />

		<!-- Performance Settings -->
		<div class="ure-card">
			<h2><?php esc_html_e( 'Performance Settings', 'universal-replace-engine' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Configure batch sizes to optimize performance for your server. Lower values use less memory but take longer.', 'universal-replace-engine' ); ?>
			</p>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Content Batch Size -->
					<tr>
						<th scope="row">
							<label for="content_batch_size">
								<?php esc_html_e( 'Content Batch Size', 'universal-replace-engine' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="content_batch_size"
								name="ure_settings[content_batch_size]"
								value="<?php echo esc_attr( $settings['content_batch_size'] ); ?>"
								min="10"
								max="1000"
								step="10"
								class="small-text"
							/>
							<p class="description">
								<?php esc_html_e( 'Number of posts to process per batch for content operations (10-1000). Default: 100', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>

					<!-- Database Batch Size -->
					<tr>
						<th scope="row">
							<label for="database_batch_size">
								<?php esc_html_e( 'Database Batch Size', 'universal-replace-engine' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="database_batch_size"
								name="ure_settings[database_batch_size]"
								value="<?php echo esc_attr( $settings['database_batch_size'] ); ?>"
								min="100"
								max="10000"
								step="100"
								class="small-text"
							/>
							<p class="description">
								<?php esc_html_e( 'Number of rows to process per batch for database operations (100-10000). Default: 5000', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>

					<!-- Backup Batch Size -->
					<tr>
						<th scope="row">
							<label for="backup_batch_size">
								<?php esc_html_e( 'Backup Batch Size', 'universal-replace-engine' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="backup_batch_size"
								name="ure_settings[backup_batch_size]"
								value="<?php echo esc_attr( $settings['backup_batch_size'] ); ?>"
								min="100"
								max="5000"
								step="100"
								class="small-text"
							/>
							<p class="description">
								<?php esc_html_e( 'Number of rows to backup per batch (100-5000). Default: 1000', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Preview & History Settings -->
		<div class="ure-card">
			<h2><?php esc_html_e( 'Preview & History Settings', 'universal-replace-engine' ); ?></h2>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Max Preview Results -->
					<tr>
						<th scope="row">
							<label for="max_preview_results">
								<?php esc_html_e( 'Max Preview Results', 'universal-replace-engine' ); ?>
								<?php if ( ! $is_pro ) : ?>
									<span class="ure-badge ure-badge-pro"><?php esc_html_e( 'Pro: Unlimited', 'universal-replace-engine' ); ?></span>
								<?php endif; ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="max_preview_results"
								name="ure_settings[max_preview_results]"
								value="<?php echo esc_attr( $settings['max_preview_results'] ); ?>"
								min="1"
								max="<?php echo $is_pro ? '100' : '50'; ?>"
								class="small-text"
							/>
							<p class="description">
								<?php
								if ( $is_pro ) {
									esc_html_e( 'Maximum number of results to show in preview (1-100).', 'universal-replace-engine' );
								} else {
									esc_html_e( 'Maximum number of results to show in preview (1-50). Pro version removes this limit.', 'universal-replace-engine' );
								}
								?>
							</p>
						</td>
					</tr>

					<!-- History Limit -->
					<tr>
						<th scope="row">
							<label for="history_limit">
								<?php esc_html_e( 'Operation History Limit', 'universal-replace-engine' ); ?>
								<?php if ( ! $is_pro ) : ?>
									<span class="ure-badge ure-badge-pro"><?php esc_html_e( 'Pro: 50', 'universal-replace-engine' ); ?></span>
								<?php endif; ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="history_limit"
								name="ure_settings[history_limit]"
								value="<?php echo esc_attr( $settings['history_limit'] ); ?>"
								min="1"
								max="<?php echo $is_pro ? '50' : '10'; ?>"
								class="small-text"
							/>
							<p class="description">
								<?php
								if ( $is_pro ) {
									esc_html_e( 'Number of operations to keep in history for rollback (1-50).', 'universal-replace-engine' );
								} else {
									esc_html_e( 'Number of operations to keep in history for rollback (1-10). Pro version keeps up to 50.', 'universal-replace-engine' );
								}
								?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Backup Settings -->
		<div class="ure-card">
			<h2><?php esc_html_e( 'Backup Settings', 'universal-replace-engine' ); ?></h2>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Backup Retention -->
					<tr>
						<th scope="row">
							<label for="backup_retention_days">
								<?php esc_html_e( 'Backup Retention', 'universal-replace-engine' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="backup_retention_days"
								name="ure_settings[backup_retention_days]"
								value="<?php echo esc_attr( $settings['backup_retention_days'] ); ?>"
								min="1"
								max="30"
								class="small-text"
							/>
							<span><?php esc_html_e( 'days', 'universal-replace-engine' ); ?></span>
							<p class="description">
								<?php esc_html_e( 'Automatically delete backups older than this many days (1-30). Default: 7', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Feature Toggles -->
		<div class="ure-card">
			<h2><?php esc_html_e( 'Feature Toggles', 'universal-replace-engine' ); ?></h2>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Enable Logging -->
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Operation Logging', 'universal-replace-engine' ); ?>
						</th>
						<td>
							<label>
								<input
									type="checkbox"
									name="ure_settings[enable_logging]"
									value="1"
									<?php checked( $settings['enable_logging'] ); ?>
								/>
								<?php esc_html_e( 'Enable detailed logging for all operations', 'universal-replace-engine' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Logs are stored in the database and can be viewed in the History tab.', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>

					<!-- AJAX Processing -->
					<tr>
						<th scope="row">
							<?php esc_html_e( 'AJAX Progress Bar', 'universal-replace-engine' ); ?>
						</th>
						<td>
							<label>
								<input
									type="checkbox"
									name="ure_settings[ajax_processing]"
									value="1"
									<?php checked( $settings['ajax_processing'] ); ?>
								/>
								<?php esc_html_e( 'Enable real-time progress updates (recommended)', 'universal-replace-engine' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Shows a live progress bar during long operations. Disable if you experience issues.', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>

					<!-- Show Warnings -->
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Safety Warnings', 'universal-replace-engine' ); ?>
						</th>
						<td>
							<label>
								<input
									type="checkbox"
									name="ure_settings[show_warnings]"
									value="1"
									<?php checked( $settings['show_warnings'] ); ?>
								/>
								<?php esc_html_e( 'Show safety warnings and confirmations', 'universal-replace-engine' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Recommended to keep enabled to prevent accidental data loss.', 'universal-replace-engine' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Save Buttons -->
		<p class="submit">
			<?php submit_button( __( 'Save Settings', 'universal-replace-engine' ), 'primary', 'submit', false ); ?>
			<button type="submit" name="ure_action" value="reset_settings" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all settings to defaults?', 'universal-replace-engine' ); ?>')">
				<?php esc_html_e( 'Reset to Defaults', 'universal-replace-engine' ); ?>
			</button>
		</p>
	</form>

	<!-- System Info -->
	<div class="ure-card">
		<h2><?php esc_html_e( 'System Information', 'universal-replace-engine' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Plugin Version', 'universal-replace-engine' ); ?></strong></td>
					<td><?php echo esc_html( URE_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'WordPress Version', 'universal-replace-engine' ); ?></strong></td>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'PHP Version', 'universal-replace-engine' ); ?></strong></td>
					<td><?php echo esc_html( phpversion() ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Memory Limit', 'universal-replace-engine' ); ?></strong></td>
					<td><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Max Execution Time', 'universal-replace-engine' ); ?></strong></td>
					<td><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?> seconds</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Pro Status', 'universal-replace-engine' ); ?></strong></td>
					<td>
						<?php if ( $is_pro ) : ?>
							<span class="ure-badge ure-badge-success"><?php esc_html_e( 'Active', 'universal-replace-engine' ); ?></span>
						<?php else : ?>
							<span class="ure-badge ure-badge-default"><?php esc_html_e( 'Free Version', 'universal-replace-engine' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<style>
.ure-settings-page .ure-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	margin: 20px 0;
	padding: 20px;
}

.ure-settings-page .ure-card h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}

.ure-settings-page .ure-badge {
	display: inline-block;
	padding: 2px 8px;
	font-size: 11px;
	font-weight: 600;
	line-height: 1.5;
	border-radius: 3px;
	margin-left: 8px;
}

.ure-settings-page .ure-badge-pro {
	background: #7e3af2;
	color: #fff;
}

.ure-settings-page .ure-badge-success {
	background: #0e9f6e;
	color: #fff;
}

.ure-settings-page .ure-badge-default {
	background: #6b7280;
	color: #fff;
}
</style>
