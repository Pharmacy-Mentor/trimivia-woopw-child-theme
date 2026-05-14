<?php
/**
 * Template Name: All Conditions
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$title_before = get_field('all_cond_hero_title_before');
if (!is_string($title_before) || $title_before === '') {
	$title_before = __('Our', 'woocommerce');
}
$title_accent = get_field('all_cond_hero_title_accent');
if (!is_string($title_accent) || $title_accent === '') {
	$title_accent = __('Conditions', 'woocommerce');
}
$hero_description = get_field('all_cond_hero_description');
$search_placeholder = get_field('all_cond_search_placeholder');
if (!is_string($search_placeholder) || $search_placeholder === '') {
	$search_placeholder = __('Search conditions...', 'woocommerce');
}
$badge_rows       = get_field('all_cond_hero_badges');
$show_count_badge = (bool) get_field('all_cond_show_count_badge');
$count_suffix     = get_field('all_cond_count_badge_suffix');
if (!is_string($count_suffix) || $count_suffix === '') {
	$count_suffix = __('conditions with treatments', 'woocommerce');
}
$show_banner_form = (bool) get_field('all_cond_show_banner_form');

$conditions = get_terms(
	array(
		'taxonomy'   => 'condition',
		'hide_empty' => false,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

$condition_cards = array();
$visible_count   = 0;

if (!is_wp_error($conditions) && !empty($conditions)) {
	foreach ($conditions as $term) {
		$products = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'condition',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);
		if (empty($products)) {
			continue;
		}
		$visible_count++;
		$condition_cards[] = $term;
	}
}

if (!is_string($hero_description) || $hero_description === '') {
	$hero_description = __(
		'Browse clinically proven treatments by condition. Every prescription is reviewed and approved by a UK-registered pharmacist prescriber.',
		'woocommerce'
	);
}
?>
<section class="trimvia-conditions-hero trimvia-conditions-hero--all section-pad">
	<div class="container">
		<div class="trimvia-conditions-hero__inner text-center">
			<h1 class="trimvia-conditions-hero__title">
				<span class="trimvia-conditions-hero__title-plain"><?php echo esc_html($title_before); ?></span>
				<span class="trimvia-conditions-hero__title-accent"><?php echo esc_html($title_accent); ?></span>
			</h1>
			<p class="trimvia-conditions-hero__desc"><?php echo wp_kses_post(nl2br($hero_description)); ?></p>

			<div class="trimvia-conditions-hero__search-wrap search-container">
				<label class="screen-reader-text" for="trimvia-all-conditions-filter"><?php esc_html_e('Filter conditions', 'woocommerce'); ?></label>
				<div class="trimvia-conditions-hero__search">
					<i class="fa-solid fa-magnifying-glass trimvia-conditions-hero__search-icon" aria-hidden="true"></i>
					<input
						id="trimvia-all-conditions-filter"
						type="search"
						class="trimvia-conditions-hero__search-input"
						placeholder="<?php echo esc_attr($search_placeholder); ?>"
						autocomplete="off"
					/>
				</div>
			</div>

			<?php if ($show_count_badge || ($badge_rows && is_array($badge_rows))) : ?>
				<ul class="trimvia-conditions-hero__badges" aria-label="<?php esc_attr_e('Highlights', 'woocommerce'); ?>">
					<?php if ($show_count_badge) : ?>
						<li class="trimvia-conditions-hero__badge">
							<?php
							printf(
								/* translators: 1: number of terms, 2: suffix phrase */
								esc_html(_x('%1$d %2$s', 'conditions count badge', 'woocommerce')),
								(int) $visible_count,
								esc_html($count_suffix)
							);
							?>
						</li>
					<?php endif; ?>
					<?php
					if ($badge_rows && is_array($badge_rows)) :
						foreach ($badge_rows as $row) {
							if (empty($row['text'])) {
								continue;
							}
							echo '<li class="trimvia-conditions-hero__badge">';
							echo wp_kses_post($row['text']);
							echo '</li>';
						}
					endif;
					?>
				</ul>
			<?php endif; ?>

			<?php if ($show_banner_form) : ?>
				<div class="trimvia-conditions-hero__legacy-form mt-4">
					<?php echo do_shortcode('[BannerForm]'); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if (!empty($condition_cards)) : ?>
	<section class="trimvia-condition-listings section-pad">
		<div class="container">
			<div class="row trimvia-condition-listings__row">
				<?php foreach ($condition_cards as $term) : ?>
					<?php
					$search_blob = strtolower($term->name . ' ' . wp_strip_all_tags(term_description($term->term_id, 'condition')));
					?>
					<div
						class="col-lg-4 col-md-6 col-sm-12 mb-4 trimvia-condition-card-wrap d-flex"
						data-search="<?php echo esc_attr($search_blob); ?>"
					>
						<div class="trimvia-condition-card w-100 d-flex flex-column">
							<div class="trimvia-condition-card__media position-relative">
								<a href="<?php echo esc_url(get_term_link($term)); ?>" class="trimvia-condition-card__media-link"><span class="screen-reader-text"><?php echo esc_html($term->name); ?></span></a>
								<?php
								if (function_exists('get_field') && get_field('featured_image', $term)) {
									echo wp_get_attachment_image(
										get_field('featured_image', $term),
										'medium_large',
										false,
										array(
											'class'    => 'trimvia-condition-card__img',
											'loading'  => 'lazy',
											'decoding' => 'async',
										)
									);
								} else {
									echo function_exists('get_placeholder_image') ? get_placeholder_image(false, 'medium_large') : '';
								}
								?>
							</div>
							<div class="trimvia-condition-card__body d-flex flex-column flex-grow-1">
								<h4 class="trimvia-condition-card__title">
									<a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
								</h4>
								<a class="trimvia-condition-card__btn mt-auto" href="<?php echo esc_url(get_term_link($term)); ?>">
									<?php esc_html_e('View Treatments', 'woocommerce'); ?> <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<script>
	jQuery(function ($) {
		var $cards = $('.trimvia-condition-card-wrap');
		$('#trimvia-all-conditions-filter').on('input', function () {
			var q = $(this).val().toLowerCase().trim();
			if (!q) {
				$cards.show();
				return;
			}
			$cards.each(function () {
				var hay = ($(this).data('search') || '').toString();
				$(this).toggle(hay.indexOf(q) !== -1);
			});
		});
	});
</script>
<?php
get_footer();
