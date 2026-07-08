<?php
/**
 * Pharmacy Mentor - WooPW child theme bootstrap.
 *
 * DEPLOY: upload this file only to wp-content/themes/Pharmacy-Mentor -WooPW-child/
 * Never copy it into wp-content/themes/pharmacymentor/ (parent). WordPress loads both
 * theme functions.php files; duplicate function names cause a fatal error.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/class-trimvia-nav-walker.php';
require_once get_stylesheet_directory() . '/inc/blog-content-images.php';
require_once get_stylesheet_directory() . '/inc/prescriber-modal-enrichment.php';
require_once get_stylesheet_directory() . '/inc/trimvia-service-icons.php';
require_once get_stylesheet_directory() . '/inc/customizer/bootstrap.php';
require_once get_stylesheet_directory() . '/inc/theme-options.php';

$trimvia_patient_id_stream = get_stylesheet_directory() . '/inc/patient-id-stream.php';
if (file_exists($trimvia_patient_id_stream)) {
	require_once $trimvia_patient_id_stream;
}

$trimvia_consultation_file_stream = get_stylesheet_directory() . '/inc/consultation-file-stream.php';
if (file_exists($trimvia_consultation_file_stream)) {
	require_once $trimvia_consultation_file_stream;
}

/**
 * Theme supports required for WordPress and Yoast SEO document titles.
 */
function trimvia_child_theme_setup()
{
	add_theme_support('title-tag');
	// Fixed header handles its own offset; prevent WP html margin-top double gap.
	add_theme_support('admin-bar', array('callback' => '__return_false'));
}
add_action('after_setup_theme', 'trimvia_child_theme_setup', 0);

/**
 * Stop WordPress from injecting html { margin-top: 32px } for the admin bar.
 */
function trimvia_disable_admin_bar_bump()
{
	remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'trimvia_disable_admin_bar_bump', 0);
add_action('admin_init', 'trimvia_disable_admin_bar_bump', 0);

/**
 * Match parent theme WooCommerce gallery support (zoom, lightbox, slider).
 */
function trimvia_child_woocommerce_support()
{
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'trimvia_child_woocommerce_support', 11);

/**
 * Fallback document title when Yoast/meta filters leave the title empty.
 *
 * @param string $title Current document title.
 * @return string
 */
function trimvia_document_title_fallback($title)
{
	if (is_admin() || is_customize_preview()) 
	{
		return $title;
	}

	$title = is_string($title) ? trim(wp_strip_all_tags($title)) : '';
	if ('' !== $title) {
		return $title;
	}

	if (function_exists('YoastSEO')) {
		try {
			$yoast_title = YoastSEO()->meta->for_current_page()->title;
			$yoast_title = is_string($yoast_title) ? trim(wp_strip_all_tags($yoast_title)) : '';
			if ('' !== $yoast_title) {
				return $yoast_title;
			}
		} catch (Throwable $e) {
			// Yoast meta unavailable for this request.
		}
	}

	if (is_singular()) {
		$singular_title = trim(get_the_title());
		if ('' !== $singular_title) {
			return $singular_title;
		}
	}

	if (is_home() && !is_front_page()) {
		$posts_page_id = (int) get_option('page_for_posts');
		if ($posts_page_id > 0) {
			$posts_page_title = trim(get_the_title($posts_page_id));
			if ('' !== $posts_page_title) {
				return $posts_page_title;
			}
		}
	}

	$site_name = trim((string) get_bloginfo('name', 'display'));
	if ('' !== $site_name) {
		return $site_name;
	}

	return 'Trimvia';
}
add_filter('pre_get_document_title', 'trimvia_document_title_fallback', 100);

/**
 * Keep legacy frontend scripts stable (signature, practitioner dashboard, modal scripts).
 */
function trimvia_force_jquery_compatibility()
{
	if (is_admin()) {
		return;
	}

	wp_enqueue_script('jquery');
	wp_add_inline_script(
		'jquery',
		'window.$ = window.jQuery; window.admin = window.admin || {}; if (!window.admin.ajax) { window.admin.ajax = "' . esc_url_raw(admin_url('admin-ajax.php')) . '"; }',
		'after'
	);
}
add_action('wp_enqueue_scripts', 'trimvia_force_jquery_compatibility', 999999);

/**
 * Stop parent theme Customizer fonts from overriding Trimvia typography.
 */
function trimvia_disable_parent_theme_fonts()
{
	remove_action('wp_head', 'theme_fonts_customize_css');
	remove_action('wp_enqueue_scripts', 'theme_fonts_scripts');
}
add_action('after_setup_theme', 'trimvia_disable_parent_theme_fonts', 20);

/**
 * Preconnect to Google font files served from gstatic.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function trimvia_font_resource_hints($urls, $relation_type)
{
	if ('preconnect' === $relation_type) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter('wp_resource_hints', 'trimvia_font_resource_hints', 10, 2);

/**
 * Whether Bootstrap modal assets are needed (consultation popup, prescriber modals).
 *
 * @return bool
 */
function trimvia_needs_bootstrap_modal_assets()
{
	if (function_exists('is_order_received_page') && is_order_received_page()) {
		return true;
	}

	if (function_exists('is_account_page') && is_account_page()) {
		if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
			return true;
		}

		if (trimvia_user_has_prescriber_access()) {
			return true;
		}
	}

	if (trimvia_is_order_tracking_page()) {
		return true;
	}

	return false;
}

/**
 * Whether the current page is the public order tracking page.
 *
 * @return bool
 */
function trimvia_is_order_tracking_page()
{
	if (!function_exists('is_page') || !is_page()) {
		return false;
	}

	$page = get_queried_object();
	if (!$page instanceof WP_Post) {
		return false;
	}

	if ('track-your-order' === $page->post_name) {
		return true;
	}

	return has_shortcode($page->post_content, 'woocommerce_order_tracking');
}

/**
 * Enqueue parent Bootstrap 4 assets used by WooPW consultation/prescriber modals.
 *
 * @param WP_Theme $parent_theme Parent theme object.
 * @param array    $style_deps   Style handles to append bootstrap CSS to.
 * @return array Script handles required by child common.js.
 */
function trimvia_enqueue_bootstrap_modal_assets($parent_theme, array &$style_deps)
{
	$script_deps = array();
	$parent_bootstrap_css = get_template_directory() . '/assets/css/bootstrap.min.css';
	$parent_bootstrap_js  = get_template_directory() . '/assets/js/bootstrap.min.js';

	if (file_exists($parent_bootstrap_css) && !wp_style_is('trimvia-parent-bootstrap', 'enqueued')) {
		wp_enqueue_style(
			'trimvia-parent-bootstrap',
			get_template_directory_uri() . '/assets/css/bootstrap.min.css',
			array(),
			$parent_theme->get('Version')
		);
		$style_deps[] = 'trimvia-parent-bootstrap';
	}

	if (file_exists($parent_bootstrap_js) && !wp_script_is('trimvia-parent-bootstrap', 'enqueued')) {
		wp_enqueue_script(
			'trimvia-popper',
			'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
			array(),
			'1.16.1',
			true
		);

		wp_enqueue_script(
			'trimvia-parent-bootstrap',
			get_template_directory_uri() . '/assets/js/bootstrap.min.js',
			array('jquery', 'trimvia-popper'),
			$parent_theme->get('Version'),
			true
		);
		$script_deps[] = 'trimvia-parent-bootstrap';
	}

	return $script_deps;
}

/**
 * Enqueue parent + child assets.
 */
