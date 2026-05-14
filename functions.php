<?php
/**
 * Pharmacy Mentor - WooPW child theme bootstrap.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/class-trimvia-nav-walker.php';

/**
 * Enqueue parent + child assets.
 */
function trimvia_child_enqueue_assets()
{
	$parent_theme = wp_get_theme(get_template());
	$child_theme = wp_get_theme();

	wp_enqueue_style(
		'theme-woopw-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_theme->get('Version')
	);

	wp_enqueue_style(
		'theme-woopw-child-style',
		get_stylesheet_uri(),
		array('theme-woopw-parent-style'),
		$child_theme->get('Version')
	);

	wp_enqueue_style(
		'theme-woopw-child-responsive',
		get_stylesheet_directory_uri() . '/assets/css/style.css',
		array('theme-woopw-child-style'),
		filemtime(get_stylesheet_directory() . '/assets/css/style.css')
	);

	wp_enqueue_style(
		'theme-woopw-child-google-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'trimvia-font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);

	wp_enqueue_script(
		'theme-woopw-child-common',
		get_stylesheet_directory_uri() . '/assets/js/common.js',
		array(),
		filemtime(get_stylesheet_directory() . '/assets/js/common.js'),
		true
	);

	if (is_front_page() || is_home()) {
		wp_enqueue_script(
			'trimvia-chart-js',
			'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js',
			array(),
			null,
			true
		);
		wp_enqueue_script(
			'trimvia-homepage-chart',
			get_stylesheet_directory_uri() . '/assets/js/homepage-chart.js',
			array('trimvia-chart-js'),
			filemtime(get_stylesheet_directory() . '/assets/js/homepage-chart.js'),
			true
		);
	}

	if (is_page('consultation')) {
		wp_enqueue_script(
			'trimvia-consultation-steps',
			get_stylesheet_directory_uri() . '/assets/js/consultation-steps.js',
			array(),
			filemtime(get_stylesheet_directory() . '/assets/js/consultation-steps.js'),
			true
		);
		wp_enqueue_script(
			'trimvia-gravity-consult',
			get_stylesheet_directory_uri() . '/assets/js/trimvia-gravity-consult.js',
			array('jquery'),
			filemtime(get_stylesheet_directory() . '/assets/js/trimvia-gravity-consult.js'),
			true
		);
		wp_enqueue_script(
			'trimvia-cflp-consult',
			get_stylesheet_directory_uri() . '/assets/js/trimvia-cflp-consult.js',
			array('trimvia-gravity-consult'),
			filemtime(get_stylesheet_directory() . '/assets/js/trimvia-cflp-consult.js'),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'trimvia_child_enqueue_assets', 100);

/**
 * Sanitize Font Awesome class list.
 *
 * @param string $value Raw icon classes.
 * @return string
 */
function trimvia_sanitize_icon_class($value)
{
	$classes = preg_split('/\s+/', (string) $value);
	$classes = array_filter($classes);
	$classes = array_map('sanitize_html_class', $classes);
	$classes = array_filter($classes);

	return implode(' ', $classes);
}

/**
 * Add nav-item class to top-level menu items that have dropdowns (mega menu CSS).
 *
 * @param array    $classes CSS classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Menu args.
 * @param int      $depth   Nesting depth.
 * @return array
 */
function trimvia_nav_menu_parent_item_class($classes, $item, $args, $depth)
{
	if (0 === (int) $depth && in_array('menu-item-has-children', $classes, true)) {
		$classes[] = 'nav-item';
	}
	return $classes;
}
add_filter('nav_menu_css_class', 'trimvia_nav_menu_parent_item_class', 10, 4);

/**
 * Append dropdown chevron to top-level items with children.
 *
 * @param string   $title Menu title HTML.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Depth.
 * @return string
 */
function trimvia_nav_menu_parent_chevron($title, $item, $args, $depth)
{
	if (0 !== (int) $depth || !in_array('menu-item-has-children', $item->classes, true)) {
		return $title;
	}
	$svg  = '<svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
	return $title . $svg;
}
add_filter('nav_menu_item_title', 'trimvia_nav_menu_parent_chevron', 10, 4);

/**
 * Whether a true_false ACF field on a condition term is visible (Service-style toggles).
 * Empty / unset field defaults to true so existing terms keep current behaviour after sync.
 *
 * @param string  $field_name ACF field name.
 * @param WP_Term $term       Condition term.
 * @return bool
 */
function trimvia_condition_field_visible($field_name, $term)
{
	if (!function_exists('get_field') || !$term instanceof WP_Term) {
		return true;
	}
	$value = get_field($field_name, $term);
	if (null === $value || '' === $value) {
		return true;
	}
	return (bool) $value;
}

/**
 * Sanitize image setting value (supports attachment ID or URL).
 *
 * @param mixed $value Raw value.
 * @return string|int
 */
function trimvia_sanitize_logo_image_value($value)
{
	if (is_numeric($value)) {
		return absint($value);
	}

	return esc_url_raw((string) $value);
}

/**
 * Sanitize text area content.
 *
 * @param string $value Raw text.
 * @return string
 */
function trimvia_sanitize_textarea($value)
{
	return sanitize_textarea_field((string) $value);
}

/**
 * Sanitize footer social links textarea.
 *
 * Format per line: icon-class|https://url|Label
 *
 * @param string $value Raw textarea value.
 * @return string
 */
function trimvia_sanitize_social_links_text($value)
{
	$lines = preg_split('/\r\n|\r|\n/', (string) $value);
	$clean = array();

	foreach ($lines as $line) {
		$line = trim($line);
		if ('' === $line) {
			continue;
		}

		$parts = array_map('trim', explode('|', $line));
		$icon  = trimvia_sanitize_icon_class($parts[0] ?? '');
		$url   = esc_url_raw($parts[1] ?? '');
		$label = sanitize_text_field($parts[2] ?? '');

		if ('' === $icon || '' === $url) {
			continue;
		}

		$clean[] = $icon . '|' . $url . '|' . $label;
	}

	return implode("\n", $clean);
}

/**
 * Parse social links list from customizer textarea.
 *
 * @param string $value Sanitized textarea value.
 * @return array<int, array<string,string>>
 */
function trimvia_parse_social_links($value)
{
	$items = array();
	$lines = preg_split('/\r\n|\r|\n/', (string) $value);

	foreach ($lines as $line) {
		$line = trim($line);
		if ('' === $line) {
			continue;
		}

		$parts = array_map('trim', explode('|', $line));
		$icon  = trimvia_sanitize_icon_class($parts[0] ?? '');
		$url   = esc_url($parts[1] ?? '');
		$label = sanitize_text_field($parts[2] ?? '');

		if ('' === $icon || '' === $url) {
			continue;
		}

		$items[] = array(
			'icon'  => $icon,
			'url'   => $url,
			'label' => $label,
		);
	}

	return $items;
}

/**
 * Available social icon choices for footer links.
 *
 * @return array<string,string>
 */
function trimvia_get_social_icon_choices()
{
	return array(
		'fa-brands fa-facebook-f' => __('Facebook', 'theme-woopm-child'),
		'fa-brands fa-instagram'  => __('Instagram', 'theme-woopm-child'),
		'fa-brands fa-x-twitter'  => __('X (Twitter)', 'theme-woopm-child'),
		'fa-brands fa-linkedin-in' => __('LinkedIn', 'theme-woopm-child'),
		'fa-brands fa-youtube'    => __('YouTube', 'theme-woopm-child'),
		'fa-brands fa-whatsapp'   => __('WhatsApp', 'theme-woopm-child'),
		'fa-brands fa-telegram'   => __('Telegram', 'theme-woopm-child'),
		'fa-brands fa-pinterest-p' => __('Pinterest', 'theme-woopm-child'),
		'fa-brands fa-tiktok'     => __('TikTok', 'theme-woopm-child'),
		'fa-brands fa-snapchat'   => __('Snapchat', 'theme-woopm-child'),
		'fa-brands fa-discord'    => __('Discord', 'theme-woopm-child'),
		'fa-solid fa-globe'       => __('Website', 'theme-woopm-child'),
	);
}

if (class_exists('WP_Customize_Control')) {
	/**
	 * Repeater control for footer social links.
	 */
	class Trimvia_Social_Repeater_Control extends WP_Customize_Control
	{
		public $type = 'trimvia_social_repeater';

		/**
		 * Render control content.
		 */
		public function render_content()
		{
			$icon_choices = trimvia_get_social_icon_choices();
?>
			<div class="trimvia-social-repeater-control">
				<?php if (!empty($this->label)) : ?>
					<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
				<?php endif; ?>
				<?php if (!empty($this->description)) : ?>
					<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
				<?php endif; ?>
				<input type="hidden" class="trimvia-social-raw" <?php $this->link(); ?> value="<?php echo esc_attr((string) $this->value()); ?>">
				<div class="trimvia-social-items"></div>
				<script type="text/template" class="trimvia-social-template">
					<div class="trimvia-social-item">
						<select class="trimvia-social-icon">
							<?php foreach ($icon_choices as $icon_class => $icon_label) : ?>
								<option value="<?php echo esc_attr($icon_class); ?>"><?php echo esc_html($icon_label); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="url" class="trimvia-social-link" placeholder="<?php echo esc_attr__('https://example.com', 'theme-woopm-child'); ?>">
						<input type="text" class="trimvia-social-label" placeholder="<?php echo esc_attr__('Label (optional)', 'theme-woopm-child'); ?>">
						<button type="button" class="button-link button-link-delete trimvia-social-remove"><?php echo esc_html__('Remove', 'theme-woopm-child'); ?></button>
					</div>
				</script>
				<button type="button" class="button button-secondary trimvia-social-add"><?php echo esc_html__('Add New', 'theme-woopm-child'); ?></button>
			</div>
<?php
		}
	}
}

/**
 * Enqueue assets for custom Customizer controls.
 */
function trimvia_enqueue_customizer_control_assets()
{
	$script_path = get_stylesheet_directory() . '/assets/js/customizer-social-repeater.js';
	$script_url  = get_stylesheet_directory_uri() . '/assets/js/customizer-social-repeater.js';

	wp_enqueue_script(
		'trimvia-customizer-social-repeater',
		$script_url,
		array('jquery', 'customize-controls'),
		file_exists($script_path) ? filemtime($script_path) : null,
		true
	);

	$customizer_css = '.trimvia-social-item{display:grid;grid-template-columns:1fr;gap:8px;padding:10px;margin-bottom:10px;background:#fff;border:1px solid #dcdcde;border-radius:6px}.trimvia-social-remove{justify-self:start}.trimvia-social-add{margin-top:6px}';
	wp_add_inline_style('customize-controls', $customizer_css);
}
add_action('customize_controls_enqueue_scripts', 'trimvia_enqueue_customizer_control_assets');

/**
 * Sanitize selected nav menu ID.
 *
 * @param mixed $value Selected value.
 * @return int
 */
function trimvia_sanitize_menu_id($value)
{
	$menu_id = absint($value);
	if (!$menu_id) {
		return 0;
	}

	return wp_get_nav_menu_object($menu_id) ? $menu_id : 0;
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
			'default'           => home_url('/consultation/'),
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
			'description' => __('If checked, /condition/… always uses the hero + product grid (for everyone), even when a logged-in user has already completed a consultation for that condition. Leave unchecked to show the post-consultation “submission complete” layout for those users.', 'theme-woopm-child'),
			'section'     => 'trimvia_service_options',
			'settings'    => 'trimvia_condition_archive_always_public_layout',
		)
	);
}
add_action('customize_register', 'trimvia_register_service_customizer_options');

