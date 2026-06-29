<?php
/**
 * Template Name: Treatments
 * Template Post Type: page
 *
 * Resolves a condition via ?condition-slug= and renders the same layout as
 * the condition taxonomy archive (content-condition-treatments.php).
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = null;
if (isset($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$condition_slug = sanitize_text_field(wp_unslash($_GET['condition-slug']));
	$found          = get_term_by('slug', $condition_slug, 'condition');
	if ($found && !is_wp_error($found)) {
		$term = $found;
	}
}

if ($term instanceof WP_Term && function_exists('trimvia_condition_has_visible_products') && !trimvia_condition_has_visible_products($term)) {
	wp_safe_redirect(home_url('/all-conditions/'));
	exit;
}

get_header();

if (!$term) :
	?>
	<section class="trimvia-conditions-hero section-pad">
		<div class="container">
			<div class="trimvia-conditions-hero__inner text-center">
				<h1 class="trimvia-conditions-hero__title">
					<span class="trimvia-conditions-hero__title-plain"><?php esc_html_e('Condition', 'woocommerce'); ?></span>
					<span class="trimvia-conditions-hero__title-accent"><?php esc_html_e('not found', 'woocommerce'); ?></span>
				</h1>
				<p class="trimvia-conditions-hero__desc">
					<?php esc_html_e('This treatment category could not be loaded. Please choose a condition from the list.', 'woocommerce'); ?>
				</p>
				<p class="mt-3">
					<a class="btn-accent" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to home', 'woocommerce'); ?></a>
				</p>
			</div>
		</div>
	</section>
	<?php
	get_footer();
	return;
endif;

get_template_part(
	'template-parts/content/content',
	'condition-treatments',
	array(
		'term' => $term,
	)
);
get_footer();
