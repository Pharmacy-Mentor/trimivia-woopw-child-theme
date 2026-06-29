<?php
/**
 * "Order received" summary — Trimvia thank you header.
 *
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;

if (!$order) {
	?>
	<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received trimvia-order-received-message">
		<?php esc_html_e('Thank you. Your order has been received.', 'woocommerce'); ?>
	</p>
	<?php
	return;
}

$order_status = $order->get_status();
$status_name  = wc_get_order_status_name($order_status);
$status_class = 'order-status--default';

if (in_array($order_status, array('processing', 'pre-screen', 'on-hold', 'pending'), true)) {
	$status_class = 'order-status--review';
} elseif (in_array($order_status, array('completed', 'dispatched'), true)) {
	$status_class = 'order-status--dispatched';
} elseif (in_array($order_status, array('cancelled', 'failed', 'refunded'), true)) {
	$status_class = 'order-status--cancelled';
}

$meta_items = array(
	array(
		'key'   => 'total',
		'label' => __('Total', 'woocommerce'),
		'value' => $order->get_formatted_order_total(),
		'html'  => true,
	),
);

if ($order->get_billing_email()) {
	$meta_items[] = array(
		'key'   => 'email',
		'label' => __('Email', 'woocommerce'),
		'value' => $order->get_billing_email(),
	);
}

if ($order->get_payment_method_title()) {
	$meta_items[] = array(
		'key'   => 'payment',
		'label' => __('Payment method', 'woocommerce'),
		'value' => $order->get_payment_method_title(),
		'html'  => true,
	);
}
?>

<div class="trimvia-view-order-status trimvia-order-received-status">
	<div class="trimvia-view-order-status__copy">
		<span class="trimvia-view-order-status__eyebrow"><?php esc_html_e('Order confirmed', 'theme-woopm-child'); ?></span>
		<p class="trimvia-order-received-message">
			<?php
			echo wp_kses_post(
				apply_filters(
					'woocommerce_thankyou_order_received_text',
					sprintf(
						/* translators: 1: order number 2: order date */
						__('Thank you. Order #%1$s was placed on %2$s.', 'theme-woopm-child'),
						'<strong>' . esc_html($order->get_order_number()) . '</strong>',
						'<strong>' . esc_html(wc_format_datetime($order->get_date_created())) . '</strong>'
					),
					$order
				)
			);
			?>
		</p>
	</div>
	<span class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_name); ?></span>
</div>

<div class="trimvia-order-received-meta" aria-label="<?php esc_attr_e('Order summary', 'theme-woopm-child'); ?>">
	<?php foreach ($meta_items as $item) : ?>
		<div class="trimvia-order-received-meta__item trimvia-order-received-meta__item--<?php echo esc_attr($item['key']); ?>">
			<span class="trimvia-order-received-meta__label"><?php echo esc_html($item['label']); ?></span>
			<?php if (!empty($item['html'])) : ?>
				<strong class="trimvia-order-received-meta__value"><?php echo wp_kses_post($item['value']); ?></strong>
			<?php else : ?>
				<strong class="trimvia-order-received-meta__value"><?php echo esc_html($item['value']); ?></strong>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
