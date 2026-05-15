<?php
/**
 * Lost password form — Trimvia account design.
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
?>

<section class="page-hero trimvia-account-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('My account', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Reset password', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Reset your password', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Enter your account email or username and we will send you a secure link to create a new password.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section rv trimvia-account-auth-section trimvia-account-lost-password-section">
	<div class="container">
		<div class="trimvia-account-auth">
			<div class="trimvia-account-auth-copy">
				<span class="product-type"><?php esc_html_e('Secure account recovery', 'theme-woopm-child'); ?></span>
				<h2><?php esc_html_e('Forgotten your password?', 'theme-woopm-child'); ?></h2>
				<p><?php esc_html_e('For your security, password reset links are sent only to the email address connected to your Trimvia account.', 'theme-woopm-child'); ?></p>
			</div>

			<div class="trimvia-account-auth-card trimvia-lost-password-card">
				<?php do_action('woocommerce_before_lost_password_form'); ?>

				<form method="post" class="woocommerce-ResetPassword lost_reset_password">
					<p><?php echo apply_filters('woocommerce_lost_password_message', esc_html__('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="user_login"><?php esc_html_e('Username or email', 'woocommerce'); ?></label>
						<input class="woocommerce-Input woocommerce-Input--text input-text form-input" type="text" name="user_login" id="user_login" autocomplete="username" />
					</p>

					<?php do_action('woocommerce_lostpassword_form'); ?>

					<p class="woocommerce-form-row form-row">
						<input type="hidden" name="wc_reset_password" value="true" />
						<button type="submit" class="woocommerce-Button button btn-accent<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" value="<?php esc_attr_e('Reset password', 'woocommerce'); ?>"><?php esc_html_e('Reset password', 'woocommerce'); ?></button>
					</p>

					<p class="trimvia-auth-note">
						<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Back to login', 'theme-woopm-child'); ?></a>
					</p>

					<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
				</form>

				<?php do_action('woocommerce_after_lost_password_form'); ?>
			</div>
		</div>
	</div>
</section>
