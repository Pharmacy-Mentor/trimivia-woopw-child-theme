<?php
/**
 * Shrink WooPW GP letter PDF logo from the child theme (no plugin edits).
 *
 * PMR "View PDF" regenerates on the fly via AJAX. Emails attach a generated
 * file — both paths are patched here so logo CSS becomes 100px.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Desired GP letter logo width in the PDF.
 *
 * @return int
 */
function trimvia_gp_letter_logo_width()
{
	return 100;
}

/**
 * Replace plugin .logo CSS with the Trimvia width.
 *
 * @param string $html Letter HTML.
 * @return string
 */
function trimvia_patch_gp_letter_logo_css($html)
{
	$width = absint(trimvia_gp_letter_logo_width());
	if ($width < 1) {
		$width = 100;
	}

	$css = '.logo {
            width: ' . $width . 'px;
            height: auto;
            max-width: ' . $width . 'px;
        }';

	$patched = preg_replace('/\.logo\s*\{[^}]*\}/', $css, $html, 1);
	return is_string($patched) ? $patched : $html;
}

/**
 * Invoke a private/protected static method on WOOPW_GP_LETTER_HELPER.
 *
 * @param string $method Method name.
 * @param array  $args   Arguments.
 * @return mixed
 */
function trimvia_gp_letter_call($method, array $args = array())
{
	if (!class_exists('WOOPW_GP_LETTER_HELPER')) {
		return null;
	}

	$ref = new ReflectionMethod('WOOPW_GP_LETTER_HELPER', $method);
	$ref->setAccessible(true);
	return $ref->invokeArgs(null, $args);
}

/**
 * Remove every callback on an action (including anonymous class methods).
 *
 * @param string $hook     Hook name.
 * @param int    $priority Priority.
 * @return void
 */
function trimvia_remove_all_action_callbacks($hook, $priority = 10)
{
	global $wp_filter;

	if (empty($wp_filter[$hook])) {
		return;
	}

	if ($wp_filter[$hook] instanceof WP_Hook) {
		unset($wp_filter[$hook]->callbacks[$priority]);
		return;
	}

	remove_all_actions($hook, $priority);
}

/**
 * Take over PMR GP letter PDF streaming so logo size can be patched.
 *
 * @return void
 */
function trimvia_override_gp_letter_stream_ajax()
{
	if (!is_admin()) {
		return;
	}

	trimvia_remove_all_action_callbacks('wp_ajax_woopw_stream_gp_letter', 10);
	add_action('wp_ajax_woopw_stream_gp_letter', 'trimvia_ajax_stream_gp_letter', 10);
}
add_action('admin_init', 'trimvia_override_gp_letter_stream_ajax', 20);

/**
 * Stream a GP letter PDF with the child-theme logo size.
 *
 * @return void
 */
function trimvia_ajax_stream_gp_letter()
{
	if (!class_exists('WOOPW_GP_LETTER_HELPER') || !class_exists('Dompdf\Dompdf')) {
		wp_die(esc_html__('GP letter helper unavailable.', 'theme-woopm-child'), 500);
	}

	$order_id     = absint($_GET['order_id'] ?? 0);
	$condition_id = absint($_GET['condition_id'] ?? 0);
	$sent_at      = isset($_GET['sent_at']) ? absint($_GET['sent_at']) : null;
	$security     = isset($_GET['security']) ? sanitize_text_field(wp_unslash($_GET['security'])) : '';

	if (!$order_id || !$condition_id) {
		wp_die(esc_html__('Invalid request.', 'theme-woopm-child'), 400);
	}

	if (!wp_verify_nonce($security, 'woopw_stream_gp_letter_' . $order_id . '_' . $condition_id)) {
		wp_die(esc_html__('Invalid security token.', 'theme-woopm-child'), 403);
	}

	if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
		wp_die(esc_html__('Forbidden', 'theme-woopm-child'), 403);
	}

	$order = wc_get_order($order_id);
	if (!$order) {
		wp_die(esc_html__('Order not found', 'theme-woopm-child'), 404);
	}

	$settings       = function_exists('get_field') ? get_field('gp_letter_template', 'condition_' . $condition_id) : array();
	$logo_base64    = trimvia_gp_letter_call('get_theme_logo_base64');
	$dob            = trimvia_gp_letter_call('format_dob', array($order->get_user_id()));
	$prescriber     = trimvia_gp_letter_call('get_prescriber_name', array($order));
	$gp_data        = WOOPW_ADMIN_ORDERS::get_instance()->get_gp_details($order);
	$today          = wp_date('j F, Y', $sent_at ?: time());
	$condition_name = trimvia_gp_letter_call('get_condition_name', array($condition_id));

	if (!$condition_name) {
		wp_die(esc_html__('Condition not found', 'theme-woopm-child'), 404);
	}

	$subject = 'Notification regarding dispensed medication: ' . $condition_name;

	$body_content = '';
	if (!empty($settings['gp_letter_body'])) {
		$body_content = wpautop(wp_kses_post($settings['gp_letter_body']));
	} else {
		$body_content = '
          <p>Please find details of the prescribed treatment above.</p>
          <p>If you require any further information, please do not hesitate to contact us.</p>
      ';
	}

	$med_table = trimvia_gp_letter_call('medication_table', array($order, array($condition_id)));
	$user      = get_user_by('ID', $order->get_user_id());
	if (!$user) {
		wp_die(esc_html__('User not found', 'theme-woopm-child'), 404);
	}

	$html = trimvia_gp_letter_call(
		'build_html',
		array(
			$logo_base64,
			$gp_data,
			$today,
			$subject,
			$condition_name,
			array($condition_id),
			$user,
			$dob,
			$body_content,
			$med_table,
			$order,
			$prescriber,
		)
	);
	$html = trimvia_patch_gp_letter_logo_css($html);

	$filename = 'gp-letter-' . $order_id . '-' . $condition_id . '.pdf';
	$dompdf   = new Dompdf\Dompdf(
		array(
			'isRemoteEnabled' => false,
			'defaultFont'     => 'DejaVu Sans',
		)
	);
	$dompdf->load_html($html, 'UTF-8');
	$dompdf->render();
	$dompdf->stream($filename, array('Attachment' => true));
	exit;
}

