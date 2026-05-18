<?php
/**
 * Single post template - child theme design.
 *
 * Falls back to parent for non-blog post types.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

if ('product' === get_post_type()) {
	$child_product_template = trailingslashit(get_stylesheet_directory()) . 'woocommerce/single-product.php';
	if (file_exists($child_product_template)) {
		include $child_product_template;
		return;
	}
}

if ('post' !== get_post_type()) {
	$parent_single_template = trailingslashit(get_template_directory()) . 'single.php';
	if (file_exists($parent_single_template) && function_exists('get_placeholder_image')) {
		include $parent_single_template;
		return;
	}
}

get_header();

the_post();
$posts_page_id = (int) get_option('page_for_posts');
$back_url = $posts_page_id > 0 ? get_permalink($posts_page_id) : home_url('/');
?>
<section class="page-hero page-hero--blog-single">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--blog-single">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('Blog', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php the_title(); ?></span>
		</div>
		<h1><?php the_title(); ?></h1>
		<p><?php echo esc_html(get_the_date('j F Y')); ?></p>
	</div>
</section>

<section class="page-section trimvia-blog-single">
	<div class="container">
		<div class="trimvia-blog-single-head">
			<a href="<?php echo esc_url($back_url); ?>" class="btn-shop">
				<?php esc_html_e('Back to all articles', 'theme-woopm-child'); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
			</a>
		</div>

		<?php if (has_post_thumbnail()) : ?>
			<div class="trimvia-blog-single-image">
				<?php the_post_thumbnail('full', array('loading' => 'lazy')); ?>
			</div>
		<?php endif; ?>

		<div class="trimvia-blog-single-content">
			<?php the_content(); ?>
		</div>

		<div class="trimvia-blog-single-meta">
			<div class="trimvia-blog-single-tax">
				<strong><?php esc_html_e('Categories:', 'theme-woopm-child'); ?></strong>
				<?php echo wp_kses_post(get_the_category_list(', ')); ?>
			</div>
			<?php if (has_tag()) : ?>
				<div class="trimvia-blog-single-tax">
					<strong><?php esc_html_e('Tags:', 'theme-woopm-child'); ?></strong>
					<?php echo wp_kses_post(get_the_tag_list('', ', ')); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
$related_posts = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array(get_the_ID()),
		'category__in'   => wp_get_post_categories(get_the_ID()),
	)
);
if (!empty($related_posts)) :
	?>
	<section class="page-section page-section--alt trimvia-blog-related">
		<div class="container">
			<div class="shop-header rv">
				<div>
					<h2 class="stitle"><?php esc_html_e('Related articles', 'theme-woopm-child'); ?></h2>
					<span class="shop-count"><?php esc_html_e('More updates you may find useful', 'theme-woopm-child'); ?></span>
				</div>
			</div>
			<div class="shop-grid trimvia-blog-grid">
				<?php foreach ($related_posts as $post) : setup_postdata($post); ?>
					<article class="product-card trimvia-post-card rv">
						<a class="product-img trimvia-post-card-image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
							<?php if (has_post_thumbnail()) : ?>
								<div class="product-img-media"><?php the_post_thumbnail('large', array('loading' => 'lazy')); ?></div>
							<?php else : ?>
								<div class="product-img-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8l8 8v8a2 2 0 0 1-2 2z"></path><path d="M13 3v8h8"></path></svg>
								</div>
							<?php endif; ?>
						</a>
						<div class="product-body">
							<div class="product-type"><?php echo esc_html(get_the_date('j M Y')); ?></div>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 24, '...')); ?></p>
							<div class="product-footer">
								<div class="trimvia-post-card-tax"><?php echo wp_kses_post(get_the_category_list(', ')); ?></div>
								<a class="btn-shop" href="<?php the_permalink(); ?>">
									<?php esc_html_e('Read article', 'theme-woopm-child'); ?>
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
								</a>
							</div>
						</div>
					</article>
				<?php endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
