<?php
if (!defined('ABSPATH')) {
	exit;
}

function trimvia_sanitize_logo_image_value($value)
{
	if (is_numeric($value)) {
		return absint($value);
	}

	return esc_url_raw((string) $value);
}

/**
 * Sanitize text area content.
 *
 * @param string $value Raw text.
 * @return string
 */
function trimvia_sanitize_textarea($value)
{
	return wp_kses_post((string) $value);
}

/**
 * Sanitize selected nav menu ID.
 *
 * @param mixed $value Selected value.
 * @return int
 */
function trimvia_sanitize_menu_id($value)
{
	$menu_id = absint($value);
	if (!$menu_id) {
		return 0;
	}

	return wp_get_nav_menu_object($menu_id) ? $menu_id : 0;
}


