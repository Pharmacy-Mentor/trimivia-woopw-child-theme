<?php
/**
 * Prescriber review modal enrichment — previous consultations + section visibility.
 *
 * WooPW outputs previous consultations in prescriber-order-review.php when available.
 * When that block is missing or empty, we hydrate it from the patient's prior orders.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Completed order statuses eligible for "previous consultation" history.
 *
 * @return array<int, string>
 */
function trimvia_get_previous_consultation_order_statuses()
{
	return array('completed', 'recalled', 'wc-recalled', 'prescribe-approve', 'partial-approved');
}

/**
 * Normalise _order_conditions meta to integer term IDs.
 *
 * @param mixed $raw Raw meta value.
 * @return array<int, int>
 */
function trimvia_normalise_order_condition_ids($raw)
{
	if (!is_array($raw)) {
		$raw = ($raw !== '' && $raw !== null) ? array($raw) : array();
	}

	return array_values(array_filter(array_map('intval', $raw)));
}

/**
 * Fetch prior consultation orders for the same patient (excluding the current order).
 *
 * @param WC_Order $current_order Current review order.
 * @return array<int, WC_Order>
 */
function trimvia_get_previous_consultation_orders_for_review($current_order)
{
	static $cache = array();

	if (!$current_order instanceof WC_Order) {
		return array();
	}

	$current_id = (int) $current_order->get_id();
	if ($current_id < 1) {
		return array();
	}

	if (isset($cache[ $current_id ])) {
		return $cache[ $current_id ];
	}

	$customer_id = (int) $current_order->get_customer_id();
	if ($customer_id < 1) {
		$cache[ $current_id ] = array();
		return $cache[ $current_id ];
	}

	$condition_ids   = trimvia_normalise_order_condition_ids($current_order->get_meta('_order_conditions'));
	$previous_orders = wc_get_orders(
		array(
			'customer_id' => $customer_id,
			'status'      => trimvia_get_previous_consultation_order_statuses(),
			'exclude'     => array($current_id),
			'limit'       => 15,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'return'      => 'objects',
		)
	);

	if (empty($previous_orders) || !is_array($previous_orders)) {
		$cache[ $current_id ] = array();
		return $cache[ $current_id ];
	}

	$matched      = array();
	$current_date = $current_order->get_date_created();

	foreach ($previous_orders as $order) {
		if (!$order instanceof WC_Order) {
			continue;
		}

		// Only match orders created before the current order to prevent matching future reorders
		if ($current_date && $order->get_date_created() && $order->get_date_created()->getTimestamp() >= $current_date->getTimestamp()) {
			continue;
		}

		$form_data = $order->get_meta('_cflp_form_data');
		if (empty($form_data)) {
			continue;
		}
		if (!is_array($form_data)) {
			$form_data = maybe_unserialize($form_data);
		}
		if (!is_array($form_data) || empty($form_data)) {
			continue;
		}

		if (!empty($condition_ids)) {
			$order_conditions = trimvia_normalise_order_condition_ids($order->get_meta('_order_conditions'));
			$matches_order    = !empty($order_conditions) && array_intersect($condition_ids, $order_conditions);

			if (!$matches_order) {
				$matches_form = false;
				foreach ($form_data as $entry) {
					if (!is_array($entry)) {
						continue;
					}

					$entry_condition_id = absint($entry['condition_id'] ?? 0);
					if ($entry_condition_id && in_array($entry_condition_id, $condition_ids, true)) {
						$matches_form = true;
						break;
					}
				}

				if (!$matches_form) {
					continue;
				}
			}
		}

		$matched[] = $order;
	}

	$cache[ $current_id ] = $matched;

	return $cache[ $current_id ];
}

/**
 * Resolve the patient-order-consultation.php template path.
 *
 * @return string
 */
function trimvia_get_patient_consultation_template_path()
{
	$child_path = get_stylesheet_directory() . '/woocommerce/myaccount/patient-order-consultation.php';
	if (file_exists($child_path)) {
		return $child_path;
	}

	if (function_exists('default_template_path')) {
		$path = default_template_path() . 'myaccount/patient-order-consultation.php';
		if (file_exists($path)) {
			return $path;
		}
	}

	if (defined('CFLP_PLUGIN_DIR')) {
		$path = trailingslashit(CFLP_PLUGIN_DIR) . 'woocommerce/myaccount/patient-order-consultation.php';
		if (file_exists($path)) {
			return $path;
		}
	}

	return '';
}

