<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id = get_the_ID();

$hero_title = 'About Trimvia';
$hero_description = 'A clinician-led online weight loss clinic powered by Mayberry Pharmacy. Safe, regulated, and built around you.';

$show_stats_section = true;
$stats_items = array(
    array('value' => '2,500+', 'label' => 'Patients Treated'),
    array('value' => '4.8★', 'label' => 'Google Rating'),
    array('value' => '24hr', 'label' => 'Delivery Turnaround'),
    array('value' => 'GPhC', 'label' => 'Fully Regulated'),
);

$show_mission_section = true;
$mission_tag = 'Our Mission';
$mission_title = 'Making weight loss safe, accessible, and effective';
$mission_description_one = 'Trimvia was founded with a clear purpose: to give people access to prescription-strength weight loss treatments without the barriers. Every consultation is reviewed by a UK-registered pharmacist prescriber, every treatment is dispensed by Mayberry Pharmacy, and every patient receives ongoing clinical support.';
$mission_description_two = 'We believe that weight management should be led by evidence, not trends. That\'s why we only offer clinically proven treatments - Mounjaro, Wegovy, and Orlistat - backed by real-world results and continuous prescriber oversight.';
$mission_image_url = '';

$show_values_section = true;
$values_tag = 'Our Values';
$values_title = 'What We Stand For';
$values_items = array(
    array('title' => 'Patient Safety First', 'description' => 'Every decision is guided by clinical safety. Our prescribers only approve treatments that are right for you.', 'icon_class' => ''),
    array('title' => 'Evidence-Based Care', 'description' => 'We only offer treatments proven by clinical trials. No fads, no shortcuts - just science that works.', 'icon_class' => ''),
    array('title' => 'Ongoing Support', 'description' => 'Your journey doesn\'t end at checkout. We provide monthly check-ins and adjust your plan as you progress.', 'icon_class' => ''),
    array('title' => 'Complete Confidentiality', 'description' => 'Your data is encrypted and protected. Treatments arrive in discreet, unbranded packaging.', 'icon_class' => ''),
    array('title' => 'Speed & Convenience', 'description' => 'Consultations take minutes. Approved treatments are dispatched the same day for next-day delivery.', 'icon_class' => ''),
    array('title' => 'Full Regulation', 'description' => 'GPhC-registered and dispensed through Mayberry Pharmacy, a fully regulated NHS pharmacy.', 'icon_class' => ''),
);

$show_team_section = true;
$team_tag = 'Meet the Team';
$team_title = 'Led by Experts in<br>Weight Management';
$team_description = 'Our clinical team is made up of UK-registered pharmacists and prescribers with deep experience in safe, sustainable weight loss.';
$team_members = array();

$show_regulatory_section = true;
$regulatory_tag = 'Regulatory';
$regulatory_title = 'Fully Regulated &amp; Compliant';
$regulatory_description = 'All prescriptions are dispensed by Mayberry Pharmacy, a GPhC-registered NHS pharmacy. Our prescribers are UK-registered pharmacist independent prescribers.';
$regulatory_items = array(
    array('title' => 'GPhC Registered', 'description' => 'Fully registered with the General Pharmaceutical Council', 'icon_class' => ''),
    array('title' => 'ICO Registered', 'description' => 'Fully GDPR compliant and registered with the ICO', 'icon_class' => ''),
    array('title' => 'NHS Pharmacy', 'description' => 'Dispensed by Mayberry Pharmacy, a regulated NHS pharmacy', 'icon_class' => ''),
);

