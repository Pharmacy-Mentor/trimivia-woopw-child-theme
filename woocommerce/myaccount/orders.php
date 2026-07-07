<?php
/**
 * Orders — Trimvia account layout (WooPW reorder support).
 *
 * WooPW registers re-order actions as a nested array per condition. The default
 * WooCommerce template only supports flat url/name pairs, so reorder buttons
 * are omitted unless this template handles the re-order key explicitly.
 *
 * @see Plugin/woopw-1.8.2/woocommerce/myaccount/orders.php
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);

if ($has_orders) :
	?>
	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table personal-orders-table">
		<thead>
			<tr>
				<?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>">
						<span class="nobr"><?php echo esc_html($column_name); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ($customer_orders->orders as $customer_order) {
				$order      = wc_get_order($customer_order); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
					<?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
							<?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
								<?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>

							<?php elseif ('order-number' === $column_id) : ?>
								<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="trimvia-order-number-link">
									<?php echo esc_html(_x('#', 'hash before order number', 'woocommerce') . $order->get_order_number()); ?>
								</a>

							<?php elseif ('order-date' === $column_id) : ?>
								<time class="trimvia-order-date" datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>">
									<?php echo esc_html(wc_format_datetime($order->get_date_created())); ?>
								</time>

							<?php elseif ('order-status' === $column_id) : ?>
								<span class="trimvia-order-status trimvia-order-status--<?php echo esc_attr(sanitize_html_class($order->get_status())); ?>">
									<?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
								</span>

							<?php elseif ('order-total' === $column_id) : ?>
								<span class="trimvia-order-total">
									<span class="trimvia-order-total__amount"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
									<span class="trimvia-order-total__count">
										<?php
										printf(
											/* translators: %s: total order items */
											esc_html__('for %s', 'theme-woopm-child'),
											esc_html(
												sprintf(
													/* translators: %s: total order items */
													_n('%s item', '%s items', $item_count, 'woocommerce'),
													number_format_i18n($item_count)
												)
											)
										);
										?>
									</span>
								</span>

							<?php elseif ('order-actions' === $column_id) : ?>
								<?php
								$actions = wc_get_account_orders_actions($order);

								if (function_exists('trimvia_build_account_order_reorder_actions')) {
									$reorder_actions = trimvia_build_account_order_reorder_actions($order);
									if (!empty($reorder_actions)) {
										$actions['re-order'] = $reorder_actions;
									}
								}

								if (!empty($actions)) {
									foreach ($actions as $key => $action) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
										if ('re-order' === $key && is_array($action)) {
											foreach ($action as $reorder_action) {
												if (empty($reorder_action['url']) || empty($reorder_action['name'])) {
													continue;
												}
												printf(
													'<a href="%1$s" class="%2$s button theme-btn-outline %3$s">%4$s</a>',
													esc_url($reorder_action['url']),
													esc_attr($wp_button_class),
													esc_attr(sanitize_html_class($key)),
													esc_html($reorder_action['name'])
												);
											}
											continue;
										}

										if (empty($action['url']) || empty($action['name'])) {
											continue;
										}

										printf(
											'<a href="%1$s" class="%2$s woocommerce-button button %3$s">%4$s</a>',
											esc_url($action['url']),
											esc_attr($wp_button_class),
											esc_attr(sanitize_html_class($key)),
											esc_html($action['name'])
										);
									}
								}
								?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action('woocommerce_before_account_orders_pagination'); ?>

	<?php if (1 < $customer_orders->max_num_pages) : ?>
		<nav class="trimvia-account-orders-pagination woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination" aria-label="<?php esc_attr_e('Orders pagination', 'theme-woopm-child'); ?>">
			<?php if (1 !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">
					<?php esc_html_e('Previous', 'woocommerce'); ?>
				</a>
			<?php endif; ?>

			<span class="trimvia-account-orders-pagination__status">
				<?php
				printf(
					/* translators: 1: current page 2: total pages */
					esc_html__('Page %1$s of %2$s', 'theme-woopm-child'),
					number_format_i18n($current_page),
					number_format_i18n($customer_orders->max_num_pages)
				);
				?>
			</span>

			<?php if ((int) $customer_orders->max_num_pages !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">
					<?php esc_html_e('Next', 'woocommerce'); ?>
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

<?php else : ?>

	<?php
	wc_print_notice(
		esc_html__('No order has been made yet.', 'woocommerce') . ' <a class="woocommerce-Button button' . esc_attr($wp_button_class) . '" href="' . esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))) . '">' . esc_html__('Browse products', 'woocommerce') . '</a>',
		'notice'
	);
	?>

<?php endif; ?>

<?php
do_action('woocommerce_after_account_orders', $has_orders);
