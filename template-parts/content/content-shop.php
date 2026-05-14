<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_the_ID();
if (function_exists('is_shop') && is_shop() && function_exists('wc_get_page_id')) {
	$shop_page_id = (int) wc_get_page_id('shop');
	if ($shop_page_id > 0) {
		$page_id = $shop_page_id;
	}
}

$hero_title = 'Shop Treatments';
$hero_breadcrumb_current = __('Start Assessment', 'theme-woopm-child');
$hero_description = 'Browse our full range of prescription weight loss treatments. Every order is reviewed by a UK-registered pharmacist prescriber before dispatch.';
$products_heading = 'All Products';
$show_shop_trust = true;
$show_after_products_details = true;
$after_products_title = 'Need Help Choosing the Right Treatment?';
$after_products_content = 'Our clinical team can guide you to the most suitable treatment based on your goals, medical history, and lifestyle.';
$show_shop_cta = true;
$shop_cta_title = 'Not sure which treatment is right for you?';
$shop_cta_description = 'Start a free consultation and our prescribers will recommend the best option based on your health profile.';
$shop_cta_button_label = 'Start Free Consultation';
$shop_cta_button_url = home_url('/consultation/');

$shop_trust_items = array(
	array(
		'icon_class' => 'fa-solid fa-shield-halved',
		'title' => 'GPhC Regulated',
		'subtitle' => 'Dispensed by Mayberry Pharmacy',
	),
	array(
		'icon_class' => 'fa-solid fa-user-doctor',
		'title' => 'Prescriber Reviewed',
		'subtitle' => 'Every order checked by a UK pharmacist',
	),
	array(
		'icon_class' => 'fa-solid fa-truck-fast',
		'title' => 'Next-Day Delivery',
		'subtitle' => 'Discreet, unbranded packaging',
	),
	array(
		'icon_class' => 'fa-solid fa-lock',
		'title' => '100% Secure',
		'subtitle' => 'Encrypted checkout and data protection',
	),
);

if (function_exists('get_field') && $page_id) {
	$hero_title_value = trim((string) get_field('shop_hero_title', $page_id));
	if ('' !== $hero_title_value) {
		$hero_title = $hero_title_value;
	}

	$hero_description_value = trim((string) get_field('shop_hero_description', $page_id));
	if ('' !== $hero_description_value) {
		$hero_description = $hero_description_value;
	}

	$hero_breadcrumb_current_value = trim((string) get_field('shop_hero_breadcrumb_current', $page_id));
	if ('' !== $hero_breadcrumb_current_value) {
		$hero_breadcrumb_current = $hero_breadcrumb_current_value;
	}

	$products_heading_value = trim((string) get_field('shop_products_heading', $page_id));
	if ('' !== $products_heading_value) {
		$products_heading = $products_heading_value;
	}

	$show_shop_trust_value = get_field('shop_trust_visibility', $page_id);
	if (null !== $show_shop_trust_value && '' !== $show_shop_trust_value) {
		$show_shop_trust = (bool) $show_shop_trust_value;
	}

	$show_after_products_details_value = get_field('shop_after_products_visibility', $page_id);
	if (null !== $show_after_products_details_value && '' !== $show_after_products_details_value) {
		$show_after_products_details = (bool) $show_after_products_details_value;
	}

	$after_products_title_value = trim((string) get_field('shop_after_products_title', $page_id));
	if ('' !== $after_products_title_value) {
		$after_products_title = $after_products_title_value;
	}

	$after_products_content_value = trim((string) get_field('shop_after_products_content', $page_id));
	if ('' !== $after_products_content_value) {
		$after_products_content = $after_products_content_value;
	}

	$show_shop_cta_value = get_field('shop_cta_visibility', $page_id);
	if (null !== $show_shop_cta_value && '' !== $show_shop_cta_value) {
		$show_shop_cta = (bool) $show_shop_cta_value;
	}

	$shop_cta_title_value = trim((string) get_field('shop_cta_title', $page_id));
	if ('' !== $shop_cta_title_value) {
		$shop_cta_title = $shop_cta_title_value;
	}

	$shop_cta_description_value = trim((string) get_field('shop_cta_description', $page_id));
	if ('' !== $shop_cta_description_value) {
		$shop_cta_description = $shop_cta_description_value;
	}

	$shop_cta_button_label_value = trim((string) get_field('shop_cta_button_label', $page_id));
	if ('' !== $shop_cta_button_label_value) {
		$shop_cta_button_label = $shop_cta_button_label_value;
	}

	$shop_cta_button_url_value = trim((string) get_field('shop_cta_button_url', $page_id));
	if ('' !== $shop_cta_button_url_value) {
		$shop_cta_button_url = $shop_cta_button_url_value;
	}

	$shop_trust_items_value = get_field('shop_trust_items', $page_id);
	if (is_array($shop_trust_items_value) && !empty($shop_trust_items_value)) {
		$clean_shop_trust_items = array();

		foreach ($shop_trust_items_value as $shop_trust_item) {
			$item_title = isset($shop_trust_item['title']) ? trim((string) $shop_trust_item['title']) : '';
			$item_subtitle = isset($shop_trust_item['subtitle']) ? trim((string) $shop_trust_item['subtitle']) : '';
			$item_icon_class = isset($shop_trust_item['icon_class']) ? trim((string) $shop_trust_item['icon_class']) : '';

			if ('' === $item_title && '' === $item_subtitle) {
				continue;
			}

			$clean_shop_trust_items[] = array(
				'icon_class' => trimvia_sanitize_icon_class($item_icon_class),
				'title' => $item_title,
				'subtitle' => $item_subtitle,
			);
		}

		if (!empty($clean_shop_trust_items)) {
			$shop_trust_items = $clean_shop_trust_items;
		}
	}
}

