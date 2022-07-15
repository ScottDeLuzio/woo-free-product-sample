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

class Woo_Free_Product_Sample_Message {

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
		'button_label'          	=> 'Order a Sample',
		'max_qty_per_order'			=> 5,
		'maximum_qty_message'      	=> ''
	);

	/**
	 * Validation message
	 *
	 * @since    2.0.0
	 * @param    none
	 */
    public static function validation_notice( $product_id ){

        $final_msg         = '';
		$setting_options   = wp_parse_args( get_option(self::$_optionName), self::$_defaultOptions );
		$max_qty           = (int)$setting_options['max_qty_per_order'];
		$default_message   = 'There is a limit of ' . sprintf(
			_n(
				'%s sample per order',
				'%s samples per order',
				$max_qty,
				'woo-free-product-sample'
			),
			$max_qty
		);
		$message 		   = isset( $setting_options['maximum_qty_message'] ) && '' != $setting_options['maximum_qty_message'] ? $setting_options['maximum_qty_message'] : $default_message;

        $product		   = wc_get_product( $product_id );
        $searchVal         = array( "{product}", "{qty}" );
        $replaceVal        = array( $product->get_name(), $setting_options['max_qty_per_order'] );
        $final_msg         = str_replace( $searchVal, $replaceVal, $message );
        return $final_msg;

    }
}