/**
 * Whether a stored consultation field contains a displayable answer.
 *
 * @param mixed $field Field payload.
 * @return bool
 */
function trimvia_consultation_field_has_value($field)
{
	if (!is_array($field)) {
		return false;
	}

	if (isset($field['type']) && 'file' === $field['type']) {
		return !empty($field['value']);
	}

	if (!array_key_exists('value', $field)) {
		return false;
	}

	$value = $field['value'];
	if (is_array($value)) {
		return (bool) array_filter($value, static function ($item) {
			return null !== $item && '' !== $item && array() !== $item;
		});
	}

	return null !== $value && '' !== $value;
}

/**
 * Count answered consultation fields in a form_data array.
 *
 * @param array<string,mixed> $form_data Stored answers.
 * @return int
 */
function trimvia_count_populated_consultation_fields($form_data)
{
	if (!is_array($form_data)) {
		return 0;
	}

	$count = 0;

	foreach ($form_data as $field) {
		if (trimvia_consultation_field_has_value($field)) {
			++$count;
		}
	}

	return $count;
}

/**
 * @param WC_Order $order Order object.
 * @return array<string,array<string,mixed>>
 */
function trimvia_get_order_form_entries(WC_Order $order)
{
	$raw = $order->get_meta('_cflp_form_data');
	if (!is_array($raw)) {
		$raw = maybe_unserialize($raw);
	}

	return is_array($raw) ? $raw : array();
}

/**
 * @param array<string,mixed> $entry     Candidate entry.
 * @param array<string,mixed> $reference Reference entry.
 * @return bool
 */
function trimvia_form_entries_match_condition(array $entry, array $reference)
{
	$reference_id = absint($reference['condition_id'] ?? 0);
	$entry_id     = absint($entry['condition_id'] ?? 0);

	if ($reference_id && $entry_id && $reference_id === $entry_id) {
		return true;
	}

	if (!empty($reference['condition_slug']) && !empty($entry['condition_slug'])) {
		return sanitize_title((string) $reference['condition_slug']) === sanitize_title((string) $entry['condition_slug']);
	}

	return false;
}

/**
 * Find the richest matching consultation entry on an order.
 *
 * @param WC_Order              $order             Order to search.
 * @param array<string,mixed>   $reference         Reference entry.
 * @param array<int,string>     $skip_keys         Form keys to ignore.
 * @param bool                  $prefer_non_reorder Prefer the initial consultation entry.
 * @return array<string,mixed>|null
 */
function trimvia_find_best_form_entry_on_order(WC_Order $order, array $reference, array $skip_keys = array(), $prefer_non_reorder = true)
{
	$entries   = trimvia_get_order_form_entries($order);
	$best      = null;
	$best_score = -1;

	foreach ($entries as $form_key => $entry) {
		if (!is_array($entry) || in_array((string) $form_key, $skip_keys, true)) {
			continue;
		}

		if (!trimvia_form_entries_match_condition($entry, $reference)) {
			continue;
		}

		if ($prefer_non_reorder && !empty($entry['is_reorder'])) {
			continue;
		}

		$score = trimvia_count_populated_consultation_fields($entry['form_data'] ?? array());
		if ($score > $best_score) {
			$best_score = $score;
			$best       = $entry;
		}
	}

	if (!$best && $prefer_non_reorder) {
		return trimvia_find_best_form_entry_on_order($order, $reference, $skip_keys, false);
	}

	return $best;
}

/**
 * Merge consultation answers, keeping the overlay when both entries define a field.
 *
 * @param array<string,mixed> $base    Base answers.
 * @param array<string,mixed> $overlay Updated answers.
 * @return array<string,mixed>
 */
function trimvia_merge_consultation_form_data(array $base, array $overlay)
{
	$merged = $base;

	foreach ($overlay as $field_key => $field_value) {
		if (trimvia_consultation_field_has_value($field_value)) {
			$merged[ $field_key ] = $field_value;
		}
	}

	return $merged;
}

