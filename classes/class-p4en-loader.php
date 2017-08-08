<?php

if ( ! class_exists( 'P4EN_Loader' ) ) {

	/**
	 * Class P4EN_Loader
	 *
	 * This class loads the plugin.
	 */
	final class P4EN_Loader {

		/** @var P4EN_Loader $instance */
		private static $instance;
		/** @var string $required_php */
		private $required_php = P4EN_REQUIRED_PHP;
		/** @var array $required_plugins */
		private $required_plugins = P4EN_REQUIRED_PLUGINS;
		/** @var P4EN_Controller $controller */
		private $controller;


		/**
		 * Singleton creational pattern.
		 * Makes sure there is only one instance at all times.
		 * @param P4EN_Controller $controller   The main controller of the plugin.
		 *
		 * @return P4EN_Loader
		 */
		public static function get_instance( P4EN_Controller $controller ) : P4EN_Loader {
			! isset( self::$instance ) and self::$instance = new self( $controller );
			return  self::$instance;
		}

		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param P4EN_Controller $controller   The main controller of the plugin.
		 */
		private function __construct( P4EN_Controller $controller ) {
			$this->controller = $controller;
			$this->check_requirements();
		}

		/**
		 * Hooks the plugin.
		 */
		public function hook_plugin() {

			Timber::$locations = P4EN_INCLUDES_DIR;

			add_action( 'admin_menu', array( $this->controller, 'load_admin_menu' ) );
			add_action( 'admin_menu', array( $this->controller, 'init_i18n' ) );
			add_action( 'admin_enqueue_scripts', array( $this->controller, 'load_admin_assets' ) );
			add_filter( 'locale', array( $this->controller, 'load_locale' ), 11, 1 );

			// Provide hook for other plugins.
			do_action( 'planet4_engagingnetworks_loaded' );
		}

		/**
		 * Checks plugin requirements.
		 * If requirements are met then hook the plugin.
		 */
		private function check_requirements() {

			if ( is_admin() ) {         // If we are on the admin panel.
				// Run the version check. If it is successful, continue with hooking under 'init' the initialization of this plugin.
				if ( $this->check_required_php() ) {
					if ( $this->check_required_plugins( $plugin ) ) {
						add_action( 'init', array( $this, 'hook_plugin' ) );
					} else {
						deactivate_plugins( P4EN_PLUGIN_BASENAME );
						wp_die(
							'<div class="updated fade">' .
							__( '<u>Plugin Requirements Error!</u><br /><br />', 'planet4-engagingnetworks' ) . P4EN_PLUGIN_NAME . __( ' requires a newer version of the following plugin.<br />', 'planet4-engagingnetworks' ) .
							'<br/>' . __( 'Minimum required version of ' . $plugin['Name'] . ': ', 'planet4-engagingnetworks' ) . '<strong>' . $plugin['min_version'] . '</strong>' .
							'<br/>' . __( 'Installed version of ' . $plugin['Name'] . ': ', 'planet4-engagingnetworks' ) . '<strong>' . $plugin['Version'] . '</strong>' .
							'</div>', 'Plugin Requirements Error', array(
								'response' => 200,
								'back_link' => true,
							)
						);
					}
				} else {
					deactivate_plugins( P4EN_PLUGIN_BASENAME );
					wp_die(
						'<div class="updated fade">' .
						__( '<u>Plugin Requirements Error!</u><br /><br />', 'planet4-engagingnetworks' ) . P4EN_PLUGIN_NAME . __( ' requires a newer version of PHP.<br />', 'planet4-engagingnetworks' ) .
						'<br/>' . __( 'Minimum required version of PHP: ', 'planet4-engagingnetworks' ) . '<strong>' . $this->required_php . '</strong>' .
						'<br/>' . __( 'Running version of PHP: ', 'planet4-engagingnetworks' ) . '<strong>' . phpversion() . '</strong>' .
						'</div>', 'Plugin Requirements Error', array(
							'response' => 200,
							'back_link' => true,
						)
					);
				}
			}
		}

		/**
		 * Check if the server's php version is less than the required php version.
		 *
		 * @return bool true if version check passed or false otherwise.
		 */
		private function check_required_php() : bool {
			return version_compare( phpversion(), $this->required_php, '>=' );
		}

		/**
		 * Check if the version of a plugin is less than the required version.
		 * @param array $plugin Will contain information on the plugin that needs update.
		 * @return bool true if version check passed or false otherwise.
		 */
		private function check_required_plugins( &$plugin ) : bool {
			$required_plugins = $this->required_plugins;

			if ( is_array( $required_plugins ) && $required_plugins ) {
				foreach ( $required_plugins as $required_plugin ) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $required_plugin['rel_path'] );
					if ( ! is_plugin_active( $required_plugin['rel_path'] ) ||
					     ! version_compare( $plugin_data['Version'], $required_plugin['min_version'], '>=' ) ) {
						$plugin = array_merge( $plugin_data, $required_plugin );
						return false;
					}
				}
			}
			return true;
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

} else {
	deactivate_plugins( P4EN_PLUGIN_BASENAME );
	wp_die(
		'<div class="updated fade">' .
		__( '<u>Plugin Conflict Error!</u><br /><br />Class <strong>P4EN_Loader</strong> already exists.<br />', 'planet4-engagingnetworks' ) .
		'</div>', 'Plugin Conflict Error', array(
			'response' => 200,
			'back_link' => true,
		)
	);
}