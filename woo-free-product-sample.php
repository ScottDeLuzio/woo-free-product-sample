<?php
/**
 * @link              https://amplifyplugins.com
 * @since             1.0.0
 * @package           Woo_Free_Product_Sample
 *
 * @wordpress-plugin
 * Plugin Name:       Free Product Sample for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/woo-free-product-sample
 * Description:       It allows customers to order a product sample in a simple way.
 * Version:           2.3.2
 * Author:            AMP-MODE
 * Author URI:        https://amplifyplugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-free-product-sample
 * Domain Path:       /languages
 * Requires PHP:      5.6
 * Requires at least: 4.4
 * Tested up to:      6.6.1
 *
 * WC requires at least: 3.1
 * WC tested up to:   9.2.3
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WFPS_VERSION', '2.3.2' );
define( 'WFPS_MINIMUM_PHP_VERSION', '5.6.0' );
define( 'WFPS_MINIMUM_WP_VERSION', '4.4' );
define( 'WFPS_MINIMUM_WC_VERSION', '3.1' );
define( 'WFPS_URL', plugins_url( '/', __FILE__ ) );
define( 'WFPS_ADMIN_URL', WFPS_URL . 'admin/' );
define( 'WFPS_PUBLIC_URL', WFPS_URL . 'public/' );
define( 'WFPS_FILE', __FILE__ );
define( 'WFPS_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WFPS_ADMIN_DIR_PATH', WFPS_ROOT_DIR_PATH . 'admin/' );
define( 'WFPS_PUBLIC_PATH', WFPS_ROOT_DIR_PATH . 'public/' );
define( 'WFPS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WFPS_PLUGIN_NAME', 'Free Product Sample for WooCommerce' );

include WFPS_ROOT_DIR_PATH . 'includes/woo-free-product-sample-functions.php';

/**
 * Add HPOS support and compability
 *
 * @author Archie Mercader <achiles@modeeffect.com>
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

/**
 * Free Product Sample for WooCommerce Start.
 *
 * @since 2.0.0
 */
class Woo_Free_Product_Sample_Start {

	/** @var \Woo_Free_Product_Sample_Start single instance of this class */
	private static $instance;

	/** @var array the admin notices to add */
	private $notices = array();

	/**
	 * Loads Free Product Sample for WooCommerce Start.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		register_activation_hook( __FILE__, array( $this, 'wfps_activation_check' ) );

		// handle notices and activation errors
		add_action( 'admin_init',    array( $this, 'wfps_check_environment' ) );
		add_action( 'admin_init',    array( $this, 'wfps_add_plugin_notices' ) );
		add_action( 'admin_notices', array( $this, 'wfps_admin_notices' ), 15 );

		// if the environment check fails, initialize the plugin
		if ( $this->wfps_is_environment_compatible() ) {
			add_action( 'plugins_loaded', array( $this, 'wfps_init_plugin' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wfps_plugin_action_links' ) );
		}
	}

	/**
	 *
	 * @since    2.0.0
	 */
    public function wfps_plugin_action_links( $links ) {
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=woo-free-product-sample' ) . '">' . __( 'Settings', 'woo-free-product-sample' ) . '</a>';
		}
		$links[] = '<a href="https://docs.amplifyplugins.com/">' . __( 'Docs', 'woo-free-product-sample' ) . '</a>';

