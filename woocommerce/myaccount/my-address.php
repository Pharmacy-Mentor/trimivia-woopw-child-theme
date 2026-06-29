<?php
/**
 * My Addresses — Trimvia account layout.
 *
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$customer_id = get_current_user_id();

if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __('Billing address', 'woocommerce'),
			'shipping' => __('Shipping address', 'woocommerce'),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __('Billing address', 'woocommerce'),
		),
		$customer_id
	);
}

$address_icons = array(
	'billing'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
	'shipping' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
);
?>

<div class="trimvia-addresses">
	<div class="trimvia-addresses-intro">
		<h2><?php esc_html_e('Saved addresses', 'theme-woopm-child'); ?></h2>
		<p>
			<?php
			echo wp_kses_post(
				apply_filters(
					'woocommerce_my_account_my_address_description',
					__('The following addresses will be used on the checkout page by default.', 'woocommerce')
				)
			);
			?>
		</p>
	</div>

	<div class="trimvia-addresses-grid woocommerce-Addresses addresses">
		<?php foreach ($get_addresses as $name => $address_title) : ?>
			<?php
			$customer     = new WC_Customer($customer_id);
			$address_rows = trimvia_get_address_detail_rows($customer, $name);
			$has_address  = !empty($address_rows['name']) || !empty($address_rows['address_lines']) || !empty($address_rows['phone']) || !empty($address_rows['email']);
			$edit_url     = wc_get_endpoint_url('edit-address', $name);
			$icon_markup  = $address_icons[$name] ?? $address_icons['billing'];
			$edit_label   = $has_address
				? __('Edit', 'theme-woopm-child')
				: __('Add', 'theme-woopm-child');
			?>
			<article class="trimvia-address-card woocommerce-Address u-column<?php echo 'billing' === $name ? '1' : '2'; ?> col-<?php echo 'billing' === $name ? '1' : '2'; ?>">
				<header class="trimvia-address-card__head woocommerce-Address-title title">
					<div class="trimvia-address-card__title-wrap">
						<span class="trimvia-address-card__icon" aria-hidden="true"><?php echo $icon_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h3><?php echo esc_html($address_title); ?></h3>
					</div>
					<a
						href="<?php echo esc_url($edit_url); ?>"
						class="edit trimvia-address-card__edit btn-sm"
						aria-label="<?php echo esc_attr(sprintf($has_address ? __('Edit %s', 'theme-woopm-child') : __('Add %s', 'theme-woopm-child'), $address_title)); ?>"
					>
						<?php echo esc_html($edit_label); ?>
					</a>
				</header>

				<div class="trimvia-address-card__body">
					<?php if ($has_address) : ?>
						<address class="trimvia-address-details"><?php trimvia_render_address_detail_rows($customer, $name); ?></address>
					<?php else : ?>
						<div class="trimvia-address-card__empty">
							<p><?php esc_html_e('You have not set up this address yet.', 'theme-woopm-child'); ?></p>
							<a href="<?php echo esc_url($edit_url); ?>" class="btn-ghost"><?php esc_html_e('Add address', 'theme-woopm-child'); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
