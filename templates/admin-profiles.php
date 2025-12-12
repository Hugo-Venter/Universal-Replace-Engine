<?php
/**
 * Profiles Section Template
 *
 * Template for saved profiles management.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$profiles = URE_Profiles::get_all();
$has_profiles = ! empty( $profiles );
?>

<div class="ure-profiles-section">
	<h3>
		<?php esc_html_e( 'Saved Profiles', 'universal-replace-engine' ); ?>
		<span class="ure-badge ure-badge-info"><?php echo esc_html( count( $profiles ) ); ?></span>
	</h3>

	<?php if ( $has_profiles ) : ?>
		<div class="ure-profiles-list">
			<form method="post" action="">
				<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>

				<div class="ure-form-row">
					<label for="ure_profile_name">
						<?php esc_html_e( 'Load Profile:', 'universal-replace-engine' ); ?>
					</label>
					<select id="ure_profile_name" name="ure_profile_name" class="regular-text">
						<option value=""><?php esc_html_e( '-- Select Profile --', 'universal-replace-engine' ); ?></option>
						<?php foreach ( $profiles as $name => $profile ) : ?>
							<option value="<?php echo esc_attr( $name ); ?>">
								<?php echo esc_html( $name ); ?>
								<?php if ( isset( $profile['updated_at'] ) ) : ?>
									(<?php echo esc_html( human_time_diff( strtotime( $profile['updated_at'] ), current_time( 'timestamp' ) ) ); ?> ago)
								<?php endif; ?>
							</option>
						<?php endforeach; ?>
					</select>

					<button type="submit" name="ure_action" value="load_profile" class="button">
						<?php esc_html_e( 'Load', 'universal-replace-engine' ); ?>
					</button>
					<button type="submit" name="ure_action" value="delete_profile" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this profile?', 'universal-replace-engine' ); ?>')">
						<?php esc_html_e( 'Delete', 'universal-replace-engine' ); ?>
					</button>
				</div>
			</form>
		</div>

		<details class="ure-profiles-details">
			<summary><?php esc_html_e( 'View All Profiles', 'universal-replace-engine' ); ?></summary>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'universal-replace-engine' ); ?></th>
						<th><?php esc_html_e( 'Search', 'universal-replace-engine' ); ?></th>
						<th><?php esc_html_e( 'Replace', 'universal-replace-engine' ); ?></th>
						<th><?php esc_html_e( 'Post Types', 'universal-replace-engine' ); ?></th>
						<th><?php esc_html_e( 'Options', 'universal-replace-engine' ); ?></th>
						<th><?php esc_html_e( 'Updated', 'universal-replace-engine' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $profiles as $name => $profile ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $name ); ?></strong></td>
							<td><code><?php echo esc_html( $profile['search'] ); ?></code></td>
							<td><code><?php echo esc_html( $profile['replace'] ); ?></code></td>
							<td><?php echo esc_html( implode( ', ', $profile['post_types'] ) ); ?></td>
							<td>
								<?php if ( $profile['case_sensitive'] ) : ?>
									<span class="ure-badge ure-badge-default" title="<?php esc_attr_e( 'Case Sensitive', 'universal-replace-engine' ); ?>">Aa</span>
								<?php endif; ?>
								<?php if ( $profile['regex_mode'] ) : ?>
									<span class="ure-badge ure-badge-pro" title="<?php esc_attr_e( 'Regex Mode', 'universal-replace-engine' ); ?>">.*</span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( human_time_diff( strtotime( $profile['updated_at'] ), current_time( 'timestamp' ) ) ); ?> ago</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</details>
	<?php else : ?>
		<p class="description">
			<?php esc_html_e( 'No saved profiles yet. Complete a search below and click "Save Profile" to save it for future use.', 'universal-replace-engine' ); ?>
		</p>
	<?php endif; ?>

	<!-- Save Current Settings as Profile -->
	<div class="ure-save-profile" style="margin-top: 15px;">
		<h4><?php esc_html_e( 'Save Current Settings', 'universal-replace-engine' ); ?></h4>
		<form method="post" action="" class="ure-inline-form">
			<?php wp_nonce_field( 'ure_action', 'ure_nonce' ); ?>
			<input type="hidden" name="ure_action" value="save_profile" />
			<input
				type="text"
				name="ure_new_profile_name"
				class="regular-text"
				placeholder="<?php esc_attr_e( 'Profile name (e.g., "Domain Migration")', 'universal-replace-engine' ); ?>"
				required
			/>
			<button type="submit" class="button button-secondary">
				<?php esc_html_e( 'Save Current Settings as Profile', 'universal-replace-engine' ); ?>
			</button>
		</form>
	</div>
</div>

<style>
.ure-profiles-section {
	background: #f8f9fa;
	border: 1px solid #ddd;
	border-radius: 4px;
	padding: 15px;
	margin-bottom: 20px;
}

.ure-profiles-section h3 {
	margin-top: 0;
	display: flex;
	align-items: center;
	gap: 8px;
}

.ure-profiles-list .ure-form-row {
	display: flex;
	align-items: center;
	gap: 10px;
	margin-bottom: 10px;
}

.ure-profiles-list .ure-form-row label {
	margin: 0;
	font-weight: 600;
}

.ure-profiles-details {
	margin-top: 15px;
}

.ure-profiles-details summary {
	cursor: pointer;
	font-weight: 600;
	padding: 8px 0;
}

.ure-profiles-details summary:hover {
	color: #0073aa;
}

.ure-profiles-details table {
	margin-top: 10px;
}

.ure-inline-form {
	display: flex;
	align-items: center;
	gap: 10px;
}

.ure-badge-info {
	background: #0073aa;
	color: #fff;
	padding: 2px 8px;
	border-radius: 10px;
	font-size: 11px;
	font-weight: 600;
}

.ure-badge-default {
	background: #6b7280;
	color: #fff;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 600;
}
</style>