		if ( !is_plugin_active( 'woo-free-product-sample-pro/woo-free-product-sample-pro.php' ) && !is_plugin_active( 'free-product-sample-for-woocommerce/woo-free-product-sample-pro.php' ) ) {
			$links[] = '<a href="https://amplifyplugins.com/downloads/free-product-sample-for-woocommerce/" style="color: #d30c5c;font-weight: bold;">' . __( 'Get Pro', 'woo-free-product-sample' ) . '</a>';
		}
        return $links;
    }

	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot clone instances of %s.', get_class( $this ) ), '2.0.0' );
	}

	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot unserialize instances of %s.', get_class( $this ) ), '2.0.0' );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function wfps_init_plugin() {

		if ( ! $this->wfps_plugins_compatible() ) {
			return;
		}

		// load the main plugin class
		require_once( WFPS_ROOT_DIR_PATH . 'includes/class-woo-free-product-sample.php' );

		$plugin = new Woo_Free_Product_Sample();
		$plugin->run();
	}

	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function wfps_activation_check() {

		if ( ! $this->wfps_is_environment_compatible() ) {

			$this->wfps_deactivate_plugin();

			wp_die( WFPS_PLUGIN_NAME . ' could not be activated. ' . $this->wfps_get_environment_message() );

		} else {

			set_transient( '_wfps_plugin_activation', true, 30 );
			/**
			* Rewrite the rules on activation.
			*/
			flush_rewrite_rules();

		}
	}

    /**
     * Determines if the HPOS feature is enabled in WooCommerce.
     *
     * @internal
     *
     * @since 2.0.0
     */

    private function wfps_is_hpos_enabled() {

		$options = get_option( 'woocommerce_hpos_options' );
		return isset( $options['enabled'] ) && $options['enabled'];

    }

	/**
	 * Checks the environment on loading WordPress, just in case the environment changes after activation.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function wfps_check_environment() {

		if ( ! $this->wfps_is_environment_compatible() && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			$this->wfps_deactivate_plugin();

			$this->add_admin_notice( 'bad_environment', 'error', WFPS_PLUGIN_NAME . ' has been deactivated. ' . $this->wfps_get_environment_message() );
		}

		/**
		* Check WooCommerce version and HPOS feature status
		*/
        if ( ! $this->wfps_is_wc_compatible() && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

            $this->add_admin_notice( 'update_woocommerce', 'error', sprintf(
                '%1$s requires WooCommerce version %2$s or higher. Please update WooCommerce to the latest version.',
                '<strong>' . WFPS_PLUGIN_NAME . '</strong>',
                WFPS_MINIMUM_WC_VERSION
            ) );

        }

		/**
		* Check for High-Performance Order Storage (HPOS) feature
		*/
        if ( $this->wfps_is_hpos_enabled() ) {

            $this->add_admin_notice( 'hpos_incompatibility', 'error', __( 'WooCommerce HPOS is enabled. This plugin may not be fully compatible.', 'woo-free-product-sample' ) );

        }

	}

	/**
	 * Adds notices for out-of-date WordPress and/or WooCommerce versions.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function wfps_add_plugin_notices() {

		if ( ! $this->wfps_is_wp_compatible() ) {

			$this->add_admin_notice( 'update_wordpress', 'error', sprintf(
				'%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
				'<strong>' . WFPS_PLUGIN_NAME . '</strong>',
				WFPS_MINIMUM_WP_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
			) );
		}

		if ( ! $this->wfps_is_wc_compatible() && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

			$this->add_admin_notice( 'update_woocommerce', 'error', sprintf(
				'%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
				'<strong>' . WFPS_PLUGIN_NAME . '</strong>',
				WFPS_MINIMUM_WC_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>',
				'<a href="' . esc_url( 'https://downloads.wordpress.org/plugin/woocommerce.' . WFPS_MINIMUM_WC_VERSION . '.zip' ) . '">', '</a>'
			) );
		}
	}

	/**
	 * Determines if the required plugins are compatible.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function wfps_plugins_compatible() {

		return $this->wfps_is_wp_compatible() && $this->wfps_is_wc_compatible();
	}

	/**
	 * Determines if the WordPress compatible.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function wfps_is_wp_compatible() {

		return version_compare( get_bloginfo( 'version' ), WFPS_MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Determines if the WooCommerce compatible.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function wfps_is_wc_compatible() {

		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WFPS_MINIMUM_WC_VERSION, '>=' );
	}

	/**
	 * Deactivates the plugin.
	 *
	 * @since 2.0.0
	 */
	private function wfps_deactivate_plugin() {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug the slug for the notice
	 * @param string $class the css class for the notice
	 * @param string $message the notice message
	 */
	public function add_admin_notice( $slug, $class, $message ) {

		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message
		);
	}

	/**
	 * Displays admin notices.
	 *
	 * @since 2.0.0
	 */
	public function wfps_admin_notices() {
		if ( ! empty( $this->notices ) ) {
			?>
			<div class="error">
			<?php
			foreach ( $this->notices as $key => $notice_values ) :
				?>
				<p><?php echo wp_kses( $notice_values['message'], array( 'a' => array( 'href' => array() ), 'button' => array( 'id' => array(), 'class' => array() ) ) ); ?></p>
				<?php
			endforeach;
			?>
			</div>
			<?php
		}
	}

	/**
	 * Determines if the server environment is compatible with this plugin.
	 *
	 * Override this method to add checks for more than just the PHP version.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function wfps_is_environment_compatible() {

		return version_compare( PHP_VERSION, WFPS_MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function wfps_get_environment_message() {

		return sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', WFPS_MINIMUM_PHP_VERSION, PHP_VERSION );
	}

	/**
	 * Gets the main Measurement Price Calculator loader instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @since 2.0.0
	 *
	 * @return \Woo_Free_Product_Sample_Start
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

}

// fire it up!
Woo_Free_Product_Sample_Start::instance();
