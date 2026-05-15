<?php
/**
 * Reset password form - Trimvia account design.
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();

do_action('woocommerce_before_reset_password_form');
?>

<section class="page-hero trimvia-account-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('My account', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Set new password', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Create a new password', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Use a strong password that you have not used before.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section rv trimvia-account-auth-section trimvia-account-reset-password-section">
	<div class="container">
		<div class="trimvia-account-auth">
			<div class="trimvia-account-auth-copy">
				<span class="product-type"><?php esc_html_e('Secure password update', 'theme-woopm-child'); ?></span>
				<h2><?php esc_html_e('Almost done', 'theme-woopm-child'); ?></h2>
				<p><?php esc_html_e('Set your new password below, then sign in to continue to your account dashboard.', 'theme-woopm-child'); ?></p>
			</div>

			<div class="trimvia-account-auth-card trimvia-lost-password-card">
				<form method="post" class="woocommerce-ResetPassword lost_reset_password">
					<p><?php echo apply_filters('woocommerce_reset_password_message', esc_html__('Enter a new password below.', 'woocommerce')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

					<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
						<label for="password_1"><?php esc_html_e('New password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="password_1" id="password_1" autocomplete="new-password" />
					</p>
					<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
						<label for="password_2"><?php esc_html_e('Re-enter new password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="password_2" id="password_2" autocomplete="new-password" />
					</p>

					<input type="hidden" name="reset_key" value="<?php echo esc_attr($args['key']); ?>" />
					<input type="hidden" name="reset_login" value="<?php echo esc_attr($args['login']); ?>" />

					<div class="clear"></div>

					<?php do_action('woocommerce_resetpassword_form'); ?>

					<p class="woocommerce-form-row form-row">
						<input type="hidden" name="wc_reset_password" value="true" />
						<button type="submit" class="woocommerce-Button button btn-accent<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" value="<?php esc_attr_e('Save', 'woocommerce'); ?>"><?php esc_html_e('Save', 'woocommerce'); ?></button>
					</p>

					<p class="trimvia-auth-note">
						<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Back to login', 'theme-woopm-child'); ?></a>
					</p>

					<?php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); ?>
				</form>
			</div>
		</div>
	</div>
</section>

<?php
do_action('woocommerce_after_reset_password_form');