if (function_exists('get_field') && $page_id) {
    $hero_title_value = trim((string) get_field('about_hero_title', $page_id));
    if ('' !== $hero_title_value) {
        $hero_title = $hero_title_value;
    }

    $hero_description_value = trim((string) get_field('about_hero_description', $page_id));
    if ('' !== $hero_description_value) {
        $hero_description = $hero_description_value;
    }

    $stats_visibility_value = get_field('about_stats_visibility', $page_id);
    if (null !== $stats_visibility_value && '' !== $stats_visibility_value) {
        $show_stats_section = (bool) $stats_visibility_value;
    }

    $stats_items_value = get_field('about_stats_items', $page_id);
    if (is_array($stats_items_value) && !empty($stats_items_value)) {
        $clean_stats_items = array();
        foreach ($stats_items_value as $stats_row) {
            $value = isset($stats_row['value']) ? trim((string) $stats_row['value']) : '';
            $label = isset($stats_row['label']) ? trim((string) $stats_row['label']) : '';
            if ('' === $value && '' === $label) {
                continue;
            }
            $clean_stats_items[] = array('value' => $value, 'label' => $label);
        }
        if (!empty($clean_stats_items)) {
            $stats_items = $clean_stats_items;
        }
    }

    $mission_visibility_value = get_field('about_mission_visibility', $page_id);
    if (null !== $mission_visibility_value && '' !== $mission_visibility_value) {
        $show_mission_section = (bool) $mission_visibility_value;
    }

    $mission_tag_value = trim((string) get_field('about_mission_tag', $page_id));
    if ('' !== $mission_tag_value) {
        $mission_tag = $mission_tag_value;
    }

    $mission_title_value = trim((string) get_field('about_mission_title', $page_id));
    if ('' !== $mission_title_value) {
        $mission_title = $mission_title_value;
    }

    $mission_desc_one_value = trim((string) get_field('about_mission_description_one', $page_id));
    if ('' !== $mission_desc_one_value) {
        $mission_description_one = $mission_desc_one_value;
    }

    $mission_desc_two_value = trim((string) get_field('about_mission_description_two', $page_id));
    if ('' !== $mission_desc_two_value) {
        $mission_description_two = $mission_desc_two_value;
    }

    $mission_image_value = get_field('about_mission_image', $page_id);
    if (function_exists('trimvia_acf_image_url')) {
        $mission_image_url = trimvia_acf_image_url($mission_image_value, 'large');
    }

    $values_visibility_value = get_field('about_values_visibility', $page_id);
    if (null !== $values_visibility_value && '' !== $values_visibility_value) {
        $show_values_section = (bool) $values_visibility_value;
    }

    $values_tag_value = trim((string) get_field('about_values_tag', $page_id));
    if ('' !== $values_tag_value) {
        $values_tag = $values_tag_value;
    }

    $values_title_value = trim((string) get_field('about_values_title', $page_id));
    if ('' !== $values_title_value) {
        $values_title = $values_title_value;
    }

    $values_items_value = get_field('about_values_items', $page_id);
    if (is_array($values_items_value) && !empty($values_items_value)) {
        $clean_values_items = array();
        foreach ($values_items_value as $value_row) {
            $title = isset($value_row['title']) ? trim((string) $value_row['title']) : '';
            $description = isset($value_row['description']) ? trim((string) $value_row['description']) : '';
            $icon_class = isset($value_row['icon_class']) ? trim((string) $value_row['icon_class']) : '';
            if ('' === $title && '' === $description) {
                continue;
            }
            $clean_values_items[] = array('title' => $title, 'description' => $description, 'icon_class' => $icon_class);
        }
        if (!empty($clean_values_items)) {
            $values_items = $clean_values_items;
        }
    }

    $team_visibility_value = get_field('about_team_visibility', $page_id);
    if (null !== $team_visibility_value && '' !== $team_visibility_value) {
        $show_team_section = (bool) $team_visibility_value;
    }

    $team_tag_value = trim((string) get_field('about_team_tag', $page_id));
    if ('' !== $team_tag_value) {
        $team_tag = $team_tag_value;
    }

    $team_title_value = trim((string) get_field('about_team_title', $page_id));
    if ('' !== $team_title_value) {
        $team_title = $team_title_value;
    }

    $team_description_value = trim((string) get_field('about_team_description', $page_id));
    if ('' !== $team_description_value) {
        $team_description = $team_description_value;
    }

    $team_members_value = get_field('about_selected_team_members', $page_id);
    if (!empty($team_members_value)) {
        if (!is_array($team_members_value)) {
            $team_members_value = array($team_members_value);
        }
        $clean_team_members = array();
        foreach ($team_members_value as $member_id) {
            $member_id = is_object($member_id) ? (int) $member_id->ID : (int) $member_id;
            if ($member_id <= 0) {
                continue;
            }

            $name = trim((string) get_the_title($member_id));
            $role = function_exists('get_field') ? trim((string) get_field('team_member_designation', $member_id)) : '';
            if ('' === $role) {
                $role = function_exists('get_field') ? trim((string) get_field('team_member_department', $member_id)) : '';
            }
            $description = function_exists('get_field') ? trim((string) get_field('team_member_description', $member_id)) : '';
            $image_url = '';
            if (function_exists('trimvia_acf_image_url')) {
                $image_url = trimvia_acf_image_url(function_exists('get_field') ? get_field('team_member_image', $member_id) : '', 'large');
            }
            if ('' === $image_url) {
                $thumb_url = get_the_post_thumbnail_url($member_id, 'large');
                if (is_string($thumb_url) && '' !== $thumb_url) {
                    $image_url = $thumb_url;
                }
            }
            if ('' === $name && '' === $role && '' === $description && '' === $image_url) {
                continue;
            }
            $clean_team_members[] = array(
                'name' => $name,
                'role' => $role,
                'description' => $description,
                'image_url' => $image_url,
            );
        }
        if (!empty($clean_team_members)) {
            $team_members = $clean_team_members;
        }
    }

    $regulatory_visibility_value = get_field('about_regulatory_visibility', $page_id);
    if (null !== $regulatory_visibility_value && '' !== $regulatory_visibility_value) {
        $show_regulatory_section = (bool) $regulatory_visibility_value;
    }

    $regulatory_tag_value = trim((string) get_field('about_regulatory_tag', $page_id));
    if ('' !== $regulatory_tag_value) {
        $regulatory_tag = $regulatory_tag_value;
    }

    $regulatory_title_value = trim((string) get_field('about_regulatory_title', $page_id));
    if ('' !== $regulatory_title_value) {
        $regulatory_title = $regulatory_title_value;
    }

    $regulatory_description_value = trim((string) get_field('about_regulatory_description', $page_id));
    if ('' !== $regulatory_description_value) {
        $regulatory_description = $regulatory_description_value;
    }

    $regulatory_items_value = get_field('about_regulatory_items', $page_id);
    if (is_array($regulatory_items_value) && !empty($regulatory_items_value)) {
        $clean_regulatory_items = array();
        foreach ($regulatory_items_value as $reg_row) {
            $title = isset($reg_row['title']) ? trim((string) $reg_row['title']) : '';
            $description = isset($reg_row['description']) ? trim((string) $reg_row['description']) : '';
            $icon_class = isset($reg_row['icon_class']) ? trim((string) $reg_row['icon_class']) : '';
            if ('' === $title && '' === $description) {
                continue;
            }
            $clean_regulatory_items[] = array('title' => $title, 'description' => $description, 'icon_class' => $icon_class);
        }
        if (!empty($clean_regulatory_items)) {
            $regulatory_items = $clean_regulatory_items;
        }
    }
}

