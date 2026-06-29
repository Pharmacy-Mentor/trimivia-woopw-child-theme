<?php
/**
 * Blog single: inline content images default to 1800×630.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

/** @var string Registered image size slug. */
const TRIMVIA_BLOG_CONTENT_IMAGE_SIZE = 'trimvia-blog-content';

/** @var int Target width in pixels. */
const TRIMVIA_BLOG_CONTENT_IMAGE_WIDTH = 1800;

/** @var int Target height in pixels. */
const TRIMVIA_BLOG_CONTENT_IMAGE_HEIGHT = 630;

/**
 * Register the blog inline image size (hard crop to 1800×630).
 */
function trimvia_register_blog_content_image_size()
{
	add_image_size(
		TRIMVIA_BLOG_CONTENT_IMAGE_SIZE,
		TRIMVIA_BLOG_CONTENT_IMAGE_WIDTH,
		TRIMVIA_BLOG_CONTENT_IMAGE_HEIGHT,
		true
	);
}
add_action('after_setup_theme', 'trimvia_register_blog_content_image_size');

/**
 * Prefer the blog content size when WordPress builds responsive srcsets for post content.
 *
 * @param string[] $sizes Image size names.
 * @return string[]
 */
function trimvia_blog_content_image_sizes($sizes)
{
	if (!is_singular('post')) {
		return $sizes;
	}

	return array(TRIMVIA_BLOG_CONTENT_IMAGE_SIZE);
}
add_filter('content_image_sizes', 'trimvia_blog_content_image_sizes');

/**
 * Replace inline content <img> tags on single posts with the 1800×630 variant.
 *
 * @param string $filtered_image Full img tag HTML.
 * @param string $context        Filter context (e.g. the_content).
 * @param int    $attachment_id  Attachment ID.
 * @return string
 */
function trimvia_blog_content_img_tag($filtered_image, $context, $attachment_id)
{
	if ('the_content' !== $context || !is_singular('post') || $attachment_id < 1) {
		return $filtered_image;
	}

	$replacement = wp_get_attachment_image(
		$attachment_id,
		TRIMVIA_BLOG_CONTENT_IMAGE_SIZE,
		false,
		array(
			'class'    => 'trimvia-blog-content-image',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	return $replacement ? $replacement : $filtered_image;
}
add_filter('wp_content_img_tag', 'trimvia_blog_content_img_tag', 10, 3);

/**
 * Ensure width/height attributes on content images match the 1800×630 ratio (prevents layout stretch).
 *
 * @param array        $attr       Attributes for the image markup.
 * @param WP_Post      $attachment Image attachment post.
 * @param string|int[] $size       Requested size.
 * @return array
 */
function trimvia_blog_content_attachment_image_attributes($attr, $attachment, $size)
{
	if (!is_singular('post') || TRIMVIA_BLOG_CONTENT_IMAGE_SIZE !== $size) {
		return $attr;
	}

	$attr['width']  = (string) TRIMVIA_BLOG_CONTENT_IMAGE_WIDTH;
	$attr['height'] = (string) TRIMVIA_BLOG_CONTENT_IMAGE_HEIGHT;

	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'trimvia_blog_content_attachment_image_attributes', 10, 3);
