<?php

if ( ! class_exists( 'P4EN_Init_Controller' ) ) {

	/**
	 * Class P4EN_Init_Controller
	 */
	final class P4EN_Init_Controller {

		/** @var P4EN_Init_Controller $instance */
		private static $instance;
		/** @var string $minimum_php_version */
		public $minimum_php_version = P4EN_MIN_PHP_VERSION;
		/** @var P4EN_Base_Controller $controller*/
		private $controller;
		/** @var P4EN_View $view*/
		private $view;

		/**
		 * Singleton creational patern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			! isset( self::$instance ) and self::$instance = new self;
			return  self::$instance;
		}

		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 */
		private function __construct() {}

		/**
		 * Checks requirements and if its ok it hooks the init_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param $controller P4EN_Base_Controller The main controller of the plugin.
		 * @param $view P4EN_View The P4EN_View instance injected in the controller.
		 * @throws \Exception Controller must be P4EN_Base_Controller and View must be P4EN_View.
		 */
		public function init( $controller, $view ) {
			$this->check_requirements();

			// Property injection
			if ( $controller instanceof P4EN_Base_Controller && $view instanceof P4EN_View ) {
				$this->controller = $controller;
				$this->view = $view;

			} else {
				throw new \Exception( 'Controller must be P4EN_Base_Controller and View must be P4EN_View.' );
			}

		}

		/**
		 * Initializes the plugin.
		 */
		public function init_plugin() {

			Timber::$locations = P4EN_INCLUDES_DIR;

			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
			add_action( 'admin_menu', array( $this, 'init_i18n' ) );    // Initialize internationalization.
			add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
			add_filter( 'locale', array( $this, 'load_locale' ), 11, 1 );

			// Provide hook for other plugins.
			do_action( 'planet4_engagingnetworks_loaded' );
		}

		/**
		 * Load the menu & submenus for the plugin
		 */
		public function load_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {

				add_menu_page(
					'EngagingNetworks',
					'EngagingNetworks',
					'edit_dashboard',
					P4EN_PLUGIN_SLUG_NAME,
					array( $this->view, 'pages' ),
					P4EN_ADMIN_DIR . '/images/logo_menu_page_16x16.jpg'
				);

				if ( current_user_can( 'manage_options' ) ) {

					add_submenu_page(
						P4EN_PLUGIN_SLUG_NAME,
						__( 'Settings', 'planet4-engagingnetworks' ),
						__( 'Settings', 'planet4-engagingnetworks' ),
						'manage_options',
						P4EN_PLUGIN_SLUG_NAME . '-settings',
						array( $this, 'prepare_settings' )
					);
				} else {
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'planet4-engagingnetworks' ),'Permission Denied Error',
						array(
							'response' => 200,
							'back_link' => true,
						)
					);
				}

				add_action( 'admin_init', array( $this->controller, 'register_settings' ) );
			}
		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function prepare_settings() {
			$this->view->settings( [
				'settings' => get_option( 'p4en_settings' ),
				'available_languages' => P4EN_LANGUAGES,
			] );
		}

		/**
		 *
		 */
		public function load_locale() {
			$settings = get_option( 'p4en_settings' );
			return $settings['p4en_lang'];
		}

		/**
		 * Checks plugin requirements.
		 */
		public function check_requirements() {

			if ( is_admin() ) {         // If we are on the admin panel.
				// Run the version check. If it is successful, continue with initialization for this plugin.
				if ( $this->php_version_check() ) {
					add_action( 'init', array( $this, 'init_plugin' ) );

				} else {
					wp_die(
						'<div class="updated fade">' .
						   __( '<u>Error!</u><br/><br/>Plugin <strong>' . P4EN_PLUGIN_NAME . '</strong> requires a newer version of PHP to be running.', 'planet4-engagingnetworks' ) .
						   '<br/>' . __( 'Minimum version of PHP required: ', 'planet4-engagingnetworks' ) . '<strong>' . $this->minimum_php_version . '</strong>' .
						   '<br/>' . __( 'Your server\'s PHP version: ', 'planet4-engagingnetworks' ) . '<strong>' . phpversion() . '</strong>' .
						   '</div>', 'Plugin Activation Error', array(
							   'response' => 200,
							   'back_link' => true,
						   )
					);
				}
			}
		}

		/**
		 * Check if the user's version is less than the required php version
		 *
		 * @return boolean true if version check passed or false otherwise.
		 */
		public function php_version_check() {
			if ( version_compare( phpversion(), $this->minimum_php_version ) < 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Load assets only on the admin pages of the plugin.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function load_admin_assets( $hook ) {
			// Load only on ?page=P4EN_PLUGIN_SLUG_NAME.
			if ( strpos( $hook, P4EN_PLUGIN_SLUG_NAME ) === false ) {
				return;
			}
			wp_enqueue_style( 'p4en_admin_style', P4EN_ADMIN_DIR . '/css/admin.css', array(), '0.1' );
			wp_enqueue_script( 'p4en_admin_script', P4EN_ADMIN_DIR . '/js/admin.js', array(), '0.1', true );
		}

		/**
		 * Initialize internationalization (i18n) for this plugin.
		 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
		 */
		public function init_i18n() {
			load_plugin_textdomain( 'planet4-engagingnetworks', false, P4EN_PLUGIN_DIRNAME . '/languages/' );
		}

		/**
		 * Make clone magic method private, so nobody can clone instance.
		 */
		private function __clone() {}

		/**
		 * Make wakeup magic method private, so nobody can unserialize instance.
		 */
		private function __wakeup() {}
	}

}
