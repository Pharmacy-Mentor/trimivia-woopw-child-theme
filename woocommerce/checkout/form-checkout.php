<?php
/**
 * Checkout form — Trimvia layout.
 *
 * Keeps WooCommerce checkout hooks intact while applying the Trimvia checkout
 * prototype structure.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>

<section class="page-hero trimvia-checkout-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Checkout', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Secure Checkout', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Your order will be reviewed by our UK-registered pharmacist prescriber before dispatch.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section trimvia-checkout-section">
	<div class="container">
		<div class="trimvia-checkout-before-form">
			<?php do_action('woocommerce_before_checkout_form', $checkout); ?>
		</div>

		<form name="checkout" method="post" class="checkout woocommerce-checkout checkout-layout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
		<div class="checkout-form">
			<div class="trimvia-checkout-form-notices woocommerce-notices-wrapper" aria-live="polite"></div>
			<?php if ($checkout->get_checkout_fields()) : ?>
				<?php do_action('woocommerce_checkout_before_customer_details'); ?>

				<div id="customer_details" class="trimvia-checkout-details">
					<div class="form-section active trimvia-checkout-panel trimvia-checkout-panel--billing">
						<div class="form-section-header">
							<div class="form-section-num">1</div>
							<div>
								<h3><?php esc_html_e('Billing Details', 'woocommerce'); ?></h3>
								<p><?php esc_html_e('Your contact and billing information.', 'theme-woopm-child'); ?></p>
							</div>
						</div>
						<div class="form-alert">
							<strong><?php esc_html_e('Secure patient details', 'theme-woopm-child'); ?></strong>
							<?php esc_html_e('We use these details to confirm your order and prescription review.', 'theme-woopm-child'); ?>
						</div>
						<?php do_action('woocommerce_checkout_billing'); ?>
					</div>

					<?php
					$trimvia_show_delivery_panel = WC()->cart && WC()->cart->needs_shipping();
					$trimvia_local_pickup_selected = function_exists('trimvia_checkout_chosen_method_is_local_pickup')
						&& trimvia_checkout_chosen_method_is_local_pickup();
					if ($trimvia_show_delivery_panel) :
					?>
					<div class="form-section active trimvia-checkout-panel trimvia-checkout-panel--shipping<?php echo $trimvia_local_pickup_selected ? ' is-collapsed' : ''; ?>"<?php echo $trimvia_local_pickup_selected ? ' hidden' : ''; ?>>
						<div class="form-section-header">
							<div class="form-section-num">2</div>
							<div>
								<h3><?php esc_html_e('Delivery Details', 'theme-woopm-child'); ?></h3>
								<p><?php esc_html_e('Where should we send your order?', 'theme-woopm-child'); ?></p>
							</div>
						</div>
						<?php do_action('woocommerce_checkout_shipping'); ?>
					</div>
					<?php endif; ?>
				</div>

				<?php do_action('woocommerce_checkout_after_customer_details'); ?>
			<?php endif; ?>

			<div class="form-section active trimvia-checkout-panel trimvia-checkout-panel--notes">
				<div class="form-alert">
					<strong><?php esc_html_e('Prescription review required:', 'theme-woopm-child'); ?></strong>
					<?php esc_html_e('Your order starts a pharmacist prescriber review. If extra information is needed, our team will contact you before dispatch.', 'theme-woopm-child'); ?>
				</div>
				<?php
				$trimvia_gp_checkout_markup = function_exists('trimvia_checkout_get_gp_section_markup')
					? trimvia_checkout_get_gp_section_markup()
					: '';
				if ('' !== $trimvia_gp_checkout_markup) :
					?>
					<div class="trimvia-checkout-gp-section">
						<?php echo $trimvia_gp_checkout_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooPW GP template markup. ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<aside class="order-summary trimvia-checkout-summary">
			<div class="order-summary-head">
				<h3><?php esc_html_e('Order Summary', 'theme-woopm-child'); ?></h3>
				<p><?php echo esc_html(sprintf(_n('%s item in your order', '%s items in your order', $cart_count, 'theme-woopm-child'), number_format_i18n($cart_count))); ?></p>
			</div>
			<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
			<div id="order_review" class="woocommerce-checkout-review-order trimvia-order-review">
				<?php do_action('woocommerce_checkout_before_order_review'); ?>
				<?php do_action('woocommerce_checkout_order_review'); ?>
				<?php do_action('woocommerce_checkout_after_order_review'); ?>
			</div>
			<div class="security-badges">
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><?php esc_html_e('256-bit SSL encryption', 'theme-woopm-child'); ?></div>
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg><?php esc_html_e('GPhC regulated', 'theme-woopm-child'); ?></div>
				<div class="security-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg><?php esc_html_e('Discreet updates by email', 'theme-woopm-child'); ?></div>
			</div>
		</aside>
		</form>

		<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
	</div>
</section>
