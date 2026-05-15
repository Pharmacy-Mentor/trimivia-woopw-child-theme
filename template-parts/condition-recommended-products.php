<?php
/**
 * Condition recommended products section.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
$recommended_products = isset($args['recommended_products']) ? $args['recommended_products'] : array();
$page_id = 3098;

if (!$term instanceof WP_Term || empty($recommended_products)) {
	return;
}

$term_acf_id = $term->taxonomy . '_' . $term->term_id;

$recommend_product_heading = get_field('recommend_product_heading', $term_acf_id)
	?: get_field('recommend_product_heading', $page_id)
	?: __('Based on your consultation, we recommend the following products...', 'theme-woopm-child');

$recomment_product_content = get_field('recommend_product_content', $term_acf_id)
	?: get_field('recommend_product_content', $page_id)
	?: __('Thanks for completing your consultation. Choose a treatment below and add it to your prescription bag to check out.', 'theme-woopm-child');
?>
<section class="page-section trimvia-condition-recommended-products">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<h2 class="stitle"><?php echo esc_html(wp_strip_all_tags((string) $recommend_product_heading)); ?></h2>
				<span class="shop-count"><?php echo esc_html(wp_strip_all_tags((string) $recomment_product_content)); ?></span>
			</div>
		</div>
		<div class="shop-grid">
			<?php foreach ($recommended_products as $post) : setup_postdata($post); ?>
				<?php
				$recommended_product = wc_get_product($post->ID);
				if (!$recommended_product instanceof WC_Product) {
					continue;
				}
				get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $recommended_product));
				?>
			<?php endforeach; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
