<?php
/**
 * Empty cart page — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
?>

<section class="page-hero trimvia-cart-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Basket', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Your Basket', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Review your selected treatments before proceeding to checkout. Every order is prescriber-reviewed before dispatch.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section trimvia-cart-section">
	<div class="container">
		<?php do_action('woocommerce_before_cart'); ?>

		<div class="cart-empty rv">
			<div class="cart-empty-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
			</div>
			<h3><?php esc_html_e('Your basket is empty', 'woocommerce'); ?></h3>
			<p><?php esc_html_e("You haven't added any treatments yet. Browse our full range of clinician-approved weight loss options.", 'theme-woopm-child'); ?></p>
			<a href="<?php echo esc_url($shop_url); ?>" class="btn-accent">
				<?php esc_html_e('Shop Treatments', 'theme-woopm-child'); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>
	</div>
</section>
