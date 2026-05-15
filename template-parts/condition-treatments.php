<?php
/**
 * Condition treatments list section.
 *
 * Child override preserving parent query/consultation behavior,
 * rendered with child product cards.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
$term_id = isset($args['term_id']) ? (int) $args['term_id'] : 0;

if (!$term instanceof WP_Term || $term_id < 1) {
	return;
}

$products = get_posts(
	array(
		'post_type'   => 'product',
		'numberposts' => -1,
		'tax_query'   => array(
			array(
				'taxonomy' => 'condition',
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	)
);

if (empty($products)) {
	return;
}
?>
<section class="page-section condition-products">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<?php if (!has_consultation_for_condition($term->slug)) : ?>
					<h2 class="stitle"><?php echo esc_html(sprintf(__('Treatments for %s', 'woocommerce'), $term->name)); ?></h2>
					<?php if (get_field('short_description', $term)) : ?>
						<span class="shop-count"><?php echo esc_html(wp_strip_all_tags(get_field('short_description', $term))); ?></span>
					<?php endif; ?>
				<?php else : ?>
					<h2 class="stitle"><?php esc_html_e('Choose your preferred treatments', 'woocommerce'); ?></h2>
					<span class="shop-count"><?php esc_html_e('Thanks for completing our consultation. Choose a treatment below and add it to your prescription bag to checkout.', 'woocommerce'); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="shop-grid">
			<?php foreach ($products as $product_post) : ?>
				<?php
				$shop_product = wc_get_product($product_post->ID);
				if (!$shop_product instanceof WC_Product) {
					continue;
				}
				get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $shop_product));
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
