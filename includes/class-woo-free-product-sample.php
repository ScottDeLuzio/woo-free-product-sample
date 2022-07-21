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

class Woo_Free_Product_Sample {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Free_Product_Sample_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version
	 */
	protected $version;

	/**
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WFPS_VERSION' ) ) {
			$this->version = WFPS_VERSION;
		} else {
			$this->version = '2.2.2';
		}
		$this->plugin_name = 'woo-free-product-sample';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		add_action( 'admin_init', array( $this, 'redirect' ) );
	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-free-product-sample-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-free-product-sample-i18n.php';

		/**
		 * The class responsible for defining settings options responsibility
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-woo-free-product-sample-settings.php';

		/**
		 * The class responsible for defining message options responsibility
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-free-product-sample-message.php';

		/**
		 * The class responsible for defining helper options responsibility
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-free-product-sample-helper.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-free-product-sample-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-free-product-sample-public.php';

		$this->loader = new Woo_Free_Product_Sample_Loader();

	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Free_Product_Sample_i18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_Free_Product_Sample_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wfps_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wfps_enqueue_scripts' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'wfps_set_default_options' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wfps_settings_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'wfps_menu_register_settings' );

	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Free_Product_Sample_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'woocommerce_init', $plugin_public, 'init' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wfps_enqueue_styles' );
		$this->loader->add_filter( 'plugins_loaded', $plugin_public, 'wfps_price' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'wfps_button', 5 );
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'wfps_add_to_cart_action', 10 );
		$this->loader->add_filter( 'woocommerce_before_calculate_totals', $plugin_public, 'wfps_apply_sample_price_to_cart_item', 10 );
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'wfps_store_id', 10, 2 );
		$this->loader->add_filter( 'wc_add_to_cart_message_html', $plugin_public, 'wfps_add_to_cart_message', 99, 4 );
		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $plugin_public, 'wfps_set_limit_per_order', 99, 4 );
		$this->loader->add_filter( 'woocommerce_get_cart_item_from_session', $plugin_public, 'wfps_get_cart_items_from_session', 10, 2 );
		$this->loader->add_action( 'woocommerce_add_order_item_meta', $plugin_public, 'wfps_save_posted_data_into_order', 10, 2 );
		$this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'wfps_set_locate_template', 10, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'wfps_alter_item_name', 10, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_price', $plugin_public, 'wfps_cart_item_price_filter', 10, 3 );
		$this->loader->add_filter( 'woocommerce_update_cart_validation', $plugin_public, 'wfps_cart_update_limit_order', 10, 4 );
		$this->loader->add_filter( 'woocommerce_cart_item_subtotal',  $plugin_public, 'wfps_item_subtotal', 99, 3 );

		// filter for Minimum/Maximum plugin override overriding
		$this->loader->add_action( 'woocommerce_before_template_part', $plugin_public, 'wfps_check_cart_items' );
		$this->loader->add_action( 'woocommerce_check_cart_items', $plugin_public, 'wfps_check_cart_items' );
		$this->loader->add_filter( 'wc_min_max_quantity_minmax_do_not_count', $plugin_public, 'wfps_cart_exclude', 10, 4 );
		$this->loader->add_filter( 'wc_min_max_quantity_minmax_cart_exclude', $plugin_public, 'wfps_cart_exclude', 10, 4 );

	}

	/**
     * Redirect to setting page when WooCommerce plugin is activated
     */
    public function redirect() {
        // Bail if no activation transient is set.
        if ( ! get_transient( '_wfps_plugin_activation' ) ) {
            return;
        }
        // Delete the activation transient.
        delete_transient( '_wfps_plugin_activation' );

		wp_safe_redirect( add_query_arg( array(
			'activate'		=> 'true',
			'plugin_status' => 'all',
			'paged'		=> '1',
		), admin_url( 'plugins.php' ) ) );

    }

	/**
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 *
	 * @since     1.0.0
	 * @return    Woo_Free_Product_Sample_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}