function trimvia_child_enqueue_assets()
{
	if (trimvia_is_customizer_preview()) {
		$parent_theme = wp_get_theme(get_template());
		$child_theme  = wp_get_theme();

		wp_enqueue_style(
			'theme-woopw-parent-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$parent_theme->get('Version')
		);

		wp_enqueue_style(
			'theme-woopw-child-style',
			get_stylesheet_uri(),
			array('theme-woopw-parent-style'),
			$child_theme->get('Version')
		);

		wp_enqueue_style(
			'theme-woopw-child-responsive',
			get_stylesheet_directory_uri() . '/assets/css/style.css',
			array('theme-woopw-child-style'),
			filemtime(get_stylesheet_directory() . '/assets/css/style.css')
		);

		wp_enqueue_style(
			'trimvia-font-awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
			array(),
			'6.5.2'
		);

		return;
	}

	$parent_theme = wp_get_theme(get_template());
	$child_theme = wp_get_theme();
	$fonts_path   = get_stylesheet_directory() . '/assets/css/trimvia-fonts.css';

	wp_enqueue_style(
		'theme-woopw-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_theme->get('Version')
	);

	wp_enqueue_style(
		'theme-woopw-child-style',
		get_stylesheet_uri(),
		array('theme-woopw-parent-style'),
		$child_theme->get('Version')
	);

	wp_enqueue_style(
		'theme-woopw-child-fonts',
		get_stylesheet_directory_uri() . '/assets/css/trimvia-fonts.css',
		array(),
		file_exists($fonts_path) ? filemtime($fonts_path) : $child_theme->get('Version')
	);

	$child_responsive_deps = array('theme-woopw-child-style', 'theme-woopw-child-fonts');
	$common_script_deps    = array('jquery');

	// Parent Bootstrap 4 â€” required for WooPW consultation modal (thank you / view order) and prescriber popups.
	if (trimvia_needs_bootstrap_modal_assets()) {
		$bootstrap_script_deps = trimvia_enqueue_bootstrap_modal_assets($parent_theme, $child_responsive_deps);
		$common_script_deps    = array_merge($common_script_deps, $bootstrap_script_deps);
	}

	$is_prescriber_account =
		function_exists('is_account_page')
		&& is_account_page()
		&& trimvia_user_has_prescriber_access();

	// cflp-prescriber-dashboard is only registered for prescribers/admins. Adding it as a
	// dependency for everyone prevents WordPress from printing theme-woopw-child-responsive.
	if ($is_prescriber_account) {
		$child_responsive_deps[] = 'cflp-prescriber-dashboard';
	}

	wp_enqueue_style(
		'theme-woopw-child-responsive',
		get_stylesheet_directory_uri() . '/assets/css/style.css',
		$child_responsive_deps,
		filemtime(get_stylesheet_directory() . '/assets/css/style.css')
	);

	$consultation_modal_css = get_stylesheet_directory() . '/assets/css/consultation-modal.css';
	wp_enqueue_style(
		'trimvia-consultation-modal',
		get_stylesheet_directory_uri() . '/assets/css/consultation-modal.css',
		array('theme-woopw-child-responsive'),
		file_exists($consultation_modal_css) ? filemtime($consultation_modal_css) : $child_theme->get('Version')
	);

	if (function_exists('is_account_page') && is_account_page()) {
		if ($is_prescriber_account) {
			$prescriber_modal_css = get_stylesheet_directory() . '/assets/css/prescriber-modal-parent.css';
			wp_enqueue_style(
				'trimvia-prescriber-modal-parent',
				get_stylesheet_directory_uri() . '/assets/css/prescriber-modal-parent.css',
				array('theme-woopw-child-responsive', 'cflp-prescriber-dashboard'),
				file_exists($prescriber_modal_css) ? filemtime($prescriber_modal_css) : $child_theme->get('Version')
			);
		}

		$signature_modal_css = get_stylesheet_directory() . '/assets/css/signature-modal.css';
		wp_enqueue_style(
			'trimvia-signature-modal',
			get_stylesheet_directory_uri() . '/assets/css/signature-modal.css',
			array('theme-woopw-child-responsive'),
			file_exists($signature_modal_css) ? filemtime($signature_modal_css) : $child_theme->get('Version')
		);
	}

	wp_enqueue_style(
		'trimvia-font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);

	wp_enqueue_script(
		'theme-woopw-child-common',
		get_stylesheet_directory_uri() . '/assets/js/common.js',
		array_values(array_unique($common_script_deps)),
		filemtime(get_stylesheet_directory() . '/assets/js/common.js'),
		true
	);

	if (is_front_page() || is_home()) {
		wp_enqueue_script(
			'trimvia-chart-js',
			'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js',
			array(),
			null,
			true
		);
		wp_enqueue_script(
			'trimvia-homepage-chart',
			get_stylesheet_directory_uri() . '/assets/js/homepage-chart.js',
			array('trimvia-chart-js'),
			filemtime(get_stylesheet_directory() . '/assets/js/homepage-chart.js'),
			true
		);
	}

	if (is_page('consultation')) {
		wp_enqueue_script(
			'trimvia-gravity-consult',
			get_stylesheet_directory_uri() . '/assets/js/trimvia-gravity-consult.js',
			array('jquery'),
			filemtime(get_stylesheet_directory() . '/assets/js/trimvia-gravity-consult.js'),
			true
		);
		wp_enqueue_script(
			'trimvia-cflp-consult',
			get_stylesheet_directory_uri() . '/assets/js/trimvia-cflp-consult.js',
			array('trimvia-gravity-consult'),
			filemtime(get_stylesheet_directory() . '/assets/js/trimvia-cflp-consult.js'),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'trimvia_child_enqueue_assets', 110);

/**
 * WooCommerce admin order screen — constrain prescription modal logo size.
 */
function trimvia_enqueue_admin_prescription_modal_styles($hook)
{
	if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen) {
		return;
	}

	$is_order_screen = ('shop_order' === $screen->post_type)
		|| (false !== strpos($screen->id, 'wc-orders'))
		|| (false !== strpos($screen->id, 'shop_order'));

	if (!$is_order_screen) {
		return;
	}

	$css_path = get_stylesheet_directory() . '/assets/css/admin-prescription-modal.css';
	if (!file_exists($css_path)) {
		return;
	}

	wp_enqueue_style(
		'trimvia-admin-prescription-modal',
		get_stylesheet_directory_uri() . '/assets/css/admin-prescription-modal.css',
		array(),
		filemtime($css_path)
	);
}
add_action('admin_enqueue_scripts', 'trimvia_enqueue_admin_prescription_modal_styles');

/**
 * Cart and checkout use Trimvia full-width layout — parent Bootstrap/responsive CSS causes mobile overflow.
 * Parent registers handles `bootstrap`, `responsive`, and WooCommerce layout CSS in pharmacy_register_styles() / WC.
 */
function trimvia_dequeue_parent_bootstrap_on_commerce_pages()
{
	if (function_exists('trimvia_is_customizer_preview') && trimvia_is_customizer_preview()) {
		return;
	}

	$is_commerce = (function_exists('is_cart') && is_cart())
		|| (function_exists('is_checkout') && is_checkout() && !(function_exists('is_order_received_page') && is_order_received_page()));

	if (!$is_commerce) {
		return;
	}

	$handles = array('bootstrap', 'responsive', 'woocommerce-layout', 'woocommerce-smallscreen');
	foreach ($handles as $handle) {
		wp_dequeue_style($handle);
		wp_deregister_style($handle);
	}
}

add_action('wp_enqueue_scripts', 'trimvia_dequeue_parent_bootstrap_on_commerce_pages', 100000);

/**
 * Ensure WooPW prescriber dashboard JS runs after Bootstrap modal plugin.
 */
function trimvia_prescriber_dashboard_script_deps()
{
	if (!wp_script_is('cflp-prescriber-dashboard', 'registered')) {
		return;
	}

	if (wp_script_is('trimvia-parent-bootstrap', 'registered')) {
		$deps = wp_scripts()->registered['cflp-prescriber-dashboard']->deps;
		if (!in_array('trimvia-parent-bootstrap', $deps, true)) {
			wp_scripts()->registered['cflp-prescriber-dashboard']->deps[] = 'trimvia-parent-bootstrap';
		}
	}

	if (wp_script_is('theme-woopw-child-common', 'registered')) {
		$deps = wp_scripts()->registered['cflp-prescriber-dashboard']->deps;
		if (!in_array('theme-woopw-child-common', $deps, true)) {
			wp_scripts()->registered['cflp-prescriber-dashboard']->deps[] = 'theme-woopw-child-common';
		}
	}

	// Always localize woopwAjax to support security nonces required by version 1.8.2
	wp_localize_script(
		'cflp-prescriber-dashboard',
		'woopwAjax',
		array(
			'ajax'                    => admin_url('admin-ajax.php'),
			'myaccount_url'           => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#',
			'nonce_order_actions'     => wp_create_nonce('woopw_order_actions'),
			'nonce_presc_actions'     => wp_create_nonce('woopw_presc_actions'),
			'nonce_more_info_form'    => wp_create_nonce('woopw_more_info_form'),
			'nonce_requested_info'    => wp_create_nonce('woopw_requested_info'),
			'nonce_process_more_info' => wp_create_nonce('woopw_process_more_info'),
			'nonce_view_prescription' => wp_create_nonce('woopw_view_prescription'),
			'nonce_prescriber_note'   => wp_create_nonce('woopw_prescriber_note'),
		)
	);
}
add_action('wp_enqueue_scripts', 'trimvia_prescriber_dashboard_script_deps', 111);

/**
 * Sanitize Font Awesome class list.
 *
 * @param string $value Raw icon classes.
 * @return string
 */
function trimvia_sanitize_icon_class($value)
{
	$classes = preg_split('/\s+/', (string) $value);
	$classes = array_filter($classes);
	$classes = array_map('sanitize_html_class', $classes);
	$classes = array_filter($classes);

	return implode(' ', $classes);
}

/**
 * Header cart count badge markup. Kept as one helper so WooCommerce fragments
 * can refresh the same element after AJAX add-to-cart events.
 *
 * @return string
 */
function trimvia_header_cart_count_badge()
{
	$count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
	$class = 'trimvia-cart-count-badge';
	if (0 === $count) {
		$class .= ' is-empty';
	}

	return sprintf(
		'<span class="%1$s" data-cart-count="%2$d" aria-hidden="true">%3$s</span>',
		esc_attr($class),
		esc_attr($count),
		esc_html(number_format_i18n($count))
	);
}

/**
 * Keep the header cart count in sync after WooCommerce AJAX add-to-cart.
 *
 * @param array<string,string> $fragments Existing cart fragments.
 * @return array<string,string>
 */
function trimvia_header_cart_count_fragment($fragments)
{
	$fragments['.trimvia-cart-count-badge'] = trimvia_header_cart_count_badge();

	return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'trimvia_header_cart_count_fragment');

/**
 * Add nav-item class to top-level menu items that have dropdowns (mega menu CSS).
 *
 * @param array    $classes CSS classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Menu args.
 * @param int      $depth   Nesting depth.
 * @return array
 */
function trimvia_nav_menu_parent_item_class($classes, $item, $args, $depth)
{
	if (0 === (int) $depth && in_array('menu-item-has-children', $classes, true)) {
		$classes[] = 'nav-item';
	}
	return $classes;
}
add_filter('nav_menu_css_class', 'trimvia_nav_menu_parent_item_class', 10, 4);

/**
 * Append dropdown chevron to top-level items with children.
 *
 * @param string   $title Menu title HTML.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Depth.
 * @return string
 */
function trimvia_nav_menu_parent_chevron($title, $item, $args, $depth)
{
	if (0 !== (int) $depth || !in_array('menu-item-has-children', $item->classes, true)) {
		return $title;
	}
	$svg  = '<svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
	return $title . $svg;
}
add_filter('nav_menu_item_title', 'trimvia_nav_menu_parent_chevron', 10, 4);

/**
 * Whether a true_false ACF field on a condition term is visible (Service-style toggles).
 * Empty / unset field defaults to true so existing terms keep current behaviour after sync.
 *
 * @param string  $field_name ACF field name.
 * @param WP_Term $term       Condition term.
 * @return bool
 */
function trimvia_condition_field_visible($field_name, $term)
{
	if (!function_exists('get_field') || !$term instanceof WP_Term) {
		return true;
	}
	$value = get_field($field_name, $term);
	if (null === $value || '' === $value) {
		return true;
	}
	return (bool) $value;
}


/**
 * Condition term for the Treatments landing page (page-treatments.php).
 *
 * @return WP_Term|null
 */
function trimvia_get_treatments_landing_condition_term()
{
	if (isset($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slug = sanitize_title(wp_unslash($_GET['condition-slug']));
		if ('' !== $slug) {
			$term = get_term_by('slug', $slug, 'condition');
			if ($term && !is_wp_error($term)) {
				return $term;
			}
		}
	}

	$setting = trim((string) get_theme_mod('trimvia_treatments_page_condition_slug', 'weight-loss'));
	if ('' === $setting) {
		return null;
	}

	$term = get_term_by('slug', sanitize_title($setting), 'condition');

	return ($term && !is_wp_error($term)) ? $term : null;
}

/**
 * Condition term when the public "treatments" layout (hero + product grid) is active (hero search + AJAX).
 *
 * @return WP_Term|null
 */
function trimvia_get_public_condition_treatments_term_for_search()
{
	if (is_tax('condition')) {
		$term = get_queried_object();
		if (!$term instanceof WP_Term || 'condition' !== $term->taxonomy) {
			return null;
		}
		if (function_exists('has_consultation_for_condition') && has_consultation_for_condition($term->slug)) {
			return null;
		}
		return $term;
	}

	if (!is_page()) {
		return null;
	}

	$stylesheet_dir = trailingslashit(get_stylesheet_directory());
	$tpl            = get_page_template();

	if ($tpl === $stylesheet_dir . 'page-treatments.php') {
		return trimvia_get_treatments_landing_condition_term();
	}

	if ($tpl === $stylesheet_dir . 'page-templates/treatments.php') {
		if (empty($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return null;
		}
		$slug = sanitize_title(wp_unslash($_GET['condition-slug']));
		if ('' === $slug) {
			return null;
		}
		$term = get_term_by('slug', $slug, 'condition');

		return ($term && !is_wp_error($term)) ? $term : null;
	}

	return null;
}

/**
 * Products that are publishable on the front end for a condition term.
 *
 * @param WP_Term|int $term Condition term or term ID.
 * @param array       $args Optional wc_get_products overrides.
 * @return array
 */
function trimvia_get_condition_visible_products($term, $args = array())
{
	$term_id = $term instanceof WP_Term ? (int) $term->term_id : absint($term);
	if ($term_id < 1 || !function_exists('wc_get_products')) {
		return array();
	}

	$query = wp_parse_args(
		$args,
		array(
			'status'             => 'publish',
			'limit'              => -1,
			'catalog_visibility' => 'visible',
			'orderby'            => 'menu_order',
			'order'              => 'ASC',
		)
	);

	$query['tax_query'] = array(
		array(
			'taxonomy' => 'condition',
			'field'    => 'term_id',
			'terms'    => array($term_id),
		),
	);

	$products = wc_get_products($query);

	return is_array($products) ? $products : array();
}

/**
 * Whether a condition has at least one visible published product.
 *
 * @param WP_Term|int $term Condition term or term ID.
 * @return bool
 */
function trimvia_condition_has_visible_products($term)
{
	return !empty(
		trimvia_get_condition_visible_products(
			$term,
			array(
				'limit'  => 1,
				'return' => 'ids',
			)
		)
	);
}

/**
 * Visible published product count for a condition.
 *
 * @param WP_Term|int $term Condition term or term ID.
 * @return int
 */
function trimvia_get_condition_visible_product_count($term)
{
	return count(
		trimvia_get_condition_visible_products(
			$term,
			array(
				'return' => 'ids',
			)
		)
	);
}

/**
 * AJAX: return shop card HTML for products in a condition, optionally filtered by search string.
 */
function trimvia_ajax_condition_treatments_search()
{
	check_ajax_referer('trimvia_condition_search', 'nonce');

	$term_id = isset($_POST['term_id']) ? absint(wp_unslash($_POST['term_id'])) : 0;
	$s       = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';

	if ($term_id < 1) {
		wp_send_json_error(array('message' => 'bad_request'), 400);
	}

	$term = get_term($term_id, 'condition');
	if (!$term || is_wp_error($term)) {
		wp_send_json_error(array('message' => 'not_found'), 404);
	}

	if (!function_exists('wc_get_products')) {
		wp_send_json_success(
			array(
				'html'  => '',
				'count' => 0,
			)
		);
	}

	$query = array(
		'status'             => 'publish',
		'limit'              => -1,
		'catalog_visibility' => 'visible',
		'orderby'            => 'menu_order',
		'order'              => 'ASC',
		'tax_query'          => array(
			array(
				'taxonomy' => 'condition',
				'field'    => 'term_id',
				'terms'    => array($term_id),
			),
		),
	);

	if ('' !== $s) {
		$query['s'] = $s;
	}

	$products = wc_get_products($query);
	if (!is_array($products)) {
		$products = array();
	}

	ob_start();
	foreach ($products as $shop_product) {
		if (!$shop_product instanceof WC_Product) {
			continue;
		}
		get_template_part(
			'template-parts/trimvia',
			'shop-product-card',
			array('product' => $shop_product)
		);
	}
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'  => $html,
			'count' => count($products),
		)
	);
}
add_action('wp_ajax_trimvia_condition_treatments_search', 'trimvia_ajax_condition_treatments_search');
add_action('wp_ajax_nopriv_trimvia_condition_treatments_search', 'trimvia_ajax_condition_treatments_search');

/**
 * Script + config for condition-scoped treatment search (hero + header).
 */
function trimvia_enqueue_condition_treatments_search()
{
	// Hero search was removed from condition pages; skip loading unused AJAX filter script.
	return;

	$term = trimvia_get_public_condition_treatments_term_for_search();
	if (!$term instanceof WP_Term) {
		return;
	}
	if (!trimvia_condition_field_visible('cond_products_section_visibility', $term)) {
		return;
	}

	$handle = 'trimvia-condition-treatments-search';
	$path   = get_stylesheet_directory() . '/assets/js/trimvia-condition-treatments-search.js';
	if (!is_readable($path)) {
		return;
	}

	wp_enqueue_script(
		$handle,
		get_stylesheet_directory_uri() . '/assets/js/trimvia-condition-treatments-search.js',
		array(),
		filemtime($path),
		true
	);

	wp_localize_script(
		$handle,
		'trimviaConditionSearch',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('trimvia_condition_search'),
			'termId'  => (int) $term->term_id,
			'i18n'    => array(
				'noResults' => __('No treatments match your search.', 'theme-woopm-child'),
			),
		)
	);
}
add_action('wp_enqueue_scripts', 'trimvia_enqueue_condition_treatments_search', 110);

/**
 * Resolve permalink for the Weight Loss `service` post (Customizer ID/slug, then fallbacks).
 *
 * @return string Empty string if no published service matches.
 */
function trimvia_get_weight_loss_service_permalink()
{
	$post_id = (int) get_theme_mod('trimvia_weight_loss_service_id', 0);
	if ($post_id > 0 && 'service' === get_post_type($post_id) && 'publish' === get_post_status($post_id)) {
		return get_permalink($post_id);
	}

	$slug_setting = trim((string) get_theme_mod('trimvia_weight_loss_service_slug', 'weight-loss-service'));
	$slugs        = array();
	if ('' !== $slug_setting) {
		$slugs[] = sanitize_title($slug_setting);
	}
	$slugs = array_merge($slugs, apply_filters('trimvia_weight_loss_service_extra_slugs', array('weight-loss')));
	$slugs = array_values(array_unique(array_filter($slugs)));

	foreach ($slugs as $slug) {
		$found = get_posts(
			array(
				'post_type'              => 'service',
				'name'                   => $slug,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			)
		);
		if (!empty($found)) {
			return get_permalink((int) $found[0]);
		}
	}

	return '';
}


/**
 * Convert ACF image field value into a URL.
 *
 * @param mixed  $value ACF image value (ID, URL, or array).
 * @param string $size  Image size.
 * @return string
 */
function trimvia_acf_image_url($value, $size = 'full')
{
	if (empty($value)) {
		return '';
	}

	if (is_numeric($value)) {
		$url = wp_get_attachment_image_url((int) $value, $size);
		return $url ?: '';
	}

	if (is_array($value)) {
		if (!empty($value['url'])) {
			return esc_url_raw($value['url']);
		}

		if (!empty($value['ID'])) {
			$url = wp_get_attachment_image_url((int) $value['ID'], $size);
			return $url ?: '';
		}
	}

	return is_string($value) ? esc_url_raw($value) : '';
}

/**
 * Register Trimvia hero fields for slide post type.
 */
function trimvia_register_slide_hero_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_slide_hero',
			'title' => 'Banner Slide (Trimvia Hero)',
			'fields' => array(
				array(
					'key' => 'field_trimvia_hero_title',
					'label' => 'Hero Title',
					'name' => 'hero_title',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_hero_title_emphasis',
					'label' => 'Hero Title Emphasis',
					'name' => 'hero_title_emphasis',
					'type' => 'text',
					'instructions' => 'Blue italic part in heading, e.g. "prescribed by experts."',
				),
				array(
					'key' => 'field_trimvia_hero_subtitle',
					'label' => 'Hero Subtitle',
					'name' => 'hero_subtitle',
					'type' => 'textarea',
					'new_lines' => 'br',
				),
				array(
					'key' => 'field_trimvia_hero_bg_image',
					'label' => 'Hero Background Image',
					'name' => 'hero_bg_image',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_hero_bg_image_mobile',
					'label' => 'Hero Mobile Background Image',
					'name' => 'hero_bg_image_mobile',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_hero_primary_cta',
					'label' => 'Primary CTA',
					'name' => 'hero_primary_cta',
					'type' => 'link',
					'return_format' => 'array',
				),
				array(
					'key' => 'field_trimvia_hero_secondary_cta',
					'label' => 'Secondary CTA',
					'name' => 'hero_secondary_cta',
					'type' => 'link',
					'return_format' => 'array',
				),
				array(
					'key' => 'field_trimvia_hero_rating_score',
					'label' => 'Rating Score',
					'name' => 'hero_rating_score',
					'type' => 'text',
					'default_value' => '4.8',
				),
				array(
					'key' => 'field_trimvia_hero_rating_label',
					'label' => 'Rating Label',
					'name' => 'hero_rating_label',
					'type' => 'text',
					'default_value' => 'Google Reviews',
				),
				array(
					'key' => 'field_trimvia_hero_pills',
					'label' => 'Hero Pills',
					'name' => 'hero_pills',
					'type' => 'repeater',
					'layout' => 'table',
					'button_label' => 'Add Pill',
					'sub_fields' => array(
						array(
							'key' => 'field_trimvia_hero_pill_text',
							'label' => 'Pill Text',
							'name' => 'pill_text',
							'type' => 'text',
						),
					),
				),
				array(
					'key' => 'field_trimvia_is_active_slide',
					'label' => 'Set As Active Hero Slide',
					'name' => 'is_active_slide',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 0,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'slide',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Fields used by Trimvia child theme homepage hero.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_slide_hero_field_group');

/**
 * Register Team Member custom post type.
 */
function trimvia_register_team_member_cpt()
{
	$labels = array(
		'name'               => __('Team Members', 'theme-woopm-child'),
		'singular_name'      => __('Team Member', 'theme-woopm-child'),
		'menu_name'          => __('Team', 'theme-woopm-child'),
		'name_admin_bar'     => __('Team Member', 'theme-woopm-child'),
		'add_new'            => __('Add New', 'theme-woopm-child'),
		'add_new_item'       => __('Add New Team Member', 'theme-woopm-child'),
		'new_item'           => __('New Team Member', 'theme-woopm-child'),
		'edit_item'          => __('Edit Team Member', 'theme-woopm-child'),
		'view_item'          => __('View Team Member', 'theme-woopm-child'),
		'all_items'          => __('All Team Members', 'theme-woopm-child'),
		'search_items'       => __('Search Team Members', 'theme-woopm-child'),
		'not_found'          => __('No team members found.', 'theme-woopm-child'),
		'not_found_in_trash' => __('No team members found in Trash.', 'theme-woopm-child'),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-groups',
		'supports'           => array('title', 'editor', 'thumbnail', 'page-attributes'),
		'publicly_queryable' => false,
		'rewrite'            => false,
	);

	register_post_type('team_member', $args);
}
add_action('init', 'trimvia_register_team_member_cpt');

/**
 * Register Team Member fields.
 */
function trimvia_register_team_member_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_team_member',
			'title' => 'Team Member Details',
			'fields' => array(
				array(
					'key' => 'field_trimvia_team_member_image',
					'label' => 'Member Image',
					'name' => 'team_member_image',
					'type' => 'image',
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
				),
				array(
					'key' => 'field_trimvia_team_member_department',
					'label' => 'Department',
					'name' => 'team_member_department',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_team_member_designation',
					'label' => 'Designation',
					'name' => 'team_member_designation',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_team_member_description',
					'label' => 'Description',
					'name' => 'team_member_description',
					'type' => 'textarea',
					'new_lines' => 'br',
				),
				array(
					'key' => 'field_trimvia_team_member_show_homepage',
					'label' => 'Show on Homepage',
					'name' => 'team_member_show_homepage',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 1,
					'instructions' => 'Turn off to hide this member from homepage team section.',
				),
				array(
					'key' => 'field_trimvia_team_member_display_order',
					'label' => 'Display Order',
					'name' => 'team_member_display_order',
					'type' => 'number',
					'default_value' => 0,
					'min' => 0,
					'step' => 1,
					'instructions' => 'Lower numbers appear first on homepage.',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'team_member',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Details used on the homepage team section.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_team_member_field_group');

/**
 * Register Testimonial custom post type.
 */
function trimvia_register_testimonial_cpt()
{
	$labels = array(
		'name'               => __('Testimonials', 'theme-woopm-child'),
		'singular_name'      => __('Testimonial', 'theme-woopm-child'),
		'menu_name'          => __('Testimonials', 'theme-woopm-child'),
		'name_admin_bar'     => __('Testimonial', 'theme-woopm-child'),
		'add_new'            => __('Add New', 'theme-woopm-child'),
		'add_new_item'       => __('Add New Testimonial', 'theme-woopm-child'),
		'new_item'           => __('New Testimonial', 'theme-woopm-child'),
		'edit_item'          => __('Edit Testimonial', 'theme-woopm-child'),
		'view_item'          => __('View Testimonial', 'theme-woopm-child'),
		'all_items'          => __('All Testimonials', 'theme-woopm-child'),
		'search_items'       => __('Search Testimonials', 'theme-woopm-child'),
		'not_found'          => __('No testimonials found.', 'theme-woopm-child'),
		'not_found_in_trash' => __('No testimonials found in Trash.', 'theme-woopm-child'),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-format-quote',
		'supports'           => array('title', 'editor', 'page-attributes'),
		'publicly_queryable' => false,
		'rewrite'            => false,
	);

	register_post_type('testimonial', $args);
}
add_action('init', 'trimvia_register_testimonial_cpt');

/**
 * Register Testimonial fields.
 */
function trimvia_register_testimonial_field_group()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_testimonial',
			'title' => 'Testimonial Details',
			'fields' => array(
				array(
					'key' => 'field_trimvia_testimonial_reviewer_name',
					'label' => 'Reviewer Name',
					'name' => 'reviewer_name',
					'type' => 'text',
				),
				array(
					'key' => 'field_trimvia_testimonial_reviewer_date',
					'label' => 'Reviewer Date',
					'name' => 'reviewer_date',
					'type' => 'text',
					'instructions' => 'Example: 13/05/2026',
				),
				array(
					'key' => 'field_trimvia_testimonial_rating',
					'label' => 'Rating (1-5)',
					'name' => 'reviewer_rating',
					'type' => 'number',
					'default_value' => 5,
					'min' => 1,
					'max' => 5,
					'step' => 1,
				),
				array(
					'key' => 'field_trimvia_testimonial_initials',
					'label' => 'Reviewer Initials',
					'name' => 'reviewer_initials',
					'type' => 'text',
					'instructions' => 'Optional. If empty, initials are generated from reviewer name.',
				),
				array(
					'key' => 'field_trimvia_testimonial_show_homepage',
					'label' => 'Show on Homepage',
					'name' => 'show_on_homepage',
					'type' => 'true_false',
					'ui' => 1,
					'default_value' => 1,
				),
				array(
					'key' => 'field_trimvia_testimonial_display_order',
					'label' => 'Display Order',
					'name' => 'display_order',
					'type' => 'number',
					'default_value' => 0,
					'min' => 0,
					'step' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'testimonial',
					),
				),
			),
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Content for homepage testimonials cards.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_testimonial_field_group');

/**
 * Add Home-page manual testimonial selector fields.
 */
function trimvia_register_home_testimonial_selector_fields()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_home_testimonial_selector',
			'title' => 'Home - Testimonials Selector',
			'fields' => array(
				array(
					'key' => 'field_trimvia_home_selected_testimonials',
					'label' => 'Select Testimonials',
					'name' => 'selected_testimonials',
					'type' => 'post_object',
					'instructions' => 'Used when Testimonials Type is set to Internal Post Type.',
					'post_type' => array('testimonial'),
					'post_status' => array('publish'),
					'return_format' => 'id',
					'multiple' => 1,
					'ui' => 1,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_67c5e1276258e',
								'operator' => '==',
								'value' => 'manual',
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/home.php',
					),
				),
			),
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Allows selecting specific testimonial posts for homepage.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_home_testimonial_selector_fields');

/**
 * Add Home trust bar fields (5 items + visibility).
 */
function trimvia_register_home_trust_bar_fields()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key' => 'group_trimvia_home_trust_bar',
			'title' => 'Home - Trust Bar',
			'fields' => array(
				array(
					'key' => 'field_trimvia_home_trust_bar_tab',
					'label' => 'Trust Bar',
					'name' => '',
					'type' => 'tab',
					'placement' => 'top',
				),
				array(
					'key' => 'field_trimvia_home_trust_bar_visibility',
					'label' => 'Trust Bar Visibility',
					'name' => 'trust_bar_visibility',
					'type' => 'true_false',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => 'Show',
					'ui_off_text' => 'Hide',
				),
				array(
					'key' => 'field_trimvia_home_trust_bar_items',
					'label' => 'Trust Bar Items',
					'name' => 'trust_bar_items',
					'type' => 'repeater',
					'instructions' => 'Add up to 5 trust items.',
					'layout' => 'block',
					'button_label' => 'Add Trust Item',
					'min' => 1,
					'max' => 5,
					'sub_fields' => array(
						array(
							'key' => 'field_trimvia_home_trust_bar_icon_class',
							'label' => 'Icon Class (Font Awesome)',
							'name' => 'icon_class',
							'type' => 'text',
							'instructions' => 'Example: fa-solid fa-shield-halved',
						),
						array(
							'key' => 'field_trimvia_home_trust_bar_title',
							'label' => 'Title',
							'name' => 'title',
							'type' => 'text',
						),
						array(
							'key' => 'field_trimvia_home_trust_bar_subtitle',
							'label' => 'Subtitle',
							'name' => 'subtitle',
							'type' => 'text',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'page_type',
						'operator' => '==',
						'value' => 'front_page',
					),
				),
				array(
					array(
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/home.php',
					),
				),
			),
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active' => true,
			'description' => 'Homepage trust strip with 5 items.',
			'show_in_rest' => 0,
		)
	);
}
add_action('acf/include_fields', 'trimvia_register_home_trust_bar_fields');

/**
 * Assign admin menu icons to parent-registered CPTs.
 *
 * @param array  $args      Post type args.
 * @param string $post_type Post type key.
 * @return array
 */
function trimvia_set_custom_post_type_icons($args, $post_type)
{
	if ('slide' === $post_type) {
		$args['menu_icon'] = 'dashicons-images-alt2';
	}

	if ('faqs' === $post_type) {
		$args['menu_icon'] = 'dashicons-editor-help';
	}

	return $args;
}
add_filter('register_post_type_args', 'trimvia_set_custom_post_type_icons', 10, 2);

/**
 * ACF options: hero + bottom CTA for Service post type archive (archive-service.php).
 */
function trimvia_register_services_archive_acf_options()
{
	if (!function_exists('acf_add_options_sub_page')) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'parent_slug' => 'edit.php?post_type=service',
			'page_title'  => __('Services archive layout', 'theme-woopm-child'),
			'menu_title'  => __('Archive layout', 'theme-woopm-child'),
			'menu_slug'   => 'trimvia-services-archive-settings',
			'capability'  => 'edit_posts',
		)
	);
}
add_action('acf/init', 'trimvia_register_services_archive_acf_options');

/**
 * Turn on a front-end archive for the `service` CPT when parent/plugin registered it without one.
 *
 * @param array  $args      CPT args.
 * @param string $post_type Post type key.
 * @return array
 */
function trimvia_service_cpt_enable_archive($args, $post_type)
{
	if ('service' !== $post_type || !is_array($args)) {
		return $args;
	}

	if (!apply_filters('trimvia_enable_service_post_type_archive', true)) {
		return $args;
	}

	$args['has_archive'] = true;

	return $args;
}
add_filter('register_post_type_args', 'trimvia_service_cpt_enable_archive', 99, 2);

/**
 * Whether the current page is the WP 2FA user setup page.
 *
 * @return bool
 */
function trimvia_is_wp2fa_setup_page()
{
	if (!is_singular('page')) {
		return false;
	}

	$post = get_queried_object();
	if (!$post instanceof WP_Post) {
		return false;
	}

	if (has_shortcode($post->post_content, 'wp-2fa-setup-form')) {
		return true;
	}

	if (class_exists('\WP2FA\WP2FA')) {
		$page_id = (int) \WP2FA\WP2FA::get_wp2fa_setting('custom-user-page-id');
		if ($page_id > 0 && (int) $post->ID === $page_id) {
			return true;
		}
	}

	return (bool) preg_match('/(?:^|-)2fa(?:-|$)/i', (string) $post->post_name);
}

/**
 * Intro copy for the 2FA setup hero.
 *
 * @return string
 */
function trimvia_get_2fa_page_intro_text()
{
	$default = __('Add two-factor authentication to strengthen the security of your user account.', 'theme-woopm-child');

	if (class_exists('\WP2FA\WP2FA')) {
		$plugin_text = \WP2FA\WP2FA::get_wp2fa_white_label_setting('user-profile-form-preamble-desc', true);
		if (is_string($plugin_text) && '' !== trim(wp_strip_all_tags($plugin_text))) {
			return trim(wp_strip_all_tags($plugin_text));
		}
	}

	return $default;
}

