<?php
if (!defined('ABSPATH')) {
    exit;
}

$hero_title          = 'Your weight loss journey,';
$hero_title_emphasis = 'prescribed by experts.';
$hero_subtitle       = 'Clinician-led consultations, prescription treatments, and ongoing support - all from the comfort of your home. Results you can see in weeks.';
$hero_primary_cta    = array(
    'url'    => home_url('/shop/'),
    'title'  => 'Start Consultation',
    'target' => '',
);
$hero_secondary_cta  = array(
    'url'    => '#how',
    'title'  => 'How It Works',
    'target' => '',
);
$hero_rating_score   = '4.8';
$hero_rating_label   = 'Google Reviews';
$hero_pills          = array('GPhC Registered', 'Tracked Delivery', 'UK Prescribers');
$hero_bg_url         = '';
$hero_bg_mobile_url  = '';

if (function_exists('get_field')) {
    $slide_query = new WP_Query(
        array(
            'post_type'      => 'slide',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => 'is_active_slide',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
        )
    );

    if (!$slide_query->have_posts()) {
        $slide_query = new WP_Query(
            array(
                'post_type'      => 'slide',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'orderby'        => array(
                    'menu_order' => 'ASC',
                    'date'       => 'DESC',
                ),
            )
        );
    }

    if ($slide_query->have_posts()) {
        $slide_query->the_post();
        $slide_id = get_the_ID();

        $hero_title_value = get_field('hero_title', $slide_id);
        if (!empty($hero_title_value)) {
            $hero_title = $hero_title_value;
        }

        $hero_emphasis_value = get_field('hero_title_emphasis', $slide_id);
        if (!empty($hero_emphasis_value)) {
            $hero_title_emphasis = $hero_emphasis_value;
        }

        $hero_subtitle_value = get_field('hero_subtitle', $slide_id);
        if (!empty($hero_subtitle_value)) {
            $hero_subtitle = wp_strip_all_tags((string) $hero_subtitle_value);
        }

        $hero_bg_field = get_field('hero_bg_image', $slide_id);
        if (empty($hero_bg_field)) {
            $hero_bg_field = get_field('banner_image', $slide_id);
        }

        if (function_exists('trimvia_acf_image_url')) {
            $hero_bg_url = trimvia_acf_image_url($hero_bg_field, 'full');
        }

        $hero_bg_mobile_field = get_field('hero_bg_image_mobile', $slide_id);
        if (function_exists('trimvia_acf_image_url')) {
            $hero_bg_mobile_url = trimvia_acf_image_url($hero_bg_mobile_field, 'full');
        }

        $primary_cta = get_field('hero_primary_cta', $slide_id);
        if (empty($primary_cta)) {
            $primary_cta = get_field('call_to_action_1', $slide_id);
        }
        if (!empty($primary_cta['url'])) {
            $hero_primary_cta = $primary_cta;
        }

        $secondary_cta = get_field('hero_secondary_cta', $slide_id);
        if (empty($secondary_cta)) {
            $secondary_cta = get_field('call_to_action_2', $slide_id);
        }
        if (!empty($secondary_cta['url'])) {
            $hero_secondary_cta = $secondary_cta;
        }

        $hero_rating_score_value = get_field('hero_rating_score', $slide_id);
        if ($hero_rating_score_value !== null && $hero_rating_score_value !== false) {
            $hero_rating_score = trim((string) $hero_rating_score_value);
        }

        $hero_rating_label_value = get_field('hero_rating_label', $slide_id);
        if ($hero_rating_label_value !== null && $hero_rating_label_value !== false) {
            $hero_rating_label = trim((string) $hero_rating_label_value);
        }

        $pill_rows = get_field('hero_pills', $slide_id);
        if (is_array($pill_rows) && !empty($pill_rows)) {
            $hero_pills = array();
            foreach ($pill_rows as $pill_row) {
                $pill_text = isset($pill_row['pill_text']) ? trim((string) $pill_row['pill_text']) : '';
                if ($pill_text !== '') {
                    $hero_pills[] = $pill_text;
                }
            }
        }
    }
    wp_reset_postdata();
}

$home_page_id = get_queried_object_id();
if (!$home_page_id) {
    $home_page_id = (int) get_option('page_on_front');
}

$results_visible        = true;
$results_small_title    = 'The Results';
$results_heading        = "Real Patients,\nReal Progress";
$results_description    = "Our patients consistently report significant weight reduction, improved energy levels, and a renewed sense of confidence. With treatments like Mounjaro and Wegovy, clinically proven to reduce body weight by up to 15-22%, real change is not only possible - it's expected. Every journey is supported by our prescribers from day one.";
$results_cta            = array(
    'url'    => '#',
    'title'  => 'Start Your Journey',
    'target' => '',
);
$results_chart_title    = 'Average % Body Weight Reduction';
$results_chart_subtitle = 'Based on internal patient-reported averages';
$results_chart_image    = '';

$team_visible           = true;
$team_small_title       = 'Meet the Team';
$team_heading           = "Led by Experts in\nWeight Management";
$team_subheading        = 'UK-registered pharmacists and prescribers with deep experience in safe, sustainable weight loss.';

$how_visible            = true;
$how_small_title        = 'How It Works';
$how_heading            = "Three Simple Steps\nto a Healthier You";
$how_bottom_note        = 'Complete your online consultation in minutes. Our prescribers review your health profile and, if suitable, your treatment is dispatched the same day.';
$how_steps              = array(
    array(
        'number'      => '1',
        'title'       => 'Online Consultation',
        'description' => 'Tell us about your health, lifestyle and weight loss goals. It takes just a few minutes from your phone or computer.',
    ),
    array(
        'number'      => '2',
        'title'       => 'Prescriber Review',
        'description' => 'A UK-registered pharmacist prescriber reviews your consultation and recommends the most suitable treatment for you.',
    ),
    array(
        'number'      => '3',
        'title'       => 'Fast, Discreet Delivery',
        'description' => 'Approved treatments are dispatched the same day in plain, unbranded packaging with ongoing clinical support included.',
    ),
);

