<?php
/**
 * Condition archive hero — Trimvia design, parent consultation gating unchanged.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
if (!$term instanceof WP_Term) {
	return;
}

$title_prefix = get_field('cond_hero_title_prefix', $term);
if (!is_string($title_prefix) || '' === $title_prefix) {
	$title_prefix = __('Our', 'woocommerce');
}
$title_suffix = get_field('cond_hero_title_suffix', $term);
if (!is_string($title_suffix) || '' === $title_suffix) {
	$title_suffix = __('Treatments', 'woocommerce');
}

$acf_intro = get_field('cond_hero_intro', $term);
if (is_string($acf_intro) && '' !== $acf_intro) {
	$hero_intro_html = wpautop(wp_kses_post($acf_intro));
} else {
	$desc = term_description($term->term_id, 'condition');
	$hero_intro_html = $desc ? apply_filters('the_content', $desc) : '';
}
if ('' === $hero_intro_html) {
	$hero_intro_html = '<p>' . esc_html__(
		'Browse our clinically proven treatments. Every prescription is reviewed and approved by a UK-registered pharmacist prescriber.',
		'woocommerce'
	) . '</p>';
}

$show_count_badge = (bool) get_field('cond_hero_show_count_badge', $term);
$count_suffix     = get_field('cond_hero_count_suffix', $term);
if (!is_string($count_suffix) || '' === $count_suffix) {
	$count_suffix = __('treatments available', 'woocommerce');
}
$badge_rows = get_field('cond_hero_badges', $term);

$hero_image_id = 0;
if (function_exists('get_field')) {
	$hero_image_field = get_field('cond_hero_image', $term);
	if (is_array($hero_image_field) && !empty($hero_image_field['ID'])) {
		$hero_image_id = (int) $hero_image_field['ID'];
	} elseif (is_numeric($hero_image_field)) {
		$hero_image_id = (int) $hero_image_field;
	}
	if ($hero_image_id < 1) {
		$hero_image_id = (int) get_field('featured_image', $term);
	}
}

$consultation_url = function_exists('trimvia_get_consultation_url')
	? trimvia_get_consultation_url($term->slug)
	: home_url('/consultation/?condition-slug=' . rawurlencode($term->slug));
$questionnaire_id      = function_exists('get_field') ? get_field('questionnaire', $term) : null;
$show_consultation_cta = !empty($questionnaire_id);

$treatment_count = function_exists('trimvia_get_condition_visible_product_count')
	? trimvia_get_condition_visible_product_count($term)
	: 0;
?>
<section class="trimvia-conditions-hero trimvia-conditions-hero--treatment trimvia-conditions-hero--split<?php echo $hero_image_id > 0 ? '' : ' trimvia-conditions-hero--no-media'; ?> section-pad" data-term-id="<?php echo (int) $term->term_id; ?>">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="trimvia-conditions-hero__back rv">
			<a href="<?php echo esc_url(home_url('/all-conditions/')); ?>" class="trimvia-conditions-hero__back-link">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
				<?php esc_html_e('Back to Conditions', 'woocommerce'); ?>
			</a>
		</div>
		<div class="trimvia-conditions-hero__grid">
			<div class="trimvia-conditions-hero__content">
				<h1 class="trimvia-conditions-hero__title">
					<span class="trimvia-conditions-hero__title-plain"><?php echo esc_html($title_prefix . ' ' . $term->name . ' '); ?></span>
					<span class="trimvia-conditions-hero__title-accent"><?php echo esc_html($title_suffix); ?></span>
				</h1>
				<div class="trimvia-conditions-hero__desc"><?php echo $hero_intro_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

				<?php if ($show_count_badge || ($badge_rows && is_array($badge_rows))) : ?>
					<ul class="trimvia-conditions-hero__badges" aria-label="<?php esc_attr_e('Highlights', 'woocommerce'); ?>">
						<?php if ($show_count_badge) : ?>
							<li class="trimvia-conditions-hero__badge">
								<?php
								printf(
									/* translators: 1: number of products, 2: suffix e.g. "treatments available" */
									esc_html(_x('%1$d %2$s', 'treatment count badge', 'woocommerce')),
									(int) $treatment_count,
									esc_html($count_suffix)
								);
								?>
							</li>
						<?php endif; ?>
						<?php
						if ($badge_rows && is_array($badge_rows)) :
							foreach ($badge_rows as $row) {
								if (empty($row['text'])) {
									continue;
								}
								echo '<li class="trimvia-conditions-hero__badge">';
								echo wp_kses_post($row['text']);
								echo '</li>';
							}
						endif;
						?>
					</ul>
				<?php endif; ?>

				<?php if ($show_consultation_cta) : ?>
					<div class="trimvia-conditions-hero__actions">
						<a href="<?php echo esc_url($consultation_url); ?>" class="btn-cta btn-pulse trimvia-conditions-hero__cta">
							<?php esc_html_e('Start Consultation', 'theme-woopm-child'); ?>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
						</a>
					</div>
					<?php if (get_field('banner_content_tagline', $term)) : ?>
						<div class="trimvia-conditions-hero__tagline">
							<?php echo wpautop(wp_kses_post(get_field('banner_content_tagline', $term))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php if ($hero_image_id > 0) : ?>
				<div class="trimvia-conditions-hero__media">
					<div class="trimvia-conditions-hero__media-frame">
						<?php
						echo wp_get_attachment_image(
							$hero_image_id,
							'full',
							false,
							array(
								'class'   => 'trimvia-conditions-hero__image',
								'loading' => 'eager',
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
