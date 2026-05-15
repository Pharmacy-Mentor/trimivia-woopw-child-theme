<?php
/**
 * Blog listing section.
 *
 * Child override preserving parent query/pagination behavior.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$paged = (get_query_var('paged')) ? (int) get_query_var('paged') : 1;
$loop = new WP_Query(
	array(
		'post_type'      => 'post',
		'paged'          => $paged,
		'posts_per_page' => (int) get_option('posts_per_page'),
		'order'          => 'DESC',
	)
);

if (!$loop->have_posts()) {
	wp_reset_query();
	return;
}
?>
<section class="page-section page-section--alt trimvia-blog-list-block">
	<div class="container">
		<div class="shop-header rv">
			<div>
				<p class="stag"><?php esc_html_e('Blog', 'theme-woopm-child'); ?></p>
				<h2 class="stitle"><?php esc_html_e('Latest updates', 'theme-woopm-child'); ?></h2>
				<span class="shop-count"><?php echo wp_kses_post((string) get_field('banner_blog_short', get_option('page_for_posts'))); ?></span>
			</div>
		</div>

		<div class="shop-grid trimvia-blog-grid">
			<?php while ($loop->have_posts()) : $loop->the_post(); ?>
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
						<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 26, '...')); ?></p>
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
			<?php render_pagination($loop); ?>
		</div>
	</div>
</section>
<?php wp_reset_query(); ?>
