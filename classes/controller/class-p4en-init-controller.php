<?php

if (!class_exists('P4EN_Init_Controller')) {

	/**
	 * Class P4EN_Init_Controller
	 */
	final class P4EN_Init_Controller {

		// Properties
		private static $instance;
		public $P4EN_minimum_php_version = P4EN_MIN_PHP_VERSION;


		private $view;

		/**
		 * Singleton Creational patern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			!isset( self::$instance ) AND self::$instance = new self;
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
		 */
		public function init($view = null) {
			$this->check_requirements();

			if(!is_null($view))
				$this->view = $view;
		}

		/**
		 * Initializes the plugin.
		 */
		public function init_plugin() {

			add_action( 'plugins_loaded', array($this, 'P4EN_init_i18n') );    // Initialize internationalization
			add_action( 'admin_menu', array($this, 'P4EN_admin_menu_load') );
		}

		/**
		 * Load the menu & submenus for the plugin
		 */
		public function P4EN_admin_menu_load() {

			$current_user = wp_get_current_user();

			if(in_array("administrator", $current_user->roles) || in_array("editor", $current_user->roles)) {

				add_menu_page(
					P4EN_PLUGIN_SHORT_NAME,
					P4EN_PLUGIN_SHORT_NAME,
					'edit_dashboard',
					'engaging-networks',
					array($this->view, 'render_dashboard'),
					'none'
				);

				add_submenu_page(
					'engaging-networks',
					esc_html__( 'Settings', P4EN_PLUGIN_TEXTDOMAIN ),
					esc_html__( 'Settings', P4EN_PLUGIN_TEXTDOMAIN ),
					'manage_options',
					'engaging-networks-settings',
					array($this->view, 'render_settings')
				);
			}
		}

		/**
		 * Checks plugin requirements.
		 */
		public function check_requirements() {
			// If we are on the admin panel
			if ( is_admin() ) {
				// Run the version check.
				// If it is successful, continue with initialization for this plugin
				if ( $this->P4EN_php_version_check() ) {
					add_action('init', array($this, 'init_plugin'));
					// Provide hook for other plugins
					do_action( 'planet4_engagingnetworks_loaded' );

				} else {
					wp_die('<div class="updated fade">' .
					       __( '<u>Error!</u><br/><br/>Plugin <strong>'.P4EN_PLUGIN_NAME.'</strong> requires a newer version of PHP to be running.', P4EN_PLUGIN_TEXTDOMAIN ) .
					       '<br/>' . __( 'Minimum version of PHP required: ', P4EN_PLUGIN_TEXTDOMAIN ) . '<strong>' . $this->P4EN_minimum_php_version . '</strong>' .
					       '<br/>' . __( 'Your server\'s PHP version: ', P4EN_PLUGIN_TEXTDOMAIN ) . '<strong>' . phpversion() . '</strong>' .
					       '</div>', 'Plugin Activation Error', array( 'response'=>200, 'back_link'=>TRUE ) );
				}
			}
		}

		/**
		 * Check the PHP version and give a useful error message if the user's version is less than the required version
		 *
		 * @return boolean true if version check passed. If false, displays an error.
		 */
		public function P4EN_php_version_check() {
			if (version_compare(phpversion(), $this->P4EN_minimum_php_version) < 0)
				return false;
			return true;
		}

		/**
		 * Initialize internationalization (i18n) for this plugin.
		 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
		 */
		public function P4EN_init_i18n() {
			load_plugin_textdomain(P4EN_PLUGIN_TEXTDOMAIN, false, P4EN_PLUGIN_DIRNAME . '/languages/');
		}
	}

}
