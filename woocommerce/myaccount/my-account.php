<?php
/**
 * My Account page shell — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
?>

<section class="page-hero trimvia-account-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('My account', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('My account', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Manage orders, addresses, profile details, and clinical account information in one secure place.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section rv trimvia-account-section">
	<div class="container">
		<div class="account-page-grid">
			<?php do_action('woocommerce_account_navigation'); ?>

			<div class="woocommerce-MyAccount-content trimvia-account-content">
				<?php
				/**
				 * My Account content.
				 *
				 * @since 2.6.0
				 */
				do_action('woocommerce_account_content');
				?>
			</div>
		</div>
	</div>
</section>

<section class="page-section page-section--alt rv trimvia-account-trust">
	<div class="container">
		<div class="shop-trust">
			<div class="shop-trust-item">
				<div class="shop-trust-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
				</div>
				<h4><?php esc_html_e('GPhC regulated', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Dispensed by Mayberry Pharmacy', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<h4><?php esc_html_e('Clinical messaging', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Secure notes through your account', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				</div>
				<h4><?php esc_html_e('Encrypted access', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Protecting your health data', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				</div>
				<h4><?php esc_html_e('Need help?', 'theme-woopm-child'); ?></h4>
				<p><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact us', 'theme-woopm-child'); ?></a></p>
			</div>
		</div>
	</div>
</section>
