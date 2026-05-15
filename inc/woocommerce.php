<?php
add_filter('woocommerce_show_variation_price', '__return_true');
//Woocommerce intial setup hook
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

function woocommerce_account_related_js() {
	if(is_account_page()):
		wp_enqueue_style( 'bootstrap-datepicker-style', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css', array(), null );
		wp_enqueue_script( 'bootstrap-datepicker-script', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js', array(), null, true );
	endif;
}
add_action( 'wp_enqueue_scripts', 'woocommerce_account_related_js', 90 );

//in default page tempalte custom hook
add_action('theme_after_default_page_template_title', function() {
	if(is_user_logged_in() && is_account_page()):
	?>
	<div class="my-account-action-wrapper mt-4">
		<a href="<?=wc_get_page_permalink('myaccount')?>" class="theme-btn w-auto mx-2 my-2"><?php echo __('Back to Account Dashboard', 'woocommerce') ?></a>
		<a class="theme-btn-s4 w-auto mx-2 my-2" href="<?=wp_logout_url( wc_get_page_permalink('myaccount') ) ?>">Log Out</a>
	</div>
<?php
	endif;
});

//create custom button woo field
add_filter('woocommerce_form_field_button', function($field, $key, $args, $value) {
	$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';
	$custom_attributes = [];
	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value );
		}
	}
	
	$field_html .= '<button type = "'. esc_attr( $args['type'] ) .'" class="'.  esc_attr( implode( ' ', $args['button_class'] ) ) .'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" '. esc_attr( implode( ' ', $custom_attributes ) ) .' >'. esc_attr( $args['button_value'] ?: $args['label'] ) .' '. $args['inner_html'] .' </button>';
	
	$container_class = esc_attr( implode( ' ', $args['class'] ) );
	$container_id    = esc_attr( $args['id'] ) . '_field';
	$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
	return $field;
}, 10, 4);

/*
 * Cart Page
 */

add_action( 'woocommerce_before_cart', 'woo_add_row_open', 20 ); // After notices

add_action( 'woocommerce_before_cart', 'woo_add_primary_column_open', 21 );
add_action( 'woocommerce_after_cart_table', 'woo_add_cart_page_div_close', 10 ); // close column

add_action( 'woocommerce_after_cart_table', 'woo_add_sidebar_wrapper_open', 11 );
add_action( 'woocommerce_after_cart', 'woo_add_cart_page_div_close' ); // close column

add_action( 'woocommerce_after_cart', 'woo_add_cart_page_div_close' ); // close row

/*
 * Checkout Page
 */

// remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

add_action( 'woocommerce_checkout_before_customer_details', 'woo_add_row_open', 20 );

add_action( 'woocommerce_checkout_before_customer_details', 'woo_add_primary_column_open', 21 );
add_action( 'woocommerce_checkout_before_order_review_heading', 'woo_add_cart_page_div_close', 16 );

add_action( 'woocommerce_checkout_before_order_review_heading', 'woo_add_sidebar_wrapper_open', 17 );
add_action( 'woocommerce_after_checkout_form', 'woo_add_cart_page_div_close' ); // close column

add_action( 'woocommerce_after_checkout_form', 'woo_add_cart_page_div_close' ); // close row


add_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_before_order_review_heading', function() {
	global $woocommerce;
	$checkout = $woocommerce->checkout;
	if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
		do_action( 'woocommerce_review_order_before_shipping' );
		echo "<div class='checkout-delivery-methods-wrapper'>";
			wc_cart_totals_shipping_html();
		echo "</div>";
		do_action( 'woocommerce_review_order_after_shipping', $checkout );
	endif;
}, 10);


function woo_add_row_open() {
	echo '<div class="row">';
}

function woo_add_cart_page_div_close() {
	echo '</div>';
}

function woo_add_primary_column_open() {
	echo '<div class="col-lg-8 col-md-12 mb-4">';
}

function woo_add_sidebar_wrapper_open() {
	echo '<div class="col-lg-4 col-md-12 mb-4">';
}


add_action('init', function() {
	if(function_exists('plines_checkout_fields')):
		remove_action('woocommerce_after_order_notes', 'plines_checkout_fields');
		add_action('woocommerce_review_order_after_shipping', 'plines_checkout_fields', 20);
	endif;
});

add_action( 'woocommerce_after_variations_table', 'woopw_add_variations_wrapper', 25 );

function woopw_add_variations_wrapper () {
	return '<div class="woocommerce-single-variation-summary"><div class="woocommerce-variation-description"></div></div>';
}

add_filter( 'body_class' , 'woocommerce_myaccount_classes' );
function woocommerce_myaccount_classes($classes) {
    if ( is_user_logged_in() && is_account_page()):
		$classes[] = 'woocommerce-dashboard-page';
	endif;
	
	return $classes;
}

add_filter( 'body_class' , 'woocommerce_search_classes' );
function woocommerce_search_classes($classes) {
	if ( is_search() ) {
		$classes[] = 'woocommerce';
	}

	return $classes;
}

add_filter( 'woocommerce_account_menu_items', 'pm_remove_links_from_myaccount_menu' );
function pm_remove_links_from_myaccount_menu( $menu_links ){
	
	unset( $menu_links[ 'dashboard' ] );
	unset( $menu_links[ 'customer-logout' ] );
	return $menu_links;
	
}

add_action( 'wp_footer', 'woo_modify_wc_variation_desc_position' );
function woo_modify_wc_variation_desc_position() {
	if(is_product() || is_cart()):
    ?>
    <script>
    (function($) {
        $(document).on( 'found_variation', function() {
			console.info('Found Variation');
            var desc = $( '.woocommerce-variation.single_variation' ).find( 
					'.woocommerce-variation-description' ).html();
            var $entry_summary = $( '.woocommerce-single-variation-summary' ), $wc_var_desc = $entry_summary.find( '.woocommerce-variation-description' );
			$entry_summary.parents('.summary').find( '.woocommerce-variation-description').remove();
            if ( $wc_var_desc.length == 0 ) {
                $entry_summary.html( '<div class="woocommerce-variation-description"></div>' );
            }
            $entry_summary.parents('.summary').find( '.woocommerce-variation-description').html( desc );
			
			var total_variations = $('.variation-input-wrapper').find('select option').length, price = $('.product .entry-summary').find('p.price'), variation_price = $('.woocommerce-variation-price');
			if(total_variations == 1 && $.trim(variation_price.html()).length == 0) {
				variation_price.html("<p class='price'>"+ price.html() +"</p>");
			}
			
        });
    })( jQuery );

    </script>
    <?php
	endif;
}