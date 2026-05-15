<?php
/**
 * Global FAQs section.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$faq_args = array(
	'post_type'      => 'faqs',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
);

$faqs = new WP_Query($faq_args);
if (!$faqs->have_posts()) {
	wp_reset_query();
	return;
}
?>
<section class="section-pad trimvia-global-faqs" style="background:var(--white)">
	<div class="container">
		<div class="faq-center rv">
			<?php if (get_field('faq_section_heading', $front_page_id)) : ?>
				<div class="stag" style="justify-content:center"><?php echo esc_html(get_field('faq_section_heading', $front_page_id)); ?></div>
			<?php endif; ?>
			<?php if (get_field('faq_heading', $front_page_id)) : ?>
				<h2 class="stitle"><?php echo esc_html(get_field('faq_heading', $front_page_id)); ?></h2>
			<?php endif; ?>
			<?php if (get_field('faq_short_description', $front_page_id)) : ?>
				<p class="sdesc"><?php echo esc_html(wp_strip_all_tags(get_field('faq_short_description', $front_page_id))); ?></p>
			<?php endif; ?>
		</div>
		<div class="faq-list">
			<?php $f_ind = 0; while ($faqs->have_posts()) : $faqs->the_post(); ?>
				<div class="fq <?php echo 0 === $f_ind ? 'active' : ''; ?> rv">
					<button class="fq-btn" type="button">
						<?php the_title(); ?>
						<div class="fq-chev">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
						</div>
					</button>
					<div class="fq-a">
						<div class="fq-a-in"><?php the_content(); ?></div>
					</div>
				</div>
				<?php $f_ind++; ?>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php wp_reset_query(); ?>
