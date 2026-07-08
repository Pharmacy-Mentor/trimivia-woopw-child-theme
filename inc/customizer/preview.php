<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Remove preview scripts that break the Customizer iframe (widgets, parent main.js).
 *
 * Parent theme enqueues `main` (script.js) at priority 99999, so this must run later.
 *
 * @return void
 */
function trimvia_customize_preview_dequeue_disruptive_scripts()
{
    // Keep WordPress Customizer core scripts loaded; removing them can leave the preview iframe stuck loading.
    wp_dequeue_script('main');
    wp_deregister_script('main');
    wp_dequeue_script('theme-woopw-child-common');
    wp_deregister_script('theme-woopw-child-common');
    wp_dequeue_script('trimvia-chart-js');
    wp_dequeue_script('trimvia-homepage-chart');
    wp_dequeue_script('trimvia-gravity-consult');
    wp_dequeue_script('trimvia-cflp-consult');
    wp_dequeue_script('trimvia-condition-treatments-search');
    wp_dequeue_script('flashcanvas');
    wp_dequeue_script('jSignature');
    wp_dequeue_script('cflp-prescriber-dashboard');
}     

/**
 * Customizer preview iframe bootstrap â€” keep preview light; selective refresh is disabled.
 *
 * @return void
 */
function trimvia_customize_preview_bootstrap()
{
	// Parent script.js references #scrollToTop which is absent in many preview pages.
	remove_action('wp_enqueue_scripts', 'pharmacy_register_custom_scripts', 99999);
	add_action('wp_enqueue_scripts', 'trimvia_customize_preview_dequeue_disruptive_scripts', 999999);
}
add_action('customize_preview_init', 'trimvia_customize_preview_bootstrap', 0);


