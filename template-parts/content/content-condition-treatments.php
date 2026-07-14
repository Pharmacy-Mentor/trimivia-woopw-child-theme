<?php
/**
 * Condition term: Trimvia treatments layout (hero, how it works, products, about, FAQs).
 * Used by Page Template "Treatments" (query param) and taxonomy-condition.php archive.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
if (!$term instanceof WP_Term || 'condition' !== $term->taxonomy) {
	return;
}

$title_prefix = get_field('cond_hero_title_prefix', $term);
if (!is_string($title_prefix) || $title_prefix === '') {
	$title_prefix = __('Our', 'woocommerce');
}
$title_suffix = get_field('cond_hero_title_suffix', $term);
if (!is_string($title_suffix) || $title_suffix === '') {
	$title_suffix = __('Treatments', 'woocommerce');
}
$acf_intro = get_field('cond_hero_intro', $term);
if (is_string($acf_intro) && $acf_intro !== '') {
	$hero_intro_html = wpautop(wp_kses_post($acf_intro));
} else {
	$desc = term_description($term->term_id, 'condition');
	$hero_intro_html = $desc ? apply_filters('the_content', $desc) : '';
}
if ($hero_intro_html === '') {
	$hero_intro_html = '<p>' . esc_html__(
		'Browse our clinically proven treatments. Every prescription is reviewed and approved by a UK-registered pharmacist prescriber.',
		'woocommerce'
	) . '</p>';
}
$show_count_badge = (bool) get_field('cond_hero_show_count_badge', $term);
$count_suffix     = get_field('cond_hero_count_suffix', $term);
if (!is_string($count_suffix) || $count_suffix === '') {
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
$questionnaire_id = function_exists('get_field') ? get_field('questionnaire', $term) : null;
$show_consultation_cta = !empty($questionnaire_id);

$condition_products = function_exists('trimvia_get_condition_visible_products')
	? trimvia_get_condition_visible_products($term)
	: array();
$treatment_count = is_array($condition_products) ? count($condition_products) : 0;
if ($treatment_count < 1) {
	return;
}

$show_hero     = trimvia_condition_field_visible('cond_hero_visibility', $term);
$show_products = trimvia_condition_field_visible('cond_products_section_visibility', $term);
$show_about    = trimvia_condition_field_visible('cond_about_section_visibility', $term);
$show_faqs     = trimvia_condition_field_visible('cond_faqs_section_visibility', $term);
$show_order_steps = trimvia_condition_field_visible('cond_order_steps_visibility', $term);
$show_popular_categories = trimvia_condition_field_visible('cond_popular_categories_visibility', $term);

$show_products_intro_header = function_exists('get_field') ? (bool) get_field('cond_products_show_intro_header', $term) : false;

$cond_products_kicker    = '';
$cond_products_heading   = '';
$cond_products_desc_html = '';
if ($show_products_intro_header && function_exists('get_field')) {
	$cond_products_kicker = get_field('cond_products_kicker', $term);
	$cond_products_heading = get_field('cond_products_heading', $term);
	$cond_products_description_raw = get_field('cond_products_description', $term);
	if (!is_string($cond_products_kicker) || '' === trim($cond_products_kicker)) {
		$cond_products_kicker = __('Treatments', 'theme-woopm-child');
	}
	if (!is_string($cond_products_heading) || '' === trim($cond_products_heading)) {
		$cond_products_heading = __('Choose your treatment', 'theme-woopm-child');
	}
	if (is_string($cond_products_description_raw) && '' !== trim($cond_products_description_raw)) {
		$cond_products_desc_html = wpautop(wp_kses_post($cond_products_description_raw));
	} else {
		$term_short = get_field('short_description', $term);
		if (is_string($term_short) && '' !== trim($term_short)) {
			$cond_products_desc_html = wpautop(wp_kses_post($term_short));
		}
	}
}
?>

<?php if ($show_hero) : ?>
<section class="trimvia-conditions-hero trimvia-conditions-hero--treatment trimvia-conditions-hero--split<?php echo $hero_image_id > 0 ? '' : ' trimvia-conditions-hero--no-media'; ?> section-pad" data-term-id="<?php echo (int) $term->term_id; ?>">
	<div class="hero-noise"></div>
	<div class="container">
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
<?php endif; ?>

<?php
if ($show_order_steps) {
	get_template_part(
		'template-parts/order',
		'steps',
		array(
			'term' => $term,
		)
	);
}
?>

<?php if ($show_products) : ?>
	<section class="page-section trimvia-treatment-products condition-products" id="trimvia-treatment-products">
		<div class="container">
			<?php if ($show_products_intro_header) : ?>
			<div class="condition-products-section-head">
				<div class="stag"><?php echo esc_html($cond_products_kicker); ?></div>
				<h2 class="stitle"><?php echo esc_html($cond_products_heading); ?></h2>
				<?php if ('' !== $cond_products_desc_html) : ?>
					<div class="sdesc condition-products-desc"><?php echo $cond_products_desc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wpautop/kses ?></div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="shop-grid">
				<?php foreach ($condition_products as $shop_product) : ?>
					<?php
					if (!$shop_product instanceof WC_Product) {
						continue;
					}
					get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $shop_product));
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ($show_about && function_exists('get_field') && get_field('condition_group_content', $term)) : ?>
	<?php $condition_group = get_field('condition_group_content', $term); ?>
	<section class="how-sec section-pad trimvia-condition-about" id="about-condition">
		<div class="container">
			<div class="trimvia-condition-about__head rv">
				<div class="stag"><?php esc_html_e('About this condition', 'woocommerce'); ?></div>
				<h2 class="stitle"><?php echo esc_html(sprintf(__('About %s', 'woocommerce'), $term->name)); ?></h2>
			</div>
			<div class="trimvia-condition-about__blocks">
				<?php
				$block_index = 0;
				if (is_array($condition_group)) {
					foreach ($condition_group as $group_key => $content_group) {
						if (!is_string($group_key) || strpos($group_key, 'content_group_') !== 0 || !is_array($content_group)) {
							continue;
						}
						$content_group_description   = isset($content_group['content_description']) ? $content_group['content_description'] : '';
						$content_group_featured_img = isset($content_group['featured_image']) ? $content_group['featured_image'] : null;
						if (empty($content_group_description)) {
							continue;
						}
						$block_index++;
						$swap = ( $block_index % 2 === 0 );
						?>
					<div class="trimvia-condition-about__block rv<?php echo $swap ? ' trimvia-condition-about__block--reverse' : ''; ?>">
						<div class="trimvia-condition-about__media">
							<?php
							if ($content_group_featured_img) {
								if (is_array($content_group_featured_img) && !empty($content_group_featured_img['ID'])) {
									echo wp_get_attachment_image((int) $content_group_featured_img['ID'], 'full', false, array( 'class' => 'trimvia-condition-about__image', 'loading' => 'lazy' ));
								} elseif (is_array($content_group_featured_img) && !empty($content_group_featured_img['url'])) {
									echo '<img src="' . esc_url($content_group_featured_img['url']) . '" class="trimvia-condition-about__image" alt="" loading="lazy" />';
								} elseif (is_numeric($content_group_featured_img)) {
									echo wp_get_attachment_image((int) $content_group_featured_img, 'full', false, array( 'class' => 'trimvia-condition-about__image', 'loading' => 'lazy' ));
								}
							} else {
								$ph = function_exists('get_placeholder_image') ? get_placeholder_image() : '';
								if ($ph) {
									echo '<img src="' . esc_url($ph) . '" class="trimvia-condition-about__image" alt="" loading="lazy" />';
								}
							}
							?>
						</div>
						<div class="trimvia-condition-about__copy">
							<?php echo $content_group_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF WYSIWYG ?>
						</div>
					</div>
						<?php
					}
				}
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ($show_faqs) : ?>
	<?php get_template_part('template-parts/condition', 'faqs', array('term' => $term)); ?>
<?php endif; ?>

<?php if ($show_popular_categories) : ?>
	<?php get_template_part('template-parts/popular', 'categories', array('term' => $term)); ?>
<?php endif; ?>
