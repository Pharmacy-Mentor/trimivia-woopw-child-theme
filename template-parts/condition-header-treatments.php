<?php
/**
 * Condition header for post-consultation treatments state.
 *
 * @package theme-woopm-child
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<section class="page-hero page-hero--service trimvia-condition-header-treatments">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--service">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span class="breadcrumb-current"><?php esc_html_e('Treatments', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Choose your preferred treatment', 'woopw'); ?></h1>
		<p><?php esc_html_e('Thanks for completing our consultation. Choose a treatment below and add it to your prescription bag to check out.', 'theme-woopm-child'); ?></p>
	</div>
</section>