/**
 * Merge group definitions by title so reorder-only groups remain visible.
 *
 * @param array<int,array<string,mixed>> $base_groups    Base groups.
 * @param array<int,array<string,mixed>> $overlay_groups Overlay groups.
 * @return array<int,array<string,mixed>>
 */
function trimvia_merge_consultation_form_groups(array $base_groups, array $overlay_groups)
{
	if (empty($base_groups)) {
		return $overlay_groups;
	}

	if (empty($overlay_groups)) {
		return $base_groups;
	}

	$merged_by_title = array();

	foreach ($base_groups as $group) {
		if (!is_array($group)) {
			continue;
		}

		$title = strtolower(trim((string) ($group['title'] ?? '')));
		if ('' === $title) {
			$merged_by_title[] = $group;
			continue;
		}

		$merged_by_title[ $title ] = $group;
	}

	foreach ($overlay_groups as $group) {
		if (!is_array($group)) {
			continue;
		}

		$title = strtolower(trim((string) ($group['title'] ?? '')));
		if ('' === $title) {
			$merged_by_title[] = $group;
			continue;
		}

		if (!isset($merged_by_title[ $title ])) {
			$merged_by_title[ $title ] = $group;
			continue;
		}

		$existing_names = isset($merged_by_title[ $title ]['field_names']) && is_array($merged_by_title[ $title ]['field_names'])
			? $merged_by_title[ $title ]['field_names']
			: array();
		$overlay_names  = isset($group['field_names']) && is_array($group['field_names']) ? $group['field_names'] : array();

		$merged_by_title[ $title ]['field_names'] = array_values(array_unique(array_merge($existing_names, $overlay_names)));
	}

	return array_values($merged_by_title);
}

/**
 * Merge a base consultation with a reorder/overlay entry for display.
 *
 * @param array<string,mixed> $base    Base consultation entry.
 * @param array<string,mixed> $overlay Reorder consultation entry.
 * @return array<string,mixed>
 */
function trimvia_merge_consultation_entries(array $base, array $overlay)
{
	$merged = $base;

	$merged['form_data'] = trimvia_merge_consultation_form_data(
		is_array($base['form_data'] ?? null) ? $base['form_data'] : array(),
		is_array($overlay['form_data'] ?? null) ? $overlay['form_data'] : array()
	);

	$merged['form_groups'] = trimvia_merge_consultation_form_groups(
		is_array($base['form_groups'] ?? null) ? $base['form_groups'] : array(),
		is_array($overlay['form_groups'] ?? null) ? $overlay['form_groups'] : array()
	);

	if (!empty($overlay['is_reorder'])) {
		$merged['is_reorder'] = $overlay['is_reorder'];
	}

	if (!empty($overlay['previous_order'])) {
		$merged['previous_order'] = $overlay['previous_order'];
	}

	if (!empty($overlay['completed_by']) && empty($merged['completed_by'])) {
		$merged['completed_by'] = $overlay['completed_by'];
	}

	return $merged;
}

/**
 * Resolve sparse reorder consultations by merging in the original order's answers.
 *
 * @param WC_Order            $order      Source order.
 * @param array<string,mixed> $form_entry Stored consultation payload.
 * @param string              $form_key   Entry key on the source order.
 * @return array<string,mixed>
 */
function trimvia_resolve_consultation_display_entry(WC_Order $order, array $form_entry, $form_key = '', ?array $history_orders = null)
{
	$answered_fields = trimvia_count_populated_consultation_fields($form_entry['form_data'] ?? array());
	$needs_merge     = !empty($form_entry['is_reorder']) || $answered_fields < 2;

	if (!$needs_merge) {
		return $form_entry;
	}

	$skip_keys         = '' !== (string) $form_key ? array((string) $form_key) : array();
	$original_entry    = null;
	$previous_order_id = absint($form_entry['previous_order'] ?? 0);

	if ($previous_order_id > 0) {
		$previous_order = wc_get_order($previous_order_id);
		if ($previous_order instanceof WC_Order) {
			$original_entry = trimvia_find_best_form_entry_on_order($previous_order, $form_entry);
		}
	}

	if (!$original_entry) {
		$original_entry = trimvia_find_best_form_entry_on_order($order, $form_entry, $skip_keys);
	}

	if (!$original_entry) {
		if (null === $history_orders) {
			$history_orders = trimvia_get_previous_consultation_orders_for_review($order);
		}
		$original_entry = trimvia_find_consultation_entry_in_order_history(
			is_array($history_orders) ? $history_orders : array(),
			$form_entry,
			array((int) $order->get_id())
		);
	}

	if (!$original_entry || $original_entry === $form_entry) {
		return $form_entry;
	}

	return trimvia_merge_consultation_entries($original_entry, $form_entry);
}