/**
 * Add page-specific body classes.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function trimvia_child_body_classes($classes)
{
	if (is_page('consultation')) {
		$classes[] = 'consultation-page';
		$classes[] = 'cflp-multistep-v2';
	}
	if (is_page_template('page-templates/services-trimvia.php')) {
		$classes[] = 'trimvia-services-archive-page';
	}
	if (is_post_type_archive('service')) {
		$classes[] = 'trimvia-services-archive-archive';
	}
	if (function_exists('is_product') && is_product()) {
		$classes[] = 'trimvia-single-product-page';
	}
	if (function_exists('is_account_page') && is_account_page()) {
		$classes[] = 'trimvia-account-page';
	}
	if (trimvia_is_wp2fa_setup_page()) {
		$classes[] = 'trimvia-2fa-page';
		$classes[] = 'trimvia-account-page';
	}
	if (function_exists('is_cart') && is_cart()) {
		$classes[] = 'trimvia-cart-page';
	}
	if (function_exists('is_checkout') && is_checkout() && !(function_exists('is_order_received_page') && is_order_received_page())) {
		$classes[] = 'trimvia-checkout-page';
	}
	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay')) {
		$classes[] = 'trimvia-order-pay-page';
	}
	if (function_exists('is_order_received_page') && is_order_received_page()) {
		$classes[] = 'trimvia-order-received-page';
	}
	if (is_tax('condition')) {
		$classes[] = 'trimvia-condition-page';
	}
	if (is_page('all-conditions')) {
		$classes[] = 'trimvia-all-conditions-page';
	}
	return $classes;
}
add_filter('body_class', 'trimvia_child_body_classes');

/**
 * Replace WooPW bootstrap registration notice with Trimvia auth card styling.
 */
function trimvia_registration_login_notice()
{
	$notice_text = get_option('woopw_registration_notice');
	$text        = $notice_text
		? wp_kses_post($notice_text)
		: esc_html__('Please take 2 minutes to sign up in order to view & start an online consultation.', 'woopw');
	?>
	<div class="trimvia-auth-notice woop-notice" role="status">
		<div class="trimvia-auth-notice-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
		</div>
		<p><?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses/escaped above. ?></p>
	</div>
	<?php
}

/**
 * Swap WooPW login notice output on the guest account page.
 */
function trimvia_replace_woopw_registration_notice()
{
	if (!function_exists('is_account_page') || !is_account_page() || is_user_logged_in()) {
		return;
	}

	global $wp_filter;

	if (!isset($wp_filter['woocommerce_before_customer_login_form'])) {
		return;
	}

	foreach ($wp_filter['woocommerce_before_customer_login_form']->callbacks as $priority => $callbacks) {
		foreach ($callbacks as $callback) {
			if (
				!empty($callback['function'])
				&& is_array($callback['function'])
				&& is_object($callback['function'][0])
				&& 'render_text_before_form' === $callback['function'][1]
			) {
				remove_action('woocommerce_before_customer_login_form', $callback['function'], (int) $priority);
			}
		}
	}

	add_action('woocommerce_before_customer_login_form', 'trimvia_registration_login_notice', 10);
}
add_action('wp', 'trimvia_replace_woopw_registration_notice', 20);

/**
 * Read a billing/shipping field from a customer or order object.
 *
 * @param WC_Customer|WC_Order $source  Data source.
 * @param string               $type    Address type.
 * @param string               $field   Field suffix.
 * @return string
 */
function trimvia_get_address_field_value($source, $type, $field)
{
	$method = 'get_' . $type . '_' . $field;

	if (is_object($source) && is_callable(array($source, $method))) {
		return trim((string) $source->$method());
	}

	return '';
}

/**
 * Build structured address rows for Trimvia account/order layouts.
 *
 * @param WC_Customer|WC_Order $source Address source.
 * @param string               $type   billing|shipping.
 * @return array{name:string,address_lines:string[],phone:string,email:string}
 */
function trimvia_get_address_detail_rows($source, $type = 'billing')
{
	$first_name = trimvia_get_address_field_value($source, $type, 'first_name');
	$last_name  = trimvia_get_address_field_value($source, $type, 'last_name');
	$company    = trimvia_get_address_field_value($source, $type, 'company');
	$name       = trim($first_name . ' ' . $last_name);

	if ('' === $name) {
		$name = $company;
		$company = '';
	}

	$address_lines = array_filter(
		array(
			$company,
			trimvia_get_address_field_value($source, $type, 'address_1'),
			trimvia_get_address_field_value($source, $type, 'address_2'),
			trimvia_get_address_field_value($source, $type, 'city'),
			trimvia_get_address_field_value($source, $type, 'state'),
			trimvia_get_address_field_value($source, $type, 'postcode'),
		)
	);

	$country_code = trimvia_get_address_field_value($source, $type, 'country');
	if ($country_code && function_exists('WC') && WC()->countries && !empty(WC()->countries->countries[$country_code])) {
		$address_lines[] = WC()->countries->countries[$country_code];
	}

	return array(
		'name'          => $name,
		'address_lines' => array_values($address_lines),
		'phone'         => trimvia_get_address_field_value($source, $type, 'phone'),
		'email'         => 'billing' === $type ? trimvia_get_address_field_value($source, $type, 'email') : '',
	);
}

/**
 * Render address rows with icons for name, address, phone, and email.
 *
 * @param WC_Customer|WC_Order $source Address source.
 * @param string               $type   billing|shipping.
 * @return void
 */
function trimvia_render_address_detail_rows($source, $type = 'billing')
{
	$rows = trimvia_get_address_detail_rows($source, $type);

	$icons = array(
		'name'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'address' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		'phone'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.86.33 1.7.62 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.18a2 2 0 0 1 2.11-.45c.8.29 1.64.5 2.5.62A2 2 0 0 1 22 16.92z"/></svg>',
		'email'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>',
	);
	?>
		<?php if (!empty($rows['name'])) : ?>
			<p class="trimvia-address-line trimvia-address-line--name">
				<span class="trimvia-address-line__icon" aria-hidden="true"><?php echo $icons['name']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="trimvia-address-line__content"><?php echo esc_html($rows['name']); ?></span>
			</p>
		<?php endif; ?>

		<?php if (!empty($rows['address_lines'])) : ?>
			<p class="trimvia-address-line trimvia-address-line--address">
				<span class="trimvia-address-line__icon" aria-hidden="true"><?php echo $icons['address']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="trimvia-address-line__content">
					<?php echo wp_kses_post(implode('<br>', array_map('esc_html', $rows['address_lines']))); ?>
				</span>
			</p>
		<?php endif; ?>

		<?php if (!empty($rows['phone'])) : ?>
			<p class="trimvia-address-line trimvia-address-line--phone woocommerce-customer-details--phone">
				<span class="trimvia-address-line__icon" aria-hidden="true"><?php echo $icons['phone']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="trimvia-address-line__content"><?php echo esc_html($rows['phone']); ?></span>
			</p>
		<?php endif; ?>

		<?php if (!empty($rows['email'])) : ?>
			<p class="trimvia-address-line trimvia-address-line--email woocommerce-customer-details--email">
				<span class="trimvia-address-line__icon" aria-hidden="true"><?php echo $icons['email']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="trimvia-address-line__content"><a href="mailto:<?php echo esc_attr($rows['email']); ?>"><?php echo esc_html($rows['email']); ?></a></span>
			</p>
		<?php endif; ?>
	<?php
}

/**
 * The parent theme wraps cart contents in Bootstrap row/column hooks. The child cart template
 * already provides its own full-width layout, so remove those wrappers on cart pages only.
 */
function trimvia_disable_parent_cart_layout_hooks()
{
	if (!function_exists('is_cart') || !is_cart()) {
		return;
	}

	remove_action('woocommerce_before_cart', 'woo_add_row_open', 20);
	remove_action('woocommerce_before_cart', 'woo_add_primary_column_open', 21);
	remove_action('woocommerce_after_cart_table', 'woo_add_cart_page_div_close', 10);
	remove_action('woocommerce_after_cart_table', 'woo_add_sidebar_wrapper_open', 11);
	remove_action('woocommerce_after_cart', 'woo_add_cart_page_div_close');
	remove_action('woocommerce_after_cart', 'woo_add_cart_page_div_close');
	remove_action('woocommerce_after_cart', 'custom_cross_sells_row', 15);
}
add_action('wp', 'trimvia_disable_parent_cart_layout_hooks', 5);

/**
 * Wrap cart notices in Trimvia markup for consistent icon/text/button styling.
 */
function trimvia_prepare_cart_notices()
{
	if (!function_exists('is_cart') || !is_cart()) {
		return;
	}

	remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
	add_action('woocommerce_before_cart', 'trimvia_output_cart_notices', 10);
}

function trimvia_output_cart_notices()
{
	if (!function_exists('woocommerce_output_all_notices')) {
		return;
	}

	echo '<div class="trimvia-wc-notices trimvia-cart-notices">';
	woocommerce_output_all_notices();
	echo '</div>';
}
add_action('wp', 'trimvia_prepare_cart_notices', 20);

/**
 * Remove specific callbacks from a WooCommerce hook priority bucket.
 *
 * @param string   $hook_name     Hook name.
 * @param int      $priority      Hook priority.
 * @param callable $should_remove Callback predicate.
 * @return void
 */
function trimvia_remove_checkout_hook_callbacks($hook_name, $priority, $should_remove)
{
	global $wp_filter;

	if (!isset($wp_filter[ $hook_name ]->callbacks[ $priority ])) {
		return;
	}

	foreach ($wp_filter[ $hook_name ]->callbacks[ $priority ] as $hook_id => $hook) {
		if ($should_remove($hook['function'])) {
			unset($wp_filter[ $hook_name ]->callbacks[ $priority ][ $hook_id ]);
		}
	}

	if (empty($wp_filter[ $hook_name ]->callbacks[ $priority ])) {
		unset($wp_filter[ $hook_name ]->callbacks[ $priority ]);
	}
}

/**
 * The parent checkout template injects Bootstrap row/column wrappers around the
 * default WooCommerce checkout. The Trimvia checkout template has its own grid,
 * so remove those wrappers on checkout pages only.
 */
function trimvia_disable_parent_checkout_layout_hooks()
{
	if (!function_exists('is_checkout') || !is_checkout()) {
		return;
	}

	remove_action('woocommerce_checkout_before_customer_details', 'woo_add_row_open', 20);
	remove_action('woocommerce_checkout_before_customer_details', 'woo_add_primary_column_open', 21);
	remove_action('woocommerce_checkout_before_order_review_heading', 'woo_add_cart_page_div_close', 16);
	remove_action('woocommerce_checkout_before_order_review_heading', 'woo_add_sidebar_wrapper_open', 17);
	remove_action('woocommerce_after_checkout_form', 'woo_add_cart_page_div_close');
	remove_action('woocommerce_after_checkout_form', 'woo_add_cart_page_div_close');

	// Parent theme outputs shipping methods on this hook; Trimvia renders them once in the sidebar.
	trimvia_remove_checkout_hook_callbacks(
		'woocommerce_checkout_before_order_review_heading',
		10,
		static function ($callback) {
			return $callback instanceof Closure;
		}
	);

	// WooPW registers GP on `new WOOPW_FRONTEND_GP()`, not get_instance(), so remove by method name.
	trimvia_remove_checkout_hook_callbacks(
		'woocommerce_checkout_before_order_review_heading',
		15,
		static function ($callback) {
			return is_array($callback)
				&& isset($callback[1])
				&& 'render_inform_your_gp_section' === $callback[1];
		}
	);
}
add_action('wp', 'trimvia_disable_parent_checkout_layout_hooks', 20);

/**
 * Order pay must submit as a normal POST (WC_Form_Handler::pay_action), not via
 * wc-checkout.js AJAX. The pay form must not use the checkout form classes.
 *
 * @return void
 */
function trimvia_dequeue_checkout_script_on_order_pay()
{
	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay')) {
		wp_dequeue_script('wc-checkout');
	}
}
add_action('wp_enqueue_scripts', 'trimvia_dequeue_checkout_script_on_order_pay', 999999);

/**
 * Output delivery/shipping method options in checkout (parent inc/woocommerce.php flow).
 *
 * @return void
 */
function trimvia_checkout_render_delivery_methods()
{
	if (!function_exists('WC') || !WC()->cart) {
		return;
	}

	if (!WC()->cart->needs_shipping() || !WC()->cart->show_shipping()) {
		return;
	}

	do_action('woocommerce_review_order_before_shipping');

	echo '<div class="trimvia-checkout-delivery-methods checkout-delivery-methods-wrapper">';
	if (function_exists('wc_cart_totals_shipping_html')) {
		wc_cart_totals_shipping_html();
	}
	echo '</div>';

	do_action('woocommerce_review_order_after_shipping');
}
add_action('woocommerce_checkout_before_order_review_heading', 'trimvia_checkout_render_delivery_methods', 10);

/**
 * Shipping method label as name + cost spans (matches basket delivery tabs).
 *
 * Replaces WooCommerce's "Label: £x.xx" text so checkout tabs can align the
 * method name left and price right, same as the basket Order Summary.
 *
 * @param string           $label  Default full label.
 * @param WC_Shipping_Rate $method Shipping rate.
 * @return string
 */
function trimvia_shipping_method_full_label($label, $method)
{
	if (!function_exists('WC') || !WC()->cart) {
		return $label;
	}

	$cost = (float) $method->cost;
	if ($method->taxes && WC()->cart->display_prices_including_tax()) {
		$cost += array_sum(array_map('floatval', (array) $method->taxes));
	}

	$cost_html = $cost > 0 ? wc_price($cost) : esc_html__('Free!', 'theme-woopm-child');

	return '<span class="method-name">' . esc_html($method->get_label()) . '</span><span class="method-cost">' . wp_kses_post($cost_html) . '</span>';
}
add_filter('woocommerce_cart_shipping_method_full_label', 'trimvia_shipping_method_full_label', 20, 2);

/**
 * Whether a shipping method ID is local pickup.
 *
 * @param string $method_id Chosen shipping method ID.
 * @return bool
 */
function trimvia_checkout_is_local_pickup_method($method_id)
{
	$method_id = (string) $method_id;

	return '' !== $method_id && 0 === strpos($method_id, 'local_pickup');
}

/**
 * Whether the currently chosen checkout shipping method is local pickup.
 *
 * @return bool
 */
function trimvia_checkout_chosen_method_is_local_pickup()
{
	if (!function_exists('WC') || !WC()->session) {
		return false;
	}

	$chosen_methods = WC()->session->get('chosen_shipping_methods');

	if (empty($chosen_methods[0])) {
		return false;
	}

	return trimvia_checkout_is_local_pickup_method($chosen_methods[0]);
}

/**
 * Render WooPW GP checkout markup only in the Trimvia checkout panel.
 *
 * @return string
 */
function trimvia_checkout_get_gp_section_markup()
{
	if (!function_exists('WC') || !WC()->session || !class_exists('WOOPW_FRONTEND_GP')) {
		return '';
	}

	$gp_frontend = WOOPW_FRONTEND_GP::get_instance();

	if (!is_object($gp_frontend) || !method_exists($gp_frontend, 'render_inform_your_gp_section')) {
		return '';
	}

	ob_start();
	$gp_frontend->render_inform_your_gp_section();

	return trim((string) ob_get_clean());
}

/**
 * Resolve a logged-in patient's saved GP for checkout.
 *
 * Matches the WooPW admin flow: account default (`_current_gp_details`) first,
 * then the most recent order with `_order_consultation_gp_info`.
 *
 * @param int $user_id User ID. Defaults to current user.
 * @return array<string,mixed>|null {
 *     @type string $source  `account` or `order`.
 *     @type int    $post_id Linked `woo-gp-services` post ID when available.
 *     @type string $name    GP surgery name.
 *     @type string $address GP surgery address.
 *     @type string $email   GP surgery email.
 * }
 */
function trimvia_get_saved_patient_gp($user_id = 0)
{
	$user_id = $user_id ? absint($user_id) : get_current_user_id();
	if (!$user_id) {
		return null;
	}

	$prefix        = function_exists('gp_meta_prefix') ? gp_meta_prefix() : '';
	$current_gp_id = absint(get_user_meta($user_id, '_current_gp_details', true));

	if ($current_gp_id) {
		$gp_post = get_post($current_gp_id);
		if ($gp_post && 'woo-gp-services' === $gp_post->post_type) {
			return array(
				'source'  => 'account',
				'post_id' => $current_gp_id,
				'name'    => sanitize_text_field($gp_post->post_title),
				'address' => sanitize_text_field(get_post_meta($current_gp_id, $prefix . 'address', true)),
				'email'   => sanitize_text_field(get_post_meta($current_gp_id, $prefix . 'email', true)),
			);
		}
	}

	if (!function_exists('wc_get_orders')) {
		return null;
	}

	$orders = wc_get_orders(
		array(
			'limit'       => 10,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'customer_id' => $user_id,
			'return'      => 'objects',
		)
	);

	foreach ($orders as $previous_order) {
		if (!$previous_order instanceof WC_Order) {
			continue;
		}

		$previous_gp = $previous_order->get_meta('_order_consultation_gp_info');
		if (!is_array($previous_gp) || empty($previous_gp['gp_surgery_name'])) {
			continue;
		}

		$name    = sanitize_text_field((string) $previous_gp['gp_surgery_name']);
		$address = isset($previous_gp['gp_surgery_address']) ? sanitize_text_field((string) $previous_gp['gp_surgery_address']) : '';
		$email   = isset($previous_gp['gp_surgery_email']) ? sanitize_text_field((string) $previous_gp['gp_surgery_email']) : '';

		if ('' === trim($name) && '' === trim($address)) {
			continue;
		}

		$post_id = 0;
		if ('' !== $address) {
			$existing_posts = get_posts(
				array(
					'post_type'      => 'woo-gp-services',
					'post_status'    => array('draft', 'publish', 'private'),
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => $prefix . 'address',
							'value'   => $address,
							'compare' => '=',
						),
					),
				)
			);

			if (!empty($existing_posts[0])) {
				$post_id = absint($existing_posts[0]);
			}
		}

		return array(
			'source'  => 'order',
			'post_id' => $post_id,
			'name'    => $name,
			'address' => $address,
			'email'   => $email,
		);
	}

	return null;
}

/**
 * Persist saved/previous GP details when WooPW cannot resolve `_current_gp_details`.
 *
 * @param WC_Order        $order Order object.
 * @param array<string,mixed> $data  Posted checkout data.
 * @return void
 */
