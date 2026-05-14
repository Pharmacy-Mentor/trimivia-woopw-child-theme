<?php
/**
 * Legacy sections from parent theme single-service (order steps, categories, booking, FAQs).
 * Expects global $post set to the current service.
 */
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
if (!$post_id || 'service' !== get_post_type($post_id)) {
    return;
}
?>
<?php if (get_field('order_steps_visibility')) : ?>
<!-- Order Steps -->
<section class="order-step-section section-padding section-background-1">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="order-counter theme-bg-gradient">
					<p><?php echo esc_html(get_field('countdown_before_text')); ?> <span id="countdown" <?php echo get_option('woopw_delivery_cutoff') ? 'data-cutoff-time="' . esc_attr(get_option('woopw_delivery_cutoff')) . '"' : ''; ?>>0h 0m 0s</span> <?php echo esc_html(get_field('countdown_after_text')); ?></p>
					<span class="separator"></span>
					<p><?php echo esc_html(get_field('countdown_freeshipping_text')); ?></p>
				</div>
			</div>
		</div>
		<div class="row justify-content-between">
		<?php if (have_rows('order_steps_listing')) : ?>
			<?php
			while (have_rows('order_steps_listing')) :
				the_row();
				$icon_type  = get_sub_field('steps_iconimage');
				$icon_class = get_sub_field('steps_icon');
				$image_url  = get_sub_field('steps_image');
				$heading    = get_sub_field('steps_heading');
				$content    = get_sub_field('steps_content');
				?>
				<div class="col-lg-4 col-md-6 content-column">
					<div class="order-step-item">
						<?php if ($icon_type) : ?>
							<div class="icon">
								<?php if ('Icon' === $icon_type && $icon_class) : ?>
									<i class="<?php echo esc_attr($icon_class); ?>"></i>
								<?php elseif ('Image' === $icon_type && $image_url) : ?>
									<img src="<?php echo esc_url($image_url); ?>" class="img-fluid" alt="<?php echo esc_attr($heading); ?>" />
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="content">
							<?php if ($heading) : ?>
								<h3><?php echo esc_html($heading); ?></h3>
							<?php endif; ?>

							<?php if ($content) : ?>
								<p><?php echo esc_html($content); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
	</div>
</section>
<?php endif; ?>

<?php if (get_field('popular_categories_visibility')) : ?>
<section class="popular-categories section-padding">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 section-header-wrapper text-center mb-5">
				<div class="content-block">
					<?php if (get_field('category_small_heading')) : ?>
						<h5><?php echo esc_html(get_field('category_small_heading')); ?></h5>
					<?php endif; ?>
					<?php if (get_field('category_heading')) : ?>
						<h2 class="section-title"><?php echo esc_html(get_field('category_heading')); ?></h2>
					<?php endif; ?>
					<?php if (get_field('category_content')) : ?>
						<?php echo wp_kses_post(wpautop(get_field('category_content'))); ?>
					<?php endif; ?>
				</div>
			</div>

			<?php
			$categories = get_field('category_listing');
			if ($categories) :
				foreach ($categories as $cat) {
					$term = get_term($cat);
					if (!$term) {
						continue;
					}

					$term_link = get_term_link($term->term_id);
					$term_name = $term->name;
					?>
					<div class="col-lg-4 col-md-6 col-sm-12 mb-4">
						<div class="popular-cat">
							<div class="featured-image position-relative">
								<a href="<?php echo esc_url($term_link); ?>" class="overlay-link"></a>
								<?php
								if (function_exists('get_field') && get_field('featured_image', $term)) {
									echo wp_get_attachment_image(
										get_field('featured_image', $term),
										'medium_large',
										false,
										array(
											'class' => 'img-fluid',
											'alt'   => $term_name,
										)
									);
								} elseif (function_exists('get_placeholder_image')) {
									?>
									<img src="<?php echo esc_url(get_placeholder_image()); ?>" class="img-fluid" alt="<?php echo esc_attr($term->name); ?>" />
								<?php } ?>
							</div>
							<div class="term-meta">
								<h4><a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term_name); ?></a></h4>
								<div class="call-to-action text-center">
									<a class="theme-btn-outline w-100" href="<?php echo esc_url($term_link); ?>">
										<?php esc_html_e('View Condition', 'woocommerce'); ?> <i class="fa fa-angle-right"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
			endif;
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if (get_field('booking_calendar_visibility')) : ?>
<section class="booking-calender section-padding section-background-1 ">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 section-header-wrapper text-center mb-5">
				<div class="content-block">
					<?php if (get_field('booking_calendar_heading')) : ?>
						<h5><?php echo esc_html(get_field('booking_calendar_heading')); ?></h5>
					<?php endif; ?>
					<?php echo get_field('booking_calendar_content'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- legacy rich field ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if (get_field('faqs_visibility')) : ?>
<section class="faqs-section section-padding section-background-2">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 section-header-wrapper text-center mb-5">
				<div class="content-block">
					<?php if (get_field('faqs_small_heading')) : ?>
						<h5><?php echo esc_html(get_field('faqs_small_heading')); ?></h5>
					<?php endif; ?>
					<?php if (get_field('faqs_heading')) : ?>
						<h2 class="section-title"><?php echo esc_html(get_field('faqs_heading')); ?></h2>
					<?php endif; ?>
					<?php if (get_field('faqs_content')) : ?>
						<?php echo wp_kses_post(wpautop(get_field('faqs_content'))); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-lg-12 col-md-12">
				<div class="faq-accordion" id="faqAccordion">
					<?php
					$condition_faqs = get_field('condition_faqs');
					if (!empty($condition_faqs) && is_array($condition_faqs)) {
						$f_ind = 0;
						foreach ($condition_faqs as $faq_post) {
							setup_postdata($faq_post);
							?>
					<div class="card <?php echo 0 === $f_ind ? '' : 'collapsed'; ?>" data-toggle="collapse" data-target="#collapse<?php echo esc_attr((string) $f_ind); ?>" aria-expanded="true" aria-controls="collapse<?php echo esc_attr((string) $f_ind); ?>">
						<div class="card-header" id="heading<?php echo esc_attr((string) $f_ind); ?>">
							<h4 class="mb-0">
								<?php the_title(); ?> <i class="fa fa-angle-down"></i>
							</h4>
						</div>
						<div id="collapse<?php echo esc_attr((string) $f_ind); ?>" class="collapse <?php echo 0 === $f_ind ? 'show' : ''; ?>" aria-labelledby="heading<?php echo esc_attr((string) $f_ind); ?>" data-parent="#faqAccordion">
							<div class="card-body">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
							<?php
							++$f_ind;
						}
						wp_reset_postdata();
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
