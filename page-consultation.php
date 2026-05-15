<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ensure the consultation slug uses the child consultation template.
 *
 * Without this hand-off, WordPress can load this page-{slug} file first and
 * bypass the styled consultation flow in page-templates/consultation.php.
 */
require get_stylesheet_directory() . '/page-templates/consultation.php';
