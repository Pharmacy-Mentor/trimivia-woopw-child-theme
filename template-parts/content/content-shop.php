<?php
if (!defined('ABSPATH')) {
	exit;
}

$is_shop = function_exists('is_shop') && is_shop();
$page_id = get_the_ID();
if ($is_shop && function_exists('wc_get_page_id')) {
	$shop_page_id = (int) wc_get_page_id('shop');
	if ($shop_page_id > 0) {
		$page_id = $shop_page_id;
	}
}

$hero_title = __('Shop Treatments', 'theme-woopm-child');
$hero_breadcrumb_current = __('Start Assessment', 'theme-woopm-child');
$hero_description = __('Browse our full range of prescription weight loss treatments. Every order is reviewed by a UK-registered pharmacist prescriber before dispatch.', 'theme-woopm-child');
$products_heading = __('All Products', 'theme-woopm-child');
$show_shop_trust = true;
$show_after_products_details = true;
$after_products_title = __('Need Help Choosing the Right Treatment?', 'theme-woopm-child');
$after_products_content = __('Our clinical team can guide you to the most suitable treatment based on your goals, medical history, and lifestyle.', 'theme-woopm-child');
$show_shop_cta = true;
$shop_cta_title = __('Not sure which treatment is right for you?', 'theme-woopm-child');
$shop_cta_description = __('Start a free consultation and our prescribers will recommend the best option based on your health profile.', 'theme-woopm-child');
$shop_cta_button_label = __('Start Consultation', 'theme-woopm-child');
$shop_cta_button_url = home_url('/shop/');

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
		'title' => 'Tracked Delivery',
		'subtitle' => 'Discreet, unbranded packaging',
	),
	array(
		'icon_class' => 'fa-solid fa-lock',
		'title' => '100% Secure',
		'subtitle' => 'Encrypted checkout and data protection',
	),
);

