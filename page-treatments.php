<?php
/**
 * Front-end template for the WordPress page with slug `treatments`.
 *
 * Renders the live condition layout (hero + Woo products + about + FAQs), not the static HTML demo.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$term = trimvia_get_treatments_landing_condition_term();

if ($term instanceof WP_Term && 'condition' === $term->taxonomy) {
	get_template_part(
		'template-parts/content/content',
		'condition-treatments',
		array(
			'term' => $term,
		)
	);
} else {
	?>
	<section class="trimvia-conditions-hero section-pad">
		<div class="container">
			<div class="trimvia-conditions-hero__inner text-center">
				<h1 class="trimvia-conditions-hero__title">
					<span class="trimvia-conditions-hero__title-plain"><?php esc_html_e('Treatments', 'theme-woopm-child'); ?> </span>
					<span class="trimvia-conditions-hero__title-accent"><?php esc_html_e('unavailable', 'theme-woopm-child'); ?></span>
				</h1>
				<div class="trimvia-conditions-hero__desc">
					<p>
						<?php esc_html_e('No matching condition was found. Set the condition slug in Appearance → Customize → Service Pages, or open a condition from your menu.', 'theme-woopm-child'); ?>
					</p>
				</div>
				<p class="mt-3">
					<a class="btn-accent" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to home', 'theme-woopm-child'); ?></a>
				</p>
			</div>
		</div>
	</section>
	<?php
}

get_footer();
