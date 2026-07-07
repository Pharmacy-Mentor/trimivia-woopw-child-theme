<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register Customizer options for service page behaviour.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function trimvia_register_service_customizer_options($wp_customize)
{
	$wp_customize->add_section(
		'trimvia_service_options',
		array(
			'title'       => __('Service Pages', 'theme-woopm-child'),
			'description' => __('The Weight Loss demo WordPress page (slug weight-loss-service) redirects to your real service when a matching service post is found.', 'theme-woopm-child'),
			'priority'    => 34,
		)
	);

	$wp_customize->add_setting(
		'trimvia_weight_loss_service_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		'trimvia_weight_loss_service_id',
		array(
			'type'        => 'number',
			'label'       => __('Weight Loss service post ID', 'theme-woopm-child'),
			'description' => __('Leave at 0 to look up by slug below.', 'theme-woopm-child'),
			'section'     => 'trimvia_service_options',
			'settings'    => 'trimvia_weight_loss_service_id',
			'input_attrs' => array(
				'min'  => 0,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'trimvia_weight_loss_service_slug',
		array(
			'default'           => 'weight-loss-service',
			'sanitize_callback' => 'sanitize_title',
		)
	);

	$wp_customize->add_control(
		'trimvia_weight_loss_service_slug',
		array(
			'type'        => 'text',
			'label'       => __('Weight Loss service slug (primary)', 'theme-woopm-child'),
			'description' => __('First slug tried when post ID is 0. Default: weight-loss-service', 'theme-woopm-child'),
			'section'     => 'trimvia_service_options',
			'settings'    => 'trimvia_weight_loss_service_slug',
		)
	);

	$wp_customize->add_setting(
		'trimvia_treatments_page_condition_slug',
		array(
			'default'           => 'weight-loss',
			'sanitize_callback' => 'sanitize_title',
		)
	);

	$wp_customize->add_control(
		'trimvia_treatments_page_condition_slug',
		array(
			'type'        => 'text',
			'label'       => __('Treatments page condition slug', 'theme-woopm-child'),
			'description' => __('WordPress page using page-treatments.php shows the same layout as the condition archive. Set the `condition` taxonomy slug (e.g. weight-loss). Override per request with ?condition-slug= slug.', 'theme-woopm-child'),
			'section'     => 'trimvia_service_options',
			'settings'    => 'trimvia_treatments_page_condition_slug',
		)
	);

	$wp_customize->add_setting(
		'trimvia_condition_archive_always_public_layout',
		array(
			'default'           => false,
			'sanitize_callback' => function ($value) {
				return (bool) $value;
			},
		)
	);

	$wp_customize->add_control(
		'trimvia_condition_archive_always_public_layout',
		array(
			'type'        => 'checkbox',
			'label'       => __('Condition URLs: always show full treatments page', 'theme-woopm-child'),
			'description' => __('If checked, /condition/â€¦ always uses the hero + product grid (for everyone), even when a logged-in user has already completed a consultation for that condition. Leave unchecked to show the post-consultation â€œsubmission completeâ€ layout for those users.', 'theme-woopm-child'),
			'section'     => 'trimvia_service_options',
			'settings'    => 'trimvia_condition_archive_always_public_layout',
		)
	);
}
add_action('customize_register', 'trimvia_register_service_customizer_options');


