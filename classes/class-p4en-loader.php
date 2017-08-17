<?php

if ( ! class_exists( 'P4EN_Loader' ) ) {

	/**
	 * Class P4EN_Loader
	 *
	 * This class checks requirements and if all are met then it hooks the plugin.
	 */
	final class P4EN_Loader {

		/** @var P4EN_Loader $instance */
		private static $instance;
		/** @var P4EN_Controller $controller */
		private $controller;
		/** @var string $required_php */
		private $required_php = P4EN_REQUIRED_PHP;
		/** @var array $required_plugins */
		private $required_plugins = P4EN_REQUIRED_PLUGINS;


		/**
		 * Singleton creational pattern.
		 * Makes sure there is only one instance at all times.
		 *
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
		private function hook_plugin() {
			add_action( 'admin_menu', array( $this, 'load_i18n' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
			add_action( 'admin_menu', array( $this->controller, 'create_admin_menu' ) );
			add_filter( 'locale', array( $this->controller, 'set_locale' ), 11, 1 );

			// Provide hook for other plugins.
			do_action( 'p4en_action_loaded' );
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
						$this->hook_plugin();
					} else {
						deactivate_plugins( P4EN_PLUGIN_BASENAME );
						wp_die(
							'<div class="error fade">' .
							'<u>' . esc_html__( 'Plugin Requirements Error!', 'planet4-engagingnetworks' ) . '</u><br /><br />' . esc_html( P4EN_PLUGIN_NAME ) . esc_html__( ' requires a newer version of the following plugin.', 'planet4-engagingnetworks' ) . '<br />' .
							'<br/>' . esc_html__( 'Minimum required version of ', 'planet4-engagingnetworks' ) . esc_html( $plugin['Name'] ) . ': <strong>' . esc_html( $plugin['min_version'] ) . '</strong>' .
							'<br/>' . esc_html__( 'Installed version of ', 'planet4-engagingnetworks' ) . esc_html( $plugin['Name'] ) . ': <strong>' . esc_html( $plugin['Version'] ) . '</strong>' .
							'</div>', 'Plugin Requirements Error', array(
								'response' => WP_Http::OK,
								'back_link' => true,
							)
						);
					}
				} else {
					deactivate_plugins( P4EN_PLUGIN_BASENAME );
					wp_die(
						'<div class="error fade">' .
						'<u>' . esc_html__( 'Plugin Requirements Error!', 'planet4-engagingnetworks' ) . '</u><br /><br />' . esc_html( P4EN_PLUGIN_NAME . __( ' requires a newer version of PHP.', 'planet4-engagingnetworks' ) ) . '<br />' .
						'<br/>' . esc_html__( 'Minimum required version of PHP: ', 'planet4-engagingnetworks' ) . '<strong>' . esc_html( $this->required_php ) . '</strong>' .
						'<br/>' . esc_html__( 'Running version of PHP: ', 'planet4-engagingnetworks' ) . '<strong>' . esc_html( phpversion() ) . '</strong>' .
						'</div>', 'Plugin Requirements Error', array(
							'response' => WP_Http::OK,
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
		 *
		 * @param array $plugin Will contain information for those plugins whose requirements are not met.
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
		 * Load assets only on the admin pages of the plugin.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function load_admin_assets( $hook ) {
			// Load the assets only on the plugin's pages.
			if ( strpos( $hook, P4EN_PLUGIN_SLUG_NAME ) === false ) {
				return;
			}

			wp_enqueue_script( 'p4en_jquery', '//code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );
			if ( strpos( $hook, 'pages-datatable' ) !== false ) {
				wp_enqueue_style( 'p4en_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', array(), '4.0.0-alpha.6' );
				wp_enqueue_style( 'p4en_datatables_bootstrap', 'https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css', array( 'p4en_bootstrap' ), '1.10.15' );

				wp_enqueue_script( 'p4en_datatables', 'https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js', array( 'p4en_jquery' ), '1.10.15', true );
				wp_enqueue_script( 'p4en_datatables_bootstrap', 'https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js', array( 'p4en_datatables' ), '1.10.15', true );
			}
			wp_enqueue_style( 'p4en_admin_style', P4EN_ADMIN_DIR . 'css/admin.css', array(), '0.1' );
			wp_enqueue_script( 'p4en_admin_script', P4EN_ADMIN_DIR . 'js/admin.js', array(), '0.1', true );
		}

		/**
		 * Load internationalization (i18n) for this plugin.
		 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
		 */
		public function load_i18n() {
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

} else {
	deactivate_plugins( P4EN_PLUGIN_BASENAME );
	wp_die(
		'<div class="error fade">' .
		'<u>' . esc_html__( 'Plugin Conflict Error!', 'planet4-engagingnetworks' ) . '</u><br /><br />' . esc_html__( 'Class P4EN_Loader already exists.', 'planet4-engagingnetworks' ) . '<br />' .
		'</div>', 'Plugin Conflict Error', array(
			'response' => WP_Http::OK,
			'back_link' => true,
		)
	);
}