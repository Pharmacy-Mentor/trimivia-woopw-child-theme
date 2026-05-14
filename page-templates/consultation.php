<?php
/**
 * Template Name: Consultation
 * Template Post Type: page
 *
 * Child override: same WooPW / ACF behaviour as parent, Trimvia layout, hardening.
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$is_under_process    = false;
$recommend_enabled   = class_exists('WOOPW_ADDON_MANAGER') ? WOOPW_ADDON_MANAGER::enable_product_recommend() : false;
$slug                = isset($_GET['condition-slug']) && $_GET['condition-slug'] ? sanitize_text_field(wp_unslash($_GET['condition-slug'])) : '';
$condition_slug      = $slug;
$questionnaire_id    = '';
$term                = null;

if ($condition_slug) {
	$found = get_term_by('slug', $condition_slug, 'condition');
	if ($found && !is_wp_error($found)) {
		$term = $found;
		$previous_conditions = function_exists('woopw_check_orders_previous_conditions') ? woopw_check_orders_previous_conditions() : array();

		if (is_array($previous_conditions) && in_array((int) $term->term_id, array_map('intval', $previous_conditions), true)) {
			$questionnaire_id = (function_exists('get_field') ? get_field('reorder_questionnaire', $term) : '');
			if (!$questionnaire_id) {
				$questionnaire_id = get_option('default_reorder_questionnaire');
			}
			if (!$questionnaire_id && function_exists('get_field')) {
				$questionnaire_id = get_field('questionnaire', $term);
			}
		} elseif (function_exists('get_field')) {
			$questionnaire_id = get_field('questionnaire', $term);
		}
	}
}

$old_consultation_order_complete = false;
$previous_completed_order_id     = 0;

if (is_user_logged_in() && $term instanceof WP_Term) {
	$user = wp_get_current_user();

	if (function_exists('get_user_latest_pending_consultation_order')) {
		$consultation_order_complete = get_user_latest_pending_consultation_order($user, $term->term_id);
		if ($consultation_order_complete) {
			$is_under_process = true;
		}
	}

	if (function_exists('get_user_latest_completed_consultation_order')) {
		$old_consultation_order_complete = get_user_latest_completed_consultation_order($user, $term->term_id, false);
	}
	if ($old_consultation_order_complete && function_exists('woopw_find_previous_order_for_condition')) {
		$previous_completed_order_id = absint(woopw_find_previous_order_for_condition((int) $term->term_id, (int) $user->ID));
	}
}

$recommend_error = isset($_GET['recommend_error']) && (string) $_GET['recommend_error'] === '1';

$show_questionnaire_sidebar = ! $is_under_process
	&& $term instanceof WP_Term
	&& ! empty($questionnaire_id );

if (have_posts()) {
	while (have_posts()) {
		the_post();

		$consult_hero_sub = '';
		if ($term instanceof WP_Term) {
			$t_desc_raw = term_description($term->term_id, 'condition');
			if ($t_desc_raw) {
				$consult_hero_sub = wp_trim_words(wp_strip_all_tags($t_desc_raw), 40, '…');
			}
		}
		if ($consult_hero_sub === '') {
			$consult_hero_sub = __(
				'Complete this short assessment so our prescribers can determine if treatment is right for you.',
				'woopw'
			);
		}

		$trimvia_contact_url   = home_url('/contact-us/');
		$trimvia_contact_phone = '';
		$contact_page          = get_page_by_path('contact-us');
		if ($contact_page instanceof WP_Post) {
			$trimvia_contact_url = get_permalink($contact_page);
			if (function_exists('get_field')) {
				$trimvia_contact_phone = trim((string) get_field('contact_urgent_phone', $contact_page->ID));
			}
		}
		$trimvia_condition_cancel_url = ( $term instanceof WP_Term && $condition_slug )
			? home_url('/condition/' . rawurlencode($condition_slug) . '/')
			: home_url('/');
		?>
		<section class="page-hero page-hero--consultation">
			<div class="hero-noise" aria-hidden="true"></div>
			<div class="container">
				<nav class="breadcrumb breadcrumb--consultation" aria-label="<?php esc_attr_e('Breadcrumb', 'woocommerce'); ?>">
					<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'woopw'); ?></a>
					<span class="breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
					<span class="breadcrumb-current"><?php the_title(); ?></span>
				</nav>
				<?php if ($questionnaire_id && $term instanceof WP_Term) : ?>
					<h1>
						<?php
						printf(
							/* translators: %s: condition name */
							esc_html__('Assessment for %s', 'woopw'),
							esc_html($term->name)
						);
						?>
					</h1>
				<?php else : ?>
					<h1><?php the_title(); ?></h1>
				<?php endif; ?>
				<p><?php echo esc_html($consult_hero_sub); ?></p>
				<div class="consult-banner__chip consult-hero-meta" role="note">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
					<?php esc_html_e('Takes approximately 2 minutes', 'woopw'); ?>
				</div>
			</div>
		</section>

		<?php if (!empty(get_the_content())) { ?>
			<section class="section-padding trimvia-consult-intro">
				<div class="container">
					<div class="row">
						<div class="col-12 trimvia-consult-intro__inner">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</section>
		<?php } ?>

		<?php if ($recommend_enabled && !$is_under_process && $old_consultation_order_complete && $condition_slug) { ?>
			<section id="recommend-notification" class="consult-layout trimvia-consult-reorder-wrap">
				<div class="trimvia-consult-woo-outer">
					<div class="trimvia-consult-woo-card trimvia-consult-woo-card--single">
						<div class="trimvia-consult-reorder">
							<h3><?php esc_html_e('Welcome back!', 'woopw'); ?></h3>
							<p>
								<?php
								esc_html_e(
									'You’ve previously completed a consultation with us. Using your earlier information, we can quickly show you personalized product recommendations.',
									'woopw'
								);
								?>
							</p>
							<p>
								<?php
								esc_html_e(
									'Would you like to continue using your previous details, or start a fresh consultation?',
									'woopw'
								);
								?>
							</p>
							<div class="consultation-actions">
								<?php
								$cache         = function_exists('uniqueIdReal') ? uniqueIdReal(16) : wp_generate_password(12, false);
								$redirect_args = array(
									'nocache'    => $cache,
									'is_reorder' => 1,
								);
								if (!empty($previous_completed_order_id)) {
									$redirect_args['order_id'] = (int) $previous_completed_order_id;
								}
								$redirect_url = add_query_arg($redirect_args, site_url('/condition/' . rawurlencode($condition_slug)));
								?>
								<a href="<?php echo esc_url($redirect_url); ?>" class="btn-next">
									<?php esc_html_e('Continue with previous consultation', 'woopw'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php } ?>

		<?php if ($recommend_error) { ?>
			<section class="consult-layout trimvia-consult-error-wrap">
				<div class="trimvia-consult-woo-outer">
					<div class="trimvia-consult-woo-card trimvia-consult-woo-card--single">
						<div class="trimvia-consult-error" role="alert">
							<h2 class="trimvia-consult-error__title"><?php esc_html_e('This treatment is no longer available.', 'woopw'); ?></h2>
							<p class="trimvia-consult-error__text">
								<?php esc_html_e('This product does not match your selected treatment. Please re-take the consultation to order this product.', 'woopw'); ?>
							</p>
						</div>
					</div>
				</div>
			</section>
		<?php } ?>

		<section id="consultationform" class="consult-layout trimvia-consult-form-shell">
			<div class="trimvia-consult-woo-outer">
				<div class="trimvia-consult-woo-card<?php echo $show_questionnaire_sidebar ? '' : ' trimvia-consult-woo-card--single'; ?>">
					<div class="trimvia-consult-woo-card__grid">
						<div class="trimvia-consult-woo-main">
							<?php if ($show_questionnaire_sidebar && $term instanceof WP_Term) : ?>
								<div class="trimvia-consult-condition-intro">
									<h2 class="trimvia-consult-condition-intro__title"><?php echo esc_html($term->name); ?></h2>
									<div class="trimvia-consult-condition-intro__text">
										<?php
										$t_desc = term_description($term->term_id, 'condition');
										if ($t_desc) {
											echo apply_filters('the_content', $t_desc); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										} else {
											echo '<p>' . esc_html__(
												'Please fill out the form below so that our clinicians can determine if the treatment will be suitable for you to take.',
												'woocommerce'
											) . '</p>';
										}
										?>
									</div>
								</div>
							<?php endif; ?>

							<div class="consult-form trimvia-consult-woo-form consult-form--trimvia-shell">
								<?php if ($show_questionnaire_sidebar) : ?>
									<div class="trimvia-consult-step-chrome">
										<div class="step-indicator trimvia-consult-step-indicator" id="trimvia-consult-step-indicator" role="list" aria-label="<?php esc_attr_e('Assessment steps', 'woopw'); ?>"></div>
										<div class="progress-bar-wrap trimvia-consult-progress-bar">
											<div class="progress-bar-info">
												<span class="progress-bar-label">
													<?php esc_html_e('Step', 'woopw'); ?>
													<span id="trimvia-consult-current-step">1</span>
													<span id="trimvia-consult-step-of" class="trimvia-consult-step-of"><?php esc_html_e('of', 'woopw'); ?></span>
													<span id="trimvia-consult-step-total">3</span>
												</span>
												<span class="progress-bar-pct" id="trimvia-consult-progress-pct">33%</span>
											</div>
											<div class="progress-bar-track">
												<div class="progress-bar-fill" id="trimvia-consult-progress-fill" style="width:33%"></div>
											</div>
										</div>
									</div>
								<?php endif; ?>
								<?php
								if ($is_under_process) {
									$msg = function_exists('get_field') ? get_field('consultation_under_process', get_the_ID()) : '';
									if ($msg) {
										echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ACF HTML field
									}
								} elseif ($term instanceof WP_Term) {
									if ($questionnaire_id) {
										echo do_shortcode($questionnaire_id);
									}
								} else {
									echo '<p class="trimvia-consult-missing-condition">' . esc_html__(
										'No treatment condition was specified. Please start from the treatments or conditions page.',
										'woocommerce'
									) . '</p>';
								}
								?>
							</div>
						</div>

						<?php if ($show_questionnaire_sidebar) : ?>
							<aside class="consult-sidebar trimvia-consult-woo-sidebar" id="trimvia-assessment-progress" aria-label="<?php esc_attr_e('Assessment progress', 'woocommerce'); ?>">
								<div class="sidebar-card sidebar-card--progress">
									<h4><?php esc_html_e('Assessment progress', 'woocommerce'); ?></h4>
									<div class="progress-list" id="trimvia-consult-progress-list">
										<div class="progress-step active">
											<span class="progress-step-num" aria-hidden="true">1</span>
											<span class="progress-step-label"><?php esc_html_e('About your health', 'woocommerce'); ?></span>
										</div>
										<div class="progress-step">
											<span class="progress-step-num" aria-hidden="true">2</span>
											<span class="progress-step-label"><?php esc_html_e('About your condition', 'woocommerce'); ?></span>
										</div>
										<div class="progress-step">
											<span class="progress-step-num" aria-hidden="true">3</span>
											<span class="progress-step-label"><?php esc_html_e('Agreement and consent', 'woocommerce'); ?></span>
										</div>
									</div>
								</div>
								<div class="sidebar-card">
									<div class="sidebar-help">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
										<div>
											<strong><?php esc_html_e('Need a helping hand?', 'woopw'); ?></strong><br>
											<?php if ($trimvia_contact_phone !== '') : ?>
												<?php
												$trimvia_phone_href = preg_replace('/[^0-9+]/', '', $trimvia_contact_phone);
												?>
												<?php esc_html_e('Give us a call on', 'woopw'); ?>
												<strong><a href="tel:<?php echo esc_attr($trimvia_phone_href); ?>"><?php echo esc_html($trimvia_contact_phone); ?></a></strong>
												<?php esc_html_e(' or ', 'woopw'); ?>
											<?php endif; ?>
											<a href="<?php echo esc_url($trimvia_contact_url); ?>"><?php esc_html_e('Contact us', 'woopw'); ?></a>
										</div>
									</div>
								</div>
								<div class="sidebar-card">
									<div class="sidebar-trust">
										<div class="sidebar-trust-item">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
											<?php esc_html_e('GPhC-registered pharmacy', 'woopw'); ?>
										</div>
										<div class="sidebar-trust-item">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
											<?php esc_html_e('UK prescribers review every order', 'woopw'); ?>
										</div>
										<div class="sidebar-trust-item">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
											<?php esc_html_e('Confidential & discreet', 'woopw'); ?>
										</div>
										<div class="sidebar-trust-item">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
											<?php esc_html_e('Next-day delivery available', 'woopw'); ?>
										</div>
									</div>
								</div>
								<p class="trimvia-consult-cancel-wrap">
									<a href="<?php echo esc_url($trimvia_condition_cancel_url); ?>" class="trimvia-consult-cancel-link"><?php esc_html_e('Cancel assessment', 'woopw'); ?></a>
								</p>
							</aside>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}

get_footer();
