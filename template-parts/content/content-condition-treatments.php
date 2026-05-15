<?php
/**
 * Condition term: Trimvia treatments layout (hero, product grid, about, FAQs).
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
$search_placeholder = get_field('cond_hero_search_placeholder', $term);
if (!is_string($search_placeholder) || $search_placeholder === '') {
	$search_placeholder = __('Search treatments...', 'woocommerce');
}
$show_count_badge = (bool) get_field('cond_hero_show_count_badge', $term);
$count_suffix     = get_field('cond_hero_count_suffix', $term);
if (!is_string($count_suffix) || $count_suffix === '') {
	$count_suffix = __('treatments available', 'woocommerce');
}
$badge_rows = get_field('cond_hero_badges', $term);

$condition_products = array();
if (function_exists('wc_get_products')) {
	$condition_products = wc_get_products(
		array(
			'status'             => 'publish',
			'limit'              => -1,
			'catalog_visibility' => 'visible',
			'orderby'            => 'menu_order',
			'order'              => 'ASC',
			'tax_query'          => array(
				array(
					'taxonomy' => 'condition',
					'field'    => 'term_id',
					'terms'    => array((int) $term->term_id),
				),
			),
		)
	);
}
$treatment_count = is_array($condition_products) ? count($condition_products) : 0;

$show_hero    = trimvia_condition_field_visible('cond_hero_visibility', $term);
$show_products = trimvia_condition_field_visible('cond_products_section_visibility', $term);
$show_about   = trimvia_condition_field_visible('cond_about_section_visibility', $term);
$show_faqs    = trimvia_condition_field_visible('cond_faqs_section_visibility', $term);

$show_products_intro_header = function_exists('get_field') ? (bool) get_field('cond_products_show_intro_header', $term) : false;

$cond_products_kicker            = '';
$cond_products_heading           = '';
$cond_products_desc_html         = '';
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
<section class="trimvia-conditions-hero trimvia-conditions-hero--treatment section-pad" data-term-id="<?php echo (int) $term->term_id; ?>">
	<div class="container">
		<div class="trimvia-conditions-hero__inner text-center">
			<h1 class="trimvia-conditions-hero__title">
				<span class="trimvia-conditions-hero__title-plain"><?php echo esc_html($title_prefix . ' ' . $term->name . ' '); ?></span>
				<span class="trimvia-conditions-hero__title-accent"><?php echo esc_html($title_suffix); ?></span>
			</h1>
			<div class="trimvia-conditions-hero__desc"><?php echo $hero_intro_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

			<div class="trimvia-conditions-hero__search-wrap search-container">
				<label class="screen-reader-text" for="trimvia-treatments-filter"><?php esc_html_e('Filter treatments', 'woocommerce'); ?></label>
				<div class="trimvia-conditions-hero__search">
					<i class="fa-solid fa-magnifying-glass trimvia-conditions-hero__search-icon" aria-hidden="true"></i>
					<input
						id="trimvia-treatments-filter"
						type="search"
						class="trimvia-conditions-hero__search-input trimvia-condition-search-input"
						placeholder="<?php echo esc_attr($search_placeholder); ?>"
						autocomplete="off"
					/>
				</div>
			</div>

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
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ($show_products) : ?>
	<?php if (!empty($condition_products)) : ?>
	<section class="page-section trimvia-treatment-products condition-products" id="trimvia-treatment-products">
		<div class="container">
			<?php if ($show_products_intro_header) : ?>
			<div class="condition-products-section-head">
				<p class="condition-products-kicker"><?php echo esc_html($cond_products_kicker); ?></p>
				<h2 class="stitle condition-products-heading"><?php echo esc_html($cond_products_heading); ?></h2>
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
	<?php else : ?>
	<section class="section-padding">
		<div class="container text-center">
			<p><?php esc_html_e('No published products are linked to this condition yet.', 'woocommerce'); ?></p>
		</div>
	</section>
	<?php endif; ?>
<?php endif; ?>

<?php if ($show_about && function_exists('get_field') && get_field('condition_group_content', $term)) : ?>
	<?php $condition_group = get_field('condition_group_content', $term); ?>
	<section class="section-padding about-condition-content trimvia-condition-about">
		<div class="container">
			<div class="section-header-wrapper trimvia-condition-about__head">
				<div class="row align-items-center">
					<div class="col-lg-12 col-md-12 text-center">
						<div class="content-block trimvia-condition-about__title-wrap">
							<h5 class="trimvia-condition-about__kicker"><?php esc_html_e('About this condition', 'woocommerce'); ?></h5>
							<h2 class="section-title trimvia-condition-about__title"><?php echo esc_html(sprintf(__('About %s', 'woocommerce'), $term->name)); ?></h2>
						</div>
					</div>
				</div>
			</div>
			<div class="content-column-blocks mt-5">
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
					<div class="content-column trimvia-condition-about__block">
						<div class="row align-items-center">
							<div class="col-lg-6 col-md-6 col-sm-12 <?php echo $swap ? 'order-md-2 order-1' : ''; ?>">
								<div class="featured-image-wrapper trimvia-condition-about__media">
									<?php
									if ($content_group_featured_img) {
										if (is_array($content_group_featured_img) && !empty($content_group_featured_img['sizes']['large'])) {
											echo '<img src="' . esc_url($content_group_featured_img['sizes']['large']) . '" class="img-fluid" alt="" />';
										} elseif (is_numeric($content_group_featured_img)) {
											echo wp_get_attachment_image((int) $content_group_featured_img, 'large', false, array( 'class' => 'img-fluid' ));
										}
									} else {
										$ph = function_exists('get_placeholder_image') ? get_placeholder_image() : '';
										if ($ph) {
											echo '<img src="' . esc_url($ph) . '" class="img-fluid" alt="" />';
										}
									}
									?>
								</div>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-12 <?php echo $swap ? 'order-md-1 order-2' : ''; ?>">
								<div class="trimvia-condition-about__copy">
									<?php echo $content_group_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF WYSIWYG ?>
								</div>
							</div>
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

