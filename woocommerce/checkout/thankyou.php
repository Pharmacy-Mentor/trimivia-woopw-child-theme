<?php
/**
 * Thank you page — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined('ABSPATH') || exit;
?>

<section class="page-hero trimvia-order-received-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Order confirmed', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Thank you for your order', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Your order has been received and is being processed.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="trimvia-order-received-section">
	<div class="container">
		<div class="woocommerce-order trimvia-order-received">

			<?php if ($order) : ?>

				<?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

				<?php if ($order->has_status('failed')) : ?>

					<div class="trimvia-view-order trimvia-order-received-failed">
						<div class="trimvia-view-order-card">
							<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
								<?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?>
							</p>

							<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
								<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay btn-accent"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
								<?php if (is_user_logged_in()) : ?>
									<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay btn-ghost"><?php esc_html_e('My account', 'woocommerce'); ?></a>
								<?php endif; ?>
							</p>
						</div>
					</div>

				<?php else : ?>

					<div class="trimvia-view-order">
						<?php wc_get_template('checkout/order-received.php', array('order' => $order)); ?>

						<?php
						ob_start();
						do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
						$payment_markup = trim(ob_get_clean());
						?>

						<?php if ($payment_markup) : ?>
							<div class="trimvia-order-received-payment-note" role="note">
								<span class="trimvia-order-received-payment-note__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 10v6"/><circle cx="12" cy="7" r="1" fill="currentColor" stroke="none"/></svg>
								</span>
								<div class="trimvia-order-received-payment-note__content">
									<?php echo wp_kses_post($payment_markup); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="trimvia-view-order-details">
							<?php do_action('woocommerce_thankyou', $order->get_id()); ?>
						</div>

						<div class="trimvia-order-received-actions">
							<?php if (is_user_logged_in()) : ?>
								<a class="btn-ghost" href="<?php echo esc_url(wc_get_endpoint_url('view-order', $order->get_id(), wc_get_page_permalink('myaccount'))); ?>">
									<?php esc_html_e('View order in account', 'theme-woopm-child'); ?>
								</a>
							<?php endif; ?>
							<a class="btn-accent" href="<?php echo esc_url(wc_get_page_permalink('shop') ?: home_url('/shop/')); ?>">
								<?php esc_html_e('Continue shopping', 'theme-woopm-child'); ?>
							</a>
						</div>
					</div>

				<?php endif; ?>

			<?php else : ?>

				<div class="trimvia-view-order">
					<?php wc_get_template('checkout/order-received.php', array('order' => false)); ?>
				</div>

			<?php endif; ?>

		</div>
	</div>
</section>
