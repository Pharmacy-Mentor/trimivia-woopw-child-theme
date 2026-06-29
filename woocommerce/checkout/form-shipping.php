<?php
/**
 * Checkout shipping form — Trimvia delivery panel.
 *
 * Always shows delivery address fields in the dedicated Delivery Details panel
 * (parent theme flow), instead of hiding them behind a toggle.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

$trimvia_local_pickup_selected = function_exists('trimvia_checkout_chosen_method_is_local_pickup')
	&& trimvia_checkout_chosen_method_is_local_pickup();

?>
<div class="woocommerce-shipping-fields trimvia-checkout-shipping-fields">
	<?php if (true === WC()->cart->needs_shipping()) : ?>
		<input
			type="hidden"
			name="ship_to_different_address"
			id="trimvia_ship_to_different_address"
			value="<?php echo $trimvia_local_pickup_selected ? '0' : '1'; ?>"
		/>

		<label class="trimvia-checkout-same-address">
			<input
				type="checkbox"
				id="trimvia_same_as_billing"
				name="trimvia_same_as_billing"
				value="1"
				<?php checked($trimvia_local_pickup_selected); ?>
			/>
			<span><?php esc_html_e('Use billing address for delivery', 'theme-woopm-child'); ?></span>
		</label>

		<div class="shipping_address trimvia-checkout-shipping-address">
			<?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields('shipping');

				foreach ($fields as $key => $field) {
					woocommerce_form_field($key, $field, $checkout->get_value($key));
				}
				?>
			</div>

			<?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>
		</div>
	<?php endif; ?>
</div>
