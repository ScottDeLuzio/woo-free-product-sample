<?php 

if( class_exists( 'BeRocket_MM_Quantity' ) ) {
    add_action( 'woocommerce_after_calculate_totals', 'new_calculate_total', 10, 1 );
    function new_calculate_total(){
        if( check_product_sample_is_added_in_cart() == true ) {
            add_action( 'woocommerce_after_cart_table',  'wp_footer_hide' );
            add_action( 'woocommerce_after_mini_cart',  'wp_footer_hide' );
        }    
    }
    
    function wp_footer_hide() {
        echo '<style>
            .checkout-button{display:block!important;}
            .checkout{display:block!important;}
        </style>';
    }
    
    function check_product_sample_is_added_in_cart() {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if( isset( $cart_item['free_sample'] ) && isset( $cart_item['sample_price'] ) ) {
                return true;
            }
        }
        return false;
    }
}