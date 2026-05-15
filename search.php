<?php
/**
 * Search results template - child theme design.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$search_query = get_search_query();
$result_count = (int) $GLOBALS['wp_query']->found_posts;
?>
<section class="page-hero page-hero--search">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--search">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Search', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php printf(esc_html__('Search results for "%s"', 'theme-woopm-child'), esc_html($search_query)); ?></h1>
		<p>
			<?php
			printf(
				/* translators: %s number of results */
				esc_html(_n('%s result found', '%s results found', $result_count, 'theme-woopm-child')),
				esc_html(number_format_i18n($result_count))
			);
			?>
		</p>
	</div>
</section>

<section class="page-section trimvia-search-results">
	<div class="container">
		<form class="trimvia-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
			<label class="screen-reader-text" for="trimvia-search"><?php esc_html_e('Search', 'theme-woopm-child'); ?></label>
			<input id="trimvia-search" type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search posts and pages...', 'theme-woopm-child'); ?>">
			<button type="submit"><?php esc_html_e('Search', 'theme-woopm-child'); ?></button>
		</form>

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
							<div class="product-type"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?></div>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 26, '...')); ?></p>
							<div class="product-footer">
								<div class="trimvia-post-card-tax"><?php echo esc_html(get_the_date('j M Y')); ?></div>
								<a class="btn-shop" href="<?php the_permalink(); ?>">
									<?php esc_html_e('Open result', 'theme-woopm-child'); ?>
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
				<h3><?php esc_html_e('No results found.', 'theme-woopm-child'); ?></h3>
				<p><?php esc_html_e('Try different keywords or browse treatments from the main menu.', 'theme-woopm-child'); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
