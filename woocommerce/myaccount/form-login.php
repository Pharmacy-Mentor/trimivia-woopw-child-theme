<?php
/**
 * Login/register form — Trimvia account design.
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$show_registration = 'yes' === get_option('woocommerce_enable_myaccount_registration');
$active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (!$show_registration || !in_array($active_tab, array('login', 'register'), true)) {
	$active_tab = 'login';
}
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
		<p><?php esc_html_e('Sign in to manage orders, addresses, and your secure Trimvia account.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section rv trimvia-account-auth-section">
	<div class="container">
		<div class="trimvia-account-auth">
			<div class="trimvia-account-auth-copy">
				<span class="product-type"><?php esc_html_e('Secure patient access', 'theme-woopm-child'); ?></span>
				<h2><?php esc_html_e('Welcome back', 'theme-woopm-child'); ?></h2>
				<p><?php esc_html_e('Access your treatment history, delivery details, and account preferences through your secure customer area.', 'theme-woopm-child'); ?></p>
				<div class="shop-trust single-product-trust-inline">
					<div class="shop-trust-item">
						<div class="shop-trust-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						</div>
						<div>
							<h4><?php esc_html_e('Encrypted access', 'theme-woopm-child'); ?></h4>
							<p><?php esc_html_e('Your health data is protected.', 'theme-woopm-child'); ?></p>
						</div>
					</div>
					<div class="shop-trust-item">
						<div class="shop-trust-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
						</div>
						<div>
							<h4><?php esc_html_e('Regulated care', 'theme-woopm-child'); ?></h4>
							<p><?php esc_html_e('Managed through trusted pharmacy workflows.', 'theme-woopm-child'); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="trimvia-account-auth-card">
				<?php do_action('woocommerce_before_customer_login_form'); ?>

				<?php if ($show_registration) : ?>
					<div class="trimvia-account-auth-tabs" role="tablist" aria-label="<?php esc_attr_e('Account access', 'theme-woopm-child'); ?>">
						<a class="<?php echo 'login' === $active_tab ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('tab', 'login')); ?>"><?php esc_html_e('Login', 'woocommerce'); ?></a>
						<a class="<?php echo 'register' === $active_tab ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('tab', 'register')); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></a>
					</div>
				<?php endif; ?>

				<div id="customer_login">
					<div class="trimvia-auth-panel <?php echo 'login' === $active_tab ? 'is-active' : ''; ?>" <?php echo 'login' === $active_tab ? '' : 'hidden'; ?>>
						<h2><?php esc_html_e('Log in to your account', 'woocommerce'); ?></h2>
						<form class="woocommerce-form woocommerce-form-login login" method="post">
							<?php do_action('woocommerce_login_form_start'); ?>

							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="username"><?php esc_html_e('Username or email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
							</p>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
								<input class="woocommerce-Input woocommerce-Input--text input-text form-input" type="password" name="password" id="password" autocomplete="current-password" />
							</p>

							<?php do_action('woocommerce_login_form'); ?>

							<p class="form-row trimvia-auth-actions">
								<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
									<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
									<span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
								</label>
								<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
								<button type="submit" class="woocommerce-button button woocommerce-form-login__submit btn-accent" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><?php esc_html_e('Log in', 'woocommerce'); ?></button>
							</p>
							<p class="woocommerce-LostPassword lost_password">
								<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
							</p>

							<?php do_action('woocommerce_login_form_end'); ?>
						</form>
					</div>

					<?php if ($show_registration) : ?>
						<div class="trimvia-auth-panel <?php echo 'register' === $active_tab ? 'is-active' : ''; ?>" <?php echo 'register' === $active_tab ? '' : 'hidden'; ?>>
							<h2><?php esc_html_e('Create an account', 'woocommerce'); ?></h2>
							<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>
								<?php do_action('woocommerce_register_form_start'); ?>

								<?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
									<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
										<input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
									</p>
								<?php endif; ?>

								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
									<input type="email" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
								</p>

								<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
									<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
										<input type="password" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="password" id="reg_password" autocomplete="new-password" />
									</p>
								<?php else : ?>
									<p class="trimvia-auth-note"><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>
								<?php endif; ?>

								<?php do_action('woocommerce_register_form'); ?>

								<p class="woocommerce-form-row form-row">
									<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
									<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit btn-accent" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
								</p>

								<?php do_action('woocommerce_register_form_end'); ?>
							</form>
						</div>
					<?php endif; ?>
				</div>

				<?php do_action('woocommerce_after_customer_login_form'); ?>
			</div>
		</div>
	</div>
</section>
