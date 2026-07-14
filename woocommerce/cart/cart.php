<?php
/**
 * Cart Page — Trimvia layout.
 *
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined('ABSPATH') || exit;

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>

<section class="page-hero trimvia-cart-hero">
	<div class="hero-noise"></div>
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'theme-woopm-child'); ?></a>
			<span>&rsaquo;</span>
			<span><?php esc_html_e('Basket', 'theme-woopm-child'); ?></span>
		</div>
		<h1><?php esc_html_e('Your Basket', 'theme-woopm-child'); ?></h1>
		<p><?php esc_html_e('Review your selected treatments before proceeding to checkout. Every order is prescriber-reviewed before dispatch.', 'theme-woopm-child'); ?></p>
	</div>
</section>

<section class="page-section trimvia-cart-section">
	<div class="container">
		<?php do_action('woocommerce_before_cart'); ?>

		<div class="rx-notice rv">
			<div class="rx-notice-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
			</div>
			<div>
				<h5><?php esc_html_e('Prescription Required', 'theme-woopm-child'); ?></h5>
				<p><?php esc_html_e('These treatments require a clinical consultation before your order can be dispensed. Completing your purchase begins the prescription review process with one of our UK-registered pharmacist prescribers.', 'theme-woopm-child'); ?></p>
			</div>
		</div>

		<a href="<?php echo esc_url($shop_url); ?>" class="cart-back rv">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
			<?php esc_html_e('Continue shopping', 'woocommerce'); ?>
		</a>

		<?php if (WC()->cart && !WC()->cart->is_empty()) : ?>
			<div class="cart-layout rv">
				<div>
					<form class="woocommerce-cart-form trimvia-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
						<?php do_action('woocommerce_before_cart_table'); ?>

						<div class="cart-items-wrap">
							<div class="cart-items-head">
								<span><?php esc_html_e('Product', 'woocommerce'); ?></span>
								<span><?php esc_html_e('Quantity', 'woocommerce'); ?></span>
								<span><?php esc_html_e('Price', 'woocommerce'); ?></span>
								<span></span>
							</div>

							<?php do_action('woocommerce_before_cart_contents'); ?>

							<?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
								<?php
								$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
								$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
								$product_name = $_product instanceof WC_Product ? $_product->get_name() : '';

								if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
									continue;
								}

								$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
								$product_title = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
								$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
								$product_categories = wc_get_product_category_list($product_id, ', ', '', '');
								$cart_item_data = wc_get_formatted_cart_item_data($cart_item);
								?>
								<div class="cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
									<div class="cart-item-info">
										<div class="cart-item-img">
											<?php if ($product_permalink) : ?>
												<a href="<?php echo esc_url($product_permalink); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
											<?php else : ?>
												<?php echo wp_kses_post($thumbnail); ?>
											<?php endif; ?>
										</div>
										<div>
											<div class="cart-item-name">
												<?php if ($product_permalink) : ?>
													<a href="<?php echo esc_url($product_permalink); ?>"><?php echo esc_html($product_title); ?></a>
												<?php else : ?>
													<?php echo esc_html($product_title); ?>
												<?php endif; ?>
											</div>
											<?php if ('' !== $cart_item_data) : ?>
												<div class="cart-item-sub"><?php echo wp_kses_post($cart_item_data); ?></div>
											<?php endif; ?>
											<?php if ($_product->is_on_backorder($cart_item['quantity'])) : ?>
												<span class="cart-item-tag"><?php esc_html_e('Backorder', 'woocommerce'); ?></span>
											<?php elseif ('' !== $product_categories) : ?>
												<span class="cart-item-tag"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($product_categories), 3, '')); ?></span>
											<?php endif; ?>
										</div>
									</div>

									<div class="cart-item-quantity">
										<?php
										if ($_product->is_sold_individually()) {
											$min_quantity = 1;
											$max_quantity = 1;
										} else {
											$min_quantity = 0;
											$max_quantity = $_product->get_max_purchase_quantity();
										}

										echo apply_filters(
											'woocommerce_cart_item_quantity',
											woocommerce_quantity_input(
												array(
													'input_name' => "cart[{$cart_item_key}][qty]",
													'input_value' => $cart_item['quantity'],
													'max_value' => $max_quantity,
													'min_value' => $min_quantity,
													'product_name' => $product_name,
												),
												$_product,
												false
											),
											$cart_item_key,
											$cart_item
										); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</div>

									<div
										class="cart-item-price"
										data-unit-price="<?php echo esc_attr(wc_get_price_to_display($_product)); ?>"
										data-currency-symbol="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>"
										data-currency-position="<?php echo esc_attr(get_option('woocommerce_currency_pos', 'left')); ?>"
										data-price-decimals="<?php echo esc_attr(wc_get_price_decimals()); ?>"
										data-decimal-separator="<?php echo esc_attr(wc_get_price_decimal_separator()); ?>"
										data-thousand-separator="<?php echo esc_attr(wc_get_price_thousand_separator()); ?>"
									>
										<span class="cart-item-price-total"><?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<?php if ((int) $cart_item['quantity'] > 1) : ?>
											<small class="cart-item-price-each"><?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e('each', 'theme-woopm-child'); ?></small>
										<?php endif; ?>
									</div>

									<div class="cart-item-remove">
										<?php
										echo apply_filters(
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a href="%s" class="cart-remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></a>',
												esc_url(wc_get_cart_remove_url($cart_item_key)),
												esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
												esc_attr($product_id),
												esc_attr($_product->get_sku())
											),
											$cart_item_key
										); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</div>
								</div>
							<?php endforeach; ?>

							<?php do_action('woocommerce_cart_contents'); ?>

							<div class="cart-coupon">
								<?php if (wc_coupons_enabled()) : ?>
									<div class="cart-coupon-apply-group">
										<svg class="cart-coupon__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
										<label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label>
										<input type="text" name="coupon_code" class="cart-coupon-input input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Have a coupon code?', 'theme-woopm-child'); ?>" data-trimvia-placeholder-mobile="<?php esc_attr_e('Enter coupon code', 'theme-woopm-child'); ?>" />
										<button type="submit" class="cart-coupon-btn button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply', 'theme-woopm-child'); ?></button>
									</div>
									<?php do_action('woocommerce_cart_coupon'); ?>
								<?php endif; ?>
								<button type="submit" class="cart-update-btn button" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update basket', 'theme-woopm-child'); ?></button>
								<?php do_action('woocommerce_cart_actions'); ?>
								<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
							</div>

							<?php do_action('woocommerce_after_cart_contents'); ?>
						</div>

						<?php do_action('woocommerce_after_cart_table'); ?>
					</form>
				</div>

				<?php woocommerce_cart_totals(); ?>
			</div>
		<?php else : ?>
			<div class="cart-empty rv">
				<div class="cart-empty-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
				</div>
				<h3><?php esc_html_e('Your basket is empty', 'woocommerce'); ?></h3>
				<p><?php esc_html_e("You haven't added any treatments yet. Browse our full range of clinician-approved weight loss options.", 'theme-woopm-child'); ?></p>
				<a href="<?php echo esc_url($shop_url); ?>" class="btn-accent">
					<?php esc_html_e('Shop Treatments', 'theme-woopm-child'); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php do_action('woocommerce_after_cart'); ?>