/**
 * Keep only consultation groups that contain at least one answered field.
 *
 * @param array<int,array<string,mixed>> $form_groups Group definitions.
 * @param array<string,mixed>            $form_data   Stored answers.
 * @return array<int,array<string,mixed>>
 */
function trimvia_filter_consultation_groups_with_answers(array $form_groups, $form_data)
{
	if (!is_array($form_data)) {
		return array();
	}

	if (empty($form_groups)) {
		return $form_groups;
	}

	$filtered = array();

	foreach ($form_groups as $group) {
		if (!is_array($group)) {
			continue;
		}

		$field_names = isset($group['field_names']) && is_array($group['field_names']) ? $group['field_names'] : array();
		$has_answer  = false;

		foreach ($field_names as $field_name) {
			if (isset($form_data[ $field_name ]) && trimvia_consultation_field_has_value($form_data[ $field_name ])) {
				$has_answer = true;
				break;
			}
		}

		if ($has_answer) {
			$filtered[] = $group;
		}
	}

	return $filtered;
}

/**
 * Find the richest matching consultation entry across prior orders.
 *
 * @param array<int,WC_Order> $previous_orders Prior orders.
 * @param array<string,mixed> $reference       Reference entry.
 * @param array<int,int>      $skip_order_ids  Orders to skip.
 * @return array<string,mixed>|null
 */
function trimvia_find_consultation_entry_in_order_history(array $previous_orders, array $reference, array $skip_order_ids = array())
{
	foreach ($previous_orders as $previous_order) {
		if (!$previous_order instanceof WC_Order) {
			continue;
		}

		if (in_array((int) $previous_order->get_id(), $skip_order_ids, true)) {
			continue;
		}

		$entry = trimvia_find_best_form_entry_on_order($previous_order, $reference);
		if ($entry && trimvia_count_populated_consultation_fields($entry['form_data'] ?? array()) > 0) {
			return $entry;
		}
	}

	return null;
}

/**
 * Build consultation_data for patient-order-consultation.php from a stored entry.
 *
 * When reorder form_groups do not match merged answers, falls back to flat field rendering.
 *
 * @param array<string,mixed> $form_entry    Stored consultation payload.
 * @param string              $fallback_name Display name when form_title is missing.
 * @return array<string,mixed>
 */
function trimvia_prepare_consultation_display_data(array $form_entry, $fallback_name = '')
{
	$form_data = $form_entry['form_data'] ?? array();
	if (!is_array($form_data)) {
		$form_data = maybe_unserialize($form_data);
	}
	if (!is_array($form_data)) {
		$form_data = array();
	}

	$form_data = apply_filters('woopw_consultation_form_entry', $form_data);
	if (!is_array($form_data)) {
		$form_data = array();
	}

	$form_groups = is_array($form_entry['form_groups'] ?? null) ? $form_entry['form_groups'] : array();
	if (!empty($form_groups)) {
		$form_groups = trimvia_filter_consultation_groups_with_answers($form_groups, $form_data);

		$has_renderable_field = false;
		foreach ($form_groups as $group) {
			if (!is_array($group)) {
				continue;
			}

			$field_names = isset($group['field_names']) && is_array($group['field_names']) ? $group['field_names'] : array();
			foreach ($field_names as $field_name) {
				if (isset($form_data[ $field_name ]) && trimvia_consultation_field_has_value($form_data[ $field_name ])) {
					$has_renderable_field = true;
					break 2;
				}
			}
		}

		if (!$has_renderable_field) {
			$form_groups = array();
		}
	}

	$name = '' !== (string) $fallback_name
		? (string) $fallback_name
		: (string) ($form_entry['form_title'] ?? __('Consultation', 'woopw'));

	return array(
		'name'        => $name,
		'attempts'    => $form_data,
		'form_groups' => $form_groups,
	);
}

