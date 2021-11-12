<?php

/**
 * The file that defines the core plugin class
 *
 *
 * @link       https://amplifyplugins.com/
 * @since      1.0.0
 *
 * @package    Woo_Free_Product_Sample
 * @subpackage Woo_Free_Product_Sample/includes
 * @author     hossain88 <muhin.cse.diu@gmail.com>
 */

class Woo_Free_Product_Sample_Helper {

/**
	 * The option of this plugin.
	 *
	 * @since    2.0.0
	 * @param    string
	 */
	public static $_optionName  = 'woo_free_product_sample_settings';

	/**
	 * The option group of this plugin.
	 *
	 * @since    2.0.0
	 * @param    string
	 */
	public static $_optionGroup = 'woo-free-product-sample-options-group';

	/**
	 * The default option of this plugin.
	 *
	 * @since    2.0.0
	 * @param    array
	 */
	public static $_defaultOptions = array(
		'button_label'          => 'Order a Sample',
		'max_qty_per_order'		=> 5,
		'maximum_qty_message'	=> ''
	);

	/**
	 * Check product is in stock
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_settings() {
		return wp_parse_args( get_option(self::$_optionName), self::$_defaultOptions );
	}

	/**
	 * Check product is in stock
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_is_in_stock() {
        global $product;
        return $product->is_in_stock();
	}

	/**
	 * Check product already is in cart
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_check_sample_is_in_cart( $product_id ) {

		global $woocommerce;
		$setting_options   = self::wfps_settings();
		$disable_limit 	   = isset( $setting_options['disable_limit_per_order'] ) ? $setting_options['disable_limit_per_order'] : null;
		$notice_type 	   = isset( $setting_options['limit_per_order'] ) ? $setting_options['limit_per_order'] : 'all';

		if( isset( $disable_limit ) || is_admin() ) {
			return TRUE;
		} else {
			if ( !empty( $woocommerce->cart->get_cart() ) ) {
				foreach( $woocommerce->cart->get_cart() as $key => $val ) {
					if( 'product' == $notice_type ) {
						if( ( isset( $val['free_sample'] ) && $product_id == $val['free_sample'] ) && ( $setting_options['max_qty_per_order'] <= $val['quantity'] ) ) {
							return FALSE;
						}
					} else if( 'all' == $notice_type ) {
						if( ( isset( $val['free_sample'] ) ) && ( $setting_options['max_qty_per_order'] <= self::wfps_cart_total() ) ) {
							return FALSE;
						}
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * Check product quantity is in cart
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_cart_total( ) {

		global $woocommerce;
		$total = 0;
		foreach( $woocommerce->cart->get_cart() as $key => $val ) {
			if( isset( $val['free_sample'] ) ) {
				$total += $val['quantity'];
			}
		}
		return $total;

	}


	/**
	 * Check product type in product details page
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_product_type() {
		global $product;
		if( $product->is_type( 'simple' ) ) {
			return 'simple';
		} else if( $product->is_type( 'variable' ) ) {
			return 'variable';
		} else {
			return NULL;
		}
    }

	/**
	 * Display sample button
	 *
	 * @since    2.0.0
	 * @param    none
	 */
    public static function wfps_request_button() {

        $button  = '';
        switch ( self::wfps_product_type() ) {
            case "simple":
                $button = '<button type="submit" name="simple-add-to-cart" value="'.get_the_ID().'" id="woo-free-sample-button" class="woo-free-sample-button">'.sprintf( esc_html__( '%s', 'woo-free-product-sample' ), self::wfps_button_text() ).'</button>';
                break;
            case "variable":
                $button = '<button type="submit" name="variable-add-to-cart" value="'.get_the_ID().'" id="woo-free-sample-button" class="woo-free-sample-button">'.sprintf( esc_html__( '%s', 'woo-free-product-sample' ), self::wfps_button_text() ).'</button>';
                break;
            default:
                $button = '';
        }
        return $button;
    }

	/**
	 * Retrieve button label
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_button_text() {
		global $product;
		$product_id 		= $product->get_id();
		$custom_label_key	= apply_filters( 'woo_free_product_sample_custom_label_key_product', 'fps_custom_label', $product );
		$custom_label		= get_post_meta( $product_id, $custom_label_key, true );
		$default_label		= apply_filters( 'woo_free_product_sample_default_label_product', esc_html__( 'Order a Sample', 'woo-free-product-sample' ) );

		$vars = array(
			'{product_name}'		=> $product->get_name(),
			'{custom_label}'		=> $custom_label && '' != $custom_label ? esc_html( $custom_label ) : $default_label,
		);

		$setting_options   = self::wfps_settings();

		$setting_options['button_label'] = strtr( $setting_options['button_label'], $vars );

		$button_label = isset( $setting_options['button_label'] ) ? esc_html__( $setting_options['button_label'], 'woo-free-product-sample' ) : $default_label;

		$button_label = apply_filters( 'woo_free_product_sample_button_label_product', $button_label, $product );

		return $button_label;
	}

	/**
	 * Return sample price
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public static function wfps_price( $product_id ) {
		return apply_filters( 'woo_free_product_sample_price', 0.00, $product_id );
	}

	/**
	 * Sample Qty
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   void
	 */
	public static function wfps_sample_qty() {

		if ( class_exists( 'SPQ_Smart_Product_Quantity' ) ) {
			return empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) );
		}

		return 1;
	}

	/**
	 * Retrieve all products in the store
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   array
	 */
	public static function wfps_products() {

		global $wpdb;
		$table 	= $wpdb->prefix . 'posts';
		$sql 	= $wpdb->prepare("SELECT ID, `post_title` FROM $table WHERE `post_type` = %s AND `post_status`= 'publish' ORDER BY post_title", 'product');
		$data 	= [];
		$data 	= $wpdb->get_results($sql, ARRAY_A);
		return $data;

	}

	/**
	 * Retrieve all categories of the products
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   array
	 */
	public static function wfps_categories() {

		$orderby 	= 'name';
		$order 		= 'asc';
		$hide_empty = false ;
		$cat_args 	= array(
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty,
		);

		$data 		= array();
		$categories = get_terms( 'product_cat', $cat_args );
		$inc 		= 0;
		foreach( $categories as $cat ) {
			$data[$inc]['ID']  		   = $cat->term_id;
			$data[$inc]['post_title']  = $cat->name;
			$inc++;
		}
		return $data;

    }

    /**
	 * Get all shipping classes
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   void
	 */
	public static function wfps_shipping_class() {

		$data 		= array();
		$data[-1] 	= __( 'No Shipping Class', 'woo-free-product-sample-pro' );
		$shipping_classes = get_terms( array( 'taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );
		foreach( $shipping_classes as $sc ) {
			$data[$sc->term_id]  = $sc->name;
		}
		return $data;

	}

	/**
	 * Get all tax classes
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   void
	 */
	public static function wfps_tax_class() {

		$data 		= array();
		$options = array(
			'' => __( 'Standard', 'woocommerce' ),
		);

		$tax_classes = \WC_Tax::get_tax_classes();

		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}

		foreach ( $options as $key => $value ) {
			$data[$key] = $value;
		}
		return $data;

	}

	/**
	 * Get pro plugin exists
	 *
	 * @since    1.0.0
	 * @param    none
     * @return   void
	 */
	public static function is_pro()
    {
        return class_exists('Woo_Free_Product_Sample_Pro');
    }


}