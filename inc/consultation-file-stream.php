<?php
/**
 * Secure consultation upload streaming for child-theme consultation templates.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register the child stream endpoint for consultation upload previews.
 */
function trimvia_register_consultation_upload_stream_actions()
{
	add_action('wp_ajax_trimvia_stream_consultation_file', 'trimvia_stream_consultation_file');
	add_action('wp_ajax_nopriv_trimvia_stream_consultation_file', 'trimvia_stream_consultation_file');

	remove_action('wp_ajax_woopw_stream_consultation_file', 'woopw_stream_consultation_file');
	remove_action('wp_ajax_nopriv_woopw_stream_consultation_file', 'woopw_stream_consultation_file');

	if (!has_action('wp_ajax_woopw_stream_consultation_file', 'trimvia_stream_woopw_consultation_file')) {
		add_action('wp_ajax_woopw_stream_consultation_file', 'trimvia_stream_woopw_consultation_file', 1);
	}

	if (!has_action('wp_ajax_nopriv_woopw_stream_consultation_file', 'trimvia_stream_woopw_consultation_file')) {
		add_action('wp_ajax_nopriv_woopw_stream_consultation_file', 'trimvia_stream_woopw_consultation_file', 1);
	}
}
add_action('init', 'trimvia_register_consultation_upload_stream_actions', 20);

/**
 * Normalise stored upload paths and URLs to a relative storage path.
 *
 * @param string $path Stored path or URL.
 * @return string
 */
function trimvia_normalise_consultation_storage_path($path)
{
	$path = trim(wp_normalize_path((string) $path));
	if ('' === $path) {
		return '';
	}

	if (filter_var($path, FILTER_VALIDATE_URL)) {
		$url_path = wp_parse_url($path, PHP_URL_PATH);
		if (is_string($url_path) && '' !== $url_path) {
			$path = trim(wp_normalize_path($url_path));
		}
	}

	$bases = array();
	if (defined('CFLP_STORAGE_PATH')) {
		$bases[] = wp_normalize_path(CFLP_STORAGE_PATH);
	}
	$bases[] = wp_normalize_path(ABSPATH . 'sfs/');

	foreach ($bases as $base) {
		if ('' !== $base && 0 === strpos($path, $base)) {
			return ltrim(substr($path, strlen($base)), '/');
		}
	}

	if (0 === strpos($path, '/sfs/')) {
		return ltrim(substr($path, 5), '/');
	}

	if (0 === strpos($path, 'sfs/')) {
		return ltrim(substr($path, 4), '/');
	}

	$sfs_pos = strrpos($path, '/sfs/');
	if (false !== $sfs_pos) {
		return ltrim(substr($path, $sfs_pos + 5), '/');
	}

	return ltrim($path, '/');
}

/**
 * Storage roots allowed for consultation uploads.
 *
 * @return array<int,string>
 */
function trimvia_get_consultation_allowed_storage_bases()
{
	$bases = array();

	if (defined('CFLP_STORAGE_PATH')) {
		$bases[] = CFLP_STORAGE_PATH;
	}

	$bases[] = ABSPATH . 'sfs/';

	$bases = array_filter(array_map('realpath', $bases));
	$bases = array_map('wp_normalize_path', $bases);

	return array_values(array_unique($bases));
}

/**
 * Check whether a file path is inside an allowed storage base.
 *
 * @param string            $filepath File path.
 * @param array<int,string> $bases    Allowed base paths.
 * @return bool
 */
function trimvia_consultation_path_is_allowed($filepath, array $bases)
{
	$filepath = wp_normalize_path((string) $filepath);

	foreach ($bases as $base) {
		$base = trailingslashit(wp_normalize_path((string) $base));
		if (0 === stripos($filepath, $base)) {
			return true;
		}
	}

	return false;
}

/**
 * Resolve a stored consultation upload entry to a readable local file path.
 *
 * @param array<string,mixed> $file Upload metadata.
 * @return string
 */