/**
 * Render consultation Q&A markup for a stored form entry.
 *
 * @param WC_Order $order      Source order.
 * @param array    $form_entry Stored consultation payload.
 * @param string   $form_key   Entry key on the source order.
 * @return string
 */
function trimvia_render_previous_consultation_body(WC_Order $order, array $form_entry, $form_key = '', ?array $history_orders = null)
{
	$form_entry = trimvia_resolve_consultation_display_entry($order, $form_entry, $form_key, $history_orders);

	$template = trimvia_get_patient_consultation_template_path();
	if ('' === $template) {
		return '';
	}

	$consultation_data = trimvia_prepare_consultation_display_data($form_entry);

	$ordered_by = trim($order->get_formatted_billing_full_name());
	if ('' === $ordered_by) {
		$ordered_by = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
	}

	$ordered_on = $order->get_date_created()
		? $order->get_date_created()->date_i18n('F j, Y h:i A')
		: '—';

	$order_number = (int) $order->get_id();
	$is_prescriber = 1;
	$show_q_desc   = true;

	ob_start();
	include $template;

	return (string) ob_get_clean();
}

/**
 * Build a single previous-consultation accordion item.
 *
 * @param WC_Order $order      Previous order.
 * @param array    $form_entry Consultation payload.
 * @param string   $form_key   Unique form key.
 * @param int                $index           Zero-based index.
 * @param array<int,WC_Order>|null $history_orders Cached prior orders for resolve.
 * @return string
 */