$sort_options = array(
	'popular' => __('Most Popular', 'theme-woopm-child'),
	'price_low' => __('Price: Low to High', 'theme-woopm-child'),
	'price_high' => __('Price: High to Low', 'theme-woopm-child'),
	'newest' => __('Newest First', 'theme-woopm-child'),
);

$active_sort = isset($_GET['sort']) ? sanitize_key((string) wp_unslash($_GET['sort'])) : 'popular';
if (!array_key_exists($active_sort, $sort_options)) {
	$active_sort = 'popular';
}

$shop_product_query_args = array(
	'status' => 'publish',
	'limit' => -1,
	'catalog_visibility' => 'visible',
);

if ('price_low' === $active_sort) {
	$shop_product_query_args['orderby'] = 'price';
	$shop_product_query_args['order'] = 'ASC';
} elseif ('price_high' === $active_sort) {
	$shop_product_query_args['orderby'] = 'price';
	$shop_product_query_args['order'] = 'DESC';
} elseif ('newest' === $active_sort) {
	$shop_product_query_args['orderby'] = 'date';
	$shop_product_query_args['order'] = 'DESC';
} else {
	$shop_product_query_args['orderby'] = 'popularity';
	$shop_product_query_args['order'] = 'DESC';
}

$shop_products = function_exists('wc_get_products') ? wc_get_products($shop_product_query_args) : array();
$shop_products_count = is_array($shop_products) ? count($shop_products) : 0;
?>
<section class="page-hero page-hero--shop">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--shop"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a> <span>&rsaquo;</span> <span><?php echo esc_html($hero_breadcrumb_current); ?></span></div>
		<h1><?php echo esc_html($hero_title); ?></h1>
		<p><?php echo wp_kses_post($hero_description); ?></p>
	</div>
</section>

