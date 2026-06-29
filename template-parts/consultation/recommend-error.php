<?php
/**
 * Product recommendation mismatch notice.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<section class="consult-layout trimvia-consult-error-wrap">
	<div class="trimvia-consult-woo-outer">
		<div class="trimvia-consult-woo-card trimvia-consult-woo-card--single">
			<div class="trimvia-consult-error" role="alert">
				<h2 class="trimvia-consult-error__title"><?php esc_html_e('This treatment is no longer available.', 'woopw'); ?></h2>
				<p class="trimvia-consult-error__text">
					<?php esc_html_e('This product does not match your selected treatment. Please re-take the consultation to order this product.', 'woopw'); ?>
				</p>
			</div>
		</div>
	</div>
</section>