/**
 * Condition term for the Treatments landing page (page-treatments.php).
 *
 * @return WP_Term|null
 */
function trimvia_get_treatments_landing_condition_term()
{
	if (isset($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slug = sanitize_title(wp_unslash($_GET['condition-slug']));
		if ('' !== $slug) {
			$term = get_term_by('slug', $slug, 'condition');
			if ($term && !is_wp_error($term)) {
				return $term;
			}
		}
	}

	$setting = trim((string) get_theme_mod('trimvia_treatments_page_condition_slug', 'weight-loss'));
	if ('' === $setting) {
		return null;
	}

	$term = get_term_by('slug', sanitize_title($setting), 'condition');

	return ($term && !is_wp_error($term)) ? $term : null;
}

/**
 * Condition term when the public "treatments" layout (hero + product grid) is active (hero search + AJAX).
 *
 * @return WP_Term|null
 */
function trimvia_get_public_condition_treatments_term_for_search()
{
	if (is_tax('condition')) {
		$term = get_queried_object();
		if (!$term instanceof WP_Term || 'condition' !== $term->taxonomy) {
			return null;
		}
		if (function_exists('has_consultation_for_condition') && has_consultation_for_condition($term->slug)) {
			return null;
		}
		return $term;
	}

	if (!is_page()) {
		return null;
	}

	$stylesheet_dir = trailingslashit(get_stylesheet_directory());
	$tpl            = get_page_template();

	if ($tpl === $stylesheet_dir . 'page-treatments.php') {
		return trimvia_get_treatments_landing_condition_term();
	}

	if ($tpl === $stylesheet_dir . 'page-templates/treatments.php') {
		if (empty($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return null;
		}
		$slug = sanitize_title(wp_unslash($_GET['condition-slug']));
		if ('' === $slug) {
			return null;
		}
		$term = get_term_by('slug', $slug, 'condition');

		return ($term && !is_wp_error($term)) ? $term : null;
	}

	return null;
}

/**
 * AJAX: return shop card HTML for products in a condition, optionally filtered by search string.
 */
function trimvia_ajax_condition_treatments_search()
{
	check_ajax_referer('trimvia_condition_search', 'nonce');

	$term_id = isset($_POST['term_id']) ? absint(wp_unslash($_POST['term_id'])) : 0;
	$s       = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';

	if ($term_id < 1) {
		wp_send_json_error(array('message' => 'bad_request'), 400);
	}

	$term = get_term($term_id, 'condition');
	if (!$term || is_wp_error($term)) {
		wp_send_json_error(array('message' => 'not_found'), 404);
	}

	if (!function_exists('wc_get_products')) {
		wp_send_json_success(
			array(
				'html'  => '',
				'count' => 0,
			)
		);
	}

	$query = array(
		'status'             => 'publish',
		'limit'              => -1,
		'catalog_visibility' => 'visible',
		'orderby'            => 'menu_order',
		'order'              => 'ASC',
		'tax_query'          => array(
			array(
				'taxonomy' => 'condition',
				'field'    => 'term_id',
				'terms'    => array($term_id),
			),
		),
	);

	if ('' !== $s) {
		$query['s'] = $s;
	}

	$products = wc_get_products($query);
	if (!is_array($products)) {
		$products = array();
	}

	ob_start();
	foreach ($products as $shop_product) {
		if (!$shop_product instanceof WC_Product) {
			continue;
		}
		get_template_part(
			'template-parts/trimvia',
			'shop-product-card',
			array('product' => $shop_product)
		);
	}
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'  => $html,
			'count' => count($products),
		)
	);
}
add_action('wp_ajax_trimvia_condition_treatments_search', 'trimvia_ajax_condition_treatments_search');
add_action('wp_ajax_nopriv_trimvia_condition_treatments_search', 'trimvia_ajax_condition_treatments_search');

