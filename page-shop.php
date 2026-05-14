<?php
/**
 * Template Name: Shop (Trimvia)
 * Assign this template on your Shop page if you are not using the WooCommerce “Shop page”
 * setting, so “Shop Page Content” fields appear in the editor.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
get_template_part('template-parts/content/content', 'shop');
get_footer();
