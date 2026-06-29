<?php
/**
 * Order steps section (How it works).
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$front_page_id = (int) get_option('page_on_front');
$visible = function_exists('get_field') ? (bool) get_field('order_steps_section_visibility', $front_page_id) : false;
if (!$visible) {
	return;
}

$term = isset($args['term']) ? $args['term'] : null;
if ($term instanceof WP_Term && function_exists('trimvia_condition_field_visible')) {
	if (!trimvia_condition_field_visible('cond_order_steps_visibility', $term)) {
		return;
	}
}

$order_step_title = (string) get_field('order_step_title', $front_page_id);
$order_step = get_field('order_steps', $front_page_id);
if (!is_array($order_step) || empty($order_step)) {
	return;
}

$steps = array();
for ($i = 1; $i <= count($order_step); $i++) {
	$row = isset($order_step['step_' . $i]) ? $order_step['step_' . $i] : array();
	$content = isset($row['short_description']) ? (string) $row['short_description'] : '';
	if ('' === trim(wp_strip_all_tags($content))) {
		continue;
	}

	$title = sprintf(
		/* translators: %d: step number */
		__('Step %d', 'theme-woopm-child'),
		$i
	);
	$description = wp_strip_all_tags($content);

	if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $content, $heading_match)) {
		$title = wp_strip_all_tags($heading_match[1]);
		$description = trim(wp_strip_all_tags(preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $content, 1)));
	}

	if ('' === $description) {
		$description = wp_strip_all_tags($content);
	}

	$steps[] = array(
		'number'      => (string) $i,
		'title'       => $title,
		'description' => $description,
	);
}

if (empty($steps)) {
	return;
}
?>
<section class="how-sec section-pad trimvia-order-steps" id="how-it-works">
	<div class="container">
		<div class="trimvia-order-steps__head rv">
			<?php if ('' !== trim($order_step_title)) : ?>
				<div class="stag"><?php esc_html_e('How It Works', 'theme-woopm-child'); ?></div>
				<h2 class="stitle"><?php echo esc_html($order_step_title); ?></h2>
			<?php else : ?>
				<div class="stag"><?php esc_html_e('How It Works', 'theme-woopm-child'); ?></div>
				<h2 class="stitle"><?php esc_html_e('Three Simple Steps', 'theme-woopm-child'); ?></h2>
			<?php endif; ?>
		</div>

		<div class="steps trimvia-order-steps__cards">
			<?php foreach ($steps as $step_index => $step) : ?>
				<?php
				$delay_class = '';
				if (0 === $step_index % 3) {
					$delay_class = ' rv-d1';
				} elseif (1 === $step_index % 3) {
					$delay_class = ' rv-d2';
				} else {
					$delay_class = ' rv-d3';
				}
				?>
				<div class="scard rv<?php echo esc_attr($delay_class); ?>">
					<div class="snum"><?php echo esc_html($step['number']); ?></div>
					<h3><?php echo esc_html($step['title']); ?></h3>
					<p><?php echo esc_html($step['description']); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
