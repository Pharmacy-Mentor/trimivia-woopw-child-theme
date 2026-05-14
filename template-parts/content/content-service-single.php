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
$help_text           = __('Email info@trimvia.co.uk or call us during opening hours.', 'theme-woopm-child');
$breadcrumb_current_label = get_the_title($post_id);

$default_features = array(
	array('feature_icon' => 'clock', 'feature_text' => __('2-min consultation', 'theme-woopm-child')),
	array('feature_icon' => 'truck', 'feature_text' => __('Next-day delivery', 'theme-woopm-child')),
	array('feature_icon' => 'shield', 'feature_text' => __('GPhC regulated', 'theme-woopm-child')),
	array('feature_icon' => 'user', 'feature_text' => __('Prescriber support', 'theme-woopm-child')),
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
			$icon = isset($row['feature_icon']) ? (string) $row['feature_icon'] : 'clock';
			$text = isset($row['feature_text']) ? trim((string) $row['feature_text']) : '';
			if ('' === $text) {
				continue;
			}
			$clean[] = array(
				'feature_icon' => $icon,
				'feature_text' => $text,
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
			$t  = isset($row['highlight_title']) ? trim((string) $row['highlight_title']) : '';
			$st = isset($row['highlight_subtitle']) ? trim((string) $row['highlight_subtitle']) : '';
			$u  = isset($row['highlight_url']) ? trim((string) $row['highlight_url']) : '';
			$ic = isset($row['highlight_icon']) ? (string) $row['highlight_icon'] : 'shield';
			if ('' === $t && '' === $st) {
				continue;
			}
			$hclean[] = array(
				'highlight_title'    => $t,
				'highlight_subtitle' => $st,
				'highlight_url'      => $u,
				'highlight_icon'     => $ic,
			);
		}
		if (!empty($hclean)) {
			$highlights_items = $hclean;
		}
	}

	$raw_treatment_products = get_field('service_treatment_products', $post_id);
	$product_ids            = array();
	if (is_array($raw_treatment_products)) {
		foreach ($raw_treatment_products as $item) {
			$rid = 0;
			if (is_numeric($item)) {
				$rid = (int) $item;
			} elseif (is_object($item) && isset($item->ID)) {
				$rid = (int) $item->ID;
			} elseif (is_array($item) && isset($item['ID'])) {
				$rid = (int) $item['ID'];
			}
			if ($rid > 0) {
				$product_ids[] = $rid;
			}
		}
	}
	$product_ids = array_values(array_unique(array_filter($product_ids)));

	if (!empty($product_ids) && function_exists('wc_get_product')) {
		$from_products = array();
		$icons_cycle   = array('shield', 'pulse', 'grid', 'clock', 'truck', 'user');
		$icon_i        = 0;
		foreach ($product_ids as $prod_id) {
			if ('product' !== get_post_type($prod_id) || 'publish' !== get_post_status($prod_id)) {
				continue;
			}
			$product = wc_get_product($prod_id);
			if (!$product) {
				continue;
			}
			$price_html = $product->get_price_html();
			$subtitle   = $price_html ? wp_strip_all_tags(html_entity_decode($price_html, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
			$from_products[] = array(
				'highlight_title'    => $product->get_name(),
				'highlight_subtitle' => $subtitle,
				'highlight_url'      => get_permalink($prod_id),
				'highlight_icon'     => $icons_cycle[ $icon_i % count($icons_cycle) ],
			);
			++$icon_i;
		}
		if (!empty($from_products)) {
			$highlights_items = $from_products;
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

$cta_resolved_url = $cta_button_url;
if ('' === $cta_resolved_url) {
	$cta_resolved_url = get_theme_mod('trimvia_header_primary_button_link', home_url('/consultation/'));
}

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
$help_text_stripped     = trim(wp_strip_all_tags($help_text));
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
				$f_icon = isset($feature_row['feature_icon']) ? $feature_row['feature_icon'] : 'clock';
				if (!isset($trimvia_service_icon_svgs[ $f_icon ])) {
					$f_icon = 'clock';
				}
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG sprite map.
				echo $trimvia_service_icon_svgs[ $f_icon ];
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
					<?php echo get_the_post_thumbnail($post_id, 'large', array('style' => 'width:100%;height:auto;display:block;')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
						$h_icon = isset($hi['highlight_icon']) ? $hi['highlight_icon'] : 'shield';
						if (!isset($trimvia_service_icon_svgs[ $h_icon ])) {
							$h_icon = 'shield';
						}
						$h_url = isset($hi['highlight_url']) ? trim((string) $hi['highlight_url']) : '';
						?>
					<div class="contact-item">
						<div class="contact-icon">
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG sprite map.
							echo $trimvia_service_icon_svgs[ $h_icon ];
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
					<div class="service-help-text"><?php echo wp_kses_post(wpautop($help_text)); ?></div>
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
