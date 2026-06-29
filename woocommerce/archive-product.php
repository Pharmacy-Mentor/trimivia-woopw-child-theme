<?php
/**
 * WooCommerce shop/archive template aligned with HTML theme shop layout.
 *
 * @package theme-woopm-child
 */

defined('ABSPATH') || exit;

get_header('shop');

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
do_action('woocommerce_before_main_content');

$archive_title = woocommerce_page_title(false);
$hero_title    = is_shop() ? __('Shop Treatments', 'theme-woopm-child') : $archive_title;

$archive_description_raw = '';
if (is_shop()) {
	$shop_page_id = (int) wc_get_page_id('shop');
	if ($shop_page_id > 0) {
		$shop_page = get_post($shop_page_id);
		if ($shop_page instanceof WP_Post) {
			$archive_description_raw = (string) $shop_page->post_content;
		}
	}
} else {
	$archive_description_raw = (string) term_description();
}

$archive_description = trim(wp_strip_all_tags($archive_description_raw));
if ('' === $archive_description) {
	$archive_description = __('Browse our full range of prescription weight loss treatments. Every order is reviewed by a UK-registered pharmacist prescriber before dispatch.', 'theme-woopm-child');
}

$products_total = (int) wc_get_loop_prop('total');
$count_label    = sprintf(
	/* translators: %s: number of treatments shown */
	_n('Showing %s treatment', 'Showing %s treatments', $products_total, 'theme-woopm-child'),
	number_format_i18n($products_total)
);
?>
<section class="page-hero page-hero--shop">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--shop">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($archive_title); ?></span>
		</div>
		<h1><?php echo esc_html($hero_title); ?></h1>
		<p><?php echo esc_html($archive_description); ?></p>
	</div>
</section>

<section class="page-section">
	<div class="container">
		<?php if (function_exists('woocommerce_output_all_notices')) : ?>
			<div class="trimvia-wc-notices">
				<?php woocommerce_output_all_notices(); ?>
			</div>
		<?php endif; ?>

		<div class="shop-header rv">
			<div>
				<h2 class="stitle trimvia-shop-title"><?php echo esc_html($archive_title); ?></h2>
				<span class="shop-count"><?php echo esc_html($count_label); ?></span>
			</div>
			<div class="shop-sort">
				<label><?php esc_html_e('Sort by:', 'theme-woopm-child'); ?></label>
				<?php
				if (function_exists('woocommerce_catalog_ordering')) {
					woocommerce_catalog_ordering();
				}
				?>
			</div>
		</div>

		<?php if (woocommerce_product_loop()) : ?>
			<div class="shop-grid">
				<?php while (have_posts()) : ?>
					<?php
					the_post();
					do_action('woocommerce_shop_loop');
					$shop_product = wc_get_product(get_the_ID());
					if (!$shop_product instanceof WC_Product || !$shop_product->is_visible()) {
						continue;
					}
					get_template_part(
						'template-parts/trimvia',
						'shop-product-card',
						array('product' => $shop_product)
					);
					?>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<?php do_action('woocommerce_no_products_found'); ?>
		<?php endif; ?>

		<div class="shop-trust shop-trust--after-products rv">
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
				<h4><?php esc_html_e('Tracked Delivery', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Discreet, unbranded packaging', 'theme-woopm-child'); ?></p>
			</div>
			<div class="shop-trust-item">
				<div class="shop-trust-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
				<h4><?php esc_html_e('100% Secure', 'theme-woopm-child'); ?></h4>
				<p><?php esc_html_e('Encrypted checkout and data protection', 'theme-woopm-child'); ?></p>
			</div>
		</div>

		<div class="shop-after-products">
			<section class="trimvia-shop-cta cta-sec">
				<div class="orb orb-1"></div>
				<div class="orb orb-2"></div>
				<div class="trimvia-shop-cta-inner">
					<h2 class="stitle"><?php esc_html_e('Not sure which treatment is right for you?', 'theme-woopm-child'); ?></h2>
					<p class="sdesc"><?php esc_html_e('Start a free consultation and our prescribers will recommend the best option based on your health profile.', 'theme-woopm-child'); ?></p>
					<a href="<?php echo esc_url(home_url('/shop/')); ?>" class="btn-cta">
						<?php esc_html_e('Start Consultation', 'theme-woopm-child'); ?>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</a>
				</div>
			</section>
		</div>
	</div>
</section>
<?php
do_action('woocommerce_after_main_content');
get_footer('shop');