$why_visible            = true;
$why_small_title        = 'Why Trimvia';
$why_heading            = 'Why Choose Trimvia?';
$why_description        = 'Prescription-strength treatments, not fad diets. Every plan is built by UK-registered clinicians around your unique health profile.';
$why_points             = array(
    array('icon_class' => 'fa-solid fa-square-check', 'title' => 'Prescription treatments, not fads', 'subtitle' => 'Mounjaro, Wegovy & Orlistat available'),
    array('icon_class' => 'fa-solid fa-shield-halved', 'title' => 'Backed by clinical evidence', 'subtitle' => 'Evidence-based protocols, continuously updated'),
    array('icon_class' => 'fa-solid fa-clock', 'title' => 'UK-registered pharmacist prescribers', 'subtitle' => 'GPhC-regulated, Mayberry Pharmacy-dispensed'),
    array('icon_class' => 'fa-solid fa-users', 'title' => 'Ongoing support & monthly check-ins', 'subtitle' => 'We monitor your progress every step'),
    array('icon_class' => 'fa-solid fa-star', 'title' => 'Hundreds of 5-star reviews', 'subtitle' => 'Rated excellent by patients across the UK'),
);
$why_cta                = array(
    'url'    => '#',
    'title'  => 'Read More',
    'target' => '',
);
$why_media_image        = '';
$why_media_label        = 'Lifestyle Image / Video';
$why_media_hint         = 'e.g. person jogging, active lifestyle, or before/after transformation';
$why_stats              = array(
    array('value' => '15%', 'label' => 'Average body weight reduction over 24 weeks'),
    array('value' => '2,500+', 'label' => 'Patients treated through Mayberry Pharmacy'),
    array('value' => '4.8★', 'label' => 'Average rating across Google & Trustpilot'),
    array('value' => '24hr', 'label' => 'Discreet next-day delivery, every time'),
);

$cta_visible            = true;
$cta_small_title        = 'Start Today';
$cta_heading            = 'Take the first step towards a healthier you';
$cta_description        = 'Our free online consultation takes just minutes. A prescriber will review your profile and recommend the right treatment delivered to your door.';
$cta_button             = array(
    'url'    => home_url('/shop/'),
    'title'  => 'Start Consultation',
    'target' => '',
);
$cta_note               = 'GPhC-Regulated · Powered by Mayberry Pharmacy';
$cta_media_image        = '';

