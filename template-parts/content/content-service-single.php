<?php
if (!defined('ABSPATH')) {
	exit;
}

$post_id = get_the_ID();

$trimvia_service_icon_svgs = array(
	'clock'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
	'truck'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
	'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	'user'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
	'pulse'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
	'grid'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
);

$show_hero               = true;
$show_features           = true;
$show_main_column        = true;
$show_featured_image     = true;
$show_sidebar_cta        = true;
$show_sidebar_highlights = true;
$show_sidebar_help       = true;

$hero_title          = get_the_title($post_id);
$hero_description    = '';
$features_items      = array();
$cta_title           = __('Ready to Get Started?', 'theme-woopm-child');
$cta_text            = __('Complete a free online consultation and a prescriber will review your suitability within hours.', 'theme-woopm-child');
$cta_button_label    = __('Start Consultation', 'theme-woopm-child');
$cta_button_url      = '';
$highlights_title    = __('Treatments We Offer', 'theme-woopm-child');
$highlights_items    = array();
$help_title          = __('Need Help?', 'theme-woopm-child');
$help_text           = __('Email [pharmacy_email] or call us during opening hours.', 'theme-woopm-child');
$breadcrumb_current_label = get_the_title($post_id);

$default_features = array(
	array(
		'feature_text' => __('2-min consultation', 'theme-woopm-child'),
		'feature_icon_config' => array('type' => 'builtin', 'builtin' => 'clock', 'fa' => '', 'upload' => null),
	),
	array(
		'feature_text' => __('Next-day delivery', 'theme-woopm-child'),
		'feature_icon_config' => array('type' => 'builtin', 'builtin' => 'truck', 'fa' => '', 'upload' => null),
	),
	array(
		'feature_text' => __('GPhC regulated', 'theme-woopm-child'),
		'feature_icon_config' => array('type' => 'builtin', 'builtin' => 'shield', 'fa' => '', 'upload' => null),
	),
	array(
		'feature_text' => __('Prescriber support', 'theme-woopm-child'),
		'feature_icon_config' => array('type' => 'builtin', 'builtin' => 'user', 'fa' => '', 'upload' => null),
	),
);

