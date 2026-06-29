<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id    = get_the_ID();
$hero_title = get_the_title($page_id);
$is_track_order_page = function_exists('trimvia_is_order_tracking_page') && trimvia_is_order_tracking_page();
$is_view_order_page  = function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received'));
$needs_order_wrapper = $is_track_order_page || $is_view_order_page;

$section_classes     = 'page-section page-section--default';
if ($is_track_order_page) {
	$section_classes .= ' trimvia-track-order-section';
}
?>
<section class="page-hero page-hero--default">
	<div class="hero-noise"></div>
	<div class="container">
		<nav class="breadcrumb breadcrumb--default" aria-label="<?php esc_attr_e('Breadcrumb', 'theme-woopm-child'); ?>">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php echo esc_html($hero_title); ?></span>
		</nav>
		<h1><?php echo esc_html($hero_title); ?></h1>
	</div>
</section>

<?php if (have_posts()) : ?>
	<?php
	while (have_posts()) :
		the_post();
		?>
<section class="<?php echo esc_attr($section_classes); ?>">
	<div class="container trimvia-page-body">
		<?php if ( $needs_order_wrapper ) : ?>
			<div class="trimvia-view-order">
				<div class="trimvia-view-order-details">
					<?php the_content(); ?>
				</div>
			</div>
		<?php else : ?>
			<?php the_content(); ?>
		<?php endif; ?>
	</div>
</section>
		<?php
	endwhile;
	?>
<?php endif; ?>
