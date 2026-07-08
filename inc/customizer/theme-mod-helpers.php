<?php
if (!defined('ABSPATH')) {
	exit;
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
 * Email address from Pharmacy Mentor Options (Customizer → Options → Email Address).
 *
 * @return string
 */
function trimvia_get_pharmacy_theme_email()
{
	return sanitize_email((string) get_theme_mod('email', ''));
}

/**
 * Shortcode: [pharmacy_email]
 *
 * @return string
 */
function trimvia_pharmacy_email_shortcode()
{
	return trimvia_get_pharmacy_theme_email();
}
add_shortcode('pharmacy_email', 'trimvia_pharmacy_email_shortcode');

/**
 * Resolve a contact email field value (plain email or shortcode).
 *
 * @param string $value Raw ACF value.
 * @return string Sanitized email address.
 */
function trimvia_resolve_contact_email($value)
{
	$value = trim((string) $value);
	if ('' === $value) {
		return '';
	}

	if (false !== strpos($value, '[')) {
		$value = trim(wp_strip_all_tags(do_shortcode($value)));
	}

	return sanitize_email($value);
}

/**
 * Allow shortcodes in the contact page email ACF field (not strict email input).
 *
 * @param array<string,mixed> $field ACF field settings.
 * @return array<string,mixed>
 */
function trimvia_contact_email_acf_load_field($field)
{
	if (is_array($field)) {
		$field['type'] = 'text';
		if (empty($field['instructions'])) {
			$field['instructions'] = __('Enter an email address, or use [pharmacy_email] to pull the email from Appearance → Customize → Pharmacy Mentor Options → Email Address.', 'theme-woopm-child');
		}
	}

	return $field;
}
add_filter('acf/load_field/key=field_trimvia_contact_email', 'trimvia_contact_email_acf_load_field');

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


