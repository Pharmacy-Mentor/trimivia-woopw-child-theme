<?php
/**
 * Taxonomy archive: condition (native URL e.g. /condition/{slug}/).
 *
 * Default: Trimvia layout matching page template Treatments (see content-condition-treatments.php).
 * After consultation / reorder: parent theme “after consultation” product template when available.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$queried = get_queried_object();
if (!$queried instanceof WP_Term || 'condition' !== $queried->taxonomy) {
	get_footer();
	return;
}

$term_id = (int) $queried->term_id;
$term    = get_term($term_id);
if (is_wp_error($term) || !$term) {
	get_footer();
	return;
}

$consultation_completed       = function_exists('has_consultation_for_condition') ? has_consultation_for_condition($term->slug) : false;
$is_reorder                  = isset($_GET['is_reorder']) ? absint(wp_unslash($_GET['is_reorder'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$reorder_order_id            = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$old_consultation_complete   = false;

if (is_user_logged_in() && 1 === $is_reorder && function_exists('get_user_latest_completed_consultation_order')) {
	$user = wp_get_current_user();
	$old_consultation_complete = (bool) get_user_latest_completed_consultation_order($user, $term->term_id, true, $reorder_order_id);
	$consultation_completed    = true;
}

if ((bool) get_theme_mod('trimvia_condition_archive_always_public_layout', false)) {
	$consultation_completed = false;
}

if ($consultation_completed) {
	get_template_part(
		'template-parts/condition',
		'treatments-afterconsultation',
		array(
			'term'    => $term,
			'term_id' => $term_id,
		)
	);
} else {
	get_template_part(
		'template-parts/content/content',
		'condition-treatments',
		array(
			'term' => $term,
		)
	);
}

get_footer();
