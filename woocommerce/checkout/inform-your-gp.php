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
	<div class="trimvia-gp-head">
		<div class="trimvia-gp-head__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/><path d="M10 9h.01"/><path d="M14 9h.01"/></svg>
		</div>
		<div class="trimvia-gp-head__copy">
			<h2><?php esc_html_e('We need to inform your GP.', 'woocommerce'); ?></h2>
			<p><?php esc_html_e('Choose how you would like to share your GP surgery details for this order.', 'theme-woopm-child'); ?></p>
		</div>
	</div>

	<div class="form-alert trimvia-gp-review-alert">
		<strong><?php esc_html_e('Prescription review required:', 'theme-woopm-child'); ?></strong>
		<span><?php esc_html_e('Your order starts a pharmacist prescriber review. If extra information is needed, our team will contact you before dispatch.', 'theme-woopm-child'); ?></span>
	</div>

	<div class="woocommerce-form woocommerce-form-gp-surgery">
		<input type="hidden" name="inform_gp" value="yes" id="inform_gp" />

		<div class="gp-form-fields-wrapper">
			<div class="trimvia-gp-options woocommerce-gp-surgery-options" role="radiogroup" aria-label="<?php esc_attr_e('How would you like to provide GP details?', 'theme-woopm-child'); ?>">
				<?php if ($has_saved_gp) : ?>
					<label class="trimvia-gp-option<?php echo ('current' === $selected_gp_mode) ? ' is-selected' : ''; ?>">
						<input
							id="gp-surgery-option-current"
							type="radio"
							name="gp_surgery"
							value="current"
							<?php checked($selected_gp_mode, 'current'); ?>
						/>
						<span class="trimvia-gp-option__card">
							<span class="trimvia-gp-option__radio" aria-hidden="true"></span>
							<span class="trimvia-gp-option__icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
							</span>
							<span class="trimvia-gp-option__copy">
								<span class="trimvia-gp-option__title">
									<?php
									if (isset($saved_gp['source']) && 'order' === $saved_gp['source']) {
										esc_html_e('Use previous GP', 'theme-woopm-child');
									} else {
										esc_html_e('Use saved GP', 'theme-woopm-child');
									}
									?>
								</span>
								<span class="trimvia-gp-option__desc"><?php esc_html_e('Continue with the GP details we already have on file.', 'theme-woopm-child'); ?></span>
							</span>
						</span>
					</label>
				<?php endif; ?>

				<label class="trimvia-gp-option<?php echo ('nhs' === $selected_gp_mode) ? ' is-selected' : ''; ?>">
					<input
						id="gp-surgery-option-nhs"
						type="radio"
						name="gp_surgery"
						value="nhs"
						<?php checked($selected_gp_mode, 'nhs'); ?>
					/>
					<span class="trimvia-gp-option__card">
						<span class="trimvia-gp-option__radio" aria-hidden="true"></span>
						<span class="trimvia-gp-option__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
						</span>
						<span class="trimvia-gp-option__copy">
							<span class="trimvia-gp-option__title"><?php esc_html_e('Search GP surgery', 'theme-woopm-child'); ?></span>
							<span class="trimvia-gp-option__desc"><?php esc_html_e('Find your surgery in the NHS directory.', 'theme-woopm-child'); ?></span>
						</span>
					</span>
				</label>

				<label class="trimvia-gp-option<?php echo ('manual' === $selected_gp_mode) ? ' is-selected' : ''; ?>">
					<input
						id="gp-surgery-option-manual"
						type="radio"
						name="gp_surgery"
						value="manual"
						<?php checked($selected_gp_mode, 'manual'); ?>
					/>
					<span class="trimvia-gp-option__card">
						<span class="trimvia-gp-option__radio" aria-hidden="true"></span>
						<span class="trimvia-gp-option__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
						</span>
						<span class="trimvia-gp-option__copy">
							<span class="trimvia-gp-option__title"><?php esc_html_e('Enter manually', 'theme-woopm-child'); ?></span>
							<span class="trimvia-gp-option__desc"><?php esc_html_e('Type the surgery name and full address yourself.', 'theme-woopm-child'); ?></span>
						</span>
					</span>
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
					<p class="form-row form-row-wide trimvia-gp-field trimvia-gp-field--search">
						<label for="gp-surgery-selector"><?php esc_html_e('Search for your GP surgery', 'theme-woopm-child'); ?>&nbsp;<span class="required">*</span></label>
						<select id="gp-surgery-selector" class="gp-surgery-selector" aria-label="<?php esc_attr_e('Search for GP Surgery', 'woocommerce'); ?>"<?php echo $show_search_panel ? '' : ' disabled'; ?>>
							<option value=""><?php esc_html_e('Start typing to find your GP surgery', 'theme-woopm-child'); ?></option>
						</select>
					</p>
				</div>

				<div class="trimvia-gp-panel trimvia-gp-panel--manual<?php echo $show_manual_panel ? ' is-active' : ''; ?>" data-gp-panel="manual"<?php echo $show_manual_panel ? '' : ' hidden'; ?>>
					<p class="form-row form-row-wide trimvia-gp-field">
						<label for="gp-surgery-name"><?php esc_html_e('GP surgery name', 'theme-woopm-child'); ?>&nbsp;<span class="required">*</span></label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text"
							name="gp_surgery_name"
							id="gp-surgery-name"
							placeholder="<?php esc_attr_e('e.g. High Street Medical Centre', 'theme-woopm-child'); ?>"
							value="<?php echo !empty($_POST['gp_surgery_name']) ? esc_attr(wp_unslash($_POST['gp_surgery_name'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
							<?php echo ('current' === $selected_gp_mode) ? 'disabled' : ''; ?>
						/>
					</p>
					<p class="form-row form-row-wide trimvia-gp-field">
						<label for="gp-surgery-full-address"><?php esc_html_e('Full address & postcode', 'theme-woopm-child'); ?>&nbsp;<span class="required">*</span></label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text"
							id="gp-surgery-full-address"
							data-trimvia-gp-address-input
							placeholder="<?php esc_attr_e('Street, town, and postcode', 'theme-woopm-child'); ?>"
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
