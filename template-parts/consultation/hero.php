<?php
/**
 * Consultation hero / banner.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term                     = $args['term'] ?? null;
$questionnaire_id         = $args['questionnaire_id'] ?? '';
$consult_hero_sub         = $args['consult_hero_sub'] ?? '';
$consult_approx_minutes   = max(1, (int) ($args['consult_approx_minutes'] ?? 5));
?>
<section class="page-hero page-hero--consultation">
	<div class="hero-noise" aria-hidden="true"></div>
	<div class="container">
		<nav class="breadcrumb breadcrumb--consultation" aria-label="<?php esc_attr_e('Breadcrumb', 'woocommerce'); ?>">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'woopw'); ?></a>
			<span class="breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
			<span class="breadcrumb-current"><?php the_title(); ?></span>
		</nav>
		<?php if ($questionnaire_id && $term instanceof WP_Term) : ?>
			<h1>
				<?php
				printf(
					/* translators: %s: condition name */
					esc_html__('Assessment for %s', 'woopw'),
					esc_html($term->name)
				);
				?>
			</h1>
		<?php else : ?>
			<h1><?php the_title(); ?></h1>
		<?php endif; ?>
		<p><?php echo esc_html($consult_hero_sub); ?></p>
		<div class="consult-banner__chip consult-hero-meta" role="note">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			<?php
			printf(
				/* translators: %d: estimated minutes */
				esc_html(_n('Takes approximately %d minute', 'Takes approximately %d minutes', $consult_approx_minutes, 'woopw')),
				$consult_approx_minutes
			);
			?>
		</div>
	</div>
</section>
