<?php
/**
 * My Account navigation — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_navigation');

$icons = array(
	'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
	'practitioner-orders' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
	'orders' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
	'subscriptions' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
	'edit-address' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
	'edit-account' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
	'patient-history' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
	// WooPW "Prescription Upload" My Account item (endpoint + shortcode wired in functions.php).
	// Icon is used when that menu item is present; currently hidden while the add-on is disabled.
	'prescription-upload' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
	'customer-logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
);

$default_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
$account_menu_items = wc_get_account_menu_items();
$logout_url = function_exists('wc_logout_url') ? wc_logout_url() : wp_logout_url(wc_get_page_permalink('myaccount'));
?>

<aside class="account-nav-wrap woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e('Account menu', 'theme-woopm-child'); ?>">
	<h3><?php esc_html_e('Menu', 'theme-woopm-child'); ?></h3>
	<ul class="account-nav">
		<?php foreach ($account_menu_items as $endpoint => $label) : ?>
			<?php if ('customer-logout' === $endpoint) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
				<a href="<?php echo esc_url(trimvia_get_account_menu_item_url($endpoint, $label)); ?>">
					<?php echo wp_kses($icons[$endpoint] ?? $default_icon, trimvia_account_allowed_svg()); ?>
					<span class="menu-label"><?php echo esc_html(is_array($label) && isset($label['title']) ? $label['title'] : $label); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<ul class="account-nav account-nav--danger">
		<li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--customer-logout">
			<a href="<?php echo esc_url($logout_url); ?>">
				<?php echo wp_kses($icons['customer-logout'], trimvia_account_allowed_svg()); ?>
				<?php esc_html_e('Log out', 'woocommerce'); ?>
			</a>
		</li>
	</ul>
</aside>

<?php do_action('woocommerce_after_account_navigation'); ?>
