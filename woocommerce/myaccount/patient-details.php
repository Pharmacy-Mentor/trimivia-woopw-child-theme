<?php
/**
 * Patient details panel for the prescriber review modal.
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$user = get_user_by('id', $user_id);

$first_name      = $user ? get_user_meta($user_id, 'first_name', true) : '';
$last_name       = $user ? get_user_meta($user_id, 'last_name', true) : '';
$email           = $user ? $user->user_email : '';
$phone           = get_user_meta($user_id, 'billing_phone', true);
$patient_dob_raw = get_user_meta($user_id, 'patient_dob', true);
$patient_dob     = '';

if (!empty($patient_dob_raw)) {
	try {
		$date        = new DateTime($patient_dob_raw);
		$patient_dob = $date->format('jS F Y');
	} catch (Exception $exception) {
		$patient_dob = '';
	}
}

$countries      = WC()->countries ? WC()->countries->countries : array();
$country_code   = get_user_meta($user_id, 'billing_country', true);
$address_parts  = array_filter(
	array(
		get_user_meta($user_id, 'billing_address_1', true),
		get_user_meta($user_id, 'billing_address_2', true),
		get_user_meta($user_id, 'billing_city', true),
		get_user_meta($user_id, 'billing_state', true),
		get_user_meta($user_id, 'billing_postcode', true),
		$countries[ $country_code ] ?? '',
	)
);
$full_address   = implode('<br>', array_map('esc_html', $address_parts));
$patient_id_url = function_exists('trimvia_get_patient_id_stream_url') ? trimvia_get_patient_id_stream_url($user_id) : '';
$verified_badge = '' !== $patient_id_url;
?>
<div class="card patient-card mb-4 mt-4">
	<div class="patient-header">
		<div class="patient-title">
			<i class="fa fa-user-circle"></i>
			<strong><?php esc_html_e('Patient Details', 'woopw'); ?></strong>
		</div>

		<?php if ($verified_badge) : ?>
			<span class="verified-badge">
				<i class="fa fa-shield-alt"></i> <?php esc_html_e('Verified', 'woopw'); ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="patient-body">
		<div class="patient-row">
			<span class="label">
				<i class="fa fa-user"></i> <?php esc_html_e('Name', 'woopw'); ?>
			</span>
			<span class="value">
				<?php echo esc_html(trim($first_name . ' ' . $last_name) ?: '-'); ?>
			</span>
		</div>

		<div class="patient-row">
			<span class="label"><?php esc_html_e('# Customer ID', 'woopw'); ?></span>
			<span class="value">
				<?php echo esc_html($user_id ?: '-'); ?>
			</span>
		</div>

		<div class="patient-row">
			<span class="label">
				<i class="fa fa-envelope"></i> <?php esc_html_e('Email', 'woopw'); ?>
			</span>
			<span class="value">
				<?php if ($email) : ?>
					<a href="mailto:<?php echo esc_attr($email); ?>">
						<?php echo esc_html($email); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html('-'); ?>
				<?php endif; ?>
			</span>
		</div>

		<div class="patient-row">
			<span class="label">
				<i class="fa fa-phone"></i> <?php esc_html_e('Phone', 'woopw'); ?>
			</span>
			<span class="value">
				<?php if ($phone) : ?>
					<a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>">
						<?php echo esc_html($phone); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html('-'); ?>
				<?php endif; ?>
			</span>
		</div>

		<?php if (!empty($patient_dob)) : ?>
			<div class="patient-row">
				<span class="label">
					<i class="fa fa-calendar"></i> <?php esc_html_e('Date of Birth', 'woopw'); ?>
				</span>
				<span class="value"><?php echo esc_html($patient_dob); ?></span>
			</div>
		<?php endif; ?>

		<div class="patient-row address">
			<span class="label">
				<i class="fa fa-map-marker"></i> <?php esc_html_e('Address', 'woopw'); ?>
			</span>
			<span class="value">
				<?php echo wp_kses_post($full_address ?: '-'); ?>
			</span>
		</div>

		<hr>

		<div class="pm-id-verification">
			<div class="pm-id-header">
				<strong><?php esc_html_e('ID Verification', 'woopw'); ?></strong>
			</div>

			<div class="pm-id-body">
				<?php if (!empty($patient_id_url)) : ?>
					<a href="<?php echo esc_url($patient_id_url); ?>" class="zoomed-view" target="_blank" rel="noopener">
						<img
							src="<?php echo esc_url($patient_id_url); ?>"
							alt="<?php esc_attr_e('Patient ID', 'woopw'); ?>" />
					</a>
				<?php else : ?>
					<div class="pm-id-placeholder">
						<span><?php esc_html_e('No ID uploaded', 'woopw'); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
