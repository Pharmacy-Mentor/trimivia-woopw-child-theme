<?php
/**
 * Header navigation walker: mega-style dropdowns using product ACF "Menu Display" fields
 * (show_menu_item, menu_description, menu_icon_image, menu_icon) — same data as parent WooPW_Menu_Walker.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('trimvia_nav_default_mega_icon_svg')) {
	/**
	 * @return string
	 */
	function trimvia_nav_default_mega_icon_svg()
	{
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
	}
}

if (!function_exists('trimvia_nav_product_mega_icon_inner')) {
	/**
	 * Icon cell inner HTML for a product (Menu Display ACF on product).
	 *
	 * @param int $product_id Product post ID.
	 * @return string
	 */
	function trimvia_nav_product_mega_icon_inner($product_id)
	{
		$product_id = (int) $product_id;
		if ($product_id <= 0 || !function_exists('get_field')) {
			return trimvia_nav_default_mega_icon_svg();
		}

		$img_id = get_field('menu_icon_image', $product_id);
		if (!empty($img_id)) {
			$img_id = is_array($img_id) && isset($img_id['ID']) ? (int) $img_id['ID'] : (int) $img_id;
			if ($img_id > 0) {
				return wp_get_attachment_image(
					$img_id,
					'thumbnail',
					false,
					array(
						'class' => 'trimvia-mega-icon-img',
						'alt'   => '',
					)
				);
			}
		}

		$icon = get_field('menu_icon', $product_id);
		if (is_string($icon) && '' !== trim($icon)) {
			return wp_kses(
				$icon,
				array(
					'i'       => array('class' => true, 'aria-hidden' => true, 'style' => true),
					'svg'     => array(
						'class'        => true,
						'xmlns'        => true,
						'viewbox'      => true,
						'viewBox'      => true,
						'fill'         => true,
						'stroke'       => true,
						'stroke-width' => true,
						'width'        => true,
						'height'       => true,
						'aria-hidden'  => true,
					),
					'path'    => array(
						'd'               => true,
						'fill'            => true,
						'stroke'          => true,
						'stroke-width'    => true,
						'stroke-linecap'  => true,
						'stroke-linejoin' => true,
					),
					'circle'  => array('cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true),
					'line'    => array('x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true),
					'rect'    => array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true),
					'polyline' => array('points' => true, 'fill' => true, 'stroke' => true),
					'polygon' => array('points' => true, 'fill' => true, 'stroke' => true),
				)
			);
		}

		return trimvia_nav_default_mega_icon_svg();
	}
}

if (!class_exists('Trimvia_Nav_Walker')) {

	/**
	 * @extends Walker_Nav_Menu
	 */
	class Trimvia_Nav_Walker extends Walker_Nav_Menu
	{

		/**
		 * @param string   $output Output.
		 * @param int      $depth  Depth.
		 * @param stdClass $args   Menu args.
		 */
		public function start_lvl(&$output, $depth = 0, $args = null)
		{
			if (0 === (int) $depth) {
				$output .= '<div class="mega-menu"><ul class="mega-menu-grid" role="list">';
				return;
			}
			parent::start_lvl($output, $depth, $args);
		}

		/**
		 * @param string   $output Output.
		 * @param int      $depth  Depth.
		 * @param stdClass $args   Menu args.
		 */
		public function end_lvl(&$output, $depth = 0, $args = null)
		{
			if (0 === (int) $depth) {
				$output .= '</ul></div>';
				return;
			}
			parent::end_lvl($output, $depth, $args);
		}

		/**
		 * @param string   $output Output.
		 * @param WP_Post  $item   Menu item.
		 * @param int      $depth  Depth.
		 * @param stdClass $args   Menu args.
		 * @param int      $id     Item ID.
		 */
		public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
		{
			if (1 === (int) $depth) {
				$indent = $depth ? str_repeat("\t", $depth) : '';

				$classes = empty($item->classes) ? array() : (array) $item->classes;
				if (!in_array('menu-item', $classes, true)) {
					$classes[] = 'menu-item';
				}

				$class_string = implode(' ', array_map('sanitize_html_class', array_filter(array_map('trim', $classes))));
				$class_attr   = $class_string ? ' class="' . esc_attr($class_string) . '"' : '';

				$item_id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
				$id_attr = $item_id ? ' id="' . esc_attr($item_id) . '"' : '';

				$atts           = array();
				$atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
				$atts['target'] = !empty($item->target) ? $item->target : '';
				$atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
				$atts['href']   = !empty($item->url) ? $item->url : '';

				$atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

				$attributes = '';
				foreach ($atts as $attr => $value) {
					if (is_scalar($value) && '' !== $value && false !== $value) {
						$value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
						$attributes .= ' ' . $attr . '="' . $value . '"';
					}
				}

				$title = apply_filters('the_title', $item->title, $item->object_id);

				$icon_inner = trimvia_nav_default_mega_icon_svg();
				$desc_html  = '';

				if ('product' === $item->object && !empty($item->object_id)) {
					$pid        = (int) $item->object_id;
					$icon_inner = trimvia_nav_product_mega_icon_inner($pid);
					if (function_exists('get_field')) {
						$desc = get_field('menu_description', $pid);
						if (is_string($desc) && '' !== trim($desc)) {
							$desc_html = '<p>' . esc_html($desc) . '</p>';
						}
					}
				}

				$output .= $indent . '<li' . $id_attr . $class_attr . '>';
				$output .= '<a class="mega-link"' . $attributes . '>';
				$output .= '<div class="mega-link-icon">' . $icon_inner . '</div>';
				$output .= '<div class="mega-link-text"><h4>' . esc_html($title) . '</h4>' . $desc_html . '</div>';
				$output .= '</a>';

				return;
			}

			parent::start_el($output, $item, $depth, $args, $id);
		}
	}
}
