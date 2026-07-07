<?php
/**
 * Patient order consultation display.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$show_desc = isset($show_q_desc) ? (bool) $show_q_desc : true;
$is_prescriber = 0;
$risk_options  = array();

if (is_user_logged_in()) {
	$current_user = wp_get_current_user();

	if (
		in_array('prescriber', (array) $current_user->roles, true)
		|| in_array('administrator', (array) $current_user->roles, true)
		|| current_user_can('manage_woocommerce')
	) {
		$is_prescriber = 1;
	}

	if (function_exists('woopwpm_get_field_type_risk_options')) {
		$risk_options = woopwpm_get_field_type_risk_options(1);
	}
}

$order_id  = isset($order_number) ? absint($order_number) : 0;
$order_obj = isset($order) && $order instanceof WC_Order ? $order : null;
if (!$order_obj && $order_id > 0 && function_exists('wc_get_order')) {
	$order_obj = wc_get_order($order_id);
}
if ($order_obj instanceof WC_Order && $order_id < 1) {
	$order_id = (int) $order_obj->get_id();
}
$order_key = $order_obj instanceof WC_Order ? $order_obj->get_order_key() : '';

$form_groups  = is_array($consultation_data['form_groups'] ?? null) ? $consultation_data['form_groups'] : array();
$attempts_map = is_array($consultation_data['attempts'] ?? null) ? $consultation_data['attempts'] : array();
$use_groups   = !empty($form_groups);
$render_items = array();

if ($use_groups) {
	foreach ($form_groups as $group) {
		if (!is_array($group) || (isset($group['group_status']) && 'hidden' === $group['group_status'])) {
			continue;
		}

		$has_score       = isset($group['score']) && null !== $group['score'] && !empty($group['message']);
		$patient_can_see = ($group['score_show_patient'] ?? '0') === '1';
		$show_score      = $has_score && (1 === $is_prescriber || $patient_can_see);

		$group_item = array(
			'type'         => 'group_header',
			'title'        => $group['title'] ?? '',
			'highest_risk' => $group['highest_risk'] ?? null,
		);

		if ($show_score) {
			$group_item['score']   = $group['score'];
			$group_item['message'] = $group['message'];
		}

		$render_items[] = $group_item;

		$field_names = is_array($group['field_names'] ?? null) ? $group['field_names'] : array();
		foreach ($field_names as $field_name) {
			if (isset($attempts_map[ $field_name ])) {
				$render_items[] = array(
					'type' => 'field',
					'data' => $attempts_map[ $field_name ],
				);
			}
		}
	}
} else {
	foreach ($attempts_map as $data) {
		$render_items[] = array(
			'type' => 'field',
			'data' => $data,
		);
	}
}

?>

<h4 class="cons-title">
	<?php
	printf(
		/* translators: %s: consultation name */
		esc_html__('Consultation for: %s', 'woopw'),
		esc_html($consultation_data['name'] ?? '')
	);
	?>
</h4>
<div class="">
	<p><strong><?php esc_html_e('Completed By:', 'woopw'); ?></strong> <?php echo esc_html($ordered_by ?? ''); ?></p>
	<p class="cons-completedby"><strong><?php esc_html_e('On:', 'woopw'); ?></strong> <?php echo esc_html($ordered_on ?? ''); ?></p>
</div>

