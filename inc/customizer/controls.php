<?php
if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('WP_Customize_Control')) {
	/**
	 * Repeater control for footer social links.
	 */
	class Trimvia_Social_Repeater_Control extends WP_Customize_Control
	{
		public $type = 'trimvia_social_repeater';

		/**
		 * Render control content.
		 */
		public function render_content()
		{
			$icon_choices = trimvia_get_social_icon_choices();
?>
			<div class="trimvia-social-repeater-control">
				<?php if (!empty($this->label)) : ?>
					<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
				<?php endif; ?>
				<?php if (!empty($this->description)) : ?>
					<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
				<?php endif; ?>
				<input type="hidden" class="trimvia-social-raw" <?php $this->link(); ?> value="<?php echo esc_attr((string) $this->value()); ?>">
				<div class="trimvia-social-items"></div>
				<script type="text/template" class="trimvia-social-template">
					<div class="trimvia-social-item">
						<select class="trimvia-social-icon">
							<?php foreach ($icon_choices as $icon_class => $icon_label) : ?>
								<option value="<?php echo esc_attr($icon_class); ?>"><?php echo esc_html($icon_label); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="url" class="trimvia-social-link" placeholder="<?php echo esc_attr__('https://example.com', 'theme-woopm-child'); ?>">
						<input type="text" class="trimvia-social-label" placeholder="<?php echo esc_attr__('Label (optional)', 'theme-woopm-child'); ?>">
						<button type="button" class="button-link button-link-delete trimvia-social-remove"><?php echo esc_html__('Remove', 'theme-woopm-child'); ?></button>
					</div>
				</script>
				<button type="button" class="button button-secondary trimvia-social-add"><?php echo esc_html__('Add New', 'theme-woopm-child'); ?></button>
			</div>
<?php
		}
	}
}

/**
 * Enqueue assets for custom Customizer controls.
 */
function trimvia_enqueue_customizer_control_assets()
{
	if (defined('TRIMVIA_ENABLE_CUSTOMIZER_SOCIAL_REPEATER') && !TRIMVIA_ENABLE_CUSTOMIZER_SOCIAL_REPEATER) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/customizer-social-repeater.js';
	$script_url  = get_stylesheet_directory_uri() . '/assets/js/customizer-social-repeater.js';

	wp_enqueue_script(
		'trimvia-customizer-social-repeater',
		$script_url,
		array('jquery', 'customize-controls'),
		file_exists($script_path) ? filemtime($script_path) : null,
		true
	);

	$customizer_css = '.trimvia-social-item{display:grid;grid-template-columns:1fr;gap:8px;padding:10px;margin-bottom:10px;background:#fff;border:1px solid #dcdcde;border-radius:6px}.trimvia-social-remove{justify-self:start}.trimvia-social-add{margin-top:6px}';
	wp_add_inline_style('customize-controls', $customizer_css);
}
add_action('customize_controls_enqueue_scripts', 'trimvia_enqueue_customizer_control_assets');
/**
 * Ensure WordPress selective-refresh exports exist before core Customizer scripts run.
 * Some optimized/minified admin loads can execute customize-selective-refresh before
 * its localized object is printed, which leaves the Customizer stuck loading.
 */
function trimvia_customizer_partial_refresh_exports_fallback()
{
	$shim = <<<'JS'
window._customizePartialRefreshExports = window._customizePartialRefreshExports || {
  partials: {},
  renderQueryVar: 'wp_customize_render_partials',
  l10n: {}
};
window.customizePartialRefreshExports = window.customizePartialRefreshExports || window._customizePartialRefreshExports;
var _customizePartialRefreshExports = window._customizePartialRefreshExports;
var customizePartialRefreshExports = window.customizePartialRefreshExports;
JS;

	wp_add_inline_script('customize-controls', $shim, 'before');
	wp_add_inline_script('customize-selective-refresh', $shim, 'before');
}
add_action('customize_controls_enqueue_scripts', 'trimvia_customizer_partial_refresh_exports_fallback', 0);
/**
 * Print the partial-refresh export fallback before combined admin scripts execute.
 */
function trimvia_print_customizer_partial_refresh_exports_fallback()
{
	?>
	<script>
	window._customizePartialRefreshExports = window._customizePartialRefreshExports || {
	  partials: {},
	  renderQueryVar: 'wp_customize_render_partials',
	  l10n: {}
	};
	window.customizePartialRefreshExports = window.customizePartialRefreshExports || window._customizePartialRefreshExports;
	var _customizePartialRefreshExports = window._customizePartialRefreshExports;
	var customizePartialRefreshExports = window.customizePartialRefreshExports;
	</script>
	<?php
}
add_action('customize_controls_print_scripts', 'trimvia_print_customizer_partial_refresh_exports_fallback', 0);