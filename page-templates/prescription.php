<?php
/**
 * Template Name: Prescription
 * Template Post Type: page
 *
 * Child override preserving parent ACF-driven behavior.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<section class="banner-section pb-150 pt-150" style="background-image:url(<?php echo esc_url(get_field('banner_image')['url'] ?: get_placeholder_image()); ?>);background-size:cover;background-repeat:no-repeat;background-position:center;">
	<div class="container">
		<div class="row">
			<div class="col-lg-5 col-md-12">
				<div class="presc-banner-content">
					<?php echo wp_kses_post(get_field('banner_content')); ?>
					<div class="banner-cta mt-3">
						<?php if (get_field('cta_button_1_type') === 'link' && get_field('banner_cta_button_1')) : ?>
							<a class="theme-btn mb-2 mr-2" href="<?php echo esc_url(get_field('banner_cta_button_1')['url'] ?: '#'); ?>" target="<?php echo esc_attr(get_field('banner_cta_button_1')['target'] ?: '_self'); ?>"><?php echo esc_html(get_field('banner_cta_button_1')['title']); ?></a>
						<?php endif; ?>
						<?php if (!is_user_logged_in() && get_field('cta_button_1_type') === 'upw' && get_field('cta_button_1_text')) : ?>
							<a class="theme-btn mb-2 mr-2" href="#" data-bs-target="#pmLoginModal" data-bs-toggle="modal" data-bs-dismiss="modal"><?php echo esc_html(get_field('cta_button_1_text')); ?></a>
						<?php endif; ?>
						<?php if (get_field('cta_button_2_type') === 'link' && get_field('banner_cta_button_2')) : ?>
							<a class="theme-btn mb-2 mr-2" href="<?php echo esc_url(get_field('banner_cta_button_2')['url'] ?: '#'); ?>" target="<?php echo esc_attr(get_field('banner_cta_button_2')['target'] ?: '_self'); ?>"><?php echo esc_html(get_field('banner_cta_button_2')['title']); ?></a>
						<?php endif; ?>
						<?php if (!is_user_logged_in() && get_field('cta_button_2_type') === 'upw' && get_field('cta_button_2_text')) : ?>
							<a class="theme-btn mb-2 mr-2" href="#" data-bs-target="#pmLoginModal" data-bs-toggle="modal" data-bs-dismiss="modal"><?php echo esc_html(get_field('cta_button_2_text')); ?></a>
						<?php endif; ?>
						<img class="nhs-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nhs-logo.svg'); ?>" alt="NHS">
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="order-step-section pb-100 pt-100">
	<div class="container">
		<div class="row justify-content-center">
			<?php if (get_field('step_section_heading')) : ?>
				<div class="col-lg-12 col-md-12 mb-4 text-center">
					<h2 class="section-heading"><?php echo esc_html(get_field('step_section_heading')); ?></h2>
				</div>
			<?php endif; ?>
			<?php
			for ($i = 1; $i <= 3; $i++) :
				$step = get_field('order_step_' . $i);
				if (!$step) {
					continue;
				}
				?>
				<div class="col-lg-4 col-md-6 col-sm-12">
					<div class="featured-image mb-3">
						<img src="<?php echo esc_url($step['image']['url'] ?: get_placeholder_image()); ?>" class="img img-fluid" alt="">
					</div>
					<div class="order-step-content"><?php echo wp_kses_post($step['short_content']); ?></div>
				</div>
			<?php endfor; ?>
		</div>

		<div class="row mt-3">
			<div class="col-lg-12 col-md-12 text-center">
				<?php if (get_field('order_call_to_action_type') === 'link' && get_field('order_call_to_action')) : ?>
					<a class="theme-btn" href="<?php echo esc_url(get_field('order_call_to_action')['url']); ?>" target="<?php echo esc_attr(get_field('order_call_to_action')['target'] ?: '_self'); ?>"><?php echo esc_html(get_field('order_call_to_action')['title']); ?></a>
				<?php endif; ?>
				<?php if (!is_user_logged_in() && get_field('order_call_to_action_type') === 'upw' && get_field('order_call_to_action_text')) : ?>
					<a class="theme-btn mb-2" href="#" data-bs-target="#pmLoginModal" data-bs-toggle="modal" data-bs-dismiss="modal"><?php echo esc_html(get_field('order_call_to_action_text')); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<section class="prescription-content-section bg-theme-style-2 pb-100 pt-100">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 col-md-12 mb-3">
				<div class="featured-image">
					<img src="<?php echo esc_url(has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'full') : get_placeholder_image()); ?>" class="img img-fluid" alt="">
				</div>
			</div>
			<div class="col-lg-6 col-md-12 mb-3">
				<div class="presc-content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="step-section pb-50 pt-100">
	<div class="container">
		<?php
		for ($i = 1; $i <= 6; $i++) :
			$step = get_field('how_it_work_step_' . $i);
			if (empty($step['short_content'])) {
				continue;
			}
			?>
			<div class="row align-items-center step-content mb-100">
				<div class="col-lg-6 col-md-12 mb-3 <?php echo $i % 2 === 0 ? 'order-md-1 order-2' : ''; ?>">
					<div class="featured-image">
						<img src="<?php echo esc_url($step['image']['url'] ?: get_placeholder_image()); ?>" class="img img-fluid" alt="">
					</div>
				</div>
				<div class="col-lg-6 col-md-12 mb-3">
					<div class="short-content mb-2"><?php echo wp_kses_post($step['short_content']); ?></div>
					<div class="cta-block">
						<?php if ($step['call_to_action_type'] === 'link' && !empty($step['call_to_action'])) : ?>
							<a class="theme-btn" href="<?php echo esc_url($step['call_to_action']['url']); ?>" target="<?php echo esc_attr($step['call_to_action']['target'] ?: '_self'); ?>"><?php echo esc_html($step['call_to_action']['title']); ?></a>
						<?php endif; ?>
						<?php if (!is_user_logged_in() && $step['call_to_action_type'] === 'upw' && !empty($step['call_to_action_text'])) : ?>
							<a class="theme-btn mb-2" href="#" data-bs-target="#pmLoginModal" data-bs-toggle="modal" data-bs-dismiss="modal"><?php echo esc_html($step['call_to_action_text']); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endfor; ?>
	</div>
</section>
<?php if (!get_field('reviews_section_switch') && get_field('reviews_shortcode')) : ?>
	<section class="reviews-section bg-theme-style-3 pb-100 pt-100">
		<div class="container">
			<div class="row">
				<?php if (get_field('reviews_heading')) : ?>
					<div class="col-lg-12 col-md-12 mb-4 text-center">
						<h2 class="section-heading"><?php echo esc_html(get_field('reviews_heading')); ?></h2>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="container">
			<div class="our-reviews"><?php echo do_shortcode(get_field('reviews_shortcode')); ?></div>
		</div>
	</section>
<?php endif; ?>
<?php if (!get_field('app_section_switch') && get_field('ourapp_short_content')) : ?>
	<section class="app-section pb-100 pt-100">
		<div class="container">
			<div class="row">
				<div class="col-lg-10 col-md-12 mx-auto mb-4">
					<div class="pt-20 pb-20 pl-20 pr-20 app-content-container bg-theme-style-1">
						<div class="row">
							<div class="col-lg-6 col-md-12 mb-3">
								<div class="featured-image">
									<img src="<?php echo esc_url(get_field('featured_image')['url'] ?: get_placeholder_image()); ?>" class="img img-fluid" alt="">
								</div>
							</div>
							<div class="col-lg-6 col-md-12 app-content">
								<?php echo wp_kses_post(get_field('ourapp_short_content')); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
