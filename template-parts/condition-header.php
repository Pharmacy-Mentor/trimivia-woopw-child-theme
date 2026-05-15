<?php
/**
 * Condition header (public treatments view).
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
?>
<section class="page-hero page-hero--service trimvia-condition-header">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--service">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url(home_url('/all-conditions/')); ?>"><?php esc_html_e('All Conditions', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span class="breadcrumb-current"><?php echo esc_html($term->name); ?></span>
		</div>
		<h1><?php echo esc_html(sprintf(__('%s Treatments', 'woocommerce'), $term->name)); ?></h1>
		<?php if (term_description($term->term_id, $term->taxonomy)) : ?>
			<p><?php echo esc_html(wp_strip_all_tags(term_description($term->term_id, $term->taxonomy))); ?></p>
		<?php endif; ?>
		<div class="call-to-action mt-4">
			<?php $questionnaire_id = get_field('questionnaire', $term); ?>
			<?php if (!empty($questionnaire_id)) : ?>
				<a class="btn-shop" href="<?php echo esc_url(get_site_url('') . '/consultation/?condition-slug=' . $term->slug); ?>">
					<?php esc_html_e('Start your Consultation', 'theme-woopm-child'); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php if (get_field('banner_content_tagline', $term)) : ?>
			<div class="banner-tagline-content mt-3">
				<?php echo wpautop(wp_kses_post(get_field('banner_content_tagline', $term))); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