<section class="page-section">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<h2 class="stitle" style="font-size:28px;margin-bottom:4px;"><?php echo esc_html($products_heading); ?></h2>
				<span class="shop-count"><?php echo esc_html(sprintf(_n('Showing %s treatment', 'Showing %s treatments', $shop_products_count, 'theme-woopm-child'), number_format_i18n($shop_products_count))); ?></span>
			</div>
			<form class="shop-sort" method="get" action="<?php echo esc_url(get_permalink($page_id)); ?>">
				<label for="trimvia-shop-sort"><?php esc_html_e('Sort by:', 'theme-woopm-child'); ?></label>
				<select id="trimvia-shop-sort" name="sort" onchange="this.form.submit()">
					<?php foreach ($sort_options as $sort_value => $sort_label) : ?>
						<option value="<?php echo esc_attr($sort_value); ?>" <?php selected($active_sort, $sort_value); ?>><?php echo esc_html($sort_label); ?></option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>

		<?php if (!empty($shop_products)) : ?>
			<div class="shop-grid">
				<?php foreach ($shop_products as $shop_product) : ?>
					<?php
					if (!$shop_product instanceof WC_Product) {
						continue;
					}
					get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $shop_product));
					?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="shop-empty">
				<h3><?php esc_html_e('No products found', 'theme-woopm-child'); ?></h3>
				<p><?php esc_html_e('Please add products in WooCommerce to show them here.', 'theme-woopm-child'); ?></p>
			</div>
		<?php endif; ?>

		<?php if ($show_shop_trust && !empty($shop_trust_items)) : ?>
			<div class="shop-trust shop-trust--after-products rv">
				<?php foreach ($shop_trust_items as $shop_trust_item) : ?>
					<?php
					$trust_icon_class = isset($shop_trust_item['icon_class']) ? trimvia_sanitize_icon_class((string) $shop_trust_item['icon_class']) : '';
					$trust_title = isset($shop_trust_item['title']) ? trim((string) $shop_trust_item['title']) : '';
					$trust_subtitle = isset($shop_trust_item['subtitle']) ? trim((string) $shop_trust_item['subtitle']) : '';
					?>
					<div class="shop-trust-item">
						<div class="shop-trust-icon">
							<?php if ('' !== $trust_icon_class) : ?>
								<i class="<?php echo esc_attr($trust_icon_class); ?>" aria-hidden="true"></i>
							<?php else : ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
							<?php endif; ?>
						</div>
						<?php if ('' !== $trust_title) : ?>
							<h4><?php echo esc_html($trust_title); ?></h4>
						<?php endif; ?>
						<?php if ('' !== $trust_subtitle) : ?>
							<p><?php echo esc_html($trust_subtitle); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ($show_after_products_details && ('' !== trim((string) $after_products_title) || '' !== trim((string) $after_products_content))) : ?>
			<div class="shop-after-products rv">
				<?php if ('' !== trim((string) $after_products_title)) : ?>
					<h2 class="stitle"><?php echo esc_html($after_products_title); ?></h2>
				<?php endif; ?>
				<?php if ('' !== trim((string) $after_products_content)) : ?>
					<div class="shop-after-products-content"><?php echo wp_kses_post(wpautop($after_products_content)); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ($show_shop_cta) : ?>
	<section class="cta-sec" style="position:relative;overflow:hidden;">
		<div class="orb orb-1" style="top:-30%;right:-20%;opacity:.4"></div>
		<div class="orb orb-2" style="bottom:-30%;left:-15%;opacity:.25"></div>
		<div style="max-width:700px;margin:0 auto;padding:80px 40px;text-align:center;position:relative;z-index:1;">
			<h2 class="stitle" style="color:#fff;font-size:clamp(30px,3.5vw,44px);"><?php echo esc_html($shop_cta_title); ?></h2>
			<p class="sdesc" style="color:rgba(255,255,255,0.7);max-width:440px;margin:0 auto 36px;"><?php echo esc_html($shop_cta_description); ?></p>
			<a href="<?php echo esc_url($shop_cta_button_url); ?>" class="btn-cta" style="color:var(--blue);background:#fff;padding:17px 40px;font-size:15px;font-weight:700;border-radius:50px;display:inline-flex;align-items:center;gap:12px;box-shadow:0 8px 30px rgba(0,0,0,0.2);">
				<?php echo esc_html($shop_cta_button_label); ?>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
			</a>
		</div>
	</section>
<?php endif; ?>

