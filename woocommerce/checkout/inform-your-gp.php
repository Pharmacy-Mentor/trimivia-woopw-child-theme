<?php
/**
 * Checkout GP form — Trimvia child override.
 *
 * WooPW expects `gp_surgery=current` for saved GP details. Parent/plugin flow stores
 * the account default in `_current_gp_details`, but returning customers may only
 * have GP saved on a previous order (`_order_consultation_gp_info`). This template
 * resolves both and exposes them as the first radio option.
 *
 * @package WooCommerce\Templates
 * @version 1.3.3
 */

defined('ABSPATH') || exit;

$is_optional = get_option('optional_gp_surgery');
$saved_gp    = function_exists('trimvia_get_saved_patient_gp') ? trimvia_get_saved_patient_gp() : null;
$has_saved_gp = is_array($saved_gp) && ('' !== trim((string) $saved_gp['name']) || '' !== trim((string) $saved_gp['address']));

$selected_gp_mode = 'nhs';
if (!empty($_POST['gp_surgery'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$selected_gp_mode = sanitize_text_field(wp_unslash($_POST['gp_surgery'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing
} elseif ($has_saved_gp) {
	$selected_gp_mode = 'current';
}

$show_current_panel = ('current' === $selected_gp_mode);
$show_search_panel  = ('nhs' === $selected_gp_mode);
$show_manual_panel  = ('manual' === $selected_gp_mode);

$saved_gp_name    = $has_saved_gp ? (string) $saved_gp['name'] : '';
$saved_gp_address = $has_saved_gp ? (string) $saved_gp['address'] : '';
$saved_gp_email   = $has_saved_gp ? (string) $saved_gp['email'] : '';
$saved_gp_post_id = ($has_saved_gp && !empty($saved_gp['post_id'])) ? absint($saved_gp['post_id']) : 0;
?>

<div class="woo-gp-form-wrapper trimvia-gp-form">
	<h2><?php esc_html_e('We need to inform your GP.', 'woocommerce'); ?></h2>

	<div class="woocommerce-form woocommerce-form-gp-surgery">
		<input type="hidden" name="inform_gp" value="yes" id="inform_gp" />

		<div class="gp-form-fields-wrapper">
			<div class="trimvia-gp-options woocommerce-gp-surgery-options" role="radiogroup" aria-label="<?php esc_attr_e('How would you like to provide GP details?', 'theme-woopm-child'); ?>">
				<?php if ($has_saved_gp) : ?>
					<label class="trimvia-gp-option">
						<input
							id="gp-surgery-option-current"
							type="radio"
							name="gp_surgery"
							value="current"
							<?php checked($selected_gp_mode, 'current'); ?>
						/>
						<span class="trimvia-gp-option__label">
							<?php
							if (isset($saved_gp['source']) && 'order' === $saved_gp['source']) {
								esc_html_e('Use previous GP details', 'theme-woopm-child');
							} else {
								esc_html_e('Use current GP details', 'theme-woopm-child');
							}
							?>
						</span>
					</label>
				<?php endif; ?>

				<label class="trimvia-gp-option">
					<input
						id="gp-surgery-option-nhs"
						type="radio"
						name="gp_surgery"
						value="nhs"
						<?php checked($selected_gp_mode, 'nhs'); ?>
					/>
					<span class="trimvia-gp-option__label"><?php esc_html_e('Search for GP Surgery', 'woocommerce'); ?></span>
				</label>

				<label class="trimvia-gp-option">
					<input
						id="gp-surgery-option-manual"
						type="radio"
						name="gp_surgery"
						value="manual"
						<?php checked($selected_gp_mode, 'manual'); ?>
					/>
					<span class="trimvia-gp-option__label"><?php esc_html_e('Enter Manually', 'woocommerce'); ?></span>
				</label>
			</div>

			<div class="trimvia-gp-panels">
				<?php if ($has_saved_gp) : ?>
					<div class="trimvia-gp-panel trimvia-gp-panel--current<?php echo $show_current_panel ? ' is-active' : ''; ?>" data-gp-panel="current"<?php echo $show_current_panel ? '' : ' hidden'; ?>>
						<?php
						if ($saved_gp_post_id) {
							$gp_args = array(
								'post_type'      => 'woo-gp-services',
								'post_status'    => array('draft', 'publish', 'private'),
								'posts_per_page' => 1,
								'p'              => $saved_gp_post_id,
							);
							$gp_details = new WP_Query($gp_args);
							if ($gp_details->have_posts()) {
								$prefix = gp_meta_prefix();
								while ($gp_details->have_posts()) {
									$gp_details->the_post();
									?>
									<div class="trimvia-gp-current-card">
										<div class="trimvia-gp-current-row">
											<span class="trimvia-gp-current-label"><?php esc_html_e('GP Surgery Name', 'woopw'); ?></span>
											<span class="trimvia-gp-current-value"><?php the_title(); ?></span>
										</div>
										<?php if (get_post_meta(get_the_ID(), $prefix . 'address', true)) : ?>
											<div class="trimvia-gp-current-row">
												<span class="trimvia-gp-current-label"><?php esc_html_e('GP Surgery Address', 'woopw'); ?></span>
												<span class="trimvia-gp-current-value"><?php echo esc_html(get_post_meta(get_the_ID(), $prefix . 'address', true)); ?></span>
											</div>
										<?php endif; ?>
									</div>
									<?php
								}
							}
							wp_reset_postdata();
						} else {
							?>
							<div class="trimvia-gp-current-card">
								<?php if ('' !== trim($saved_gp_name)) : ?>
									<div class="trimvia-gp-current-row">
										<span class="trimvia-gp-current-label"><?php esc_html_e('GP Surgery Name', 'woopw'); ?></span>
										<span class="trimvia-gp-current-value"><?php echo esc_html($saved_gp_name); ?></span>
									</div>
								<?php endif; ?>
								<?php if ('' !== trim($saved_gp_address)) : ?>
									<div class="trimvia-gp-current-row">
										<span class="trimvia-gp-current-label"><?php esc_html_e('GP Surgery Address', 'woopw'); ?></span>
										<span class="trimvia-gp-current-value"><?php echo esc_html($saved_gp_address); ?></span>
									</div>
								<?php endif; ?>
							</div>
							<?php
						}
						?>
					</div>
				<?php endif; ?>

				<div class="trimvia-gp-panel trimvia-gp-panel--search<?php echo $show_search_panel ? ' is-active' : ''; ?>" data-gp-panel="nhs"<?php echo $show_search_panel ? '' : ' hidden'; ?>>
					<p class="form-row form-row-wide trimvia-gp-field">
						<label for="gp-surgery-selector"><?php esc_html_e('Search for GP Surgery', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<select id="gp-surgery-selector" class="gp-surgery-selector"<?php echo $show_search_panel ? '' : ' disabled'; ?>>
							<option value=""><?php esc_html_e('Select GP Surgery', 'theme-woopm-child'); ?></option>
						</select>
					</p>
				</div>

				<div class="trimvia-gp-panel trimvia-gp-panel--manual<?php echo $show_manual_panel ? ' is-active' : ''; ?>" data-gp-panel="manual"<?php echo $show_manual_panel ? '' : ' hidden'; ?>>
					<p class="form-row form-row-wide trimvia-gp-field">
						<label for="gp-surgery-name"><?php esc_html_e('Please enter your GP Surgery Name', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text"
							name="gp_surgery_name"
							id="gp-surgery-name"
							placeholder="<?php esc_attr_e('Please enter GP Surgery Name', 'theme-woopm-child'); ?>"
							value="<?php echo !empty($_POST['gp_surgery_name']) ? esc_attr(wp_unslash($_POST['gp_surgery_name'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
							<?php echo ('current' === $selected_gp_mode) ? 'disabled' : ''; ?>
						/>
					</p>
					<p class="form-row form-row-wide trimvia-gp-field">
						<label for="gp-surgery-full-address"><?php esc_html_e('Please enter your GP Surgery full address & postcode', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text"
							id="gp-surgery-full-address"
							data-trimvia-gp-address-input
							placeholder="<?php esc_attr_e('Please enter your GP Surgery full address & postcode', 'theme-woopm-child'); ?>"
							value="<?php echo !empty($_POST['gp_surgery_address']) && 'manual' === $selected_gp_mode ? esc_attr(wp_unslash($_POST['gp_surgery_address'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
							<?php echo ('manual' === $selected_gp_mode) ? '' : 'disabled'; ?>
						/>
					</p>
				</div>
			</div>

			<input type="hidden" name="gp_surgery_email" id="gp-surgery-email" value="<?php echo !empty($_POST['gp_surgery_email']) ? esc_attr(wp_unslash($_POST['gp_surgery_email'])) : esc_attr($saved_gp_email); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
			<input
				type="hidden"
				name="gp_surgery_address"
				id="gp-surgery-address-submit"
				value="<?php echo !empty($_POST['gp_surgery_address']) ? esc_attr(wp_unslash($_POST['gp_surgery_address'])) : esc_attr($saved_gp_address); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
			/>

			<p class="form-row form-row-wide consent-checkbox trimvia-gp-consent">
				<input
					id="gp-surgery-consent"
					type="checkbox"
					name="gp_surgery_consent"
					value="<?php echo esc_attr(sprintf('I give my consent for %s to contact my GP surgery on my behalf', get_bloginfo('name'))); ?>"
					<?php checked(!empty($_POST['gp_surgery_consent'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
				/>
				<label for="gp-surgery-consent">
					<?php
					printf(
						/* translators: %s: site name */
						esc_html__('I give my consent for %s to contact my GP surgery on my behalf.', 'theme-woopm-child'),
						'<strong>' . esc_html(get_bloginfo('name')) . '</strong>'
					);
					?>
					<?php if (!$is_optional) : ?>
						<span class="required">*</span>
					<?php endif; ?>
				</label>
			</p>
		</div>
	</div>
</div>
