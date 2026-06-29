<?php

/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

global $product;

// Ensure $product is an object
if (! is_object($product)) {
	$product = wc_get_product(get_the_ID());
}

if (! $product || ! $product->is_purchasable()) {
	return;
}

// Show stock message (e.g. "Out of stock").
//echo wc_get_stock_html( $product ); // WPCS: XSS ok.

// ✅ Only show the form if the product is in stock.
if ($product->is_in_stock()) : ?>

	<?php do_action('woocommerce_before_add_to_cart_form'); ?>

	<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action('woocommerce_before_add_to_cart_button'); ?>

		<div class="woo-cart-form-wrapper">
			<div class="woo-cart-form-meta-wrapper">
				<?php do_action('woocommerce_single_product_meta_wrapper_content'); ?>
			</div>
		</div>

		<?php do_action('woocommerce_before_add_to_cart_quantity_wrapper'); ?>

		<?php
		$show_assessment_cta = function_exists('trimvia_product_should_show_assessment_cta')
			&& trimvia_product_should_show_assessment_cta($product);
		$show_quantity       = !$show_assessment_cta && function_exists('trimvia_should_show_single_product_quantity')
			? trimvia_should_show_single_product_quantity($product)
			: !$show_assessment_cta;
		$quantity_value      = function_exists('trimvia_get_single_product_quantity_value')
			? trimvia_get_single_product_quantity_value($product)
			: 1;
		$cart_actions_class  = 'woocommerce-variation-add-to-cart variations_button quantity-cartbtn trimvia-product-cart-actions';
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
						<span class="trimvia-product-qty-label"><?php esc_html_e('Qty', 'theme-woopm-child'); ?></span>
						<?php
						do_action('woocommerce_before_add_to_cart_quantity');

						woocommerce_quantity_input(
							array(
								'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
								'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
								'input_value' => $quantity_value,
							)
						);

						do_action('woocommerce_after_add_to_cart_quantity');
						?>
					</div>
				<?php else : ?>
					<input type="hidden" name="quantity" value="<?php echo esc_attr((string) $quantity_value); ?>" />
				<?php endif; ?>

				<div class="woo-variation-action-wrap">
					<div class="woo-cart-form-act-wrapper">
						<?php
						if (function_exists('trimvia_render_single_product_cart_button')) {
							trimvia_render_single_product_cart_button($product, 'simple');
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</form>

	<?php do_action('woocommerce_after_add_to_cart_form'); ?>
<?php endif; ?>