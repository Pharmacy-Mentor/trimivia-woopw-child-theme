<?php get_header(); ?>
<?php the_post(); ?>
<section class="page-hero page-hero--author">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Author', 'theme-woopm-child'); ?></span>
		</div>
		<h1 class="entry-title author"><?php the_author_link(); ?></h1>
		<?php if ('' !== get_the_author_meta('user_description')) : ?>
			<p class="archive-meta"><?php echo esc_html(get_the_author_meta('user_description')); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php rewind_posts(); ?>

<main id="content" class="page-section trimvia-legacy-content trimvia-author-archive">
	<div class="container">
		<div class="trimvia-legacy-main-grid">
			<div class="trimvia-legacy-main">
				<?php while (have_posts()) : the_post(); ?>
					<?php get_template_part('entry'); ?>
				<?php endwhile; ?>
				<?php get_template_part('nav', 'below'); ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>