if (function_exists('get_field') && $post_id) {
	$hero_vis = get_field('service_hero_visibility', $post_id);
	if (null !== $hero_vis && '' !== $hero_vis) {
		$show_hero = (bool) $hero_vis;
	}

	$hero_title_field = trim((string) get_field('service_hero_title', $post_id));
	if ('' !== $hero_title_field) {
		$hero_title = $hero_title_field;
	}

	$hero_description_field = trim((string) get_field('service_hero_description', $post_id));
	if ('' !== $hero_description_field) {
		$hero_description = $hero_description_field;
	}

	$breadcrumb_label_field = trim((string) get_field('service_breadcrumb_label', $post_id));
	if ('' !== $breadcrumb_label_field) {
		$breadcrumb_current_label = $breadcrumb_label_field;
	}

	$feat_vis = get_field('service_features_visibility', $post_id);
	if (null !== $feat_vis && '' !== $feat_vis) {
		$show_features = (bool) $feat_vis;
	}

	$features_rows = get_field('service_features_items', $post_id);
	if (is_array($features_rows) && !empty($features_rows)) {
		$clean = array();
		foreach ($features_rows as $row) {
			$text = isset($row['feature_text']) ? trim((string) $row['feature_text']) : '';
			if ('' === $text) {
				continue;
			}
			$icon_config = function_exists('trimvia_parse_service_icon_config')
				? trimvia_parse_service_icon_config($row, 'feature')
				: array(
					'type'    => 'builtin',
					'builtin' => isset($row['feature_icon']) ? (string) $row['feature_icon'] : 'clock',
					'fa'      => '',
					'upload'  => null,
				);
			$clean[] = array(
				'feature_text'        => $text,
				'feature_icon_config' => $icon_config,
			);
		}
		if (!empty($clean)) {
			$features_items = $clean;
		}
	}

	$main_vis = get_field('service_main_visibility', $post_id);
	if (null !== $main_vis && '' !== $main_vis) {
		$show_main_column = (bool) $main_vis;
	}

	$feat_img_vis = get_field('service_show_featured_image', $post_id);
	if (null !== $feat_img_vis && '' !== $feat_img_vis) {
		$show_featured_image = (bool) $feat_img_vis;
	}

	$cta_vis = get_field('service_sidebar_cta_visibility', $post_id);
	if (null !== $cta_vis && '' !== $cta_vis) {
		$show_sidebar_cta = (bool) $cta_vis;
	}

	$h_vis = get_field('service_sidebar_highlights_visibility', $post_id);
	if (null !== $h_vis && '' !== $h_vis) {
		$show_sidebar_highlights = (bool) $h_vis;
	}

	$help_vis = get_field('service_sidebar_help_visibility', $post_id);
	if (null !== $help_vis && '' !== $help_vis) {
		$show_sidebar_help = (bool) $help_vis;
	}

	$cta_title_field = trim((string) get_field('service_sidebar_cta_title', $post_id));
	if ('' !== $cta_title_field) {
		$cta_title = $cta_title_field;
	}

	$cta_text_field = trim((string) get_field('service_sidebar_cta_text', $post_id));
	if ('' !== $cta_text_field) {
		$cta_text = $cta_text_field;
	}

	$cta_btn_l = trim((string) get_field('service_sidebar_cta_button_label', $post_id));
	if ('' !== $cta_btn_l) {
		$cta_button_label = $cta_btn_l;
	}

	$cta_btn_u = trim((string) get_field('service_sidebar_cta_button_url', $post_id));
	if ('' !== $cta_btn_u) {
		$cta_button_url = $cta_btn_u;
	}

	$hil_title = trim((string) get_field('service_sidebar_highlights_title', $post_id));
	if ('' !== $hil_title) {
		$highlights_title = $hil_title;
	}

	$hil_rows = get_field('service_sidebar_highlights_items', $post_id);
	if (is_array($hil_rows) && !empty($hil_rows)) {
		$hclean = array();
		foreach ($hil_rows as $row) {
			if (!is_array($row)) {
				continue;
			}
			$item = function_exists('trimvia_build_service_highlight_item_from_row')
				? trimvia_build_service_highlight_item_from_row($row)
				: null;
			if (null !== $item) {
				$hclean[] = $item;
			}
		}
		if (!empty($hclean)) {
			$highlights_items = $hclean;
		}
	}

	// Treatment products auto-fill the highlights card only when manual items are empty.
	if (empty($highlights_items)) {
		$raw_treatment_products = get_field('service_treatment_products', $post_id);
		$product_ids            = array();
		if (is_array($raw_treatment_products)) {
			foreach ($raw_treatment_products as $item) {
				$rid = function_exists('trimvia_resolve_acf_post_id')
					? trimvia_resolve_acf_post_id($item)
					: 0;
				if ($rid > 0) {
					$product_ids[] = $rid;
				}
			}
		}
		$product_ids = array_values(array_unique(array_filter($product_ids)));

		if (!empty($product_ids) && function_exists('trimvia_build_service_highlights_from_product_ids')) {
			$from_products = trimvia_build_service_highlights_from_product_ids($product_ids);
			if (!empty($from_products)) {
				$highlights_items = $from_products;
			}
		}
	}

	$help_title_field = trim((string) get_field('service_sidebar_help_title', $post_id));
	if ('' !== $help_title_field) {
		$help_title = $help_title_field;
	}

	$help_text_field = trim((string) get_field('service_sidebar_help_text', $post_id));
	if ('' !== $help_text_field) {
		$help_text = $help_text_field;
	}
}

if ($show_features && empty($features_items)) {
	$features_items = $default_features;
}

$services_archive_url = get_post_type_archive_link('service');
if (!is_string($services_archive_url) || '' === $services_archive_url) {
	$services_archive_url = home_url('/services/');
}

