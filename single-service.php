<?php
/**
 * Singular template for service CPT — Trimvia layout + legacy sections from parent theme.
 */
if (!defined('ABSPATH')) {
	exit;
}

get_header();
while (have_posts()) :
	the_post();
	get_template_part('template-parts/content/content', 'service-single');
endwhile;

get_template_part('template-parts/content/content', 'service-single-legacy');

get_footer();
