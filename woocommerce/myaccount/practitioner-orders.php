<?php

/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.7.0
 */

defined('ABSPATH') || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */

$review_table_columns = array(
	'order-number'                => esc_html__('Order No.', 'woopw'),
	'order-date'                  => esc_html__('Date Placed', 'woopw'),
	'order-practitioner-details'  => esc_html__('Patient Details', 'woopw'),
	'order-risk'                  => esc_html__('Order Risk', 'woopw'),
	'order-approve-decline'       => esc_html__('Approve / Decline', 'woopw'),
);

$completed_table_columns = array(
	'order-number'                => esc_html__('Order No.', 'woopw'),
	'order-date'                  => esc_html__('Date Placed', 'woopw'),
	'order-practitioner-details'  => esc_html__('Patient Details', 'woopw'),
	'order-approve-decline'       => esc_html__('Approve / Decline', 'woopw'),
	'order-prescription-details'  => esc_html__('Details', 'woopw'),
);


$current_page_url = wc_get_account_endpoint_url('practitioner-orders');

$limit = 10;

$current_user = wp_get_current_user();

$completed_orders_pagination_arg = 'completed_paged';
$under_review_orders_pagination_arg = 'pending_paged';
$completed_orders_current_page = (isset($_GET[$completed_orders_pagination_arg]) && $_GET[$completed_orders_pagination_arg]) ? absint($_GET[$completed_orders_pagination_arg]) : 1;
$under_review_orders_current_page = (isset($_GET[$under_review_orders_pagination_arg]) && $_GET[$under_review_orders_pagination_arg]) ? absint($_GET[$under_review_orders_pagination_arg]) : 1;

$completed_orders_query = get_practitioner_orders_completed($current_user, $limit, $completed_orders_current_page);
$completed_orders = $completed_orders_query->orders;
$completed_orders_total_pages = $completed_orders_query->max_num_pages;

$under_review_orders_query = get_practitioner_orders_under_review($current_user, $limit, $under_review_orders_current_page);
$under_review_orders = $under_review_orders_query->orders;
$under_review_orders_total_pages = $under_review_orders_query->max_num_pages;

