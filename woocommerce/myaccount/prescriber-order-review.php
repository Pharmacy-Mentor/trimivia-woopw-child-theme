<?php
/**
 * Prescriber review modal — use WooPW plugin template (same as parent theme).
 *
 * Parent theme has no override here; WooCommerce falls back to the plugin file via
 * default_template_path(). The child theme must delegate to that file so AJAX modal
 * behaviour matches parent/WooPW 1.8.2, then optionally enrich previous consultations.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$plugin_template = '';
if (function_exists('default_template_path')) {
	$plugin_template = default_template_path() . 'myaccount/prescriber-order-review.php';
}

if ('' === $plugin_template || !is_readable($plugin_template)) {
	return;
}

// WooPW passes $order, $user, $order_number, $prescriptions into this template scope.
ob_start();
include $plugin_template;
$html = (string) ob_get_clean();

if (
	'' !== $html
	&& isset($order)
	&& $order instanceof WC_Order
	&& function_exists('trimvia_enrich_prescriber_modal_html')
) {
	try {
		$html = trimvia_enrich_prescriber_modal_html($html, $order);
	} catch (Throwable $exception) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log('Trimvia prescriber modal enrich: ' . $exception->getMessage());
		}
	}
}

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin template markup.