$service_category_term = null;
$service_terms         = get_the_terms($post_id, 'service-category');
if (!is_wp_error($service_terms) && !empty($service_terms)) {
	$service_category_term = $service_terms[0];
	if (class_exists('WPSEO_Primary_Term')) {
		$primary_term    = new WPSEO_Primary_Term('service-category', $post_id);
		$primary_term_id = $primary_term->get_primary_term();
		if (!is_wp_error($primary_term_id) && $primary_term_id) {
			$primary_term_obj = get_term((int) $primary_term_id, 'service-category');
			if (!is_wp_error($primary_term_obj) && !empty($primary_term_obj)) {
				$service_category_term = $primary_term_obj;
			}
		}
	} else {
		$primary_term_id = get_post_meta($post_id, '_yoast_wpseo_primary_service-category', true);
		if (!empty($primary_term_id)) {
			$primary_term_obj = get_term((int) $primary_term_id, 'service-category');
			if (!is_wp_error($primary_term_obj) && !empty($primary_term_obj)) {
				$service_category_term = $primary_term_obj;
			}
		}
	}
}

$cta_resolved_url = function_exists('trimvia_get_service_sidebar_cta_url')
	? trimvia_get_service_sidebar_cta_url($post_id, $cta_button_url)
	: $cta_button_url;

$legacy_icon        = function_exists('get_field') ? get_field('icon', $post_id) : '';
$raw_content        = get_post_field('post_content', $post_id);
$has_editor_content = is_string($raw_content) && '' !== trim(wp_strip_all_tags($raw_content));

$main_has_body = $show_main_column
	&& (
		($show_featured_image && has_post_thumbnail($post_id))
		|| $has_editor_content
		|| !empty($legacy_icon)
	);

$sidebar_has_highlights = $show_sidebar_highlights && !empty($highlights_items);
$help_text_rendered     = function_exists('trimvia_render_text_with_shortcodes')
	? trimvia_render_text_with_shortcodes($help_text)
	: do_shortcode($help_text);
$help_text_stripped     = trim(wp_strip_all_tags($help_text_rendered));
$contact_box_legacy     = function_exists('get_field') ? get_field('contact_box_override', $post_id) : '';
$sidebar_has_help       = $show_sidebar_help && ('' !== $help_text_stripped || !empty($contact_box_legacy));

$has_sidebar = $show_sidebar_cta
	|| $sidebar_has_highlights
	|| $sidebar_has_help;

$grid_modifier_class = ($has_sidebar && !$main_has_body) ? ' content-grid--sidebar-only' : '';

?>
<?php if ($show_hero) : ?>
<section class="page-hero page-hero--service">
	<div class="hero-noise"></div>
	<div class="container">
		<nav class="breadcrumb breadcrumb--service" aria-label="<?php esc_attr_e('Breadcrumb', 'theme-woopm-child'); ?>">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<a href="<?php echo esc_url($services_archive_url); ?>"><?php esc_html_e('Services', 'theme-woopm-child'); ?></a>
			<?php if ($service_category_term && !is_wp_error($service_category_term)) : ?>
				<?php
				$cat_link = get_term_link($service_category_term);
				if (!is_wp_error($cat_link)) :
					?>
				<span>&rsaquo;</span>
				<a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($service_category_term->name); ?></a>
				<?php endif; ?>
			<?php endif; ?>
			<span>&rsaquo;</span>
			<span class="breadcrumb-current"><?php echo esc_html($breadcrumb_current_label); ?></span>
		</nav>
		<h1><?php echo esc_html($hero_title); ?></h1>
		<?php if ('' !== trim(wp_strip_all_tags($hero_description))) : ?>
			<p><?php echo wp_kses_post($hero_description); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if (($show_features && !empty($features_items)) || $has_sidebar || $main_has_body) : ?>
