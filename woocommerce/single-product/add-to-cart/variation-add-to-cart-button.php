<?php

/**
 * Single variation cart button
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

global $product;

$show_assessment_cta = function_exists('trimvia_product_should_show_assessment_cta')
	&& trimvia_product_should_show_assessment_cta($product);
$show_quantity       = !$show_assessment_cta && function_exists('trimvia_should_show_single_product_quantity')
	? trimvia_should_show_single_product_quantity($product)
	: !$show_assessment_cta;
$quantity_value      = function_exists('trimvia_get_single_product_quantity_value')
	? trimvia_get_single_product_quantity_value($product)
	: 1;
$quantity_max        = function_exists('trimvia_get_single_product_quantity_max')
	? trimvia_get_single_product_quantity_max($product)
	: $product->get_max_purchase_quantity();
$cart_actions_class  = 'woocommerce-variation-add-to-cart variations_button trimvia-product-cart-actions';
if ($show_quantity) {
	$cart_actions_class .= ' trimvia-product-cart-actions--has-qty';
}
if ($show_assessment_cta) {
	$cart_actions_class .= ' trimvia-product-cart-actions--assessment';
}
?>
<div class="<?php echo esc_attr($cart_actions_class); ?>">
	<div class="trimvia-product-cart-actions__row">
		<?php if ($show_quantity) : ?>
			<div class="trimvia-product-qty-row woo-cart-form-meta-wrapper">
				<?php do_action('woocommerce_before_add_to_cart_button'); ?>
				<?php do_action('woocommerce_before_add_to_cart_quantity'); ?>
				<span class="trimvia-product-qty-label"><?php esc_html_e('Qty', 'theme-woopm-child'); ?></span>
				<?php
				woocommerce_quantity_input(
					array(
						'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
						'max_value'   => apply_filters('woocommerce_quantity_input_max', $quantity_max, $product),
						'input_value' => $quantity_value,
					)
				);
				do_action('woocommerce_after_add_to_cart_quantity');
				?>
			</div>
		<?php else : ?>
			<?php do_action('woocommerce_before_add_to_cart_button'); ?>
			<input type="hidden" name="quantity" value="<?php echo esc_attr((string) $quantity_value); ?>" />
		<?php endif; ?>

		<div class="woo-variation-action-wrap">
			<?php
			if (function_exists('trimvia_render_single_product_cart_button')) {
				trimvia_render_single_product_cart_button($product, 'variation');
			}
			?>
		</div>
	</div>

	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
