<?php
/**
 * Condition FAQs section - child theme UI override.
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

$condition_faqs = function_exists('get_field') ? get_field('condition_faqs', $term) : array();
if (!is_array($condition_faqs) || empty($condition_faqs)) {
	return;
}

$front_page_id = (int) get_option('page_on_front');
$faq_small_heading = function_exists('get_field') ? (string) get_field('faq_section_heading', $front_page_id) : '';
$faq_heading = function_exists('get_field') ? (string) get_field('faq_heading', $front_page_id) : '';
$faq_description = function_exists('get_field') ? (string) get_field('faq_short_description', $front_page_id) : '';
?>
<section class="section-pad trimvia-condition-faqs" id="condition-faqs" style="background:var(--white)">
	<div class="container">
		<div class="faq-center rv">
			<?php if ('' !== trim($faq_small_heading)) : ?>
				<div class="stag" style="justify-content:center"><?php echo esc_html($faq_small_heading); ?></div>
			<?php endif; ?>
			<?php if ('' !== trim($faq_heading)) : ?>
				<h2 class="stitle"><?php echo esc_html($faq_heading); ?></h2>
			<?php endif; ?>
			<?php if ('' !== trim($faq_description)) : ?>
				<p class="sdesc"><?php echo esc_html(wp_strip_all_tags($faq_description)); ?></p>
			<?php endif; ?>
		</div>
		<div class="faq-list">
			<?php
			$faq_index = 0;
			foreach ($condition_faqs as $faq_post) :
				if (!$faq_post instanceof WP_Post) {
					$faq_post = get_post((int) $faq_post);
				}
				if (!$faq_post instanceof WP_Post || 'publish' !== $faq_post->post_status) {
					continue;
				}
				?>
				<div class="fq <?php echo 0 === $faq_index ? 'active' : ''; ?> rv">
					<button class="fq-btn" type="button">
						<?php echo esc_html(get_the_title($faq_post)); ?>
						<div class="fq-chev">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
								<path d="M6 9l6 6 6-6"></path>
							</svg>
						</div>
					</button>
					<div class="fq-a">
						<div class="fq-a-in">
							<?php echo wp_kses_post(apply_filters('the_content', (string) $faq_post->post_content)); ?>
						</div>
					</div>
				</div>
				<?php
				$faq_index++;
			endforeach;
			?>
		</div>
	</div>
</section>
