<?php
/**
 * Secure patient ID image streaming for child-theme patient detail templates.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register the stream action when the active WooPW build does not provide it.
 */
function trimvia_register_patient_id_stream_action()
{
	if (has_action('wp_ajax_woopw_stream_patient_id', 'woopw_stream_patient_id')) {
		remove_action('wp_ajax_woopw_stream_patient_id', 'woopw_stream_patient_id');
	}

	if (!has_action('wp_ajax_woopw_stream_patient_id', 'trimvia_stream_patient_id')) {
		add_action('wp_ajax_woopw_stream_patient_id', 'trimvia_stream_patient_id', 1);
	}
}
add_action('init', 'trimvia_register_patient_id_stream_action', 20);

/**
 * Whether the current user can view a patient's ID image.
 *
 * @param int $user_id Patient user ID.
 * @return bool
 */
function trimvia_current_user_can_view_patient_id($user_id)
{
	$user_id         = absint($user_id);
	$current_user_id = get_current_user_id();

	if ($user_id < 1 || $current_user_id < 1) {
		return false;
	}

	if ($current_user_id === $user_id) {
		return true;
	}

	if (
		current_user_can('administrator')
		|| current_user_can('prescriber')
		|| current_user_can('manage_options')
		|| current_user_can('manage_woocommerce')
	) {
		return true;
	}

	$current_user = wp_get_current_user();
	return $current_user instanceof WP_User
		&& (
			in_array('administrator', (array) $current_user->roles, true)
			|| in_array('prescriber', (array) $current_user->roles, true)
		);
}

/**
 * Return the first stored patient ID image entry.
 *
 * @param int $user_id Patient user ID.
 * @return array<string,mixed>|null
 */
function trimvia_get_patient_id_image_entry($user_id)
{
	$data = maybe_unserialize(get_user_meta(absint($user_id), 'patient_id', true));

	if (!is_array($data) || empty($data)) {
		return null;
	}

	if (!empty($data['path']) || !empty($data['url']) || !empty($data['file'])) {
		return $data;
	}

	$entry = reset($data);
	if (!is_array($entry)) {
		return null;
	}

	if (empty($entry['path']) && empty($entry['url']) && empty($entry['file'])) {
		return null;
	}

	return $entry;
}

/**
 * Build a nonce-protected stream URL for a patient ID image.
 *
 * @param int $user_id Patient user ID.
 * @return string
 */
function trimvia_get_patient_id_stream_url($user_id)
{
	$user_id = absint($user_id);
	if ($user_id < 1 || !trimvia_get_patient_id_image_entry($user_id)) {
		return '';
	}

	return add_query_arg(
		array(
			'action'  => 'woopw_stream_patient_id',
			'user_id' => $user_id,
			'nonce'   => wp_create_nonce('woopw_stream_patient_id_' . $user_id),
		),
		admin_url('admin-ajax.php')
	);
}

/**
 * Normalise legacy and modern stored path formats to a relative storage path.
 *
 * @param string $path Stored path or URL path.
 * @return string
 */
function trimvia_normalise_patient_id_storage_path($path)
{
	$path = trim(wp_normalize_path((string) $path));
	if ('' === $path) {
		return '';
	}

	$url_path = wp_parse_url($path, PHP_URL_PATH);
	if (is_string($url_path) && '' !== $url_path) {
		$path = trim(wp_normalize_path($url_path));
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
 * Storage roots allowed for patient ID image streaming.
 *
 * @return array<int,string>
 */
function trimvia_get_patient_id_allowed_storage_bases()
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
 * Check whether a file path is under one of the allowed storage bases.
 *
 * @param string            $filepath File path.
 * @param array<int,string> $bases    Allowed base paths.
 * @return bool
 */
function trimvia_patient_id_path_is_allowed($filepath, array $bases)
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
 * Resolve a stored patient ID entry to a readable local file path.
 *
 * @param array<string,mixed> $entry   Stored patient ID entry.
 * @param int                 $user_id Patient user ID.
 * @return string
 */
function trimvia_resolve_patient_id_file_path(array $entry, $user_id = 0)
{
	$bases = trimvia_get_patient_id_allowed_storage_bases();

	if (!empty($entry['path'])) {
		$direct_path = realpath((string) $entry['path']);
		if ($direct_path && is_file($direct_path) && trimvia_patient_id_path_is_allowed($direct_path, $bases)) {
			return $direct_path;
		}
	}

	$relative_sources = array();
	foreach (array('path', 'url', 'file') as $key) {
		if (!empty($entry[ $key ])) {
			$relative_sources[] = trimvia_normalise_patient_id_storage_path((string) $entry[ $key ]);
		}
	}

	if (!empty($entry['file'])) {
		$owner_id = absint($entry['user_id'] ?? $user_id);
		if ($owner_id > 0) {
			$relative_sources[] = $owner_id . '/' . sanitize_file_name((string) $entry['file']);
		}
	}

	foreach (array_filter(array_unique($relative_sources)) as $relative_path) {
		foreach ($bases as $base) {
			$candidate = realpath(trailingslashit($base) . $relative_path);
			if ($candidate && is_file($candidate) && trimvia_patient_id_path_is_allowed($candidate, $bases)) {
				return $candidate;
			}
		}
	}

	return '';
}

/**
 * Stop the stream request with an HTTP status code.
 *
 * @param string $message Error message.
 * @param int    $status  HTTP status code.
 */
function trimvia_patient_id_stream_die($message, $status)
{
	wp_die(esc_html($message), '', array('response' => absint($status)));
}

/**
 * Stream a stored patient ID image to authorised users.
 */
function trimvia_stream_patient_id()
{
	$user_id = absint($_GET['user_id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce   = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if (!trimvia_current_user_can_view_patient_id($user_id)) {
		trimvia_patient_id_stream_die('Forbidden', 403);
	}

	if (!wp_verify_nonce($nonce, 'woopw_stream_patient_id_' . $user_id)) {
		trimvia_patient_id_stream_die('Forbidden', 403);
	}

	$entry = trimvia_get_patient_id_image_entry($user_id);
	if (!$entry) {
		trimvia_patient_id_stream_die('No ID image found', 404);
	}

	$filepath = trimvia_resolve_patient_id_file_path($entry, $user_id);
	if ('' === $filepath) {
		trimvia_patient_id_stream_die('Invalid file', 400);
	}

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime  = $finfo->file($filepath);

	if (!in_array($mime, array('image/jpeg', 'image/png', 'image/gif', 'image/webp'), true)) {
		trimvia_patient_id_stream_die('File type not allowed', 403);
	}

	$filename = !empty($entry['file']) ? sanitize_file_name((string) $entry['file']) : basename($filepath);

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
