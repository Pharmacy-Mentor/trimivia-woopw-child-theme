<?php
/**
 * Single product template aligned with HTML Theme single-product layout.
 *
 * @package theme-woopm-child
 */

defined('ABSPATH') || exit;

global $product;

// Safety guard: prevent duplicate rendering when parent/child template loaders
// both trigger the single-product content template in the same request.
if (!empty($GLOBALS['trimvia_single_product_content_rendered'])) {
	return;
}
$GLOBALS['trimvia_single_product_content_rendered'] = true;

do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$product_id = $product instanceof WC_Product ? (int) $product->get_id() : (int) get_the_ID();

$shop_url = function_exists('wc_get_page_permalink')
	? wc_get_page_permalink('shop')
	: home_url('/shop/');

$product_title = $product instanceof WC_Product ? $product->get_name() : get_the_title($product_id);
$short_description_raw = $product instanceof WC_Product ? (string) $product->get_short_description() : '';
$hero_description = trim(wp_strip_all_tags($short_description_raw));
if ('' === $hero_description) {
	$hero_description = __('Prescription treatment supplied after a clinical suitability assessment by a UK-registered prescriber.', 'theme-woopm-child');
}

$condition_terms = get_the_terms($product_id, 'condition');
$condition_term  = (!empty($condition_terms) && !is_wp_error($condition_terms)) ? reset($condition_terms) : null;
$condition_link  = ($condition_term instanceof WP_Term) ? get_term_link($condition_term) : '';
$condition_slug  = ($condition_term instanceof WP_Term) ? sanitize_title((string) $condition_term->slug) : '';

$category_label = trim(wp_strip_all_tags(wc_get_product_category_list($product_id, ' · ')));
$is_prescription_value = function_exists('get_field') ? strtolower(trim((string) get_field('is_prescription_product', $product_id))) : '';
$is_prescription = in_array($is_prescription_value, array('yes', '1', 'true', 'plines'), true);

$type_parts = array();
if ('' !== $category_label) {
	$type_parts[] = $category_label;
}
if ($is_prescription) {
	$type_parts[] = __('Prescription only', 'theme-woopm-child');
}
$product_type_label = !empty($type_parts) ? implode(' · ', $type_parts) : __('Prescription treatment', 'theme-woopm-child');

$stock_text = ($product instanceof WC_Product && $product->is_in_stock())
	? __('In stock - ships after approval', 'theme-woopm-child')
	: __('Out of stock', 'theme-woopm-child');

$stock_class = ($product instanceof WC_Product && $product->is_in_stock()) ? 'single-product-stock' : 'single-product-stock single-product-stock--out';
$plines_class = ('plines' === $is_prescription_value) ? 'plines-products' : '';
$consultation_url = function_exists('trimvia_get_consultation_url')
	? trimvia_get_consultation_url($condition_slug)
	: home_url('/consultation/');
?>
<section class="page-hero page-hero--single">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--single">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Shop', 'theme-woopm-child'); ?></a>
			<?php if ($condition_term instanceof WP_Term && !is_wp_error($condition_link) && '' !== (string) $condition_link) : ?>
				<span>&rsaquo;</span>
				<a href="<?php echo esc_url($condition_link); ?>"><?php echo esc_html($condition_term->name); ?></a>
			<?php endif; ?>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($product_title); ?></span>
		</div>
		<h1><?php echo esc_html($product_title); ?></h1>
		<p><?php echo esc_html($hero_description); ?></p>
	</div>
</section>

