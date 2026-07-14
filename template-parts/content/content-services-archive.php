<?php
/**
 * Services archive / landing: hero, grid (CPT `service`), bottom CTA.
 * ACF from page ID or ACF options (`acf_id` => 'option').
 *
 * @package theme-woopm-child
 *
 * @param array $args {
 *     @type int|string $acf_id Post ID or 'option'.
 * }
 */

if (!defined('ABSPATH')) {
	exit;
}

$acf_id = isset($args['acf_id']) ? $args['acf_id'] : get_the_ID();
if (!is_numeric($acf_id) && 'option' !== $acf_id) {
	return;
}

$get = static function ($field) use ($acf_id) {
	if (!function_exists('get_field')) {
		return null;
	}
	return get_field($field, $acf_id);
};

$hero_visible = true;
$hero_vis     = $get('svc_archive_hero_visibility');
if (null !== $hero_vis && '' !== $hero_vis) {
	$hero_visible = (bool) $hero_vis;
}

$breadcrumb_label = trim((string) $get('svc_archive_breadcrumb_label'));
if ('' === $breadcrumb_label) {
	$breadcrumb_label = __('Services', 'theme-woopm-child');
}

$hero_title = trim((string) $get('svc_archive_hero_title'));
if ('' === $hero_title) {
	$hero_title = __('Our Services', 'theme-woopm-child');
}

$hero_intro = trim((string) $get('svc_archive_hero_intro'));
if ('' === $hero_intro) {
	$hero_intro = __(
		'Explore clinic services from vaccinations and travel health to prescribing support and weight management — delivered with the same pharmacy-led standards as our treatments.',
		'theme-woopm-child'
	);
}

$grid_visible = true;
$grid_vis     = $get('svc_archive_grid_visibility');
if (null !== $grid_vis && '' !== $grid_vis) {
	$grid_visible = (bool) $grid_vis;
}

$show_cta = true;
$cta_vis  = $get('svc_archive_cta_visibility');
if (null !== $cta_vis && '' !== $cta_vis) {
	$show_cta = (bool) $cta_vis;
}

$cta_title = trim((string) $get('svc_archive_cta_title'));
if ('' === $cta_title) {
	$cta_title = __('Need help choosing a service?', 'theme-woopm-child');
}

$cta_text = trim((string) $get('svc_archive_cta_text'));
if ('' === $cta_text) {
	$cta_text = __('Start a consultation or contact our team — we will point you to the right clinical pathway.', 'theme-woopm-child');
}

$cta_btn_label = trim((string) $get('svc_archive_cta_button_label'));
if ('' === $cta_btn_label) {
	$cta_btn_label = __('Start Consultation', 'theme-woopm-child');
}
if (stripos($cta_btn_label, 'consultation') !== false) {
	$cta_btn_label = __('Start Consultation', 'theme-woopm-child');
}

$cta_btn_url = trim((string) $get('svc_archive_cta_button_url'));
if ('' === $cta_btn_url) {
	$cta_btn_url = home_url('/shop/');
}
if (stripos($cta_btn_url, 'consultation') !== false) {
	$cta_btn_url = home_url('/shop/');
}

$placeholder = function_exists('get_placeholder_image') ? get_placeholder_image() : '';

$service_query = new WP_Query(
	array(
		'post_type'           => 'service',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$chevron_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';
?>
<?php if ($hero_visible) : ?>
<section class="page-hero page-hero--services trimvia-services-archive-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<nav class="breadcrumb breadcrumb--services" aria-label="<?php esc_attr_e('Breadcrumb', 'theme-woopm-child'); ?>">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span aria-hidden="true">›</span>
			<span class="breadcrumb-current"><?php echo esc_html($breadcrumb_label); ?></span>
		</nav>
		<h1><?php echo esc_html($hero_title); ?></h1>
		<p><?php echo esc_html($hero_intro); ?></p>
	</div>
</section>
<?php endif; ?>

<?php if ($grid_visible) : ?>
<section class="page-section page-section--alt trimvia-services-archive-grid-section">
	<div class="container">
		<?php if ($service_query->have_posts()) : ?>
			<div class="services-grid">
				<?php
				$i = 0;
				while ($service_query->have_posts()) :
					$service_query->the_post();
					$delays = array( '', ' rv-d1', ' rv-d2', ' rv-d3' );
					$delay  = $delays[ $i % 4 ];
					$i++;
					$thumb_url = '';
					if (has_post_thumbnail()) {
						$thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
					} elseif ($placeholder) {
						$thumb_url = $placeholder;
					}
					$raw_excerpt = get_the_excerpt();
					if ('' === trim($raw_excerpt)) {
						$raw_excerpt = wp_strip_all_tags(get_the_content());
					}
					$excerpt = wp_trim_words($raw_excerpt, 36, '…');
					?>
				<article class="service-card rv<?php echo esc_attr($delay); ?>">
					<div class="service-card__media">
						<?php if ($thumb_url) : ?>
							<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="640" height="512" loading="lazy" />
						<?php endif; ?>
					</div>
					<div class="service-card__body">
						<h2 class="service-card__title"><?php the_title(); ?></h2>
						<?php if ('' !== $excerpt) : ?>
							<p class="service-card__excerpt"><?php echo esc_html($excerpt); ?></p>
						<?php endif; ?>
						<a class="service-card__cta" href="<?php the_permalink(); ?>">
							<?php esc_html_e('Read More', 'theme-woopm-child'); ?>
							<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="trimvia-services-archive-empty"><?php esc_html_e('No services are published yet.', 'theme-woopm-child'); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ($show_cta) : ?>
<section class="cta-sec trimvia-services-archive-cta" style="position:relative;overflow:hidden;">
	<div class="orb orb-1" style="top:-30%;right:-20%;opacity:.4"></div>
	<div class="orb orb-2" style="bottom:-30%;left:-15%;opacity:.25"></div>
	<div style="max-width:700px;margin:0 auto;padding:80px 40px;text-align:center;position:relative;z-index:1;">
		<h2 class="stitle" style="color:#fff;font-size:clamp(30px,3.5vw,44px);"><?php echo esc_html($cta_title); ?></h2>
		<p class="sdesc" style="color:rgba(255,255,255,0.7);max-width:440px;margin:0 auto 36px;"><?php echo esc_html($cta_text); ?></p>
		<a href="<?php echo esc_url($cta_btn_url); ?>" class="btn-cta">
			<?php echo esc_html($cta_btn_label); ?>
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</a>
	</div>
</section>
<?php endif; ?>
