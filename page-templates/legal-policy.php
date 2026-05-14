<?php
/**
 * Template Name: Legal / Policy
 * Template Post Type: page
 *
 * Privacy policy, terms, and similar legal pages with an ACF-driven hero.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
get_template_part('template-parts/content/content', 'legal-policy');
get_footer();
