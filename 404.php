<?php get_header(); ?>

<section class="page-hero page-hero--404">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('404', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Page not found', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Sorry, the page you are looking for does not exist or has moved.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section trimvia-legacy-content trimvia-404-section">
	<div class="container">
		<div class="trimvia-legacy-card trimvia-404-card">
			<p><?php esc_html_e('Try returning to the homepage or browsing our latest treatment and health content.', 'theme-woopm-child'); ?></p>
			<a class="btn-shop" href="<?php echo esc_url(home_url('/')); ?>">
				<?php esc_html_e('Go to homepage', 'theme-woopm-child'); ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
			</a>
		</div>
	</div>
</section>

<?php get_footer(); ?>