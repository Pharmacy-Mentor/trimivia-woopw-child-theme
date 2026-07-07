<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Whether the current request is the Customizer preview iframe.
 *
 * @return bool
 */
function trimvia_is_customizer_preview()
{
	return function_exists('is_customize_preview') && is_customize_preview();
}

/**
 * Whether the current request is the Customizer admin screen.
 *
 * @return bool
 */
function trimvia_is_customizer_screen()
{
	global $pagenow;

	return is_admin() && isset($pagenow) && 'customize.php' === $pagenow;
}