function trimvia_resolve_consultation_file_path(array $file)
{
	$bases = trimvia_get_consultation_allowed_storage_bases();

	foreach (array('path', 'url', 'value') as $key) {
		if (empty($file[ $key ])) {
			continue;
		}

		$direct_path = realpath((string) $file[ $key ]);
		if ($direct_path && is_file($direct_path) && trimvia_consultation_path_is_allowed($direct_path, $bases)) {
			return $direct_path;
		}
	}

	$relative_sources = array();
	foreach (array('path', 'url', 'value', 'file') as $key) {
		if (!empty($file[ $key ])) {
			$relative_sources[] = trimvia_normalise_consultation_storage_path((string) $file[ $key ]);
		}
	}

	if (!empty($file['file'])) {
		$owner_id = absint($file['user_id'] ?? 0);
		if ($owner_id > 0) {
			$relative_sources[] = $owner_id . '/' . sanitize_file_name((string) $file['file']);
		}
	}

	foreach (array_filter(array_unique($relative_sources)) as $relative_path) {
		foreach ($bases as $base) {
			$candidate = realpath(trailingslashit($base) . $relative_path);
			if ($candidate && is_file($candidate) && trimvia_consultation_path_is_allowed($candidate, $bases)) {
				return $candidate;
			}
		}
	}

	return '';
}

/**
 * Determine a consultation upload MIME type.
 *
 * @param array<string,mixed> $file     Upload metadata.
 * @param string             $filepath Optional resolved file path.
 * @return string
 */
function trimvia_get_consultation_file_mime(array $file, $filepath = '')
{
	foreach (array('file_type', 'type', 'mime') as $key) {
		if (!empty($file[ $key ]) && is_string($file[ $key ]) && false !== strpos($file[ $key ], '/')) {
			return (string) $file[ $key ];
		}
	}

	if ('' !== $filepath && is_file($filepath)) {
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime  = $finfo->file($filepath);
		return is_string($mime) ? $mime : '';
	}

	return '';
}

/**
 * Build a stream URL for a consultation upload.
 *
 * @param array<string,mixed> $file      Upload metadata.
 * @param int                 $order_id  Optional order ID.
 * @param string              $order_key Optional order key for guest access.
 * @return string
 */
function trimvia_get_consultation_file_stream_url(array $file, $order_id = 0, $order_key = '')
{
	$filepath = trimvia_resolve_consultation_file_path($file);
	if ('' === $filepath) {
		return '';
	}

	$token = str_replace('-', '', wp_generate_uuid4());
	$mime  = trimvia_get_consultation_file_mime($file, $filepath);

	set_transient(
		'trimvia_consult_file_' . $token,
		array(
			'path'     => $filepath,
			'filename' => !empty($file['file']) ? sanitize_file_name((string) $file['file']) : basename($filepath),
			'mime'     => $mime,
			'owner_id' => absint($file['user_id'] ?? 0),
			'order_id' => absint($order_id),
		),
		6 * HOUR_IN_SECONDS
	);

	$args = array(
		'action' => 'trimvia_stream_consultation_file',
		'token'  => $token,
		'nonce'  => wp_create_nonce('trimvia_consultation_file_' . $token),
	);

	if ('' !== (string) $order_key) {
		$args['order_key'] = (string) $order_key;
	}

	return add_query_arg($args, admin_url('admin-ajax.php'));
}

/**
 * Render a consultation upload preview.
 *
 * @param array<string,mixed> $file      File metadata.
 * @param string              $label     Image alt/link text.
 * @param int                 $order_id  Order ID.
 * @param string              $order_key Order key.
 * @return bool
 */
