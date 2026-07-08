<?php
/**
 * Checkout order review — Trimvia summary design.
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
?>

<div class="trimvia-review-order-summary woocommerce-checkout-review-order-table">
	<div class="order-summary-items">
		<?php do_action('woocommerce_review_order_before_cart_contents'); ?>

		<?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
			<?php
			$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

			if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0 || !apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
				continue;
			}

			$product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
			$item_data = wc_get_formatted_cart_item_data($cart_item);
			?>
			<div class="summary-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
				<div>
					<div class="summary-item-name"><?php echo wp_kses_post($product_name); ?></div>
					<div class="summary-item-qty">
						<?php
						echo apply_filters(
							'woocommerce_checkout_cart_item_quantity',
							sprintf(
								/* translators: %s: product quantity. */
								esc_html__('Quantity: %s', 'theme-woopm-child'),
								esc_html($cart_item['quantity'])
							),
							$cart_item,
							$cart_item_key
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
					<?php if ('' !== $item_data) : ?>
						<div class="summary-item-meta"><?php echo wp_kses_post($item_data); ?></div>
					<?php endif; ?>
				</div>
				<div class="summary-item-price">
					<?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		<?php endforeach; ?>

		<?php do_action('woocommerce_review_order_after_cart_contents'); ?>
	</div>

	<div class="summary-lines">
		<div class="summary-line cart-subtotal">
			<span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
			<span><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php if (WC()->cart->needs_shipping()) : ?>
			<?php
			$shipping_label = __('Delivery', 'theme-woopm-child');
			$shipping_cost_html = wp_kses_post(WC()->cart->get_cart_shipping_total());
			$is_free = (float) WC()->cart->get_shipping_total() <= 0;

			// WC()->shipping is a magic property (no __isset), so isset() on it is always false; call the method.
			if (function_exists('WC') && WC()->shipping()) {
				$chosen_shipping_methods = WC()->session ? WC()->session->get('chosen_shipping_methods') : array();
				$packages = WC()->shipping()->get_packages();
				foreach ($packages as $i => $package) {
					$chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
					if ($chosen_method && isset($package['rates'][$chosen_method])) {
						$rate = $package['rates'][$chosen_method];
						$shipping_label = $rate->get_label();

						$cost = (float) $rate->cost;
						if ($rate->taxes && WC()->cart->display_prices_including_tax()) {
							$cost += array_sum($rate->taxes);
						}
						$shipping_cost_html = $cost > 0 ? wc_price($cost) : __('Free!', 'theme-woopm-child');
						$is_free = $cost <= 0;
						break;
					}
				}
			}
			?>
			<div class="summary-line shipping-total<?php echo $is_free ? ' free' : ''; ?>">
				<span><?php echo esc_html($shipping_label); ?></span>
				<span><?php echo wp_kses_post($shipping_cost_html); ?></span>
			</div>
		<?php endif; ?>

		<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
			<div class="summary-line cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
				<span><?php wc_cart_totals_coupon_label($coupon); ?></span>
				<span><?php wc_cart_totals_coupon_html($coupon); ?></span>
			</div>
		<?php endforeach; ?>

		<?php foreach (WC()->cart->get_fees() as $fee) : ?>
			<div class="summary-line fee">
				<span><?php echo esc_html($fee->name); ?></span>
				<span><?php wc_cart_totals_fee_html($fee); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
					<div class="summary-line tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
						<span><?php echo esc_html($tax->label); ?></span>
						<span><?php echo wp_kses_post($tax->formatted_amount); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="summary-line tax-total">
					<span><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
					<span><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_review_order_before_order_total'); ?>

		<div class="summary-line total order-total">
			<span><?php esc_html_e('Total', 'woocommerce'); ?></span>
			<span><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action('woocommerce_review_order_after_order_total'); ?>
	</div>
</div>
