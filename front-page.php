<?php
/**
 * Front page template.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
get_template_part('template-parts/content/content', 'homepage');
get_footer();