if (function_exists('get_field') && $home_page_id) {
    $results_visible_setting = get_field('results_section_visibility', $home_page_id);
    if ($results_visible_setting !== null && $results_visible_setting !== '') {
        $results_visible = (bool) $results_visible_setting;
    }

    $results_small_title_value = trim((string) get_field('results_section_small_title', $home_page_id));
    if ($results_small_title_value !== '') {
        $results_small_title = $results_small_title_value;
    }

    $results_heading_value = trim((string) get_field('results_section_heading', $home_page_id));
    if ($results_heading_value !== '') {
        $results_heading = $results_heading_value;
    }

    $results_description_value = trim((string) get_field('results_section_description', $home_page_id));
    if ($results_description_value !== '') {
        $results_description = $results_description_value;
    }

    $results_cta_value = get_field('results_section_cta', $home_page_id);
    if (!empty($results_cta_value['url'])) {
        $results_cta = $results_cta_value;
    }

    $results_chart_title_value = trim((string) get_field('results_chart_title', $home_page_id));
    if ($results_chart_title_value !== '') {
        $results_chart_title = $results_chart_title_value;
    }

    $results_chart_subtitle_value = trim((string) get_field('results_chart_subtitle', $home_page_id));
    if ($results_chart_subtitle_value !== '') {
        $results_chart_subtitle = $results_chart_subtitle_value;
    }

    $results_chart_image_field = get_field('results_chart_image', $home_page_id);
    if (function_exists('trimvia_acf_image_url')) {
        $results_chart_image = trimvia_acf_image_url($results_chart_image_field, 'full');
    }
    if (!$results_chart_image && is_numeric($results_chart_image_field)) {
        $results_chart_image = wp_get_attachment_image_url((int) $results_chart_image_field, 'full');
    }

    $team_visible_setting = get_field('team_section_visibility', $home_page_id);
    if ($team_visible_setting !== null && $team_visible_setting !== '') {
        $team_visible = (bool) $team_visible_setting;
    }

    $team_small_title_value = trim((string) get_field('team_section_small_title', $home_page_id));
    if ($team_small_title_value !== '') {
        $team_small_title = $team_small_title_value;
    }

    $team_heading_value = trim((string) get_field('team_section_heading', $home_page_id));
    if ($team_heading_value !== '') {
        $team_heading = $team_heading_value;
    }

    $team_subheading_value = trim((string) get_field('team_section_subheading', $home_page_id));
    if ($team_subheading_value !== '') {
        $team_subheading = $team_subheading_value;
    }

    $how_visible_setting = get_field('how_section_visibility', $home_page_id);
    if ($how_visible_setting !== null && $how_visible_setting !== '') {
        $how_visible = (bool) $how_visible_setting;
    }

    $how_small_title_value = trim((string) get_field('how_section_small_title', $home_page_id));
    if ($how_small_title_value !== '') {
        $how_small_title = $how_small_title_value;
    }

    $how_heading_value = trim((string) get_field('how_section_heading', $home_page_id));
    if ($how_heading_value !== '') {
        $how_heading = $how_heading_value;
    }

    $how_bottom_note_value = trim((string) get_field('how_section_bottom_note', $home_page_id));
    if ($how_bottom_note_value !== '') {
        $how_bottom_note = $how_bottom_note_value;
    }

    $how_steps_rows = get_field('how_section_steps', $home_page_id);
    if (is_array($how_steps_rows) && !empty($how_steps_rows)) {
        $how_steps = array();
        foreach ($how_steps_rows as $step_index => $how_step_row) {
            $step_number = trim((string) ($how_step_row['step_number'] ?? ''));
            $step_title = trim((string) ($how_step_row['step_title'] ?? ''));
            $step_description = trim((string) ($how_step_row['step_description'] ?? ''));

            if ('' === $step_title && '' === $step_description) {
                continue;
            }

            if ('' === $step_number) {
                $step_number = (string) ($step_index + 1);
            }

            $how_steps[] = array(
                'number'      => $step_number,
                'title'       => $step_title,
                'description' => $step_description,
            );
        }
    }

    $why_visible_setting = get_field('why_section_visibility', $home_page_id);
    if ($why_visible_setting !== null && $why_visible_setting !== '') {
        $why_visible = (bool) $why_visible_setting;
    }

    $why_small_title_value = trim((string) get_field('why_section_small_title', $home_page_id));
    if ($why_small_title_value !== '') {
        $why_small_title = $why_small_title_value;
    }

    $why_heading_value = trim((string) get_field('why_section_heading', $home_page_id));
    if ($why_heading_value !== '') {
        $why_heading = $why_heading_value;
    }

    $why_description_value = trim((string) get_field('why_section_description', $home_page_id));
    if ($why_description_value !== '') {
        $why_description = $why_description_value;
    }

    $why_points_rows = get_field('why_section_points', $home_page_id);
    if (is_array($why_points_rows) && !empty($why_points_rows)) {
        $why_points = array();
        foreach ($why_points_rows as $why_row) {
            $point_title = trim((string) ($why_row['point_title'] ?? ''));
            $point_subtitle = trim((string) ($why_row['point_subtitle'] ?? ''));
            $point_icon = trim((string) ($why_row['icon_class'] ?? ''));
            if ('' === $point_title && '' === $point_subtitle) {
                continue;
            }
            if ('' === $point_icon) {
                $point_icon = 'fa-solid fa-circle-check';
            }
            $why_points[] = array(
                'icon_class' => $point_icon,
                'title'      => $point_title,
                'subtitle'   => $point_subtitle,
            );
        }
    }

    $why_cta_value = get_field('why_section_cta', $home_page_id);
    if (!empty($why_cta_value['url'])) {
        $why_cta = $why_cta_value;
    }

    $why_media_image_field = get_field('why_section_media_image', $home_page_id);
    if (function_exists('trimvia_acf_image_url')) {
        $why_media_image = trimvia_acf_image_url($why_media_image_field, 'full');
    }
    if (!$why_media_image && is_numeric($why_media_image_field)) {
        $why_media_image = wp_get_attachment_image_url((int) $why_media_image_field, 'full');
    }

    $why_media_label_value = trim((string) get_field('why_section_media_label', $home_page_id));
    if ($why_media_label_value !== '') {
        $why_media_label = $why_media_label_value;
    }

    $why_media_hint_value = trim((string) get_field('why_section_media_hint', $home_page_id));
    if ($why_media_hint_value !== '') {
        $why_media_hint = $why_media_hint_value;
    }

    $why_stats_rows = get_field('why_section_stats', $home_page_id);
    if (metadata_exists('post', $home_page_id, 'why_section_stats')) {
        $why_stats = array();
        if (is_array($why_stats_rows)) {
            foreach ($why_stats_rows as $stat_row) {
                $stat_value = trim((string) ($stat_row['stat_value'] ?? ''));
                $stat_label = trim((string) ($stat_row['stat_label'] ?? ''));
                if ($stat_value === '' && $stat_label === '') {
                    continue;
                }
                $why_stats[] = array(
                    'value' => $stat_value,
                    'label' => $stat_label,
                );
            }
        }
    }

    $cta_visible_setting = get_field('cta_section_visibility', $home_page_id);
    if ($cta_visible_setting !== null && $cta_visible_setting !== '') {
        $cta_visible = (bool) $cta_visible_setting;
    }

    $cta_small_title_value = trim((string) get_field('cta_section_small_title', $home_page_id));
    if ($cta_small_title_value !== '') {
        $cta_small_title = $cta_small_title_value;
    }

    $cta_heading_value = trim((string) get_field('cta_section_heading', $home_page_id));
    if ($cta_heading_value !== '') {
        $cta_heading = $cta_heading_value;
    }

    $cta_description_value = trim((string) get_field('cta_section_description', $home_page_id));
    if ($cta_description_value !== '') {
        $cta_description = $cta_description_value;
    }

    $cta_button_value = get_field('cta_section_button', $home_page_id);
    if (!empty($cta_button_value['url'])) {
        $cta_button = $cta_button_value;
    }

    $cta_note_value = trim((string) get_field('cta_section_note', $home_page_id));
    if ($cta_note_value !== '') {
        $cta_note = $cta_note_value;
    }

    $cta_media_image_field = get_field('cta_section_media_image', $home_page_id);
    if (function_exists('trimvia_acf_image_url')) {
        $cta_media_image = trimvia_acf_image_url($cta_media_image_field, 'full');
    }
    if (!$cta_media_image && is_numeric($cta_media_image_field)) {
        $cta_media_image = wp_get_attachment_image_url((int) $cta_media_image_field, 'full');
    }
}

