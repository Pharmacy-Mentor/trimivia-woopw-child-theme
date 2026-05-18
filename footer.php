<?php
if (!defined('ABSPATH')) {
	exit;
}

$site_name                = get_bloginfo('name');
$footer_logo_type         = get_theme_mod('trimvia_footer_logo_type', 'text');
$is_footer_image_logo     = ('image' === $footer_logo_type);
$footer_logo_text_primary = get_theme_mod('trimvia_footer_logo_text_primary', 'Trim');
$footer_logo_text_secondary = get_theme_mod('trimvia_footer_logo_text_secondary', 'via');
$footer_logo_value        = get_theme_mod('trimvia_footer_logo_image', '');
$footer_logo_image_url    = '';

if (!empty($footer_logo_value)) {
	$footer_logo_image_url = is_numeric($footer_logo_value)
		? wp_get_attachment_image_url(absint($footer_logo_value), 'full')
		: $footer_logo_value;
}

$use_footer_image_logo = $is_footer_image_logo && !empty($footer_logo_image_url);
$footer_description    = get_theme_mod('trimvia_footer_description', __('Transform your health with confidence. Expert care, proven treatments, and a journey tailored to you.', 'theme-woopm-child'));
$footer_email          = sanitize_email(get_theme_mod('trimvia_footer_email', 'info@trimvia.co.uk'));
$footer_social_items   = trimvia_parse_social_links(get_theme_mod('trimvia_footer_social_links', ''));

$quick_title = get_theme_mod('trimvia_footer_quick_menu_title', __('Quick Links', 'theme-woopm-child'));
$legal_title = get_theme_mod('trimvia_footer_legal_menu_title', __('Legal', 'theme-woopm-child'));
$support_title = get_theme_mod('trimvia_footer_support_menu_title', __('Support', 'theme-woopm-child'));

$quick_menu_id   = absint(get_theme_mod('trimvia_footer_quick_menu', 0));
$legal_menu_id   = absint(get_theme_mod('trimvia_footer_legal_menu', 0));
$support_menu_id = absint(get_theme_mod('trimvia_footer_support_menu', 0));

$copyright_text   = get_theme_mod('trimvia_footer_copyright', __('© 2026 Trimvia. All rights reserved.', 'theme-woopm-child'));
$right_label_one  = get_theme_mod('trimvia_footer_right_label_one', __('GPhC Registered', 'theme-woopm-child'));
$right_label_two  = get_theme_mod('trimvia_footer_right_label_two', __('ICO Registered', 'theme-woopm-child'));
$bottom_note_text = get_theme_mod('trimvia_footer_bottom_description', __('Trimvia is a private online weight management service that provides safe access to prescription weight loss treatments through UK-registered healthcare professionals. All prescriptions issued through Trimvia are dispensed by our partner, Mayberry Pharmacy, a fully regulated NHS-registered pharmacy. Always read the patient leaflet and speak to a healthcare professional before starting new treatments.', 'theme-woopm-child'));
?>
<footer class="footer">
	<div class="footer-grid">
		<div class="footer-brand">
			<?php if ($use_footer_image_logo) : ?>
				<img src="<?php echo esc_url($footer_logo_image_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="footer-logo-image">
			<?php else : ?>
				<div class="logo-text footer-logo-text"><strong><?php echo esc_html($footer_logo_text_primary); ?></strong><span><?php echo esc_html($footer_logo_text_secondary); ?></span></div>
			<?php endif; ?>
			<?php if (!empty($footer_description)) : ?>
				<p><?php echo wp_kses_post(nl2br($footer_description)); ?></p>
			<?php endif; ?>
			<?php if (!empty($footer_email)) : ?>
				<a href="mailto:<?php echo esc_attr($footer_email); ?>" class="footer-email">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
					<?php echo esc_html($footer_email); ?>
				</a>
			<?php endif; ?>
			<?php if (!empty($footer_social_items)) : ?>
				<div class="f-socials">
					<?php foreach ($footer_social_items as $social_item) : ?>
						<a href="<?php echo esc_url($social_item['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social_item['label'] ?: 'Social link'); ?>">
							<i class="<?php echo esc_attr($social_item['icon']); ?>" aria-hidden="true"></i>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="f-col">
			<h4><?php echo esc_html($quick_title); ?></h4>
			<?php
			if ($quick_menu_id && wp_get_nav_menu_object($quick_menu_id)) {
				wp_nav_menu(
					array(
						'menu'       => $quick_menu_id,
						'container'  => false,
						'menu_class' => 'f-menu',
						'fallback_cb' => false,
						'depth'      => 1,
					)
				);
			} else {
				?>
				<a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
				<a href="<?php echo esc_url(home_url('/treatments/')); ?>">All Treatments</a>
				<a href="<?php echo esc_url(home_url('/shop/')); ?>">Weight Loss Hub</a>
				<a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a>
				<?php
			}
			?>
		</div>
		<div class="f-col">
			<h4><?php echo esc_html($legal_title); ?></h4>
			<?php
			if ($legal_menu_id && wp_get_nav_menu_object($legal_menu_id)) {
				wp_nav_menu(
					array(
						'menu'       => $legal_menu_id,
						'container'  => false,
						'menu_class' => 'f-menu',
						'fallback_cb' => false,
						'depth'      => 1,
					)
				);
			} else {
				?>
				<a href="#">Privacy Policy</a>
				<a href="#">Cookie Policy</a>
				<a href="#">Terms &amp; Conditions</a>
				<a href="#">Medical Disclaimer</a>
				<a href="#">GPhC / Pharmacy Info</a>
				<?php
			}
			?>
		</div>
		<div class="f-col">
			<h4><?php echo esc_html($support_title); ?></h4>
			<?php
			if ($support_menu_id && wp_get_nav_menu_object($support_menu_id)) {
				wp_nav_menu(
					array(
						'menu'       => $support_menu_id,
						'container'  => false,
						'menu_class' => 'f-menu',
						'fallback_cb' => false,
						'depth'      => 1,
					)
				);
			} else {
				?>
				<a href="#">Delivery &amp; Returns</a>
				<a href="<?php echo esc_url(home_url('/#faq')); ?>">FAQ's</a>
				<a href="#">Mayberry Pharmacy</a>
				<a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url()); ?>">Patient Login</a>
				<?php
			}
			?>
		</div>
	</div>
	<div class="footer-bottom">
		<span><?php echo wp_kses_post(nl2br(html_entity_decode((string) $copyright_text, ENT_QUOTES, 'UTF-8'))); ?></span>
		<div class="f-badges">
			<?php if (!empty($right_label_one)) : ?>
				<span class="f-badge"><?php echo wp_kses_post(nl2br(html_entity_decode((string) $right_label_one, ENT_QUOTES, 'UTF-8'))); ?></span>
			<?php endif; ?>
			<?php if (!empty($right_label_two)) : ?>
				<span class="f-badge"><?php echo wp_kses_post(nl2br(html_entity_decode((string) $right_label_two, ENT_QUOTES, 'UTF-8'))); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php if (!empty($bottom_note_text)) : ?>
		<div class="f-legal"><p><?php echo wp_kses_post(nl2br(html_entity_decode((string) $bottom_note_text, ENT_QUOTES, 'UTF-8'))); ?></p></div>
	<?php endif; ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
