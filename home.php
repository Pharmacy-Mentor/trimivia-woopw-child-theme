<?php
/**
 * Blog posts index (Posts page) - child theme design.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$posts_page_id = (int) get_option('page_for_posts');
$blog_title = $posts_page_id > 0 ? get_the_title($posts_page_id) : single_post_title('', false);
if (!is_string($blog_title) || '' === trim($blog_title)) {
	$blog_title = __('News & Updates', 'theme-woopm-child');
}

$blog_intro = '';
$search_form_title = '';
$search_shortcode = '';

if (function_exists('get_field') && $posts_page_id > 0) {
	$blog_intro = (string) get_field('banner_blog_short', $posts_page_id);
	$search_form_title = (string) get_field('search_form_title', $posts_page_id);
	$search_shortcode = (string) get_field('search_shortcode', $posts_page_id);
}
?>
<section class="page-hero page-hero--blog">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--blog">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($blog_title); ?></span>
		</div>
		<h1><?php echo esc_html($blog_title); ?></h1>
		<?php if ('' !== trim(wp_strip_all_tags($blog_intro))) : ?>
			<p><?php echo esc_html(wp_strip_all_tags($blog_intro)); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="page-section trimvia-blog-archive">
	<div class="container">
		<div class="trimvia-search-form-wrap">
			<?php if ('' !== trim($search_form_title)) : ?>
				<h2 class="stitle" style="font-size:clamp(24px,3vw,34px);"><?php echo esc_html($search_form_title); ?></h2>
			<?php endif; ?>
			<?php if ('' !== trim($search_shortcode)) : ?>
				<?php echo do_shortcode($search_shortcode); ?>
			<?php else : ?>
				<form class="trimvia-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
					<label class="screen-reader-text" for="trimvia-blog-search"><?php esc_html_e('Search posts', 'theme-woopm-child'); ?></label>
					<input id="trimvia-blog-search" type="search" name="s" placeholder="<?php esc_attr_e('Search blog entries...', 'theme-woopm-child'); ?>">
					<button type="submit"><?php esc_html_e('Search', 'theme-woopm-child'); ?></button>
				</form>
			<?php endif; ?>
		</div>

		<?php if (have_posts()) : ?>
			<div class="shop-grid trimvia-blog-grid">
				<?php while (have_posts()) : the_post(); ?>
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
							<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 28, '...')); ?></p>
							<div class="product-footer">
								<div class="trimvia-post-card-tax"><?php echo wp_kses_post(get_the_category_list(', ')); ?></div>
								<a class="btn-shop" href="<?php the_permalink(); ?>">
									<?php esc_html_e('Read article', 'theme-woopm-child'); ?>
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
								</a>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="trimvia-blog-pagination">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __('Previous', 'theme-woopm-child'),
						'next_text' => __('Next', 'theme-woopm-child'),
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="shop-empty">
				<h3><?php esc_html_e('No blog posts found.', 'theme-woopm-child'); ?></h3>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