if (isset($hero_primary_cta['title']) && stripos((string) $hero_primary_cta['title'], 'consultation') !== false) {
    $hero_primary_cta['title'] = 'Start Consultation';
}
if (empty($hero_primary_cta['url']) || (isset($hero_primary_cta['url']) && stripos((string) $hero_primary_cta['url'], 'consultation') !== false)) {
    $hero_primary_cta['url'] = home_url('/shop/');
}

if (isset($cta_button['title']) && stripos((string) $cta_button['title'], 'consultation') !== false) {
    $cta_button['title'] = 'Start Consultation';
}
if (empty($cta_button['url']) || (isset($cta_button['url']) && stripos((string) $cta_button['url'], 'consultation') !== false)) {
    $cta_button['url'] = home_url('/shop/');
}

$hero_img_style_parts = array();
if (!empty($hero_bg_url)) {
    $hero_img_style_parts[] = "--hero-desktop-bg:url('" . esc_url($hero_bg_url) . "')";
}
if (!empty($hero_bg_mobile_url)) {
    $hero_img_style_parts[] = "--hero-mobile-bg:url('" . esc_url($hero_bg_mobile_url) . "')";
}
$hero_img_style_attr = '';
if (!empty($hero_img_style_parts)) {
    $hero_img_style_attr = ' style="' . esc_attr(implode(';', $hero_img_style_parts)) . '"';
}
?>
<section class="hero">
  <div class="hero-bg">
    <div class="hero-gradient"></div>
    <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
    <div class="hero-noise"></div><div class="hero-grid"></div>
  </div>
  <div class="hero-glow"></div>
  <div class="hero-img"<?php echo $hero_img_style_attr; ?>><div class="hero-img-overlay"></div></div>
  <div class="hero-content">
    <h1><?php echo esc_html($hero_title); ?> <em><?php echo esc_html($hero_title_emphasis); ?></em></h1>
    <p class="hero-sub"><?php echo esc_html($hero_subtitle); ?></p>
    <div class="hero-btns">
      <a href="<?php echo esc_url($hero_primary_cta['url']); ?>" class="btn-hero" <?php echo !empty($hero_primary_cta['target']) ? 'target="' . esc_attr($hero_primary_cta['target']) . '"' : ''; ?>><span><?php echo esc_html($hero_primary_cta['title']); ?></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      <a href="<?php echo esc_url($hero_secondary_cta['url']); ?>" class="btn-hero-o" <?php echo !empty($hero_secondary_cta['target']) ? 'target="' . esc_attr($hero_secondary_cta['target']) . '"' : ''; ?>><?php echo esc_html($hero_secondary_cta['title']); ?></a>
    </div>
    <div class="hero-proof">
      <?php if (!empty($hero_rating_score) || !empty($hero_rating_label)) : ?>
        <div style="display:flex;align-items:center;gap:8px">
          <?php if (!empty($hero_rating_score)) : ?>
            <div class="hero-score"><?php echo esc_html($hero_rating_score); ?></div>
          <?php endif; ?>
          <div>
            <div class="hero-stars"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
            <?php if (!empty($hero_rating_label)) : ?>
              <div class="hero-rating-lbl"><?php echo esc_html($hero_rating_label); ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="hero-div"></div>
      <?php endif; ?>
      <div class="hero-pills">
        <?php foreach ($hero_pills as $hero_pill) : ?>
          <span class="hero-pill"><?php echo esc_html($hero_pill); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php
$trust_bar_visible = true;
$trust_bar_items = array(
  array(
    'icon_class' => 'fa-solid fa-shield-halved',
    'title'      => 'GPhC-Regulated Pharmacy',
    'subtitle'   => 'Fully registered & compliant',
  ),
  array(
    'icon_class' => 'fa-solid fa-user-doctor',
    'title'      => 'UK Pharmacist Prescribers',
    'subtitle'   => 'Expert clinical assessments',
  ),
  array(
    'icon_class' => 'fa-solid fa-lock',
    'title'      => '100% Confidential & Secure',
    'subtitle'   => 'Your data is always protected',
  ),
  array(
    'icon_class' => 'fa-solid fa-truck-fast',
    'title'      => 'Tracked Delivery',
    'subtitle'   => 'Discreet, unbranded packaging',
  ),
  array(
    'icon_class' => 'fa-solid fa-wave-square',
    'title'      => 'Science-Backed Treatments',
    'subtitle'   => 'Clinically proven GLP-1 therapies',
  ),
);

