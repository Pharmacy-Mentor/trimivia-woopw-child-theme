<?php
/**
 * My Account dashboard — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$display_name = $current_user instanceof WP_User && $current_user->exists() ? $current_user->display_name : __('there', 'theme-woopm-child');
$email = $current_user instanceof WP_User ? $current_user->user_email : '';
$initials = '';

if ($current_user instanceof WP_User && $current_user->exists()) {
	$name_parts = preg_split('/\s+/', trim($display_name));
	if (is_array($name_parts)) {
		foreach (array_slice($name_parts, 0, 2) as $name_part) {
			$initials .= strtoupper(substr($name_part, 0, 1));
		}
	}
}
$initials = $initials ?: 'TV';

$orders = array();
if (function_exists('wc_get_orders') && $current_user instanceof WP_User && $current_user->ID) {
	$orders = wc_get_orders(
		array(
			'customer_id' => $current_user->ID,
			'limit' => 2,
			'orderby' => 'date',
			'order' => 'DESC',
		)
	);
}

$orders_url = wc_get_account_endpoint_url('orders');
$account_url = wc_get_account_endpoint_url('edit-account');
$addresses_url = wc_get_account_endpoint_url('edit-address');
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$contact_url = home_url('/contact/');
?>

<div class="account-welcome rv">
	<div class="account-avatar" aria-hidden="true"><?php echo esc_html($initials); ?></div>
	<div class="account-welcome-text">
		<h2><?php echo esc_html(sprintf(__('Hello, %s', 'theme-woopm-child'), $display_name)); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: account email */
				esc_html__('Signed in as %s. Manage your orders, addresses, and account details securely.', 'theme-woopm-child'),
				'<strong>' . esc_html($email) . '</strong>'
			);
			?>
		</p>
	</div>
	<div class="account-welcome-actions">
		<a href="<?php echo esc_url($shop_url); ?>" class="btn-accent"><?php esc_html_e('Shop treatments', 'theme-woopm-child'); ?></a>
		<a href="<?php echo esc_url($contact_url); ?>" class="btn-ghost"><?php esc_html_e('Contact care team', 'theme-woopm-child'); ?></a>
	</div>
</div>

<dl class="account-quick-grid">
	<div class="account-quick-card">
		<dt><?php esc_html_e('Recent orders', 'theme-woopm-child'); ?></dt>
		<dd>
			<?php echo esc_html(number_format_i18n(count($orders))); ?>
			<small><?php esc_html_e('Latest account activity', 'theme-woopm-child'); ?></small>
		</dd>
	</div>
	<div class="account-quick-card">
		<dt><?php esc_html_e('Delivery details', 'theme-woopm-child'); ?></dt>
		<dd>
			<?php esc_html_e('Saved addresses', 'theme-woopm-child'); ?>
			<small><a href="<?php echo esc_url($addresses_url); ?>"><?php esc_html_e('Manage delivery and billing', 'theme-woopm-child'); ?></a></small>
		</dd>
	</div>
	<div class="account-quick-card">
		<dt><?php esc_html_e('Account security', 'theme-woopm-child'); ?></dt>
		<dd>
			<?php esc_html_e('Profile details', 'theme-woopm-child'); ?>
			<small><a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Update email or password', 'theme-woopm-child'); ?></a></small>
		</dd>
	</div>
</dl>

<div class="account-section-head">
	<h2><?php esc_html_e('Recent orders', 'theme-woopm-child'); ?></h2>
	<a href="<?php echo esc_url($orders_url); ?>"><?php esc_html_e('View all orders', 'theme-woopm-child'); ?></a>
</div>

<?php if (!empty($orders)) : ?>
	<div class="account-orders">
		<?php foreach ($orders as $index => $order) : ?>
			<?php
			if (!$order instanceof WC_Order) {
				continue;
			}
			$items = $order->get_items();
			$item_names = array();
			foreach ($items as $item) {
				$item_names[] = $item->get_name();
			}
			$order_title = !empty($item_names) ? implode(', ', array_slice($item_names, 0, 2)) : __('Order items', 'theme-woopm-child');
			$status_class = $order->has_status(array('completed', 'processing')) ? 'order-status--dispatched' : 'order-status--review';
			?>
			<article class="account-order-card rv rv-d<?php echo esc_attr((string) min($index + 1, 3)); ?>">
				<div>
					<div class="account-order-meta">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: order number, 2: order date */
								__('Order #%1$s · Placed %2$s', 'theme-woopm-child'),
								$order->get_order_number(),
								wc_format_datetime($order->get_date_created())
							)
						);
						?>
					</div>
					<div class="account-order-title"><?php echo esc_html($order_title); ?></div>
					<div class="account-order-sub">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: item count */
								_n('%s item', '%s items', $order->get_item_count(), 'theme-woopm-child'),
								number_format_i18n($order->get_item_count())
							)
						);
						?>
					</div>
				</div>
				<div class="account-order-aside">
					<span class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span>
					<div class="account-order-price"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></div>
					<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="btn-sm"><?php esc_html_e('View order', 'theme-woopm-child'); ?></a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="account-empty-card">
		<h3><?php esc_html_e('No recent orders yet', 'theme-woopm-child'); ?></h3>
		<p><?php esc_html_e('When you order a treatment, tracking and order details will appear here.', 'theme-woopm-child'); ?></p>
		<a href="<?php echo esc_url($shop_url); ?>" class="btn-accent"><?php esc_html_e('Browse treatments', 'theme-woopm-child'); ?></a>
	</div>
<?php endif; ?>

<div class="account-profile-preview rv rv-d3">
	<h3><?php esc_html_e('Contact & login', 'theme-woopm-child'); ?></h3>
	<div class="form-grid">
		<div class="form-group">
			<label class="form-label"><?php esc_html_e('Email address', 'theme-woopm-child'); ?></label>
			<input type="email" class="form-input" value="<?php echo esc_attr($email); ?>" disabled>
		</div>
		<div class="form-group">
			<label class="form-label"><?php esc_html_e('Display name', 'theme-woopm-child'); ?></label>
			<input type="text" class="form-input" value="<?php echo esc_attr($display_name); ?>" disabled>
		</div>
		<div class="form-group full">
			<a href="<?php echo esc_url($account_url); ?>" class="btn-accent"><?php esc_html_e('Edit account details', 'theme-woopm-child'); ?></a>
			<a href="<?php echo esc_url($addresses_url); ?>" class="btn-ghost"><?php esc_html_e('Manage addresses', 'theme-woopm-child'); ?></a>
		</div>
	</div>
</div>

<?php
do_action('woocommerce_before_my_account');
do_action('woocommerce_after_my_account');
?>
