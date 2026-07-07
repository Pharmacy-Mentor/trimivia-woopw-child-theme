<?php
/**
 * Child theme Customizer module loader.
 *
 * Disable the entire module to test Customizer performance without child theme hooks:
 *   define( 'TRIMVIA_ENABLE_CHILD_CUSTOMIZER', false ); // in wp-config.php
 *   (Re-enables parent theme Customizer registration when false.)
 *
 * Disable only the social repeater control script (UI still works as a raw textarea):
 *   define( 'TRIMVIA_ENABLE_CUSTOMIZER_SOCIAL_REPEATER', false );
 *
 * Move header/footer editing to the admin Theme Options page instead of Customizer:
 *   define( 'TRIMVIA_ENABLE_HEADER_FOOTER_CUSTOMIZER', false );
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('TRIMVIA_ENABLE_CHILD_CUSTOMIZER')) {
	define('TRIMVIA_ENABLE_CHILD_CUSTOMIZER', true);
}

if (!defined('TRIMVIA_ENABLE_CUSTOMIZER_SOCIAL_REPEATER')) {
	define('TRIMVIA_ENABLE_CUSTOMIZER_SOCIAL_REPEATER', false);
}

if (!defined('TRIMVIA_ENABLE_HEADER_FOOTER_CUSTOMIZER')) {
	define('TRIMVIA_ENABLE_HEADER_FOOTER_CUSTOMIZER', false);
}

$trimvia_customizer_dir = __DIR__;

require_once $trimvia_customizer_dir . '/helpers.php';
require_once $trimvia_customizer_dir . '/theme-mod-helpers.php';

if (!TRIMVIA_ENABLE_CHILD_CUSTOMIZER) {
	return;
}

require_once $trimvia_customizer_dir . '/sanitize.php';
require_once $trimvia_customizer_dir . '/controls.php';
require_once $trimvia_customizer_dir . '/parent-cleanup.php';
require_once $trimvia_customizer_dir . '/preview.php';
require_once $trimvia_customizer_dir . '/register-service.php';

if (TRIMVIA_ENABLE_HEADER_FOOTER_CUSTOMIZER) {
	require_once $trimvia_customizer_dir . '/register-header.php';
	require_once $trimvia_customizer_dir . '/register-footer.php';
}
