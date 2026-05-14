<?php
if (!defined('ABSPATH')) {
	exit;
}

$site_name             = get_bloginfo('name');
$logo_type             = get_theme_mod('trimvia_header_logo_type', 'text');
$is_image_logo         = ('image' === $logo_type);
$text_logo_primary     = get_theme_mod('trimvia_header_logo_text_primary', 'Trim');
$text_logo_secondary   = get_theme_mod('trimvia_header_logo_text_secondary', 'via');
$text_logo_badge       = strtoupper(substr(trim((string) $text_logo_primary), 0, 1));
$text_logo_badge       = $text_logo_badge ?: 'T';
$header_logo_value     = get_theme_mod('trimvia_header_logo', '');
$sticky_logo_value     = get_theme_mod('trimvia_header_sticky_logo', '');
$header_logo_url       = '';
$sticky_header_logo_url = '';

if (!empty($header_logo_value)) {
	$header_logo_url = is_numeric($header_logo_value)
		? wp_get_attachment_image_url(absint($header_logo_value), 'full')
		: $header_logo_value;
}

if (!empty($sticky_logo_value)) {
	$sticky_header_logo_url = is_numeric($sticky_logo_value)
		? wp_get_attachment_image_url(absint($sticky_logo_value), 'full')
		: $sticky_logo_value;
}

$logo_default_url      = $header_logo_url ?: $sticky_header_logo_url;
$use_image_logo        = $is_image_logo && !empty($logo_default_url);

$header_menu_id        = absint(get_theme_mod('trimvia_header_primary_menu', 0));
$header_icon_class     = trimvia_sanitize_icon_class(get_theme_mod('trimvia_header_icon_class', 'fa-solid fa-user'));
$default_icon_link     = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/');
$header_icon_link      = get_theme_mod('trimvia_header_icon_link', $default_icon_link);

$default_secondary_link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
$secondary_text         = get_theme_mod('trimvia_header_secondary_button_text', __('Login', 'theme-woopm-child'));
$secondary_link         = get_theme_mod('trimvia_header_secondary_button_link', $default_secondary_link);

$primary_text = get_theme_mod('trimvia_header_primary_button_text', __('Start Consultation', 'theme-woopm-child'));
$primary_link = get_theme_mod('trimvia_header_primary_button_link', home_url('/consultation/'));

$header_classes = 'header';
if (!empty($sticky_header_logo_url)) {
	$header_classes .= ' has-sticky-logo';
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="<?php echo esc_attr($header_classes); ?>" id="header">
	<div class="header-inner">
		<a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
			<?php if ($use_image_logo) : ?>
				<span class="logo-images">
					<img src="<?php echo esc_url($logo_default_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="logo-image logo-image-default">
					<?php if (!empty($sticky_header_logo_url)) : ?>
						<img src="<?php echo esc_url($sticky_header_logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="logo-image logo-image-sticky">
					<?php endif; ?>
				</span>
			<?php else : ?>
				<div class="logo-badge"><?php echo esc_html($text_logo_badge); ?></div>
				<div class="logo-text"><strong><?php echo esc_html($text_logo_primary); ?></strong><span><?php echo esc_html($text_logo_secondary); ?></span></div>
			<?php endif; ?>
		</a>
		<nav class="nav">
			<?php
			if ($header_menu_id && wp_get_nav_menu_object($header_menu_id)) {
				$menu_args = array(
					'menu'         => $header_menu_id,
					'container'    => false,
					'menu_class'   => 'menu',
					'depth'        => 0,
					'fallback_cb'  => false,
				);
				if (class_exists('Trimvia_Nav_Walker')) {
					$menu_args['walker'] = new Trimvia_Nav_Walker();
				}
				wp_nav_menu($menu_args);
			} else {
				?>
				<a href="<?php echo esc_url(home_url('/treatments/')); ?>">Treatments</a>
				<a href="<?php echo esc_url(home_url('/#why')); ?>">Why Trimvia</a>
				<a href="<?php echo esc_url(home_url('/#team')); ?>">Our Team</a>
				<a href="<?php echo esc_url(home_url('/#faq')); ?>">FAQs</a>
				<?php
			}
			?>
		</nav>
		<div class="header-actions">
			<?php if (!empty($header_icon_class) && !empty($header_icon_link)) : ?>
				<a href="<?php echo esc_url($header_icon_link); ?>" class="btn-basket custom-header-icon" aria-label="Quick action">
					<i class="<?php echo esc_attr($header_icon_class); ?>" aria-hidden="true"></i>
				</a>
			<?php endif; ?>
			<?php if (!empty($secondary_text) && !empty($secondary_link)) : ?>
				<a href="<?php echo esc_url($secondary_link); ?>" class="btn-ghost"><?php echo esc_html($secondary_text); ?></a>
			<?php endif; ?>
			<?php if (!empty($primary_text) && !empty($primary_link)) : ?>
				<a href="<?php echo esc_url($primary_link); ?>" class="btn-accent"><?php echo esc_html($primary_text); ?></a>
			<?php endif; ?>
		</div>
		<button class="mobile-menu" aria-label="Menu">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
		</button>
	</div>
</header>
