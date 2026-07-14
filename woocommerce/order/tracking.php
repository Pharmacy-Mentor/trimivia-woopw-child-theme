<?php
/**
 * Order tracking — Trimvia layout (desktop banner + mobile card).
 *
 * @package WooCommerce\Templates
 * @version 10.6.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;

$notes        = $order->get_customer_order_notes();
$order_status = $order->get_status();
$status_name  = wc_get_order_status_name($order_status);
$status_class = function_exists('trimvia_get_order_status_badge_class')
	? trimvia_get_order_status_badge_class($order_status)
	: 'trimvia-track-status--default';
$order_number = $order->get_order_number();
$order_date   = wc_format_datetime($order->get_date_created());
$order_date_mobile = wc_format_datetime($order->get_date_created(), 'j F Y');
?>
<div class="trimvia-track-order-summary" aria-live="polite">
	<div class="trimvia-track-order-summary__desktop">
		<p class="trimvia-track-order-summary__sentence">
			<?php esc_html_e('Order #', 'theme-woopm-child'); ?>
			<span class="trimvia-track-order-summary__highlight"><?php echo esc_html($order_number); ?></span>
			<?php esc_html_e(' was placed on ', 'theme-woopm-child'); ?>
			<span class="trimvia-track-order-summary__highlight"><?php echo esc_html($order_date); ?></span>
			<?php esc_html_e(' and is currently ', 'theme-woopm-child'); ?>
			<span class="trimvia-track-order-summary__status <?php echo esc_attr($status_class); ?>">
				<?php echo esc_html($status_name); ?>
			</span>.
		</p>
	</div>

	<div class="trimvia-track-order-summary__mobile">
		<h2 class="trimvia-track-order-summary__title"><?php esc_html_e('My Order', 'theme-woopm-child'); ?></h2>
		<div class="trimvia-track-order-summary__card">
			<div class="trimvia-track-order-summary__row">
				<span class="trimvia-track-order-summary__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
						<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
						<polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
						<line x1="12" y1="22.08" x2="12" y2="12"/>
					</svg>
				</span>
				<div class="trimvia-track-order-summary__meta">
					<span class="trimvia-track-order-summary__label"><?php esc_html_e('Order', 'theme-woopm-child'); ?></span>
					<span class="trimvia-track-order-summary__value">#<?php echo esc_html($order_number); ?></span>
				</div>
			</div>

			<div class="trimvia-track-order-summary__row">
				<span class="trimvia-track-order-summary__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
						<line x1="16" y1="2" x2="16" y2="6"/>
						<line x1="8" y1="2" x2="8" y2="6"/>
						<line x1="3" y1="10" x2="21" y2="10"/>
					</svg>
				</span>
				<div class="trimvia-track-order-summary__meta">
					<span class="trimvia-track-order-summary__label"><?php esc_html_e('Placed on', 'theme-woopm-child'); ?></span>
					<span class="trimvia-track-order-summary__value"><?php echo esc_html($order_date_mobile); ?></span>
				</div>
			</div>

			<div class="trimvia-track-order-summary__status-row">
				<span class="trimvia-track-order-summary__label"><?php esc_html_e('Status', 'theme-woopm-child'); ?></span>
				<span class="trimvia-track-order-summary__status <?php echo esc_attr($status_class); ?>">
					<span class="trimvia-track-order-summary__status-dot" aria-hidden="true"></span>
					<?php echo esc_html($status_name); ?>
				</span>
			</div>
		</div>
	</div>
</div>

<?php if ($notes) : ?>
	<section class="trimvia-view-order-card trimvia-view-order-updates trimvia-track-order-updates">
		<h2><?php esc_html_e('Order updates', 'woocommerce'); ?></h2>
		<ol class="woocommerce-OrderUpdates commentlist notes trimvia-order-updates">
			<?php foreach ($notes as $note) : ?>
				<li class="woocommerce-OrderUpdate comment note">
					<div class="woocommerce-OrderUpdate-inner comment_container">
						<div class="woocommerce-OrderUpdate-text comment-text">
							<p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html(date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($note->comment_date))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							<div class="woocommerce-OrderUpdate-description description">
								<?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?>
							</div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>
<?php endif; ?>

<?php do_action('woocommerce_view_order', $order->get_id()); ?>