if (function_exists('get_field')) {
  $trust_home_page_id = get_queried_object_id();
  if (!$trust_home_page_id) {
    $trust_home_page_id = (int) get_option('page_on_front');
  }

  if ($trust_home_page_id) {
    $trust_visibility_setting = get_field('trust_bar_visibility', $trust_home_page_id);
    if ($trust_visibility_setting !== null && $trust_visibility_setting !== '') {
      $trust_bar_visible = (bool) $trust_visibility_setting;
    }

    $trust_items_value = get_field('trust_bar_items', $trust_home_page_id);
    if (is_array($trust_items_value) && !empty($trust_items_value)) {
      $trust_bar_items = array();
      foreach ($trust_items_value as $trust_item) {
        $item_icon = trim((string) ($trust_item['icon_class'] ?? ''));
        $item_title = trim((string) ($trust_item['title'] ?? ''));
        $item_subtitle = trim((string) ($trust_item['subtitle'] ?? ''));

        if ('' === $item_title && '' === $item_subtitle) {
          continue;
        }

        if ('' === $item_icon) {
          $item_icon = 'fa-solid fa-circle-check';
        }

        $trust_bar_items[] = array(
          'icon_class' => $item_icon,
          'title'      => $item_title,
          'subtitle'   => $item_subtitle,
        );
      }
    }
  }
}
?>
<?php if ($trust_bar_visible && !empty($trust_bar_items)) : ?>
<section class="trust-bar">
  <div class="trust-track">
    <div class="trust-set">
      <?php foreach ($trust_bar_items as $trust_item) : ?>
        <div class="t-chip"><div class="t-icon"><i class="<?php echo esc_attr($trust_item['icon_class']); ?>" aria-hidden="true"></i></div><div><h4><?php echo esc_html($trust_item['title']); ?></h4><p><?php echo esc_html($trust_item['subtitle']); ?></p></div></div>
      <?php endforeach; ?>
    </div>
    <div class="trust-set" aria-hidden="true">
      <?php foreach ($trust_bar_items as $trust_item) : ?>
        <div class="t-chip"><div class="t-icon"><i class="<?php echo esc_attr($trust_item['icon_class']); ?>" aria-hidden="true"></i></div><div><h4><?php echo esc_html($trust_item['title']); ?></h4><p><?php echo esc_html($trust_item['subtitle']); ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($how_visible) : ?>
<section class="how-sec section-pad" id="how">
  <div class="container">
    <div class="rv" style="margin-bottom:64px;">
      <div class="stag"><?php echo esc_html($how_small_title); ?></div>
      <h2 class="stitle"><?php echo nl2br(esc_html($how_heading)); ?></h2>
    </div>
    <div class="steps">
      <?php foreach ($how_steps as $step_index => $how_step) : ?>
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
          <div class="snum"><?php echo esc_html($how_step['number']); ?></div><h3><?php echo esc_html($how_step['title']); ?></h3><p><?php echo esc_html($how_step['description']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="rv" style="margin-top:56px;font-size:16px;color:var(--g500);text-align:center;max-width:580px;margin-left:auto;margin-right:auto;line-height:1.75;font-weight:300;"><?php echo esc_html($how_bottom_note); ?></p>
  </div>
</section>
<?php endif; ?>

<?php if ($why_visible) : ?>
<section class="why-sec section-pad" id="why">
  <div class="hero-noise"></div>
  <div class="container">
    <div class="why-grid">
      <div class="rv">
        <div class="stag"><?php echo esc_html($why_small_title); ?></div><h2 class="stitle"><?php echo esc_html($why_heading); ?></h2><p class="sdesc"><?php echo esc_html($why_description); ?></p>
        <div class="wlist">
          <?php foreach ($why_points as $why_point) : ?>
          <div class="witem"><div class="wico"><i class="<?php echo esc_attr($why_point['icon_class']); ?>" aria-hidden="true"></i></div><div><h4><?php echo esc_html($why_point['title']); ?></h4><p class="wsub"><?php echo esc_html($why_point['subtitle']); ?></p></div></div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:32px;padding-left:60px;">
          <a href="<?php echo esc_url($why_cta['url']); ?>" class="btn-outline" <?php echo !empty($why_cta['target']) ? 'target="' . esc_attr($why_cta['target']) . '"' : ''; ?>><?php echo esc_html($why_cta['title']); ?> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>
      <div class="rv rv-d2" style="display:flex;flex-direction:column;gap:24px;">
        <?php if (!empty($why_media_image)) : ?>
          <div class="media-zone media-zone--dark media-zone--why media-zone--filled">
            <img src="<?php echo esc_url($why_media_image); ?>" alt="<?php echo esc_attr($why_media_label); ?>" class="why-media-image" loading="lazy" decoding="async">
          </div>
        <?php else : ?>
          <div class="media-zone media-zone--dark media-zone--why" style="min-height:320px;">
            <div class="media-zone-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
            <div class="media-zone-label"><?php echo esc_html($why_media_label); ?></div>
            <div class="media-zone-hint"><?php echo esc_html($why_media_hint); ?></div>
          </div>
        <?php endif; ?>
        <div class="stats-col">
          <?php foreach ($why_stats as $why_stat) : ?>
          <div class="stat-g"><div class="stat-v"><?php echo esc_html($why_stat['value']); ?></div><div class="stat-l"><?php echo esc_html($why_stat['label']); ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($results_visible) : ?>
<section class="res-sec section-pad" id="results">
  <div class="container">
    <div class="res-grid">
      <div class="rv"><div class="stag"><?php echo esc_html($results_small_title); ?></div><h2 class="stitle"><?php echo nl2br(esc_html($results_heading)); ?></h2><p class="sdesc"><?php echo esc_html($results_description); ?></p><?php if (!empty($results_cta['url']) && !empty($results_cta['title'])) : ?><div style="margin-top:36px"><a href="<?php echo esc_url($results_cta['url']); ?>" class="btn-accent" style="padding:14px 34px;font-size:15px" <?php echo !empty($results_cta['target']) ? 'target="' . esc_attr($results_cta['target']) . '"' : ''; ?>><?php echo esc_html($results_cta['title']); ?></a></div><?php endif; ?></div>
      <div class="chart-card rv rv-d2"><h4><?php echo esc_html($results_chart_title); ?></h4><p class="chart-sub"><?php echo esc_html($results_chart_subtitle); ?></p><div class="weight-chart-wrap"><?php if (!empty($results_chart_image)) : ?><img src="<?php echo esc_url($results_chart_image); ?>" alt="<?php echo esc_attr($results_chart_title); ?>" class="results-chart-image"><?php else : ?><canvas id="weightChart"></canvas><?php endif; ?></div></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
$team_members = array();
$team_query = new WP_Query(
  array(
    'post_type'      => 'team_member',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
      array(
        'key'     => 'team_member_show_homepage',
        'value'   => '1',
        'compare' => '=',
      ),
    ),
    'meta_key'       => 'team_member_display_order',
    'orderby'        => array(
      'meta_value_num' => 'ASC',
      'menu_order'     => 'ASC',
      'date'           => 'DESC',
    ),
  )
);