/**
 * Before emailing a GP letter, rewrite the attached PDF with the smaller logo.
 *
 * @param array $args wp_mail args.
 * @return array
 */
function trimvia_patch_gp_letter_mail_attachment($args)
{
	if (empty($args['attachments']) || !is_array($args['attachments'])) {
		return $args;
	}

	if (!class_exists('WOOPW_GP_LETTER_HELPER') || !class_exists('Dompdf\Dompdf')) {
		return $args;
	}

	foreach ($args['attachments'] as $index => $path) {
		if (!is_string($path) || false === strpos($path, 'gp-letter-')) {
			continue;
		}

		if (!preg_match('#presc-gp-order[/\\\\](\d+)[/\\\\]gp-letter-#', $path, $matches)) {
			continue;
		}

		$order_id = absint($matches[1]);
		$order    = wc_get_order($order_id);
		if (!$order) {
			continue;
		}

		$condition_ids = WOOPW_GP_LETTER_HELPER::get_all_conditions($order);
		if (empty($condition_ids)) {
			continue;
		}

		$settings = function_exists('get_field')
			? get_field('gp_letter_template', 'condition_' . $condition_ids[0])
			: array();

		// Rebuild using the same public generator, then re-render HTML with patched CSS.
		$user_id     = $order->get_user_id();
		$user        = get_user_by('ID', $user_id);
		$gp_data     = WOOPW_ADMIN_ORDERS::get_instance()->get_gp_details($order);
		$today       = wp_date('j F, Y');
		$logo_base64 = trimvia_gp_letter_call('get_theme_logo_base64');
		$dob         = trimvia_gp_letter_call('format_dob', array($user_id));
		$prescriber  = trimvia_gp_letter_call('get_prescriber_name', array($order));

		$condition_names = array();
		foreach ($condition_ids as $cid) {
			$name = trimvia_gp_letter_call('get_condition_name', array($cid));
			if ($name) {
				$condition_names[] = $name;
			}
		}
		if (empty($condition_names) || !$user) {
			continue;
		}

		$last           = array_pop($condition_names);
		$condition_list = $condition_names
			? implode(', ', $condition_names) . ' and ' . $last
			: $last;
		$subject        = (1 === count($condition_ids))
			? 'Notification regarding dispensed medication: ' . $condition_list
			: 'Notification regarding dispensed medications: ' . $condition_list;

		$body_blocks = array();
		$has_custom  = false;
		foreach ($condition_ids as $cid) {
			$cond_settings = function_exists('get_field') ? get_field('gp_letter_template', 'condition_' . $cid) : array();
			if (!empty($cond_settings['gp_letter_body'])) {
				$has_custom    = true;
				$body_blocks[] = '<strong>' . esc_html(trimvia_gp_letter_call('get_condition_name', array($cid))) . ':</strong> '
					. wpautop(wp_kses_post($cond_settings['gp_letter_body']));
			}
		}

		$body_content = $has_custom
			? implode('<hr style="margin:20px 0;">', $body_blocks)
			: '<p>Please find details of the prescribed treatment above.</p>
            <p>If you require any further information, please do not hesitate to contact us.</p>';

		$med_table = trimvia_gp_letter_call('medication_table', array($order, $condition_ids));
		$html      = trimvia_gp_letter_call(
			'build_html',
			array(
				$logo_base64,
				$gp_data,
				$today,
				$subject,
				$condition_list,
				$condition_ids,
				$user,
				$dob,
				$body_content,
				$med_table,
				$order,
				$prescriber,
			)
		);
		$html = trimvia_patch_gp_letter_logo_css($html);

		$dompdf = new Dompdf\Dompdf(
			array(
				'isRemoteEnabled' => false,
				'defaultFont'     => 'DejaVu Sans',
			)
		);
		$dompdf->load_html($html, 'UTF-8');
		$dompdf->render();

		// Overwrite the temp attachment the plugin just created.
		file_put_contents($path, $dompdf->output());
		$args['attachments'][$index] = $path;
	}

	return $args;
}
add_filter('wp_mail', 'trimvia_patch_gp_letter_mail_attachment', 5);