if (function_exists('get_field') && $page_id > 0) {
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
			if (preg_match('/^next[\s-]?day\s+delivery$/i', $item_title)) {
				$item_title = 'Tracked Delivery';
			}
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

$shop_products_count = 0;
if (function_exists('wc_get_loop_prop')) {
	$shop_products_count = (int) wc_get_loop_prop('total');
}
if ($shop_products_count < 1 && isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query) {
	$shop_products_count = (int) $GLOBALS['wp_query']->found_posts;
}

$query_post_type = '';
if (isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query) {
	$query_post_type = $GLOBALS['wp_query']->get('post_type');
}

$native_query_has_products = false;
if (isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query && is_array($GLOBALS['wp_query']->posts)) {
	foreach ($GLOBALS['wp_query']->posts as $native_query_post) {
		if ($native_query_post instanceof WP_Post && 'product' === $native_query_post->post_type) {
			$native_query_has_products = true;
			break;
		}
	}
}

$is_query_product_type = false;
if (is_array($query_post_type)) {
	$is_query_product_type = in_array('product', $query_post_type, true);
} elseif (is_string($query_post_type) && '' !== $query_post_type) {
	$is_query_product_type = ('product' === $query_post_type);
} elseif (function_exists('is_shop') && is_shop()) {
	$is_query_product_type = $native_query_has_products;
}

$render_native_product_loop = $is_query_product_type && $native_query_has_products && woocommerce_product_loop();
$use_fallback_products      = false;
$fallback_product_results   = null;
if (!$render_native_product_loop && function_exists('wc_get_products')) {
	$fallback_paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

	$orderby_param = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'menu_order';
	$orderby = 'menu_order';
	$order = 'ASC';

	switch ($orderby_param) {
		case 'price':
			$orderby = 'price';
			$order   = 'ASC';
			break;
		case 'price-desc':
			$orderby = 'price';
			$order   = 'DESC';
			break;
		case 'popularity':
			$orderby = 'popularity';
			$order   = 'DESC';
			break;
		case 'rating':
			$orderby = 'rating';
			$order   = 'DESC';
			break;
		case 'date':
			$orderby = 'date';
			$order   = 'DESC';
			break;
		case 'menu_order':
		default:
			$orderby = 'menu_order';
			$order   = 'ASC';
			break;
	}

	$fallback_product_results = wc_get_products(
		array(
			'status'             => 'publish',
			'limit'              => (int) apply_filters('loop_shop_per_page', 12),
			'page'               => $fallback_paged,
			'orderby'            => $orderby,
			'order'              => $order,
			'catalog_visibility' => 'visible',
			'paginate'           => true,
		)
	);

	if (is_object($fallback_product_results) && !empty($fallback_product_results->products)) {
		$use_fallback_products = true;
		$shop_products_count   = (int) $fallback_product_results->total;

		if ('price' === $orderby && is_array($fallback_product_results->products)) {
			usort($fallback_product_results->products, function($a, $b) use ($order) {
				$price_a = $a->is_type('variable') ? (float) $a->get_variation_price('min', true) : (float) $a->get_price();
				$price_b = $b->is_type('variable') ? (float) $b->get_variation_price('min', true) : (float) $b->get_price();

				if (abs($price_a - $price_b) < 0.001) {
					return 0;
				}

				if ('ASC' === $order) {
					return ($price_a < $price_b) ? -1 : 1;
				} else {
					return ($price_a > $price_b) ? -1 : 1;
				}
			});
		}
	}
}
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
			<div class="shop-sort">
				<?php do_action('woocommerce_before_shop_loop'); ?>
			</div>
		</div>

		<?php if ($render_native_product_loop || $use_fallback_products) : ?>
			<div class="shop-grid">
				<?php if ($use_fallback_products && is_object($fallback_product_results)) : ?>
					<?php foreach ($fallback_product_results->products as $product) : ?>
						<?php
						if (!$product instanceof WC_Product || !$product->is_visible()) {
							continue;
						}
						get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $product));
						?>
					<?php endforeach; ?>
				<?php else : ?>
					<?php
					$orderby_param = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : '';
					if (('price' === $orderby_param || 'price-desc' === $orderby_param) && isset($GLOBALS['wp_query']->posts) && is_array($GLOBALS['wp_query']->posts)) {
						usort($GLOBALS['wp_query']->posts, function($a, $b) use ($orderby_param) {
							$product_a = wc_get_product($a->ID);
							$product_b = wc_get_product($b->ID);

							if (!$product_a instanceof WC_Product) {
								return 1;
							}
							if (!$product_b instanceof WC_Product) {
								return -1;
							}

							$price_a = $product_a->is_type('variable') ? (float) $product_a->get_variation_price('min', true) : (float) $product_a->get_price();
							$price_b = $product_b->is_type('variable') ? (float) $product_b->get_variation_price('min', true) : (float) $product_b->get_price();

							if (abs($price_a - $price_b) < 0.001) {
								return 0;
							}

							if ('price' === $orderby_param) {
								return ($price_a < $price_b) ? -1 : 1;
							} else {
								return ($price_a > $price_b) ? -1 : 1;
							}
						});
					}
					?>
					<?php while (have_posts()) : ?>
						<?php
						the_post();
						global $product;
						if (!$product instanceof WC_Product) {
							$product = wc_get_product(get_the_ID());
						}
						if (!$product instanceof WC_Product || !$product->is_visible()) {
							continue;
						}
						get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $product));
						?>
					<?php endwhile; ?>
				<?php endif; ?>
			</div>

			<div class="trimvia-shop-pagination">
				<?php if ($use_fallback_products && is_object($fallback_product_results)) : ?>
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'total'      => (int) $fallback_product_results->max_num_pages,
								'current'    => max(1, (int) get_query_var('paged'), (int) get_query_var('page')),
								'type'       => 'list',
								'prev_text'  => __('Prev', 'theme-woopm-child'),
								'next_text'  => __('Next', 'theme-woopm-child'),
								'mid_size'   => 1,
							)
						)
					);
					?>
				<?php else : ?>
					<?php do_action('woocommerce_after_shop_loop'); ?>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php do_action('woocommerce_no_products_found'); ?>
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