if (empty($team_members)) {
    $team_query = new WP_Query(
        array(
            'post_type' => 'team_member',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        )
    );

    if ($team_query->have_posts()) {
        $fallback_team_members = array();
        foreach ($team_query->posts as $team_post) {
            $member_id = (int) $team_post->ID;
            $name = trim((string) get_the_title($member_id));
            $role = function_exists('get_field') ? trim((string) get_field('team_member_designation', $member_id)) : '';
            if ('' === $role) {
                $role = function_exists('get_field') ? trim((string) get_field('team_member_department', $member_id)) : '';
            }
            $description = function_exists('get_field') ? trim((string) get_field('team_member_description', $member_id)) : '';
            $image_url = '';
            if (function_exists('trimvia_acf_image_url')) {
                $image_url = trimvia_acf_image_url(function_exists('get_field') ? get_field('team_member_image', $member_id) : '', 'large');
            }
            if ('' === $image_url) {
                $thumb_url = get_the_post_thumbnail_url($member_id, 'large');
                if (is_string($thumb_url) && '' !== $thumb_url) {
                    $image_url = $thumb_url;
                }
            }
            $fallback_team_members[] = array(
                'name' => $name,
                'role' => $role,
                'description' => $description,
                'image_url' => $image_url,
            );
        }
        $team_members = $fallback_team_members;
    }
    wp_reset_postdata();
}