function trimvia_build_previous_consultation_accordion_item(WC_Order $order, array $form_entry, $form_key, $index, ?array $history_orders = null)
{
	$order_id   = (int) $order->get_id();
	$collapse_id = 'trimvia-prev-consult-' . $order_id . '-' . $index;
	$heading_id  = $collapse_id . '-heading';

	$condition_label = '';
	if (!empty($form_entry['condition_id'])) {
		$term = get_term((int) $form_entry['condition_id'], 'condition');
		if ($term instanceof WP_Term && !is_wp_error($term)) {
			$condition_label = $term->name;
		}
	}
	if ('' === $condition_label && !empty($form_entry['condition_slug'])) {
		$term = get_term_by('slug', sanitize_title((string) $form_entry['condition_slug']), 'condition');
		if ($term instanceof WP_Term) {
			$condition_label = $term->name;
		}
	}
	if ('' === $condition_label && !empty($form_entry['form_title'])) {
		$condition_label = (string) $form_entry['form_title'];
	}
	if ('' === $condition_label) {
		$condition_label = sprintf(
			/* translators: %s: WooCommerce order number */
			__('Order #%s', 'woopw'),
			$order->get_order_number()
		);
	}

	$date_label = $order->get_date_created()
		? $order->get_date_created()->date_i18n('F j, Y h:i A')
		: '';

	$completed_by = '';
	if (!empty($form_entry['completed_by'])) {
		$completed_by = (string) $form_entry['completed_by'];
	}

	$body_html = trimvia_render_previous_consultation_body($order, $form_entry, (string) $form_key, $history_orders);

	$reorder_notice = '';
	if (!empty($form_entry['is_reorder'])) {
		$original_order_id = absint($form_entry['previous_order'] ?? 0);
		if ($original_order_id > 0) {
			$original_order = wc_get_order($original_order_id);
			if ($original_order instanceof WC_Order) {
				$edit_link = get_edit_post_link($original_order_id);
				if (!$edit_link) {
					$edit_link = admin_url('post.php?post=' . $original_order_id . '&action=edit');
				}

				$reorder_notice = sprintf(
					'<div class="trimvia-reorder-source-notice"><p><span class="trimvia-consult-meta-label">%s</span> <a href="%s" target="_blank" rel="noopener">#%s</a></p></div>',
					esc_html__('Original order', 'theme-woopm-child'),
					esc_url($edit_link),
					esc_html($original_order->get_order_number())
				);
			}
		}
	}

	$form_title = !empty($form_entry['form_title'])
		? (string) $form_entry['form_title']
		: sprintf(
			/* translators: %s: condition name */
			__('Consultation for: %s', 'woopw'),
			$condition_label
		);

	$is_reorder   = !empty($form_entry['is_reorder']);
	$date_iso     = $order->get_date_created() ? $order->get_date_created()->date('c') : '';
	$edit_link    = get_edit_post_link($order_id);
	if (!$edit_link) {
		$edit_link = admin_url('post.php?post=' . $order_id . '&action=edit');
	}

	ob_start();
	?>
	<article class="trimvia-prev-card trimvia-previous-consultation-item" data-trimvia-prev-card="1">
		<h4 class="trimvia-prev-card-sr-title" id="<?php echo esc_attr($heading_id); ?>">
			<?php echo esc_html($form_title); ?>
		</h4>
		<button
			class="trimvia-prev-card-head collapsed"
			type="button"
			data-toggle="collapse"
			data-target="#<?php echo esc_attr($collapse_id); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr($collapse_id); ?>"
		>
			<span class="trimvia-prev-card-head-main">
				<span class="trimvia-prev-card-pill"><?php echo esc_html($condition_label); ?></span>
				<span class="trimvia-prev-card-title"><?php echo esc_html($form_title); ?></span>
			</span>
			<span class="trimvia-prev-card-head-aside">
				<?php if ($date_label) : ?>
					<time class="trimvia-prev-card-date"<?php echo $date_iso ? ' datetime="' . esc_attr($date_iso) . '"' : ''; ?>>
						<?php echo esc_html($date_label); ?>
					</time>
				<?php endif; ?>
				<?php if ($is_reorder) : ?>
					<span class="trimvia-prev-card-badge"><?php esc_html_e('Reorder', 'theme-woopm-child'); ?></span>
				<?php endif; ?>
				<span class="trimvia-prev-card-chev" aria-hidden="true"></span>
			</span>
		</button>
		<div
			id="<?php echo esc_attr($collapse_id); ?>"
			class="trimvia-prev-card-panel collapse"
			data-parent="#trimviaPreviousConsultationsAccordion"
			aria-labelledby="<?php echo esc_attr($heading_id); ?>"
		>
			<div class="trimvia-prev-card-body accordion-body">
				<div class="trimvia-prev-card-meta-grid trimvia-consult-meta">
					<div class="trimvia-prev-card-meta-item">
						<span class="trimvia-prev-card-meta-label"><?php esc_html_e('Order', 'woopw'); ?></span>
						<a class="trimvia-prev-card-meta-value trimvia-prev-card-order-link" href="<?php echo esc_url($edit_link); ?>" target="_blank" rel="noopener">
							#<?php echo esc_html($order->get_order_number()); ?>
						</a>
					</div>
					<?php if ($completed_by) : ?>
						<div class="trimvia-prev-card-meta-item">
							<span class="trimvia-prev-card-meta-label"><?php esc_html_e('Completed by', 'woopw'); ?></span>
							<span class="trimvia-prev-card-meta-value"><?php echo esc_html($completed_by); ?></span>
						</div>
					<?php endif; ?>
					<?php if ($date_label) : ?>
						<div class="trimvia-prev-card-meta-item">
							<span class="trimvia-prev-card-meta-label"><?php esc_html_e('Date', 'woopw'); ?></span>
							<span class="trimvia-prev-card-meta-value"><?php echo esc_html($date_label); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<div class="trimvia-prev-card-responses">
					<?php
					if ('' !== $reorder_notice) {
						echo $reorder_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
					}

					if ('' !== $body_html) {
						echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooPW template markup.
					} else {
						echo '<p class="trimvia-previous-consultation-empty">' . esc_html__(
							'Consultation responses are unavailable for this order.',
							'woopw'
						) . '</p>';
					}
					?>
				</div>
			</div>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Pick the richest resolved consultation entry on an order for modal display.
 *
 * @param WC_Order        $order         Source order.
 * @param array<int, int> $condition_ids Optional condition IDs to match.
 * @return array{key:string,entry:array<string,mixed>}|null
 */
function trimvia_get_best_form_entry_for_order_display(WC_Order $order, array $condition_ids = array(), ?array $history_orders = null)
{
	$entries    = trimvia_get_order_form_entries($order);
	$best       = null;
	$best_score = -1;

	foreach ($entries as $form_key => $entry) {
		if (!is_array($entry)) {
			continue;
		}

		$entry_condition_id = absint($entry['condition_id'] ?? 0);
		if (!empty($condition_ids) && $entry_condition_id && !in_array($entry_condition_id, $condition_ids, true)) {
			continue;
		}

		$resolved = trimvia_resolve_consultation_display_entry($order, $entry, (string) $form_key, $history_orders);
		$score    = trimvia_count_populated_consultation_fields($resolved['form_data'] ?? array());
		if ($score > $best_score) {
			$best_score = $score;
			$best       = array(
				'key'   => (string) $form_key,
				'entry' => $resolved,
			);
		}
	}

	if (!$best || $best_score < 1) {
		return null;
	}

	return $best;
}

/**
 * Build the full #pmPreviousConsultations block.
 *
 * @param WC_Order             $current_order   Current order.
 * @param array<int, WC_Order> $previous_orders Previous orders.
 * @return string
 */
function trimvia_build_previous_consultations_section_html($current_order, array $previous_orders)
{
	if (!$current_order instanceof WC_Order || empty($previous_orders)) {
		return '';
	}

	$items_html    = '';
	$item_index    = 0;
	$condition_ids = trimvia_normalise_order_condition_ids($current_order->get_meta('_order_conditions'));

	foreach ($previous_orders as $previous_order) {
		if (!$previous_order instanceof WC_Order) {
			continue;
		}

		$best = trimvia_get_best_form_entry_for_order_display($previous_order, $condition_ids, $previous_orders);
		if (!$best) {
			continue;
		}

		$items_html .= trimvia_build_previous_consultation_accordion_item(
			$previous_order,
			$best['entry'],
			$best['key'],
			$item_index,
			$previous_orders
		);
		++$item_index;
	}

	if ('' === $items_html) {
		return '';
	}

	ob_start();
	?>
	<div id="pmPreviousConsultations" class="trimvia-previous-consultations trimvia-previous-consultations-list">
		<div class="trimvia-prev-section-head">
			<h3 class="trimvia-prev-section-title"><?php esc_html_e('Previous Consultations', 'woopw'); ?></h3>
			<span class="trimvia-prev-section-count" aria-label="<?php echo esc_attr(sprintf(
				/* translators: %d: number of previous consultations */
				_n('%d consultation', '%d consultations', $item_index, 'theme-woopm-child'),
				$item_index
			)); ?>">
				<?php echo esc_html((string) $item_index); ?>
			</span>
		</div>
		<div class="trimvia-prev-cards pm-consultation-accordion accordion" id="trimviaPreviousConsultationsAccordion">
			<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Inner markup for #pmPreviousConsultations (heading + accordion).
 *
 * @param WC_Order             $current_order   Current order.
 * @param array<int, WC_Order> $previous_orders Previous orders.
 * @return string
 */
