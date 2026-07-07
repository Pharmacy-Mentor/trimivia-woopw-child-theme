<?php
/**
 * Order details — Trimvia thank you / view order layout.
 *
 * @package WooCommerce\Templates
 * @version 9.0.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (!$order) {
	return;
}

$order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$downloads             = $order->get_downloadable_items();
$show_customer_details = $order->get_user_id() === get_current_user_id();
$patient_consultation  = $order->get_meta('_cflp_form_data');
$patient_consultation_data = array();

if ($show_downloads) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}

if ($patient_consultation) {
	foreach ($patient_consultation as $form_id => $form_data) {
		$form_data     = apply_filters('woopw_consultation_form_entry', $form_data);
		$condition_key = isset($form_data['condition_id']) ? $form_data['condition_id'] : (isset($form_data['condition_slug']) ? $form_data['condition_slug'] : false);
		$condition     = false;

		if (is_string($condition_key)) {
			$condition = get_term_by('slug', $condition_key, 'condition');
		}
		if (is_numeric($condition_key)) {
			$condition = get_term_by('id', $condition_key, 'condition');
		}
		if (isset($condition) && $condition) {
			$patient_consultation_data[ $condition->term_id ]['name']        = $condition->name;
			$patient_consultation_data[ $condition->term_id ]['attempts']    = $form_data['form_data'];
			$patient_consultation_data[ $condition->term_id ]['form_groups'] = $form_data['form_groups'] ?? array();
		}
	}
}
?>
<section class="woocommerce-order-details trimvia-view-order-card trimvia-order-received-details">
	<?php do_action('woocommerce_order_details_before_order_table', $order); ?>

	<div class="order-detail-heading trimvia-order-received-details__head">
		<h2 class="woocommerce-order-details__title"><?php esc_html_e('Order details', 'woocommerce'); ?></h2>
		<?php if (!empty($patient_consultation_data)) : ?>
			<a
				href="#"
				class="btn-accent trimvia-view-consultation-btn"
				data-toggle="modal"
				data-target="#consultation-patient-modal"
				role="button"
			>
				<?php esc_html_e('View your Consultation', 'theme-woopm-child'); ?>
			</a>
		<?php endif; ?>
	</div>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action('woocommerce_order_details_before_order_table_items', $order);

			foreach ($order_items as $item_id => $item) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action('woocommerce_order_details_after_order_table_items', $order);
			?>
		</tbody>

		<tfoot>
			<?php foreach ($order->get_order_item_totals() as $key => $total) : ?>
				<tr>
					<th scope="row"><?php echo esc_html($total['label']); ?></th>
					<td><?php echo wp_kses_post($total['value']); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ($order->get_customer_note()) : ?>
				<tr>
					<th><?php esc_html_e('Note:', 'woocommerce'); ?></th>
					<td><?php echo wp_kses(nl2br(wptexturize($order->get_customer_note())), array()); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action('woocommerce_order_details_after_order_table', $order); ?>
</section>

<?php if (!empty($patient_consultation_data)) : ?>
<div
	class="modal fade trimvia-consultation-modal"
	id="consultation-patient-modal"
	tabindex="-1"
	role="dialog"
	aria-hidden="true"
	aria-labelledby="consultation-patient-modal-title"
>
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2 id="consultation-patient-modal-title" class="trimvia-consultation-modal__title"><?php esc_html_e('Your consultation', 'theme-woopm-child'); ?></h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'theme-woopm-child'); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="consultation-content">
					<?php
					foreach ($patient_consultation_data as $consultation_data) {
						wc_get_template(
							'myaccount/patient-order-consultation.php',
							array(
								'consultation_data' => $consultation_data,
								'ordered_by'        => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
								'ordered_on'        => $order->get_date_created()->date('F d, Y h:i A'),
								'show_q_desc'       => false,
								'order_number'      => $order->get_id(),
								'order'             => $order,
							),
							'',
							function_exists('default_template_path') ? default_template_path() : ''
						);
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?php
do_action('woocommerce_after_order_details', $order);

if ($show_customer_details) {
	wc_get_template(
		'order/order-details-customer.php',
		array('order' => $order)
	);
}
