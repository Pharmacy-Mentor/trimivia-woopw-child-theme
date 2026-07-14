<?php
/**
 * Service sidebar highlight items linked to WooCommerce products.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Resolve an ACF post object / relationship value to a post ID.
 *
 * @param mixed $value Field value.
 * @return int
 */
function trimvia_resolve_acf_post_id($value)
{
	if (is_numeric($value)) {
		return (int) $value;
	}

	if ($value instanceof WP_Post) {
		return (int) $value->ID;
	}

	if (is_array($value)) {
		if (isset($value['ID'])) {
			return (int) $value['ID'];
		}
		if (isset($value['id'])) {
			return (int) $value['id'];
		}
	}

	return 0;
}

/**
 * Plain-text product price subtitle for service highlight items.
 *
 * @param WC_Product $product Product object.
 * @return string
 */
function trimvia_format_service_highlight_price_subtitle($product)
{
	if (!$product instanceof WC_Product) {
		return '';
	}

	$format_amount = static function ($amount) {
		$amount = is_numeric($amount) ? (float) $amount : 0.0;
		if ($amount <= 0) {
			return '';
		}

		return wp_strip_all_tags(html_entity_decode((string) wc_price($amount), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	};

	if ($product->is_type('variable')) {
		$min_price = $format_amount($product->get_variation_price('min', true));
		$max_price = $format_amount($product->get_variation_price('max', true));

		if ('' !== $min_price && '' !== $max_price && $min_price !== $max_price) {
			return sprintf(
				/* translators: 1: minimum price, 2: maximum price */
				__('From %1$s - %2$s', 'theme-woopm-child'),
				$min_price,
				$max_price
			);
		}

		if ('' !== $min_price) {
			return sprintf(
				/* translators: %s: product price */
				__('From %s', 'theme-woopm-child'),
				$min_price
			);
		}
	}

	$price = $format_amount($product->get_price());
	if ('' !== $price) {
		return sprintf(
			/* translators: %s: product price */
			__('From %s', 'theme-woopm-child'),
			$price
		);
	}

	return '';
}

/**
 * Product icon markup for a service highlight item.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function trimvia_get_service_highlight_product_icon_html($product_id)
{
	$product_id = (int) $product_id;
	if ($product_id <= 0) {
		return '';
	}

	if (function_exists('get_field')) {
		$img_id = get_field('menu_icon_image', $product_id);
		if (!empty($img_id)) {
			$img_id = is_array($img_id) && isset($img_id['ID']) ? (int) $img_id['ID'] : (int) $img_id;
			if ($img_id > 0) {
				return wp_get_attachment_image(
					$img_id,
					'thumbnail',
					false,
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
			}
		}

		$icon = get_field('menu_icon', $product_id);
		if (is_string($icon) && '' !== trim($icon)) {
			return wp_kses(
				$icon,
				array(
					'i'        => array('class' => true, 'aria-hidden' => true, 'style' => true),
					'svg'      => array(
						'class'        => true,
						'xmlns'        => true,
						'viewbox'      => true,
						'viewBox'      => true,
						'fill'         => true,
						'stroke'       => true,
						'stroke-width' => true,
						'width'        => true,
						'height'       => true,
						'aria-hidden'  => true,
					),
					'path'     => array(
						'd'               => true,
						'fill'            => true,
						'stroke'          => true,
						'stroke-width'    => true,
						'stroke-linecap'  => true,
						'stroke-linejoin' => true,
					),
					'circle'   => array('cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true),
					'line'     => array('x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true),
					'rect'     => array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true),
					'polyline' => array('points' => true, 'fill' => true, 'stroke' => true),
					'polygon'  => array('points' => true, 'fill' => true, 'stroke' => true),
				)
			);
		}
	}

	if (function_exists('wc_get_product')) {
		$product = wc_get_product($product_id);
		if ($product instanceof WC_Product) {
			$image_id = (int) $product->get_image_id();
			if ($image_id > 0) {
				return wp_get_attachment_image(
					$image_id,
					'thumbnail',
					false,
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
			}
		}
	}

	return '';
}

/**
 * Build one sidebar highlight item from a repeater row.
 *
 * @param array<string,mixed> $row ACF repeater row.
 * @return array<string,mixed>|null
 */
function trimvia_build_service_highlight_item_from_row(array $row)
{
	$product_id = trimvia_resolve_acf_post_id($row['highlight_product'] ?? 0);

	$title    = isset($row['highlight_title']) ? trim((string) $row['highlight_title']) : '';
	$subtitle = isset($row['highlight_subtitle']) ? trim((string) $row['highlight_subtitle']) : '';
	$url      = isset($row['highlight_url']) ? trim((string) $row['highlight_url']) : '';

	$icon_config = function_exists('trimvia_parse_service_icon_config')
		? trimvia_parse_service_icon_config($row, 'highlight')
		: array(
			'type'    => 'builtin',
			'builtin' => isset($row['highlight_icon']) ? (string) $row['highlight_icon'] : 'shield',
			'fa'      => '',
			'upload'  => null,
		);

	$icon_html = '';

	if ($product_id > 0 && function_exists('wc_get_product')) {
		$product = wc_get_product($product_id);
		if ($product instanceof WC_Product) {
			if ('' === $title) {
				$title = $product->get_name();
			}
			if ('' === $subtitle) {
				$subtitle = trimvia_format_service_highlight_price_subtitle($product);
			}
			if ('' === $url) {
				$url = (string) get_permalink($product_id);
			}

			$icon_html = trimvia_get_service_highlight_product_icon_html($product_id);
		}
	}

	if ('' === $title && '' === $subtitle && $product_id <= 0) {
		return null;
	}

	$item = array(
		'highlight_title'       => $title,
		'highlight_subtitle'    => $subtitle,
		'highlight_url'         => $url,
		'highlight_icon_config' => $icon_config,
	);

	if ('' !== $icon_html) {
		$item['highlight_icon_html'] = $icon_html;
	}

	return $item;
}

/**
 * Build highlight items from linked treatment products.
 *
 * @param array<int> $product_ids Product IDs.
 * @return array<int, array<string,mixed>>
 */
function trimvia_build_service_highlights_from_product_ids(array $product_ids)
{
	$items = array();

	if (!function_exists('wc_get_product')) {
		return $items;
	}

	foreach ($product_ids as $product_id) {
		$product_id = (int) $product_id;
		if ($product_id <= 0 || 'product' !== get_post_type($product_id) || 'publish' !== get_post_status($product_id)) {
			continue;
		}

		$product = wc_get_product($product_id);
		if (!$product instanceof WC_Product) {
			continue;
		}

		$icon_html = trimvia_get_service_highlight_product_icon_html($product_id);
		$item      = array(
			'highlight_title'       => $product->get_name(),
			'highlight_subtitle'    => trimvia_format_service_highlight_price_subtitle($product),
			'highlight_url'         => (string) get_permalink($product_id),
			'highlight_icon_config' => array(
				'type'    => 'builtin',
				'builtin' => 'shield',
				'fa'      => '',
				'upload'  => null,
			),
		);

		if ('' !== $icon_html) {
			$item['highlight_icon_html'] = $icon_html;
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * AJAX: product data for service highlight repeater rows.
 */
function trimvia_ajax_service_highlight_product_data()
{
	if (!current_user_can('edit_posts')) {
		wp_send_json_error(array('message' => __('Permission denied.', 'theme-woopm-child')), 403);
	}

	check_ajax_referer('trimvia_service_highlight_product', 'nonce');

	$product_id = isset($_POST['product_id']) ? (int) wp_unslash($_POST['product_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ($product_id <= 0 || !function_exists('wc_get_product')) {
		wp_send_json_error(array('message' => __('Invalid product.', 'theme-woopm-child')), 400);
	}

	$product = wc_get_product($product_id);
	if (!$product instanceof WC_Product) {
		wp_send_json_error(array('message' => __('Product not found.', 'theme-woopm-child')), 404);
	}

	wp_send_json_success(
		array(
			'title'    => $product->get_name(),
			'subtitle' => trimvia_format_service_highlight_price_subtitle($product),
			'url'      => (string) get_permalink($product_id),
		)
	);
}
add_action('wp_ajax_trimvia_service_highlight_product_data', 'trimvia_ajax_service_highlight_product_data');

/**
 * Enqueue admin JS for highlight product auto-fill.
 *
 * @param string $hook_suffix Admin page hook.
 */
function trimvia_enqueue_service_highlight_product_admin_assets($hook_suffix)
{
	if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || 'service' !== $screen->post_type) {
		return;
	}

	$js_path = get_stylesheet_directory() . '/assets/js/admin-service-highlight-product.js';
	wp_enqueue_script(
		'trimvia-service-highlight-product-admin',
		get_stylesheet_directory_uri() . '/assets/js/admin-service-highlight-product.js',
		array('jquery', 'acf-input'),
		file_exists($js_path) ? filemtime($js_path) : null,
		true
	);

	wp_localize_script(
		'trimvia-service-highlight-product-admin',
		'trimviaServiceHighlightProduct',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('trimvia_service_highlight_product'),
		)
	);
}
add_action('acf/input/admin_enqueue_scripts', 'trimvia_enqueue_service_highlight_product_admin_assets');
