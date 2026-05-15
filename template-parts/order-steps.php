<?php
/**
 * Order steps section.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$visible = function_exists('get_field') ? (bool) get_field('order_steps_section_visibility', $front_page_id) : false;
if (!$visible) {
	return;
}

$order_step_title = (string) get_field('order_step_title', $front_page_id);
$order_step = get_field('order_steps', $front_page_id);
if (!is_array($order_step) || empty($order_step)) {
	return;
}
?>
<section class="page-section page-section--alt trimvia-order-steps">
	<div class="container">
		<?php if (!empty($order_step_title)) : ?>
			<h2 class="section-title text-center mb-5"><?php echo esc_html($order_step_title); ?></h2>
		<?php endif; ?>

		<div class="shop-trust">
			<?php for ($i = 1; $i <= count($order_step); $i++) : ?>
				<?php
				$row = isset($order_step['step_' . $i]) ? $order_step['step_' . $i] : array();
				$icon_url = isset($row['icon']['url']) ? $row['icon']['url'] : '';
				$content = isset($row['short_description']) ? $row['short_description'] : '';
				if ('' === trim(wp_strip_all_tags((string) $content))) {
					continue;
				}
				?>
				<div class="shop-trust-item">
					<div class="shop-trust-icon">
						<?php if ($icon_url) : ?>
							<img src="<?php echo esc_url($icon_url); ?>" alt="" loading="lazy">
						<?php else : ?>
							<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
						<?php endif; ?>
					</div>
					<div class="content"><?php echo wp_kses_post($content); ?></div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>
