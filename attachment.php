<?php get_header(); ?>
<?php global $post; ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<section class="page-hero page-hero--attachment">
		<div class="hero-noise"></div>
		<div class="container">
			<div class="breadcrumb">
				<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
				<span>&rsaquo;</span>
				<span><?php the_title(); ?></span>
			</div>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</div>
	</section>

	<main id="content" class="page-section trimvia-legacy-content trimvia-attachment-page">
		<div class="container">
			<div class="trimvia-legacy-main-grid">
				<div class="trimvia-legacy-main">
					<article id="post-<?php the_ID(); ?>" <?php post_class('trimvia-legacy-card'); ?>>
						<header class="trimvia-legacy-entry-header">
							<?php edit_post_link(); ?>
							<?php get_template_part('entry', 'meta'); ?>
							<a class="trimvia-parent-link" href="<?php echo esc_url(get_permalink($post->post_parent)); ?>" title="<?php printf(esc_html__('Return to %s', 'blankslate'), esc_html(get_the_title($post->post_parent), 1)); ?>" rev="attachment"><?php printf(esc_html__('%s Return to ', 'blankslate'), '<span class="meta-nav">&larr;</span>'); ?><?php echo get_the_title($post->post_parent); ?></a>
							<nav id="nav-above" class="navigation trimvia-legacy-nav">
								<div class="nav-previous"><?php previous_image_link(false, '&lsaquo;'); ?></div>
								<div class="nav-next"><?php next_image_link(false, '&rsaquo;'); ?></div>
							</nav>
						</header>
						<div class="entry-content">
							<div class="entry-attachment">
								<?php if (wp_attachment_is_image($post->ID)) : $att_image = wp_get_attachment_image_src($post->ID, 'full'); ?>
									<p class="attachment"><a href="<?php echo esc_url(wp_get_attachment_url($post->ID)); ?>" title="<?php the_title_attribute(); ?>" rel="attachment"><img src="<?php echo esc_url($att_image[0]); ?>" width="<?php echo esc_attr($att_image[1]); ?>" height="<?php echo esc_attr($att_image[2]); ?>" class="attachment-full" alt="<?php $post->post_excerpt; ?>" /></a></p>
								<?php else : ?>
									<a href="<?php echo esc_url(wp_get_attachment_url($post->ID)); ?>" title="<?php echo esc_attr(get_the_title($post->ID), 1); ?>" rel="attachment"><?php echo esc_url(basename($post->guid)); ?></a>
								<?php endif; ?>
							</div>
							<div class="entry-caption"><?php if (!empty($post->post_excerpt)) { the_excerpt(); } ?></div>
							<?php if (has_post_thumbnail()) { the_post_thumbnail(); } ?>
						</div>
					</article>
					<?php comments_template(); ?>
				</div>
				<?php get_sidebar(); ?>
			</div>
		</div>
	</main>
<?php endwhile; endif; ?>
<?php get_footer(); ?>