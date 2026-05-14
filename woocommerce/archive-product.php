<?php
/**
 * Custom shop archive template override.
 *
 * Uses child theme custom shop layout for the main Shop page
 * and falls back to parent template for other product archives.
 */

defined('ABSPATH') || exit;

if (function_exists('is_shop') && !is_shop()) {
	$parent_archive_template = trailingslashit(get_template_directory()) . 'woocommerce/archive-product.php';
	if (file_exists($parent_archive_template)) {
		include $parent_archive_template;
		return;
	}
}

get_header('shop');
get_template_part('template-parts/content/content', 'shop');
get_footer('shop');