$value_icons = array(
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
);

$regulatory_icons = array(
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
);
?>
<section class="page-hero page-hero--about">
  <div class="hero-noise"></div>
  <div class="container">
    <div class="breadcrumb breadcrumb--about"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> <span>&rsaquo;</span> <span><?php echo esc_html($hero_title); ?></span></div>
    <h1><?php echo esc_html($hero_title); ?></h1>
    <p><?php echo wp_kses_post($hero_description); ?></p>
  </div>
</section>

<?php if ($show_stats_section) : ?>
<section class="stat-bar">
  <div class="container">
    <?php foreach ($stats_items as $stats_index => $stats_item) : ?>
      <div class="stat-item rv <?php echo esc_attr(0 === $stats_index ? '' : 'rv-d' . min($stats_index, 3)); ?>">
        <h3><?php echo esc_html($stats_item['value']); ?></h3>
        <p><?php echo esc_html($stats_item['label']); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($show_mission_section) : ?>
<section class="page-section">
  <div class="container">
    <div class="mission-grid">
      <div class="rv">
        <div class="stag"><?php echo esc_html($mission_tag); ?></div>
        <h2 class="stitle"><?php echo esc_html($mission_title); ?></h2>
        <p class="sdesc" style="max-width:none;"><?php echo wp_kses_post($mission_description_one); ?></p>
        <p class="sdesc" style="max-width:none;margin-top:16px;"><?php echo wp_kses_post($mission_description_two); ?></p>
      </div>
      <div class="mission-img rv rv-d2">
        <?php if ('' !== $mission_image_url) : ?>
          <img src="<?php echo esc_url($mission_image_url); ?>" alt="<?php echo esc_attr($mission_title); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:var(--rxl);min-height:400px;">
        <?php else : ?>
          <div class="media-zone" style="width:100%;height:100%;border-radius:var(--rxl);min-height:400px;">
            <div class="media-zone-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
            <div class="media-zone-label">About Image</div>
            <div class="media-zone-hint">e.g. pharmacy team, clinic interior, or branded lifestyle shot</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_values_section) : ?>
<section class="page-section page-section--alt">
  <div class="container">
    <div style="text-align:center;margin-bottom:56px;" class="rv">
      <div class="stag" style="justify-content:center;"><?php echo esc_html($values_tag); ?></div>
      <h2 class="stitle"><?php echo esc_html($values_title); ?></h2>
    </div>
    <div class="values-grid">
      <?php foreach ($values_items as $value_index => $value_item) : ?>
        <?php
        $delay_class = 0 === ($value_index % 3) ? '' : 'rv-d' . ($value_index % 3);
        $icon_svg = $value_icons[$value_index % count($value_icons)];
        $icon_class_raw = isset($value_item['icon_class']) ? (string) $value_item['icon_class'] : '';
        $icon_class = function_exists('trimvia_sanitize_icon_class') ? trimvia_sanitize_icon_class($icon_class_raw) : trim($icon_class_raw);
        ?>
        <div class="value-card rv <?php echo esc_attr($delay_class); ?>">
          <div class="value-icon">
            <?php if ('' !== $icon_class) : ?>
              <i class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></i>
            <?php else : ?>
              <?php echo $icon_svg; ?>
            <?php endif; ?>
          </div>
          <h3><?php echo esc_html($value_item['title']); ?></h3>
          <p><?php echo esc_html($value_item['description']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_team_section) : ?>