if ($team_query->have_posts()) {
  while ($team_query->have_posts()) {
    $team_query->the_post();
    $member_id = get_the_ID();

    $member_image = function_exists('get_field') ? get_field('team_member_image', $member_id) : '';
    $member_image_url = function_exists('trimvia_acf_image_url') ? trimvia_acf_image_url($member_image, 'full') : '';
    if (!$member_image_url) {
      $member_image_url = get_the_post_thumbnail_url($member_id, 'full');
    }

    $department = function_exists('get_field') ? trim((string) get_field('team_member_department', $member_id)) : '';
    $designation = function_exists('get_field') ? trim((string) get_field('team_member_designation', $member_id)) : '';
    $description = function_exists('get_field') ? trim((string) get_field('team_member_description', $member_id)) : '';
    if ($description === '') {
      $description = has_excerpt($member_id) ? get_the_excerpt($member_id) : wp_trim_words(wp_strip_all_tags((string) get_the_content(null, false, $member_id)), 22);
    }

    $role_parts = array_filter(array($department, $designation));
    $role_line = implode(' - ', $role_parts);

    $team_members[] = array(
      'name'        => get_the_title($member_id),
      'role_line'   => $role_line,
      'description' => $description,
      'image_url'   => $member_image_url ?: '',
    );
  }
  wp_reset_postdata();
}
?>

<?php if ($team_visible) : ?>
<section class="section-pad" id="team" style="background:var(--white)">
  <div class="container">
    <div class="rv"><div class="stag"><?php echo esc_html($team_small_title); ?></div><h2 class="stitle"><?php echo nl2br(esc_html($team_heading)); ?></h2><p class="sdesc"><?php echo esc_html($team_subheading); ?></p></div>
    <div class="team-row">
      <?php if (!empty($team_members)) : ?>
        <?php foreach ($team_members as $index => $team_member) : ?>
          <?php
          $delay_class = '';
          if (0 === $index % 3) {
            $delay_class = ' rv-d1';
          } elseif (1 === $index % 3) {
            $delay_class = ' rv-d2';
          } else {
            $delay_class = ' rv-d3';
          }
          ?>
          <div class="tcard rv<?php echo esc_attr($delay_class); ?>">
            <div class="tphoto">
              <?php if (!empty($team_member['image_url'])) : ?>
                <img src="<?php echo esc_url($team_member['image_url']); ?>" alt="<?php echo esc_attr($team_member['name']); ?>">
              <?php else : ?>
                <div class="tphoto-init"><?php echo esc_html(strtoupper(substr($team_member['name'], 0, 1))); ?></div>
              <?php endif; ?>
            </div>
            <div class="tbody">
              <h3><?php echo esc_html($team_member['name']); ?></h3>
              <?php if (!empty($team_member['role_line'])) : ?>
                <div class="trole"><?php echo esc_html($team_member['role_line']); ?></div>
              <?php endif; ?>
              <?php if (!empty($team_member['description'])) : ?>
                <p><?php echo esc_html($team_member['description']); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="tcard rv rv-d1"><div class="tphoto"><div class="media-zone media-zone--team"><div class="media-zone-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><div class="media-zone-label">Team Photo</div></div></div><div class="tbody"><h3>Lorem Ipsum</h3><div class="trole">MPharmS IP - Independent Prescribing Pharmacist</div><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.</p></div></div>
        <div class="tcard rv rv-d2"><div class="tphoto"><div class="media-zone media-zone--team"><div class="media-zone-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><div class="media-zone-label">Team Photo</div></div></div><div class="tbody"><h3>Lorem Ipsum</h3><div class="trole">MPharmS - Pharmacist Operations Manager</div><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.</p></div></div>
        <div class="tcard rv rv-d3"><div class="tphoto"><div class="media-zone media-zone--team"><div class="media-zone-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><div class="media-zone-label">Team Photo</div></div></div><div class="tbody"><h3>Lorem Ipsum</h3><div class="trole">MRPharmS - Pharmacist &amp; Director</div><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p></div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
$testimonials_visible      = true;
$testimonials_small_title  = 'Customer Testimonials';
$testimonials_heading      = 'What Our Patients Say';
$testimonials_description  = '';
$testimonials_type         = 'manual';
$trustindex_shortcode      = '';
$reviewsio_shortcode       = '';
$testimonial_cards         = array();