<div class="prescription-patient-data patient-consultation">
	<?php $woopw_in_group = false; ?>
	<?php foreach ($render_items as $item) : ?>
		<?php if ('group_header' === $item['type']) : ?>
			<?php if ($woopw_in_group) : ?>
				</div>
			<?php endif; ?>
			<div class="woopw-consultation-group">
			<?php $woopw_in_group = true; ?>
			<div class="patient-group-header">
				<div class="patient-group-title">
					<div class="patient-group-left">
						<span class="patient-group-title-text"><?php echo esc_html($item['title']); ?></span>
						<?php if (isset($item['score']) && null !== $item['score'] && !empty($item['message'])) : ?>
							<div class="patient-group-score-wrap">
								<p class="patient-group-score">
									<strong><?php esc_html_e('Score:', 'woopw'); ?> <?php echo esc_html($item['score']); ?></strong>
									- <?php echo esc_html($item['message']); ?>
								</p>
							</div>
						<?php endif; ?>
					</div>

					<?php
					if (1 === $is_prescriber && !empty($item['highest_risk'])) :
						$group_risk_key = (string) $item['highest_risk'];
						if (isset($risk_options[ $group_risk_key ])) :
							?>
							<span class="patient-group-risk">
								<?php esc_html_e('Group Risk : ', 'woopw'); ?>
								<i class="fa-solid fa-flag risk-<?php echo esc_attr($group_risk_key); ?>"></i>
								<span class="risk-label"><?php echo esc_html($risk_options[ $group_risk_key ]); ?></span>
							</span>
							<?php
						endif;
					endif;
					?>
				</div>
			</div>
			<?php continue; ?>
		<?php endif; ?>

		<?php
		$data = is_array($item['data'] ?? null) ? $item['data'] : array();
		if (empty($data)) {
			continue;
		}

		$field_type = (string) ($data['type'] ?? '');
		$title      = function_exists('woopw_get_consultation_form_question_title')
			? woopw_get_consultation_form_question_title($data, false)
			: esc_html($data['label'] ?? '');

		$option_risk_flag = '';
		if (1 === $is_prescriber && isset($data['option_risk']) && strlen((string) $data['option_risk']) > 0) {
			$option_risk_index = is_string($data['option_risk'])
				? min(explode(',', $data['option_risk']))
				: $data['option_risk'];

			$option_risk_flag = '<i class="fa-solid fa-flag risk-' . esc_attr($option_risk_index) . '"></i><span class="risk-label">' . esc_html($risk_options[ $option_risk_index ] ?? '') . '</span>';
		}
		?>

		<?php if (!in_array($field_type, array('conditional', 'bmi', 'file', 'image'), true) && isset($data['value']) && is_array($data['value'])) : ?>
			<?php
			if (!array_filter($data['value'])) {
				continue;
			}
			?>
			<div class="patient-row">
				<div class="q-label parent" data-type="1">
					<?php if (!empty($title)) : ?>
						<label class="q-field-label">
							<i class="fa fa-check-circle-o"></i>
							<?php echo wp_kses_post($title); ?>
						</label>
					<?php endif; ?>

					<div class="pm-answer-row">
						<div class="user-submission user-sub-detail">
							<?php
							if (in_array($field_type, array('weight', 'height', 'waist'), true)) {
								foreach ($data['value'] as $extension => $value) {
									if ('' !== (string) $value) {
										echo esc_html(ltrim((string) $value, '0')) . '<sup> ' . esc_html($extension) . '</sup> ';
									}
								}
							} else {
								echo esc_html(implode(', ', array_filter($data['value'])));
							}
							?>
						</div>

						<div class="risk-label-wrap">
							<?php echo wp_kses_post($option_risk_flag); ?>
						</div>
					</div>
				</div>
			</div>
			<?php continue; ?>
		<?php endif; ?>

		<?php if (isset($data['value']) && is_array($data['value']) && 'conditional' === $field_type) : ?>
			<?php
			$conditional = $data['value'];
			$answer      = strtolower(trim((string) ($conditional['option'] ?? '')));
			$description = '';

			if (!empty($conditional['conditional']['value'])) {
				$description = is_string($conditional['conditional']['value'])
					? $conditional['conditional']['value']
					: ($conditional['conditional']['value']['value'] ?? '');
			}
			?>
			<div class="patient-row wrap-row">
				<div class="q-label parent" data-type="2">
					<label class="q-field-label">
						<i class="fa fa-check-circle-o"></i>
						<strong><?php echo wp_kses_post($title); ?></strong>
					</label>
					<div class="pm-answer-row">
						<div class="user-sub-detail">
							<?php if ('no' === $answer) : ?>
								<?php esc_html_e('No', 'woopw'); ?>
							<?php elseif ('yes' === $answer) : ?>
								<?php esc_html_e('Yes', 'woopw'); ?>
								<?php if (!empty($description) && $show_desc) : ?>
									<div class="child-description d-block" style="margin-top:6px;">
										<?php echo wp_kses_post(wpautop(stripcslashes($description))); ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="risk-label-wrap">
						<?php echo wp_kses_post($option_risk_flag); ?>
					</div>
				</div>
			</div>
			<?php continue; ?>
		<?php endif; ?>

		<?php if (isset($data['value']) && is_array($data['value']) && 'bmi' === $field_type) : ?>
			<div class="patient-row">
				<div class="q-label parent" data-type="2">
					<label class="q-field-label">
						<i class="fa fa-check-circle-o"></i>
						<strong><?php echo wp_kses_post($title); ?></strong>
					</label>

					<div class="pm-answer-row">
						<div class="user-sub-detail">
							<?php echo esc_html($data['value']['calc_bmi_text'] ?? 'N/A'); ?>
						</div>

						<div class="risk-label-wrap">
							<?php echo wp_kses_post($option_risk_flag); ?>
						</div>
					</div>
				</div>
			</div>
			<?php continue; ?>
		<?php endif; ?>

		<?php if ((in_array($field_type, array('file', 'image'), true) && isset($data['file_meta'])) || !empty($data['value'])) : ?>
			<div class="patient-row">
				<div class="q-label parent" data-type="3">
					<?php if (!empty($data['label'])) : ?>
						<label class="q-field-label">
							<i class="fa fa-check-circle-o"></i>
							<strong><?php echo wp_kses_post($title); ?></strong>
						</label>
					<?php endif; ?>

					<div class="pm-answer-row">
						<div class="user-sub-detail">
							<?php if (in_array($field_type, array('file', 'image'), true) && isset($data['file_meta'])) : ?>
								<div class="pm-file-preview-grid" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start;">
									<?php
									$files = isset($data['file_meta']['files']) && is_array($data['file_meta']['files']) && !empty($data['file_meta']['files'])
										? $data['file_meta']['files']
										: array($data['file_meta']);

									$rendered_upload = false;
									if (function_exists('trimvia_render_consultation_upload_preview')) {
										foreach ($files as $file) {
											if (is_array($file)) {
												$rendered_upload = trimvia_render_consultation_upload_preview($file, (string) ($data['label'] ?? __('Uploaded file', 'woopw')), $order_id, $order_key) || $rendered_upload;
											}
										}
									}

									if (!$rendered_upload && !empty($data['value']) && function_exists('trimvia_render_consultation_upload_preview')) {
										$values = is_array($data['value']) ? $data['value'] : explode(',', (string) $data['value']);
										foreach ($values as $value) {
											$value = trim((string) $value);
											if ('' !== $value) {
												$rendered_upload = trimvia_render_consultation_upload_preview(
													array(
														'path'    => $value,
														'url'     => $value,
														'user_id' => 0,
													),
													(string) ($data['label'] ?? __('Uploaded file', 'woopw')),
													$order_id,
													$order_key
												) || $rendered_upload;
											}
										}
									}
									?>
								</div>
							<?php else : ?>
								<?php
								echo is_array($data['value'])
									? esc_html(stripcslashes(implode(', ', $data['value'])))
									: esc_html(stripcslashes((string) $data['value']));
								?>
							<?php endif; ?>
						</div>

						<div class="risk-label-wrap">
							<?php echo wp_kses_post($option_risk_flag); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ($woopw_in_group) : ?>
		</div>
	<?php endif; ?>
</div>
