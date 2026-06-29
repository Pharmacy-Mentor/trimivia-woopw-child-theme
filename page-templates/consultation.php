<?php
/**
 * Template Name: Consultation
 * Template Post Type: page
 *
 * Trimvia consultation page — parent WooPW functionality, child-theme design shell.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/consultation/bootstrap.php';

get_header();

$trimvia_consult = trimvia_get_consultation_context();

if (have_posts()) {
	while (have_posts()) {
		the_post();

		get_template_part('template-parts/consultation/hero', null, $trimvia_consult);

		if (!empty(get_the_content())) {
			get_template_part('template-parts/consultation/page-content');
		}

		if (
			$trimvia_consult['recommend_enabled']
			&& !$trimvia_consult['is_under_process']
			&& $trimvia_consult['old_consultation_order_complete']
			&& $trimvia_consult['condition_slug']
		) {
			get_template_part('template-parts/consultation/reorder-notice', null, $trimvia_consult);
		}

		if ($trimvia_consult['recommend_error']) {
			get_template_part('template-parts/consultation/recommend-error');
		}

		get_template_part(
			'template-parts/consultation/form-shell',
			null,
			array(
				'context' => $trimvia_consult,
			)
		);
	}
}

get_footer();