function trimvia_render_consultation_upload_preview(array $file, $label, $order_id, $order_key)
{
	$stream_url = trimvia_get_consultation_file_stream_url($file, $order_id, $order_key);
	if ('' === $stream_url) {
		return false;
	}

	$filepath = trimvia_resolve_consultation_file_path($file);
	$mime     = trimvia_get_consultation_file_mime($file, $filepath);

	if (0 === strpos($mime, 'image/')) {
		?>
		<a href="<?php echo esc_url($stream_url); ?>" target="_blank" rel="noopener noreferrer" class="trimvia-consultation-upload-link">
			<img
				src="<?php echo esc_url($stream_url); ?>"
				alt="<?php echo esc_attr($label); ?>"
				style="width:150px;height:150px;object-fit:contain;border-radius:6px;margin:0 12px 12px 0;" />
		</a>
		<?php
		return true;
	}

	?>
	<a href="<?php echo esc_url($stream_url); ?>" target="_blank" rel="noopener noreferrer" class="trimvia-consultation-upload-link">
		<?php echo esc_html($label ?: __('View file', 'woopw')); ?>
	</a>
	<?php
	return true;
}

/**
 * Whether the current request can stream the prepared consultation upload.
 *
 * @param array<string,mixed> $file_data Prepared file data.
 * @param string              $order_key Optional order key.
 * @return bool
 */
function trimvia_current_user_can_stream_consultation_file(array $file_data, $order_key = '')
{
	if (
		current_user_can('administrator')
		|| current_user_can('prescriber')
		|| current_user_can('manage_options')
		|| current_user_can('manage_woocommerce')
	) {
		return true;
	}

	$current_user = wp_get_current_user();
	if ($current_user instanceof WP_User && in_array('prescriber', (array) $current_user->roles, true)) {
		return true;
	}

	$order_id = absint($file_data['order_id'] ?? 0);
	if ($order_id > 0 && function_exists('wc_get_order')) {
		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order) {
			$current_user_id = get_current_user_id();
			if ($current_user_id > 0 && (int) $order->get_user_id() === $current_user_id) {
				return true;
			}

			if ('' !== (string) $order_key && hash_equals($order->get_order_key(), (string) $order_key)) {
				return true;
			}
		}
	}

	$owner_id = absint($file_data['owner_id'] ?? 0);
	return $owner_id > 0 && get_current_user_id() === $owner_id;
}

/**
 * Stop a consultation stream request with an HTTP status code.
 *
 * @param string $message Error message.
 * @param int    $status  HTTP status code.
 */
function trimvia_consultation_file_stream_die($message, $status)
{
	wp_die(esc_html($message), '', array('response' => absint($status)));
}

/**
 * Output a resolved consultation file.
 *
 * @param string $filepath Resolved local file path.
 * @param string $filename Optional download/display filename.
 */
function trimvia_output_consultation_file($filepath, $filename = '')
{
	$filepath = realpath((string) $filepath);
	$bases    = trimvia_get_consultation_allowed_storage_bases();

	if (!$filepath || !is_file($filepath) || !trimvia_consultation_path_is_allowed($filepath, $bases)) {
		trimvia_consultation_file_stream_die('Invalid file', 400);
	}

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime  = $finfo->file($filepath);

	if (!in_array($mime, array('application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'), true)) {
		trimvia_consultation_file_stream_die('File type not allowed', 403);
	}

	$filename = '' !== (string) $filename ? sanitize_file_name((string) $filename) : basename($filepath);

	header('Content-Type: ' . $mime);
	header('Content-Disposition: inline; filename="' . $filename . '"');
	header('Content-Length: ' . filesize($filepath));
	header('Cache-Control: private, no-store');
	header('X-Content-Type-Options: nosniff');

	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	readfile($filepath); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}

/**
 * Find an uploaded consultation file using the query shape emitted by WooPW templates.
 *
 * @param WC_Order $order      Order object.
 * @param string   $field_key  Consultation field key/name.
 * @param int      $file_index Requested file index.
 * @return array<string,mixed>
 */
