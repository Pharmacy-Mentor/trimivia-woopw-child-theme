<?php
/**
 * Condition content section — Trimvia about blocks, parent ACF data source.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
if (!$term instanceof WP_Term) {
	return;
}

$condition_group = function_exists('get_field') ? get_field('condition_group_content', $term) : array();
if (!is_array($condition_group) || empty($condition_group)) {
	return;
}
?>
<section class="how-sec section-pad trimvia-condition-about" id="about-condition">
	<div class="container">
		<div class="trimvia-condition-about__head rv">
			<div class="stag"><?php esc_html_e('About this condition', 'woocommerce'); ?></div>
			<h2 class="stitle"><?php echo esc_html(sprintf(__('About %s', 'woocommerce'), $term->name)); ?></h2>
			<?php if (get_field('short_description', $term)) : ?>
				<p class="sdesc"><?php echo esc_html(wp_strip_all_tags((string) get_field('short_description', $term))); ?></p>
			<?php endif; ?>
		</div>
		<div class="trimvia-condition-about__blocks">
			<?php
			$block_index = 0;
			foreach ($condition_group as $group_key => $content_group) {
				if (!is_string($group_key) || strpos($group_key, 'content_group_') !== 0 || !is_array($content_group)) {
					continue;
				}
				$content_group_description   = isset($content_group['content_description']) ? $content_group['content_description'] : '';
				$content_group_featured_img = isset($content_group['featured_image']) ? $content_group['featured_image'] : null;
				if (empty($content_group_description)) {
					continue;
				}
				$block_index++;
				$swap = ($block_index % 2 === 0);
				?>
				<div class="trimvia-condition-about__block rv<?php echo $swap ? ' trimvia-condition-about__block--reverse' : ''; ?>">
					<div class="trimvia-condition-about__media">
						<?php
						if ($content_group_featured_img) {
							if (is_array($content_group_featured_img) && !empty($content_group_featured_img['ID'])) {
								echo wp_get_attachment_image((int) $content_group_featured_img['ID'], 'full', false, array('class' => 'trimvia-condition-about__image', 'loading' => 'lazy'));
							} elseif (is_array($content_group_featured_img) && !empty($content_group_featured_img['url'])) {
								echo '<img src="' . esc_url($content_group_featured_img['url']) . '" class="trimvia-condition-about__image" alt="" loading="lazy" />';
							} elseif (is_numeric($content_group_featured_img)) {
								echo wp_get_attachment_image((int) $content_group_featured_img, 'full', false, array('class' => 'trimvia-condition-about__image', 'loading' => 'lazy'));
							}
						} else {
							$ph = function_exists('get_placeholder_image') ? get_placeholder_image() : '';
							if ($ph) {
								echo '<img src="' . esc_url($ph) . '" class="trimvia-condition-about__image" alt="" loading="lazy" />';
							}
						}
						?>
					</div>
					<div class="trimvia-condition-about__copy">
						<?php echo $content_group_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF WYSIWYG ?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
