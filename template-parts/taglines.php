<?php
/**
 * Homepage taglines strip.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$section_taglines = function_exists('get_field') ? get_field('section_taglines', $front_page_id) : array();
if (!is_array($section_taglines) || empty($section_taglines)) {
	return;
}
?>
<section class="page-section page-section--alt trimvia-taglines">
	<div class="container">
		<div class="shop-trust">
			<?php for ($i = 1; $i <= count($section_taglines); $i++) : ?>
				<?php
				$row = isset($section_taglines['tagline_' . $i]) ? $section_taglines['tagline_' . $i] : array();
				$icon_url = isset($row['icon_image']['url']) ? $row['icon_image']['url'] : '';
				$tagline = isset($row['short_info']) ? $row['short_info'] : '';
				if ('' === trim(wp_strip_all_tags((string) $tagline))) {
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
					<div class="short-info"><?php echo wp_kses_post($tagline); ?></div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>