/**
 * Script + config for condition-scoped treatment search (hero + header).
 */
function trimvia_enqueue_condition_treatments_search()
{
	$term = trimvia_get_public_condition_treatments_term_for_search();
	if (!$term instanceof WP_Term) {
		return;
	}
	if (!trimvia_condition_field_visible('cond_products_section_visibility', $term)) {
		return;
	}

	$handle = 'trimvia-condition-treatments-search';
	$path   = get_stylesheet_directory() . '/assets/js/trimvia-condition-treatments-search.js';
	if (!is_readable($path)) {
		return;
	}

	wp_enqueue_script(
		$handle,
		get_stylesheet_directory_uri() . '/assets/js/trimvia-condition-treatments-search.js',
		array(),
		filemtime($path),
		true
	);

	wp_localize_script(
		$handle,
		'trimviaConditionSearch',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('trimvia_condition_search'),
			'termId'  => (int) $term->term_id,
			'i18n'    => array(
				'noResults' => __('No treatments match your search.', 'theme-woopm-child'),
			),
		)
	);
}
add_action('wp_enqueue_scripts', 'trimvia_enqueue_condition_treatments_search', 110);

/**
 * Resolve permalink for the Weight Loss `service` post (Customizer ID/slug, then fallbacks).
 *
 * @return string Empty string if no published service matches.
 */
