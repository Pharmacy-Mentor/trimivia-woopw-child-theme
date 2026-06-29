<?php
/**
 * View Order — Trimvia account layout.
 *
 * @package WooCommerce\Templates
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

$notes          = $order->get_customer_order_notes();
$order_status   = $order->get_status();
$user_id        = get_current_user_id();
$patient_images = $order->get_meta('_patient_images');
$status_name    = wc_get_order_status_name($order_status);
$status_class   = 'order-status--default';

if (in_array($order_status, array('processing', 'pre-screen', 'on-hold', 'pending'), true)) {
	$status_class = 'order-status--review';
} elseif (in_array($order_status, array('completed', 'dispatched'), true)) {
	$status_class = 'order-status--dispatched';
} elseif (in_array($order_status, array('cancelled', 'failed', 'refunded'), true)) {
	$status_class = 'order-status--cancelled';
}

$upload_fields = array(
	array(
		'name'  => 'patient_id_image',
		'id'    => 'patient_image',
		'label' => __('ID image', 'woopw'),
		'hint'  => __('A clear photo of your photo ID for identity verification.', 'theme-woopm-child'),
	),
	array(
		'name'  => 'patient_scales_image',
		'id'    => 'patient_scales_image',
		'label' => __('Image on scales', 'woopw'),
		'hint'  => __('A full-body photo standing on scales, showing your current weight.', 'theme-woopm-child'),
	),
	array(
		'name'  => 'patient_mirror_image',
		'id'    => 'patient_mirror_image',
		'label' => __('Image taken in front of a mirror', 'woopw'),
		'hint'  => __('A front-facing mirror photo to help our clinical team assess your progress.', 'theme-woopm-child'),
	),
);
?>

<div class="trimvia-view-order">
	<div class="trimvia-view-order-status">
		<div class="trimvia-view-order-status__copy">
			<span class="trimvia-view-order-status__eyebrow"><?php esc_html_e('Order summary', 'theme-woopm-child'); ?></span>
			<p>
				<?php
				printf(
					/* translators: 1: order number 2: order date */
					esc_html__('Order #%1$s was placed on %2$s.', 'theme-woopm-child'),
					'<strong>' . esc_html($order->get_order_number()) . '</strong>',
					'<strong>' . esc_html(wc_format_datetime($order->get_date_created())) . '</strong>'
				);
				?>
			</p>
		</div>
		<span class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_name); ?></span>
	</div>

	<?php if ($notes) : ?>
		<section class="trimvia-view-order-card trimvia-view-order-updates">
			<h2><?php esc_html_e('Order updates', 'woocommerce'); ?></h2>
			<ol class="woocommerce-OrderUpdates commentlist notes trimvia-order-updates">
				<?php foreach ($notes as $note) : ?>
					<li class="woocommerce-OrderUpdate comment note">
						<div class="woocommerce-OrderUpdate-inner comment_container">
							<div class="woocommerce-OrderUpdate-text comment-text">
								<p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html(date_i18n(esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'), strtotime($note->comment_date))); ?></p>
								<div class="woocommerce-OrderUpdate-description description">
									<?php echo wpautop(wptexturize($note->comment_content)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ($patient_images) : ?>
		<section class="trimvia-view-order-card trimvia-view-order-gallery">
			<div class="trimvia-view-order-card__head">
				<h2><?php esc_html_e('Uploaded images', 'woopw'); ?></h2>
				<p><?php esc_html_e('Images already submitted for this order.', 'theme-woopm-child'); ?></p>
			</div>
			<div class="woocommerce-order-images trimvia-upload-gallery">
				<?php foreach ($patient_images as $image) : ?>
					<?php
					$finfo = new finfo(FILEINFO_MIME_TYPE);
					$type  = $finfo->file($image['path']);
					if (!in_array($type, array('image/png', 'image/jpeg', 'image/jpg', 'image/webp'), true)) {
						continue;
					}
					$data_base64 = 'data:' . $type . ';base64,' . base64_encode(file_get_contents($image['path'])); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					$image_label = '';
					if (!empty($image['field_name'])) {
						$image_label = ucwords(str_replace('_', ' ', $image['field_name']));
					}
					?>
					<figure class="trimvia-upload-gallery__item">
						<?php if ('pre-screen' === $order_status || 'processing' === $order_status) : ?>
							<div class="trimvia-upload-gallery__loading disabled d-none" aria-hidden="true"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i></div>
							<a href="#" class="delete-img-btn trimvia-upload-gallery__delete" aria-label="<?php esc_attr_e('Delete this image', 'woopw'); ?>">
						<?php endif; ?>
						<div class="trimvia-upload-gallery__media">
							<img class="img-squared" src="<?php echo esc_attr($data_base64); ?>" alt="<?php echo esc_attr($image_label ?: $image['file']); ?>" data-title="<?php echo esc_attr($image['file']); ?>" />
						</div>
						<?php if ($image_label) : ?>
							<figcaption><?php echo esc_html($image_label); ?></figcaption>
						<?php endif; ?>
						<?php if ('pre-screen' === $order_status || 'processing' === $order_status) : ?>
							<i class="fa-solid fa-xmark close-btn trimvia-upload-gallery__close" title="<?php esc_attr_e('Delete this image', 'woopw'); ?>" aria-hidden="true"></i>
							</a>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ('pre-screen' === $order_status || 'processing' === $order_status) : ?>
		<section class="trimvia-view-order-card trimvia-view-order-uploads woocommerce-order-uploads">
			<div class="trimvia-view-order-card__head">
				<div class="trimvia-view-order-card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
				</div>
				<div>
					<h2 class="woocommerce-order-uploads__title"><?php esc_html_e('Submit your images', 'woopw'); ?></h2>
					<p><?php esc_html_e('Upload the three verification photos below so our clinical team can review your order.', 'theme-woopm-child'); ?></p>
				</div>
			</div>

			<form id="pre-screen-images" class="trimvia-upload-form" enctype="multipart/form-data">
				<?php wp_nonce_field('patient-upload-nonce_' . $user_id); ?>
				<input type="hidden" id="_order_id" name="_order_id" value="<?php echo esc_attr($order->get_order_number()); ?>" />

				<div class="trimvia-upload-form__grid">
					<?php foreach ($upload_fields as $field) : ?>
						<div class="trimvia-upload-field">
							<label class="trimvia-upload-field__label" for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
							<p class="trimvia-upload-field__hint"><?php echo esc_html($field['hint']); ?></p>
							<div class="trimvia-upload-field__control">
								<input
									name="<?php echo esc_attr($field['name']); ?>"
									class="woopw-form-control form-input trimvia-upload-field__input"
									type="file"
									id="<?php echo esc_attr($field['id']); ?>"
									accept="image/*"
								/>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="trimvia-upload-form__actions">
					<button class="image-upload btn-accent" type="submit"><?php esc_html_e('Submit images', 'theme-woopm-child'); ?></button>
					<p class="trimvia-upload-form__note"><?php esc_html_e('Accepted formats: JPG, PNG, WEBP. Images are stored securely for clinical review only.', 'theme-woopm-child'); ?></p>
				</div>
			</form>
		</section>
	<?php endif; ?>

	<div class="trimvia-view-order-details">
		<?php do_action('woocommerce_view_order', $order_id); ?>
	</div>
</div>

<script>
	jQuery(document).ready(function ($) {
		jQuery('#pre-screen-images').on('submit', function (e) {
			e.preventDefault();

			let button = jQuery('button.image-upload');
			let buttonLabel = jQuery(button).html();

			jQuery('button.image-upload').html('<?php echo esc_js(__('Saving...', 'woopw')); ?><i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
			jQuery('button.image-upload').prop('disabled', true);

			const nonce = jQuery('#_wpnonce').val();
			const referrer = jQuery('input[name="_wp_http_referer"]').val();
			const orderID = jQuery('#_order_id').val();
			let formData = new FormData();

			formData.append('_wpnonce', nonce);
			formData.append('_wp_http_referer', referrer);
			formData.append('_order_id', orderID);
			formData.append('action', 'submit_patient_images');

			let filesExist = false;

			jQuery('.woopw-form-control').each(function (i, item) {
				let fileData = jQuery(this).prop('files')[0];

				if (fileData) {
					formData.append(item.getAttribute('name'), fileData);
					filesExist = true;
				}
			});

			if (!filesExist) {
				setTimeout(function () {
					jQuery('button.image-upload').html(buttonLabel);
				}, 3000);

				jQuery('button.image-upload').html('<?php echo esc_js(__('No images selected!', 'woopw')); ?>');
				jQuery('button.image-upload').prop('disabled', false);

				return;
			}

			jQuery.ajax({
				url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
				type: 'POST',
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (response) {
					if (response.success) {
						location.reload();
						return;
					}

					setTimeout(function () {
						jQuery('button.image-upload').html(buttonLabel);
					}, 3000);

					jQuery('button.image-upload').html('<?php echo esc_js(__('Failed!', 'woopw')); ?>');
					jQuery('button.image-upload').prop('disabled', false);
				},
				error: function () {
					setTimeout(function () {
						jQuery('button.image-upload').html(buttonLabel);
					}, 3000);

					jQuery('button.image-upload').html('<?php echo esc_js(__('Failed!', 'woopw')); ?>');
					jQuery('button.image-upload').prop('disabled', false);
				}
			});
		});

		jQuery('.delete-img-btn').on('click', function (e) {
			e.preventDefault();
			if (window.confirm('<?php echo esc_js(__('Are you sure you want to delete this image?', 'theme-woopm-child')); ?>')) {
				deleteImage(e);
			}
		});

		function deleteImage(el) {
			let img = jQuery(el.target).data('title');

			if (!img) {
				img = jQuery(el.currentTarget).find('img').data('title');
			}

			let errorMessage = '<?php echo esc_js(__('Sorry! This file could not be removed.', 'woopw')); ?>';

			jQuery(el.currentTarget).closest('figure').find('.disabled').removeClass('d-none').addClass('d-grid');

			const nonce = jQuery('#_wpnonce').val();
			const referrer = jQuery('input[name="_wp_http_referer"]').val();
			const orderID = jQuery('#_order_id').val();
			let formData = new FormData();

			formData.append('_wpnonce', nonce);
			formData.append('_wp_http_referer', referrer);
			formData.append('_order_id', orderID);
			formData.append('action', 'delete_patient_images');
			formData.append('image', img);

			jQuery.ajax({
				url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
				type: 'POST',
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				success: function (response) {
					if (response.success) {
						jQuery(el.currentTarget).closest('figure').remove();
						return;
					}

					setTimeout(function () {
						jQuery(el.currentTarget).closest('figure').find('.disabled').html(errorMessage);
					}, 3000);
					jQuery(el.currentTarget).closest('figure').find('.disabled').removeClass('d-grid').addClass('d-none');
				},
				error: function () {
					setTimeout(function () {
						jQuery(el.currentTarget).closest('figure').find('.disabled').html(errorMessage);
					}, 3000);
					jQuery(el.currentTarget).closest('figure').find('.disabled').removeClass('d-grid').addClass('d-none');
				}
			});
		}
	});
</script>
