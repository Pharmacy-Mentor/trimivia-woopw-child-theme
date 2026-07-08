<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id = get_the_ID();

$hero_title = get_the_title($page_id);
$hero_description = 'Have a question about our treatments or need help with an order? Our team is here to help.';
$form_title = 'Send Us a Message';
$form_description = 'Fill in the form below and we\'ll get back to you within 24 hours.';
$contact_form_shortcode = '';
$form_heading_html = '';
$show_contact_form = true;
$show_map_section = true;
$show_urgent_help_section = true;
$show_contact_details_section = true;
$show_opening_hours_section = true;
$map_embed = '';
$map_title = 'Find Us';
$map_address = "Mayberry Pharmacy\nLorem ipsum street\nCity, Postcode";
$urgent_title = 'Need Urgent Help?';
$urgent_description = 'If you need immediate assistance with a treatment or order, call us directly.';
$urgent_phone = '01234 567890';
$contact_details_title = 'Contact Details';
$contact_email_label = 'Email';
$contact_email = 'info@trimvia.co.uk';
$contact_address_label = 'Address';
$contact_address = "Mayberry Pharmacy\nLorem ipsum street\nCity, Postcode";
$opening_hours_title = 'Opening Hours';
$opening_hours = array(
    array(
        'day' => 'Monday - Friday',
        'time' => '9:00 - 18:00',
    ),
    array(
        'day' => 'Saturday',
        'time' => '9:00 - 13:00',
    ),
    array(
        'day' => 'Sunday',
        'time' => 'Closed',
    ),
);

