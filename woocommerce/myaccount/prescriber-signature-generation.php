<?php
/**
 * Prescriber signature pad popup (child override).
 *
 * @package theme-woopm-child
 */

defined( 'ABSPATH' ) || exit;

$show_close = isset( $presc_edit_mode ) || ! isset( $step );
?>
<div class="prescriber-sign-gen-wrapper make-popup <?php echo isset( $step ) ? 'step-' . esc_attr( $step ) . ' d-none ' : ''; ?><?php echo isset( $presc_edit_mode ) ? ' position-fixed presc-edit-popup' : ''; ?>">
	<div class="prescriber-sign-gen-container popup-content-wrapper">
		<?php if ( $show_close ) : ?>
			<div class="close-popup">
				<button type="button" class="close-me" aria-label="<?php esc_attr_e( 'Close', 'theme-woopm-child' ); ?>">&times;</button>
			</div>
		<?php endif; ?>
		<h2><?php esc_html_e( 'Draw your Signature', 'theme-woopm-child' ); ?></h2>
		<p><?php esc_html_e( 'Here you can setup your signature which will be used later in your generated prescription during approval of prescription.', 'theme-woopm-child' ); ?></p>
		<div class="sign-form-wrapper">
			<?php wp_nonce_field( 'sign-generation-nonce_' . $user_id ); ?>
			<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
			<div class="signature-input-wrapper">
				<div id="presc-jsignature"></div>
			</div>
			<div class="form-input-wrapper">
				<button type="button" class="btn theme-btn save-sign my-3 mr-1"><?php esc_html_e( 'Save', 'theme-woopm-child' ); ?></button>
				<button type="button" class="btn theme-btn processing d-none my-3 mr-1"><?php esc_html_e( 'Processing...', 'theme-woopm-child' ); ?></button>
				<button type="button" class="btn theme-btn-s4 clear-sign my-3 mr-1"><?php esc_html_e( 'clear', 'theme-woopm-child' ); ?></button>
				<a class="btn theme-btn my-3 mr-1" href="<?php echo esc_url( wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'Logout', 'theme-woopm-child' ); ?></a>
			</div>
		</div>
	</div>
</div>
