<?php
/**
 * Condition archive (after consultation) - child theme UI override.
 *
 * Keeps parent recommendation/business logic intact while rendering
 * with Trimvia child frontend components.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$term = isset($args['term']) ? $args['term'] : null;
$term_id = isset($args['term_id']) ? (int) $args['term_id'] : 0;

if (!$term instanceof WP_Term || $term_id < 1) {
	return;
}

$page_id = 3098;
$term_acf_id = $term->taxonomy . '_' . $term->term_id;

// Parent-compatible recommendation logic.
$recommend_enabled = class_exists('WOOPW_ADDON_MANAGER') && WOOPW_ADDON_MANAGER::enable_product_recommend();
$recommend_class = false;
$recommended_products = array();
$recommend_product_ids = array();

if ($recommend_enabled && !empty($_SESSION['wp_cflp_form_data'])) {
	$products_id_list = woopw_get_products_by_condition_slug(
		$_SESSION['wp_cflp_form_data'],
		$term->slug
	);

	$exclude_products = $products_id_list['exclude_product'] ?? array();
	$recommend_product_ids = $products_id_list['recommend_product'] ?? array();
	$recommend_product_ids = array_diff($recommend_product_ids, $exclude_products);

	if (!empty($recommend_product_ids) && function_exists('has_consultation_for_condition') && has_consultation_for_condition($term->slug)) {
		$recommended_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post__in'       => $recommend_product_ids,
				'orderby'        => 'post__in',
			)
		);
		$recommend_class = !empty($recommended_products);
	}
}

// Main products (exclude recommended list when active).
$products_args = array(
	'post_type'   => 'product',
	'numberposts' => -1,
	'tax_query'   => array(
		array(
			'taxonomy' => 'condition',
			'field'    => 'term_id',
			'terms'    => $term_id,
		),
	),
);

if (!empty($recommend_product_ids)) {
	$products_args['post__not_in'] = $recommend_product_ids;
}

$products = get_posts($products_args);

// Banner copy fallback (term first, then shared page fields).
$condition_banner_heading = get_field('condition_banner_heading', $term_acf_id);
if (!is_string($condition_banner_heading) || '' === trim($condition_banner_heading)) {
	$condition_banner_heading = get_field('treatment_banner_heading', $page_id);
}
if (!is_string($condition_banner_heading) || '' === trim($condition_banner_heading)) {
	$condition_banner_heading = __('Consultation submitted successfully', 'theme-woopm-child');
}

$condition_banner_content = get_field('condition_banner_content', $term_acf_id);
if (!is_string($condition_banner_content) || '' === trim($condition_banner_content)) {
	$condition_banner_content = (string) get_field('treatment_banner_content', $page_id);
}

$banner_trust_content = get_field('banner_trust_content', $term_acf_id);
if (!is_string($banner_trust_content) || '' === trim($banner_trust_content)) {
	$banner_trust_content = (string) get_field('banner_trust_content', $page_id);
}

$banner_dispatch_content = get_field('banner_dispatch_content', $term_acf_id);
if (!is_string($banner_dispatch_content) || '' === trim($banner_dispatch_content)) {
	$banner_dispatch_content = (string) get_field('banner_dispatch_content', $page_id);
}

$condition_banner_content_text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $condition_banner_content)));
$banner_dispatch_content_text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $banner_dispatch_content)));

$suitable_product_heading = get_field('suitable_product_heading', $term_acf_id);
if (!is_string($suitable_product_heading) || '' === trim($suitable_product_heading)) {
	$suitable_product_heading = get_field('suitable_product_heading', $page_id);
}
if (!is_string($suitable_product_heading) || '' === trim($suitable_product_heading)) {
	$suitable_product_heading = __('The products below are also suitable for you.', 'theme-woopm-child');
}

// Submission progress items.
$steps_source = false;
if (have_rows('submitting_featured_content', $term_acf_id)) {
	$steps_source = $term_acf_id;
} elseif (have_rows('submitting_featured_content', $page_id)) {
	$steps_source = $page_id;
}

$default_steps = array(
	array(
		'icon'    => 'fa fa-check-circle',
		'heading' => __('Submit Consultation', 'theme-woopm-child'),
	),
	array(
		'icon'    => 'fa-solid fa-arrow-rotate-right',
		'heading' => __('Choose Treatment', 'theme-woopm-child'),
	),
	array(
		'icon'    => 'fa-solid fa-arrow-rotate-right',
		'heading' => __('Checkout', 'theme-woopm-child'),
	),
);

$steps_rows = $default_steps;
if ($steps_source) {
	$acf_rows = get_field('submitting_featured_content', $steps_source);
	if (is_array($acf_rows) && !empty($acf_rows)) {
		$has_dynamic = false;
		foreach ($acf_rows as $row) {
			if (!empty($row['icon']) || !empty($row['heading'])) {
				$has_dynamic = true;
				break;
			}
		}
		if ($has_dynamic) {
			$steps_rows = $acf_rows;
		}
	}
}
?>

<section class="page-hero trimvia-condition-complete-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--condition-complete">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($term->name); ?></span>
		</div>
		<div class="trimvia-condition-complete-inner">
			<h1 class="trimvia-condition-complete-title">
				<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
				<?php echo esc_html($condition_banner_heading); ?>
			</h1>
			<?php if ('' !== $condition_banner_content_text) : ?>
				<div class="trimvia-condition-complete-desc">
					<p><?php echo esc_html($condition_banner_content_text); ?></p>
				</div>
			<?php endif; ?>
			<?php if ('' !== $banner_dispatch_content_text) : ?>
				<div class="trimvia-condition-complete-dispatch">
					<p><?php echo esc_html($banner_dispatch_content_text); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if (!empty($steps_rows)) : ?>
	<section class="page-section page-section--alt trimvia-condition-complete-steps">
		<div class="container">
			<div class="shop-trust">
				<?php
				$visible_index = 0;
				foreach ($steps_rows as $row) :
					$icon = isset($row['icon']) ? (string) $row['icon'] : '';
					$heading = isset($row['heading']) ? trim((string) $row['heading']) : '';
					if ('' === $icon && '' === $heading) {
						continue;
					}
					$is_completed = (0 === $visible_index);
					$visible_index++;
					?>
					<div class="shop-trust-item<?php echo $is_completed ? ' is-completed' : ''; ?>">
						<div class="shop-trust-icon">
							<?php if ('' !== $icon) : ?>
								<i class="<?php echo esc_attr($icon); ?>" aria-hidden="true"></i>
							<?php else : ?>
								<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
							<?php endif; ?>
						</div>
						<h4><?php echo esc_html($heading); ?></h4>
						<p>
							<?php echo $is_completed ? esc_html__('Completed', 'theme-woopm-child') : esc_html__('Next step', 'theme-woopm-child'); ?>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ($recommend_class) : ?>
	<section id="recommended_products" class="page-section trimvia-condition-complete-recommended">
		<div class="container">
			<div class="shop-header rv">
				<div>
					<h2 class="stitle"><?php esc_html_e('Recommended for your consultation', 'theme-woopm-child'); ?></h2>
					<span class="shop-count"><?php esc_html_e('Matched from your submitted answers', 'theme-woopm-child'); ?></span>
				</div>
			</div>
			<div class="shop-grid">
				<?php foreach ($recommended_products as $recommended_post) : ?>
					<?php
					$recommended_product = wc_get_product($recommended_post->ID);
					if (!$recommended_product instanceof WC_Product) {
						continue;
					}
					get_template_part(
						'template-parts/trimvia',
						'shop-product-card',
						array('product' => $recommended_product)
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="page-section trimvia-condition-complete-products" id="treatments">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<h2 class="stitle"><?php echo esc_html($suitable_product_heading); ?></h2>
				<span class="shop-count">
					<?php
					printf(
						/* translators: %s: number of products */
						esc_html(_n('%s treatment available', '%s treatments available', count($products), 'theme-woopm-child')),
						esc_html(number_format_i18n(count($products)))
					);
					?>
				</span>
			</div>
		</div>
		<?php if (!empty($products)) : ?>
			<div class="shop-grid">
				<?php foreach ($products as $product_post) : ?>
					<?php
					$shop_product = wc_get_product($product_post->ID);
					if (!$shop_product instanceof WC_Product) {
						continue;
					}
					get_template_part(
						'template-parts/trimvia',
						'shop-product-card',
						array('product' => $shop_product)
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="shop-empty">
				<h3><?php esc_html_e('No products found.', 'theme-woopm-child'); ?></h3>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="page-section page-section--alt trimvia-condition-complete-usp">
	<div class="container">
		<div class="shop-trust">
			<?php
			$usp_rows = get_field('footer_usp_bar', $page_id);
			if (is_array($usp_rows) && !empty($usp_rows)) :
				foreach ($usp_rows as $usp_row) :
					$usp_icon_id = $usp_row['icon'] ?? 0;
					$usp_title = isset($usp_row['title']) ? trim((string) $usp_row['title']) : '';
					if ('' === $usp_title) {
						continue;
					}
					?>
					<div class="shop-trust-item">
						<div class="shop-trust-icon">
							<?php if (!empty($usp_icon_id)) : ?>
								<?php echo wp_get_attachment_image((int) $usp_icon_id, 'thumbnail', false, array('loading' => 'lazy')); ?>
							<?php else : ?>
								<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
							<?php endif; ?>
						</div>
						<h4><?php echo esc_html($usp_title); ?></h4>
					</div>
				<?php
				endforeach;
			else :
				?>
				<div class="shop-trust-item">
					<div class="shop-trust-icon"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></div>
					<h4><?php esc_html_e('UK-Prescribing Pharmacists', 'theme-woopm-child'); ?></h4>
				</div>
				<div class="shop-trust-item">
					<div class="shop-trust-icon"><i class="fa-solid fa-notes-medical" aria-hidden="true"></i></div>
					<h4><?php esc_html_e('No GP Appointments Needed', 'theme-woopm-child'); ?></h4>
				</div>
				<div class="shop-trust-item">
					<div class="shop-trust-icon"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
					<h4><?php esc_html_e('Fast & Discreet Delivery', 'theme-woopm-child'); ?></h4>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		if (!document.body.classList.contains('tax-condition')) {
			return;
		}

		setTimeout(function () {
			var targetId = <?php echo wp_json_encode($recommend_class ? 'recommended_products' : 'treatments'); ?>;
			var target = document.getElementById(targetId);
			if (!target) {
				return;
			}

			var elementTop = target.getBoundingClientRect().top + window.pageYOffset;
			window.scrollTo({
				top: elementTop - 50,
				behavior: 'smooth'
			});
		}, 1000);
	});
</script>