if (function_exists('get_field') && $home_page_id) {
  $testimonials_visible_setting = get_field('reviews_section_visibility', $home_page_id);
  if ($testimonials_visible_setting !== null && $testimonials_visible_setting !== '') {
    $testimonials_visible = (bool) $testimonials_visible_setting;
  }

  $testimonials_small_title_value = trim((string) get_field('rev_section_heading', $home_page_id));
  if ($testimonials_small_title_value !== '') {
    $testimonials_small_title = $testimonials_small_title_value;
  }

  $testimonials_heading_value = trim((string) get_field('rev_heading', $home_page_id));
  if ($testimonials_heading_value !== '') {
    $testimonials_heading = $testimonials_heading_value;
  }

  $testimonials_description_value = trim((string) get_field('reviews_short_description', $home_page_id));
  if ($testimonials_description_value !== '') {
    $testimonials_description = $testimonials_description_value;
  }

  $testimonials_type_value = trim((string) get_field('testimonials_type', $home_page_id));
  if ($testimonials_type_value !== '') {
    $testimonials_type = $testimonials_type_value;
  }

  $trustindex_shortcode = trim((string) get_field('trustindex_shortcode', $home_page_id));
  $reviewsio_shortcode  = trim((string) get_field('reviewsio_widget', $home_page_id));

  if ('manual' === $testimonials_type) {
    $selected_testimonials = get_field('selected_testimonials', $home_page_id);
    $manual_testimonial_ids = array();

    if (is_array($selected_testimonials) && !empty($selected_testimonials)) {
      foreach ($selected_testimonials as $selected_item) {
        if (is_object($selected_item) && !empty($selected_item->ID)) {
          $manual_testimonial_ids[] = (int) $selected_item->ID;
        } elseif (is_numeric($selected_item)) {
          $manual_testimonial_ids[] = (int) $selected_item;
        }
      }
    }

    if (!empty($manual_testimonial_ids)) {
      $testimonial_query = new WP_Query(
        array(
          'post_type'      => 'testimonial',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'post__in'       => $manual_testimonial_ids,
          'orderby'        => 'post__in',
        )
      );
    } else {
      $testimonial_query = new WP_Query(
        array(
          'post_type'      => 'testimonial',
          'post_status'    => 'publish',
          'posts_per_page' => 6,
          'meta_query'     => array(
            array(
              'key'     => 'show_on_homepage',
              'value'   => '1',
              'compare' => '=',
            ),
          ),
          'meta_key'       => 'display_order',
          'orderby'        => array(
            'meta_value_num' => 'ASC',
            'menu_order'     => 'ASC',
            'date'           => 'DESC',
          ),
        )
      );
    }

    if (!empty($testimonial_query) && $testimonial_query->have_posts()) {
      while ($testimonial_query->have_posts()) {
        $testimonial_query->the_post();
        $testimonial_id = get_the_ID();

        $reviewer_name = trim((string) get_field('reviewer_name', $testimonial_id));
        if ('' === $reviewer_name) {
          $reviewer_name = get_the_title($testimonial_id);
        }

        $reviewer_date = trim((string) get_field('reviewer_date', $testimonial_id));
        $reviewer_rating = (int) get_field('reviewer_rating', $testimonial_id);
        if ($reviewer_rating < 1 || $reviewer_rating > 5) {
          $reviewer_rating = 5;
        }

        $reviewer_initials = trim((string) get_field('reviewer_initials', $testimonial_id));
        if ('' === $reviewer_initials && '' !== $reviewer_name) {
          $name_parts = preg_split('/\s+/', $reviewer_name);
          $name_parts = array_filter($name_parts);
          if (!empty($name_parts)) {
            $first_initial = strtoupper(substr((string) reset($name_parts), 0, 1));
            $last_initial = '';
            if (count($name_parts) > 1) {
              $last_item = end($name_parts);
              $last_initial = strtoupper(substr((string) $last_item, 0, 1));
            }
            $reviewer_initials = $first_initial . $last_initial;
          }
        }

        $testimonial_cards[] = array(
          'title'    => get_the_title($testimonial_id),
          'content'  => wp_strip_all_tags((string) get_the_content(null, false, $testimonial_id)),
          'name'     => $reviewer_name,
          'date'     => $reviewer_date,
          'rating'   => $reviewer_rating,
          'initials' => $reviewer_initials ?: 'TR',
        );
      }
      wp_reset_postdata();
    }
  }
}
?>

<?php if ($testimonials_visible) : ?>
<section class="testi-sec section-pad" id="testimonials">
  <div class="container">
    <div class="rv" style="text-align:center;margin-bottom:64px"><div class="stag" style="justify-content:center"><?php echo esc_html($testimonials_small_title); ?></div><h2 class="stitle"><?php echo esc_html($testimonials_heading); ?></h2><?php if ('' !== $testimonials_description) : ?><p class="sdesc" style="margin:14px auto 0;"><?php echo esc_html($testimonials_description); ?></p><?php endif; ?></div>

    <?php if ('trustindex' === $testimonials_type && '' !== $trustindex_shortcode) : ?>
      <div class="testimonial-embed rv"><?php echo do_shortcode($trustindex_shortcode); ?></div>
    <?php elseif ('reviewsio' === $testimonials_type && '' !== $reviewsio_shortcode) : ?>
      <div class="testimonial-embed rv"><?php echo do_shortcode($reviewsio_shortcode); ?></div>
    <?php elseif (!empty($testimonial_cards)) : ?>
      <div class="tg">
        <?php foreach ($testimonial_cards as $card_index => $testimonial_card) : ?>
          <?php
          $delay_class = '';
          if (0 === $card_index % 3) {
            $delay_class = ' rv-d1';
          } elseif (1 === $card_index % 3) {
            $delay_class = ' rv-d2';
          } else {
            $delay_class = ' rv-d3';
          }
          ?>
          <div class="tc rv<?php echo esc_attr($delay_class); ?>">
            <div class="tc-stars">
              <?php for ($star = 0; $star < $testimonial_card['rating']; $star++) : ?>
                <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              <?php endfor; ?>
            </div>
            <h4><?php echo esc_html($testimonial_card['title']); ?></h4>
            <blockquote><?php echo esc_html($testimonial_card['content']); ?></blockquote>
            <div class="tc-author"><div class="tc-av"><?php echo esc_html($testimonial_card['initials']); ?></div><div><div class="nm"><?php echo esc_html($testimonial_card['name']); ?></div><div class="dt"><?php echo esc_html($testimonial_card['date']); ?></div></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="tg">
        <div class="tc rv rv-d1">
          <div class="tc-stars"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
          <h4>Lorem Ipsum Dolor Sit</h4><blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</blockquote>
          <div class="tc-author"><div class="tc-av">JD</div><div><div class="nm">Jane Doe</div><div class="dt">DD/MM/YYYY</div></div></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php
