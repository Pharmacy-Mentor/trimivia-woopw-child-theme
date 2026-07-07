<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register footer controls in the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_register_footer_customizer_options($wp_customize)
{
	$wp_customize->add_section(
		'trimvia_footer_options',
		array(
			'title'    => __('Footer Options', 'theme-woopm-child'),
			'priority' => 36,
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_logo_type',
		array(
			'default'           => 'text',
			'sanitize_callback' => function ($value) {
				return in_array($value, array('text', 'image'), true) ? $value : 'text';
			},
		)
	);

	$wp_customize->add_control(
		'trimvia_footer_logo_type',
		array(
			'type'     => 'radio',
			'label'    => __('Footer Logo Type', 'theme-woopm-child'),
			'section'  => 'trimvia_footer_options',
			'choices'  => array(
				'text'  => __('Text Logo', 'theme-woopm-child'),
				'image' => __('Image Logo', 'theme-woopm-child'),
			),
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_logo_text_primary',
		array(
			'default'           => 'Trim',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_footer_logo_text_primary',
		array(
			'type'     => 'text',
			'label'    => __('Footer Text Logo Primary Part', 'theme-woopm-child'),
			'section'  => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_logo_text_secondary',
		array(
			'default'           => 'via',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_footer_logo_text_secondary',
		array(
			'type'     => 'text',
			'label'    => __('Footer Text Logo Secondary Part', 'theme-woopm-child'),
			'section'  => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_logo_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'trimvia_sanitize_logo_image_value',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'trimvia_footer_logo_image',
			array(
				'label'    => __('Footer Logo Image', 'theme-woopm-child'),
				'section'  => 'trimvia_footer_options',
				'settings' => 'trimvia_footer_logo_image',
			)
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_description',
		array(
			'default'           => __('Transform your health with confidence. Expert care, proven treatments, and a journey tailored to you.', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'trimvia_footer_description',
		array(
			'type'    => 'textarea',
			'label'   => __('Footer Description', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_email',
		array(
			'default'           => 'info@trimvia.co.uk',
			'sanitize_callback' => 'sanitize_email',
		)
	);

	$wp_customize->add_control(
		'trimvia_footer_email',
		array(
			'type'    => 'email',
			'label'   => __('Footer Email', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_social_links',
		array(
			'default'           => "fa-brands fa-facebook-f|https://facebook.com|Facebook\nfa-brands fa-instagram|https://instagram.com|Instagram",
			'sanitize_callback' => 'trimvia_sanitize_social_links_text',
		)
	);

	$wp_customize->add_control(
		new Trimvia_Social_Repeater_Control(
			$wp_customize,
			'trimvia_footer_social_links',
			array(
				'label'       => __('Social Media Links', 'theme-woopm-child'),
				'description' => __('Select icon, add link, and click Add New for more social items.', 'theme-woopm-child'),
				'section'     => 'trimvia_footer_options',
				'settings'    => 'trimvia_footer_social_links',
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
		'trimvia_footer_quick_menu_title',
		array(
			'default'           => __('Quick Links', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_quick_menu_title',
		array(
			'type'    => 'text',
			'label'   => __('Menu 1 Title', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);
	$wp_customize->add_setting(
		'trimvia_footer_quick_menu',
		array(
			'default'           => 0,
			'sanitize_callback' => 'trimvia_sanitize_menu_id',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_quick_menu',
		array(
			'type'    => 'select',
			'label'   => __('Menu 1: Quick Links', 'theme-woopm-child'),
			'choices' => $menu_choices,
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_legal_menu_title',
		array(
			'default'           => __('Legal', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_legal_menu_title',
		array(
			'type'    => 'text',
			'label'   => __('Menu 2 Title', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);
	$wp_customize->add_setting(
		'trimvia_footer_legal_menu',
		array(
			'default'           => 0,
			'sanitize_callback' => 'trimvia_sanitize_menu_id',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_legal_menu',
		array(
			'type'    => 'select',
			'label'   => __('Menu 2: Legal', 'theme-woopm-child'),
			'choices' => $menu_choices,
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_support_menu_title',
		array(
			'default'           => __('Support', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_support_menu_title',
		array(
			'type'    => 'text',
			'label'   => __('Menu 3 Title', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);
	$wp_customize->add_setting(
		'trimvia_footer_support_menu',
		array(
			'default'           => 0,
			'sanitize_callback' => 'trimvia_sanitize_menu_id',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_support_menu',
		array(
			'type'    => 'select',
			'label'   => __('Menu 3: Support', 'theme-woopm-child'),
			'choices' => $menu_choices,
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_copyright',
		array(
			'default'           => __('Â© 2026 Trimvia. All rights reserved.', 'theme-woopm-child'),
			'sanitize_callback' => 'trimvia_sanitize_textarea',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_copyright',
		array(
			'type'    => 'textarea',
			'label'   => __('Copyright Text', 'theme-woopm-child'),
			'description' => __('Supports links/HTML and line breaks (Enter key).', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_right_label_one',
		array(
			'default'           => __('GPhC Registered', 'theme-woopm-child'),
			'sanitize_callback' => 'trimvia_sanitize_textarea',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_right_label_one',
		array(
			'type'    => 'textarea',
			'label'   => __('Right Label 1', 'theme-woopm-child'),
			'description' => __('Supports links/HTML and line breaks (Enter key).', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_right_label_two',
		array(
			'default'           => __('ICO Registered', 'theme-woopm-child'),
			'sanitize_callback' => 'trimvia_sanitize_textarea',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_right_label_two',
		array(
			'type'    => 'textarea',
			'label'   => __('Right Label 2', 'theme-woopm-child'),
			'description' => __('Supports links/HTML and line breaks (Enter key).', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_bottom_description',
		array(
			'default'           => __('Trimvia is a private online weight management service that provides safe access to prescription weight loss treatments through UK-registered healthcare professionals. All prescriptions issued through Trimvia are dispensed by our partner, Mayberry Pharmacy, a fully regulated NHS-registered pharmacy. Always read the patient leaflet and speak to a healthcare professional before starting new treatments.', 'theme-woopm-child'),
			'sanitize_callback' => 'trimvia_sanitize_textarea',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_bottom_description',
		array(
			'type'    => 'textarea',
			'label'   => __('Bottom Footer Description', 'theme-woopm-child'),
			'description' => __('Supports links/HTML and line breaks (Enter key).', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);
}
add_action('customize_register', 'trimvia_register_footer_customizer_options');