<section class="page-section service-singular-section" style="padding-top:40px;">
	<div class="container">
		<?php if ($show_features && !empty($features_items)) : ?>
		<div class="quick-info rv">
			<?php foreach ($features_items as $feature_row) : ?>
			<div class="quick-info-item">
				<?php
				$icon_config = isset($feature_row['feature_icon_config']) && is_array($feature_row['feature_icon_config'])
					? $feature_row['feature_icon_config']
					: array('type' => 'builtin', 'builtin' => 'clock', 'fa' => '', 'upload' => null);
				$icon_html   = function_exists('trimvia_render_service_icon_html')
					? trimvia_render_service_icon_html($icon_config, $trimvia_service_icon_svgs)
					: ($trimvia_service_icon_svgs['clock'] ?? '');
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in helper or hardcoded SVG map.
				echo $icon_html;
				?>
				<span><?php echo esc_html($feature_row['feature_text']); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ($main_has_body || $has_sidebar) : ?>
		<div class="content-grid<?php echo esc_attr($grid_modifier_class); ?>">
			<?php if ($main_has_body) : ?>
			<div class="article-content rv">
				<?php if ($legacy_icon) : ?>
				<div class="service-legacy-icon"><?php echo $legacy_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<?php if ($show_featured_image && has_post_thumbnail($post_id)) : ?>
				<div class="service-featured-image" style="margin-bottom:32px;border-radius:var(--rl);overflow:hidden;">
					<?php echo get_the_post_thumbnail($post_id, 'full', array('style' => 'width:100%;height:auto;display:block;')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php endif; ?>
				<?php if ($has_editor_content) : ?>
					<?php the_content(); ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ($has_sidebar) : ?>
			<div class="sidebar">
				<?php if ($show_sidebar_cta) : ?>
				<div class="sidebar-card--blue sidebar-card">
					<h4><?php echo esc_html($cta_title); ?></h4>
					<p><?php echo wp_kses_post($cta_text); ?></p>
					<?php if ('' !== $cta_button_label && '' !== $cta_resolved_url) : ?>
					<a href="<?php echo esc_url($cta_resolved_url); ?>" class="btn-cta btn-cta--sidebar"><?php echo esc_html($cta_button_label); ?> &rarr;</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ($sidebar_has_highlights) : ?>
				<div class="sidebar-card">
					<h4><?php echo esc_html($highlights_title); ?></h4>
					<?php foreach ($highlights_items as $hi) : ?>
						<?php
						$h_url = isset($hi['highlight_url']) ? trim((string) $hi['highlight_url']) : '';
						?>
					<div class="contact-item">
						<div class="contact-icon">
							<?php
							if (!empty($hi['highlight_icon_html'])) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in product icon helper.
								echo $hi['highlight_icon_html'];
							} else {
								$icon_config = isset($hi['highlight_icon_config']) && is_array($hi['highlight_icon_config'])
									? $hi['highlight_icon_config']
									: array(
										'type'    => 'builtin',
										'builtin' => isset($hi['highlight_icon']) ? (string) $hi['highlight_icon'] : 'shield',
										'fa'      => '',
										'upload'  => null,
									);
								$icon_html   = function_exists('trimvia_render_service_icon_html')
									? trimvia_render_service_icon_html($icon_config, $trimvia_service_icon_svgs)
									: ($trimvia_service_icon_svgs['shield'] ?? '');
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in helper or hardcoded SVG map.
								echo $icon_html;
							}
							?>
						</div>
						<div>
							<?php if ('' !== $h_url) : ?>
							<h5><a href="<?php echo esc_url($h_url); ?>"><?php echo esc_html($hi['highlight_title']); ?></a></h5>
							<?php else : ?>
							<h5><?php echo esc_html($hi['highlight_title']); ?></h5>
							<?php endif; ?>
							<?php if ('' !== $hi['highlight_subtitle']) : ?>
							<p><?php echo esc_html($hi['highlight_subtitle']); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ($sidebar_has_help) : ?>
				<div class="sidebar-card">
					<h4><?php echo esc_html($help_title); ?></h4>
					<?php if ('' !== $help_text_stripped) : ?>
					<div class="service-help-text"><?php echo wp_kses_post(wpautop($help_text_rendered)); ?></div>
					<?php endif; ?>
					<?php if (!empty($contact_box_legacy)) : ?>
					<div class="service-help-legacy"><?php echo $contact_box_legacy; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>
