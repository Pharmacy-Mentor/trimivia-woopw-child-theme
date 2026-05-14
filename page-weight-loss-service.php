<?php
if (!defined('ABSPATH')) {
	exit;
}

$trimvia_weight_loss_target = function_exists('trimvia_get_weight_loss_service_permalink')
	? trimvia_get_weight_loss_service_permalink()
	: '';

if (is_string($trimvia_weight_loss_target) && '' !== $trimvia_weight_loss_target) {
	wp_safe_redirect($trimvia_weight_loss_target, 301);
	exit;
}

get_header();
get_template_part('template-parts/content/content', 'weight-loss-service');
get_footer();
