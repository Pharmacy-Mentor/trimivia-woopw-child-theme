<?php
/**
 * Blog archives (category/tag/date/author) - child theme design.
 *
 * Falls back to parent archive template for non-post-type archives.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

if (is_post_type_archive() && !is_post_type_archive('post')) {
	$parent_archive_template = trailingslashit(get_template_directory()) . 'archive.php';
	if (file_exists($parent_archive_template)) {
		include $parent_archive_template;
		return;
	}
}

get_header();

$archive_title = get_the_archive_title();
$archive_description_raw = get_the_archive_description();
$archive_description = trim(wp_strip_all_tags((string) $archive_description_raw));
if ('' === $archive_description) {
	$archive_description = __('Explore the latest treatment insights, pharmacy guidance, and health updates from our clinical team.', 'theme-woopm-child');
}
?>
<section class="page-hero page-hero--blog">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--blog">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url(get_permalink((int) get_option('page_for_posts'))); ?>"><?php esc_html_e('Blog', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html(wp_strip_all_tags($archive_title)); ?></span>
		</div>
		<h1><?php echo esc_html(wp_strip_all_tags($archive_title)); ?></h1>
		<p><?php echo esc_html($archive_description); ?></p>
	</div>
</section>

<section class="page-section trimvia-blog-archive">
	<div class="container">
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
								<div class="trimvia-post-card-tax">
									<?php
									$categories_list = get_the_category_list(', ');
									if ($categories_list) {
										echo wp_kses_post($categories_list);
									}
									?>
								</div>
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
				<h3><?php esc_html_e('No posts found in this archive.', 'theme-woopm-child'); ?></h3>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
