<?php
/**
 * WP 2FA setup page — Trimvia layout (hero + configuration card).
 *
 * @package Theme_WooPW_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
?>

<?php while (have_posts()) : ?>
	<?php the_post(); ?>

	<section class="page-hero trimvia-account-hero trimvia-2fa-hero">
		<div class="hero-noise"></div>
		<div class="container">
			<div class="breadcrumb">
				<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
				<span>&rsaquo;</span>
				<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('My account', 'theme-woopm-child'); ?></a>
				<span>&rsaquo;</span>
				<span><?php the_title(); ?></span>
			</div>
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html(trimvia_get_2fa_page_intro_text()); ?></p>
		</div>
	</section>

	<section class="page-section rv trimvia-2fa-section">
		<div class="container">
			<div class="trimvia-2fa-shell">
				<a class="trimvia-2fa-back-link" href="<?php echo esc_url($account_url); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
					<?php esc_html_e('Back to account', 'theme-woopm-child'); ?>
				</a>

				<div class="trimvia-2fa-card">
					<div class="trimvia-2fa-card-head">
						<h2><?php esc_html_e('Security settings', 'theme-woopm-child'); ?></h2>
						<p><?php esc_html_e('Review your current setup and configure two-factor authentication for this account.', 'theme-woopm-child'); ?></p>
					</div>
					<div class="trimvia-2fa-card-body entry-content">
						<?php the_content(); ?>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php endwhile; ?>
