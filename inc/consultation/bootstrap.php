<?php
/**
 * Consultation page context — parent WooPW behaviour with child-theme hardening.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Build consultation template variables (mirrors parent consultation.php logic).
 *
 * @return array<string, mixed>
 */
function trimvia_get_consultation_context()
{
	$is_under_process    = false;
	$recommend_enabled   = class_exists('WOOPW_ADDON_MANAGER') ? WOOPW_ADDON_MANAGER::enable_product_recommend() : false;
	$fpr_enabled         = class_exists('WOOPW_ADDON_MANAGER') ? WOOPW_ADDON_MANAGER::enable_product_recommendation_rules() : false;
	$slug                = isset($_GET['condition-slug']) && $_GET['condition-slug'] ? sanitize_text_field(wp_unslash($_GET['condition-slug'])) : '';
	$condition_slug      = $slug;
	$questionnaire_id    = '';
	$term                = null;

	if ($condition_slug) {
		$found = get_term_by('slug', $condition_slug, 'condition');
		if ($found && !is_wp_error($found)) {
			$term = $found;
			$previous_conditions = function_exists('woopw_check_orders_previous_conditions') ? woopw_check_orders_previous_conditions() : array();

			if (is_array($previous_conditions) && in_array((int) $term->term_id, array_map('intval', $previous_conditions), true)) {
				$questionnaire_id = (function_exists('get_field') ? get_field('reorder_questionnaire', $term) : '');
				if (!$questionnaire_id) {
					$questionnaire_id = get_option('default_reorder_questionnaire');
				}
				if (!$questionnaire_id && function_exists('get_field')) {
					$questionnaire_id = get_field('questionnaire', $term);
				}
			} elseif (function_exists('get_field')) {
				$questionnaire_id = get_field('questionnaire', $term);
			}
		}
	}

	$old_consultation_order_complete = false;
	$previous_completed_order_id     = 0;

	if (is_user_logged_in() && $term instanceof WP_Term) {
		$user = wp_get_current_user();

		if (function_exists('get_user_latest_pending_consultation_order')) {
			$consultation_order_complete = get_user_latest_pending_consultation_order($user, $term->term_id);
			if ($consultation_order_complete) {
				$is_under_process = true;
			}
		}

		if (function_exists('get_user_latest_completed_consultation_order')) {
			$old_consultation_order_complete = get_user_latest_completed_consultation_order($user, $term->term_id, false);
		}
		if ($old_consultation_order_complete && function_exists('woopw_find_previous_order_for_condition')) {
			$previous_completed_order_id = absint(woopw_find_previous_order_for_condition((int) $term->term_id, (int) $user->ID));
		}
	}

	$recommend_error = isset($_GET['recommend_error']) && (string) $_GET['recommend_error'] === '1';

	$current_params = array();
	foreach ($_GET as $key => $value) {
		if (is_scalar($value)) {
			$current_params[sanitize_key((string) $key)] = sanitize_text_field(wp_unslash((string) $value));
		}
	}

	$redirect_back = add_query_arg($current_params, get_permalink());
	$account_base_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
	$login_url = add_query_arg(
		array(
			'redirect_to'   => $redirect_back,
			'_redirect_url' => $redirect_back,
		),
		$account_base_url
	);
	$account_url = add_query_arg('tab', 'register', $login_url);

	$show_questionnaire_sidebar = !$is_under_process
		&& $term instanceof WP_Term
		&& !empty($questionnaire_id);

	$consult_hero_sub = '';
	if ($term instanceof WP_Term) {
		$t_desc_raw = term_description($term->term_id, 'condition');
		if ($t_desc_raw) {
			$consult_hero_sub = wp_trim_words(wp_strip_all_tags($t_desc_raw), 40, '…');
		}
	}
	if ($consult_hero_sub === '') {
		$consult_hero_sub = __(
			'Complete this short assessment so our prescribers can determine if treatment is right for you.',
			'woopw'
		);
	}

	$trimvia_contact_url   = home_url('/contact-us/');
	$trimvia_contact_phone = '';
	$contact_page          = get_page_by_path('contact-us');
	if ($contact_page instanceof WP_Post) {
		$trimvia_contact_url = get_permalink($contact_page);
		if (function_exists('get_field')) {
			$trimvia_contact_phone = trim((string) get_field('contact_urgent_phone', $contact_page->ID));
		}
	}

	$trimvia_condition_cancel_url = ($term instanceof WP_Term && $condition_slug)
		? home_url('/condition/' . rawurlencode($condition_slug) . '/')
		: home_url('/');

	$consult_approx_minutes = 5;
	if ($term instanceof WP_Term && function_exists('get_field')) {
		$approx_time = (int) get_field('approximately_taken_time', $term);
		if ($approx_time > 0) {
			$consult_approx_minutes = $approx_time;
		}
	}

	return compact(
		'is_under_process',
		'recommend_enabled',
		'fpr_enabled',
		'condition_slug',
		'questionnaire_id',
		'term',
		'old_consultation_order_complete',
		'previous_completed_order_id',
		'recommend_error',
		'login_url',
		'account_url',
		'show_questionnaire_sidebar',
		'consult_hero_sub',
		'consult_approx_minutes',
		'trimvia_contact_url',
		'trimvia_contact_phone',
		'trimvia_condition_cancel_url'
	);
}

/**
 * Render questionnaire shortcode output (same as parent do_shortcode call).
 *
 * @param array<string, mixed> $context Consultation context.
 * @return string
 */
function trimvia_render_consultation_questionnaire(array $context)
{
	if (!empty($context['is_under_process'])) {
		$msg = function_exists('get_field') ? get_field('consultation_under_process', get_the_ID()) : '';
		return $msg ? (string) $msg : '';
	}

	if (!($context['term'] instanceof WP_Term)) {
		return '<p class="trimvia-consult-missing-condition">' . esc_html__(
			'No treatment condition was specified. Please start from the treatments or conditions page.',
			'woocommerce'
		) . '</p>';
	}

	if (empty($context['questionnaire_id'])) {
		return '';
	}

	return do_shortcode($context['questionnaire_id']);
}