function trimvia_build_previous_consultations_inner_html(WC_Order $current_order, array $previous_orders)
{
	$section = trimvia_build_previous_consultations_section_html($current_order, $previous_orders);
	if ('' === $section) {
		return '';
	}

	return (string) preg_replace(
		'/^<div id="pmPreviousConsultations"[^>]*>(.*)<\/div>\s*$/is',
		'$1',
		$section
	);
}

/**
 * Count accordion items already present in #pmPreviousConsultations markup.
 *
 * @param string $html Modal HTML.
 * @return int
 */
function trimvia_count_previous_consultation_items_in_html($html)
{
	if (!preg_match('/id=["\']pmPreviousConsultations["\'][^>]*>(.*)/is', $html, $matches)) {
		return 0;
	}

	$tail = (string) $matches[1];
	if (!preg_match('/accordion-item|pm-consultation-wrap/is', $tail)) {
		return 0;
	}

	return (int) preg_match_all('/class=["\'][^"\']*trimvia-prev-card/is', $tail)
		+ (int) preg_match_all('/class=["\'][^"\']*accordion-item/is', $tail)
		+ (int) preg_match_all('/class=["\'][^"\']*pm-consultation-wrap/is', $tail);
}

/**
 * Replace the #pmPreviousConsultations block in modal HTML.
 *
 * @param string $html         Modal HTML.
 * @param string $section_html Replacement block markup.
 * @return string
 */
