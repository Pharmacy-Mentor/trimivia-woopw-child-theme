<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register header controls in the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_register_header_customizer_options($wp_customize)
{
	$wp_customize->add_section(
		'trimvia_header_options',
		array(
			'title'    => __('Header Options', 'theme-woopm-child'),
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_logo_type',
		array(
			'default'           => 'text',
			'sanitize_callback' => function ($value) {
				return in_array($value, array('text', 'image'), true) ? $value : 'text';
			},
		)
	);

	$wp_customize->add_control(
		'trimvia_header_logo_type',
		array(
			'type'     => 'radio',
			'label'    => __('Header Logo Type', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'choices'  => array(
				'text'  => __('Text Logo', 'theme-woopm-child'),
				'image' => __('Image Logo', 'theme-woopm-child'),
			),
			'settings' => 'trimvia_header_logo_type',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_logo_text_primary',
		array(
			'default'           => 'Trim',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_logo_text_primary',
		array(
			'type'        => 'text',
			'label'       => __('Text Logo Primary Part', 'theme-woopm-child'),
			'description' => __('Example: "Trim" (bold part).', 'theme-woopm-child'),
			'section'     => 'trimvia_header_options',
			'settings'    => 'trimvia_header_logo_text_primary',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_logo_text_secondary',
		array(
			'default'           => 'via',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_logo_text_secondary',
		array(
			'type'        => 'text',
			'label'       => __('Text Logo Secondary Part', 'theme-woopm-child'),
			'description' => __('Example: "via" (light part).', 'theme-woopm-child'),
			'section'     => 'trimvia_header_options',
			'settings'    => 'trimvia_header_logo_text_secondary',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_logo',
		array(
			'default'           => '',
			'sanitize_callback' => 'trimvia_sanitize_logo_image_value',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'trimvia_header_logo',
			array(
				'label'    => __('Header Logo', 'theme-woopm-child'),
				'section'  => 'trimvia_header_options',
				'settings' => 'trimvia_header_logo',
			)
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_sticky_logo',
		array(
			'default'           => '',
			'sanitize_callback' => 'trimvia_sanitize_logo_image_value',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'trimvia_header_sticky_logo',
			array(
				'label'       => __('Sticky Header Logo', 'theme-woopm-child'),
				'description' => __('Shown when header becomes sticky/scrolled.', 'theme-woopm-child'),
				'section'     => 'trimvia_header_options',
				'settings'    => 'trimvia_header_sticky_logo',
			)
		)
	);

	$menu_choices = array(
		0 => __('Select a menu', 'theme-woopm-child'),
	);

	$menus = wp_get_nav_menus();
	if (!empty($menus) && !is_wp_error($menus)) {
		foreach ($menus as $menu) {
			$menu_choices[$menu->term_id] = $menu->name;
		}
	}

	$wp_customize->add_setting(
		'trimvia_header_primary_menu',
		array(
			'default'           => 0,
			'sanitize_callback' => 'trimvia_sanitize_menu_id',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_primary_menu',
		array(
			'type'     => 'select',
			'label'    => __('Primary Menu', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'choices'  => $menu_choices,
			'settings' => 'trimvia_header_primary_menu',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_icon_class',
		array(
			'default'           => 'fa-solid fa-user',
			'sanitize_callback' => 'trimvia_sanitize_icon_class',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_icon_class',
		array(
			'type'        => 'select',
			'label'       => __('Header Icon (Font Awesome)', 'theme-woopm-child'),
			'description' => __('Choose an icon for the quick action link.', 'theme-woopm-child'),
			'section'     => 'trimvia_header_options',
			'choices'     => array(
				''                           => __('None', 'theme-woopm-child'),
				'fa-solid fa-user'           => __('User', 'theme-woopm-child'),
				'fa-solid fa-cart-shopping'  => __('Cart', 'theme-woopm-child'),
				'fa-solid fa-phone'          => __('Phone', 'theme-woopm-child'),
				'fa-solid fa-envelope'       => __('Email', 'theme-woopm-child'),
				'fa-solid fa-heart'          => __('Heart', 'theme-woopm-child'),
				'fa-solid fa-circle-info'    => __('Info', 'theme-woopm-child'),
				'fa-brands fa-whatsapp'      => __('WhatsApp', 'theme-woopm-child'),
				'fa-brands fa-instagram'     => __('Instagram', 'theme-woopm-child'),
				'fa-brands fa-facebook-f'    => __('Facebook', 'theme-woopm-child'),
			),
			'settings'    => 'trimvia_header_icon_class',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_icon_link',
		array(
			'default'           => home_url('/'),
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_icon_link',
		array(
			'type'     => 'url',
			'label'    => __('Header Icon Link', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'settings' => 'trimvia_header_icon_link',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_primary_button_text',
		array(
			'default'           => __('Start Consultation', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_primary_button_text',
		array(
			'type'     => 'text',
			'label'    => __('Primary Button Text', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'settings' => 'trimvia_header_primary_button_text',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_primary_button_link',
		array(
			'default'           => home_url('/shop/'),
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_primary_button_link',
		array(
			'type'     => 'url',
			'label'    => __('Primary Button Link', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'settings' => 'trimvia_header_primary_button_link',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_secondary_button_text',
		array(
			'default'           => __('Login', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_secondary_button_text',
		array(
			'type'     => 'text',
			'label'    => __('Secondary Button Text', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'settings' => 'trimvia_header_secondary_button_text',
		)
	);

	$wp_customize->add_setting(
		'trimvia_header_secondary_button_link',
		array(
			'default'           => home_url('/my-account/'),
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'trimvia_header_secondary_button_link',
		array(
			'type'     => 'url',
			'label'    => __('Secondary Button Link', 'theme-woopm-child'),
			'section'  => 'trimvia_header_options',
			'settings' => 'trimvia_header_secondary_button_link',
		)
	);
}
add_action('customize_register', 'trimvia_register_header_customizer_options');


