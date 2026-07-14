<?php
/**
 * Pay for order form — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 8.2.0
 */

defined('ABSPATH') || exit;

$totals      = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$order_items = $order->get_items();
$item_count  = $order->get_item_count();
?>

<section class="page-hero trimvia-checkout-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Complete Payment', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Complete Your Payment', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Review your order details below and complete payment to confirm your prescription order.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section trimvia-checkout-section">
	<div class="container">
		<div class="trimvia-wc-notices trimvia-order-pay-notices">
			<?php
			if (function_exists('woocommerce_output_all_notices')) {
				woocommerce_output_all_notices();
			}
			?>
		</div>

		<form id="order_review" method="post" class="trimvia-order-pay-form checkout-layout trimvia-order-pay-layout" action="<?php echo esc_url($order->get_checkout_payment_url()); ?>">
		<div class="checkout-form">
			<div class="form-section active trimvia-checkout-panel trimvia-checkout-panel--payment">
				<div class="form-section-header">
					<div class="form-section-num">1</div>
					<div>
						<h3><?php esc_html_e('Order Payment', 'theme-woopm-child'); ?></h3>
						<p><?php esc_html_e('Choose your payment method to complete this order.', 'theme-woopm-child'); ?></p>
					</div>
				</div>
				<div class="form-alert">
					<strong><?php esc_html_e('Pending order:', 'theme-woopm-child'); ?></strong>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: order number. */
							__('You are paying for order #%s. Once payment is complete, our team will continue processing your prescription.', 'theme-woopm-child'),
							$order->get_order_number()
						)
					);
					?>
				</div>

				<?php do_action('woocommerce_pay_order_before_payment'); ?>

				<div id="payment" class="woocommerce-checkout-payment">
					<?php if ($order->needs_payment()) : ?>
						<ul class="wc_payment_methods payment_methods methods">
							<?php
							if (!empty($available_gateways)) {
								foreach ($available_gateways as $gateway) {
									wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
								}
							} else {
								echo '<li>';
								wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
								echo '</li>';
							}
							?>
						</ul>
					<?php endif; ?>
					<div class="form-row place-order">
						<input type="hidden" name="woocommerce_pay" value="1" />

						<?php wc_get_template('checkout/terms.php'); ?>

						<?php do_action('woocommerce_pay_order_before_submit'); ?>

						<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

						<?php do_action('woocommerce_pay_order_after_submit'); ?>

						<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
					</div>
				</div>
			</div>
		</div>

		<aside class="order-summary trimvia-checkout-summary">
			<div class="order-summary-head">
				<h3><?php esc_html_e('Order Summary', 'theme-woopm-child'); ?></h3>
				<p><?php echo esc_html(sprintf(_n('%s item in your order', '%s items in your order', $item_count, 'theme-woopm-child'), number_format_i18n($item_count))); ?></p>
			</div>

			<div class="trimvia-review-order-summary trimvia-order-pay-summary">
				<div class="order-summary-items">
					<?php if (count($order_items) > 0) : ?>
						<?php foreach ($order_items as $item_id => $item) : ?>
							<?php
							if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
								continue;
							}

							$product_name = apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false);
							?>
							<div class="summary-item <?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
								<div>
									<div class="summary-item-name"><?php echo wp_kses_post($product_name); ?></div>
									<div class="summary-item-qty">
										<?php
										echo apply_filters(
											'woocommerce_order_item_quantity_html',
											sprintf(
												/* translators: %s: product quantity. */
												esc_html__('Quantity: %s', 'theme-woopm-child'),
												esc_html($item->get_quantity())
											),
											$item
										); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</div>
									<?php
									ob_start();
									do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
									wc_display_item_meta($item);
									do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
									$item_meta = trim(ob_get_clean());
									if ('' !== $item_meta) :
										?>
										<div class="summary-item-meta"><?php echo wp_kses_post($item_meta); ?></div>
									<?php endif; ?>
								</div>
								<div class="summary-item-price">
									<?php echo $order->get_formatted_line_subtotal($item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<?php if ($totals) : ?>
					<div class="summary-lines">
						<?php foreach ($totals as $key => $total) : ?>
							<div class="summary-line <?php echo esc_attr(sanitize_html_class($key)); ?><?php echo 'order_total' === $key ? ' total order-total' : ''; ?>">
								<span><?php echo wp_kses_post($total['label']); ?></span>
								<span><?php echo wp_kses_post($total['value']); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="security-badges">
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><?php esc_html_e('256-bit SSL encryption', 'theme-woopm-child'); ?></div>
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg><?php esc_html_e('GPhC regulated', 'theme-woopm-child'); ?></div>
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg><?php esc_html_e('Discreet updates by email', 'theme-woopm-child'); ?></div>
			</div>
		</aside>
		</form>
	</div>
</section>
