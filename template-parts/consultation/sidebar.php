<?php
/**
 * Consultation progress sidebar (Trimvia design — synced by trimvia-cflp-consult.js).
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$trimvia_contact_url    = $args['trimvia_contact_url'] ?? home_url('/contact-us/');
$trimvia_contact_phone  = $args['trimvia_contact_phone'] ?? '';
$trimvia_condition_cancel_url = $args['trimvia_condition_cancel_url'] ?? home_url('/');
?>
<aside class="consult-sidebar trimvia-consult-woo-sidebar" id="trimvia-assessment-progress" aria-label="<?php esc_attr_e('Assessment progress', 'woocommerce'); ?>">
	<div class="sidebar-card sidebar-card--progress">
		<h4><?php esc_html_e('Assessment Progress', 'woocommerce'); ?></h4>
		<div class="progress-list" id="trimvia-consult-progress-list" aria-live="polite"></div>
	</div>
	<div class="sidebar-card">
		<div class="sidebar-help">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
			<div>
				<strong><?php esc_html_e('Need a helping hand?', 'woopw'); ?></strong><br>
				<?php if ($trimvia_contact_phone !== '') : ?>
					<?php $trimvia_phone_href = preg_replace('/[^0-9+]/', '', $trimvia_contact_phone); ?>
					<?php esc_html_e('Give us a call on', 'woopw'); ?>
					<strong><a href="tel:<?php echo esc_attr($trimvia_phone_href); ?>"><?php echo esc_html($trimvia_contact_phone); ?></a></strong>
					<?php esc_html_e(' or ', 'woopw'); ?>
				<?php endif; ?>
				<a href="<?php echo esc_url($trimvia_contact_url); ?>"><?php esc_html_e('Contact us', 'woopw'); ?></a>
			</div>
		</div>
	</div>
	<div class="sidebar-card">
		<div class="sidebar-trust">
			<div class="sidebar-trust-item">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				<?php esc_html_e('GPhC-registered pharmacy', 'woopw'); ?>
			</div>
			<div class="sidebar-trust-item">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				<?php esc_html_e('UK prescribers review every order', 'woopw'); ?>
			</div>
			<div class="sidebar-trust-item">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				<?php esc_html_e('Confidential & discreet', 'woopw'); ?>
			</div>
			<div class="sidebar-trust-item">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				<?php esc_html_e('Next-day delivery available', 'woopw'); ?>
			</div>
		</div>
	</div>
	<p class="trimvia-consult-cancel-wrap">
		<a href="<?php echo esc_url($trimvia_condition_cancel_url); ?>" class="trimvia-consult-cancel-link"><?php esc_html_e('Cancel assessment', 'woopw'); ?></a>
	</p>
</aside>
