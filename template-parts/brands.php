<?php
/**
 * Brands section.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$brands_terms = get_field('brands', $front_page_id);
if (!is_array($brands_terms) || empty($brands_terms)) {
	return;
}
?>
<section id="brands-sec" class="page-section trimvia-brands">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<?php if (get_field('brands_heading', $front_page_id)) : ?>
					<h2 class="stitle"><?php echo esc_html(get_field('brands_heading', $front_page_id)); ?></h2>
				<?php endif; ?>
				<?php if (get_field('brands_short_description', $front_page_id)) : ?>
					<span class="shop-count"><?php echo esc_html(wp_strip_all_tags(get_field('brands_short_description', $front_page_id))); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="shop-grid">
			<?php foreach ($brands_terms as $term) : ?>
				<?php
				if (!$term instanceof WP_Term) {
					continue;
				}
				$brand_link = get_term_link($term->term_id, 'berocket_brand');
				if (is_wp_error($brand_link)) {
					continue;
				}
				$brand_logo = (string) get_term_meta($term->term_id, 'brand_image_url', true);
				?>
				<article class="product-card rv">
					<a class="product-img" href="<?php echo esc_url($brand_link); ?>">
						<?php if ($brand_logo) : ?>
							<div class="product-img-media"><img src="<?php echo esc_url($brand_logo); ?>" alt="<?php echo esc_attr($term->name); ?>" loading="lazy"></div>
						<?php else : ?>
							<div class="product-img-icon"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i></div>
						<?php endif; ?>
					</a>
					<div class="product-body">
						<h3><a href="<?php echo esc_url($brand_link); ?>"><?php echo esc_html($term->name); ?></a></h3>
						<div class="product-footer">
							<a class="btn-shop" href="<?php echo esc_url($brand_link); ?>">
								<?php esc_html_e('View brand', 'theme-woopm-child'); ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
							</a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="row brands-cta-container mt-4">
			<div class="col-lg-6 col-md-6 mb-3 text-md-left">
				<?php if (get_field('brands_call_to_action_1', $front_page_id)) : ?>
					<a class="btn-shop" href="<?php echo esc_url(get_field('brands_call_to_action_1', $front_page_id)['url'] ?: '#'); ?>"><?php echo esc_html(get_field('brands_call_to_action_1', $front_page_id)['title']); ?></a>
				<?php endif; ?>
			</div>
			<div class="col-lg-6 col-md-6 mb-3 text-md-right">
				<?php if (get_field('brands_call_to_action_2', $front_page_id)) : ?>
					<a class="btn-shop-outline" href="<?php echo esc_url(get_field('brands_call_to_action_2', $front_page_id)['url'] ?: '#'); ?>"><?php echo esc_html(get_field('brands_call_to_action_2', $front_page_id)['title']); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
