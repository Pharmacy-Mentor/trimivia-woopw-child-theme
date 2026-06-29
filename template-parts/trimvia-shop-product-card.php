<?php
/**
 * Single product card — same markup as the main Shop page (shop-grid).
 *
 * @package theme-woopm-child
 *
 * @param array $args {
 *     @type WC_Product $product Product instance.
 * }
 */

if (!defined('ABSPATH')) {
	exit;
}

$shop_product = null;
if ( isset( $args ) && is_array( $args ) && isset( $args['product'] ) && $args['product'] instanceof WC_Product ) {
	$shop_product = $args['product'];
} elseif ( isset( $product ) && $product instanceof WC_Product ) {
	$shop_product = $product;
}
if ( ! $shop_product instanceof WC_Product || ! $shop_product->is_visible() ) {
	return;
}

$product_id = $shop_product->get_id();
$product_title = $shop_product->get_name();
$product_link = get_permalink($product_id);
$product_categories = wc_get_product_category_list($product_id, ', ', '', '');
$product_short_description_raw = wp_strip_all_tags((string) $shop_product->get_short_description());
$product_description_raw = wp_strip_all_tags((string) $shop_product->get_description());
$product_subtitle = '' !== $product_short_description_raw ? wp_trim_words($product_short_description_raw, 8, '') : '';
$product_description = '' !== $product_short_description_raw ? wp_trim_words($product_short_description_raw, 28, '...') : wp_trim_words($product_description_raw, 28, '...');
$product_image_id = $shop_product->get_image_id();

$product_feature_points = array();
if ('' !== $product_short_description_raw) {
	$product_sentence_split = preg_split('/[\r\n]+|(?<=[\.\!\?])\s+/', $product_short_description_raw);
	if (is_array($product_sentence_split)) {
		foreach ($product_sentence_split as $product_sentence) {
			$product_sentence = trim((string) $product_sentence);
			if ('' === $product_sentence) {
				continue;
			}
			$product_feature_points[] = $product_sentence;
			if (count($product_feature_points) >= 3) {
				break;
			}
		}
	}
}

$product_badge_label = '';
$product_badge_class = '';

if (function_exists('trimvia_product_is_out_of_stock') && trimvia_product_is_out_of_stock($shop_product)) {
	$product_badge_label = __('Out of stock', 'theme-woopm-child');
	$product_badge_class = 'product-badge--out-of-stock';
} elseif ($shop_product->is_featured()) {
	$product_badge_label = __('Most Popular', 'theme-woopm-child');
	$product_badge_class = 'product-badge--popular';
} elseif (false !== strpos(strtolower((string) $product_categories), 'oral')) {
	$product_badge_label = __('Oral', 'theme-woopm-child');
	$product_badge_class = 'product-badge--oral';
} elseif ($shop_product->get_date_created() && (time() - $shop_product->get_date_created()->getTimestamp()) <= MONTH_IN_SECONDS) {
	$product_badge_label = __('New', 'theme-woopm-child');
	$product_badge_class = 'product-badge--new';
}

$product_price_value = (float) $shop_product->get_price();
if ($shop_product->is_type('variable')) {
	$product_price_value = (float) $shop_product->get_variation_price('min', true);
}

$consultation_required = function_exists('trimvia_is_product_consultation_required')
	? trimvia_is_product_consultation_required($shop_product)
	: false;

$loop_button_text = method_exists($shop_product, 'add_to_cart_text')
	? $shop_product->add_to_cart_text()
	: __('Select options', 'woocommerce');
$product_button_text = $loop_button_text;

if ($shop_product->is_type('variable')) {
	$read_more_label = __('Read more', 'woocommerce');
	$select_options_label = __('Select options', 'woocommerce');
	if ($consultation_required) {
		$product_button_text = __('Start Assessment', 'theme-woopm-child');
	} elseif ($loop_button_text === $read_more_label) {
		$product_button_text = $loop_button_text;
	} elseif ($loop_button_text === $select_options_label || 'Select options' === $loop_button_text) {
		$product_button_text = __('View product', 'theme-woopm-child');
	} else {
		$product_button_text = $loop_button_text;
	}
} elseif (!$shop_product->is_type('grouped')) {
	$single_button_text = method_exists($shop_product, 'single_add_to_cart_text')
		? $shop_product->single_add_to_cart_text()
		: '';

	if ('' !== $single_button_text && $single_button_text !== $loop_button_text) {
		$product_button_text = $single_button_text;
	}
}

$product_button_url = method_exists($shop_product, 'add_to_cart_url')
	? $shop_product->add_to_cart_url()
	: $product_link;

if (function_exists('trimvia_get_product_entry_url')) {
	$product_button_url = trimvia_get_product_entry_url($shop_product, $product_button_url);
}

if ($consultation_required) {
	$product_button_text = __('Start Assessment', 'theme-woopm-child');
}
?>
<article class="product-card product-card--linked rv">
	<a class="product-card-hit" href="<?php echo esc_url($product_link); ?>" aria-labelledby="<?php echo esc_attr('product-card-title-' . $product_id); ?>">
		<span class="screen-reader-text">
			<?php
			printf(
				/* translators: %s: product name */
				esc_html__('View %s', 'theme-woopm-child'),
				esc_html($product_title)
			);
			?>
		</span>
	</a>
	<div class="product-img">
		<?php if ('' !== $product_badge_label) : ?>
			<span class="product-badge <?php echo esc_attr($product_badge_class); ?>"><?php echo esc_html($product_badge_label); ?></span>
		<?php endif; ?>
		<?php if ($product_image_id) : ?>
			<div class="product-img-media"><?php echo wp_get_attachment_image($product_image_id, 'large', false, array('loading' => 'lazy')); ?></div>
		<?php else : ?>
			<div class="product-img-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
			</div>
		<?php endif; ?>
	</div>
	<div class="product-body">
		<?php if ('' !== $product_categories) : ?>
			<div class="product-type"><?php echo esc_html(wp_strip_all_tags($product_categories)); ?></div>
		<?php endif; ?>
		<h3 id="<?php echo esc_attr('product-card-title-' . $product_id); ?>"><?php echo esc_html($product_title); ?></h3>
		<?php if ('' !== $product_subtitle) : ?>
			<div class="product-subtitle"><?php echo esc_html($product_subtitle); ?></div>
		<?php endif; ?>
		<?php if ('' !== $product_description) : ?>
			<p><?php echo esc_html($product_description); ?></p>
		<?php endif; ?>
		<?php if (!empty($product_feature_points)) : ?>
			<div class="product-features">
				<?php foreach ($product_feature_points as $product_feature_point) : ?>
					<div class="product-feature"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12" /></svg> <?php echo esc_html($product_feature_point); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="product-footer">
			<div class="product-price">
				<span class="price-from"><?php esc_html_e('From', 'theme-woopm-child'); ?></span>
				<span class="price-value"><?php echo wp_kses_post(wc_price($product_price_value)); ?></span>
			</div>
			<a href="<?php echo esc_url($product_button_url); ?>" class="btn-shop" aria-labelledby="<?php echo esc_attr('product-card-title-' . $product_id); ?>">
				<?php echo esc_html($product_button_text); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
			</a>
		</div>
	</div>
</article>
