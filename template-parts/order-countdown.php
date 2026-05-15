<?php
/**
 * Order countdown strip.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$visible = function_exists('get_field') ? (bool) get_field('order_countdown_visibility', $front_page_id) : false;
if (!$visible) {
	return;
}

$background_type = function_exists('get_field') ? (string) get_field('countdown_background_type', $front_page_id) : 'solid';
$background = 'solid' === $background_type
	? (string) get_field('counter_background_color', $front_page_id)
	: (string) get_field('counter_background_color_style', $front_page_id);
?>
<section class="page-section trimvia-order-countdown">
	<div class="container">
		<div class="order-counter enable_featured_pro" style="background:<?php echo esc_attr($background); ?>">
			<p>
				<?php echo esc_html((string) get_field('heading_before_countdown', $front_page_id)); ?>
				<span id="countdown" <?php if (get_option('woopw_delivery_cutoff', $front_page_id)) : ?>data-cutoff-time="<?php echo esc_attr((string) get_option('woopw_delivery_cutoff', $front_page_id)); ?>"<?php endif; ?>>0h 0m 0s</span>
				<?php echo esc_html((string) get_field('heading_after_countdown', $front_page_id)); ?>
			</p>
			<span class="separator"></span>
			<p><?php echo esc_html((string) get_field('countdown_subheading', $front_page_id)); ?></p>
		</div>
	</div>
</section>
