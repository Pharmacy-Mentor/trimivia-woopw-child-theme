<?php
/**
 * Child theme options page for Trimvia header/footer settings.
 *
 * These fields save to the same theme_mod values used by header.php and footer.php,
 * but they avoid the WordPress Customizer live preview/autosave flow.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

function trimvia_theme_options_menu()
{
	add_menu_page(
		__('Theme Options', 'theme-woopm-child'),
		__('Theme Options', 'theme-woopm-child'),
		'edit_theme_options',
		'trimvia-theme-options',
		'trimvia_render_theme_options_page',
		'dashicons-admin-customizer',
		61
	);
}
add_action('admin_menu', 'trimvia_theme_options_menu');

/**
 * Whether the current admin screen is the Trimvia Theme Options page.
 *
 * @param string $hook_suffix Admin hook suffix.
 * @return bool
 */
function trimvia_theme_options_is_screen($hook_suffix = '')
{
	if ($hook_suffix !== '') {
		return 'toplevel_page_trimvia-theme-options' === $hook_suffix;
	}

	return is_admin() && isset($_GET['page']) && 'trimvia-theme-options' === sanitize_key(wp_unslash($_GET['page'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Enqueue media uploader for image fields on Theme Options page.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function trimvia_enqueue_theme_options_assets($hook_suffix)
{
	if (!trimvia_theme_options_is_screen($hook_suffix)) {
		return;
	}

	wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'trimvia_enqueue_theme_options_assets');

function trimvia_theme_options_fields()
{
	return array(
		'header' => array(
			'title'       => __('Header Options', 'theme-woopm-child'),
			'description' => __('Control the site logo, navigation, icon link, and the two main header buttons visitors see first.', 'theme-woopm-child'),
			'fields' => array(
				'trimvia_header_logo_type' => array(
					'label'       => __('Header Logo Type', 'theme-woopm-child'),
					'type'        => 'select',
					'default'     => 'text',
					'description' => __('Choose text logo or uploaded image logo for the main header.', 'theme-woopm-child'),
					'choices'     => array(
						'text'  => __('Text Logo', 'theme-woopm-child'),
						'image' => __('Image Logo', 'theme-woopm-child'),
					),
				),
				'trimvia_header_logo_text_primary' => array(
					'label'   => __('Text Logo Primary Part', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => 'Trim',
				),
				'trimvia_header_logo_text_secondary' => array(
					'label'   => __('Text Logo Secondary Part', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => 'via',
				),
				'trimvia_header_logo' => array(
					'label'       => __('Header Logo', 'theme-woopm-child'),
					'type'        => 'image',
					'default'     => '',
					'description' => __('Upload or choose the main logo used in the header when "Image Logo" is selected.', 'theme-woopm-child'),
				),
				'trimvia_header_sticky_logo' => array(
					'label'       => __('Sticky Header Logo', 'theme-woopm-child'),
					'type'        => 'image',
					'default'     => '',
					'description' => __('Optional logo shown after the header becomes sticky while scrolling.', 'theme-woopm-child'),
				),
				'trimvia_header_primary_menu' => array(
					'label'       => __('Primary Menu', 'theme-woopm-child'),
					'type'        => 'menu',
					'default'     => 0,
					'description' => __('Select the navigation menu displayed in the center of the header.', 'theme-woopm-child'),
				),
				'trimvia_header_icon_class' => array(
					'label'       => __('Header Icon', 'theme-woopm-child'),
					'type'        => 'select',
					'default'     => 'fa-solid fa-user',
					'description' => __('Choose the icon shown beside the quick-link action in the header.', 'theme-woopm-child'),
					'choices'     => trimvia_theme_options_icon_choices(),
				),
				'trimvia_header_icon_link' => array(
					'label'       => __('Header Icon Link', 'theme-woopm-child'),
					'type'        => 'link',
					'default'     => home_url('/'),
					'description' => __('Where the header icon should send visitors when clicked. Accepts a full URL or a relative path like /basket.', 'theme-woopm-child'),
				),
				'trimvia_header_primary_button_text' => array(
					'label'       => __('Primary Button Text', 'theme-woopm-child'),
					'type'        => 'text',
					'default'     => __('Start Consultation', 'theme-woopm-child'),
					'description' => __('Main CTA label shown as the blue button in the header.', 'theme-woopm-child'),
				),
				'trimvia_header_primary_button_link' => array(
					'label'       => __('Primary Button Link', 'theme-woopm-child'),
					'type'        => 'link',
					'default'     => home_url('/shop/'),
					'description' => __('Destination for the primary header button. Accepts a full URL or a relative path like /condition/weight-loss.', 'theme-woopm-child'),
				),
				'trimvia_header_secondary_button_text' => array(
					'label'       => __('Secondary Button Text', 'theme-woopm-child'),
					'type'        => 'text',
					'default'     => __('Login', 'theme-woopm-child'),
					'description' => __('Label for the secondary outline button shown to logged-out visitors.', 'theme-woopm-child'),
				),
				'trimvia_header_secondary_button_link' => array(
					'label'       => __('Secondary Button Link', 'theme-woopm-child'),
					'type'        => 'link',
					'default'     => home_url('/my-account/'),
					'description' => __('Destination for the secondary header button. Accepts a full URL or a relative path like /my-account/.', 'theme-woopm-child'),
				),
			),
		),
		'footer' => array(
			'title'       => __('Footer Options', 'theme-woopm-child'),
			'description' => __('Manage the footer logo, brand copy, contact details, social links, footer menus, and compliance notes.', 'theme-woopm-child'),
			'fields' => array(
				'trimvia_footer_logo_type' => array(
					'label'       => __('Footer Logo Type', 'theme-woopm-child'),
					'type'        => 'select',
					'default'     => 'text',
					'description' => __('Choose whether the footer uses a text logo or uploaded image logo.', 'theme-woopm-child'),
					'choices'     => array(
						'text'  => __('Text Logo', 'theme-woopm-child'),
						'image' => __('Image Logo', 'theme-woopm-child'),
					),
				),
				'trimvia_footer_logo_text_primary' => array(
					'label'   => __('Footer Text Logo Primary Part', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => 'Trim',
				),
				'trimvia_footer_logo_text_secondary' => array(
					'label'   => __('Footer Text Logo Secondary Part', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => 'via',
				),
				'trimvia_footer_logo_image' => array(
					'label'       => __('Footer Logo', 'theme-woopm-child'),
					'type'        => 'image',
					'default'     => '',
					'description' => __('Upload or choose the footer logo used when "Image Logo" is selected.', 'theme-woopm-child'),
				),
				'trimvia_footer_description' => array(
					'label'       => __('Footer Description', 'theme-woopm-child'),
					'type'        => 'richtext',
					'default'     => __('Transform your health with confidence. Expert care, proven treatments, and a journey tailored to you.', 'theme-woopm-child'),
					'description' => __('Use the editor to add links, line breaks, and basic formatting.', 'theme-woopm-child'),
				),
				'trimvia_footer_email' => array(
					'label'   => __('Footer Email', 'theme-woopm-child'),
					'type'    => 'email',
					'default' => 'info@trimvia.co.uk',
				),
				'trimvia_footer_social_links' => array(
					'label'       => __('Social Media Links', 'theme-woopm-child'),
					'type'        => 'social_repeater',
					'default'     => "fa-brands fa-facebook-f|https://facebook.com|Facebook\nfa-brands fa-instagram|https://instagram.com|Instagram",
					'description' => __('Add as many social links as you need. Choose an icon, add the URL, and optionally a label for accessibility.', 'theme-woopm-child'),
				),
				'trimvia_footer_quick_menu_title' => array(
					'label'   => __('Menu 1 Title', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => __('Quick Links', 'theme-woopm-child'),
				),
				'trimvia_footer_quick_menu' => array(
					'label'   => __('Menu 1: Quick Links', 'theme-woopm-child'),
					'type'    => 'menu',
					'default' => 0,
				),
				'trimvia_footer_legal_menu_title' => array(
					'label'   => __('Menu 2 Title', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => __('Legal', 'theme-woopm-child'),
				),
				'trimvia_footer_legal_menu' => array(
					'label'   => __('Menu 2: Legal', 'theme-woopm-child'),
					'type'    => 'menu',
					'default' => 0,
				),
				'trimvia_footer_support_menu_title' => array(
					'label'   => __('Menu 3 Title', 'theme-woopm-child'),
					'type'    => 'text',
					'default' => __('Support', 'theme-woopm-child'),
				),
				'trimvia_footer_support_menu' => array(
					'label'   => __('Menu 3: Support', 'theme-woopm-child'),
					'type'    => 'menu',
					'default' => 0,
				),
				'trimvia_footer_copyright' => array(
					'label'       => __('Copyright Text', 'theme-woopm-child'),
					'type'        => 'richtext',
					'default'     => __('&copy; 2026 Trimvia. All rights reserved.', 'theme-woopm-child'),
					'description' => __('Supports links and basic HTML formatting.', 'theme-woopm-child'),
				),
				'trimvia_footer_right_label_one' => array(
					'label'       => __('Right Label 1', 'theme-woopm-child'),
					'type'        => 'richtext',
					'default'     => __('GPhC Registered', 'theme-woopm-child'),
					'description' => __('Useful for registration details, compliance links, or short HTML content.', 'theme-woopm-child'),
				),
				'trimvia_footer_right_label_two' => array(
					'label'       => __('Right Label 2', 'theme-woopm-child'),
					'type'        => 'richtext',
					'default'     => __('ICO Registered', 'theme-woopm-child'),
					'description' => __('Useful for pharmacist, superintendent, or regulator links.', 'theme-woopm-child'),
				),
				'trimvia_footer_bottom_description' => array(
					'label'       => __('Bottom Footer Description', 'theme-woopm-child'),
					'type'        => 'richtext',
					'default'     => __('Trimvia is a private online weight management service that provides safe access to prescription weight loss treatments through UK-registered healthcare professionals. All prescriptions issued through Trimvia are dispensed by our partner, Mayberry Pharmacy, a fully regulated NHS-registered pharmacy. Always read the patient leaflet and speak to a healthcare professional before starting new treatments.', 'theme-woopm-child'),
					'description' => __('Best for the long compliance paragraph shown at the very bottom of the footer.', 'theme-woopm-child'),
				),
			),
		),
	);
}

function trimvia_theme_options_icon_choices()
{
	return array(
		''                          => __('None', 'theme-woopm-child'),
		'fa-solid fa-user'          => __('User', 'theme-woopm-child'),
		'fa-solid fa-cart-shopping' => __('Cart', 'theme-woopm-child'),
		'fa-solid fa-phone'         => __('Phone', 'theme-woopm-child'),
		'fa-solid fa-envelope'      => __('Email', 'theme-woopm-child'),
		'fa-solid fa-heart'         => __('Heart', 'theme-woopm-child'),
		'fa-solid fa-circle-info'   => __('Info', 'theme-woopm-child'),
		'fa-brands fa-whatsapp'     => __('WhatsApp', 'theme-woopm-child'),
		'fa-brands fa-instagram'    => __('Instagram', 'theme-woopm-child'),
		'fa-brands fa-facebook-f'   => __('Facebook', 'theme-woopm-child'),
	);
}

function trimvia_render_theme_options_page()
{
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('You do not have permission to edit theme options.', 'theme-woopm-child'));
	}

	if (isset($_POST['trimvia_theme_options_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['trimvia_theme_options_nonce'])), 'trimvia_save_theme_options')) {
		trimvia_save_theme_options();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Trimvia theme options saved.', 'theme-woopm-child') . '</p></div>';
	}

	$sections = trimvia_theme_options_fields();
	?>
	<div class="wrap trimvia-theme-options-wrap">
		<?php trimvia_render_theme_options_page_styles(); ?>
		<h1><?php esc_html_e('Theme Options', 'theme-woopm-child'); ?></h1>
		<div class="trimvia-theme-options-intro">
			<div>
				<h2><?php esc_html_e('Header and Footer Settings', 'theme-woopm-child'); ?></h2>
				<p><?php esc_html_e('Use this page to manage the most important brand settings without opening the Customizer.', 'theme-woopm-child'); ?></p>
			</div>
			<ul>
				<li><?php esc_html_e('Upload logos directly from the Media Library.', 'theme-woopm-child'); ?></li>
				<li><?php esc_html_e('Update menus, buttons, email, and footer copy in one place.', 'theme-woopm-child'); ?></li>
				<li><?php esc_html_e('Changes save to the same values already used by the live site.', 'theme-woopm-child'); ?></li>
			</ul>
		</div>
		<form method="post" action="">
			<?php wp_nonce_field('trimvia_save_theme_options', 'trimvia_theme_options_nonce'); ?>
			<div class="trimvia-theme-options-grid">
				<?php foreach ($sections as $section) : ?>
					<section class="trimvia-theme-options-card">
						<div class="trimvia-theme-options-card__head">
							<h2><?php echo esc_html($section['title']); ?></h2>
							<?php if (!empty($section['description'])) : ?>
								<p><?php echo esc_html($section['description']); ?></p>
							<?php endif; ?>
						</div>
						<table class="form-table" role="presentation">
							<tbody>
								<?php foreach ($section['fields'] as $key => $field) : ?>
									<?php trimvia_render_theme_options_field($key, $field); ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</section>
				<?php endforeach; ?>
			</div>
			<?php submit_button(__('Save Theme Options', 'theme-woopm-child')); ?>
		</form>
	</div>
	<?php trimvia_render_theme_options_page_scripts(); ?>
	<?php
}

function trimvia_save_theme_options()
{
	$sections = trimvia_theme_options_fields();

	foreach ($sections as $section) {
		foreach ($section['fields'] as $key => $field) {
			$raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
			set_theme_mod($key, trimvia_sanitize_theme_option_value($raw, $field));
		}
	}
}

function trimvia_sanitize_theme_option_value($value, $field)
{
	$type = $field['type'] ?? 'text';

	if ('select' === $type) {
		$choices = isset($field['choices']) && is_array($field['choices']) ? $field['choices'] : array();
		$value   = sanitize_text_field((string) $value);
		return array_key_exists($value, $choices) ? $value : (string) ($field['default'] ?? '');
	}

	if ('menu' === $type) {
		$menu_id = absint($value);
		return $menu_id && wp_get_nav_menu_object($menu_id) ? $menu_id : 0;
	}

	if ('url' === $type) {
		return esc_url_raw((string) $value);
	}

	if ('link' === $type) {
		return trimvia_sanitize_theme_option_link_value($value);
	}

	if ('image' === $type) {
		return function_exists('trimvia_sanitize_logo_image_value')
			? trimvia_sanitize_logo_image_value($value)
			: esc_url_raw((string) $value);
	}

	if ('social_repeater' === $type) {
		return function_exists('trimvia_sanitize_social_links_text')
			? trimvia_sanitize_social_links_text($value)
			: sanitize_textarea_field((string) $value);
	}

	if ('richtext' === $type) {
		return wp_kses_post((string) $value);
	}

	if ('email' === $type) {
		return sanitize_email((string) $value);
	}

	if ('textarea' === $type) {
		return wp_kses_post((string) $value);
	}

	return sanitize_text_field((string) $value);
}

function trimvia_render_theme_options_field($key, $field)
{
	$type        = $field['type'] ?? 'text';
	$value       = get_theme_mod($key, $field['default'] ?? '');
	$description = $field['description'] ?? '';
	?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
		</th>
		<td>
			<?php if ('textarea' === $type) : ?>
				<textarea class="large-text" rows="4" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"><?php echo esc_textarea($value); ?></textarea>
			<?php elseif ('richtext' === $type) : ?>
				<?php trimvia_render_theme_options_richtext_field($key, $value, $field); ?>
			<?php elseif ('social_repeater' === $type) : ?>
				<?php trimvia_render_theme_options_social_repeater_field($key, $value, $field); ?>
			<?php elseif ('image' === $type) : ?>
				<?php trimvia_render_theme_options_image_field($key, $value, $field); ?>
			<?php elseif ('select' === $type) : ?>
				<select id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>">
					<?php foreach ($field['choices'] as $choice_value => $choice_label) : ?>
						<option value="<?php echo esc_attr($choice_value); ?>" <?php selected($value, $choice_value); ?>><?php echo esc_html($choice_label); ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ('menu' === $type) : ?>
				<?php trimvia_render_theme_options_menu_select($key, absint($value)); ?>
			<?php elseif ('link' === $type) : ?>
				<input
					class="regular-text"
					type="text"
					id="<?php echo esc_attr($key); ?>"
					name="<?php echo esc_attr($key); ?>"
					value="<?php echo esc_attr($value); ?>"
					placeholder="<?php esc_attr_e('/basket or https://example.com/page', 'theme-woopm-child'); ?>"
					spellcheck="false"
				>
			<?php else : ?>
				<input class="regular-text" type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
			<?php endif; ?>
			<?php if ($description) : ?>
				<p class="description"><?php echo esc_html($description); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Resolve image preview URL from stored theme option value.
 *
 * @param mixed $value Stored theme option value.
 * @return string
 */
function trimvia_get_theme_options_image_preview_url($value)
{
	if (is_numeric($value)) {
		return (string) wp_get_attachment_image_url(absint($value), 'medium');
	}

	return esc_url((string) $value);
}

/**
 * Render media upload field with preview.
 *
 * @param string $key   Field key.
 * @param mixed  $value Field value.
 * @param array  $field Field schema.
 * @return void
 */
function trimvia_render_theme_options_image_field($key, $value, $field)
{
	$preview_url  = trimvia_get_theme_options_image_preview_url($value);
	$button_label = !empty($value) ? __('Replace image', 'theme-woopm-child') : __('Upload image', 'theme-woopm-child');
	?>
	<div class="trimvia-theme-image-field">
		<div class="trimvia-theme-image-preview<?php echo $preview_url ? '' : ' is-empty'; ?>">
			<?php if ($preview_url) : ?>
				<img src="<?php echo esc_url($preview_url); ?>" alt="" />
			<?php else : ?>
				<span><?php esc_html_e('No image selected', 'theme-woopm-child'); ?></span>
			<?php endif; ?>
		</div>
		<div class="trimvia-theme-image-actions">
			<input
				class="regular-text trimvia-theme-image-input"
				type="text"
				id="<?php echo esc_attr($key); ?>"
				name="<?php echo esc_attr($key); ?>"
				value="<?php echo esc_attr((string) $value); ?>"
				placeholder="<?php esc_attr_e('Media Library image URL', 'theme-woopm-child'); ?>"
			/>
			<div class="trimvia-theme-image-buttons">
				<button
					type="button"
					class="button button-secondary trimvia-theme-upload-button"
					data-target="<?php echo esc_attr($key); ?>"
					data-title="<?php echo esc_attr($field['label']); ?>"
				><?php echo esc_html($button_label); ?></button>
				<button
					type="button"
					class="button-link-delete trimvia-theme-remove-button<?php echo empty($value) ? ' is-hidden' : ''; ?>"
					data-target="<?php echo esc_attr($key); ?>"
				><?php esc_html_e('Remove', 'theme-woopm-child'); ?></button>
			</div>
		</div>
	</div>
	<?php
}

function trimvia_render_theme_options_menu_select($key, $value)
{
	$menus = wp_get_nav_menus();
	?>
	<select id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>">
		<option value="0"><?php esc_html_e('Select a menu', 'theme-woopm-child'); ?></option>
		<?php if (!empty($menus) && !is_wp_error($menus)) : ?>
			<?php foreach ($menus as $menu) : ?>
				<option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($value, $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
	<?php
}

/**
 * Sanitize link fields that may contain either full URLs or site-relative paths.
 *
 * @param mixed $value Raw link value.
 * @return string
 */
function trimvia_sanitize_theme_option_link_value($value)
{
	$value = sanitize_text_field((string) $value);

	if ($value === '') {
		return '';
	}

	if (
		0 === strpos($value, '/') ||
		0 === strpos($value, '#') ||
		0 === strpos($value, '?')
	) {
		return $value;
	}

	return esc_url_raw($value);
}

/**
 * Render WordPress rich text editor field.
 *
 * @param string $key   Field key.
 * @param mixed  $value Field value.
 * @param array  $field Field schema.
 * @return void
 */
function trimvia_render_theme_options_richtext_field($key, $value, $field)
{
	$rows = isset($field['rows']) ? max(4, (int) $field['rows']) : 6;

	wp_editor(
		(string) $value,
		$key,
		array(
			'textarea_name' => $key,
			'textarea_rows' => $rows,
			'media_buttons' => false,
			'teeny'         => false,
			'quicktags'     => true,
			'tinymce'       => array(
				'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo',
				'toolbar2' => '',
			),
			'editor_class'  => 'trimvia-theme-richtext',
		)
	);
}

/**
 * Render social media repeater field.
 *
 * @param string $key   Field key.
 * @param mixed  $value Field value.
 * @param array  $field Field schema.
 * @return void
 */
function trimvia_render_theme_options_social_repeater_field($key, $value, $field)
{
	$items = function_exists('trimvia_parse_social_links')
		? trimvia_parse_social_links((string) $value)
		: array();
	$icon_choices = function_exists('trimvia_get_social_icon_choices')
		? trimvia_get_social_icon_choices()
		: array();

	if (empty($items)) {
		$items = array(
			array(
				'icon'  => '',
				'url'   => '',
				'label' => '',
			),
		);
	}
	?>
	<div class="trimvia-social-repeater" data-field-key="<?php echo esc_attr($key); ?>">
		<textarea
			class="trimvia-social-repeater__storage"
			id="<?php echo esc_attr($key); ?>"
			name="<?php echo esc_attr($key); ?>"
			hidden
		><?php echo esc_textarea((string) $value); ?></textarea>
		<div class="trimvia-social-repeater__rows">
			<?php foreach ($items as $index => $item) : ?>
				<?php trimvia_render_theme_options_social_repeater_row($icon_choices, $item, $index); ?>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" class="button button-secondary trimvia-social-repeater__add">
				<?php esc_html_e('Add social link', 'theme-woopm-child'); ?>
			</button>
		</p>
		<script type="text/template" class="trimvia-social-repeater__template">
			<?php
			ob_start();
			trimvia_render_theme_options_social_repeater_row(
				$icon_choices,
				array(
					'icon'  => '',
					'url'   => '',
					'label' => '',
				),
				'{{index}}'
			);
			echo str_replace(array("\r", "\n"), '', (string) ob_get_clean());
			?>
		</script>
	</div>
	<?php
}

/**
 * Render one social repeater row.
 *
 * @param array<string,string>   $icon_choices Available icons.
 * @param array<string,string>   $item         Row item values.
 * @param int|string             $index        Row index placeholder.
 * @return void
 */
function trimvia_render_theme_options_social_repeater_row(array $icon_choices, array $item, $index)
{
	$icon  = isset($item['icon']) ? (string) $item['icon'] : '';
	$url   = isset($item['url']) ? (string) $item['url'] : '';
	$label = isset($item['label']) ? (string) $item['label'] : '';
	?>
	<div class="trimvia-social-row" data-row-index="<?php echo esc_attr((string) $index); ?>">
		<div class="trimvia-social-row__grid">
			<div class="trimvia-social-row__field">
				<label><?php esc_html_e('Icon', 'theme-woopm-child'); ?></label>
				<select class="trimvia-social-row__icon">
					<option value=""><?php esc_html_e('Select an icon', 'theme-woopm-child'); ?></option>
					<?php foreach ($icon_choices as $choice_value => $choice_label) : ?>
						<option value="<?php echo esc_attr($choice_value); ?>" <?php selected($icon, $choice_value); ?>><?php echo esc_html($choice_label); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="trimvia-social-row__field">
				<label><?php esc_html_e('Link URL', 'theme-woopm-child'); ?></label>
				<input class="regular-text trimvia-social-row__url" type="url" value="<?php echo esc_attr($url); ?>" placeholder="https://example.com">
			</div>
			<div class="trimvia-social-row__field">
				<label><?php esc_html_e('Label', 'theme-woopm-child'); ?></label>
				<input class="regular-text trimvia-social-row__label" type="text" value="<?php echo esc_attr($label); ?>" placeholder="<?php esc_attr_e('Instagram', 'theme-woopm-child'); ?>">
			</div>
		</div>
		<div class="trimvia-social-row__actions">
			<span class="trimvia-social-row__hint"><?php esc_html_e('Shown in the footer social icons list.', 'theme-woopm-child'); ?></span>
			<button type="button" class="button-link-delete trimvia-social-row__remove"><?php esc_html_e('Remove', 'theme-woopm-child'); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Render page-specific styles for Theme Options.
 *
 * @return void
 */
function trimvia_render_theme_options_page_styles()
{
	?>
	<style>
		.trimvia-theme-options-wrap { max-width: 1220px; }
		.trimvia-theme-options-intro {
			display: grid;
			grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr);
			gap: 24px;
			padding: 24px 28px;
			margin: 18px 0 28px;
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 16px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}
		.trimvia-theme-options-intro h2 { margin: 0 0 8px; font-size: 24px; }
		.trimvia-theme-options-intro p { margin: 0; color: #50575e; }
		.trimvia-theme-options-intro ul { margin: 0; padding-left: 18px; color: #1d2327; }
		.trimvia-theme-options-intro li + li { margin-top: 8px; }
		.trimvia-theme-options-grid { display: grid; gap: 24px; }
		.trimvia-theme-options-card {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 16px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			overflow: hidden;
		}
		.trimvia-theme-options-card__head {
			padding: 24px 28px 12px;
			border-bottom: 1px solid #f0f0f1;
		}
		.trimvia-theme-options-card__head h2 { margin: 0 0 6px; font-size: 28px; }
		.trimvia-theme-options-card__head p { margin: 0; color: #50575e; max-width: 760px; }
		.trimvia-theme-options-card .form-table { margin: 0; }
		.trimvia-theme-options-card .form-table th,
		.trimvia-theme-options-card .form-table td { padding: 18px 28px; }
		.trimvia-theme-options-card .form-table th { width: 280px; }
		.trimvia-theme-options-card .form-table tr:not(:last-child) th,
		.trimvia-theme-options-card .form-table tr:not(:last-child) td { border-bottom: 1px solid #f0f0f1; }
		.trimvia-theme-options-card input.regular-text,
		.trimvia-theme-options-card select,
		.trimvia-theme-options-card textarea { width: min(100%, 520px); }
		.trimvia-theme-options-card .wp-editor-wrap { width: min(100%, 760px); }
		.trimvia-theme-options-card .wp-editor-container textarea { width: 100% !important; }
		.trimvia-theme-image-field { display: grid; gap: 14px; align-items: start; }
		.trimvia-theme-image-preview {
			width: 180px;
			min-height: 120px;
			padding: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #f6f7f7;
			border: 1px dashed #c3c4c7;
			border-radius: 12px;
		}
		.trimvia-theme-image-preview.is-empty { color: #646970; font-size: 13px; }
		.trimvia-theme-image-preview img { display: block; max-width: 100%; max-height: 96px; object-fit: contain; }
		.trimvia-theme-image-actions { display: grid; gap: 10px; }
		.trimvia-theme-image-buttons { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
		.trimvia-theme-remove-button.is-hidden { display: none; }
		.trimvia-social-repeater { width: min(100%, 900px); }
		.trimvia-social-repeater__rows { display: grid; gap: 14px; }
		.trimvia-social-row {
			padding: 16px;
			background: #f8f9fb;
			border: 1px solid #dcdcde;
			border-radius: 12px;
		}
		.trimvia-social-row__grid {
			display: grid;
			grid-template-columns: minmax(180px, 220px) minmax(240px, 1fr) minmax(180px, 240px);
			gap: 14px;
			align-items: end;
		}
		.trimvia-social-row__field { display: grid; gap: 6px; }
		.trimvia-social-row__field label {
			font-weight: 600;
			color: #1d2327;
		}
		.trimvia-social-row__field select,
		.trimvia-social-row__field input { width: 100% !important; max-width: none; }
		.trimvia-social-row__actions {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 12px;
			margin-top: 12px;
		}
		.trimvia-social-row__hint { color: #646970; font-size: 12px; }
		@media (max-width: 960px) {
			.trimvia-theme-options-intro { grid-template-columns: 1fr; }
			.trimvia-theme-options-card .form-table th,
			.trimvia-theme-options-card .form-table td { display: block; width: auto; padding: 14px 18px; }
			.trimvia-theme-options-card .form-table th { padding-bottom: 0; }
			.trimvia-social-row__grid { grid-template-columns: 1fr; }
			.trimvia-social-row__actions { flex-direction: column; align-items: flex-start; }
		}
	</style>
	<?php
}

/**
 * Render page-specific scripts for Theme Options.
 *
 * @return void
 */
function trimvia_render_theme_options_page_scripts()
{
	?>
	<script>
		(function($) {
			var getFieldWrap = function(targetId) {
				var $input = $("#" + targetId);
				return $input.closest(".trimvia-theme-image-field");
			};

			var setPreview = function($wrap, imageUrl) {
				var $preview = $wrap.find(".trimvia-theme-image-preview");
				if (!imageUrl) {
					$preview.addClass("is-empty").html("<span><?php echo esc_js(__('No image selected', 'theme-woopm-child')); ?></span>");
					return;
				}

				$preview.removeClass("is-empty").html('<img src="' + imageUrl + '" alt="">');
			};

			var openImageFrame = function($button) {
				if (typeof wp === "undefined" || !wp.media) {
					if (window.console && typeof window.console.warn === "function") {
						window.console.warn("WordPress media library is not available on this page.");
					}
					return;
				}

				var targetId = $button.data("target");
				var frame = wp.media({
					title: $button.data("title") || "<?php echo esc_js(__('Select image', 'theme-woopm-child')); ?>",
					button: {
						text: "<?php echo esc_js(__('Use this image', 'theme-woopm-child')); ?>"
					},
					library: {
						type: "image"
					},
					multiple: false
				});

				frame.on("select", function() {
					var attachment = frame.state().get("selection").first().toJSON();
					var $wrap = getFieldWrap(targetId);
					var previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

					$("#" + targetId).val(attachment.url).trigger("change");
					setPreview($wrap, previewUrl);
					$wrap.find(".trimvia-theme-remove-button").removeClass("is-hidden");
					$button.text("<?php echo esc_js(__('Replace image', 'theme-woopm-child')); ?>");
				});

				frame.open();
			};

			$(document).on("click", ".trimvia-theme-upload-button", function(event) {
				event.preventDefault();
				openImageFrame($(this));
			});

			$(document).on("click", ".trimvia-theme-remove-button", function(event) {
				event.preventDefault();
				var targetId = $(this).data("target");
				var $wrap = getFieldWrap(targetId);
				$("#" + targetId).val("").trigger("change");
				setPreview($wrap, "");
				$wrap.find(".trimvia-theme-upload-button").text("<?php echo esc_js(__('Upload image', 'theme-woopm-child')); ?>");
				$(this).addClass("is-hidden");
			});

			$(document).on("input change", ".trimvia-theme-image-input", function() {
				var $input = $(this);
				var $wrap = $input.closest(".trimvia-theme-image-field");
				var value = $.trim($input.val());
				setPreview($wrap, value);
				$wrap.find(".trimvia-theme-remove-button").toggleClass("is-hidden", value === "");
			});

			var syncSocialRepeater = function($repeater) {
				var rows = [];

				$repeater.find(".trimvia-social-row").each(function() {
					var $row = $(this);
					var icon = $.trim($row.find(".trimvia-social-row__icon").val() || "");
					var url = $.trim($row.find(".trimvia-social-row__url").val() || "");
					var label = $.trim($row.find(".trimvia-social-row__label").val() || "");

					if (!icon || !url) {
						return;
					}

					rows.push(icon + "|" + url + "|" + label);
				});

				$repeater.find(".trimvia-social-repeater__storage").val(rows.join("\n"));
			};

			var bindSocialRepeater = function($repeater) {
				if (!$repeater.length || $repeater.data("trimviaSocialReady") === 1) {
					return;
				}

				$repeater.data("trimviaSocialReady", 1);
				syncSocialRepeater($repeater);
			};

			$(".trimvia-social-repeater").each(function() {
				bindSocialRepeater($(this));
			});

			$(document).on("click", ".trimvia-social-repeater__add", function(event) {
				event.preventDefault();
				var $repeater = $(this).closest(".trimvia-social-repeater");
				var template = $.trim($repeater.find(".trimvia-social-repeater__template").html() || "");
				var nextIndex = $repeater.find(".trimvia-social-row").length;

				if (!template) {
					return;
				}

				$repeater.find(".trimvia-social-repeater__rows").append(template.replace(/{{index}}/g, String(nextIndex)));
				syncSocialRepeater($repeater);
			});

			$(document).on("click", ".trimvia-social-row__remove", function(event) {
				event.preventDefault();
				var $repeater = $(this).closest(".trimvia-social-repeater");
				$(this).closest(".trimvia-social-row").remove();

				if (!$repeater.find(".trimvia-social-row").length) {
					$repeater.find(".trimvia-social-repeater__add").trigger("click");
				}

				syncSocialRepeater($repeater);
			});

			$(document).on("input change", ".trimvia-social-repeater select, .trimvia-social-repeater input", function() {
				syncSocialRepeater($(this).closest(".trimvia-social-repeater"));
			});
		})(jQuery);
	</script>
	<?php
}
