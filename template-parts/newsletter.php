<?php
/**
 * Newsletter section - child theme design override.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');

$newsletter_heading = function_exists('get_field') ? (string) get_field('newsletter_heading', $front_page_id) : '';
$newsletter_description = function_exists('get_field') ? (string) get_field('newsletter_short_description', $front_page_id) : '';
$newsletter_form_shortcode = function_exists('get_field') ? (string) get_field('newsletter_form_shortcode', $front_page_id) : '';

if ('' === trim($newsletter_heading) && '' === trim($newsletter_description) && '' === trim($newsletter_form_shortcode)) {
	return;
}
?>
<section class="cta-sec trimvia-newsletter-section">
	<div class="orb orb-1" style="top:-30%;right:-20%;opacity:.3"></div>
	<div class="orb orb-2" style="bottom:-30%;left:-15%;opacity:.2"></div>
	<div class="hero-noise"></div>
	<div class="container">
		<div class="trimvia-newsletter-wrap">
			<?php if ('' !== trim($newsletter_heading)) : ?>
				<h2 class="stitle"><?php echo esc_html($newsletter_heading); ?></h2>
			<?php endif; ?>
			<?php if ('' !== trim($newsletter_description)) : ?>
				<div class="sdesc"><?php echo wp_kses_post(wpautop($newsletter_description)); ?></div>
			<?php endif; ?>
			<?php if ('' !== trim($newsletter_form_shortcode)) : ?>
				<div class="trimvia-newsletter-form">
					<?php echo do_shortcode($newsletter_form_shortcode); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
