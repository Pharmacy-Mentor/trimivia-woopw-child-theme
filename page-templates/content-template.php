<?php
/**
 * Template Name: Column Content Template
 *
 * Child override for the parent content template.
 * Keeps existing ACF field behavior while rendering with child layout classes.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<section class="page-hero page-hero--service">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--service">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span class="breadcrumb-current"><?php the_title(); ?></span>
		</div>
		<h1><?php the_title(); ?></h1>
	</div>
</section>

<section class="page-section page-section--alt">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-7 col-md-12 order-2 order-md-1">
				<div class="banner-content article-content">
					<?php the_content(); ?>
				</div>
				<?php if (get_field('banner_button_link_1') || get_field('banner_button_link_2')) : ?>
					<div class="call-to-action mt-4">
						<?php if (get_field('banner_button_link_1')) : ?>
							<a class="btn-shop mr-3" href="<?php echo esc_url(get_field('banner_button_link_1')['url'] ?: '#'); ?>" target="<?php echo esc_attr(get_field('banner_button_link_1')['target'] ?: '_self'); ?>">
								<?php echo esc_html(get_field('banner_button_link_1')['title']); ?>
							</a>
						<?php endif; ?>
						<?php if (get_field('banner_button_link_2')) : ?>
							<a class="btn-shop-outline" href="<?php echo esc_url(get_field('banner_button_link_2')['url'] ?: '#'); ?>" target="<?php echo esc_attr(get_field('banner_button_link_2')['target'] ?: '_self'); ?>">
								<?php echo esc_html(get_field('banner_button_link_2')['title']); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="col-lg-5 col-md-12 order-1 order-md-2">
				<div class="banner-image">
					<?php if (has_post_thumbnail()) : ?>
						<?php the_post_thumbnail('large', array('class' => 'img-fluid')); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_template_part('template-parts/taglines'); ?>

<?php
$content_groups = get_field('content_groups');
if ($content_groups) :
	for ($i = 1; $i <= count($content_groups); $i++) :
		$content_section = $content_groups['content_group_' . $i];
		if (empty($content_section['content_block'])) {
			continue;
		}
		?>
		<section class="page-section <?php echo $i % 2 === 0 ? 'page-section--alt' : ''; ?>">
			<div class="container">
				<div class="row align-items-center">
					<?php if (!empty($content_section['section_title']) || !empty($content_section['section_heading'])) : ?>
						<div class="col-lg-12 col-md-12 section-header-wrapper text-center mb-4">
							<div class="content-block">
								<?php if (!empty($content_section['section_title'])) : ?>
									<h5><?php echo esc_html($content_section['section_title']); ?></h5>
								<?php endif; ?>
								<?php if (!empty($content_section['section_heading'])) : ?>
									<h2 class="section-title"><?php echo esc_html($content_section['section_heading']); ?></h2>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="col-lg-6 col-md-12 content-column <?php echo $i % 2 === 0 ? '' : 'order-md-2 order-1'; ?>">
						<div class="article-content">
							<?php echo wp_kses_post($content_section['content_block']); ?>
						</div>

						<?php if (!empty($content_section['call_to_action_1']) || !empty($content_section['call_to_action_2'])) : ?>
							<div class="call-to-action mt-5">
								<?php if (!empty($content_section['call_to_action_1'])) : ?>
									<a class="btn-shop mr-3" href="<?php echo esc_url($content_section['call_to_action_1']['url'] ?: '#'); ?>" target="<?php echo esc_attr($content_section['call_to_action_1']['target'] ?: '_self'); ?>">
										<?php echo esc_html($content_section['call_to_action_1']['title']); ?>
									</a>
								<?php endif; ?>
								<?php if (!empty($content_section['call_to_action_2'])) : ?>
									<a class="btn-shop-outline" href="<?php echo esc_url($content_section['call_to_action_2']['url'] ?: '#'); ?>" target="<?php echo esc_attr($content_section['call_to_action_2']['target'] ?: '_self'); ?>">
										<?php echo esc_html($content_section['call_to_action_2']['title']); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="col-lg-6 col-md-12 featured-img-column <?php echo $i % 2 === 0 ? '' : 'order-md-1 order-2'; ?>">
						<?php if (!empty($content_section['featured_image']['sizes']['large'])) : ?>
							<div class="featured-image-wrapper">
								<img src="<?php echo esc_url($content_section['featured_image']['sizes']['large']); ?>" class="img-fluid" alt="">
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endfor; ?>
<?php endif; ?>

<?php get_template_part('template-parts/blogs'); ?>

<?php get_footer(); ?>
