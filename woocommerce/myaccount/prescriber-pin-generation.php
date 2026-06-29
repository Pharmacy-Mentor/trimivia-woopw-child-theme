<?php
/**
 * Prescriber security PIN setup popup (child override).
 *
 * @package theme-woopm-child
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="prescriber-pin-gen-wrapper make-popup <?php echo isset( $step ) ? 'step-' . esc_attr( $step ) : ''; ?>">
	<div class="prescriber-pin-gen-container popup-content-wrapper">
		<h2><?php esc_html_e( 'Setup your Security Pin', 'theme-woopm-child' ); ?></h2>
		<p>
			<?php
			esc_html_e(
				'Please setup your 6 digit unique identification number that will be used to authorise prescription approval.',
				'theme-woopm-child'
			);
			?>
			<strong><?php esc_html_e( 'Your security PIN must be exactly 6 digits.', 'theme-woopm-child' ); ?></strong>
		</p>
		<div class="pin-form-wrapper">
			<?php wp_nonce_field( 'pin-generation-nonce_' . $user_id ); ?>
			<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
			<div class="form-input-wrapper">
				<input
					type="text"
					class="pin-input"
					name="pin_number"
					maxlength="6"
					inputmode="numeric"
					pattern="[0-9]*"
					autocomplete="off"
					aria-label="<?php esc_attr_e( 'Security PIN', 'theme-woopm-child' ); ?>"
				/>
			</div>
			<div class="form-input-wrapper">
				<button type="button" class="theme-btn btn processing d-none"><?php esc_html_e( 'Processing...', 'theme-woopm-child' ); ?></button>
				<button type="button" class="theme-btn btn save-pin mr-3"><?php esc_html_e( 'Next', 'theme-woopm-child' ); ?></button>
				<a href="<?php echo esc_url( wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="theme-btn-s4 save-pin mr-3"><?php esc_html_e( 'Logout', 'theme-woopm-child' ); ?></a>
			</div>
		</div>
	</div>
</div>
