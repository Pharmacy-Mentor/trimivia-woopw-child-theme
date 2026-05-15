<?php
/**
 * Single page tabs
 * @author        Chris@PharmacyMentor.com
 * @compatible    WooCommerce 8
 */
 
 add_filter( 'woocommerce_product_tabs', 'my_remove_product_tabs', 98 );
 
 function my_remove_product_tabs( $tabs ) {
   unset( $tabs['additional_information'] ); // To remove the additional information tab
   return $tabs;
 }

 add_filter( 'woocommerce_product_tabs', 'woopw_add_tabs', 9999 );
   
 function woopw_add_tabs( $tabs ) {
    $product_id = wc_get_product()->get_id();
    // TAB SORTING (DESC 10, ADD INFO 20, REVIEWS 30)

    // Other tabs
    if (have_rows('product_tabs', $product_id)) {
        while (have_rows('product_tabs', $product_id)) { the_row();
            if (get_sub_field('enable', $product_id)) {

                $index = get_row_index();
                $tab_label = (get_sub_field('tab_label', $product_id))? get_sub_field('tab_label', $product_id) : __('Label', 'woopw');
                $id = (get_sub_field('tab_label', $product_id)) ? wc_convert_str_to_slug(get_sub_field('tab_label', $product_id)) : 'woopw-tab-' . $index;
                $title = get_sub_field('tab_title', $product_id);
                $tab_content = get_sub_field('tab_content', $product_id);

                $tabs[$id] = array(
                    'title' => $tab_label, // Tab Label
                    'tab_title' => $title,
                    'tab_content' => $tab_content,
                    'priority' => 1, 
                    'callback' => 'woopw_tab_content_others', // TAB CONTENT
                 );

            }
        }
    }

    // FAQS
    if ( get_field('enable_faq_tab', $product_id ) && get_field('select_faqs', $product_id ) ) {
        $tabs['faq'] = array(
        'title' => __( 'FAQs', 'woopw' ), // TAB TITLE
        'product_id' => $product_id,
        'priority' => 50, 
        'callback' => 'woopw_tab_content_faqs', // TAB CONTENT
        );
    }

    // Return all tabs
    return $tabs;
 }

/*********Adding custom tab to WooCommercec detail page************/


// add_filter( 'woocommerce_product_tabs', 'woo_custom_product_tabs' );
function woo_custom_product_tabs( $tabs ) {
    // Adds the other products tab
    $tabs['other_products_tab'] = array(
        'title'     => __( 'Related Products', 'woocommerce' ),
        'priority'  => 120,
        'callback'  => 'woo_related_products_tab_content'
    );

    return $tabs;
}

// New Related Tab contents

function woo_related_products_tab_content() {
    // The qty pricing tab content  
   
	global $product; // If not set…

    if( ! is_a( $product, 'WC_Product' ) ){
        $product = wc_get_product(get_the_id());
    }

    $args = array(
        'posts_per_page' => 3,
        'columns'        => 4,
        'orderby'        => 'rand',
        'order'          => 'desc',
    );

    $args['related_products'] = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );
    $args['related_products'] = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );

    // Set global loop values.
    wc_set_loop_prop( 'name', 'related' );
    wc_set_loop_prop( 'columns', $args['columns'] );

    wc_get_template( 'single-product/related.php', $args );

}

 function woopw_tab_content_faqs() {
    $product_id = wc_get_product()->get_id();

    echo ( !empty($tab['tab_title']) ? '<h2>' . $tab['tab_title'] . '</h2>' : '' );
    
    $faqs = get_field('select_faqs', $product_id);

    if ($faqs) {
        $i = 0;
        ?>
        <div itemscope itemtype="https://schema.org/FAQPage">
            <ul class="faq-accordion" id="faq-accordion">
            <?php foreach ($faqs as $faq_id) { ?>
                <li class="card" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <div class="card-header" id="faq-heading-<?php echo $i; ?>">
                        <h2 class="mb-0" itemprop="name">
                            <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center gap-3" type="button" data-toggle="collapse" data-target="#collapse-<?php echo $i; ?>" aria-expanded="<?php echo ($i == 0) ? 'true' : 'false'; ?>" aria-controls="collapse-<?php echo $i; ?>">
                                <?php echo get_the_title($faq_id); ?>
                                <i class="fa fa-angle-down text-decoration-none"></i>
                            </button>
                        </h2>
                    </div>

                    <div id="collapse-<?php echo $i; ?>" class="collapse" aria-labelledby="faq-heading-<?php echo $i; ?>" data-parent="#faq-accordion" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <div class="card-body m-0" itemprop="text">
                            <?php echo get_post_field('post_content', $faq_id); ?>
                        </div>
                    </div>
                </li>
            <?php
                $i++;
            } ?>
            </ul>
        </div>
    <?php
    }
    ?>
    <?php
    wc_enqueue_js( "
    jQuery(document).ready(function($) {
      $( 'body' ).on( 'maybescroll', '.woocommerce-tabs', function() {
         var hash = window.location.hash;
         if ( hash === '#tab-faq' ) {
            $( this ).find( 'a[href=#tab-faq]' ).trigger( 'click' );
         } 
      });
      $( '.woocommerce-tabs' ).trigger( 'maybescroll' );
    });
   " );
   ?>
<?php }
 
 function woopw_tab_content_others($slug, $tab) {  
    echo ( !empty($tab['tab_title']) ? '<h2>' . $tab['tab_title'] . '</h2>' : '' );
    echo ( !empty($tab['tab_content']) ? $tab['tab_content'] : '' );

    wc_enqueue_js( "
    jQuery(document).ready(function($) {
      $( 'body' ).on( 'maybescroll', '.woocommerce-tabs', function() {
         var hash = window.location.hash;
         if ( hash === '#tab-". $slug ."' ) {
            $( this ).find( 'a[href=#tab-". $slug ."]' ).trigger( 'click' );
         } 
      });
      $( '.woocommerce-tabs' ).trigger( 'maybescroll' );
    });
   " );
 }