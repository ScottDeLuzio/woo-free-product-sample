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

function wfps_external_links( $location ) {
    $link = 'https://amplifyplugins.com';
    $plugins = get_option( 'active_plugins' );
    if ( is_admin() ) {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if ( in_array( 'free-product-sample-for-woocommerce/woo-free-product-sample-pro.php', $plugins ) ) {
            $plugin_data = 'woo';
        } else {
            $plugin_data = 'amp';
        }
        if ( 'woo' == $plugin_data ) {
            switch ( $location ) {
                case 'docs':
                    $link = 'https://woocommerce.com/products/free-product-sample/';
                    break;
                case 'support':
                    $link = 'https://woocommerce.com/my-account/create-a-ticket/';
                    break;
                case 'review':
                    $link = 'https://woocommerce.com/products/free-product-sample/';
                    break;
            }
        } else {
            switch ( $location ) {
                case 'docs':
                    $link = 'https://docs.amplifyplugins.com';
                    break;
                case 'support':
                    $link = 'https://amplifyplugins.com/support';
                    break;
                case 'review':
                    $link = 'https://wordpress.org/support/plugin/woo-free-product-sample/reviews/#new-post';
                    break;
            }
        }
    }
    return $link;
}