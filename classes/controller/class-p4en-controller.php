<?php

if ( ! class_exists( 'P4EN_Controller' ) ) {

	/**
	 * Class P4EN_Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	class P4EN_Controller {

		/** @var P4EN_View $view */
		private $view;


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param P4EN_View $view
		 */
		public function __construct( P4EN_View $view ) {
			$this->view = $view;
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
					wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'planet4-engagingnetworks' ),'Permission Denied Error',
						array(
							'response' => 200,
							'back_link' => true,
						)
					);
				}

				add_action( 'admin_init', array( $this, 'register_settings' ) );
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
		 * Loads the saved language.
		 */
		public function load_locale() : string {
			$settings = get_option( 'p4en_settings' );
			return isset( $settings['p4en_lang'] ) ? $settings['p4en_lang'] : '';
		}

		/**
		 * Register and store the settings and their data.
		 */
		function register_settings() {
			$args = array(
				'type'              => 'string',
				'group'             => 'p4en_options',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_options', 'p4en_settings', $args );
		}

		/**
		 * Validates and sanitizes the settings input.
		 * @param $settings array
		 *
		 * @return mixed
		 */
		public function valitize( $settings ) : array {
			if ( $this->validate( $settings ) ) {
				$this->sanitize( $settings );
			}

			return $settings;
		}

		/**
		 * Validates the settings input.
		 * @param $settings array
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			if ( $settings ) {

				$has_errors = false;
				if ( 36 !== strlen( $settings['p4en_public_api'] ) ) {
					add_settings_error(
						'p4en_settings-p4en_public_api',
						esc_attr( 'p4en_settings-p4en_public_api' ),
						__( 'Invalid value for Public API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
				if ( 36 !== strlen( $settings['p4en_private_api'] ) ) {
					add_settings_error(
						'p4en_settings-p4en_private_api',
						esc_attr( 'p4en_settings-p4en_private_api' ),
						__( 'Invalid value for Private API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
				if ( $has_errors ) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Sanitizes the settings input.
		 * @param $settings array
		 *
		 * @return array
		 */
		public function sanitize( $settings ) : array {
			if ( $settings ) {
				foreach ( $settings as $name => $setting ) {
					$settings[ $name ] = sanitize_text_field( $setting );
				}
			}
			return $settings;
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
	}
}
