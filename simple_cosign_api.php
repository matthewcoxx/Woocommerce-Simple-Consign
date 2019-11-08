<?php
/**
 * Plugin Name: SimpleCosign
 * Version: 1.0.0
 * Plugin URI: https://wedo-products.com
 * Description: SimpleCosign
 * Author: Matthew Cox
 * Author URI: https://wedo-products.com
 * Requires at least: 4.4.0
 * Tested up to: 5.2.0
 *
 * Text Domain: simple_cosign_api
 * Domain Path: /languages
 *
 * @package WordPress
 * @author  Matthew Cox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'Simple_Cosign_Class' ) ) {

	/**
	 * Main Class.
	 */
	class Simple_Cosign_Class {


		/**
		* Plugin version.
		*
		* @var string
		*/
		const VERSION = '1.0.0';


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object single instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'fallback_notice' ) );
			} else {
				$this->load_plugin_textdomain();
				$this->includes();
				
				$serializer = new Simple_Cosign_Class_Serializer();
				$serializer->init();

				$deserializer = new Simple_Cosign_Class_Deserializer();

				$admin = new Simple_Cosign_Class_Submenu( new Simple_Cosign_Class_Submenu_Page( $deserializer ) );
				$admin->init();
			}
		}

		/**
		 * Method to includes our dependencies.
		 *
		 * @var string
		 */
		public function includes() {
			include_once 'includes/simple_cosign_api-functionality.php';
			include_once( plugin_dir_path( __FILE__ ) . 'admin/simple_cosign_api-class-deserializer.php' );
			foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
				include_once $file;
			}
			include_once 'includes/simple_cosign_woo_checkout.php';
			include_once 'includes/simple_cosign_cronjob.php';
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @access public
		 * @return bool
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'wepb_plugin_locale', get_locale(), 'simple_cosign_api' );

			//load_textdomain( 'simple_cosign_api', trailingslashit( WP_LANG_DIR ) . 'simple_cosign_api/simple_cosign_api' . '-' . $locale . '.mo' );

			//load_plugin_textdomain( 'simple_cosign_api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			return true;
		}

		/**
		 * Fallback notice.
		 *
		 * We need some plugins to work, and if any isn't active we'll show you!
		 */
		public function fallback_notice() {
			echo '<div class="error">';
			echo '<p>' . __( 'SimpleCosign: Needs the WooCommerce Plugin activated.', 'simple_cosign_api' ) . '</p>';
			echo '</div>';
		}
	}
}

/**
* Initialize the plugin.
*/
add_action( 'plugins_loaded', array( 'Simple_Cosign_Class', 'get_instance' ) );