<section class="page-section rv" id="main-content">
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class($plines_class, $product); ?>>
		<div class="container">
			<div class="single-product-layout">
				<div class="single-product-gallery">
					<?php if ($product instanceof WC_Product && $product->is_featured()) : ?>
						<span class="product-badge product-badge--popular"><?php esc_html_e('Most Popular', 'theme-woopm-child'); ?></span>
					<?php endif; ?>
					<div class="single-product-main">
						<?php do_action('woocommerce_before_single_product_summary'); ?>
					</div>
				</div>

				<div class="single-product-summary">
					<div class="product-type"><?php echo esc_html($product_type_label); ?></div>
					<h2 class="single-product-title"><?php echo esc_html($product_title); ?></h2>
					<?php if ('' !== $hero_description) : ?>
						<p class="single-product-tagline"><?php echo esc_html($hero_description); ?></p>
					<?php endif; ?>

					<div class="single-product-meta">
						<span class="single-product-chip">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							<?php esc_html_e('GPhC-regulated supply', 'theme-woopm-child'); ?>
						</span>
						<span class="single-product-chip">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
							<?php esc_html_e('Prescriber reviewed order', 'theme-woopm-child'); ?>
						</span>
						<span class="single-product-chip">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 23 8 23 22 7 22 7 17"></polygon><line x1="5" y1="21" x2="5" y2="11"></line></svg>
							<?php esc_html_e('Tracked delivery', 'theme-woopm-child'); ?>
						</span>
					</div>

					<div class="single-product-price-row">
						<div class="trimvia-single-product-price">
							<?php woocommerce_template_single_price(); ?>
						</div>
						<span class="<?php echo esc_attr($stock_class); ?>"><?php echo esc_html($stock_text); ?></span>
					</div>

					<div class="trimvia-single-product-cart">
						<?php woocommerce_template_single_rating(); ?>
						<?php woocommerce_template_single_add_to_cart(); ?>

						<div class="single-product-secondary-actions trimvia-single-product-actions">
							<a class="btn-shop-outline" href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Back to all treatments', 'theme-woopm-child'); ?></a>
						</div>
					</div>

					<p class="single-product-note"><?php esc_html_e('Supply is subject to a clinical review. Always read the patient information leaflet and follow prescriber instructions.', 'theme-woopm-child'); ?></p>

					<div class="shop-trust single-product-trust-inline">
						<div class="shop-trust-item">
							<div class="shop-trust-icon"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></div>
							<div>
								<h4><?php esc_html_e('Clinical gate', 'theme-woopm-child'); ?></h4>
								<p><?php esc_html_e('Orders are approved only when medically appropriate.', 'theme-woopm-child'); ?></p>
							</div>
						</div>
						<div class="shop-trust-item">
							<div class="shop-trust-icon"><i class="fa-solid fa-box" aria-hidden="true"></i></div>
							<div>
								<h4><?php esc_html_e('Discreet packaging', 'theme-woopm-child'); ?></h4>
								<p><?php esc_html_e('Unbranded outer carton on every shipment.', 'theme-woopm-child'); ?></p>
							</div>
						</div>
					</div>

					<div class="trimvia-single-product-meta-wrap">
						<?php woocommerce_template_single_meta(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$tabs = apply_filters('woocommerce_product_tabs', array());
if (!empty($tabs)) :
	$first_tab_key = array_key_first($tabs);
	?>
	<section class="page-section page-section--alt rv rv-d1">
		<div class="container">
			<div class="single-product-details-head">
				<h2 class="stitle"><?php esc_html_e('Treatment details', 'theme-woopm-child'); ?></h2>
				<?php if ('' !== $hero_description) : ?>
					<p><?php echo esc_html($hero_description); ?></p>
				<?php endif; ?>
			</div>

			<div class="single-product-tabs trimvia-single-product-tabs" role="tablist" aria-label="<?php esc_attr_e('Product information tabs', 'theme-woopm-child'); ?>">
				<?php foreach ($tabs as $tab_key => $tab) : ?>
					<?php
					$panel_id = 'trimvia-tab-' . sanitize_title((string) $tab_key);
					$is_active = ($tab_key === $first_tab_key);
					?>
					<button type="button" class="single-product-tab <?php echo $is_active ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>" data-panel="<?php echo esc_attr($panel_id); ?>">
						<?php echo esc_html((string) ($tab['title'] ?? $tab_key)); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php foreach ($tabs as $tab_key => $tab) : ?>
				<?php
				$panel_id = 'trimvia-tab-' . sanitize_title((string) $tab_key);
				$is_active = ($tab_key === $first_tab_key);
				?>
				<div id="<?php echo esc_attr($panel_id); ?>" class="single-product-panel trimvia-single-product-panel trimvia-single-product-tab-panel article-content <?php echo $is_active ? 'is-active' : ''; ?>" role="tabpanel" <?php echo $is_active ? '' : 'hidden'; ?>>
					<?php
					if (!empty($tab['callback']) && is_callable($tab['callback'])) {
						call_user_func($tab['callback'], $tab_key, $tab);
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<section class="page-section rv rv-d3 trimvia-single-product-related">
	<div class="container">
		<?php
		woocommerce_upsell_display();
		woocommerce_output_related_products();
		?>
	</div>
</section>

<section class="page-section rv rv-d3">
	<div class="container">
		<div class="shop-trust">
			<div class="shop-trust-item">
				<div class="shop-trust-icon"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i></div>
				<h4><?php esc_html_e('GPhC Regulated', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Dispensed by Mayberry Pharmacy', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></div>
				<h4><?php esc_html_e('Prescriber Reviewed', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Every order checked by a UK pharmacist', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
				<h4><?php esc_html_e('Next-Day Delivery', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Discreet, unbranded packaging', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
				<h4><?php esc_html_e('100% Secure', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Encrypted checkout and data protection', 'theme-woopm-child'); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="trimvia-single-product-cta-wrap">
	<div class="trimvia-single-product-cta cta-sec">
		<div class="orb orb-1"></div>
		<div class="orb orb-2"></div>
		<div class="trimvia-single-product-cta-inner">
			<h2 class="stitle"><?php esc_html_e('Not sure which treatment is right for you?', 'theme-woopm-child'); ?></h2>
			<p class="sdesc"><?php esc_html_e('Start a free consultation and our prescribers will recommend the best option based on your health profile.', 'theme-woopm-child'); ?></p>
			<a href="<?php echo esc_url($consultation_url); ?>" class="btn-cta">
				<?php esc_html_e('Start Free Consultation', 'theme-woopm-child'); ?>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
			</a>
		</div>
	</div>
</section>

<?php do_action('woocommerce_after_single_product'); ?>
