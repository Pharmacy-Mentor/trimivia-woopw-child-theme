<?php

get_header();

$term = get_queried_object();

// Default banner image
$bg_image = site_url() . '/wp-content/uploads/2024/08/3cdb29b25f8cd62fc1b6b2bac1f81210-1024x683.png';
?>

<section class="page-hero page-hero--service-category" style="background-image: linear-gradient(rgba(13, 19, 48, 0.72), rgba(13, 19, 48, 0.72)), url('<?php echo esc_url($bg_image); ?>'); background-size: cover; background-position: center;">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(site_url()); ?>">Home</a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url(site_url('/services/')); ?>">Services</a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($term ? $term->name : 'Category'); ?></span>
		</div>
		<h1><?php echo esc_html($term ? $term->name : 'Services'); ?></h1>
		<?php if ($term && !empty($term->description)) : ?>
			<p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($term->description), 28, '...')); ?></p>
		<?php endif; ?>
	</div>
</section>


<section id="services" class="page-section trimvia-service-category-page">
	<div class="container">
		<?php if ($term && !empty($term->description)) : ?>
			<div class="trimvia-legacy-card trimvia-taxonomy-description">
				<?php echo wpautop($term->description); ?>
			</div>
		<?php endif; ?>
		<div class="shop-grid trimvia-service-category-grid">
        <?php
        $args = array(
            'post_type'      => 'service',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'service-category',
                    'field'    => 'term_id',
                    'terms'    => $term ? $term->term_id : 0,
                ),
            ),
        );

        $service_query = new WP_Query($args);

        if ($service_query->have_posts()) :
            while ($service_query->have_posts()) : $service_query->the_post();

                if (has_post_thumbnail()) {
                    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                } else {
                    $image_url = site_url() . '/wp-content/uploads/2024/08/3cdb29b25f8cd62fc1b6b2bac1f81210-1024x683.png';
                }

                $permalink = get_permalink();
                $title = get_the_title();
        ?>
				<article class="product-card trimvia-service-card rv">
					<a class="product-img" href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>">
						<div class="product-img-media">
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
						</div>
					</a>
					<div class="product-body">
						<div class="product-type"><?php esc_html_e('Service', 'theme-woopm-child'); ?></div>
						<h3><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>

                            <?php
                            $excerpt = get_the_excerpt();
                            if (empty($excerpt)) {
                                $excerpt = wp_strip_all_tags(get_the_content());
                            }

                            $excerpt = mb_substr($excerpt, 0, 100);

                            if (strlen($excerpt) === 100) {
                                $excerpt .= '...';
                            }
                            ?>

						<p><?php echo esc_html($excerpt); ?></p>

						<div class="product-footer">
							<a class="btn-shop" href="<?php echo esc_url($permalink); ?>">
								<?php esc_html_e('Read more', 'theme-woopm-child'); ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
							</a>
						</div>

					</div>
				</article>
        <?php
            endwhile;
		else :
			?>
			<div class="shop-empty trimvia-legacy-card">
				<h3><?php esc_html_e('No services found in this category yet.', 'theme-woopm-child'); ?></h3>
			</div>
			<?php
        endif;
        wp_reset_postdata();
        ?>
        </div>
	</div>
</section>

<?php get_footer(); ?>
