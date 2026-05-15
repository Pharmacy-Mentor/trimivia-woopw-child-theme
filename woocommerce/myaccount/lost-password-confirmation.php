<?php
/**
 * Lost password confirmation text - Trimvia account design.
 *
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined('ABSPATH') || exit;

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();

wc_print_notice(esc_html__('Password reset email has been sent.', 'woocommerce'));
?>

<?php do_action('woocommerce_before_lost_password_confirmation_message'); ?>

<section class="page-hero trimvia-account-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('My account', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Check your email', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Password reset sent', 'theme-woopm-child'); ?></h1>
	</div>
</section>

<section class="page-section rv trimvia-account-auth-section">
	<div class="container">
		<div class="trimvia-account-auth">
			<div class="trimvia-account-auth-card trimvia-lost-password-card">
				<p><?php echo esc_html(apply_filters('woocommerce_lost_password_confirmation_message', esc_html__('A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'woocommerce'))); ?></p>
				<p class="trimvia-auth-note">
					<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Back to account', 'theme-woopm-child'); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<?php do_action('woocommerce_after_lost_password_confirmation_message'); ?>
