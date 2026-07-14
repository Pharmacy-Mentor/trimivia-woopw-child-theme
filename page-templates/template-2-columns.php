<?php
/**
 * Template Name: 2 Columns Template
 * Template Post Type: page
 *
 * Child override preserving parent content behavior.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<section class="page-hero page-hero--service" style="background-image:url(<?php echo esc_url(get_the_post_thumbnail_url(null, 'full') ?: get_placeholder_image()); ?>);background-size:cover;background-position:center;">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--service">
			<a href="<?php echo esc_url(site_url()); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span class="breadcrumb-current"><?php the_title(); ?></span>
		</div>
		<h1><?php the_title(); ?></h1>
	</div>
</section>

<section class="page-section service-single-section about-section">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 col-md-12 my-4">
				<div class="template-content-left article-content">
					<?php if (have_posts()) : while (have_posts()) : the_post(); the_content(); endwhile; endif; ?>
				</div>
			</div>
			<div class="col-lg-6 col-md-12 my-4">
				<div class="template-content-right article-content">
					<?php if (get_field('right_column_content')) : echo wp_kses_post(get_field('right_column_content')); endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
