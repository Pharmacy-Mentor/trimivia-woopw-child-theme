<?php
/**
 * Checkout coupon form — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) {
	return;
}

$coupon_message = apply_filters(
	'woocommerce_checkout_coupon_message',
	esc_html__('Have a coupon?', 'woocommerce') . ' <a href="#" class="showcoupon">' . esc_html__('Click here to enter your code', 'woocommerce') . '</a>'
);
?>
<div class="woocommerce-form-coupon-toggle">
	<div class="trimvia-checkout-toggle-notice trimvia-checkout-toggle-notice--coupon" role="status">
		<div class="trimvia-checkout-toggle-notice-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
		</div>
		<p><?php echo wp_kses_post($coupon_message); ?></p>
	</div>
</div>

<form class="checkout_coupon woocommerce-form-coupon trimvia-checkout-coupon-form" method="post" style="display:none;">
	<p class="trimvia-checkout-login-intro">
		<?php esc_html_e('If you have a coupon code, please apply it below.', 'woocommerce'); ?>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide trimvia-checkout-coupon-field">
		<label for="coupon_code"><?php esc_html_e('Coupon code', 'woocommerce'); ?></label>
		<span class="trimvia-checkout-coupon-input-row">
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" id="coupon_code" value="" />
			<button type="submit" class="button btn-accent" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply coupon', 'woocommerce'); ?></button>
		</span>
	</p>
</form>