?>
<div class="woo-practitioner-orders-wrapper">
	<div class="row">
		<div class="col-lg-12 col-md-12 mb-4">
			<div class="practitioner-order-under-review-wrapper">
				<div class="practitioner-section-content">
					<h2><?= __('Awaiting Prescriber Approval ', 'woocommerce') ?></h2>
					<p><?= __('When you approve an order as a registered prescriber the prescription will be automatically generated for the order. ', 'woocommerce') ?></p>
				</div>
				<div class="practitioner-order-section">
					<table class="woocommerce-review-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
						<thead>
							<tr>
								<?php foreach ($review_table_columns as $column_id => $column_name): ?>
									<th align="center" class="practitioner-table-header-<?= $column_id ?>"><?= $column_name ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($under_review_orders as $order) {
							?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
									<?php foreach ($review_table_columns as $column_id => $column_name) : ?>
										<td align="center" class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
											<?php if ('order-number' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $review_table_columns[$column_id] ?>:</strong>
												<a class="woo-order-number" href="#">
													<strong><?php echo esc_html(_x('#', 'hash before order number', 'woocommerce') . $order->get_order_number()); ?></strong>
												</a>

											<?php elseif ('order-date' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $review_table_columns[$column_id] ?>:</strong>
												<time class="trimvia-practitioner-order-date" datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>">
													<span class="trimvia-practitioner-order-date__time"><?php echo esc_html($order->get_date_created()->date('H:i')); ?></span>
													<span class="trimvia-practitioner-order-date__day"><?php echo esc_html(wc_format_datetime($order->get_date_created(), 'd/m/Y')); ?></span>
												</time>


											<?php elseif ('order-risk' === $column_id) : ?>
												<?php
												$order_risk = $order->get_meta('_order_risk');
												$risk_options = woopwpm_get_field_type_risk_options(1);
												if (isset($risk_options) && is_array($risk_options) && isset($order_risk) && $order_risk > 0) {
													$option_risk = $option_risk_flag = '';

													$option_risk_index =  $order_risk;
													$option_risk = 'risk-' . $option_risk_index;

													$option_risk_flag =  '<i class="fa-solid fa-flag ' . $option_risk . '" ></i> <span class="risk-label"> ' . $risk_options[$option_risk_index] . '</span>';

													echo "<div class='order-list-risk'>";
													echo $option_risk_flag;
													echo "</div>";
												}

												?>

											<?php elseif ('order-practitioner-details' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $review_table_columns[$column_id] ?>:</strong>
												<span class="order-customer"><?= $order->get_billing_first_name() ?> <?= $order->get_billing_last_name() ?></span>

											<?php elseif ('order-approve-decline' === $column_id) : ?>
												<div class="order-action-wrapper">
													<strong class="mobile-only-table-heading"><?= $review_table_columns[$column_id] ?>:</strong>
													<a class="theme-btn-secondary mx-2 practitioner-order-action" href="#" data-action_type="view-prescriptions" data-order_id="<?= $order->get_order_number() ?>">Review & Sign</a>
												</div>
											<?php endif; ?>

										</td>
									<?php endforeach; ?>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
					<?php if ($under_review_orders_total_pages > 1) {
						echo '<nav class="pagination d-flex gap-2 align-items-center">';
						echo paginate_links([
							'base'      => $current_page_url . '%_%',
							'format' 	=> '?' . $under_review_orders_pagination_arg . '=%#%',
							'current'   => $under_review_orders_current_page, // Current page number
							'total'     => $under_review_orders_total_pages, // Total number of pages
							'prev_text' => __('Previous'), // Text for previous page link
							'next_text' => __('Next'), // Text for next page link
						]);
						echo '</nav>';
					} ?>
				</div>
			</div>
		</div>
		<div class="col-lg-12 col-md-12 mb-4">
			<div class="practitioner-order-completed-wrapper">
				<div class="practitioner-section-content">
					<h2><?= __('Prescriber Reviewed', 'woocommerce') ?></h2>
					<p><?= __('Once an order has been approved or declined it will appear in your reviewed list. ', 'woocommerce') ?></p>
				</div>
				<div class="practitioner-order-section">
					<table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
						<thead>
							<tr>
								<?php foreach ($completed_table_columns as $column_id => $column_name): ?>
									<th class="practitioner-table-header-<?= $column_id ?>"><?= $column_name ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($completed_orders as $order) {
								$order_status = $order->get_status();
								if ('prescribe-decline' == $order_status) {
									$cssClass = 'danger';
									$text = 'Declined';
								}
								if ('prescribe-approve' == $order_status) {
									$cssClass = 'success';
									$text = 'Approved';
								}
								if ('partial-approved' == $order_status) {
									$cssClass = 'partial';
									$text = 'Partial Approved';
								}
								if ('completed' == $order_status) {
									$cssClass = 'success';
									$text = 'Dispensed';
								}
								if ('cancelled' == $order_status) {
									$cssClass = 'danger';
									$text = 'Declined';
								}
							?>
								<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
									<?php foreach ($completed_table_columns as $column_id => $column_name) : ?>
										<td align="center" class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
											<?php if ('order-number' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $completed_table_columns[$column_id] ?>:</strong>
												<a class="woo-order-number" href="#">
													<?php echo esc_html(_x('#', 'hash before order number', 'woocommerce') . $order->get_order_number()); ?>
												</a>

											<?php elseif ('order-date' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $completed_table_columns[$column_id] ?>:</strong>
												<time class="trimvia-practitioner-order-date" datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>">
													<span class="trimvia-practitioner-order-date__time"><?php echo esc_html($order->get_date_created()->date('H:i')); ?></span>
													<span class="trimvia-practitioner-order-date__day"><?php echo esc_html(wc_format_datetime($order->get_date_created(), 'd/m/Y')); ?></span>
												</time>
											<?php elseif ('order-practitioner-details' === $column_id) : ?>
												<strong class="mobile-only-table-heading"><?= $completed_table_columns[$column_id] ?>:</strong>
												<span class="order-customer"><?= $order->get_billing_first_name() ?> <?= $order->get_billing_last_name() ?></span>
											<?php elseif ('order-approve-decline' === $column_id) : ?>
												<div class="order-status-wrapper">
													<strong class="mobile-only-table-heading"><?= $completed_table_columns[$column_id] ?>:</strong>
													<span class="order-status <?= isset($cssClass) ? $cssClass : '' ?>"><?= $text ?: 'N/A' ?></span>
												</div>
											<?php elseif ('order-prescription-details' === $column_id) : ?>
												<div class="order-notes-action-wrapper">
													<strong class="mobile-only-table-heading"><?= $completed_table_columns[$column_id] ?>:</strong>
													<a class="theme-btn-secondary mx-2 practitioner-order-action" href="#" data-action_type="view-prescriptions" data-order_id="<?= $order->get_order_number() ?>">Prescription Reviewed</a>

												</div>
											<?php endif; ?>

										</td>
									<?php endforeach; ?>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
					<?php if ($completed_orders_total_pages > 1) {
						echo '<nav class="pagination d-flex gap-2 align-items-center">';
						echo paginate_links([
							'base'      => $current_page_url . '%_%',
							'format' 	=> '?' . $completed_orders_pagination_arg . '=%#%',
							'current'   => $completed_orders_current_page, // Current page number
							'total'     => $completed_orders_total_pages, // Total number of pages
							'prev_text' => __('Previous'), // Text for previous page link
							'next_text' => __('Next'), // Text for next page link
						]);
						echo '</nav>';
					} ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="practitioner-order-modal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="load-me">
					<i class="fa fa-fw fa-spinner fa-spin loader"></i>
				</div>
				<div class="practitioner-order-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="practitioner-order-prescription-modal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="load-me">
					<i class="fa fa-fw fa-spinner fa-spin loader"></i>
				</div>
				<div class="practitioner-order-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="prescription-extra-content-modal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="load-me">
					<i class="fa fa-fw fa-spinner fa-spin loader"></i>
				</div>
				<div class="modal-content-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="order-status-template-decline">
	<div class="template-wrapper">
		<h4>Are you sure that you want to decline this order?</h4>
		<p>Once order has been declined, all prescriptions associated with this order will be declined as well, and it's status will be changed, where refund for this order will be handled by admin manually.</p>

		<button type="button" class="btn theme-btn-secondary order-status-action mr-3 my-3" data-action_type="%%action_type%%" data-order_id="%%order_number%%">Yes, Decline this order! <i class="fa fa-fw fa-spinner fa-spin loader" style="display: none;"></i></button>
		<button type="button" class="btn theme-btn-secondary mr-3 dismiss-modal my-3" data-dismiss="modal">Cancel</button>
	</div>
</script>