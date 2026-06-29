<?php
/**
 * Template Name: All Conditions
 * Template Post Type: page
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$title_before = get_field('all_cond_hero_title_before');
if (!is_string($title_before) || '' === $title_before) {
	$title_before = __('Our', 'woocommerce');
}
$title_accent = get_field('all_cond_hero_title_accent');
if (!is_string($title_accent) || '' === $title_accent) {
	$title_accent = __('Conditions', 'woocommerce');
}
$hero_description = get_field('all_cond_hero_description');
$badge_rows       = get_field('all_cond_hero_badges');
$show_count_badge = (bool) get_field('all_cond_show_count_badge');
$count_suffix     = get_field('all_cond_count_badge_suffix');
if (!is_string($count_suffix) || '' === $count_suffix) {
	$count_suffix = __('conditions with treatments', 'woocommerce');
}
$show_banner_form = (bool) get_field('all_cond_show_banner_form');

$conditions = get_terms(
	array(
		'taxonomy'   => 'condition',
		'hide_empty' => false,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

$condition_cards = array();
$visible_count   = 0;

if (!is_wp_error($conditions) && !empty($conditions)) {
	foreach ($conditions as $term) {
		$product_count = function_exists('trimvia_get_condition_visible_product_count')
			? trimvia_get_condition_visible_product_count($term)
			: (int) $term->count;
		if ($product_count < 1) {
			continue;
		}
		$visible_count++;
		$condition_cards[] = array(
			'term'  => $term,
			'count' => $product_count,
		);
	}
}

if (!is_string($hero_description) || '' === $hero_description) {
	$hero_description = __(
		'Browse clinically proven treatments by condition. Every prescription is reviewed and approved by a UK-registered pharmacist prescriber.',
		'woocommerce'
	);
}

$is_single_condition = (1 === $visible_count);
$hero_section_class  = 'trimvia-conditions-hero trimvia-conditions-hero--all section-pad';
if ($is_single_condition) {
	$hero_section_class .= ' trimvia-conditions-hero--all-single';
}
?>
<section class="<?php echo esc_attr($hero_section_class); ?>">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="trimvia-conditions-hero__inner text-center">
			<h1 class="trimvia-conditions-hero__title">
				<span class="trimvia-conditions-hero__title-plain"><?php echo esc_html($title_before); ?></span>
				<span class="trimvia-conditions-hero__title-accent"><?php echo esc_html($title_accent); ?></span>
			</h1>
			<p class="trimvia-conditions-hero__desc"><?php echo wp_kses_post(nl2br($hero_description)); ?></p>

			<?php if (($show_count_badge && !$is_single_condition) || ($badge_rows && is_array($badge_rows))) : ?>
				<ul class="trimvia-conditions-hero__badges" aria-label="<?php esc_attr_e('Highlights', 'woocommerce'); ?>">
					<?php if ($show_count_badge) : ?>
						<li class="trimvia-conditions-hero__badge">
							<?php
							printf(
								/* translators: 1: number of terms, 2: suffix phrase */
								esc_html(_nx('%1$d %2$s', '%1$d %2$s', (int) $visible_count, 'conditions count badge', 'woocommerce')),
								(int) $visible_count,
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

			<?php if ($show_banner_form) : ?>
				<div class="trimvia-conditions-hero__legacy-form">
					<?php echo do_shortcode('[BannerForm]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if (!empty($condition_cards)) : ?>
	<section class="trimvia-condition-listings section-pad<?php echo $is_single_condition ? ' trimvia-condition-listings--single' : ''; ?>" id="trimvia-all-conditions-list">
		<div class="container">
			<div class="trimvia-condition-listings__grid">
				<?php foreach ($condition_cards as $condition_card) : ?>
					<?php
					$term          = isset($condition_card['term']) && $condition_card['term'] instanceof WP_Term ? $condition_card['term'] : null;
					$product_count = isset($condition_card['count']) ? (int) $condition_card['count'] : 0;
					if (!$term) {
						continue;
					}
					$term_link     = get_term_link($term);
					$term_desc     = term_description($term->term_id, 'condition');
					$featured_id   = function_exists('get_field') ? (int) get_field('featured_image', $term) : 0;
					$desc_words    = $is_single_condition ? 36 : 22;
					if (is_wp_error($term_link)) {
						continue;
					}
					?>
					<article
						class="trimvia-condition-card rv<?php echo $is_single_condition ? ' trimvia-condition-card--featured' : ''; ?>"
					>
						<a class="trimvia-condition-card__media" href="<?php echo esc_url($term_link); ?>">
							<?php if ($featured_id > 0) : ?>
								<?php
								echo wp_get_attachment_image(
									$featured_id,
									'large',
									false,
									array(
										'class'   => 'trimvia-condition-card__img',
										'loading' => 'lazy',
									)
								);
								?>
							<?php else : ?>
								<div class="trimvia-condition-card__media-placeholder" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
								</div>
							<?php endif; ?>
						</a>
						<div class="trimvia-condition-card__body">
							<div class="trimvia-condition-card__type"><?php esc_html_e('Condition', 'theme-woopm-child'); ?></div>
							<h2 class="trimvia-condition-card__title">
								<a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>
							</h2>
							<?php if ('' !== wp_strip_all_tags($term_desc)) : ?>
								<p class="trimvia-condition-card__desc"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($term_desc), $desc_words, '...')); ?></p>
							<?php endif; ?>
							<div class="trimvia-condition-card__footer">
								<?php if (!$is_single_condition) : ?>
									<span class="trimvia-condition-card__count">
										<?php
										printf(
											/* translators: %s: treatment count */
											esc_html(_n('%s treatment', '%s treatments', $product_count, 'theme-woopm-child')),
											esc_html(number_format_i18n($product_count))
										);
										?>
									</span>
								<?php else : ?>
									<span class="trimvia-condition-card__meta">
										<?php
										printf(
											/* translators: %s: treatment count */
											esc_html(_n('%s treatment available', '%s treatments available', $product_count, 'theme-woopm-child')),
											esc_html(number_format_i18n($product_count))
										);
										?>
									</span>
								<?php endif; ?>
								<a class="trimvia-condition-card__btn<?php echo $is_single_condition ? ' trimvia-condition-card__btn--lg' : ''; ?>" href="<?php echo esc_url($term_link); ?>">
									<?php echo esc_html($is_single_condition ? __('Explore treatments', 'theme-woopm-child') : __('View Treatments', 'woocommerce')); ?>
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
								</a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php else : ?>
	<section class="trimvia-condition-listings section-pad">
		<div class="container">
			<p class="trimvia-condition-listings__empty"><?php esc_html_e('No conditions with treatments are available yet.', 'theme-woopm-child'); ?></p>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
