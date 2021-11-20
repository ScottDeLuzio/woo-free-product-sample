<?php
/**
 *
 * @link       https://amplifyplugins.com/
 * @since      1.0.0
 *
 * @package    Woo_Free_Product_Sample
 * @subpackage Woo_Free_Product_Sample/public
 */

class Woo_Free_Product_Sample_Public {

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version
	 */
	private $version;

	/**
	 * @since    2.0.0
	 * @access   public
	 * @var      integer $_total
	 */
	public $_total = 1;

	/**
	 * @since    1.0.0
	 * @param    string    $plugin_name
	 * @param    string    $version
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 	= $plugin_name;
		$this->version 		= $version;
	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function wfps_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-free-product-sample-public.css', [], $this->version, 'all' );
	}

	/**
	 *
	 * @since    2.1.4
	 */
	public function init() {

		// filter for Measurement Price Calculator plugin override overriding
		if (in_array('woocommerce-measurement-price-calculator/woocommerce-measurement-price-calculator.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_filter('wc_measurement_price_calculator_add_to_cart_validation', [ $this, 'wfps_measurement_price_calculator_add_to_cart_validation' ], 10, 4 );
		}

		// filter for Minimum/Maximum plugin override overriding
		if (in_array('woocommerce-min-max-quantities/min-max-quantities.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_filter('wc_min_max_quantity_minimum_allowed_quantity', [ $this, 'wfps_minimum_quantity' ], 10, 4 );
			add_filter('wc_min_max_quantity_maximum_allowed_quantity', [ $this, 'wfps_maximum_quantity' ], 10, 4 );
			add_filter('wc_min_max_quantity_group_of_quantity', [ $this, 'wfps_group_of_quantity' ], 10, 4 );
			// Check items.
		}

		// filter for WooCommerce Chained Products plugin override overriding
		if (in_array('woocommerce-chained-products/woocommerce-chained-products.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action( 'wc_after_chained_add_to_cart', [ $this, 'wfps_remove_chained_products' ], 20, 6 );
		}

	}

	/**
	 * Display sample button
	 *
	 * @since  	2.0.0
	 * @param  	none
	 * @return 	html
	 */
	public function wfps_button() {

		if ( \Woo_Free_Product_Sample_Helper::wfps_is_in_stock() && \Woo_Free_Product_Sample_Helper::wfps_check_sample_is_in_cart( get_the_ID() ) ) {
			$button = \Woo_Free_Product_Sample_Helper::wfps_request_button();
			echo apply_filters(
						'woo_free_product_sample_button',
						$button
					);
		}

	}

	/**
	 * Handle add to cart
	 *
	 * @since 2.0.0
	 * @param string
	 */
	public static function wfps_add_to_cart_action( $url = false ) {

		if ( ! isset( $_REQUEST['simple-add-to-cart'] ) || ! is_numeric( wp_unslash( $_REQUEST['simple-add-to-cart'] ) ) )
		{
			return;
		}

		wc_nocache_headers();

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST['simple-add-to-cart'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			return;
		}

		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
			$was_added_to_cart = self::wfps_add_to_cart_handler_variable( $product_id );
		} else {
			$was_added_to_cart = self::wfps_add_to_cart_handler_simple( $product_id );
		}

		// If we added the product to the cart we can now optionally do a redirect.
		if ( $was_added_to_cart && 0 === wc_notice_count( 'error' ) ) {
			$url = apply_filters( 'woocommerce_add_to_cart_redirect', $url, $adding_to_cart );

			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			} elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
	}

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @since 2.4.6 Split from add_to_cart_action.
	 * @param int $product_id Product ID to add to the cart.
	 * @return bool success or not
	 */
	private static function wfps_add_to_cart_handler_simple( $product_id ) {

		$quantity          = \Woo_Free_Product_Sample_Helper::wfps_sample_qty(); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}
		return false;
	}

	/**
	 * Handle adding variable products to the cart.
	 *
	 * @since 2.4.6 Split from add_to_cart_action.
	 * @throws Exception If add to cart fails.
	 * @param int $product_id Product ID to add to the cart.
	 * @return bool success or not
	 */
	private static function wfps_add_to_cart_handler_variable( $product_id ) {
		try {
			$variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$quantity           = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$missing_attributes = array();
			$variations         = array();
			$adding_to_cart     = wc_get_product( $product_id );

			if ( ! $adding_to_cart ) {
				return false;
			}

			// If the $product_id was in fact a variation ID, update the variables.
			if ( $adding_to_cart->is_type( 'variation' ) ) {
				$variation_id   = $product_id;
				$product_id     = $adding_to_cart->get_parent_id();
				$adding_to_cart = wc_get_product( $product_id );

				if ( ! $adding_to_cart ) {
					return false;
				}
			}

			// Gather posted attributes.
			$posted_attributes = array();

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

				if ( isset( $_REQUEST[ $attribute_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					if ( $attribute['is_taxonomy'] ) {
						// Don't use wc_clean as it destroys sanitized characters.
						$value = sanitize_title( wp_unslash( $_REQUEST[ $attribute_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					} else {
						$value = html_entity_decode( wc_clean( wp_unslash( $_REQUEST[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					}

					$posted_attributes[ $attribute_key ] = $value;
				}
			}

			// If no variation ID is set, attempt to get a variation ID from posted attributes.
			if ( empty( $variation_id ) ) {
				$data_store   = WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $adding_to_cart, $posted_attributes );
			}

			// Do we have a variation ID?
			if ( empty( $variation_id ) ) {
				throw new Exception( __( 'Please choose product options&hellip;', 'woocommerce' ) );
			}

			// Check the data we have is valid.
			$variation_data = wc_get_product_variation_attributes( $variation_id );

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				// Get valid value from variation data.
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
				$valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ] : '';

				/**
				 * If the attribute value was posted, check if it's valid.
				 *
				 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				 */
				if ( isset( $posted_attributes[ $attribute_key ] ) ) {
					$value = $posted_attributes[ $attribute_key ];

					// Allow if valid or show error.
					if ( $valid_value === $value ) {
						$variations[ $attribute_key ] = $value;
					} elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs(), true ) ) {
						// If valid values are empty, this is an 'any' variation so get all possible values.
						$variations[ $attribute_key ] = $value;
					} else {
						/* translators: %s: Attribute name. */
						throw new Exception( sprintf( __( 'Invalid value posted for %s', 'woocommerce' ), wc_attribute_label( $attribute['name'] ) ) );
					}
				} elseif ( '' === $valid_value ) {
					$missing_attributes[] = wc_attribute_label( $attribute['name'] );
				}
			}
			if ( ! empty( $missing_attributes ) ) {
				/* translators: %s: Attribute name. */
				throw new Exception( sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ) );
			}
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			return false;
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}

		return false;
	}

	/**
	 * Set sample price in the cart
	 *
	 * @since      2.0.0
	 * @param      string, string
	 */
	public function wfps_store_id( $cart_item ) {

		if( isset( $_REQUEST['simple-add-to-cart'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) {
			$cart_item['free_sample']  = isset( $_REQUEST['simple-add-to-cart'] ) ? sanitize_text_field( $_REQUEST['simple-add-to-cart'] ) : sanitize_text_field( $_REQUEST['variable-add-to-cart'] );
			$product_id = isset( $_REQUEST['simple-add-to-cart'] ) ? sanitize_text_field( $_REQUEST['simple-add-to-cart'] ) : sanitize_text_field( $_REQUEST['variable-add-to-cart'] );
			$cart_item['sample_price'] = (float)\Woo_Free_Product_Sample_Helper::wfps_price( $product_id );
			$cart_item['line_subtotal']= (float)\Woo_Free_Product_Sample_Helper::wfps_price( $product_id );
			$cart_item['line_total']   = (float)\Woo_Free_Product_Sample_Helper::wfps_price( $product_id );
		}
		return $cart_item;
	}

	/**
	 * Set sample price in session
	 *
	 * @since      2.0.0
	 * @param      array, array
	 */
	public function wfps_get_cart_items_from_session( $cart_item, $values ) {

		if ( isset( $values['simple-add-to-cart'] ) || isset( $values['variable-add-to-cart'] ) ) {
			$product_id 					= isset( $_REQUEST['simple-add-to-cart'] ) ? sanitize_text_field( $_REQUEST['simple-add-to-cart'] ) : sanitize_text_field( $_REQUEST['variable-add-to-cart'] );
			$cart_item['free_sample'] 		= isset( $values['simple-add-to-cart'] ) ? $values['simple-add-to-cart'] : $values['variable-add-to-cart'];
			$cart_item['line_subtotal'] 	= (float)\Woo_Free_Product_Sample_Helper::wfps_price( $product_id );
			$cart_item['line_total'] 	  	= (float)\Woo_Free_Product_Sample_Helper::wfps_price( $product_id );
		}

		return $cart_item;
	}

	/**
	 * Add product meta for sample to identity in the admin order details
	 *
	 * @since      2.0.0
	 * @param      int, array
	 */
	public function wfps_save_posted_data_into_order( $itemID, $values ) {
		$custom_order_meta = apply_filters( 'wfps_custom_order_meta', false );

		if ( false === $custom_order_meta ) {
			if ( isset( $values['free_sample'] ) ) {
				$sample 		= __( 'Sample', 'woo-free-product-sample' );
				if( get_locale() == 'de_DE' ){
					wc_add_order_item_meta( $itemID, 'Produkt', 'MUSTERBESTELLUNG' );
					wc_add_order_item_meta( $itemID, 'Preis', 'Wir übernehmen die Kosten für Sie!' );
				} else {
					wc_add_order_item_meta( $itemID, 'PRODUCT_TYPE', $sample );
					wc_add_order_item_meta( $itemID, 'SAMPLE_PRICE', (float)$values["sample_price"] );
				}

			}
		} else {
			do_action( 'wfps_custom_order_meta_action', $itemID, $values );
		}

	}

	/**
	 * Return plugin directory
	 *
	 * @since      2.0.0
	 * @param      none
	 */
	public static function wfps_get_plugin_path(){
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Return WooCommerce template path
	 *
	 * @since      2.0.0
	 * @param      none
	 */
	public function wfps_set_locate_template( $template, $template_name, $template_path ) {

		global $woocommerce;
		$_template = $template;
		if ( ! $template_path ) {
			$template_path = $woocommerce->template_url;
		}

	  	$plugin_path  = self::wfps_get_plugin_path() . '/partials/woocommerce/';
	  	$template = locate_template(
	    	array(
	      		$template_path . $template_name,
	      		$template_name
	    	)
	  	);

	  	if ( ! $template && file_exists( $plugin_path . $template_name ) )
	    	$template = $plugin_path . $template_name;

	  	if ( ! $template )
	    	$template = $_template;

		return $template;

	}

	/**
	 * Set sample price in the order meta
	 *
	 * @since      2.0.0
	 * @param      object, array
	 */
    public function wfps_apply_sample_price_to_cart_item( $cart ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		return;

		// Avoiding hook repetition (when using price calculations for example)
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
		return;

		foreach ( $cart->get_cart() as $key => $value ) {
			if( isset( $value["sample_price"] ) ) {
				$product = $value['data'];
				method_exists( $product, 'set_price' ) ? $product->set_price( (float)$value["sample_price"] ) : $product->price = $value["sample_price"];
			}

		}
	}

	/**
	 * Display validation message when order a product sample
	 *
	 * @since      2.0.0
	 * @param      int, array
	 */
	public function wfps_set_limit_per_order( $valid, $product_id ) {

		global $woocommerce;
		$setting_options   = \Woo_Free_Product_Sample_Helper::wfps_settings();
		$notice_type 	   = isset( $setting_options['limit_per_order'] ) ? $setting_options['limit_per_order'] : null;
		$disable_limit 	   = isset( $setting_options['disable_limit_per_order'] ) ? $setting_options['disable_limit_per_order'] : null;

		if( ! isset( $disable_limit ) ) :
			foreach( $woocommerce->cart->get_cart() as $key => $val ) :

				if( 'product' == $notice_type ) {

					if( ( isset( $val['free_sample'] ) && $product_id == $val['free_sample'] ) && ( $setting_options['max_qty_per_order'] <= $val['quantity'] ) && ( isset( $_REQUEST['simple-add-to-cart'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) ) {
						if( get_locale() == 'ja' ) {
							wc_add_notice( esc_html__( 'この商品を注文できます '.$setting_options['max_qty_per_order'].' 注文あたりの数量。', 'woo-free-product-sample' ), 'error' );
						} else {
							wc_add_notice( esc_html__( 'You can order this product '.$setting_options['max_qty_per_order'].' quantity per order.', 'woo-free-product-sample' ), 'error' );
						}
						exit( wp_redirect( get_permalink($product_id) ) );
					}

				} else if( 'all' == $notice_type ) {

					if( ( isset( $val['free_sample'] ) ) && ( $setting_options['max_qty_per_order'] <= \Woo_Free_Product_Sample_Helper::wfps_cart_total() ) && ( isset( $_REQUEST['simple-add-to-cart'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) ) {
						if( get_locale() == 'ja' ) {
							wc_add_notice( esc_html__( 'サンプル商品を最大で注文できます '.$setting_options['max_qty_per_order'].' 注文あたりの数量。', 'woo-free-product-sample' ), 'error' );
						} else {
							wc_add_notice( esc_html__( 'You can order sample product maximum '.$setting_options['max_qty_per_order'].' quantity per order.', 'woo-free-product-sample' ), 'error' );
						}
						exit( wp_redirect( get_permalink($product_id) ) );
					}

				}
			endforeach;
		endif;
		return $valid;

	}

	/**
	 * Show validation message in the cart page for maximum order
	 *
	 * @since      2.0.0
	 * @param      boolean, array, array, int
	 */
	public function wfps_cart_update_limit_order( $passed, $cart_item_key, $values, $updated_quantity ) {

		$product 		   = wc_get_product( $values['product_id'] );
		$setting_options   = \Woo_Free_Product_Sample_Helper::wfps_settings();
		$notice_type 	   = isset( $setting_options['limit_per_order'] ) ? $setting_options['limit_per_order'] : 'all';
		$disable_limit 	   = isset( $setting_options['disable_limit_per_order'] ) ? $setting_options['disable_limit_per_order'] : null;
		$message 		   = \Woo_Free_Product_Sample_Message::validation_notice( $product->get_id() );

		if( ! isset( $disable_limit ) ) :

			if( 'product' == $notice_type ) {

				if( ( $values['free_sample'] == $values['product_id'] ) && ( $setting_options['max_qty_per_order'] < $updated_quantity ) ) {

					if( get_locale() == "ja" ) {
						wc_add_notice( esc_html__( '注文できます '.$product->get_name().' 最大 '.$setting_options['max_qty_per_order'].'  注文ごと。', 'woo-free-product-sample' ), 'error' );
					} else {

						wc_add_notice( sprintf(
							__( '%1$s.', 'woo-free-product-sample' ),
							$message
						), 'error');

					}

					$passed = false;
				}

			} else if( 'all' == $notice_type ) {

				if( ( isset( $values['free_sample'] ) ) && ( $setting_options['max_qty_per_order'] <= \Woo_Free_Product_Sample_Helper::wfps_cart_total() ) ) {
					if( get_locale() == 'ja' ) {
						wc_add_notice( esc_html__( 'サンプル商品を最大で注文できます '.$setting_options['max_qty_per_order'].' 注文あたりの数量。', 'woo-free-product-sample' ), 'error' );
					} else {
						wc_add_notice( sprintf(
							__( '%1$s.', 'woo-free-product-sample' ),
							$message
						), 'error');
					}

					$passed = false;
				}
			}

		endif;
		return $passed;

	}

	/**
	 * Sample product added in the cart message
	 *
	 * @since      2.0.0
	 * @param      int, array
	 */
	public function wfps_add_to_cart_message ( $message, $products ) {

		$titles = '';
		if( isset( $_REQUEST['simple-add-to-cart'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) {

			$count = 0;
			$titles = array();
			foreach ( $products as $product_id => $qty ) {
				$sample =  esc_html__( 'Sample - ', 'woo-free-product-sample' );

				$titles[] = apply_filters( 'woocommerce_add_to_cart_qty_html', ( $qty > 1 ? absint( $qty ) . ' &times; ' : '' ), $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), strip_tags( $sample . get_the_title( $product_id ) ) ), $product_id );
				$count   += $qty;
			}

			$titles = array_filter( $titles );
			/* translators: %s: product name */
			$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce' ), wc_format_list_of_items( $titles ) );

			// Output success messages.
			$message = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'woocommerce' ), esc_html( $added_text ) );
			return $message;

		}

		return $message;

	}

	/**
	 * Add sample label before the product
	 *
	 * @since      2.0.0
	 * @param      string, array, array
	 */
	public function wfps_alter_item_name ( $product_name, $cart_item, $cart_item_key ) {

		$product 			= $cart_item['data']; // Get the WC_Product Object
		$sample_price 		= (float)\Woo_Free_Product_Sample_Helper::wfps_price( $cart_item['product_id'] );
		$sample_price 		= str_replace( ",",".", $sample_price );
		$prod_price 		= str_replace( ",",".", $product->get_price() );
		if( $sample_price == $prod_price ) {
			$product_name   = esc_html__( 'Sample - ', 'woo-free-product-sample' ) . $product_name;
		}

		return $product_name;
	}

   	/**
	 * Set sample price instead real price
	 *
	 * @since      2.0.0
	 * @param      float, array, array
	 */
    public function wfps_cart_item_price_filter( $price, $cart_item, $cart_item_key ) {

		$product 			= $cart_item['data']; // Get the WC_Product Object
		$sample_price 		= (float)\Woo_Free_Product_Sample_Helper::wfps_price( $cart_item['product_id'] );
		$set_price 			= str_replace( ",", ".", $sample_price );
		if( isset( $cart_item['sample_price'] ) ) {
			$price         = wc_price( $set_price );
		}

		return $price;
	}

	/**
	 * Set subtotal
	 *
	 * @since      2.0.0
	 * @param      float, array, array
	 */
	public function wfps_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {

		if( isset( $cart_item['sample_price'] ) ) {
			if(empty($cart_item['sample_price'])) {
				$price = 0.00;
			} else {
				$price = (float)$cart_item['sample_price'];
			}

			$newsubtotal = wc_price( (float)$price * (int)$cart_item['quantity'] );
			$subtotal = $newsubtotal;
		}

		return $subtotal;
	}

	/**
	 * Check Measurement Price Calculation Validation
	 *
	 * @since      2.0.0
	 * @param      boolean, integer, integer, array
	 */
	public function wfps_measurement_price_calculator_add_to_cart_validation ($valid, $product_id, $quantity, $measurements){
		global $woocommerce;
		$validation = $valid;
		if ( $_REQUEST['simple-add-to-cart'] || $_REQUEST['variable-add-to-cart'] ) {
			$woocommerce->session->set( 'wc_notices', null );
			$validation = true;
		}
		return $validation;
	}

	/**
	 * Filter for Minimum/Maximum plugin overriding
	 *
	 * @since      2.0.0
	 * @param      integer, integer, integer, array, array
	 */
	public function wfps_minimum_quantity($minimum_quantity, $checking_id, $cart_item_key, $values){
		if ( $_REQUEST['simple-add-to-cart'] || $_REQUEST['variable-add-to-cart'] )
			$minimum_quantity = 1;
		return $minimum_quantity;
	}

	/**
	 * Filter for Minimum/Maximum plugin overriding
	 *
	 * @since      2.0.0
	 * @param      integer, integer, integer, array, array
	 */
	public function wfps_maximum_quantity($maximum_quantity, $checking_id, $cart_item_key, $values){
		if ( $_REQUEST['simple-add-to-cart'] || $_REQUEST['variable-add-to-cart'] )
			$maximum_quantity = 1;
		return $maximum_quantity;
	}

	/**
	 * Filter for Minimum/Maximum plugin overriding
	 *
	 * @since      2.0.0
	 * @param      integer, integer, integer, array, array
	 */
	public function wfps_group_of_quantity($group_of_quantity, $checking_id, $cart_item_key, $values){
		if ( $_REQUEST['simple-add-to-cart'] || $_REQUEST['variable-add-to-cart'] )
			$group_of_quantity = 1;
		return $group_of_quantity;
	}

	/**
	 * Filter for Minimum/Maximum plugin overriding
	 *
	 * @since      2.0.0
	 * @param      integer, integer, integer, array, array
	 */
	public function wfps_remove_chained_products ($chained_parent_id, $quantity, $chained_variation_id, $chained_variation_data, $chained_cart_item_data, $cart_item_key){
		global $woocommerce;
		$cart = $woocommerce->cart->get_cart();
		$main_is_sample = $cart[$cart_item_key]['sample'];
		if ($main_is_sample) {
			$main_product_id = $cart[$cart_item_key]['product_id'];
			if ( !get_post_meta($main_product_id, 'sample_chained_enambled', true) ) {
				foreach ($cart as $cart_key => $cart_item) {
					if ($cart_item['product_id'] == $chained_parent_id) {
						$woocommerce->cart->remove_cart_item($cart_key);
						break;
					}
				}
			}
		}
	}

	/**
	 * Check WooCommerce min/max quantities validation message
	 *
	 * @since      2.0.0
	 * @param      array
	 */
	public function wfps_check_cart_items() {
		if( ! is_admin() ) {
			if ( class_exists('WC_Min_Max_Quantities') && WC()->cart->get_cart_contents_count() != 0 ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					if(isset($values['free_sample']) && $values['free_sample'] == $values['product_id']) {
						wc_clear_notices();
					}
				}
			}
		}

	}

	/**
	 * Check WooCommerce min/max quantities validation message
	 *
	 * @since      2.0.0
	 * @param      array
	 */
	public function wfps_cart_exclude( $exclude, $checking_id, $cart_item_key, $values ) {
		if ( class_exists('WC_Min_Max_Quantities') ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				if(isset($values['free_sample']) && $values['free_sample'] == $values['product_id']) {
					return 'yes';
				}
			}
		}
	}

	/**
     * Woo Reviews Shortcode
     *
     * @param none
     * @return void
     **/
    public function wfps_order_sample( $atts )
    {

        // $atts = shortcode_atts( array(
        //     'product_id'    => '',
		// ), $atts );

		// $product_id = $atts['product_id'];
		// $echo ='';
		// $echo .= '<a href="" class="woo-free-sample-button">';
		// 	$echo .= \Woo_Free_Product_Sample_Helper::wfps_button_text();
		// $echo .= '</a>';

		// return $echo;
    }

}