function trimvia_checkout_save_saved_gp_details($order, $data)
{
	unset($data);

	if (empty($_POST['inform_gp']) || 'yes' !== $_POST['inform_gp']) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if (empty($_POST['gp_surgery']) || 'current' !== $_POST['gp_surgery']) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if (empty($_POST['gp_surgery_consent'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if (!$order instanceof WC_Order) {
		return;
	}

	if ($order->get_meta('_order_consultation_gp_info')) {
		return;
	}

	$user_id = absint($order->get_user_id());
	if (!$user_id) {
		return;
	}

	$saved_gp = trimvia_get_saved_patient_gp($user_id);
	if (!$saved_gp || ('' === trim($saved_gp['name']) && '' === trim($saved_gp['address']))) {
		return;
	}

	$consent = sanitize_text_field(wp_unslash($_POST['gp_surgery_consent'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$gp_data = array(
		'gp_surgery_name'    => $saved_gp['name'],
		'gp_surgery_email'   => $saved_gp['email'],
		'gp_surgery_address' => $saved_gp['address'],
		'gp_consent'         => $consent,
	);

	$order->update_meta_data('_order_consultation_gp_info', $gp_data);

	$user = get_userdata($user_id);
	$note = sprintf(
		'Patient\'s GP details are currently set to their saved details:<br/>GP Surgery Name: %1$s<br/>Address: %2$s',
		esc_html($gp_data['gp_surgery_name']),
		esc_html($gp_data['gp_surgery_address'])
	);

	$order->add_meta_data(
		'_order_gp_notification_note',
		array(
			'note'    => $note,
			'user_id' => $user_id,
			'user'    => $user ? trim($user->first_name . ' ' . $user->last_name) : '',
			'sent_on' => time(),
		)
	);
	$order->update_meta_data('_order_gp_automatic_send', 1);

	if (!empty($saved_gp['post_id'])) {
		update_user_meta($user_id, '_current_gp_details', absint($saved_gp['post_id']));
	}
}
add_action('woocommerce_checkout_create_order', 'trimvia_checkout_save_saved_gp_details', 11, 2);

/**
 * Collect a separate delivery address only when a ship-to method is selected.
 *
 * @param bool $checked Default checked state.
 * @return bool
 */
function trimvia_checkout_ship_to_different_address_checked($checked)
{
	if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url()) {
		return !trimvia_checkout_chosen_method_is_local_pickup();
	}

	return (bool) $checked;
}
add_filter('woocommerce_ship_to_different_address_checked', 'trimvia_checkout_ship_to_different_address_checked', 20);

/**
 * Use explicit autocomplete tokens so payment autofill does not populate delivery fields.
 *
 * @param array<string, array<string, mixed>> $fields Checkout fields.
 * @return array<string, array<string, mixed>>
 */
function trimvia_checkout_field_autocomplete($fields)
{
	if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url()) {
		return $fields;
	}

	$autocomplete_map = array(
		'billing_first_name'  => 'billing given-name',
		'billing_last_name'   => 'billing family-name',
		'billing_company'     => 'billing organization',
		'billing_address_1'   => 'billing address-line1',
		'billing_address_2'   => 'billing address-line2',
		'billing_city'        => 'billing address-level2',
		'billing_state'       => 'billing address-level1',
		'billing_postcode'    => 'billing postal-code',
		'billing_country'     => 'billing country',
		'billing_phone'       => 'billing tel',
		'billing_email'       => 'billing email',
		'shipping_first_name' => 'shipping given-name',
		'shipping_last_name'  => 'shipping family-name',
		'shipping_company'    => 'shipping organization',
		'shipping_address_1'  => 'shipping address-line1',
		'shipping_address_2'  => 'shipping address-line2',
		'shipping_city'       => 'shipping address-level2',
		'shipping_state'      => 'shipping address-level1',
		'shipping_postcode'   => 'shipping postal-code',
		'shipping_country'    => 'shipping country',
	);

	foreach ($autocomplete_map as $field_key => $token) {
		$group = 0 === strpos($field_key, 'shipping_') ? 'shipping' : 'billing';

		if (isset($fields[ $group ][ $field_key ])) {
			$fields[ $group ][ $field_key ]['autocomplete'] = $token;
		}
	}

	return $fields;
}
add_filter('woocommerce_checkout_fields', 'trimvia_checkout_field_autocomplete', 20);

/**
 * Remove Woo sidebar on shop/catalog listing pages in child layouts.
 */
function trimvia_disable_woo_shop_sidebar()
{
	if (!function_exists('is_shop') || !function_exists('is_product_taxonomy')) {
		return;
	}

	if (is_shop() || is_product_taxonomy() || is_post_type_archive('product')) {
		remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
	}
}
add_action('wp', 'trimvia_disable_woo_shop_sidebar', 20);

/**
 * Render cart cross-sells with the same card design used on the Shop page.
 */
function trimvia_cart_cross_sells_row()
{
	if (!function_exists('is_cart') || !is_cart() || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
		return;
	}

	$cross_sell_ids = array_values(array_filter(array_map('absint', WC()->cart->get_cross_sells())));
	if (empty($cross_sell_ids) || !function_exists('wc_get_products')) {
		return;
	}

	$cross_sell_products = wc_get_products(
		array(
			'include'            => $cross_sell_ids,
			'status'             => 'publish',
			'limit'              => 3,
			'orderby'            => 'include',
			'catalog_visibility' => 'visible',
		)
	);

	if (empty($cross_sell_products)) {
		return;
	}
	?>
	<section class="page-section trimvia-cart-cross-sells">
		<div class="container">
			<div class="shop-header rv">
				<div>
					<h2 class="stitle"><?php esc_html_e('You may also be interested in', 'woocommerce'); ?></h2>
					<span class="shop-count"><?php esc_html_e('Recommended treatments from our shop', 'theme-woopm-child'); ?></span>
				</div>
			</div>
			<div class="shop-grid">
				<?php foreach ($cross_sell_products as $cross_sell_product) : ?>
					<?php
					if (!$cross_sell_product instanceof WC_Product) {
						continue;
					}

					get_template_part('template-parts/trimvia', 'shop-product-card', array('product' => $cross_sell_product));
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}
add_action('woocommerce_after_cart', 'trimvia_cart_cross_sells_row', 15);

/**
 * Allowed SVG tags for small inline account navigation icons.
 *
 * @return array<string,array<string,bool>>
 */
function trimvia_account_allowed_svg()
{
	return array(
		'svg' => array(
			'viewbox' => true,
			'viewBox' => true,
			'fill' => true,
			'stroke' => true,
			'aria-hidden' => true,
			'class' => true,
			'width' => true,
			'height' => true,
		),
		'path' => array(
			'd' => true,
			'fill' => true,
			'stroke' => true,
		),
		'polyline' => array(
			'points' => true,
			'fill' => true,
			'stroke' => true,
		),
		'line' => array(
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
			'stroke' => true,
		),
		'circle' => array(
			'cx' => true,
			'cy' => true,
			'r' => true,
			'fill' => true,
			'stroke' => true,
		),
		'rect' => array(
			'x' => true,
			'y' => true,
			'width' => true,
			'height' => true,
			'rx' => true,
			'fill' => true,
			'stroke' => true,
		),
	);
}

/**
 * Resolve My Account navigation URL for standard and plugin card menu items.
 *
 * WooPW plugin cards (e.g. patient-history, prescription-upload) store button links
 * in the menu item array. When the child theme registers a matching WooCommerce
 * endpoint (see prescription-upload block in functions.php), this helper prefers
 * wc_get_account_endpoint_url() so links stay under /my-account/.
 *
 * @param string       $endpoint Menu endpoint key.
 * @param string|array $nav_item Menu label or plugin card definition.
 * @return string
 */
function trimvia_get_account_menu_item_url($endpoint, $nav_item)
{
	if ('dashboard' === $endpoint) {
		return wc_get_page_permalink('myaccount');
	}

	if (function_exists('WC') && WC()->query) {
		$query_vars = WC()->query->get_query_vars();
		if (isset($query_vars[$endpoint])) {
			return wc_get_account_endpoint_url($endpoint);
		}
	}

	if (is_array($nav_item) && !empty($nav_item['buttons']) && is_array($nav_item['buttons'])) {
		foreach ($nav_item['buttons'] as $button) {
			if (!empty($button['link'])) {
				return $button['link'];
			}
		}
	}

	return wc_get_account_endpoint_url($endpoint);
}

/**
 * Patient Prescription Upload (WooPW add-on) â€” DISABLED FOR NOW.
 *
 * Flow when enabled:
 * 1. WooPW keeps this add-on off unless `enable_patient_rx_upload` returns true
 *    (see Plugin/woopw/includes/addons/class-addon-manager.php).
 * 2. When on, Addon Manager loads patient-rx-upload/, which registers the
 *    [woopw_patient_prescription_upload] shortcode and upload form handlers.
 * 3. WooPW patient dashboard always adds a "Prescription Upload" My Account menu
 *    card (Plugin/woopw/includes/frontend/class-frontend-patient-dashboard.php).
 * 4. This child theme normally registers /my-account/prescription-upload/ and
 *    renders that shortcode on the endpoint instead of a separate WP page.
 * 5. woocommerce/myaccount/navigation.php supplies the sidebar icon for that item.
 *
 * Turned off because the endpoint only showed the raw shortcode text (add-on UI
 * not rendering). To re-enable: uncomment the block below and visit Settings â†’
 * Permalinks once so rewrite rules refresh.
 */
// add_filter('enable_patient_rx_upload', '__return_true');
//
// function trimvia_register_prescription_upload_endpoint()
// {
// 	add_rewrite_endpoint('prescription-upload', EP_ROOT | EP_PAGES);
// }
// add_action('init', 'trimvia_register_prescription_upload_endpoint', 20);
//
// function trimvia_add_prescription_upload_query_var($vars)
// {
// 	$vars['prescription-upload'] = 'prescription-upload';
// 	return $vars;
// }
// add_filter('woocommerce_get_query_vars', 'trimvia_add_prescription_upload_query_var');
//
// function trimvia_render_prescription_upload_endpoint()
// {
// 	echo '<div class="trimvia-prescription-upload">';
//
// 	if (shortcode_exists('woopw_patient_prescription_upload')) {
// 		echo do_shortcode('[woopw_patient_prescription_upload]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
// 	} else {
// 		echo '<p class="woocommerce-info">' . esc_html__('Prescription upload is unavailable right now. Please contact the care team for help.', 'theme-woopm-child') . '</p>';
// 	}
//
// 	echo '</div>';
// }
// add_action('woocommerce_account_prescription-upload_endpoint', 'trimvia_render_prescription_upload_endpoint');

/**
 * Hide Prescription Upload from My Account while the add-on is disabled above.
 *
 * WooPW still registers the menu card even when the add-on is off; remove it so
 * patients are not sent to a page that only prints the shortcode.
 *
 * @param array<string,mixed> $items Account menu items.
 * @return array<string,mixed>
 */
function trimvia_remove_prescription_upload_menu_item($items)
{
	unset($items['prescription-upload']);

	return $items;
}
add_filter('woocommerce_account_menu_items', 'trimvia_remove_prescription_upload_menu_item', 20);

/**
 * Prescriber My Account sidebar: Dashboard first, then Prescriber Orders Dashboard.
 *
 * WooPW removes the default dashboard item; restore it so prescribers can return
 * to the main account overview before the practitioner orders endpoint.
 *
 * @param array<string,mixed> $items Account menu items.
 * @return array<string,mixed>
 */
function trimvia_prescriber_account_menu_items($items)
{
	if (!trimvia_user_has_prescriber_access()) {
		return $items;
	}

	unset($items['dashboard']);

	$ordered = array(
		'dashboard' => __('Dashboard', 'theme-woopm-child'),
	);

	if (isset($items['practitioner-orders'])) {
		$ordered['practitioner-orders'] = $items['practitioner-orders'];
		unset($items['practitioner-orders']);
	}

	if (!isset($items['reset-pin-request'])) {
		$items['reset-pin-request'] = array(
			'title'       => __('Reset Prescriber Pin', 'woocommerce'),
			'description' => __('Want to reset your prescription approval pin?', 'woocommerce'),
			'buttons'     => array(
				array(
					'title' => __('Reset Pin', 'woocommerce'),
					'class' => array('theme-btn'),
				),
			),
		);
	}

	return $ordered + $items;
}
add_filter('woocommerce_account_menu_items', 'trimvia_prescriber_account_menu_items', 25);

/**
 * Shared card section opener for edit-account clinical fields.
 *
 * @param string $title Section title.
 * @param string $description Optional helper text.
 * @param string $icon_svg Inline SVG markup.
 */
function trimvia_account_form_section_open($title, $description, $icon_svg)
{
	?>
	<div class="trimvia-account-form-section">
		<div class="trimvia-account-form-section-head">
			<span class="trimvia-account-form-section-icon" aria-hidden="true">
				<?php echo wp_kses($icon_svg, trimvia_account_allowed_svg()); ?>
			</span>
			<div class="trimvia-account-form-section-copy">
				<h3><?php echo esc_html($title); ?></h3>
				<?php if ($description) : ?>
					<p><?php echo esc_html($description); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="trimvia-account-form-section-body">
	<?php
}

/**
 * Close a Trimvia account form section card.
 */
function trimvia_account_form_section_close()
{
	echo '</div></div>';
}

/**
 * Swap WooPW edit-account field markup for Trimvia card sections.
 */
function trimvia_replace_woopw_edit_account_fields()
{
	global $wp_filter, $trimvia_woopw_registration;

	if (!isset($wp_filter['woocommerce_edit_account_form'])) {
		return;
	}

	foreach ($wp_filter['woocommerce_edit_account_form']->callbacks as $priority => $callbacks) {
		foreach ($callbacks as $callback) {
			if (
				!empty($callback['function'])
				&& is_array($callback['function'])
				&& is_object($callback['function'][0])
				&& 'render_edit_account_fields' === $callback['function'][1]
			) {
				$trimvia_woopw_registration = $callback['function'][0];
				remove_action('woocommerce_edit_account_form', $callback['function'], (int) $priority);
				add_action('woocommerce_edit_account_form', 'trimvia_render_edit_account_fields', (int) $priority);
				return;
			}
		}
	}
}
add_action('wp', 'trimvia_replace_woopw_edit_account_fields', 1);

/**
 * Render Trimvia-styled WooPW fields on the edit account form.
 */
function trimvia_render_edit_account_fields()
{
	global $trimvia_woopw_registration;

	$user = get_user_by('id', get_current_user_id());
	if (!$user instanceof WP_User) {
		return;
	}

	if (current_user_can('prescriber')) {
		$medical_body = array('NMC', 'GMC', 'GDC', 'GPhC', 'Other');
		?>
		<div class="trimvia-edit-account-grid">
			<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
				<label for="prescriber_medical_body"><?php esc_html_e('Medical Body', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
				<select class="woocommerce-select woo-input-select" name="prescriber_medical_body" id="prescriber_medical_body">
					<option value=""><?php esc_html_e('Select Option', 'woopw'); ?></option>
					<?php foreach ($medical_body as $val) : ?>
						<option value="<?php echo esc_attr($val); ?>" <?php selected(get_user_meta($user->ID, 'prescriber_medical_body', true), $val); ?>><?php echo esc_html($val); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
				<label for="prescriber_reg_number"><?php esc_html_e('Registration Number', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="prescriber_reg_number" id="prescriber_reg_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'prescriber_reg_number', true)); ?>" />
			</p>
		</div>
		<?php
	}

	if (!in_array('customer', (array) $user->roles, true)) {
		return;
	}

	$is_patient_id_optional = get_option('optional_registration_id');
	$patient_dob            = get_user_meta($user->ID, 'patient_dob', true);
	$patient_gp             = get_user_meta($user->ID, '_current_gp_details', true);
	$patient_id             = get_user_meta($user->ID, 'patient_id', true);

	trimvia_account_form_section_open(
		__('Date of birth', 'theme-woopm-child'),
		$patient_dob ? __('Your date of birth is stored securely for clinical checks.', 'theme-woopm-child') : __('Required for safe prescribing and age-appropriate treatments.', 'theme-woopm-child'),
		'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
	);
	?>
	<p class="woocommerce-form-row form-row form-row-wide trimvia-account-dob-row">
		<label for="patient_dob_display">
			<?php esc_html_e('Date of Birth', 'woopw'); ?>
			<?php if (!$patient_dob) : ?>
				&nbsp;<span class="required" aria-hidden="true">*</span>
			<?php endif; ?>
		</label>
		<?php if ($patient_dob) : ?>
			<input class="input-text" type="text" id="patient_dob_display" readonly value="<?php echo esc_attr(date_i18n('F j, Y', strtotime($patient_dob))); ?>" />
		<?php elseif ($trimvia_woopw_registration && method_exists($trimvia_woopw_registration, 'render_dob_dropdown')) : ?>
			<div class="trimvia-account-dob-fields custom-date-input">
				<?php $trimvia_woopw_registration->render_dob_dropdown(); ?>
			</div>
		<?php endif; ?>
	</p>
	<?php
	trimvia_account_form_section_close();

	trimvia_account_form_section_open(
		__('Identification', 'theme-woopm-child'),
		__('Upload a clear photo or scan of your ID so our clinical team can verify your account.', 'theme-woopm-child'),
		'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2M15 12h2M7 16h10"/></svg>'
	);

	if (!empty($patient_id) && is_array($patient_id)) {
		?>
		<div class="trimvia-account-id-gallery trimvia-upload-gallery">
			<?php foreach ($patient_id as $image) : ?>
				<?php
				if (empty($image['path']) || !is_readable($image['path'])) {
					continue;
				}
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$type  = $finfo->file($image['path']);
				if (!in_array($type, array('image/png', 'image/jpeg', 'image/jpg', 'image/webp'), true)) {
					continue;
				}
				$data_base64 = 'data:' . $type . ';base64,' . base64_encode((string) file_get_contents($image['path']));
				?>
				<figure class="trimvia-upload-gallery__item">
					<div class="trimvia-upload-gallery__media" style="aspect-ratio: 4 / 3; background: var(--off-white); display: flex; align-items: center; justify-content: center; overflow: hidden;">
						<img src="<?php echo esc_attr($data_base64); ?>" alt="<?php echo esc_attr($image['file'] ?? __('ID image', 'theme-woopm-child')); ?>" style="width: 100%; height: 100%; object-fit: contain; display: block;" />
					</div>
					<?php if (!empty($image['file'])) : ?>
						<figcaption><?php echo esc_html($image['file']); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
		<?php
	}
	?>
	<p class="woocommerce-form-row form-row form-row-wide trimvia-account-file-row">
		<label for="patient_id">
			<?php esc_html_e('Upload new ID image', 'woopw'); ?>
			<?php if (empty($patient_id) && !$is_patient_id_optional) : ?>
				&nbsp;<span class="required" aria-hidden="true">*</span>
			<?php endif; ?>
		</label>
		<input type="file" name="patient_id" id="patient_id" class="trimvia-account-file-input cflp-file-input input-text" <?php echo (empty($patient_id) && !$is_patient_id_optional) ? 'required' : ''; ?> accept="image/*" />
	</p>
	<?php
	trimvia_account_form_section_close();

	trimvia_account_form_section_open(
		__('GP details', 'theme-woopm-child'),
		__('Select your GP surgery so we can share clinical correspondence when required.', 'theme-woopm-child'),
		'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>'
	);

	if (function_exists('wc_render_all_gps_select_field')) {
		echo '<div class="trimvia-account-gp-field">';
		ob_start();
		wc_render_all_gps_select_field(
			$patient_gp,
			__('Select your GP using the field below, if it is not currently correct:', 'woopw')
		);
		$gp_field_html = ob_get_clean();
		$gp_field_html = str_replace('class="mb-3"', 'class="trimvia-account-gp-select-wrap"', $gp_field_html);
		$gp_field_html = str_replace('class="chosen-select"', 'class="chosen-select trimvia-account-gp-select"', $gp_field_html);
		echo $gp_field_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin helper output.
		echo '</div>';
	}
	trimvia_account_form_section_close();
}

/**
 * First line-item product thumbnail for account order cards.
 *
 * @param WC_Order $order Order object.
 * @param string   $size  Image size.
 * @return string HTML image markup or empty string.
 */
function trimvia_get_order_primary_product_thumbnail(WC_Order $order, $size = 'woocommerce_thumbnail')
{
	if (!$order instanceof WC_Order) {
		return '';
	}

	foreach ($order->get_items() as $item) {
		if (!$item instanceof WC_Order_Item_Product) {
			continue;
		}

		$product = $item->get_product();
		if (!$product instanceof WC_Product) {
			continue;
		}

		$image = $product->get_image(
			$size,
			array(
				'class' => 'account-order-thumb__img',
				'alt'   => $product->get_name(),
			)
		);

		if ($image) {
			return $image;
		}
	}

	return wc_placeholder_img(
		$size,
		array(
			'class' => 'account-order-thumb__img account-order-thumb__img--placeholder',
			'alt'   => '',
		)
	);
}

/**
 * Order statuses that qualify for the My Account reorder action.
 *
 * WooPW only adds reorder for completed/recalled. Trimvia/POM orders often sit in
 * prescriber-approved statuses before or instead of core "completed".
 *
 * @return string[]
 */
function trimvia_get_reorder_eligible_order_statuses()
{
	return apply_filters(
		'trimvia_reorder_eligible_order_statuses',
		array(
			'completed',
			'recalled',
		)
	);
}

/**
 * Collect condition term IDs stored on a POM order.
 *
 * @param WC_Order $order Order object.
 * @return int[]
 */
function trimvia_get_order_condition_ids(WC_Order $order)
{
	$condition_ids = array();

	if (!$order instanceof WC_Order) {
		return $condition_ids;
	}

	if (function_exists('woopw_get_new_order_conditions') && function_exists('woopw_get_reordered_conditions')) {
		$new_conditions       = woopw_get_new_order_conditions($order->get_id());
		$reordered_conditions = woopw_get_reordered_conditions($order->get_id());

		if (is_array($new_conditions)) {
			$condition_ids = array_merge($condition_ids, $new_conditions);
		}

		if (is_array($reordered_conditions)) {
			$condition_ids = array_merge($condition_ids, $reordered_conditions);
		}
	}

	if (function_exists('trimvia_normalise_order_condition_ids')) {
		$condition_ids = array_merge(
			$condition_ids,
			trimvia_normalise_order_condition_ids($order->get_meta('_order_conditions')),
			trimvia_normalise_order_condition_ids($order->get_meta('_order_conditions_reorder'))
		);

		foreach (array('_order_conditions', '_order_conditions_reorder') as $meta_key) {
			$meta_values = $order->get_meta($meta_key, false);
			if (!is_array($meta_values)) {
				continue;
			}

			foreach ($meta_values as $meta_value) {
				if ($meta_value instanceof WC_Meta_Data) {
					$meta_value = $meta_value->get_data()['value'];
				}

				$condition_ids = array_merge(
					$condition_ids,
					trimvia_normalise_order_condition_ids($meta_value)
				);
			}
		}
	}

	$form_data = $order->get_meta('_cflp_form_data');
	if (!empty($form_data)) {
		if (!is_array($form_data)) {
			$form_data = maybe_unserialize($form_data);
		}

		if (is_array($form_data)) {
			foreach ($form_data as $entry) {
				if (!is_array($entry) || empty($entry['condition_id'])) {
					continue;
				}

				$condition_ids[] = absint($entry['condition_id']);
			}
		}
	}

	if (function_exists('woopw_check_this_order_conditions')) {
		$product_conditions = woopw_check_this_order_conditions((int) $order->get_id(), (int) $order->get_user_id());
		if (is_array($product_conditions) && !isset($product_conditions['error'])) {
			$condition_ids = array_merge($condition_ids, $product_conditions);
		}
	}

	$condition_ids = array_filter(array_map('absint', $condition_ids));

	return array_values(array_unique($condition_ids));
}

/**
 * Base URL for the returning-patient reorder consultation flow.
 *
 * @return string
 */
function trimvia_get_reorder_consultation_url()
{
	$consultation_page = get_page_by_path('consultation');

	if ($consultation_page instanceof WP_Post) {
		return get_permalink($consultation_page);
	}

	return site_url('/consultation/');
}

/**
 * Build nested re-order actions for My Account > Orders (parent/WooPW format).
 *
 * @param WC_Order $order Order object.
 * @return array<int, array{url:string,name:string}>
 */
function trimvia_build_account_order_reorder_actions(WC_Order $order)
{
	$reorder_actions = array();

	if (!$order instanceof WC_Order || !is_user_logged_in()) {
		return $reorder_actions;
	}

	if (!$order->has_status(trimvia_get_reorder_eligible_order_statuses())) {
		return $reorder_actions;
	}

	$condition_ids    = trimvia_get_order_condition_ids($order);
	$consultation_url = trimvia_get_reorder_consultation_url();

	foreach ($condition_ids as $condition_id) {
		$condition = get_term_by('id', $condition_id, 'condition');
		if (!$condition || is_wp_error($condition)) {
			continue;
		}

		$reorder_actions[] = array(
			'url'  => add_query_arg(
				array(
					'condition-slug' => $condition->slug,
					'order_id'       => $order->get_id(),
					'is_reorder'     => 1,
				),
				$consultation_url
			),
			'name' => sprintf(
				/* translators: %s: condition name */
				__('Re Order - %s', 'theme-woopm-child'),
				$condition->name
			),
		);
	}

	return $reorder_actions;
}

/**
 * Ensure reorder actions appear for eligible POM orders on My Account > Orders.
 *
 * Runs after WooPW (priority 10) and only fills in when reorder is still missing.
 *
 * @param array<string,mixed> $actions Order actions.
 * @param WC_Order            $order   Order object.
 * @return array<string,mixed>
 */
function trimvia_ensure_account_order_reorder_actions($actions, $order)
{
	if (!$order instanceof WC_Order || !is_user_logged_in()) {
		return $actions;
	}

	if (isset($actions['re-order']) && is_array($actions['re-order']) && empty($actions['re-order'])) {
		unset($actions['re-order']);
	}

	$reorder_actions = trimvia_build_account_order_reorder_actions($order);
	if (empty($reorder_actions)) {
		return $actions;
	}

	$actions['re-order'] = $reorder_actions;

	return $actions;
}
add_filter('woocommerce_my_account_my_orders_actions', 'trimvia_ensure_account_order_reorder_actions', 15, 2);

/**
 * Whether an order must use the WooPW reorder consultation flow
 * instead of WooCommerce's cart "order again" shortcut.
 *
 * @param WC_Order $order Order object.
 * @return bool
 */
function trimvia_order_uses_consultation_reorder_flow(WC_Order $order)
{
	if (!$order instanceof WC_Order) {
		return false;
	}

	return !empty(trimvia_get_order_condition_ids($order));
}

/**
 * Replace WooCommerce "Order again" on view/thank-you pages with reorder consultation links.
 *
 * Parent/WooPW flow: /consultation/?condition-slug=…&order_id=…&is_reorder=1
 * WC default adds items straight to cart and skips the reorder questionnaire.
 */
function trimvia_register_view_order_reorder_flow()
{
	remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
	add_action('woocommerce_order_details_after_order_table', 'trimvia_render_view_order_reorder_buttons', 10);
}
add_action('init', 'trimvia_register_view_order_reorder_flow');

/**
 * Disable WC order-again for consultation/POM orders (button + handler).
 *
 * @param string[]       $statuses Valid statuses.
 * @param WC_Order|null  $order    Order object (not passed on all WooCommerce versions).
 * @return string[]
 */
function trimvia_filter_order_again_statuses_for_consultation_orders($statuses, $order = null)
{
	if ($order instanceof WC_Order && trimvia_order_uses_consultation_reorder_flow($order)) {
		return array();
	}

	return $statuses;
}
add_filter('woocommerce_valid_order_statuses_for_order_again', 'trimvia_filter_order_again_statuses_for_consultation_orders', 10, 2);

/**
 * Redirect legacy WC order-again URLs to the reorder consultation form.
 */
function trimvia_redirect_wc_order_again_to_consultation_reorder()
{
	if (!isset($_GET['order_again'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$order_id = absint(wp_unslash($_GET['order_again'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ($order_id <= 0) {
		return;
	}

	$order = wc_get_order($order_id);
	if (!$order instanceof WC_Order || !trimvia_order_uses_consultation_reorder_flow($order)) {
		return;
	}

	if (!is_user_logged_in() || (int) $order->get_user_id() !== get_current_user_id()) {
		return;
	}

	$reorder_actions = trimvia_build_account_order_reorder_actions($order);
	if (empty($reorder_actions[0]['url'])) {
		return;
	}

	wp_safe_redirect($reorder_actions[0]['url']);
	exit;
}
add_action('wp_loaded', 'trimvia_redirect_wc_order_again_to_consultation_reorder', 5);

/**
 * Render reorder consultation buttons on view order / thank you (parent/WooPW format).
 *
 * @param WC_Order|int $order Order object or ID.
 */
function trimvia_render_view_order_reorder_buttons($order)
{
	if (!$order instanceof WC_Order) {
		$order = wc_get_order($order);
	}

	if (!$order instanceof WC_Order) {
		return;
	}

	if ((int) $order->get_user_id() !== get_current_user_id()) {
		return;
	}

	$reorder_actions = trimvia_build_account_order_reorder_actions($order);

	if (!empty($reorder_actions)) {
		echo '<p class="order-again trimvia-view-order-reorder">';

		foreach ($reorder_actions as $reorder_action) {
			if (empty($reorder_action['url']) || empty($reorder_action['name'])) {
				continue;
			}

			printf(
				'<a href="%s" class="woocommerce-button button btn-accent trimvia-view-order-reorder-btn re-order">%s</a>',
				esc_url($reorder_action['url']),
				esc_html($reorder_action['name'])
			);
		}

		echo '</p>';
		return;
	}

	if (function_exists('woocommerce_order_again_button')) {
		woocommerce_order_again_button($order);
	}
}

/**
 * Allow guest order-received / thank-you pages to stream consultation uploads.
 *
 * WooPW registers wp_ajax only; guests viewing their consultation modal need nopriv too.
 */
function trimvia_register_consultation_file_stream_for_guests()
{
	if (function_exists('woopw_stream_consultation_file')) {
		add_action('wp_ajax_nopriv_woopw_stream_consultation_file', 'woopw_stream_consultation_file');
	}
}
add_action('init', 'trimvia_register_consultation_file_stream_for_guests', 20);

/**
 * Paginate My Account > Orders (WooCommerce default is often -1 / no pagination).
 *
 * @param array<string,mixed> $args Order query args.
 * @return array<string,mixed>
 */
function trimvia_account_orders_pagination_query($args)
{
	$args['paginate'] = true;
	$args['limit']    = (int) apply_filters('trimvia_account_orders_per_page', 10);

	return $args;
}
add_filter('woocommerce_my_account_my_orders_query', 'trimvia_account_orders_pagination_query');

/**
 * Replace plugin patient-history markup with Trimvia account card layout.
 */
function trimvia_override_patient_history_endpoint()
{
	remove_all_actions('woocommerce_account_patient-history_endpoint');
	add_action('woocommerce_account_patient-history_endpoint', 'trimvia_render_patient_history_endpoint');
}
add_action('init', 'trimvia_override_patient_history_endpoint', 20);

/**
 * Medical history fields shown on the patient-history account endpoint.
 *
 * @return array<string,array{label:string,icon:string,wide?:bool}>
 */
function trimvia_patient_history_fields()
{
	return array(
		'medical_allergies' => array(
			'label' => __('Allergies (drug and non-drug)', 'theme-woopm-child'),
			'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
		),
		'medical_conditions' => array(
			'label' => __('Relevant medical conditions (chronic, past history)', 'theme-woopm-child'),
			'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
		),
		'medical_contraindications' => array(
			'label' => __('Contraindications / risk factors (pregnancy, renal impairment, etc.)', 'theme-woopm-child'),
			'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
		),
		'medical_medications' => array(
			'label' => __('Medication history (including repeat/ongoing meds not ordered online)', 'theme-woopm-child'),
			'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10.5 20.5L3.5 13.5a4.95 4.95 0 0 1 0-7l.5-.5a4.95 4.95 0 0 1 7 0l1 1"/><path d="M13.5 3.5l7 7a4.95 4.95 0 0 1 0 7l-.5.5a4.95 4.95 0 0 1-7 0l-1-1"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
		),
		'consultation_notes' => array(
			'label' => __('Consultation notes', 'theme-woopm-child'),
			'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
			'wide'  => true,
		),
	);
}

/**
 * Render the My Medication Information account endpoint.
 */
function trimvia_render_patient_history_endpoint()
{
	$user_id = get_current_user_id();

	if (empty($user_id) || ! get_userdata($user_id)) {
		echo '<p>' . esc_html__('No patient data found.', 'theme-woopm-child') . '</p>';
		return;
	}

	$contact_url = home_url('/contact/');
	$fields      = trimvia_patient_history_fields();
	?>
	<div class="trimvia-medication-info">
		<div class="medication-info-intro rv">
			<div class="medication-info-intro-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
			</div>
			<div class="medication-info-intro-text">
				<h2><?php esc_html_e('My Medication Information', 'theme-woopm-child'); ?></h2>
				<p><?php esc_html_e('Clinical records reviewed by our care team during consultations. This information helps us prescribe safely and monitor your treatment.', 'theme-woopm-child'); ?></p>
			</div>
		</div>

		<div class="medication-info-grid">
			<?php foreach ($fields as $meta_key => $field) : ?>
				<?php
				$raw_value   = get_user_meta($user_id, $meta_key, true);
				$content     = function_exists('woopw_get_all_pmr_notes') ? woopw_get_all_pmr_notes($raw_value) : '';
				$has_content = ! empty(trim(wp_strip_all_tags((string) $content)));
				$card_class  = 'medication-info-card rv';

				if (! empty($field['wide'])) {
					$card_class .= ' medication-info-card--wide';
				}
				if (! $has_content) {
					$card_class .= ' medication-info-card--empty';
				}
				?>
				<article class="<?php echo esc_attr($card_class); ?>">
					<header class="medication-info-card-head">
						<span class="medication-info-card-icon" aria-hidden="true">
							<?php echo wp_kses($field['icon'], trimvia_account_allowed_svg()); ?>
						</span>
						<h3><?php echo esc_html($field['label']); ?></h3>
					</header>
					<div class="medication-info-card-body">
						<?php if ($has_content) : ?>
							<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered by woopw_get_all_pmr_notes. ?>
						<?php else : ?>
							<p class="medication-info-empty">
								<?php esc_html_e('No information recorded yet', 'theme-woopm-child'); ?>
							</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="medication-info-footnote rv rv-d2">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: contact page URL */
						__('Information is updated by our clinical team during consultations. If anything needs correcting, <a href="%s">contact the care team</a>.', 'theme-woopm-child'),
						esc_url($contact_url)
					)
				);
				?>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Parent theme registers load_condition_tax_script on wp_enqueue_scripts; it prints inline JS
 * immediately (jQuery not loaded yet) for a non-existent .conditions-slider â€” causes console errors
 * on condition archives. Layout uses the child grid instead.
 */
remove_action('wp_enqueue_scripts', 'load_condition_tax_script', 10);

/**
 * Trimvia single product: strip default WooCommerce summary/gallery hooks so we can render the
 * HTML-theme layout in woocommerce/content-single-product.php.
 *
 * Account/login URLs are unchanged: the child header uses wc_get_page_permalink( 'myaccount' )
 * (Customizer "Secondary Button Link"); parent WooCommerce templates handle my-account forms.
 */
function trimvia_prepare_single_product_hooks()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	if (!apply_filters('trimvia_use_custom_single_product_layout', true)) {
		return;
	}

	remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
	remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);

	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
	remove_action('woocommerce_single_product_summary', array('WC_Structured_Data', 'generate_product_data'), 60);
	remove_action('woocommerce_single_product_summary', 'custom_show_stock_status', 25);

	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('wp', 'trimvia_prepare_single_product_hooks', 20);

/**
 * Parent theme moves variation descriptions into .summary; Trimvia uses a custom layout.
 */
function trimvia_disable_parent_variation_desc_script()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	if (function_exists('woo_modify_wc_variation_desc_position')) {
		remove_action('wp_footer', 'woo_modify_wc_variation_desc_position');
	}
}
add_action('wp', 'trimvia_disable_parent_variation_desc_script', 25);

/**
 * Core wp_die() markup includes a global `body { max-width:700px; â€¦ }` rule. If that stylesheet is ever
 * printed on a normal front-end request (plugin conflict, buffering bug), WooCommerce single product
 * pages look like the grey admin â€œerrorâ€ box. Neutralise those rules only on single product views.
 */
function trimvia_single_product_neutralize_wp_die_styles()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}
	echo '<style id="trimvia-neutralize-wp-die-leak">';
	echo 'html{background:var(--off-white,#f8f9fc)!important;}';
	echo 'body.single-product.trimvia-single-product-page{max-width:none!important;width:100%!important;margin:0!important;padding:0!important;border:none!important;box-shadow:none!important;-webkit-box-shadow:none!important;background:transparent!important;color:inherit!important;font-family:inherit!important;}';
	echo 'body#error-page.single-product.trimvia-single-product-page{max-width:none!important;width:100%!important;margin:0!important;padding:0!important;border:none!important;box-shadow:none!important;-webkit-box-shadow:none!important;background:transparent!important;}';
	echo 'body.single-product.trimvia-single-product-page #error-page{max-width:none!important;margin:0!important;padding:0!important;border:none!important;box-shadow:none!important;background:transparent!important;}';
	echo '</style>';
}
add_action('wp_head', 'trimvia_single_product_neutralize_wp_die_styles', 99999);

/**
 * Single product tabs: mirror HTML prototype â€œTreatment detailsâ€ reliably.
 *
 * - WooCommerce only registers the Description tab when the long description is filled; many products
 *   only use the short description â€” add an Overview tab from short description when needed.
 * - Parent theme (woocommerce-product-tabs.php) removes Additional information at priority 98; restore
 *   it when the product has attributes or dimensions so buyers still see product data.
 * - If nothing else registered any tab, output a minimal Overview panel so the section is never blank.
 */
function trimvia_single_product_tabs_overview_from_short_description($tabs)
{
	if (!function_exists('is_product') || !is_product()) {
		return $tabs;
	}

	$product = wc_get_product(get_queried_object_id());
	if (!$product instanceof WC_Product) {
		return $tabs;
	}

	$long_plain = trim(wp_strip_all_tags(trimvia_single_product_get_long_description_raw((int) $product->get_id())));
	$short_plain = trim(wp_strip_all_tags((string) $product->get_short_description()));

	if ('' === $long_plain && '' !== $short_plain && !isset($tabs['description'])) {
		$tabs['description'] = array(
			'title'    => __('Overview', 'theme-woopm-child'),
			'priority' => 10,
			'callback' => 'trimvia_single_product_tab_render_short_description',
		);
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_tabs_overview_from_short_description', 15);

/**
 * @param string $key Tab key.
 * @param array  $tab Tab args.
 */
function trimvia_single_product_tab_render_short_description($key, $tab)
{
	global $product;

	if (!$product instanceof WC_Product) {
		return;
	}

	$short = trim((string) $product->get_short_description());
	if ('' === $short) {
		return;
	}

	echo '<div class="woocommerce-product-details__short-description">';
	echo wp_kses_post(wpautop(wptexturize($short)));
	echo '</div>';
}

/**
 * @param array $tabs Tabs.
 * @return array
 */
function trimvia_single_product_tabs_restore_additional_information($tabs)
{
	if (!function_exists('is_product') || !is_product()) {
		return $tabs;
	}

	if (isset($tabs['additional_information'])) {
		return $tabs;
	}

	$product = wc_get_product(get_queried_object_id());
	if (!$product instanceof WC_Product) {
		return $tabs;
	}

	$show_dimensions = apply_filters('wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions());
	$attribute_count = $product->get_attributes();
	$has_visible_attrs = !empty($attribute_count);

	if ($has_visible_attrs || $show_dimensions) {
		$tabs['additional_information'] = array(
			'title'    => __('Additional information', 'woocommerce'),
			'priority' => 20,
			'callback' => 'woocommerce_product_additional_information_tab',
		);
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_tabs_restore_additional_information', 101);

/**
 * @param string $key Tab key.
 * @param array  $tab Tab args.
 */
function trimvia_single_product_tab_render_fallback_overview($key, $tab)
{
	global $product;

	if (!$product instanceof WC_Product) {
		return;
	}

	$long  = trimvia_single_product_get_long_description_raw((int) $product->get_id());
	$short = trim((string) $product->get_short_description());

	if ('' !== wp_strip_all_tags($long)) {
		echo wp_kses_post(wpautop(wptexturize($long)));

		return;
	}

	if ('' !== wp_strip_all_tags($short)) {
		echo wp_kses_post(wpautop(wptexturize($short)));

		return;
	}

	echo '<p class="trimvia-product-details-placeholder">';
	esc_html_e('Add a full description, short description, or custom product tabs in the product editor to display treatment details here.', 'theme-woopm-child');
	echo '</p>';
}

/**
 * @param array $tabs Tabs.
 * @return array
 */
function trimvia_single_product_tabs_ensure_minimum($tabs)
{
	if (!function_exists('is_product') || !is_product()) {
		return $tabs;
	}

	if (!empty($tabs)) {
		return $tabs;
	}

	$tabs['trimvia-overview'] = array(
		'title'    => __('Overview', 'theme-woopm-child'),
		'priority' => 10,
		'callback' => 'trimvia_single_product_tab_render_fallback_overview',
	);

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_tabs_ensure_minimum', 10000);

/**
 * Rename WooCommerce â€œDescriptionâ€ tab label to match HTML prototype.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function trimvia_single_product_rename_description_tab($tabs)
{
	if (isset($tabs['description']['title'])) {
		$tabs['description']['title'] = __('Overview', 'theme-woopm-child');
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_rename_description_tab', 97);

/**
 * Add fixed â€œHow it worksâ€ and â€œSafetyâ€ tabs (ACF optional fields or filtered defaults).
 *
 * @param array $tabs Tabs.
 * @return array
 */
function trimvia_single_product_add_journey_tabs($tabs)
{
	if (!function_exists('is_product') || !is_product()) {
		return $tabs;
	}

	if (!isset($tabs['trimvia_how_it_works'])) {
		$tabs['trimvia_how_it_works'] = array(
			'title'    => __('How it works', 'theme-woopm-child'),
			'priority' => 16,
			'callback' => 'trimvia_single_product_tab_how_it_works_render',
		);
	}

	if (!isset($tabs['trimvia_safety'])) {
		$tabs['trimvia_safety'] = array(
			'title'    => __('Safety', 'theme-woopm-child'),
			'priority' => 17,
			'callback' => 'trimvia_single_product_tab_safety_render',
		);
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_add_journey_tabs', 17);

/**
 * @param string $key Tab key.
 * @param array  $tab Tab definition.
 */
function trimvia_single_product_tab_how_it_works_render($key, $tab)
{
	global $product;

	if (!$product instanceof WC_Product) {
		return;
	}

	$html = '';
	if (function_exists('get_field')) {
		$html = get_field('trimvia_tab_how_it_works', $product->get_id());
	}

	if ('' !== trim((string) $html)) {
		echo wp_kses_post($html);

		return;
	}

	$default_paras = array(
		__('After checkout you complete a structured health questionnaire. A UK-registered prescriber reviews your responses and may contact you if clarification is needed.', 'theme-woopm-child'),
		__('If approved, your prescription is sent to our partner pharmacy for dispensing and dispatch. You receive tracking details when your order ships.', 'theme-woopm-child'),
	);

	$default = '';
	foreach ($default_paras as $para) {
		$default .= '<p>' . esc_html($para) . '</p>';
	}

	echo wp_kses_post(apply_filters('trimvia_product_tab_how_content', $default, $product));
}

/**
 * @param string $key Tab key.
 * @param array  $tab Tab definition.
 */
function trimvia_single_product_tab_safety_render($key, $tab)
{
	global $product;

	if (!$product instanceof WC_Product) {
		return;
	}

	$html = '';
	if (function_exists('get_field')) {
		$html = get_field('trimvia_tab_safety', $product->get_id());
	}

	if ('' !== trim((string) $html)) {
		echo wp_kses_post($html);

		return;
	}

	$default_paras = array(
		__('Report suspected side effects via the Yellow Card scheme. Your prescriber will discuss common side effects, warnings, and monitoring before treatment starts.', 'theme-woopm-child'),
		__('This website does not replace the summary of product characteristics or patient information leaflet supplied with your medicine â€” always read those documents and follow clinical advice.', 'theme-woopm-child'),
	);

	$default = '';
	foreach ($default_paras as $para) {
		$default .= '<p>' . esc_html($para) . '</p>';
	}

	echo wp_kses_post(apply_filters('trimvia_product_tab_safety_content', $default, $product));
}

/**
 * Map selected FAQ posts (ACF) into the bottom accordion when the FAQ tab is not used alone.
 *
 * @param array       $items   FAQ rows {question, answer}.
 * @param WC_Product|null $product Product.
 * @return array
 */
function trimvia_single_product_faq_items_from_acf($items, $product)
{
	if (!empty($items) || !$product instanceof WC_Product || !function_exists('get_field')) {
		return $items;
	}

	if (get_field('enable_faq_tab', $product->get_id())) {
		return $items;
	}

	$faq_ids = get_field('select_faqs', $product->get_id());
	if (!is_array($faq_ids) || empty($faq_ids)) {
		return $items;
	}

	foreach ($faq_ids as $faq_id) {
		$faq_id = absint($faq_id);
		if ($faq_id < 1) {
			continue;
		}
		$post_type = get_post_type($faq_id);
		if (!$post_type || !is_string($post_type)) {
			continue;
		}

		$items[] = array(
			'question' => get_the_title($faq_id),
			'answer'   => get_post_field('post_content', $faq_id),
		);
	}

	return $items;
}
add_filter('trimvia_single_product_faq_items', 'trimvia_single_product_faq_items_from_acf', 8, 2);

/**
 * Generic FAQs when no FAQ tab, no ACF selection, and filter did not pre-fill items.
 *
 * @param array           $items   FAQ rows.
 * @param WC_Product|null $product Product.
 * @return array
 */
function trimvia_single_product_faq_items_defaults($items, $product)
{
	if (!empty($items) || !$product instanceof WC_Product) {
		return $items;
	}

	if (function_exists('get_field') && get_field('enable_faq_tab', $product->get_id())) {
		return $items;
	}

	return array(
		array(
			'question' => __('Do I need a prescription?', 'theme-woopm-child'),
			'answer'     => __('Prescription-only medicines require a prescription issued after a clinical assessment. An independent prescriber reviews your details and only approves treatment when it is medically appropriate.', 'theme-woopm-child'),
		),
		array(
			'question' => __('How quickly will my order arrive?', 'theme-woopm-child'),
			'answer'     => __('After approval, pharmacy processing and dispatch normally follow the service timelines shown at checkout. Rural postcodes may require an extra delivery day.', 'theme-woopm-child'),
		),
		array(
			'question' => __('Can I change dose or strength later?', 'theme-woopm-child'),
			'answer'     => __('Dose changes must always be clinician-led. Contact our clinical team or book a follow-up before altering strength or frequency.', 'theme-woopm-child'),
		),
	);
}
add_filter('trimvia_single_product_faq_items', 'trimvia_single_product_faq_items_defaults', 100, 2);

/**
 * Related products grid density on Trimvia single product layout.
 *
 * @param array $args Arguments.
 * @return array
 */
function trimvia_single_product_related_products_args($args)
{
	if (!function_exists('is_product') || !is_product()) {
		return $args;
	}

	$args['posts_per_page'] = 3;
	$args['columns']        = 3;

	return $args;
}
add_filter('woocommerce_output_related_products_args', 'trimvia_single_product_related_products_args');

/**
 * Upsells layout on single product.
 *
 * @param array $args Arguments.
 * @return array
 */
function trimvia_single_product_upsells_args($args)
{
	if (!function_exists('is_product') || !is_product()) {
		return $args;
	}

	$args['posts_per_page'] = 3;
	$args['columns']        = 3;

	return $args;
}
add_filter('woocommerce_upsell_display_args', 'trimvia_single_product_upsells_args');

/**
 * Section headings for upsells / related on single product.
 *
 * @param string $heading Heading text.
 * @return string
 */
function trimvia_single_product_related_heading($heading)
{
	if (!function_exists('is_product') || !is_product()) {
		return $heading;
	}

	return __('Related treatments', 'theme-woopm-child');
}
add_filter('woocommerce_product_related_products_heading', 'trimvia_single_product_related_heading');

/**
 * @param string $heading Heading text.
 * @return string
 */
function trimvia_single_product_upsells_heading($heading)
{
	if (!function_exists('is_product') || !is_product()) {
		return $heading;
	}

	return __('You may also like...', 'theme-woopm-child');
}
add_filter('woocommerce_product_upsells_products_heading', 'trimvia_single_product_upsells_heading');

/**
 * Whether the quantity field should appear on single product add-to-cart forms.
 *
 * @param WC_Product|false $product Product object.
 * @return bool
 */
function trimvia_should_show_single_product_quantity($product)
{
	if (!$product instanceof WC_Product) {
		return true;
	}

	$max = (int) $product->get_max_purchase_quantity();
	$min = (int) $product->get_min_purchase_quantity();

	if ($max > 0 && $max <= 1 && $min <= 1) {
		return false;
	}

	return true;
}

/**
 * Default quantity value for single product forms.
 *
 * @param WC_Product|false $product Product object.
 * @return int
 */
function trimvia_get_single_product_quantity_value($product)
{
	if (!$product instanceof WC_Product) {
		return 1;
	}

	if (isset($_POST['quantity'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return max(1, (int) wc_stock_amount(wp_unslash($_POST['quantity']))); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	return max(1, (int) $product->get_min_purchase_quantity());
}

/**
 * Whether consultation session bootstrapping should run on this request.
 *
 * Avoids creating a WooCommerce/PHP session on every front-end hit (homepage, blog, etc.),
 * which can overload wp_woocommerce_sessions and slow the server under traffic.
 *
 * @return bool
 */
function trimvia_should_bootstrap_consultation_session()
{
	if (function_exists('is_customize_preview') && is_customize_preview()) {
		return false;
	}

	global $pagenow;
	if (is_admin() && isset($pagenow) && 'customize.php' === $pagenow) {
		return false;
	}

	if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
		return false;
	}

	if (function_exists('is_cart') && is_cart()) {
		return true;
	}
	if (function_exists('is_checkout') && is_checkout()) {
		return true;
	}
	if (function_exists('is_product') && is_product()) {
		return true;
	}
	if (function_exists('is_shop') && is_shop()) {
		return true;
	}
	if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
		return true;
	}
	if (function_exists('is_account_page') && is_account_page()) {
		return true;
	}
	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
		return true;
	}
	if (is_page('consultation')) {
		return true;
	}
	if (is_tax('condition')) {
		return true;
	}
	if (is_singular(array('service', 'product'))) {
		return true;
	}

	return false;
}

/**
 * Ensure WooCommerce and PHP session consultation data is available (guest-safe).
 *
 * @return void
 */
function trimvia_ensure_consultation_session()
{
	static $bootstrapped = false;

	if ($bootstrapped) {
		return;
	}

	if (!trimvia_should_bootstrap_consultation_session()) {
		return;
	}

	$bootstrapped = true;

	if (function_exists('is_customize_preview') && is_customize_preview()) {
		return;
	}

	global $pagenow;
	if (is_admin() && isset($pagenow) && 'customize.php' === $pagenow) {
		return;
	}

	if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
		return;
	}

	if (!function_exists('WC') || !WC()->session) {
		return;
	}

	if (!WC()->session->has_session()) {
		WC()->session->set_customer_session_cookie(true);
	}

	if (!empty(WC()->session->get('cflp_form_data'))) {
		return;
	}

	if (
		function_exists('session_status')
		&& PHP_SESSION_NONE === session_status()
		&& !headers_sent()
	) {
		// WooPW stores a backup in PHP session when the WC session is unavailable.
		session_start();
	}

	if (!empty($_SESSION['wp_cflp_form_data'])) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		WC()->session->set('cflp_form_data', $_SESSION['wp_cflp_form_data']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
}

/**
 * Bootstrap consultation session only on WooCommerce-related front-end routes.
 *
 * @return void
 */
function trimvia_maybe_bootstrap_consultation_session()
{
	trimvia_ensure_consultation_session();
}
add_action('woocommerce_init', 'trimvia_maybe_bootstrap_consultation_session', 1);

/**
 * Whether consultation/assessment session data exists in the current request.
 *
 * @return bool
 */
function trimvia_has_consultation_session()
{
	static $cached = null;

	if (null !== $cached) {
		return $cached;
	}

	trimvia_ensure_consultation_session();

	if (function_exists('WC') && WC()->session && !empty(WC()->session->get('cflp_form_data'))) {
		$cached = true;
		return true;
	}

	$cached = !empty($_SESSION['wp_cflp_form_data']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	return $cached;
}

/**
 * Whether the current session includes a completed consultation for a condition slug.
 *
 * @param string $condition_slug Condition taxonomy slug.
 * @return bool
 */
function trimvia_has_consultation_for_condition($condition_slug)
{
	$condition_slug = sanitize_title((string) $condition_slug);

	if ('' === $condition_slug) {
		return false;
	}

	trimvia_ensure_consultation_session();

	if (function_exists('has_consultation_for_condition')) {
		return (bool) has_consultation_for_condition($condition_slug);
	}

	$session_data = array();

	if (function_exists('WC') && WC()->session) {
		$session_data = WC()->session->get('cflp_form_data');
	}

	if (empty($session_data) && !empty($_SESSION['wp_cflp_form_data'])) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$session_data = $_SESSION['wp_cflp_form_data']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	if (!is_array($session_data) || empty($session_data)) {
		return false;
	}

	if (
		!empty($session_data['condition_slug'])
		&& sanitize_title((string) $session_data['condition_slug']) === $condition_slug
	) {
		return true;
	}

	foreach ($session_data as $entry) {
		if (!is_array($entry) || empty($entry['condition_slug'])) {
			continue;
		}

		if (sanitize_title((string) $entry['condition_slug']) === $condition_slug) {
			return true;
		}
	}

	return false;
}

/**
 * Whether a product's condition has a completed consultation in the current session.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function trimvia_product_has_completed_consultation($product_id)
{
	$product_id = (int) $product_id;

	if ($product_id < 1) {
		return false;
	}

	$terms = get_the_terms($product_id, 'condition');

	if (!empty($terms) && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			if ($term instanceof WP_Term) {
				if (function_exists('has_consultation_for_condition')) {
					if (has_consultation_for_condition($term->slug)) {
						return true;
					}
				} elseif (trimvia_has_consultation_for_condition($term->slug)) {
					return true;
				}
			}
		}

		return false;
	}

	// Product has no condition terms â€” mirror parent generic session check.
	return trimvia_has_consultation_session();
}

/**
 * Whether the single-product CTA should be the assessment/consultation link.
 *
 * Mirrors parent simple.php + WooPW single_product_consultation_button_masking:
 * prescription products show Start Assessment until the condition-specific
 * consultation is completed (and any linked-order re-assessment gate passes).
 *
 * @param int|WC_Product $product Product ID or object.
 * @return bool
 */
function trimvia_product_should_show_assessment_cta($product)
{
	return trimvia_is_product_consultation_required($product);
}

/**
 * Label for the single-product assessment CTA.
 *
 * @return string
 */
function trimvia_get_single_product_assessment_button_label()
{
	if (function_exists('wc_consultation_button_title')) {
		return (string) wc_consultation_button_title();
	}

	return __('Start Assessment', 'theme-woopm-child');
}

/**
 * Whether a product is marked as prescription-only (parent ACF flow).
 *
 * @param int|WC_Product $product Product ID or object.
 * @return bool
 */
function trimvia_product_is_prescription_type($product)
{
	$product_id = 0;
	if ($product instanceof WC_Product) {
		$product_id = (int) $product->get_id();
	} elseif (is_numeric($product)) {
		$product_id = (int) $product;
	}

	if ($product_id < 1 || !function_exists('get_field')) {
		return false;
	}

	$prescription_flag = strtolower(trim((string) get_field('is_prescription_product', $product_id)));

	return in_array($prescription_flag, array('yes', '1', 'true', 'plines', 'on', 'y'), true);
}

/**
 * Single product CTA button â€” mirrors parent simple.php assessment/add-to-basket flow.
 *
 * @param WC_Product $product Product object.
 * @param string     $context Template context: simple|variation.
 * @return void
 */
function trimvia_render_single_product_cart_button($product, $context = 'simple')
{
	if (!$product instanceof WC_Product) {
		return;
	}

	$button_class = 'single_add_to_cart_button theme-btn-primary btn-accent';
	$wc_button_class = wc_wp_theme_get_element_class_name('button');
	if ($wc_button_class) {
		$button_class .= ' ' . $wc_button_class;
	}
	if ('simple' === $context) {
		$button_class .= ' alt';
	}

	if (trimvia_product_should_show_assessment_cta($product)) {
		$assessment_url = trimvia_get_product_entry_url($product);
		if ('' !== $assessment_url) {
			$button_class .= ' trimvia-assessment-cta';
			?>
			<a class="<?php echo esc_attr($button_class); ?>" href="<?php echo esc_url($assessment_url); ?>">
				<?php echo esc_html(trimvia_get_single_product_assessment_button_label()); ?>
			</a>
			<?php
			return;
		}
	}

	$cart_label = __('Add to basket', 'theme-woopm-child');

	if ('simple' === $context) {
		?>
		<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="<?php echo esc_attr($button_class); ?>">
			<?php echo esc_html($cart_label); ?>
		</button>
		<?php
		return;
	}

	?>
	<button type="submit" class="<?php echo esc_attr($button_class); ?>">
		<?php echo esc_html($cart_label); ?>
	</button>
	<?php
}

/**
 * Match single-product CTA copy with parent assessment â†’ add-to-basket flow.
 *
 * @param string           $text    Default button text.
 * @param WC_Product|false $product Product object.
 * @return string
 */
function trimvia_single_product_add_to_cart_text($text, $product)
{
	if (!function_exists('is_product') || !is_product()) {
		return $text;
	}

	if ($product instanceof WC_Product && trimvia_product_should_show_assessment_cta($product)) {
		return trimvia_get_single_product_assessment_button_label();
	}

	return __('Add to basket', 'theme-woopm-child');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'trimvia_single_product_add_to_cart_text', 20, 2);

/**
 * Remove WooCommerce's leading pipe from the reset-variations link.
 *
 * @param string $link Default reset link markup.
 * @return string
 */
function trimvia_reset_variations_link($link)
{
	return '<a class="reset_variations" href="#" role="button">' . esc_html__('Clear selection', 'theme-woopm-child') . '</a>';
}
add_filter('woocommerce_reset_variations_link', 'trimvia_reset_variations_link');

/**
 * Long product description (Product description field in admin).
 *
 * @param int $product_id Product ID.
 * @return string
 */
function trimvia_single_product_get_long_description_raw($product_id)
{
	$product_id = (int) $product_id;
	if ($product_id < 1) {
		return '';
	}

	$product = wc_get_product($product_id);
	if ($product instanceof WC_Product) {
		$description = trim((string) $product->get_description());
		if ('' !== $description) {
			return $description;
		}
	}

	return trim((string) get_post_field('post_content', $product_id));
}

/**
 * Render the long product description in Treatment details tabs.
 *
 * @param string $key Tab key.
 * @param array  $tab Tab definition.
 */
function trimvia_single_product_tab_render_description($key, $tab)
{
	$product_id = trimvia_single_product_get_current_product_id();
	if ($product_id < 1) {
		return;
	}

	$description_raw = trimvia_single_product_get_long_description_raw($product_id);
	if ('' === trim(wp_strip_all_tags($description_raw))) {
		return;
	}

	$product_post = get_post($product_id);
	if (!$product_post instanceof WP_Post) {
		echo '<div class="woocommerce-product-details__description article-content">';
		echo apply_filters('the_content', $description_raw); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';

		return;
	}

	global $post;
	$previous_post = $post ?? null;
	$post = $product_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata($product_post);

	echo '<div class="woocommerce-product-details__description article-content">';
	echo apply_filters('the_content', $description_raw); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	wp_reset_postdata();
	$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}

/**
 * Resolve the product ID for single-product tab rendering.
 *
 * @return int
 */
function trimvia_single_product_get_current_product_id()
{
	global $product;

	if ($product instanceof WC_Product) {
		return (int) $product->get_id();
	}

	$queried_id = (int) get_queried_object_id();
	if ($queried_id > 0 && 'product' === get_post_type($queried_id)) {
		return $queried_id;
	}

	$loop_id = (int) get_the_ID();
	if ($loop_id > 0 && 'product' === get_post_type($loop_id)) {
		return $loop_id;
	}

	return 0;
}

/**
 * Use a reliable description renderer for the Overview tab.
 *
 * @param array $tabs Product tabs.
 * @return array
 */
function trimvia_single_product_fix_description_tab($tabs)
{
	$product_id = trimvia_single_product_get_current_product_id();
	if ($product_id < 1) {
		return $tabs;
	}

	if ('' === trim(wp_strip_all_tags(trimvia_single_product_get_long_description_raw($product_id)))) {
		return $tabs;
	}

	if (!isset($tabs['description'])) {
		$tabs['description'] = array(
			'title'    => __('Overview', 'theme-woopm-child'),
			'priority' => 10,
			'callback' => 'trimvia_single_product_tab_render_description',
		);
	} else {
		$tabs['description']['title']    = __('Overview', 'theme-woopm-child');
		$tabs['description']['callback'] = 'trimvia_single_product_tab_render_description';
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_fix_description_tab', 10050);

/**
 * Hide the default WooCommerce "Description" heading inside tab panels.
 *
 * @param string $heading Heading text.
 * @return string
 */
function trimvia_single_product_hide_description_heading($heading)
{
	return '';
}
add_filter('woocommerce_product_description_heading', 'trimvia_single_product_hide_description_heading');

/**
 * Render ACF "Other tabs" content for the custom Treatment details UI.
 *
 * @param string $slug Tab slug.
 * @param array  $tab  Tab definition.
 */
function trimvia_single_product_tab_render_acf($slug, $tab)
{
	if (!empty($tab['tab_title'])) {
		echo '<h2>' . esc_html((string) $tab['tab_title']) . '</h2>';
	}

	if (!empty($tab['tab_content'])) {
		echo wp_kses_post((string) $tab['tab_content']);
	}
}

/**
 * Ensure ACF product tabs (Directions, etc.) appear in Treatment details.
 *
 * @param array $tabs Product tabs.
 * @return array
 */
function trimvia_single_product_register_acf_tabs($tabs)
{
	if (!function_exists('is_product') || !is_product() || !function_exists('have_rows')) {
		return $tabs;
	}

	$product_id = get_queried_object_id();
	if ($product_id < 1 || !have_rows('product_tabs', $product_id)) {
		return $tabs;
	}

	while (have_rows('product_tabs', $product_id)) {
		the_row();

		if (!get_sub_field('enable')) {
			continue;
		}

		$tab_label = trim((string) get_sub_field('tab_label'));
		if ('' === $tab_label) {
			$tab_label = __('Label', 'theme-woopm-child');
		}

		$tab_id = function_exists('wc_convert_str_to_slug')
			? wc_convert_str_to_slug($tab_label)
			: sanitize_title($tab_label);

		if ('' === $tab_id) {
			$tab_id = 'trimvia-tab-' . get_row_index();
		}

		if (isset($tabs[$tab_id])) {
			continue;
		}

		$tabs[$tab_id] = array(
			'title'        => $tab_label,
			'tab_title'    => get_sub_field('tab_title'),
			'tab_content'  => get_sub_field('tab_content'),
			'priority'     => 18,
			'callback'     => 'trimvia_single_product_tab_render_acf',
		);
	}

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'trimvia_single_product_register_acf_tabs', 10002);

/**
 * Default selected value when a variation attribute has only one purchasable option.
 *
 * @param WC_Product_Variable $product             Variable product.
 * @param string              $attribute_name      Attribute name.
 * @param array               $options             Available option slugs.
 * @param array               $available_variations Available variations data.
 * @return string
 */
function trimvia_get_variation_attribute_selected_value($product, $attribute_name, $options, $available_variations)
{
	if (!$product instanceof WC_Product_Variable || empty($options)) {
		return '';
	}

	$attribute_key = sanitize_title($attribute_name);
	$defaults      = $product->get_default_attributes();

	if (!empty($defaults[$attribute_key])) {
		$default_value = (string) $defaults[$attribute_key];
		if (in_array($default_value, $options, true)) {
			return $default_value;
		}
	}

	if (1 === count($options)) {
		return (string) reset($options);
	}

	if (is_array($available_variations) && 1 === count($available_variations)) {
		$variation_attributes = $available_variations[0]['attributes'] ?? array();
		$variation_key        = 'attribute_' . $attribute_key;

		if (!empty($variation_attributes[$variation_key])) {
			return (string) $variation_attributes[$variation_key];
		}
	}

	return '';
}

/**
 * Sync variation price into the summary price row on single product pages.
 */
function trimvia_single_product_variation_scripts()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}
	?>
	<script>
	(function ($) {
		var autoSelectSingleVariationOptions = function ($form) {
			if (!$form || !$form.length) {
				return;
			}

			var updated = false;

			$form.find('table.variations select').each(function () {
				var $select = $(this);
				var $options = $select.find('option').filter(function () {
					return $(this).val() !== '';
				});

				if ($options.length === 1 && !$select.val()) {
					$select.val($options.first().val());
					updated = true;
				}
			});

			if (updated) {
				$form.trigger('check_variations');
				return;
			}

			var allSelected = true;
			$form.find('table.variations select').each(function () {
				if (!$(this).val()) {
					allSelected = false;
				}
			});

			if (allSelected) {
				$form.trigger('check_variations');
			}
		};

		$(function () {
			if (!$('body').hasClass('trimvia-single-product-page')) {
				return;
			}

			var $priceWrap = $('.trimvia-single-product-price');
			var defaultPriceHtml = $priceWrap.length ? $priceWrap.html() : '';
			var $stockBadge = $('.single-product-price-row .single-product-stock');
			var defaultStockText = $stockBadge.data('defaultStockText') || ($stockBadge.length ? $.trim($stockBadge.text()) : '');
			var inStockText = $stockBadge.data('inStockText') || defaultStockText;
			var outOfStockText = $stockBadge.data('outOfStockText') || 'Out of stock';
			var defaultStockClass = $stockBadge.attr('class') || 'single-product-stock';

			var restoreStockBadge = function () {
				if (!$stockBadge.length) {
					return;
				}

				$stockBadge.attr('class', defaultStockClass).text(defaultStockText);
			};

			var updateStockBadge = function (variation) {
				if (!$stockBadge.length || !variation) {
					restoreStockBadge();
					return;
				}

				if (variation.is_in_stock) {
					$stockBadge.attr('class', 'single-product-stock').text(inStockText);
					return;
				}

				$stockBadge.attr('class', 'single-product-stock single-product-stock--out').text(outOfStockText);
			};

			$('.variations_form').each(function () {
				var $form = $(this);

				autoSelectSingleVariationOptions($form);

				$form.on('found_variation', function (event, variation) {
					if ($priceWrap.length && variation && variation.price_html) {
						$priceWrap.html(variation.price_html);
					}

					updateStockBadge(variation);
				}).on('reset_data hide_variation', function () {
					if ($priceWrap.length) {
						$priceWrap.html(defaultPriceHtml);
					}

					restoreStockBadge();
					autoSelectSingleVariationOptions($form);
				});
			});
		});

		$(document.body).on('wc_variation_form', function (event, $form) {
			if (!$('body').hasClass('trimvia-single-product-page')) {
				return;
			}

			autoSelectSingleVariationOptions($form);
		});

		var removeStrayVariationMarks = function ($form) {
			if (!$form || !$form.length) {
				return;
			}

			$form.find('.single_variation_wrap').contents().filter(function () {
				return this.nodeType === 3 && $.trim(this.nodeValue) === '|';
			}).remove();

			$form.contents().filter(function () {
				return this.nodeType === 3 && $.trim(this.nodeValue) === '|';
			}).remove();
		};

		$('.variations_form').each(function () {
			var $form = $(this);

			removeStrayVariationMarks($form);

			$form.on('found_variation reset_data woocommerce_update_variation_values', function () {
				removeStrayVariationMarks($form);
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action('wp_footer', 'trimvia_single_product_variation_scripts', 99);


/**
 * Match parent consultation gate behavior in loop cards/buttons.
 *
 * @param string           $text    Existing CTA text.
 * @param WC_Product|false $product Product object.
 * @return string
 */
function trimvia_loop_product_add_to_cart_text($text, $product)
{
	if (!$product instanceof WC_Product) {
		return $text;
	}

	if (trimvia_is_product_consultation_required($product)) {
		return __('Start Assessment', 'theme-woopm-child');
	}

	return $text;
}
add_filter('woocommerce_product_add_to_cart_text', 'trimvia_loop_product_add_to_cart_text', 30, 2);

/**
 * Ensure loop add-to-cart URLs route to consultation entry when required.
 *
 * @param string     $url     Existing CTA URL.
 * @param WC_Product $product Product object.
 * @return string
 */
function trimvia_loop_product_add_to_cart_url($url, $product)
{
	if (!$product instanceof WC_Product) {
		return $url;
	}

	return trimvia_get_product_entry_url($product, (string) $url);
}
add_filter('woocommerce_product_add_to_cart_url', 'trimvia_loop_product_add_to_cart_url', 30, 2);

/**
 * Resolve a primary condition slug for a product.
 *
 * @param int|WC_Product $product Product ID or object.
 * @return string
 */
function trimvia_get_product_primary_condition_slug($product)
{
	$product_id = 0;
	if ($product instanceof WC_Product) {
		$product_id = (int) $product->get_id();
	} elseif (is_numeric($product)) {
		$product_id = (int) $product;
	}

	if ($product_id < 1) {
		return '';
	}

	$terms = get_the_terms($product_id, 'condition');
	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	$primary = reset($terms);
	if (!$primary instanceof WP_Term) {
		return '';
	}

	return sanitize_title((string) $primary->slug);
}

/**
 * Whether a product should display as out of stock on listing cards.
 *
 * @param WC_Product $product Product object.
 * @return bool
 */
function trimvia_product_is_out_of_stock($product)
{
	if (!$product instanceof WC_Product) {
		return false;
	}

	if ('outofstock' === $product->get_stock_status() || !$product->is_in_stock()) {
		return true;
	}

	if ($product->is_type('variable')) {
		foreach ($product->get_children() as $child_id) {
			$variation = wc_get_product($child_id);
			if ($variation instanceof WC_Product && $variation->is_purchasable() && $variation->is_in_stock()) {
				return false;
			}
		}

		return true;
	}

	return !$product->is_purchasable();
}

/**
 * Whether the current user is a patient (not prescriber/admin staff).
 *
 * @return bool
 */
function trimvia_is_patient_user()
{
	if (!is_user_logged_in()) {
		return false;
	}

	return !trimvia_user_has_prescriber_access();
}

/**
 * Latest open prescription order for a condition (processing / awaiting approval, etc.).
 *
 * Mirrors WooPW has_open_order_for_condition() used at checkout validation.
 *
 * @param string $condition_slug Condition taxonomy slug.
 * @return WC_Order|null
 */
function trimvia_get_user_open_order_for_condition($condition_slug)
{
	if (!trimvia_is_patient_user() || !function_exists('wc_get_orders')) {
		return null;
	}

	$condition_slug = sanitize_title((string) $condition_slug);
	if ('' === $condition_slug) {
		return null;
	}

	$term = get_term_by('slug', $condition_slug, 'condition');
	if (!$term instanceof WP_Term) {
		return null;
	}

	$orders = wc_get_orders(
		array(
			'customer'   => get_current_user_id(),
			'orderby'    => 'date',
			'order'      => 'DESC',
			'limit'      => 1,
			'status'     => array('pre-screen', 'processing', 'pending', 'await-approval', 'prescribe-approve', 'prescribe-decline'),
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_cflp_form_data',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_order_prescription_only_items',
					'compare' => 'EXISTS',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_order_conditions',
						'value'   => (int) $term->term_id,
						'compare' => 'IN',
					),
					array(
						'key'     => '_order_conditions_reorder',
						'value'   => (int) $term->term_id,
						'compare' => 'IN',
					),
				),
			),
		)
	);

	if (empty($orders[0]) || !($orders[0] instanceof WC_Order)) {
		return null;
	}

	return $orders[0];
}

/**
 * Whether a logged-in patient already has an in-progress order for this product's condition.
 *
 * @param int|WC_Product $product Product ID or object.
 * @return bool
 */
function trimvia_product_has_open_order_in_progress($product)
{
	$product_id = 0;
	if ($product instanceof WC_Product) {
		$product_id = (int) $product->get_id();
	} elseif (is_numeric($product)) {
		$product_id = (int) $product;
	}

	if ($product_id < 1) {
		return false;
	}

	$condition_slug = trimvia_get_product_primary_condition_slug($product_id);
	if ('' === $condition_slug) {
		return false;
	}

	return trimvia_get_user_open_order_for_condition($condition_slug) instanceof WC_Order;
}

/**
 * Whether the logged-in user has a previous completed order for a condition.
 *
 * Used to gate returning patients behind the reorder/reassessment questionnaire
 * (parent consultation.php + WooPW reorder flow).
 *
 * @param string $condition_slug Condition taxonomy slug.
 * @return bool
 */
function trimvia_user_has_previous_completed_order_for_condition($condition_slug)
{
	if (!is_user_logged_in()) {
		return false;
	}

	$condition_slug = sanitize_title((string) $condition_slug);
	if ('' === $condition_slug) {
		return false;
	}

	$term = get_term_by('slug', $condition_slug, 'condition');
	if (!$term instanceof WP_Term) {
		return false;
	}

	if (function_exists('get_user_latest_completed_consultation_order')) {
		return (bool) get_user_latest_completed_consultation_order(wp_get_current_user(), (int) $term->term_id, false);
	}

	if (function_exists('woopw_check_orders_previous_conditions')) {
		$previous_conditions = woopw_check_orders_previous_conditions();
		return is_array($previous_conditions)
			&& in_array((int) $term->term_id, array_map('intval', $previous_conditions), true);
	}

	return false;
}

/**
 * Mark reassessment as completed for a condition in the current WC session.
 *
 * @param string $condition_slug Condition taxonomy slug.
 * @return void
 */
function trimvia_mark_reassessment_completed_for_condition($condition_slug)
{
	$condition_slug = sanitize_title((string) $condition_slug);
	if ('' === $condition_slug || !function_exists('WC') || !WC()->session) {
		return;
	}

	trimvia_ensure_consultation_session();
	$approved = WC()->session->get('trimvia_reassessment_approved', array());
	if (!is_array($approved)) {
		$approved = array();
	}

	$approved[$condition_slug] = time();
	WC()->session->set('trimvia_reassessment_approved', $approved);
}

/**
 * Whether the current session includes a fresh reassessment for a condition.
 *
 * @param string $condition_slug Condition taxonomy slug.
 * @return bool
 */
function trimvia_reassessment_approved_for_condition($condition_slug)
{
	$condition_slug = sanitize_title((string) $condition_slug);
	if ('' === $condition_slug) {
		return false;
	}

	trimvia_ensure_consultation_session();

	if (!function_exists('WC') || !WC()->session) {
		return false;
	}

	$approved = WC()->session->get('trimvia_reassessment_approved', array());
	if (!is_array($approved) || empty($approved[$condition_slug])) {
		return false;
	}

	return trimvia_has_consultation_for_condition($condition_slug);
}

/**
 * Clear reassessment approval flags (e.g. after checkout).
 *
 * @return void
 */
function trimvia_clear_reassessment_approval()
{
	if (function_exists('WC') && WC()->session) {
		WC()->session->set('trimvia_reassessment_approved', array());
	}
}

/**
 * Track reassessment completion when the consultation form is submitted.
 *
 * @param array<string, mixed> $data Form submission payload.
 * @return array<string, mixed>
 */
function trimvia_track_reassessment_submission($data)
{
	if (!is_array($data)) {
		return $data;
	}

	if (!empty($data['condition_slug'])) {
		trimvia_mark_reassessment_completed_for_condition((string) $data['condition_slug']);
	} elseif (!empty($data['condition_id'])) {
		$term = get_term((int) $data['condition_id'], 'condition');
		if ($term instanceof WP_Term) {
			trimvia_mark_reassessment_completed_for_condition($term->slug);
		}
	}

	return $data;
}
add_filter('cflp_form_submission_data', 'trimvia_track_reassessment_submission', 20);
add_action('woocommerce_thankyou', 'trimvia_clear_reassessment_approval', 5);

/**
 * Whether a product should be gated behind the consultation/treatment entry step.
 *
 * Mirrors parent simple.php + WooPW single_product_consultation_button_masking:
 * first assessment in-session unlocks add-to-basket; returning patients must
 * complete the reorder/reassessment questionnaire first.
 *
 * @param int|WC_Product $product Product ID or object.
 * @return bool
 */
function trimvia_is_product_consultation_required($product)
{
	$product_id = 0;
	if ($product instanceof WC_Product) {
		$product_id = (int) $product->get_id();
	} elseif (is_numeric($product)) {
		$product_id = (int) $product;
	}

	if ($product_id < 1) {
		return false;
	}

	$prescription_flag = '';
	if (function_exists('get_field')) {
		$prescription_flag = strtolower(trim((string) get_field('is_prescription_product', $product_id)));
	}

	$is_prescription_product = in_array($prescription_flag, array('yes', '1', 'true', 'plines', 'on', 'y'), true);
	if (!$is_prescription_product && '' !== $prescription_flag) {
		$is_prescription_product = !in_array($prescription_flag, array('no', '0', 'false', 'off', 'n'), true);
	}

	// ACF field not set: auto-detect by condition taxonomy.
	// Any product assigned to at least one condition term is treated as requiring consultation.
	if (!$is_prescription_product && '' === $prescription_flag) {
		$condition_terms = wp_get_post_terms($product_id, 'condition', array('fields' => 'ids'));
		$is_prescription_product = !is_wp_error($condition_terms) && !empty($condition_terms);
	}

	if (!$is_prescription_product) {
		return false;
	}

	$condition_slug = trimvia_get_product_primary_condition_slug($product_id);

	if ('' === $condition_slug) {
		return !trimvia_has_consultation_session();
	}

	if (trimvia_product_has_open_order_in_progress($product_id)) {
		return true;
	}

	trimvia_ensure_consultation_session();

	$consultation_completed = function_exists('has_consultation_for_condition')
		? has_consultation_for_condition($condition_slug)
		: trimvia_has_consultation_for_condition($condition_slug);

	if (!$consultation_completed) {
		return true;
	}

	if (is_user_logged_in()) {
		$needs_reassessment = trimvia_user_has_previous_completed_order_for_condition($condition_slug);

		if (!$needs_reassessment) {
			$condition_term = get_term_by('slug', $condition_slug, 'condition');
			if ($condition_term instanceof WP_Term && function_exists('woopw_get_order_linked_with_term_status')) {
				$needs_reassessment = (bool) woopw_get_order_linked_with_term_status(
					get_current_user_id(),
					(int) $condition_term->term_id
				);
			}
		}

		if ($needs_reassessment) {
			return !trimvia_reassessment_approved_for_condition($condition_slug);
		}
	}

	return false;
}

/**
 * Use one consultation CTA on single product pages (child templates only).
 *
 * WooPW adds its own "Start Assessment" link on after_variations_table while the
 * child variation/simple templates also render the same CTA — remove plugin hooks
 * and keep the default cart-button location so only one button appears.
 *
 * @return void
 */
function trimvia_consolidate_product_consultation_masking()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	if (class_exists('WOOPW_FRONTEND_PRODUCTS')) {
		$woopw_products = WOOPW_FRONTEND_PRODUCTS::get_instance();
		remove_action('woocommerce_after_variations_table', array($woopw_products, 'single_product_consultation_button_masking'), 20);
		remove_action('woocommerce_simple_add_to_cart', array($woopw_products, 'single_product_consultation_button_masking'), 30);
		remove_action('woocommerce_grouped_add_to_cart', array($woopw_products, 'single_product_consultation_button_masking'), 30);
		remove_action('woocommerce_external_add_to_cart', array($woopw_products, 'single_product_consultation_button_masking'), 30);
	}

	remove_action('woocommerce_after_variations_table', 'woocommerce_single_variation_add_to_cart_button', 20);
	remove_action('woocommerce_after_variations_table', 'trimvia_render_masked_consultation_button', 20);
	remove_action('woocommerce_simple_add_to_cart', 'trimvia_render_masked_consultation_button', 30);
	remove_action('woocommerce_grouped_add_to_cart', 'trimvia_render_masked_consultation_button', 30);
	remove_action('woocommerce_external_add_to_cart', 'trimvia_render_masked_consultation_button', 30);

	if (!has_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button')) {
		add_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
	}

	if (!has_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart')) {
		add_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
	}
}
add_action('template_redirect', 'trimvia_consolidate_product_consultation_masking', 25);

/**
 * Resolve the condition archive ("treatments") URL for a product.
 *
 * @param int|WC_Product $product Product ID or object.
 * @return string
 */
function trimvia_get_product_treatments_url($product)
{
	$condition_slug = trimvia_get_product_primary_condition_slug($product);
	if ('' === $condition_slug) {
		return '';
	}

	$condition_term = get_term_by('slug', $condition_slug, 'condition');
	if (!$condition_term instanceof WP_Term) {
		return '';
	}

	$term_link = get_term_link($condition_term);
	if (is_wp_error($term_link)) {
		return '';
	}

	return (string) $term_link;
}

/**
 * Product CTA destination for shop/archive/single contexts.
 *
 * - Normal products keep their default Woo URL.
 * - Prescription products without consultation session follow parent flow and
 *   go to the treatment/condition page first (fallback: consultation URL).
 *
 * @param int|WC_Product $product      Product ID or object.
 * @param string         $fallback_url Default URL when no gating is required.
 * @return string
 */
function trimvia_get_product_entry_url($product, $fallback_url = '')
{
	$resolved_fallback = (string) $fallback_url;
	if ('' === $resolved_fallback) {
		if ($product instanceof WC_Product && method_exists($product, 'add_to_cart_url')) {
			$resolved_fallback = (string) $product->add_to_cart_url();
		} elseif ($product instanceof WC_Product) {
			$resolved_fallback = (string) $product->get_permalink();
		}
	}

	if (!trimvia_is_product_consultation_required($product)) {
		return $resolved_fallback;
	}

	// Match parent theme behavior: go directly to consultation page, not condition archive.
	$condition_slug = trimvia_get_product_primary_condition_slug($product);
	if ('' !== $condition_slug) {
		return trimvia_get_consultation_url($condition_slug);
	}

	// Fallback: condition archive if no slug resolved.
	$treatments_url = trimvia_get_product_treatments_url($product);
	if ('' !== $treatments_url) {
		return $treatments_url;
	}

	return $resolved_fallback;
}

/**
 * Build consultation URL and append condition context when valid.
 *
 * Always resolves to an on-site consultation route. If Customizer URL is missing,
 * external, or not a consultation path, fallback to /consultation/.
 *
 * @param string $condition_slug Optional condition slug.
 * @return string
 */
function trimvia_get_consultation_url($condition_slug = '')
{
	$default_consultation_url = home_url('/consultation/');
	$base_url = trim((string) get_theme_mod('trimvia_header_primary_button_link', $default_consultation_url));
	if ('' === $base_url) {
		$base_url = $default_consultation_url;
	}

	$base_parts = wp_parse_url($base_url);
	$home_parts = wp_parse_url(home_url('/'));

	$is_same_host = true;
	if (!empty($base_parts['host']) && !empty($home_parts['host'])) {
		$is_same_host = strtolower((string) $base_parts['host']) === strtolower((string) $home_parts['host']);
	}
	$base_path = isset($base_parts['path']) ? strtolower((string) $base_parts['path']) : '';
	$is_consultation_path = false !== strpos($base_path, 'consultation');

	if (!$is_same_host || !$is_consultation_path) {
		$base_url = $default_consultation_url;
	}

	$condition_slug = sanitize_title((string) $condition_slug);
	if ('' === $condition_slug) {
		return $base_url;
	}

	return add_query_arg('condition-slug', $condition_slug, $base_url);
}

/**
 * Best-effort condition slug from current frontend context.
 *
 * @return string
 */
function trimvia_get_current_condition_slug_context()
{
	if (!empty($_GET['condition-slug'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_title(wp_unslash($_GET['condition-slug']));
	}

	if (is_tax('condition')) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			return sanitize_title((string) $term->slug);
		}
	}

	if (function_exists('is_product') && is_product()) {
		$product_id = (int) get_queried_object_id();
		return trimvia_get_product_primary_condition_slug($product_id);
	}

	return '';
}

/**
 * Prescriber My Account endpoints â€” ensure rewrite rules exist (same as parent/WooPW 1.8.2).
 *
 * WooPW registers handlers in WOOPW_FRONTEND_PRESCRIBER_DASH only when the user is
 * already logged in at plugins_loaded. The child theme registers endpoints here so
 * URLs like /my-account/reset-pin-request/ resolve reliably.
 */
function trimvia_register_prescriber_account_endpoints()
{
	$endpoints = array(
		'practitioner-orders',
		'reset-pin-request',
		'prescriber-signature',
		'prescription-more-info',
	);

	foreach ($endpoints as $endpoint) {
		add_rewrite_endpoint($endpoint, EP_ROOT | EP_PAGES);
	}
}
add_action('init', 'trimvia_register_prescriber_account_endpoints', 5);

/**
 * Register prescriber endpoints with WooCommerce query vars.
 *
 * @param array<string, string> $vars WooCommerce account query vars.
 * @return array<string, string>
 */
function trimvia_add_prescriber_account_wc_query_vars($vars)
{
	$prescriber_endpoints = array(
		'practitioner-orders',
		'reset-pin-request',
		'prescriber-signature',
		'prescription-more-info',
	);

	foreach ($prescriber_endpoints as $endpoint) {
		$vars[ $endpoint ] = $endpoint;
	}

	return $vars;
}
add_filter('woocommerce_get_query_vars', 'trimvia_add_prescriber_account_wc_query_vars');

/**
 * Bind a WooCommerce account endpoint handler exactly once.
 *
 * WooPW and the child theme can both attempt registration; dedupe to prevent
 * duplicated endpoint output (for example Change Signature rendering twice).
 *
 * @param string               $hook   WooCommerce account endpoint hook.
 * @param WOOPW_FRONTEND_PRESCRIBER_DASH $dash   Prescriber dashboard instance.
 * @param string               $method Callback method name.
 * @return void
 */
function trimvia_bind_prescriber_account_endpoint_once($hook, $dash, $method)
{
	static $bound = array();

	if (isset($bound[ $hook ])) {
		return;
	}

	remove_all_actions($hook);
	add_action($hook, array($dash, $method));
	$bound[ $hook ] = true;
}

/**
 * Bootstrap WooPW prescriber frontend hooks when plugin missed early registration.
 */
function trimvia_ensure_prescriber_frontend_dashboard_hooks()
{
	if (!trimvia_user_has_prescriber_access() || !class_exists('WOOPW_FRONTEND_PRESCRIBER_DASH')) {
		return;
	}

	$dash = WOOPW_FRONTEND_PRESCRIBER_DASH::get_instance();

	trimvia_bind_prescriber_account_endpoint_once(
		'woocommerce_account_reset-pin-request_endpoint',
		$dash,
		'woo_my_account_reset_pin_request_endpoint_content'
	);

	if (!has_action('template_redirect', array($dash, 'prescriber_reset_pin_request'))) {
		add_action('template_redirect', array($dash, 'prescriber_reset_pin_request'));
	}
	if (!has_action('template_redirect', array($dash, 'prescriber_reset_pin'))) {
		add_action('template_redirect', array($dash, 'prescriber_reset_pin'));
	}

	trimvia_bind_prescriber_account_endpoint_once(
		'woocommerce_account_practitioner-orders_endpoint',
		$dash,
		'woo_my_account_practitioner_orders_endpoint_content'
	);

	trimvia_bind_prescriber_account_endpoint_once(
		'woocommerce_account_prescriber-signature_endpoint',
		$dash,
		'woo_my_account_prescriber_update_signature_endpoint_content'
	);

	if (!has_filter('woocommerce_account_menu_items', array($dash, 'add_prescriber_dashboard_endpoints_to_myaccount_menu'))) {
		add_filter('woocommerce_account_menu_items', array($dash, 'add_prescriber_dashboard_endpoints_to_myaccount_menu'), 10, 2);
	}
}
add_action('wp', 'trimvia_ensure_prescriber_frontend_dashboard_hooks', 5);

/**
 * One-time rewrite flush after registering prescriber account endpoints.
 */
function trimvia_flush_rewrite_rules_once_for_account_endpoints()
{
	$flag = get_option('trimvia_prescriber_account_endpoints_flushed', '0');
	if ('2' === (string) $flag) {
		return;
	}

	flush_rewrite_rules(false);
	update_option('trimvia_prescriber_account_endpoints_flushed', '2', false);
}
add_action('init', 'trimvia_flush_rewrite_rules_once_for_account_endpoints', 30);

/**
 * One-time rewrite flush after registering the prescription-upload account endpoint.
 * Disabled together with trimvia_register_prescription_upload_endpoint() above.
 */
// function trimvia_flush_rewrite_rules_once_for_prescription_upload_endpoint()
// {
// 	if ('1' === (string) get_option('trimvia_prescription_upload_endpoint_flushed', '0')) {
// 		return;
// 	}
//
// 	flush_rewrite_rules(false);
// 	update_option('trimvia_prescription_upload_endpoint_flushed', '1', false);
// }
// add_action('init', 'trimvia_flush_rewrite_rules_once_for_prescription_upload_endpoint', 31);

/**
 * Whether the current user should be treated as a prescriber on the account area.
 *
 * Uses role membership as well as capability so newly converted prescribers still
 * receive onboarding popups and dashboard assets before capability caches refresh.
 *
 * @return bool
 */
function trimvia_user_has_prescriber_access()
{
	if (!is_user_logged_in()) {
		return false;
	}

	if (current_user_can('prescriber') || current_user_can('administrator')) {
		return true;
	}

	$user = wp_get_current_user();
	return in_array('prescriber', (array) $user->roles, true);
}

/**
 * Ensure the prescriber role exposes the prescriber capability expected by WooPW.
 */
function trimvia_fix_prescriber_capabilities()
{
	$role = get_role('prescriber');
	if ($role && !$role->has_cap('prescriber')) {
		$role->add_cap('prescriber');
	}
}
add_action('init', 'trimvia_fix_prescriber_capabilities');
add_action('plugins_loaded', 'trimvia_fix_prescriber_capabilities', 5);

/**
 * Remove the plugin footer renderer so the child theme can output one styled popup flow.
 */
function trimvia_remove_plugin_prescriber_pin_footer()
{
	global $wp_filter;

	if (!isset($wp_filter['wp_footer']) || !is_object($wp_filter['wp_footer'])) {
		return;
	}

	foreach ($wp_filter['wp_footer']->callbacks as $priority => $callbacks) {
		foreach ($callbacks as $callback) {
			$function = $callback['function'];
			if (is_array($function) && isset($function[1]) && 'generate_prescriber_pin' === $function[1]) {
				remove_action('wp_footer', $function, $priority);
			}
		}
	}
}
add_action('wp', 'trimvia_remove_plugin_prescriber_pin_footer', 20);

/**
 * Whether the current prescriber still needs PIN and/or signature onboarding.
 *
 * WooPW 1.8.x stores PIN/signature as JSON; legacy data may still be serialized.
 *
 * @return array{no_pin_set:bool,no_sign:bool}|false
 */
function trimvia_get_prescriber_onboarding_state()
{
	// Never show onboarding popups inside the Customizer preview.
	if (function_exists('is_customize_preview') && is_customize_preview()) {
		return false;
	}

	if (!trimvia_user_has_prescriber_access() || !function_exists('is_account_page') || !is_account_page()) {
		return false;
	}

	// Prefer WooPW plugin validation (JSON + legacy formats, fixed timestamp checks).
	if (class_exists('WOOPW_FRONTEND_PRESCRIBER_DASH')) {
		$dash = WOOPW_FRONTEND_PRESCRIBER_DASH::get_instance();
		if (method_exists($dash, 'check_presc_pin_and_esign')) {
			$validation = $dash->check_presc_pin_and_esign();
			if (!is_array($validation)) {
				return false;
			}

			return array(
				'no_pin_set' => !empty($validation['no_pin_set']),
				'no_sign'    => !empty($validation['no_sign']),
			);
		}
	}

	$current_user = wp_get_current_user();
	$no_pin_set   = true;
	$no_sign      = true;

	if (function_exists('woopw_get_pin_data')) {
		$pin_data = woopw_get_pin_data((int) $current_user->ID);
		if ($pin_data && !empty(array_filter($pin_data))) {
			$valid_id   = (int) ($pin_data['id'] ?? 0) === (int) $current_user->ID;
			$has_code   = !empty($pin_data['user_seccode']);
			$created_ts = strtotime($pin_data['created_at'] ?? '');
			$not_future = false !== $created_ts && $created_ts <= (time() + DAY_IN_SECONDS);

			if ($valid_id && $has_code && $not_future) {
				$no_pin_set = false;
			}
		}
	}

	$raw_sign = get_user_meta($current_user->ID, '_user_' . $current_user->ID . '_sign_data', true);
	if ($raw_sign) {
		$sign_data = json_decode($raw_sign, true);
		if (!is_array($sign_data)) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$sign_data = @unserialize($raw_sign);
		}
		if (is_array($sign_data) && !empty(array_filter($sign_data))) {
			$no_sign = false;
		}
	}

	return array(
		'no_pin_set' => $no_pin_set,
		'no_sign'    => $no_sign,
	);
}

/**
 * Blur the account page while PIN/signature onboarding is required.
 *
 * @param array $classes Body classes.
 * @return array
 */
function trimvia_prescriber_onboarding_body_class(array $classes)
{
	$state = trimvia_get_prescriber_onboarding_state();
	if ($state && ($state['no_pin_set'] || $state['no_sign'])) {
		$classes[] = 'presc-filter-page';
	}

	return $classes;
}
add_filter('body_class', 'trimvia_prescriber_onboarding_body_class');

/**
 * Ensure WooPW prescriber dashboard assets load for role-based prescribers.
 */
function trimvia_enqueue_prescriber_onboarding_assets()
{
	if (trimvia_is_customizer_preview() || trimvia_is_customizer_screen()) {
		return;
	}

	if (!function_exists('is_account_page') || !is_account_page() || !trimvia_user_has_prescriber_access()) {
		return;
	}

	if (wp_script_is('cflp-prescriber-dashboard', 'enqueued') && wp_style_is('cflp-prescriber-dashboard', 'enqueued')) {
		return;
	}

	if (!defined('CFLP_PLUGIN_URL') || !defined('CFLP_PLUGIN_DIR')) {
		return;
	}

	wp_enqueue_script('jquery');

	if (!wp_script_is('flashcanvas', 'registered')) {
		wp_register_script('flashcanvas', CFLP_PLUGIN_URL . 'assets/js/jsSignature/flashcanvas.js', array('jquery'), null, true);
	}
	if (!wp_script_is('jSignature', 'registered')) {
		wp_register_script('jSignature', CFLP_PLUGIN_URL . 'assets/js/jsSignature/jSignature.min.js', array('jquery', 'flashcanvas'), null, true);
	}
	if (!wp_script_is('cflp-prescriber-dashboard', 'registered')) {
		wp_register_script(
			'cflp-prescriber-dashboard',
			CFLP_PLUGIN_URL . 'assets/js/prescriber-dashboard.js',
			array('flashcanvas', 'jSignature'),
			null,
			true
		);
		wp_localize_script(
			'cflp-prescriber-dashboard',
			'woopwAjax',
			array(
				'ajax'                    => admin_url('admin-ajax.php'),
				'myaccount_url'           => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#',
				'nonce_order_actions'     => wp_create_nonce('woopw_order_actions'),
				'nonce_presc_actions'     => wp_create_nonce('woopw_presc_actions'),
				'nonce_more_info_form'    => wp_create_nonce('woopw_more_info_form'),
				'nonce_requested_info'    => wp_create_nonce('woopw_requested_info'),
				'nonce_process_more_info' => wp_create_nonce('woopw_process_more_info'),
				'nonce_view_prescription' => wp_create_nonce('woopw_view_prescription'),
				'nonce_prescriber_note'   => wp_create_nonce('woopw_prescriber_note'),
			)
		);
		wp_localize_script(
			'cflp-prescriber-dashboard',
			'pmNotes',
			array(
				'ajax'          => admin_url('admin-ajax.php'),
				'nonce'         => wp_create_nonce('woopw_prescriber_note'),
				'myaccount_url' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#',
			)
		);
	}
	if (!wp_style_is('cflp-prescriber-dashboard', 'registered')) {
		wp_register_style(
			'cflp-prescriber-dashboard',
			CFLP_PLUGIN_URL . 'assets/css/prescriber-dashboard.css',
			array(),
			file_exists(CFLP_PLUGIN_DIR . 'assets/css/prescriber-dashboard.css') ? filemtime(CFLP_PLUGIN_DIR . 'assets/css/prescriber-dashboard.css') : null
		);
	}

	wp_enqueue_script('flashcanvas');
	wp_enqueue_script('jSignature');
	wp_enqueue_script('cflp-prescriber-dashboard');
	wp_enqueue_style('cflp-prescriber-dashboard');
}
add_action('wp_enqueue_scripts', 'trimvia_enqueue_prescriber_onboarding_assets', 105);

/**
 * Render prescriber PIN/signature onboarding as centered popups on My Account.
 */
function trimvia_child_prescriber_popups()
{
	$state = trimvia_get_prescriber_onboarding_state();
	if (!$state) {
		return;
	}

	$current_user = wp_get_current_user();
	$myaccount_id = get_the_ID();

	if ($state['no_pin_set']) {
		wc_get_template(
			'myaccount/prescriber-pin-generation.php',
			array(
				'page_id' => $myaccount_id,
				'user_id' => $current_user->ID,
				'step'    => 1,
			)
		);
		wc_get_template(
			'myaccount/prescriber-signature-generation.php',
			array(
				'page_id' => $myaccount_id,
				'user_id' => $current_user->ID,
				'step'    => 2,
			)
		);
	} elseif ($state['no_sign']) {
		wc_get_template(
			'myaccount/prescriber-signature-generation.php',
			array(
				'page_id' => $myaccount_id,
				'user_id' => $current_user->ID,
			)
		);
	}
}
add_action('wp_footer', 'trimvia_child_prescriber_popups', 99);

/**
 * Remove the minimum row requirement (min = 0) for the homepage "Why Choose Trimvia" stats repeater.
 */
add_filter('acf/load_field/name=why_section_stats', 'trimvia_remove_why_stats_min_requirement');
function trimvia_remove_why_stats_min_requirement($field) {
	$field['min'] = 0;
	return $field;
}

/**
 * WooPW prescriber dashboard admin-ajax action names.
 *
 * @return array<int, string>
 */
function trimvia_get_prescriber_ajax_actions()
{
	return array(
		'prescriber_sec_pin',
		'prescriber_signature',
		'prescriber_update_signature',
		'prescriber_auth_sess',
		'render_prescription_more_info_form',
		'process_prescription_more_info',
		'render_prescription_requested_info',
		'prescriber_verification',
		'prescriber_order_status_action',
		'prescriber_order_actions',
		'prescriber_view_prescription',
	);
}

/**
 * Boot WooPW prescriber AJAX handlers after the current user is available.
 */
function trimvia_boot_woopw_prescriber_dashboard()
{
	if (!class_exists('WOOPW_ADMIN_PRESCRIBER_DASH') || !is_user_logged_in() || !trimvia_user_has_prescriber_access()) {
		return;
	}

	WOOPW_ADMIN_PRESCRIBER_DASH::get_instance();
}
add_action('init', 'trimvia_boot_woopw_prescriber_dashboard', 1);

/**
 * Discard stray output before prescriber AJAX handlers (prevents jQuery parsererror).
 */
function trimvia_guard_prescriber_ajax_output()
{
	if (!wp_doing_ajax()) {
		return;
	}

	$action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (!in_array($action, trimvia_get_prescriber_ajax_actions(), true)) {
		return;
	}

	while (ob_get_level() > 0) {
		ob_end_clean();
	}
}
add_action('init', 'trimvia_guard_prescriber_ajax_output', 0);

/**
 * Register missing WooPW prescriber dashboard AJAX hooks that fail to register
 * in the plugin constructor due to early is_user_logged_in() checks during load.
 */
function trimvia_register_missing_prescriber_ajax_hooks() {
	if (!class_exists('WOOPW_ADMIN_PRESCRIBER_DASH')) {
		return;
	}

	$ajax_actions = array(
		'prescriber_sec_pin'                  => 'prescriber_pin_generation',
		'prescriber_signature'                => 'prescriber_save_signature',
		'prescriber_update_signature'         => 'signature_modal',
		'prescriber_auth_sess'                => 'auth_prescriber_session',
		'render_prescription_more_info_form'  => 'render_prescription_more_info_form',
		'process_prescription_more_info'      => 'process_prescription_more_info',
		'render_prescription_requested_info'  => 'render_prescription_requested_info',
		'prescriber_verification'             => 'prescriber_verification',
		'prescriber_order_status_action'      => 'prescriber_prescription_actions',
		'prescriber_order_actions'            => 'prescriber_order_actions',
		'prescriber_view_prescription'        => 'prescriber_view_prescription',
	);

	foreach ($ajax_actions as $action => $method) {
		if (!has_action('wp_ajax_' . $action)) {
			add_action('wp_ajax_' . $action, function() use ($method) {
				$instance = WOOPW_ADMIN_PRESCRIBER_DASH::get_instance();
				if (method_exists($instance, $method)) {
					$instance->$method();
				}
			});
		}
	}
}
add_action('init', 'trimvia_register_missing_prescriber_ajax_hooks', 20);

/**
 * Clear stale reviewed prescription meta for orders that are still awaiting approval.
 *
 * Some WooPW orders can remain in `await-approval` while `_order_prescription_status`
 * or `_order_prescription_admin_status` still says the prescription was already
 * reviewed. That makes the modal show "View Prescription" instead of the expected
 * "Approve / Decline" actions. The parent/plugin flow treats `await-approval`
 * orders as actionable, so we normalize the stale meta before the AJAX handler
 * renders the modal or processes the action.
 *
 * @return void
 */
function trimvia_normalize_prescriber_awaiting_order_meta()
{
	if (!wp_doing_ajax()) {
		return;
	}

	$action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
	if ('prescriber_order_actions' !== $action) {
		return;
	}

	$type = isset($_POST['type']) ? sanitize_key(wp_unslash($_POST['type'])) : '';
	if (!in_array($type, array('view-prescriptions', 'approve', 'decline', 'decline_presc'), true)) {
		return;
	}

	$order_id = isset($_POST['order']) ? absint(wp_unslash($_POST['order'])) : 0;
	if ($order_id < 1) {
		return;
	}

	$order = wc_get_order($order_id);
	if (!$order instanceof WC_Order || 'await-approval' !== $order->get_status()) {
		return;
	}

	$prescription_status = (string) $order->get_meta('_order_prescription_status');
	$admin_status        = (string) $order->get_meta('_order_prescription_admin_status');
	$is_stale_reviewed   = in_array($prescription_status, array('approved', 'declined'), true)
		|| 'admin_declined' === $admin_status;

	if (!$is_stale_reviewed) {
		return;
	}

	$order->delete_meta_data('_order_prescription_status');
	$order->delete_meta_data('_order_prescription_admin_status');
	$order->delete_meta_data('_prescription_declined_reason');
	$order->save();
}
add_action('wp_ajax_prescriber_order_actions', 'trimvia_normalize_prescriber_awaiting_order_meta', 1);


/**
 * Whether a URL belongs to the current site and is safe to redirect to.
 *
 * @param string $url Candidate URL.
 * @return bool
 */
function trimvia_is_same_site_url($url)
{
	$url = trim((string) $url);
	if ($url === '' || !wp_http_validate_url($url)) {
		return false;
	}

	$home_host = wp_parse_url(home_url(), PHP_URL_HOST);
	$url_host  = wp_parse_url($url, PHP_URL_HOST);

	if ($home_host && $url_host && strcasecmp((string) $home_host, (string) $url_host) !== 0) {
		return false;
	}

	return true;
}

/**
 * Resolve a safe post-auth redirect from consultation entry links.
 *
 * @param string $fallback URL to use when no valid redirect is present.
 * @return string
 */
function trimvia_get_request_auth_redirect_target($fallback = '')
{
	foreach (array('redirect_to', '_redirect_url', 'redirect') as $key) {
		if (empty($_REQUEST[$key]) || !is_scalar($_REQUEST[$key])) {
			continue;
		}

		$raw    = wp_unslash((string) $_REQUEST[$key]);
		$target = wp_validate_redirect($raw, $fallback);

		if ($target && ($fallback === '' || $target !== $fallback)) {
			return $target;
		}

		if ($fallback === '' && trimvia_is_same_site_url($raw)) {
			return esc_url_raw($raw);
		}
	}

	return $fallback;
}

/**
 * Persist consultation return URL for the next auth redirect.
 *
 * @param string $url Target URL.
 */
function trimvia_set_consultation_auth_redirect($url)
{
	$target = wp_validate_redirect($url, '');
	if (!$target && trimvia_is_same_site_url($url)) {
		$target = esc_url_raw($url);
	}

	if (!$target || !function_exists('WC') || !WC()->session) {
		return;
	}

	WC()->session->set('trimvia_auth_redirect', $target);
}

/**
 * Store consultation return URL when a guest opens the account login page.
 */
function trimvia_capture_consultation_auth_redirect()
{
	if (is_user_logged_in() || !function_exists('is_account_page') || !is_account_page()) {
		return;
	}

	$target = trimvia_get_request_auth_redirect_target('');
	if ($target) {
		trimvia_set_consultation_auth_redirect($target);
	}
}
add_action('wp', 'trimvia_capture_consultation_auth_redirect', 5);

/**
 * Honour consultation login/register redirect links from the guest prompt.
 *
 * @param string $redirect Default WooCommerce redirect URL.
 * @return string
 */
function trimvia_consultation_auth_redirect($redirect)
{
	$target = trimvia_get_request_auth_redirect_target('');

	if (!$target && function_exists('WC') && WC()->session) {
		$stored = (string) WC()->session->get('trimvia_auth_redirect', '');
		if ($stored !== '') {
			$target = wp_validate_redirect($stored, '');
			if (!$target && trimvia_is_same_site_url($stored)) {
				$target = esc_url_raw($stored);
			}
		}
	}

	if ($target) {
		if (function_exists('WC') && WC()->session) {
			WC()->session->__unset('trimvia_auth_redirect');
		}

		return $target;
	}

	return $redirect;
}
add_filter('woocommerce_login_redirect', 'trimvia_consultation_auth_redirect', 999);
add_filter('woocommerce_registration_redirect', 'trimvia_consultation_auth_redirect', 20);

/**
 * Fallback redirect after login when WooCommerce lands on the account page.
 */
function trimvia_consultation_finish_post_login_redirect()
{
	if (!is_user_logged_in() || !function_exists('is_account_page') || !is_account_page()) {
		return;
	}

	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
		return;
	}

	$target = trimvia_get_request_auth_redirect_target('');
	if (!$target && function_exists('WC') && WC()->session) {
		$stored = (string) WC()->session->get('trimvia_auth_redirect', '');
		if ($stored !== '') {
			$target = wp_validate_redirect($stored, '');
			if (!$target && trimvia_is_same_site_url($stored)) {
				$target = esc_url_raw($stored);
			}
		}
	}

	if (!$target) {
		return;
	}

	if (function_exists('WC') && WC()->session) {
		WC()->session->__unset('trimvia_auth_redirect');
	}

	wp_safe_redirect($target);
	exit;
}
add_action('template_redirect', 'trimvia_consultation_finish_post_login_redirect', 5);

/**
 * Hide the default WordPress admin footer area.
 */
function trimvia_hide_admin_footer_area()
{
	echo '<style id="trimvia-hide-admin-footer">#wpwrap #wpfooter{display:none!important;}</style>';
}
add_action('admin_head', 'trimvia_hide_admin_footer_area');

/**
 * Remove the "Safety" tab from all single product detail views.
 */
add_filter( 'woocommerce_product_tabs', 'trimvia_remove_safety_tab', 10000 );
function trimvia_remove_safety_tab( $tabs ) {
	unset( $tabs['safety'] );
	unset( $tabs['trimvia_safety'] );
	return $tabs;
}