<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://amplifyplugins.com/
 * @since      1.0.0
 *
 * @package    Woo_Free_Product_Sample
 * @subpackage Woo_Free_Product_Sample/admin
 * @author     Mohiuddin Abdul Kader <muhin.cse.diu@gmail.com>
 */

class Woo_Free_Product_Sample_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version
	 */
	private $version;

	/**
	 * The option of this plugin.
	 *
	 * @since    2.0.0
	 * @param    string
	 */
	public $_optionName  = 'woo_free_product_sample_settings';

	/**
	 * The option group of this plugin.
	 *
	 * @since    2.0.0
	 * @param    string
	 */
	public $_optionGroup = 'woo-free-product-sample-options-group';

	/**
	 * The default option of this plugin.
	 *
	 * @since    2.0.0
	 * @param    array
	 */
	public $_defaultOptions = array(
		'button_label'      	=> 'Order a Sample',
		'max_qty_per_order'		=> 5
	);

	/**
	 * The option of this plugin.
	 *
	 * @since    2.0.0
	 * @param    string
	 */
	public $_activation  = 'the_wp_next_licence_activation';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string, string
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 	= $plugin_name;
		$this->version 		= $version;

		add_filter('plugin_row_meta', array($this, 'plugin_meta_links'), 10, 2);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wfps_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-free-product-sample-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wfps_enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-free-product-sample-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Register the admin menu for the settings
	 *
	 * @since    2.0.0
	 * @param    array
	 */
    public function wfps_settings_menu() {

        add_menu_page(
			__('Product Sample', 'woo-free-product-sample'),
			__('Product Sample', 'woo-free-product-sample'),
			'manage_options',
			'woo-free-product-sample',
            array(
                $this,
                'wfps_settings_page'
			),
			WFPS_ADMIN_URL . 'img/woo-free-product-sample.png',
			60
		);

	}

	/**
	 * Display the settings page
	 *
	 * @since    2.0.0
	 * @param    array
	 */
	public function wfps_settings_page() {

		$settings = Woo_Free_Product_Sample_Settings::wfps_setting_fields();
		return include  WFPS_ADMIN_DIR_PATH . 'partials/woo-free-product-sample-settings.php';

	}

	/**
	 * Save the setting options
	 *
	 * @since    2.0.0
	 * @param    array
	 */
	public function wfps_menu_register_settings() {

		add_option( $this->_optionName, $this->_defaultOptions );
		register_setting( $this->_optionGroup, $this->_optionName );

	}

	/**
	 * Apply filter with default options
	 *
	 * @since    2.0.0
	 * @param    none
	 */
	public function wfps_set_default_options() {
		return apply_filters( 'woo_free_product_sample_default_options', $this->_defaultOptions );
	}

	/**
	 * Load activation status
	 *
	 * @since    2.0.0
	 * @param    array
	 * @return   void
	 */
	public function get_license_status() {
		$status = get_option( 'woo-free-product-sample-pro-license-status' );
		if ( ! $status ) {
			// User hasn't saved the license to settings yet. No use making the call.
			return false;
		}
		return trim( $status );
	}

	/**
	 * Add links to plugin's description in plugins table
	 *
	 * @since    2.0.0
	 * @param    none
	 * @return   void
	 */
	public function plugin_meta_links($links, $file){
		if ($file !== plugin_basename(WFPS_FILE)) {
			return $links;
		}

		$support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/woo-free-product-sample/" title="' . __('Get help', 'woo-free-product-sample') . '">' . __('Support', 'woo-free-product-sample') . '</a>';
		$home_link = '<a target="_blank" href="https://amplifyplugins.com/downloads/free-product-sample-for-woocommerce/" title="' . __('Plugin Homepage', 'woo-free-product-sample') . '">' . __('Plugin Homepage', 'woo-free-product-sample') . '</a>';
		$rate_link = '<a target="_blank" href="https://wordpress.org/support/plugin/woo-free-product-sample/reviews/#new-post" title="' . __('Rate the plugin', 'woo-free-product-sample') . '">' . __('Rate the plugin ★★★★★', 'woo-free-product-sample') . '</a>';

		$links[] = $support_link;
		$links[] = $home_link;
		$links[] = $rate_link;

		return $links;
	}

}