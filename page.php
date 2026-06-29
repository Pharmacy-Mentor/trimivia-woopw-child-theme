<?php
/**
 * Default page template fallback.
 */

if (!defined('ABSPATH')) {
	exit;
}

if ((function_exists('is_account_page') && is_account_page()) || (function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) {
	get_header();

	if (function_exists('is_cart') && is_cart()) {
		echo do_shortcode('[woocommerce_cart]');
	} elseif (function_exists('is_checkout') && is_checkout()) {
		echo do_shortcode('[woocommerce_checkout]');
	} else {
		echo do_shortcode('[woocommerce_my_account]');
	}

	get_footer();
	return;
}

if (function_exists('trimvia_is_wp2fa_setup_page') && trimvia_is_wp2fa_setup_page()) {
	get_header();
	get_template_part('template-parts/content/content', '2fa-setup');
	get_footer();
	return;
}

get_header();
get_template_part('template-parts/content/content', 'default-page');
get_footer();
