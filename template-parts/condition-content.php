<?php
/**
 * Condition content section (about condition blocks).
 *
 * Child override keeps the same data source and behavior
 * while using child-friendly section structure.
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
<section class="section-padding about-condition-content">
	<div class="container">
		<div class="section-header-wrapper">
			<div class="row align-items-center">
				<div class="col-lg-12 col-md-12 text-center">
					<div class="content-block">
						<h5><?php esc_html_e('About This Condition', 'woocommerce'); ?></h5>
						<h2 class="section-title"><?php echo esc_html(sprintf(__('About %s', 'woocommerce'), $term->name)); ?></h2>
						<?php if (get_field('short_description', $term)) : ?>
							<?php echo wpautop(wp_kses_post(get_field('short_description', $term))); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="content-column-blocks mt-5">
			<?php
			$index = 0;
			foreach ($condition_group as $group_key => $content_group) :
				if (!is_array($content_group) || strpos((string) $group_key, 'content_group_') !== 0) {
					continue;
				}
				$description = isset($content_group['content_description']) ? $content_group['content_description'] : '';
				$featured_image = isset($content_group['featured_image']) ? $content_group['featured_image'] : 0;
				if (empty($description)) {
					continue;
				}
				$index++;
				$swap = ($index % 2 === 0);
				?>
				<div class="content-column">
					<div class="row align-items-center">
						<div class="col-lg-6 col-md-6 col-sm-12 <?php echo $swap ? 'order-md-2 order-1' : ''; ?>">
							<?php echo wp_kses_post($description); ?>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-12 <?php echo $swap ? 'order-md-1 order-2' : ''; ?>">
							<div class="featured-image-wrapper">
								<?php if (!empty($featured_image)) : ?>
									<?php echo wp_get_attachment_image((int) $featured_image, 'large', false, array('class' => 'img-fluid')); ?>
								<?php else : ?>
									<img src="<?php echo esc_url(get_placeholder_image()); ?>" class="img-fluid" alt="">
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
