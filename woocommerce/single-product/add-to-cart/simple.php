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

$is_prescription_product = get_field('is_prescription_product');

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

		<div class="woocommerce-variation-add-to-cart variations_button quantity-cartbtn">
			<div class="woo-cart-form-meta-wrapper">
				<?php
				do_action('woocommerce_before_add_to_cart_quantity');

				woocommerce_quantity_input(
					array(
						'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
						'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
						'input_value' => isset($_POST['quantity'])
							? wc_stock_amount(wp_unslash($_POST['quantity']))
							: $product->get_min_purchase_quantity(),
					)
				);

				do_action('woocommerce_after_add_to_cart_quantity');
				?>
			</div>

			<div class="woo-variation-action-wrap">
				<div class="woo-cart-form-act-wrapper">
					<?php
					$term_link = '';
					$terms = wp_get_post_terms($product->get_id(), 'condition');
					if (! empty($terms)) {
						$term_link = get_term_link($terms[0]);
					}

					if ($is_prescription_product == 'yes' && $term_link != '') {
						if (! empty(WC()->session->get('cflp_form_data'))) { ?>
							<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button theme-btn-primary alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
								<?php echo esc_html($product->single_add_to_cart_text()); ?>
							</button>
						<?php } else { ?>
							<a class="single_add_to_cart_button theme-btn-primary" href="<?php echo esc_url($term_link); ?>">
								Start Assessment
							</a>
						<?php } ?>
					<?php } else { ?>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button theme-btn-primary alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
							<?php echo esc_html($product->single_add_to_cart_text()); ?>
						</button>
					<?php } ?>
				</div>
			</div>
		</div>
	</form>

	<?php do_action('woocommerce_after_add_to_cart_form'); ?>
<?php endif; ?>