function trimvia_get_weight_loss_service_permalink()
{
	$post_id = (int) get_theme_mod('trimvia_weight_loss_service_id', 0);
	if ($post_id > 0 && 'service' === get_post_type($post_id) && 'publish' === get_post_status($post_id)) {
		return get_permalink($post_id);
	}

	$slug_setting = trim((string) get_theme_mod('trimvia_weight_loss_service_slug', 'weight-loss-service'));
	$slugs        = array();
	if ('' !== $slug_setting) {
		$slugs[] = sanitize_title($slug_setting);
	}
	$slugs = array_merge($slugs, apply_filters('trimvia_weight_loss_service_extra_slugs', array('weight-loss')));
	$slugs = array_values(array_unique(array_filter($slugs)));

	foreach ($slugs as $slug) {
		$found = get_posts(
			array(
				'post_type'              => 'service',
				'name'                   => $slug,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			)
		);
		if (!empty($found)) {
			return get_permalink((int) $found[0]);
		}
	}

	return '';
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
			'sanitize_callback' => 'trimvia_sanitize_textarea',
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
			'default'           => __('© 2026 Trimvia. All rights reserved.', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_copyright',
		array(
			'type'    => 'text',
			'label'   => __('Copyright Text', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_right_label_one',
		array(
			'default'           => __('GPhC Registered', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_right_label_one',
		array(
			'type'    => 'text',
			'label'   => __('Right Label 1', 'theme-woopm-child'),
			'section' => 'trimvia_footer_options',
		)
	);

	$wp_customize->add_setting(
		'trimvia_footer_right_label_two',
		array(
			'default'           => __('ICO Registered', 'theme-woopm-child'),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'trimvia_footer_right_label_two',
		array(
			'type'    => 'text',
			'label'   => __('Right Label 2', 'theme-woopm-child'),
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
			'section' => 'trimvia_footer_options',
		)
	);
}
add_action('customize_register', 'trimvia_register_footer_customizer_options');

/**
 * Convert ACF image field value into a URL.
 *
 * @param mixed  $value ACF image value (ID, URL, or array).
 * @param string $size  Image size.
 * @return string
 */
function trimvia_acf_image_url($value, $size = 'full')
{
	if (empty($value)) {
		return '';
	}

	if (is_numeric($value)) {
		$url = wp_get_attachment_image_url((int) $value, $size);
		return $url ?: '';
	}

	if (is_array($value)) {
		if (!empty($value['url'])) {
			return esc_url_raw($value['url']);
		}

		if (!empty($value['ID'])) {
			$url = wp_get_attachment_image_url((int) $value['ID'], $size);
			return $url ?: '';
		}
	}

	return is_string($value) ? esc_url_raw($value) : '';
}

/**
 * Register Trimvia hero fields for slide post type.
 */
function trimvia_register_slide_hero_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_slide_hero',
			'title' => 'Banner Slide (Trimvia Hero)',
			'fields' => array(
				array(
					'key' => 'field_trimvia_hero_title',
					'label' => 'Hero Title',
					'name' => 'hero_title',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_hero_title_emphasis',
					'label' => 'Hero Title Emphasis',
					'name' => 'hero_title_emphasis',
					'type' => 'text',
					'instructions' => 'Blue italic part in heading, e.g. "prescribed by experts."',
				),
				array(
					'key' => 'field_trimvia_hero_subtitle',
					'label' => 'Hero Subtitle',
					'name' => 'hero_subtitle',
					'type' => 'textarea',
					'new_lines' => 'br',
				),
				array(
					'key' => 'field_trimvia_hero_bg_image',
					'label' => 'Hero Background Image',
					'name' => 'hero_bg_image',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_hero_bg_image_mobile',
					'label' => 'Hero Mobile Background Image',
					'name' => 'hero_bg_image_mobile',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_hero_primary_cta',
					'label' => 'Primary CTA',
					'name' => 'hero_primary_cta',
					'type' => 'link',
					'return_format' => 'array',
				),
				array(
					'key' => 'field_trimvia_hero_secondary_cta',
					'label' => 'Secondary CTA',
					'name' => 'hero_secondary_cta',
					'type' => 'link',
					'return_format' => 'array',
				),
				array(
					'key' => 'field_trimvia_hero_rating_score',
					'label' => 'Rating Score',
					'name' => 'hero_rating_score',
					'type' => 'text',
					'default_value' => '4.8',
				),
				array(
					'key' => 'field_trimvia_hero_rating_label',
					'label' => 'Rating Label',
					'name' => 'hero_rating_label',
					'type' => 'text',
					'default_value' => 'Google Reviews',
				),
				array(
					'key' => 'field_trimvia_hero_pills',
					'label' => 'Hero Pills',
					'name' => 'hero_pills',
					'type' => 'repeater',
					'layout' => 'table',
					'button_label' => 'Add Pill',
					'sub_fields' => array(
						array(
							'key' => 'field_trimvia_hero_pill_text',
							'label' => 'Pill Text',
							'name' => 'pill_text',
							'type' => 'text',
						),
					),
				),
				array(
					'key' => 'field_trimvia_is_active_slide',
					'label' => 'Set As Active Hero Slide',
					'name' => 'is_active_slide',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 0,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'slide',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Fields used by Trimvia child theme homepage hero.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_slide_hero_field_group');

/**
 * Register Team Member custom post type.
 */
function trimvia_register_team_member_cpt()
{
	$labels = array(
		'name'               => __('Team Members', 'theme-woopm-child'),
		'singular_name'      => __('Team Member', 'theme-woopm-child'),
		'menu_name'          => __('Team', 'theme-woopm-child'),
		'name_admin_bar'     => __('Team Member', 'theme-woopm-child'),
		'add_new'            => __('Add New', 'theme-woopm-child'),
		'add_new_item'       => __('Add New Team Member', 'theme-woopm-child'),
		'new_item'           => __('New Team Member', 'theme-woopm-child'),
		'edit_item'          => __('Edit Team Member', 'theme-woopm-child'),
		'view_item'          => __('View Team Member', 'theme-woopm-child'),
		'all_items'          => __('All Team Members', 'theme-woopm-child'),
		'search_items'       => __('Search Team Members', 'theme-woopm-child'),
		'not_found'          => __('No team members found.', 'theme-woopm-child'),
		'not_found_in_trash' => __('No team members found in Trash.', 'theme-woopm-child'),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-groups',
		'supports'           => array('title', 'editor', 'thumbnail', 'page-attributes'),
		'publicly_queryable' => false,
		'rewrite'            => false,
	);

	register_post_type('team_member', $args);
}
add_action('init', 'trimvia_register_team_member_cpt');

/**
 * Register Team Member fields.
 */
function trimvia_register_team_member_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_team_member',
			'title' => 'Team Member Details',
			'fields' => array(
				array(
					'key' => 'field_trimvia_team_member_image',
					'label' => 'Member Image',
					'name' => 'team_member_image',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_team_member_department',
					'label' => 'Department',
					'name' => 'team_member_department',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_team_member_designation',
					'label' => 'Designation',
					'name' => 'team_member_designation',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_team_member_description',
					'label' => 'Description',
					'name' => 'team_member_description',
					'type' => 'textarea',
					'new_lines' => 'br',
				),
				array(
					'key' => 'field_trimvia_team_member_show_homepage',
					'label' => 'Show on Homepage',
					'name' => 'team_member_show_homepage',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 1,
					'instructions' => 'Turn off to hide this member from homepage team section.',
				),
				array(
					'key' => 'field_trimvia_team_member_display_order',
					'label' => 'Display Order',
					'name' => 'team_member_display_order',
					'type' => 'number',
					'default_value' => 0,
					'min' => 0,
					'step' => 1,
					'instructions' => 'Lower numbers appear first on homepage.',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'team_member',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Details used on the homepage team section.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_team_member_field_group');

/**
 * Register Testimonial custom post type.
 */
function trimvia_register_testimonial_cpt()
{
	$labels = array(
		'name'               => __('Testimonials', 'theme-woopm-child'),
		'singular_name'      => __('Testimonial', 'theme-woopm-child'),
		'menu_name'          => __('Testimonials', 'theme-woopm-child'),
		'name_admin_bar'     => __('Testimonial', 'theme-woopm-child'),
		'add_new'            => __('Add New', 'theme-woopm-child'),
		'add_new_item'       => __('Add New Testimonial', 'theme-woopm-child'),
		'new_item'           => __('New Testimonial', 'theme-woopm-child'),
		'edit_item'          => __('Edit Testimonial', 'theme-woopm-child'),
		'view_item'          => __('View Testimonial', 'theme-woopm-child'),
		'all_items'          => __('All Testimonials', 'theme-woopm-child'),
		'search_items'       => __('Search Testimonials', 'theme-woopm-child'),
		'not_found'          => __('No testimonials found.', 'theme-woopm-child'),
		'not_found_in_trash' => __('No testimonials found in Trash.', 'theme-woopm-child'),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-format-quote',
		'supports'           => array('title', 'editor', 'page-attributes'),
		'publicly_queryable' => false,
		'rewrite'            => false,
	);

	register_post_type('testimonial', $args);
}
add_action('init', 'trimvia_register_testimonial_cpt');

/**
 * Register Testimonial fields.
 */
function trimvia_register_testimonial_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_testimonial',
			'title' => 'Testimonial Details',
			'fields' => array(
				array(
					'key' => 'field_trimvia_testimonial_reviewer_name',
					'label' => 'Reviewer Name',
					'name' => 'reviewer_name',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_testimonial_reviewer_date',
					'label' => 'Reviewer Date',
					'name' => 'reviewer_date',
					'type' => 'text',
					'instructions' => 'Example: 13/05/2026',
				),
				array(
					'key' => 'field_trimvia_testimonial_rating',
					'label' => 'Rating (1-5)',
					'name' => 'reviewer_rating',
					'type' => 'number',
					'default_value' => 5,
					'min' => 1,
					'max' => 5,
					'step' => 1,
				),
				array(
					'key' => 'field_trimvia_testimonial_initials',
					'label' => 'Reviewer Initials',
					'name' => 'reviewer_initials',
					'type' => 'text',
					'instructions' => 'Optional. If empty, initials are generated from reviewer name.',
				),
				array(
					'key' => 'field_trimvia_testimonial_show_homepage',
					'label' => 'Show on Homepage',
					'name' => 'show_on_homepage',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 1,
				),
				array(
					'key' => 'field_trimvia_testimonial_display_order',
					'label' => 'Display Order',
					'name' => 'display_order',
					'type' => 'number',
					'default_value' => 0,
					'min' => 0,
					'step' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'testimonial',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Content for homepage testimonials cards.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_testimonial_field_group');

/**
 * Add Home-page manual testimonial selector fields.
 */
function trimvia_register_home_testimonial_selector_fields()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_home_testimonial_selector',
			'title' => 'Home - Testimonials Selector',
			'fields' => array(
				array(
					'key' => 'field_trimvia_home_selected_testimonials',
					'label' => 'Select Testimonials',
					'name' => 'selected_testimonials',
					'type' => 'post_object',
					'instructions' => 'Used when Testimonials Type is set to Internal Post Type.',
					'post_type' => array('testimonial'),
					'post_status' => array('publish'),
					'return_format' => 'id',
					'multiple' => 1,
					'ui' => 1,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_67c5e1276258e',
								'operator' => '==',
								'value' => 'manual',
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/home.php',
					),
				),
			),
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Allows selecting specific testimonial posts for homepage.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_home_testimonial_selector_fields');

/**
 * Add Home trust bar fields (5 items + visibility).
 */
function trimvia_register_home_trust_bar_fields()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_home_trust_bar',
			'title' => 'Home - Trust Bar',
			'fields' => array(
				array(
					'key' => 'field_trimvia_home_trust_bar_tab',
					'label' => 'Trust Bar',
					'name' => '',
					'type' => 'tab',
					'placement' => 'top',
				),
				array(
					'key' => 'field_trimvia_home_trust_bar_visibility',
					'label' => 'Trust Bar Visibility',
					'name' => 'trust_bar_visibility',
					'type' => 'true_false',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => 'Show',
					'ui_off_text' => 'Hide',
				),
				array(
					'key' => 'field_trimvia_home_trust_bar_items',
					'label' => 'Trust Bar Items',
					'name' => 'trust_bar_items',
					'type' => 'repeater',
					'instructions' => 'Add up to 5 trust items.',
					'layout' => 'block',
					'button_label' => 'Add Trust Item',
					'min' => 1,
					'max' => 5,
					'sub_fields' => array(
						array(
							'key' => 'field_trimvia_home_trust_bar_icon_class',
							'label' => 'Icon Class (Font Awesome)',
							'name' => 'icon_class',
							'type' => 'text',
							'instructions' => 'Example: fa-solid fa-shield-halved',
						),
						array(
							'key' => 'field_trimvia_home_trust_bar_title',
							'label' => 'Title',
							'name' => 'title',
							'type' => 'text',
						),
						array(
							'key' => 'field_trimvia_home_trust_bar_subtitle',
							'label' => 'Subtitle',
							'name' => 'subtitle',
							'type' => 'text',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'page_type',
						'operator' => '==',
						'value' => 'front_page',
					),
				),
				array(
					array(
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/home.php',
					),
				),
			),
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Homepage trust strip with 5 items.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_home_trust_bar_fields');

/**
 * Assign admin menu icons to parent-registered CPTs.
 *
 * @param array  $args      Post type args.
 * @param string $post_type Post type key.
 * @return array
 */
function trimvia_set_custom_post_type_icons($args, $post_type)
{
	if ('slide' === $post_type) {
		$args['menu_icon'] = 'dashicons-images-alt2';
	}

	if ('faqs' === $post_type) {
		$args['menu_icon'] = 'dashicons-editor-help';
	}

	return $args;
}
add_filter('register_post_type_args', 'trimvia_set_custom_post_type_icons', 10, 2);

/**
 * ACF options: hero + bottom CTA for Service post type archive (archive-service.php).
 */
function trimvia_register_services_archive_acf_options()
{
	if (!function_exists('acf_add_options_sub_page')) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'parent_slug' => 'edit.php?post_type=service',
			'page_title'  => __('Services archive layout', 'theme-woopm-child'),
			'menu_title'  => __('Archive layout', 'theme-woopm-child'),
			'menu_slug'   => 'trimvia-services-archive-settings',
			'capability'  => 'edit_posts',
		)
	);
}
add_action('acf/init', 'trimvia_register_services_archive_acf_options');

/**
 * Turn on a front-end archive for the `service` CPT when parent/plugin registered it without one.
 *
 * @param array  $args      CPT args.
 * @param string $post_type Post type key.
 * @return array
 */
function trimvia_service_cpt_enable_archive($args, $post_type)
{
	if ('service' !== $post_type || !is_array($args)) {
		return $args;
	}

	if (!apply_filters('trimvia_enable_service_post_type_archive', true)) {
		return $args;
	}

	$args['has_archive'] = true;

	return $args;
}
add_filter('register_post_type_args', 'trimvia_service_cpt_enable_archive', 99, 2);

/**
 * Add page-specific body classes.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function trimvia_child_body_classes($classes)
{
	if (is_page('consultation')) {
		$classes[] = 'consultation-page';
	}
	if (is_page_template('page-templates/services-trimvia.php')) {
		$classes[] = 'trimvia-services-archive-page';
	}
	if (is_post_type_archive('service')) {
		$classes[] = 'trimvia-services-archive-archive';
	}
	return $classes;
}
add_filter('body_class', 'trimvia_child_body_classes');

/**
 * Parent theme registers load_condition_tax_script on wp_enqueue_scripts; it prints inline JS
 * immediately (jQuery not loaded yet) for a non-existent .conditions-slider — causes console errors
 * on condition archives. Layout uses the child grid instead.
 */
remove_action('wp_enqueue_scripts', 'load_condition_tax_script', 10);
