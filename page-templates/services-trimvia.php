<?php
/**
 * Template Name: Services (Trimvia)
 * Template Post Type: page
 *
 * Service archive layout from static HTML — hero, grid, CTA. Edit content via ACF on this page.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

get_template_part(
	'template-parts/content/content',
	'services-archive',
	array(
		'acf_id' => get_the_ID(),
	)
);

get_footer();
