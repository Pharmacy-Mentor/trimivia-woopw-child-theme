<?php
/**
 * Main consultation form shell — WooPW shortcode only; design via child CSS.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$context                    = is_array($args['context'] ?? null) ? $args['context'] : array();
$show_questionnaire_sidebar = !empty($context['show_questionnaire_sidebar']);
$term                       = $context['term'] ?? null;
$questionnaire_html         = trimvia_render_consultation_questionnaire($context);
?>
<section id="consultationform" class="consult-layout trimvia-consult-form-shell">
	<div class="trimvia-consult-woo-outer">
		<div class="trimvia-consult-woo-card<?php echo $show_questionnaire_sidebar ? '' : ' trimvia-consult-woo-card--single'; ?>">
			<div class="trimvia-consult-woo-card__grid consult-grid">
				<div class="trimvia-consult-woo-main">
					<?php if ($show_questionnaire_sidebar && $term instanceof WP_Term) : ?>
						<div class="trimvia-consult-condition-intro">
							<h2 class="trimvia-consult-condition-intro__title"><?php echo esc_html($term->name); ?></h2>
							<div class="trimvia-consult-condition-intro__text">
								<?php
								$t_desc = term_description($term->term_id, 'condition');
								if ($t_desc) {
									echo apply_filters('the_content', $t_desc); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo '<p>' . esc_html__(
										'Please fill out the form below so that our clinicians can determine if the treatment will be suitable for you to take.',
										'woocommerce'
									) . '</p>';
								}
								?>
							</div>
						</div>
					<?php endif; ?>

					<div class="consult-form trimvia-consult-woo-form consult-form--trimvia-shell">
						<div class="trimvia-consult-questionnaire">
							<?php
							if ($questionnaire_html !== '') {
								echo $questionnaire_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode / ACF HTML
							}
							?>
						</div>
					</div>
				</div>

				<?php if ($show_questionnaire_sidebar) : ?>
					<?php get_template_part('template-parts/consultation/sidebar', null, $context); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
