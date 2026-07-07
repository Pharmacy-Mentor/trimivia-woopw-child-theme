<?php
/**
 * Service page icon helpers (built-in SVG, Font Awesome, custom upload).
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Built-in SVG icon slugs for service features / highlights.
 *
 * @return array<string, string>
 */
function trimvia_get_service_builtin_icon_choices()
{
	return array(
		'clock'  => __('Clock', 'theme-woopm-child'),
		'truck'  => __('Delivery truck', 'theme-woopm-child'),
		'shield' => __('Shield', 'theme-woopm-child'),
		'user'   => __('User / support', 'theme-woopm-child'),
		'pulse'  => __('Pulse / chart', 'theme-woopm-child'),
		'grid'   => __('Grid', 'theme-woopm-child'),
	);
}

/**
 * Default built-in slug when data is missing or invalid.
 *
 * @param string $context feature|highlight
 * @return string
 */
function trimvia_get_service_builtin_icon_default($context = 'feature')
{
	return ('highlight' === $context) ? 'shield' : 'clock';
}

/**
 * Normalize repeater row icon data (supports legacy select-only rows).
 *
 * @param array  $row     ACF repeater row.
 * @param string $context feature|highlight
 * @return array{type:string,builtin:string,fa:string,upload:array|null}
 */
function trimvia_parse_service_icon_config(array $row, $context = 'feature')
{
	$prefix      = ('highlight' === $context) ? 'highlight' : 'feature';
	$type_key    = $prefix . '_icon_type';
	$builtin_key = $prefix . '_icon';
	$fa_key      = $prefix . '_icon_fa';
	$upload_key  = $prefix . '_icon_upload';
	$default     = trimvia_get_service_builtin_icon_default($context);
	$choices     = trimvia_get_service_builtin_icon_choices();

	$type = isset($row[ $type_key ]) ? sanitize_key((string) $row[ $type_key ]) : '';
	if (!in_array($type, array('builtin', 'fontawesome', 'upload'), true)) {
		$type = 'builtin';
	}

	$builtin = isset($row[ $builtin_key ]) ? sanitize_key((string) $row[ $builtin_key ]) : $default;
	if (!isset($choices[ $builtin ])) {
		$builtin = $default;
	}

	$fa = '';
	if (isset($row[ $fa_key ])) {
		$fa = function_exists('trimvia_sanitize_icon_class')
			? trimvia_sanitize_icon_class((string) $row[ $fa_key ])
			: sanitize_text_field((string) $row[ $fa_key ]);
	}

	$upload = null;
	if (isset($row[ $upload_key ]) && is_array($row[ $upload_key ]) && !empty($row[ $upload_key ]['url'])) {
		$upload = $row[ $upload_key ];
	}

	if ('upload' === $type && empty($upload)) {
		$type = 'builtin';
	}
	if ('fontawesome' === $type && '' === $fa) {
		$type = 'builtin';
	}

	return array(
		'type'    => $type,
		'builtin' => $builtin,
		'fa'      => $fa,
		'upload'  => $upload,
	);
}

/**
 * Render icon markup for the service template.
 *
 * @param array<string, mixed> $config  Parsed icon config.
 * @param array<string, string> $svg_map Built-in SVG map.
 * @return string
 */
function trimvia_render_service_icon_html(array $config, array $svg_map)
{
	$default = isset($svg_map['clock']) ? 'clock' : (string) array_key_first($svg_map);

	switch ($config['type']) {
		case 'fontawesome':
			if ('' !== $config['fa']) {
				return '<i class="' . esc_attr($config['fa']) . '" aria-hidden="true"></i>';
			}
			break;

		case 'upload':
			if (is_array($config['upload']) && !empty($config['upload']['url'])) {
				$alt = isset($config['upload']['alt']) ? (string) $config['upload']['alt'] : '';
				return '<img src="' . esc_url($config['upload']['url']) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" />';
			}
			break;

		case 'builtin':
		default:
			$slug = $config['builtin'];
			if (!isset($svg_map[ $slug ])) {
				$slug = $default;
			}
			if (isset($svg_map[ $slug ])) {
				return $svg_map[ $slug ];
			}
	}

	if (isset($svg_map[ $default ])) {
		return $svg_map[ $default ];
	}

	return '';
}

/**
 * Populate built-in icon select choices in ACF.
 *
 * @param array<string, mixed> $field ACF field.
 * @return array<string, mixed>
 */
function trimvia_load_service_builtin_icon_field_choices($field)
{
	if (!in_array($field['name'], array('feature_icon', 'highlight_icon'), true)) {
		return $field;
	}

	$field['choices'] = trimvia_get_service_builtin_icon_choices();

	return $field;
}
add_filter('acf/load_field/name=feature_icon', 'trimvia_load_service_builtin_icon_field_choices');
add_filter('acf/load_field/name=highlight_icon', 'trimvia_load_service_builtin_icon_field_choices');

/**
 * Enqueue admin assets for the Font Awesome icon picker on service edit screens.
 *
 * @param string $hook_suffix Admin page hook.
 */
function trimvia_enqueue_service_icon_picker_admin_assets($hook_suffix)
{
	if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || 'service' !== $screen->post_type) {
		return;
	}

	wp_enqueue_style(
		'trimvia-fontawesome-admin',
		'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);

	$css_path = get_stylesheet_directory() . '/assets/css/admin-service-icon-picker.css';
	$js_path  = get_stylesheet_directory() . '/assets/js/admin-service-icon-picker.js';

	wp_enqueue_style(
		'trimvia-service-icon-picker-admin',
		get_stylesheet_directory_uri() . '/assets/css/admin-service-icon-picker.css',
		array('trimvia-fontawesome-admin'),
		file_exists($css_path) ? filemtime($css_path) : null
	);

	wp_enqueue_script(
		'trimvia-service-icon-picker-admin',
		get_stylesheet_directory_uri() . '/assets/js/admin-service-icon-picker.js',
		array('jquery', 'acf-input'),
		file_exists($js_path) ? filemtime($js_path) : null,
		true
	);

	wp_localize_script(
		'trimvia-service-icon-picker-admin',
		'trimviaServiceIconPicker',
		array(
			'metadataUrl' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/metadata/icons.json',
			'labels'      => array(
				'browse'  => __('Browse icons', 'theme-woopm-child'),
				'search'  => __('Search Font Awesome icons…', 'theme-woopm-child'),
				'loading' => __('Loading icons…', 'theme-woopm-child'),
				'empty'   => __('No icons found.', 'theme-woopm-child'),
				'close'   => __('Close', 'theme-woopm-child'),
			),
		)
	);
}
add_action('acf/input/admin_enqueue_scripts', 'trimvia_enqueue_service_icon_picker_admin_assets');
