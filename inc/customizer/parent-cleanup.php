<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Prevent the parent theme from registering ~100+ Customizer settings.
 *
 * Saved theme mods remain in the database; only the Customizer UI registration is skipped.
 */
function trimvia_prevent_parent_customizer_register()
{
	if (!class_exists('pharmacy_Customize')) {
		return;
	}

	remove_action('customize_register', array('pharmacy_Customize', 'register'));
}
add_action('after_setup_theme', 'trimvia_prevent_parent_customizer_register', 100);

/**
 * Register placeholder image theme mod (parent used to register this).
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_register_placeholder_image_setting($wp_customize)
{
	if ($wp_customize->get_setting('placeholder_image')) {
		return;
	}

	$sanitize_callback = 'trimvia_sanitize_logo_image_value';
	if (class_exists('PMCustomizerValidation')) {
		$validation        = new PMCustomizerValidation();
		$sanitize_callback = array($validation, 'pm_sanitize_image');
	}

	$wp_customize->add_setting(
		'placeholder_image',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'sanitize_callback' => $sanitize_callback,
			'transport'         => 'refresh',
		)
	);
}
add_action('customize_register', 'trimvia_register_placeholder_image_setting', 20);

/**
 * Child-theme Customizer cleanup for core/WooPW partials and shared settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_hide_parent_customizer_options($wp_customize)
{
	// Core/parent selective refresh targets markup the child theme does not output.
	$orphan_partial_ids = array(
		'blogname',
		'blogdescription',
		'custom_logo',
		'mobile_logo',
		'footer_custom_logo',
		'footer_bottom_logo',
		'bottom_footer_image_1',
		'bottom_footer_image_2',
		'bottom_footer_image_3',
	);

	if (isset($wp_customize->selective_refresh)) {
		foreach ($orphan_partial_ids as $partial_id) {
			$wp_customize->selective_refresh->remove_partial($partial_id);
		}
	}

	foreach ($orphan_partial_ids as $setting_id) {
		$setting = $wp_customize->get_setting($setting_id);
		if ($setting) {
			$setting->transport = 'refresh';
		}
	}

	// WooPW GP letter logo uses postMessage without a selective partial in this theme.
	$gp_letter_logo = $wp_customize->get_setting('gp_letter_logo');
	if ($gp_letter_logo) {
		$gp_letter_logo->transport = 'refresh';
	}

	if (class_exists('WP_Customize_Image_Control') && $wp_customize->get_setting('placeholder_image')) {
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'trimvia_placeholder_image',
				array(
					'label'       => __('Placeholder image', 'theme-woopm-child'),
					'description' => __('Default image when a post or product has no featured image.', 'theme-woopm-child'),
					'section'     => 'trimvia_service_options',
					'priority'    => 100,
					'settings'    => 'placeholder_image',
				)
			)
		);
	}
}
add_action('customize_register', 'trimvia_hide_parent_customizer_options', 999);

/**
 * Fall back to full refresh for postMessage settings that have no selective partial.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_customize_normalize_transport($wp_customize)
{
	if (!isset($wp_customize->selective_refresh)) {
		return;
	}

	$partial_settings = array();
	foreach ($wp_customize->selective_refresh->partials() as $partial) {
		foreach ((array) $partial->settings as $setting_id) {
			$partial_settings[ $setting_id ] = true;
		}
	}

	foreach ($wp_customize->settings() as $setting_id => $setting) {
		if ('postMessage' !== $setting->transport) {
			continue;
		}
		if (empty($partial_settings[ $setting_id ])) {
			$setting->transport = 'refresh';
		}
	}
}
add_action('customize_register', 'trimvia_customize_normalize_transport', 1000);

