<?php
/**
 * Order customer details — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

$show_shipping = !wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>

<section class="woocommerce-customer-details">
	<div class="trimvia-order-addresses">
		<div class="trimvia-order-addresses__grid<?php echo $show_shipping ? ' trimvia-order-addresses__grid--two' : ' trimvia-order-addresses__grid--one'; ?>">
			<article class="trimvia-view-order-card trimvia-order-address-card woocommerce-column woocommerce-column--billing-address">
				<header class="trimvia-order-address-card__head">
					<h2 class="woocommerce-column__title"><?php esc_html_e('Billing address', 'woocommerce'); ?></h2>
				</header>
				<address class="trimvia-address-details">
					<?php trimvia_render_address_detail_rows($order, 'billing'); ?>
				</address>
			</article>

			<?php if ($show_shipping) : ?>
				<article class="trimvia-view-order-card trimvia-order-address-card woocommerce-column woocommerce-column--shipping-address">
					<header class="trimvia-order-address-card__head">
						<h2 class="woocommerce-column__title"><?php esc_html_e('Shipping address', 'woocommerce'); ?></h2>
					</header>
					<address class="trimvia-address-details">
						<?php trimvia_render_address_detail_rows($order, 'shipping'); ?>
					</address>
				</article>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action('woocommerce_order_details_after_customer_details', $order); ?>
</section>
