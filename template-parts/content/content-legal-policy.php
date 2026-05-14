<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id      = get_the_ID();
$hero_title   = get_the_title($page_id);
$hero_description = '';

if (function_exists('get_field') && $page_id) {
	$header_title_override = trim((string) get_field('header_title', $page_id));
	if ('' !== $header_title_override) {
		$hero_title = $header_title_override;
	}

	$hero_description_value = trim((string) get_field('header_description', $page_id));
	if ('' !== $hero_description_value) {
		$hero_description = $hero_description_value;
	}
}

$grad_start = '';
$grad_end   = '';

if (function_exists('get_field') && $page_id) {
	$maybe_start = get_field('header_background_gradient_start', $page_id);
	$maybe_end   = get_field('header_background_gradient_end', $page_id);
	if (function_exists('sanitize_hex_color')) {
		if (is_string($maybe_start)) {
			$grad_start = (string) sanitize_hex_color($maybe_start);
		}
		if (is_string($maybe_end)) {
			$grad_end = (string) sanitize_hex_color($maybe_end);
		}
	}
}

$hero_section_classes = 'page-hero page-hero--legal';
$hero_inline_style    = '';

if ('' !== $grad_start || '' !== $grad_end) {
	$s = '' !== $grad_start ? $grad_start : '#060e24';
	$e = '' !== $grad_end ? $grad_end : '#1a56e8';
	$hero_section_classes .= ' page-hero--legal-custom';
	/* CSS vars used in style.css; values are sanitized hex from ACF/WP helpers. */
	$hero_inline_style = '--legal-hero-start:' . esc_attr($s) . ';--legal-hero-end:' . esc_attr($e) . ';';
}
?>
<section class="<?php echo esc_attr($hero_section_classes); ?>"<?php echo $hero_inline_style ? ' style="' . esc_attr($hero_inline_style) . '"' : ''; ?>>
  <div class="hero-noise"></div>
  <div class="container">
    <nav class="breadcrumb breadcrumb--legal" aria-label="<?php esc_attr_e('Breadcrumb', 'theme-woopm-child'); ?>">
      <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Home', 'theme-woopm-child'); ?></a>
      <span>&rsaquo;</span>
      <span><?php echo esc_html($hero_title); ?></span>
    </nav>
    <h1><?php echo esc_html($hero_title); ?></h1>
	<?php if ('' !== $hero_description) : ?>
    <p><?php echo wp_kses_post($hero_description); ?></p>
	<?php endif; ?>
  </div>
</section>

<?php if (have_posts()) : ?>
	<?php
	while (have_posts()) :
		the_post();
		?>
<section class="page-section page-section--legal">
  <div class="container trimvia-legal-body">
		<?php the_content(); ?>
  </div>
</section>
		<?php
	endwhile;
	?>
<?php endif; ?>
