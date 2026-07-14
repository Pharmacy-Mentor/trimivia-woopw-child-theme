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
<?php
$service_faq_small_heading = trim((string) get_field('faqs_small_heading'));
$service_faq_heading       = trim((string) get_field('faqs_heading'));
$service_faq_content       = trim((string) get_field('faqs_content'));
$condition_faqs            = get_field('condition_faqs');
$service_faq_items         = array();

if (!empty($condition_faqs) && is_array($condition_faqs)) {
	foreach ($condition_faqs as $faq_post) {
		if (!$faq_post instanceof WP_Post) {
			$faq_post = get_post((int) $faq_post);
		}
		if (!$faq_post instanceof WP_Post || 'publish' !== $faq_post->post_status) {
			continue;
		}
		$service_faq_items[] = array(
			'question' => get_the_title($faq_post),
			'answer'   => apply_filters('the_content', (string) $faq_post->post_content),
		);
	}
}
?>
<?php if (!empty($service_faq_items)) : ?>
<section class="section-pad trimvia-service-faqs" id="service-faqs" style="background:var(--white)">
	<div class="container">
		<div class="faq-center rv">
			<?php if ('' !== $service_faq_small_heading) : ?>
				<div class="stag" style="justify-content:center"><?php echo esc_html($service_faq_small_heading); ?></div>
			<?php endif; ?>
			<?php if ('' !== $service_faq_heading) : ?>
				<h2 class="stitle"><?php echo esc_html($service_faq_heading); ?></h2>
			<?php endif; ?>
			<?php if ('' !== $service_faq_content) : ?>
				<p class="sdesc"><?php echo esc_html(wp_strip_all_tags($service_faq_content)); ?></p>
			<?php endif; ?>
		</div>
		<div class="faq-list">
			<?php foreach ($service_faq_items as $faq_index => $faq_item) : ?>
			<div class="fq <?php echo 0 === (int) $faq_index ? 'active' : ''; ?> rv">
				<button class="fq-btn" type="button">
					<?php echo esc_html($faq_item['question']); ?>
					<div class="fq-chev">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
							<path d="M6 9l6 6 6-6"></path>
						</svg>
					</div>
				</button>
				<div class="fq-a">
					<div class="fq-a-in"><?php echo wp_kses_post($faq_item['answer']); ?></div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php endif; ?>
