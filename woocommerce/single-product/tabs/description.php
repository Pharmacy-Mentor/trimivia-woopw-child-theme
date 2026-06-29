<?php
/**
 * Product description tab — outputs the main product editor content.
 *
 * @package theme-woopm-child
 * @version 9.8.0
 */

defined('ABSPATH') || exit;

$product_id = function_exists('trimvia_single_product_get_current_product_id')
	? trimvia_single_product_get_current_product_id()
	: (int) get_the_ID();

if ($product_id < 1) {
	return;
}

$product_post = get_post($product_id);
if (!$product_post instanceof WP_Post) {
	return;
}

$description_raw = function_exists('trimvia_single_product_get_long_description_raw')
	? trimvia_single_product_get_long_description_raw($product_id)
	: (string) $product_post->post_content;

if ('' === trim(wp_strip_all_tags($description_raw))) {
	return;
}

global $post;
$previous_post = $post ?? null;
$post = $product_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
setup_postdata($product_post);

echo '<div class="woocommerce-product-details__description article-content">';
echo apply_filters('the_content', $description_raw); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '</div>';

wp_reset_postdata();
$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
