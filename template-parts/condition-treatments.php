<?php
/**
 * Condition treatments list — parent WooPW product query, Trimvia product cards.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term    = isset($args['term']) ? $args['term'] : null;
$term_id = isset($args['term_id']) ? (int) $args['term_id'] : 0;

if (!$term instanceof WP_Term || $term_id < 1) {
	return;
}

$products = function_exists('trimvia_get_condition_visible_products')
	? trimvia_get_condition_visible_products($term)
	: array();

if (empty($products)) {
	return;
}

$consultation_completed = function_exists('has_consultation_for_condition')
	? has_consultation_for_condition($term->slug)
	: false;

$cond_products_kicker  = __('Treatments', 'theme-woopm-child');
$cond_products_heading = sprintf(
	/* translators: %s: condition name */
	__('Treatments for %s', 'woocommerce'),
	$term->name
);
$cond_products_desc_html = '';

if (function_exists('get_field')) {
	$acf_kicker = get_field('cond_products_kicker', $term);
	$acf_heading = get_field('cond_products_heading', $term);
	$acf_description = get_field('cond_products_description', $term);

	if (is_string($acf_kicker) && '' !== trim($acf_kicker)) {
		$cond_products_kicker = $acf_kicker;
	}
	if (is_string($acf_heading) && '' !== trim($acf_heading)) {
		$cond_products_heading = $acf_heading;
	} elseif (!$consultation_completed) {
		$cond_products_heading = sprintf(
			/* translators: %s: condition name */
			__('Treatments for %s', 'woocommerce'),
			$term->name
		);
	} else {
		$cond_products_heading = __('Choose your preferred treatments', 'woocommerce');
	}

	if (is_string($acf_description) && '' !== trim($acf_description)) {
		$cond_products_desc_html = wpautop(wp_kses_post($acf_description));
	} elseif (get_field('short_description', $term)) {
		$cond_products_desc_html = wpautop(wp_kses_post(get_field('short_description', $term)));
	} elseif ($consultation_completed) {
		$cond_products_desc_html = '<p>' . esc_html__(
			'Thanks for completing our consultation. Choose a treatment below and add it to your prescription bag to checkout.',
			'woocommerce'
		) . '</p>';
	}
}
?>
<section class="page-section trimvia-treatment-products condition-products" id="trimvia-treatment-products">
	<div class="container">
		<div class="condition-products-section-head rv">
			<div class="stag"><?php echo esc_html($cond_products_kicker); ?></div>
			<h2 class="stitle"><?php echo esc_html($cond_products_heading); ?></h2>
			<?php if ('' !== $cond_products_desc_html) : ?>
				<div class="sdesc condition-products-desc"><?php echo $cond_products_desc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>
		<div class="shop-grid">
			<?php
			foreach ($products as $shop_product) {
				if (!$shop_product instanceof WC_Product) {
					continue;
				}
				get_template_part(
					'template-parts/trimvia',
					'shop-product-card',
					array('product' => $shop_product)
				);
			}
			?>
		</div>
	</div>
</section>