$faq_visible      = true;
$faq_small_heading = 'FAQs';
$faq_heading_text  = "We Can Answer All\nYour Questions";
$faq_description   = 'Everything you need to know about starting your weight loss journey with Trimvia.';
$dynamic_faq_items = array();

if (function_exists('get_field')) {
  $home_page_id = get_queried_object_id();
  if (!$home_page_id) {
    $home_page_id = (int) get_option('page_on_front');
  }

  if ($home_page_id) {
    $faq_visible_setting = get_field('faqs_visibility', $home_page_id);
    if ($faq_visible_setting !== null && $faq_visible_setting !== '') {
      $faq_visible = (bool) $faq_visible_setting;
    }

    $faq_section_heading = get_field('faq_section_heading', $home_page_id);
    if (!empty($faq_section_heading)) {
      $faq_small_heading = $faq_section_heading;
    }

    $faq_heading_value = get_field('faq_heading', $home_page_id);
    if (!empty($faq_heading_value)) {
      $faq_heading_text = $faq_heading_value;
    }

    $faq_description_value = get_field('faq_short_description', $home_page_id);
    if (!empty($faq_description_value)) {
      $faq_description = wp_strip_all_tags((string) $faq_description_value);
    }

    $faq_entries = get_field('faq_entry', $home_page_id);
    if (!empty($faq_entries) && is_array($faq_entries)) {
      foreach ($faq_entries as $faq_entry) {
        $faq_post = is_object($faq_entry) ? $faq_entry : get_post((int) $faq_entry);
        if (!$faq_post || 'publish' !== $faq_post->post_status) {
          continue;
        }

        $dynamic_faq_items[] = array(
          'question' => get_the_title($faq_post),
          'answer'   => apply_filters('the_content', (string) $faq_post->post_content),
        );
      }
    }
  }
}
?>

<?php if ($faq_visible) : ?>
<section class="section-pad" id="faq" style="background:var(--white)">
  <div class="container">
    <div class="faq-center rv">
      <div class="stag" style="justify-content:center"><?php echo esc_html($faq_small_heading); ?></div>
      <h2 class="stitle"><?php echo nl2br(esc_html($faq_heading_text)); ?></h2>
      <p class="sdesc"><?php echo esc_html($faq_description); ?></p>
    </div>
    <div class="faq-list">
      <?php if (!empty($dynamic_faq_items)) : ?>
        <?php foreach ($dynamic_faq_items as $index => $faq_item) : ?>
          <div class="fq <?php echo 0 === $index ? 'active' : ''; ?> rv">
            <button class="fq-btn"><?php echo esc_html($faq_item['question']); ?><div class="fq-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg></div></button>
            <div class="fq-a"><div class="fq-a-in"><?php echo wp_kses_post($faq_item['answer']); ?></div></div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="fq active rv"><button class="fq-btn">What happens if I'm not eligible for a treatment?<div class="fq-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg></div></button><div class="fq-a"><div class="fq-a-in">If our clinicians determine that a treatment isn't suitable for you, we'll let you know and suggest alternative options or advise you to speak with your GP. You'll never be charged for a treatment that isn't approved.</div></div></div>
        <div class="fq rv"><button class="fq-btn">Can I cancel anytime?<div class="fq-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg></div></button><div class="fq-a"><div class="fq-a-in">Yes, absolutely. No lock-in contracts. You can pause or cancel your treatment plan at any time by contacting our team.</div></div></div>
        <div class="fq rv"><button class="fq-btn">Are my purchases secure?<div class="fq-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg></div></button><div class="fq-a"><div class="fq-a-in">All transactions use industry-standard encryption. Your personal and payment information is fully protected. We're ICO registered and fully GDPR compliant.</div></div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($cta_visible) : ?>
<section class="cta-sec">
  <div class="orb orb-1" style="top:-30%;right:-20%;opacity:.4"></div>
  <div class="orb orb-2" style="bottom:-30%;left:-15%;opacity:.25"></div>
  <div class="hero-noise"></div>
  <div class="cta-layout">
    <div class="cta-text rv">
      <div class="stag" style="color:#8BB0F7"><?php echo esc_html($cta_small_title); ?></div>
      <h2 class="stitle"><?php echo esc_html($cta_heading); ?></h2>
      <p class="sdesc"><?php echo esc_html($cta_description); ?></p>
      <?php if (!empty($cta_button['url']) && !empty($cta_button['title'])) : ?>
      <a href="<?php echo esc_url($cta_button['url']); ?>" class="btn-cta" <?php echo !empty($cta_button['target']) ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>><?php echo esc_html($cta_button['title']); ?> <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      <?php endif; ?>
      <div class="cta-note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <?php echo esc_html($cta_note); ?>
      </div>
    </div>
    <div class="cta-media rv rv-d2">
      <div class="cta-media-frame">
        <?php if (!empty($cta_media_image)) : ?>
          <img src="<?php echo esc_url($cta_media_image); ?>" alt="<?php echo esc_attr($cta_heading); ?>" class="cta-media-image">
        <?php else : ?>
          <img src="assets/images/home-cta.jpg" alt="Woman running on treadmill" class="cta-media-image">
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

