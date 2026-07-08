<?php
/**
 * Cart totals - custom Order Summary.
 *
 * @package theme-woopm-child
 * @version 2.3.6
 */

defined('ABSPATH') || exit;

$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '#';
?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">
	<aside class="order-summary rv">
		<div class="order-summary-head">
			<h3><?php esc_html_e('Order Summary', 'theme-woopm-child'); ?></h3>
			<p><?php echo esc_html(sprintf(_n('%s item in your basket', '%s items in your basket', $cart_count, 'theme-woopm-child'), number_format_i18n($cart_count))); ?></p>
		</div>
		<div class="order-summary-body">
			<div class="summary-line">
				<span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
				<span><?php wc_cart_totals_subtotal_html(); ?></span>
			</div>
			<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
				<div class="summary-line discount cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
					<span><?php wc_cart_totals_coupon_label($coupon); ?></span>
					<span><?php wc_cart_totals_coupon_html($coupon); ?></span>
				</div>
			<?php endforeach; ?>
			<?php if (WC()->cart->needs_shipping()) : ?>
				<?php
				$chosen_shipping_methods = WC()->session ? WC()->session->get('chosen_shipping_methods') : array();
				// WC()->shipping is a magic property (no __isset), so isset() on it is always false; call the method.
				$packages = WC()->shipping() ? WC()->shipping()->get_packages() : array();
				foreach ($packages as $i => $package) :
					$chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
					$rates = $package['rates'];
					
					if (count($rates) > 1) :
						?>
						<div class="summary-shipping-selection">
							<span class="shipping-selection-title"><?php esc_html_e('Delivery options', 'theme-woopm-child'); ?></span>
							<ul id="shipping_method_<?php echo esc_attr($i); ?>" class="woocommerce-shipping-methods trimvia-shipping-methods">
								<?php foreach ($rates as $rate) : ?>
									<li>
										<input type="radio" name="shipping_method[<?php echo esc_attr($i); ?>]" data-index="<?php echo esc_attr($i); ?>" id="shipping_method_<?php echo esc_attr($i); ?>_<?php echo esc_attr(sanitize_title($rate->id)); ?>" value="<?php echo esc_attr($rate->id); ?>" class="shipping_method" <?php checked($rate->id, $chosen_method); ?> />
										<label for="shipping_method_<?php echo esc_attr($i); ?>_<?php echo esc_attr(sanitize_title($rate->id)); ?>">
											<span class="method-name"><?php echo esc_html($rate->get_label()); ?></span>
											<span class="method-cost">
												<?php
												$cost = (float) $rate->cost;
												if ($rate->taxes && WC()->cart->display_prices_including_tax()) {
													$cost += array_sum($rate->taxes);
												}
												echo wp_kses_post($cost > 0 ? wc_price($cost) : __('Free!', 'theme-woopm-child'));
												?>
											</span>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php else : ?>
						<?php
						// Single rate display
						$rate = reset($rates);
						$shipping_label = $rate ? $rate->get_label() : __('Delivery', 'theme-woopm-child');
						$cost = $rate ? (float) $rate->cost : 0;
						if ($rate && $rate->taxes && WC()->cart->display_prices_including_tax()) {
							$cost += array_sum($rate->taxes);
						}
						$shipping_cost_html = $cost > 0 ? wc_price($cost) : __('Free!', 'theme-woopm-child');
						$is_free = $cost <= 0;
						?>
						<div class="summary-line<?php echo $is_free ? ' free' : ''; ?>">
							<span><?php echo esc_html($shipping_label); ?></span>
							<span><?php echo wp_kses_post($shipping_cost_html); ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php foreach (WC()->cart->get_fees() as $fee) : ?>
				<div class="summary-line">
					<span><?php echo esc_html($fee->name); ?></span>
					<span><?php wc_cart_totals_fee_html($fee); ?></span>
				</div>
			<?php endforeach; ?>
			<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
					<div class="summary-line tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
						<span><?php echo esc_html($tax->label); ?></span>
						<span><?php echo wp_kses_post($tax->formatted_amount); ?></span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<div class="summary-total">
				<span class="label"><?php esc_html_e('Total today', 'theme-woopm-child'); ?></span>
				<span class="amount"><?php wc_cart_totals_order_total_html(); ?></span>
			</div>
			<p class="summary-note"><?php esc_html_e('Charged after prescription approval where clinical review is required. You can review final details before payment.', 'theme-woopm-child'); ?></p>
			<a href="<?php echo esc_url($checkout_url); ?>" class="checkout-btn">
				<?php esc_html_e('Proceed to Checkout', 'woocommerce'); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>
		<div class="order-trust">
			<div class="order-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><?php esc_html_e('256-bit SSL encrypted checkout', 'theme-woopm-child'); ?></div>
			<div class="order-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg><?php esc_html_e('GPhC Registered Pharmacy', 'theme-woopm-child'); ?></div>
			<div class="order-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg><?php esc_html_e('Discreet delivery', 'theme-woopm-child'); ?></div>
			<div class="order-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><?php esc_html_e('UK-registered prescribers', 'theme-woopm-child'); ?></div>
		</div>
	</aside>
</div>
