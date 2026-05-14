<?php
/**
 * Post type archive for `service` (when has_archive is enabled).
 * Uses the same layout as Services (Trimvia) page; configure copy under WP Admin → Services archive.
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
		'acf_id' => 'option',
	)
);

get_footer();
