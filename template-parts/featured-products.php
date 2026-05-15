<?php
/**
 * Featured products section - child theme design override.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$featured_items = function_exists('get_field') ? get_field('top_featured_products', $front_page_id) : array();
if (!is_array($featured_items) || empty($featured_items)) {
	return;
}

$section_heading = function_exists('get_field') ? (string) get_field('feat_prod_section_heading', $front_page_id) : '';
$heading = function_exists('get_field') ? (string) get_field('featured_products_heading', $front_page_id) : '';
$description = function_exists('get_field') ? (string) get_field('featured_product_short_info', $front_page_id) : '';
?>
<section class="page-section trimvia-featured-products">
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
			<?php foreach ($featured_items as $featured_item) : ?>
				<?php
				$featured_product = null;
				if ($featured_item instanceof WC_Product) {
					$featured_product = $featured_item;
				} elseif ($featured_item instanceof WP_Post) {
					$featured_product = wc_get_product($featured_item->ID);
				} else {
					$featured_product = wc_get_product((int) $featured_item);
				}

				if (!$featured_product instanceof WC_Product) {
					continue;
				}

				get_template_part(
					'template-parts/trimvia',
					'shop-product-card',
					array(
						'product' => $featured_product,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