<section class="page-section about-team-section">
  <div class="container">
    <div class="about-team-intro rv">
      <div class="stag" style="justify-content:center;"><?php echo esc_html($team_tag); ?></div>
      <h2 class="stitle"><?php echo wp_kses_post($team_title); ?></h2>
      <p class="sdesc"><?php echo esc_html($team_description); ?></p>
    </div>
    <div class="about-team-grid">
      <?php foreach ($team_members as $member_index => $member) : ?>
        <?php $member_delay_class = 0 === $member_index ? '' : 'rv-d' . min($member_index, 3); ?>
        <div class="treatment-card about-team-card rv <?php echo esc_attr($member_delay_class); ?>">
          <div class="treatment-card-img" style="height:260px;background:linear-gradient(160deg, var(--blue), var(--blue-dark));">
            <?php if (!empty($member['image_url'])) : ?>
              <img src="<?php echo esc_url($member['image_url']); ?>" alt="<?php echo esc_attr($member['name']); ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php else : ?>
              <div class="media-zone media-zone--dark" style="width:100%;height:100%;border-radius:0;border:none;min-height:auto;">
                <div class="media-zone-icon" style="background:rgba(255,255,255,0.15);box-shadow:none;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                <div class="media-zone-label" style="color:rgba(255,255,255,0.8);">Team Photo</div>
              </div>
            <?php endif; ?>
          </div>
          <div class="treatment-card-body" style="text-align:center;">
            <h3><?php echo esc_html($member['name']); ?></h3>
            <p class="about-team-role"><?php echo esc_html($member['role']); ?></p>
            <p class="about-team-description"><?php echo esc_html($member['description']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_regulatory_section) : ?>
<section class="page-section page-section--alt">
  <div class="container">
    <div style="text-align:center;margin-bottom:56px;" class="rv">
      <div class="stag" style="justify-content:center;"><?php echo esc_html($regulatory_tag); ?></div>
      <h2 class="stitle"><?php echo wp_kses_post($regulatory_title); ?></h2>
      <p class="sdesc" style="margin:0 auto;"><?php echo esc_html($regulatory_description); ?></p>
    </div>
    <div class="about-regulatory-grid">
      <?php foreach ($regulatory_items as $reg_index => $reg_item) : ?>
        <?php
        $reg_delay_class = 0 === $reg_index ? '' : 'rv-d' . min($reg_index, 3);
        $reg_icon = $regulatory_icons[$reg_index % count($regulatory_icons)];
        $reg_icon_class_raw = isset($reg_item['icon_class']) ? (string) $reg_item['icon_class'] : '';
        $reg_icon_class = function_exists('trimvia_sanitize_icon_class') ? trimvia_sanitize_icon_class($reg_icon_class_raw) : trim($reg_icon_class_raw);
        ?>
        <div class="sidebar-card about-regulatory-card rv <?php echo esc_attr($reg_delay_class); ?>">
          <div class="value-icon">
            <?php if ('' !== $reg_icon_class) : ?>
              <i class="<?php echo esc_attr($reg_icon_class); ?>" aria-hidden="true"></i>
            <?php else : ?>
              <?php echo $reg_icon; ?>
            <?php endif; ?>
          </div>
          <h4><?php echo esc_html($reg_item['title']); ?></h4>
          <p><?php echo esc_html($reg_item['description']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

