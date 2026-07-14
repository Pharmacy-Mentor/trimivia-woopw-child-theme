<?php
/**
 * Popular categories section - child theme design override.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
if ($term instanceof WP_Term && function_exists('trimvia_condition_field_visible')) {
	if (!trimvia_condition_field_visible('cond_popular_categories_visibility', $term)) {
		return;
	}
}

$front_page_id = (int) get_option('page_on_front');
$popular_categories = function_exists('get_field') ? get_field('popular_categories', $front_page_id) : array();
if (!is_array($popular_categories) || empty($popular_categories)) {
	return;
}

$popular_categories = array_values(
	array_filter(
		$popular_categories,
		function ($popular_term) use ($term) {
			if (
				$term instanceof WP_Term
				&& $popular_term instanceof WP_Term
				&& (int) $term->term_id === (int) $popular_term->term_id
			) {
				return false;
			}

			return $popular_term instanceof WP_Term
				&& function_exists('trimvia_condition_has_visible_products')
				&& trimvia_condition_has_visible_products($popular_term);
		}
	)
);
if (empty($popular_categories)) {
	return;
}

$section_heading = function_exists('get_field') ? (string) get_field('cat_section_heading', $front_page_id) : '';
$heading = function_exists('get_field') ? (string) get_field('cat_heading', $front_page_id) : '';
$description = function_exists('get_field') ? (string) get_field('cat_short_description', $front_page_id) : '';
?>
<section class="page-section page-section--alt trimvia-popular-categories">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<?php if ('' !== trim($section_heading)) : ?>
					<p class="stag"><?php echo esc_html($section_heading); ?></p>
				<?php endif; ?>
				<?php if ('' !== trim($heading)) : ?>
					<h2 class="stitle"><?php echo esc_html($heading); ?></h2>
				<?php endif; ?>
				<?php if ('' !== trim($description)) : ?>
					<div class="shop-count"><?php echo esc_html(wp_strip_all_tags($description)); ?></div>
				<?php endif; ?>
			</div>
		</div>

		<div class="shop-grid">
			<?php foreach ($popular_categories as $popular_term) : ?>
				<?php
				if (!$popular_term instanceof WP_Term) {
					continue;
				}
				$term_link = get_term_link($popular_term->term_id);
				if (is_wp_error($term_link)) {
					continue;
				}
				$product_count          = function_exists('trimvia_get_condition_visible_product_count') ? trimvia_get_condition_visible_product_count($popular_term) : (int) $popular_term->count;
				$term_featured_image_id = function_exists('get_field') ? (int) get_field('featured_image', $popular_term) : 0;
				?>
				<article class="product-card rv trimvia-category-card">
					<a class="product-img" href="<?php echo esc_url($term_link); ?>">
						<?php if ($term_featured_image_id > 0) : ?>
							<div class="product-img-media">
								<?php echo wp_get_attachment_image($term_featured_image_id, 'full', false, array('loading' => 'lazy')); ?>
							</div>
						<?php else : ?>
							<div class="product-img-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
							</div>
						<?php endif; ?>
					</a>
					<div class="product-body">
						<div class="product-type"><?php esc_html_e('Condition', 'theme-woopm-child'); ?></div>
						<h3><a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($popular_term->name); ?></a></h3>
						<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) $popular_term->description), 26, '...')); ?></p>
						<div class="product-footer">
							<div class="trimvia-post-card-tax">
								<?php
								printf(
									/* translators: %s: term count */
									esc_html(_n('%s treatment', '%s treatments', $product_count, 'theme-woopm-child')),
									esc_html(number_format_i18n($product_count))
								);
								?>
							</div>
							<a class="btn-shop" href="<?php echo esc_url($term_link); ?>">
								<?php esc_html_e('View range', 'theme-woopm-child'); ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
							</a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
