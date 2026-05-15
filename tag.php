<?php
/**
 * Tag archive - child theme override.
 *
 * Reuse the child archive layout so blog tags
 * match the new blog design.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}

$child_archive_template = trailingslashit(get_stylesheet_directory()) . 'archive.php';
if (file_exists($child_archive_template)) {
	include $child_archive_template;
	return;
}

$parent_tag_template = trailingslashit(get_template_directory()) . 'tag.php';
if (file_exists($parent_tag_template)) {
	include $parent_tag_template;
}
