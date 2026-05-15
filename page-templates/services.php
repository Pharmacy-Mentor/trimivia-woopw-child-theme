<?php
/**
 * Template Name: Services
 * Template Post Type: page
 *
 * Child compatibility wrapper for legacy "Services" page template assignments.
 * Uses the existing Trimvia services archive renderer.
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
