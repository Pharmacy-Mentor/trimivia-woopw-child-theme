<?php
/**
 * Taxonomy archive: condition (e.g. /condition/weight-loss/).
 *
 * Child template parts use Trimvia design; section order matches HTML mockup:
 * hero → how it works → products → about → FAQs → popular categories.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$queried = get_queried_object();
if (!$queried instanceof WP_Term || 'condition' !== $queried->taxonomy) {
	get_header();
	get_footer();
	return;
}

$term_id = (int) $queried->term_id;
$term    = get_term($term_id);
if (is_wp_error($term) || !$term) {
	get_header();
	get_footer();
	return;
}

if (function_exists('trimvia_condition_has_visible_products') && !trimvia_condition_has_visible_products($term)) {
	wp_safe_redirect(home_url('/all-conditions/'));
	exit;
}

get_header();

$consultation_completed = function_exists('has_consultation_for_condition')
	? has_consultation_for_condition($term->slug)
	: false;

$is_reorder         = isset($_GET['is_reorder']) ? absint(wp_unslash($_GET['is_reorder'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$reorder_order_id   = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if (is_user_logged_in() && 1 === $is_reorder && function_exists('get_user_latest_completed_consultation_order')) {
	$user = wp_get_current_user();
	get_user_latest_completed_consultation_order($user, $term->term_id, true, $reorder_order_id);
	$consultation_completed = true;
}

if ($term && !$consultation_completed) {
	get_template_part(
		'template-parts/condition',
		'header',
		array(
			'term_id' => $term_id,
			'term'    => $term,
		)
	);
}

if ($term && !$consultation_completed) {
	get_template_part(
		'template-parts/order',
		'steps',
		array(
			'term' => $term,
		)
	);

	get_template_part(
		'template-parts/condition',
		'treatments',
		array(
			'term'    => $term,
			'term_id' => $term_id,
		)
	);
} else {
	get_template_part(
		'template-parts/condition',
		'treatments-afterconsultation',
		array(
			'term'    => $term,
			'term_id' => $term_id,
		)
	);
}

if ($term && !$consultation_completed) {
	get_template_part(
		'template-parts/condition',
		'content',
		array(
			'term' => $term,
		)
	);

	get_template_part(
		'template-parts/condition',
		'faqs',
		array(
			'term' => $term,
		)
	);

	get_template_part(
		'template-parts/popular',
		'categories',
		array(
			'term' => $term,
		)
	);
}

get_footer();