function trimvia_replace_previous_consultations_block($html, $section_html)
{
	if ('' === trim($html) || '' === trim($section_html)) {
		return $html;
	}

	if (!preg_match('/<div[^>]*\bid=(["\'])pmPreviousConsultations\1[^>]*>/i', $html, $start, PREG_OFFSET_CAPTURE)) {
		return $html;
	}

	$start_pos = $start[0][1];
	$tag_end   = $start_pos + strlen($start[0][0]);
	$depth     = 1;
	$pos       = $tag_end;
	$length    = strlen($html);

	while ($pos < $length && $depth > 0) {
		$next_open  = stripos($html, '<div', $pos);
		$next_close = stripos($html, '</div', $pos);

		if (false === $next_close) {
			break;
		}

		if (false !== $next_open && $next_open < $next_close) {
			++$depth;
			$pos = $next_open + 4;
			continue;
		}

		--$depth;
		$close_end = strpos($html, '>', $next_close);
		if (false === $close_end) {
			break;
		}

		$pos = $close_end + 1;

		if (0 === $depth) {
			return substr($html, 0, $start_pos) . $section_html . substr($html, $pos);
		}
	}

	return $html;
}

/**
 * Inject or replace the previous-consultations block in prescriber modal HTML.
 *
 * @param string        $html  Modal HTML.
 * @param WC_Order|null $order Current order.
 * @return string
 */
function trimvia_inject_previous_consultations_into_modal_html($html, $order = null)
{
	if (!$order instanceof WC_Order || '' === trim($html)) {
		return $html;
	}

	$previous_orders = trimvia_get_previous_consultation_orders_for_review($order);
	$section_html    = trimvia_build_previous_consultations_section_html($order, $previous_orders);

	if ('' === $section_html) {
		return $html;
	}

	if (preg_match('/id=["\']pmPreviousConsultations["\']/i', $html)) {
		return trimvia_replace_previous_consultations_block($html, $section_html);
	}

	if (preg_match('/<div[^>]*\bid=["\']pmCurrentConsultations["\'][^>]*>/is', $html)) {
		return (string) preg_replace(
			'/(<div[^>]*\bid=["\']pmCurrentConsultations["\'][^>]*>.*?<\/div>\s*<\/div>)/is',
			'$1<div class="col-12 mb-4 trimvia-previous-consultations-col">' . $section_html . '</div>',
			$html,
			1
		);
	}

	if ('' !== $section_html) {
		return $html . $section_html;
	}

	return $html;
}

/**
 * Enrich prescriber modal markup (previous consultations + downstream filters).
 *
 * @param string        $html  Modal HTML.
 * @param WC_Order|null $order Current order.
 * @return string
 */
function trimvia_enrich_prescriber_modal_html($html, $order = null)
{
	if ('' === trim((string) $html) || !$order instanceof WC_Order) {
		return (string) $html;
	}

	try {
		$html = trimvia_inject_previous_consultations_into_modal_html($html, $order);
	} catch (Throwable $exception) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log('Trimvia prescriber modal inject: ' . $exception->getMessage());
		}
	}

	return (string) apply_filters('trimvia_enrich_prescriber_modal_html', $html, $order);
}

/**
 * Safe entry point for prescriber-order-review.php — never throws.
 *
 * @param mixed $order Current review order.
 * @return string
 */
function trimvia_render_previous_consultations_for_review_modal($order)
{
	if (!$order instanceof WC_Order) {
		return '';
	}

	try {
		$previous_orders = trimvia_get_previous_consultation_orders_for_review($order);
		return trimvia_build_previous_consultations_section_html($order, $previous_orders);
	} catch (Throwable $exception) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log('Trimvia previous consultations modal: ' . $exception->getMessage());
		}
		return '';
	}
}
