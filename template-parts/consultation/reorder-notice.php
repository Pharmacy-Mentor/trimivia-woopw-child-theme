<?php
/**
 * Returning patient reorder prompt (parent recommend flow).
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$condition_slug              = $args['condition_slug'] ?? '';
$previous_completed_order_id   = (int) ($args['previous_completed_order_id'] ?? 0);
?>
<section id="recommend-notification" class="consult-layout trimvia-consult-reorder-wrap">
	<div class="trimvia-consult-woo-outer">
		<div class="trimvia-consult-woo-card trimvia-consult-woo-card--single">
			<div class="trimvia-consult-reorder">
				<h3><?php esc_html_e('Welcome back!', 'woopw'); ?></h3>
				<p>
					<?php
					esc_html_e(
						'You’ve previously completed a consultation with us. Using your earlier information, we can quickly show you personalized product recommendations.',
						'woopw'
					);
					?>
				</p>
				<p>
					<?php
					esc_html_e(
						'Would you like to continue using your previous details, or start a fresh consultation?',
						'woopw'
					);
					?>
				</p>
				<div class="consultation-actions">
					<?php
					$cache         = function_exists('uniqueIdReal') ? uniqueIdReal(16) : wp_generate_password(12, false);
					$redirect_args = array(
						'nocache'    => $cache,
						'is_reorder' => 1,
					);
					if ($previous_completed_order_id) {
						$redirect_args['order_id'] = $previous_completed_order_id;
					}
					$redirect_url = add_query_arg($redirect_args, site_url('/condition/' . rawurlencode($condition_slug)));
					?>
					<a href="#consultationform" class="btn-next trimvia-consult-reassessment-btn">
						<?php esc_html_e('Complete reassessment', 'woopw'); ?>
					</a>
					<a href="<?php echo esc_url($redirect_url); ?>" class="btn-next trimvia-consult-reorder-btn">
						<?php esc_html_e('Continue with previous consultation', 'woopw'); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
