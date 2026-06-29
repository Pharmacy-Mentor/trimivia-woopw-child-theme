<?php
/**
 * Checkout login form — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

if (is_user_logged_in() || 'no' === get_option('woocommerce_enable_checkout_login_reminder')) {
	return;
}

$login_message = apply_filters(
	'woocommerce_checkout_login_message',
	esc_html__('Returning customer?', 'woocommerce') . ' <a href="#" class="showlogin">' . esc_html__('Click here to login', 'woocommerce') . '</a>'
);
?>
<div class="woocommerce-form-login-toggle">
	<div class="trimvia-checkout-toggle-notice trimvia-checkout-toggle-notice--login" role="status">
		<div class="trimvia-checkout-toggle-notice-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
		</div>
		<p><?php echo wp_kses_post($login_message); ?></p>
	</div>
</div>

<form class="woocommerce-form woocommerce-form-login login trimvia-checkout-login-form" method="post" style="display:none;">
	<?php do_action('woocommerce_login_form_start'); ?>

	<p class="trimvia-checkout-login-intro">
		<?php esc_html_e('If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce'); ?>
	</p>

	<div class="trimvia-checkout-login-fields">
		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
			<label for="username"><?php esc_html_e('Username or email', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="input-text" name="username" id="username" autocomplete="username" />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
			<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<span class="woocommerce-input-wrapper password-input">
				<input class="input-text woocommerce-Input" type="password" name="password" id="password" autocomplete="current-password" />
			</span>
		</p>
	</div>

	<?php do_action('woocommerce_login_form'); ?>

	<p class="form-row trimvia-checkout-login-actions">
		<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
		<button type="submit" class="woocommerce-button button woocommerce-form-login__submit btn-accent" name="login" value="<?php esc_attr_e('Login', 'woocommerce'); ?>"><?php esc_html_e('Login', 'woocommerce'); ?></button>
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
			<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
			<span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
		</label>
	</p>

	<p class="lost_password">
		<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
	</p>

	<?php do_action('woocommerce_login_form_end'); ?>
</form>