function trimvia_find_woopw_consultation_file($order, $field_key, $file_index)
{
	if (!$order instanceof WC_Order) {
		return array();
	}

	$forms = maybe_unserialize($order->get_meta('_cflp_form_data'));
	if (!is_array($forms) || empty($forms)) {
		return array();
	}

	$field_key  = (string) $field_key;
	$file_index = max(0, absint($file_index));

	foreach ($forms as $form) {
		if (!is_array($form)) {
			continue;
		}

		$form_data = isset($form['form_data']) && is_array($form['form_data']) ? $form['form_data'] : $form;

		foreach ($form_data as $form_field_key => $field) {
			if (!is_array($field)) {
				continue;
			}

			$field_type = (string) ($field['type'] ?? '');
			if (!in_array($field_type, array('file', 'image'), true)) {
				continue;
			}

			$saved_field_key = (string) ($field['name'] ?? $form_field_key);
			if ('' !== $field_key && $field_key !== $saved_field_key && $field_key !== (string) $form_field_key) {
				continue;
			}

			$file_meta = isset($field['file_meta']) && is_array($field['file_meta']) ? $field['file_meta'] : array();
			$files     = isset($file_meta['files']) && is_array($file_meta['files']) && !empty($file_meta['files'])
				? $file_meta['files']
				: array_filter(array($file_meta));

			if (isset($files[ $file_index ]) && is_array($files[ $file_index ])) {
				return $files[ $file_index ];
			}

			if (!empty($field['value'])) {
				$values = is_array($field['value']) ? array_values($field['value']) : explode(',', (string) $field['value']);
				if (!empty($values[ $file_index ])) {
					return array(
						'path'    => trim((string) $values[ $file_index ]),
						'url'     => trim((string) $values[ $file_index ]),
						'user_id' => (int) $order->get_user_id(),
					);
				}
			}
		}
	}

	return array();
}

/**
 * Stream consultation uploads for WooPW's native admin-ajax URL format.
 */
function trimvia_stream_woopw_consultation_file()
{
	$order_id   = absint($_GET['order_id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$field_key  = isset($_GET['field_key']) ? sanitize_text_field(wp_unslash($_GET['field_key'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$file_index = absint($_GET['file_index'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$order_key  = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce      = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ($order_id < 1 || '' === $field_key || !wp_verify_nonce($nonce, 'woopw_consultation_file_' . $order_id . '_' . $field_key)) {
		trimvia_consultation_file_stream_die('Forbidden', 403);
	}

	$order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
	if (!$order instanceof WC_Order) {
		trimvia_consultation_file_stream_die('Order not found', 404);
	}

	$file = trimvia_find_woopw_consultation_file($order, $field_key, $file_index);
	if (empty($file)) {
		trimvia_consultation_file_stream_die('File not found', 404);
	}

	$filepath = trimvia_resolve_consultation_file_path($file);
	if ('' === $filepath) {
		trimvia_consultation_file_stream_die('Invalid file', 400);
	}

	$file_data = array(
		'path'     => $filepath,
		'filename' => !empty($file['file']) ? sanitize_file_name((string) $file['file']) : basename($filepath),
		'owner_id' => absint($file['user_id'] ?? $order->get_user_id()),
		'order_id' => $order_id,
	);

	if (!trimvia_current_user_can_stream_consultation_file($file_data, $order_key)) {
		trimvia_consultation_file_stream_die('Forbidden', 403);
	}

	trimvia_output_consultation_file($filepath, (string) $file_data['filename']);
}

/**
 * Stream a prepared consultation upload.
 */
function trimvia_stream_consultation_file()
{
	$token     = isset($_GET['token']) ? sanitize_key(wp_unslash($_GET['token'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce     = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$order_key = isset($_GET['order_key']) ? sanitize_text_field(wp_unslash($_GET['order_key'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ('' === $token || !wp_verify_nonce($nonce, 'trimvia_consultation_file_' . $token)) {
		trimvia_consultation_file_stream_die('Forbidden', 403);
	}

	$file_data = get_transient('trimvia_consult_file_' . $token);
	if (!is_array($file_data) || empty($file_data['path'])) {
		trimvia_consultation_file_stream_die('File not found', 404);
	}

	if (!trimvia_current_user_can_stream_consultation_file($file_data, $order_key)) {
		trimvia_consultation_file_stream_die('Forbidden', 403);
	}

	$filename = !empty($file_data['filename']) ? sanitize_file_name((string) $file_data['filename']) : basename((string) $file_data['path']);
	trimvia_output_consultation_file((string) $file_data['path'], $filename);
}