if (function_exists('get_field') && $page_id) {
    $show_contact_form_value = get_field('contact_form_visibility', $page_id);
    if (null !== $show_contact_form_value && '' !== $show_contact_form_value) {
        $show_contact_form = (bool) $show_contact_form_value;
    }

    $show_map_section_value = get_field('contact_map_visibility', $page_id);
    if (null !== $show_map_section_value && '' !== $show_map_section_value) {
        $show_map_section = (bool) $show_map_section_value;
    }

    $show_urgent_help_section_value = get_field('contact_urgent_help_visibility', $page_id);
    if (null !== $show_urgent_help_section_value && '' !== $show_urgent_help_section_value) {
        $show_urgent_help_section = (bool) $show_urgent_help_section_value;
    }

    $show_contact_details_section_value = get_field('contact_details_visibility', $page_id);
    if (null !== $show_contact_details_section_value && '' !== $show_contact_details_section_value) {
        $show_contact_details_section = (bool) $show_contact_details_section_value;
    }

    $show_opening_hours_section_value = get_field('contact_opening_hours_visibility', $page_id);
    if (null !== $show_opening_hours_section_value && '' !== $show_opening_hours_section_value) {
        $show_opening_hours_section = (bool) $show_opening_hours_section_value;
    }

    $hero_title_value = trim((string) get_field('contact_hero_title', $page_id));
    if ('' !== $hero_title_value) {
        $hero_title = $hero_title_value;
    }

    $hero_description_value = trim((string) get_field('contact_hero_description', $page_id));
    if ('' !== $hero_description_value) {
        $hero_description = $hero_description_value;
    }

    $form_title_value = trim((string) get_field('contact_form_title', $page_id));
    if ('' !== $form_title_value) {
        $form_title = $form_title_value;
    }

    $form_description_value = trim((string) get_field('contact_form_description', $page_id));
    if ('' !== $form_description_value) {
        $form_description = $form_description_value;
    }

    $contact_form_shortcode_value = trim((string) get_field('contact_form_shortcode', $page_id));
    if ('' !== $contact_form_shortcode_value) {
        $contact_form_shortcode = $contact_form_shortcode_value;
    }

    $contact_form_shortcode_alt_value = trim((string) get_field('contact_form_7_shortcode', $page_id));
    if ('' === $contact_form_shortcode && '' !== $contact_form_shortcode_alt_value) {
        $contact_form_shortcode = $contact_form_shortcode_alt_value;
    }

    $form_heading_html_value = trim((string) get_field('contact_detail_heading', $page_id));
    if ('' !== $form_heading_html_value) {
        $form_heading_html = $form_heading_html_value;
    }

    $map_embed_value = trim((string) get_field('contact_map_embed', $page_id));
    if ('' !== $map_embed_value) {
        $map_embed = $map_embed_value;
    }

    $map_embed_alt_value = trim((string) get_field('map', $page_id));
    if ('' === $map_embed && '' !== $map_embed_alt_value) {
        $map_embed = $map_embed_alt_value;
    }

    $map_title_value = trim((string) get_field('contact_map_title', $page_id));
    if ('' !== $map_title_value) {
        $map_title = $map_title_value;
    }

    $map_address_value = trim((string) get_field('contact_map_address', $page_id));
    if ('' !== $map_address_value) {
        $map_address = $map_address_value;
    }

    $urgent_title_value = trim((string) get_field('contact_urgent_title', $page_id));
    if ('' !== $urgent_title_value) {
        $urgent_title = $urgent_title_value;
    }

    $urgent_description_value = trim((string) get_field('contact_urgent_description', $page_id));
    if ('' !== $urgent_description_value) {
        $urgent_description = $urgent_description_value;
    }

    $urgent_phone_value = trim((string) get_field('contact_urgent_phone', $page_id));
    if ('' !== $urgent_phone_value) {
        $urgent_phone = $urgent_phone_value;
    }

    $contact_details_title_value = trim((string) get_field('contact_details_title', $page_id));
    if ('' !== $contact_details_title_value) {
        $contact_details_title = $contact_details_title_value;
    }

    $contact_email_label_value = trim((string) get_field('contact_email_label', $page_id));
    if ('' !== $contact_email_label_value) {
        $contact_email_label = $contact_email_label_value;
    }

    $contact_email_value = trim((string) get_field('contact_email', $page_id));
    if ('' !== $contact_email_value) {
        $contact_email = function_exists('trimvia_resolve_contact_email')
            ? trimvia_resolve_contact_email($contact_email_value)
            : sanitize_email($contact_email_value);
    }

    $contact_address_label_value = trim((string) get_field('contact_address_label', $page_id));
    if ('' !== $contact_address_label_value) {
        $contact_address_label = $contact_address_label_value;
    }

    $contact_address_value = trim((string) get_field('contact_address', $page_id));
    if ('' !== $contact_address_value) {
        $contact_address = $contact_address_value;
    }

    $opening_hours_title_value = trim((string) get_field('contact_opening_hours_title', $page_id));
    if ('' !== $opening_hours_title_value) {
        $opening_hours_title = $opening_hours_title_value;
    }

    $opening_hours_value = get_field('contact_opening_hours', $page_id);
    if (is_array($opening_hours_value) && !empty($opening_hours_value)) {
        $clean_opening_hours = array();

        foreach ($opening_hours_value as $opening_hours_row) {
            $day = isset($opening_hours_row['day']) ? trim((string) $opening_hours_row['day']) : '';
            $time = isset($opening_hours_row['time']) ? trim((string) $opening_hours_row['time']) : '';

            if ('' === $day && '' === $time) {
                continue;
            }

            $clean_opening_hours[] = array(
                'day' => $day,
                'time' => $time,
            );
        }

        if (!empty($clean_opening_hours)) {
            $opening_hours = $clean_opening_hours;
        }
    }
}

$phone_href = preg_replace('/[^0-9\+]/', '', $urgent_phone);
$allowed_map_html = array(
    'iframe' => array(
        'src' => true,
        'width' => true,
        'height' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'name' => true,
        'allowfullscreen' => true,
        'allow' => true,
        'loading' => true,
        'referrerpolicy' => true,
        'title' => true,
        'frameborder' => true,
        'marginheight' => true,
        'marginwidth' => true,
        'scrolling' => true,
        'aria-hidden' => true,
        'tabindex' => true,
    ),
    'div' => array(
        'class' => true,
        'style' => true,
    ),
);
?>
<section class="page-hero page-hero--contact">
  <div class="hero-noise"></div>
  <div class="container">
    <div class="breadcrumb breadcrumb--contact"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> <span>&rsaquo;</span> <span><?php echo esc_html($hero_title); ?></span></div>
    <h1><?php echo esc_html($hero_title); ?></h1>
    <p><?php echo wp_kses_post($hero_description); ?></p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="content-grid">
      <?php if ($show_contact_form) : ?>
        <div>
          <?php if ('' !== $form_heading_html) : ?>
            <div class="sdesc" style="margin-bottom:32px;"><?php echo wp_kses_post($form_heading_html); ?></div>
          <?php else : ?>
            <h2 class="stitle" style="font-size:28px;margin-bottom:8px;"><?php echo esc_html($form_title); ?></h2>
            <p class="sdesc" style="margin-bottom:32px;"><?php echo esc_html($form_description); ?></p>
          <?php endif; ?>
          <div class="trimvia-contact-form-wrap">
            <?php if ('' !== $contact_form_shortcode) : ?>
              <?php echo do_shortcode($contact_form_shortcode); ?>
            <?php else : ?>
              <p><?php echo esc_html__('Add the Contact Form 7 shortcode in ACF field "Contact Form Shortcode".', 'theme-woopm-child'); ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="sidebar">
        <?php if ($show_map_section) : ?>
          <div class="sidebar-card" style="padding:0;overflow:hidden;">
            <?php if ('' !== $map_embed) : ?>
              <div class="trimvia-map-embed"><?php echo wp_kses($map_embed, $allowed_map_html); ?></div>
            <?php else : ?>
              <div class="media-zone" style="min-height:220px;border-radius:0;border:none;border-bottom:2.5px dashed var(--blue);">
                <div class="media-zone-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                <div class="media-zone-label"><?php echo esc_html__('Map Embed', 'theme-woopm-child'); ?></div>
                <div class="media-zone-hint"><?php echo esc_html__('Google Maps iframe or static map image', 'theme-woopm-child'); ?></div>
              </div>
            <?php endif; ?>
            <div style="padding:20px 28px;">
              <h4 style="font-family:var(--fd);font-size:16px;color:var(--g900);margin-bottom:4px;"><?php echo esc_html($map_title); ?></h4>
              <p style="font-size:13px;color:var(--g500);"><?php echo wp_kses_post(nl2br(esc_html($map_address))); ?></p>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($show_urgent_help_section) : ?>
          <div class="sidebar-card--blue sidebar-card">
            <h4><?php echo esc_html($urgent_title); ?></h4>
            <p><?php echo esc_html($urgent_description); ?></p>
            <?php if ('' !== $phone_href) : ?>
              <div style="margin-top:16px;font-size:22px;font-weight:700;"><a href="tel:<?php echo esc_attr($phone_href); ?>" style="color:#fff;"><?php echo esc_html($urgent_phone); ?></a></div>
            <?php else : ?>
              <div style="margin-top:16px;font-size:22px;font-weight:700;"><?php echo esc_html($urgent_phone); ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if ($show_contact_details_section) : ?>
          <div class="sidebar-card">
            <h4><?php echo esc_html($contact_details_title); ?></h4>
            <div class="contact-item">
              <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg></div>
              <div>
                <h5><?php echo esc_html($contact_email_label); ?></h5>
                <p><a href="mailto:<?php echo esc_attr(antispambot($contact_email)); ?>"><?php echo esc_html(antispambot($contact_email)); ?></a></p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
              <div>
                <h5><?php echo esc_html($contact_address_label); ?></h5>
                <p><?php echo wp_kses_post(nl2br(esc_html($contact_address))); ?></p>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($show_opening_hours_section) : ?>
          <div class="sidebar-card">
            <h4><?php echo esc_html($opening_hours_title); ?></h4>
            <div style="font-size:14px;color:var(--g500);line-height:2;">
              <?php foreach ($opening_hours as $opening_row) : ?>
                <?php
                $day_label = isset($opening_row['day']) ? trim((string) $opening_row['day']) : '';
                $time_label = isset($opening_row['time']) ? trim((string) $opening_row['time']) : '';

                if ('' === $day_label && '' === $time_label) {
                    continue;
                }
                ?>
                <div style="display:flex;justify-content:space-between;gap:20px;">
                  <span><?php echo esc_html($day_label); ?></span>
                  <strong style="color:var(--g800);text-align:right;"><?php echo esc_html($time_label); ?></strong>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

