<?php

if ( ! class_exists( 'P4EN_Controller' ) ) {

	/**
	 * Class P4EN_Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	class P4EN_Controller {

		/** @var P4EN_View $view */
		protected $view;
		/** @var P4EN_Pages_Datatable_Controller $pages_datatable_controller */
		protected $pages_datatable_controller;


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param P4EN_View $view The view object.
		 */
		public function __construct( P4EN_View $view ) {
			$this->view = $view;
			$this->pages_datatable_controller = new P4EN_Pages_Datatable_Controller( $view );
		}

		/**
		 * Create the menu for the plugin
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {

				add_menu_page(
					'EngagingNetworks',
					'EngagingNetworks',
					'edit_pages',
					P4EN_PLUGIN_SLUG_NAME,
					array( $this, 'prepare_pages' ),
					P4EN_ADMIN_DIR . 'images/logo_menu_page_16x16.jpg'
				);

				add_submenu_page(
					P4EN_PLUGIN_SLUG_NAME,
					__( 'EN Pages DataTable', 'planet4-engagingnetworks' ),
					__( 'EN Pages DataTable', 'planet4-engagingnetworks' ),
					'edit_pages',
					'pages-datatable',
					array( $this->pages_datatable_controller, 'prepare_pages_datatable' )
				);

				if ( current_user_can( 'manage_options' ) ) {

					add_submenu_page(
						P4EN_PLUGIN_SLUG_NAME,
						__( 'Settings', 'planet4-engagingnetworks' ),
						__( 'Settings', 'planet4-engagingnetworks' ),
						'manage_options',
						'settings',
						array( $this, 'prepare_settings' )
					);
				} else {
					wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'planet4-engagingnetworks' ),'Permission Denied Error',
						array(
							'response' => WP_Http::OK,
							'back_link' => true,
						)
					);
				}

				add_action( 'admin_init', array( $this, 'register_settings' ) );
			}
		}

		/**
		 * Loads the saved language.
		 */
		public function set_locale() : string {
			$main_settings = get_option( 'p4en_main_settings' );
			return isset( $main_settings['p4en_lang'] ) ? $main_settings['p4en_lang'] : '';
		}

		/**
		 * Pass all needed data to the view object for the main page.
		 */
		public function prepare_pages() {
			$this->view->pages( [
				'pages' => [],
			] );
		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function prepare_settings() {
			$this->view->settings( [
				'settings' => get_option( 'p4en_main_settings' ),
				'available_languages' => P4EN_LANGUAGES,
			] );
		}

		/**
		 * Register and store the settings and their data.
		 */
		public function register_settings() {
			$args = array(
				'type'              => 'string',
				'group'             => 'p4en_main_settings_group',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_main_settings_group', 'p4en_main_settings', $args );

			$args2 = array(
				'type'              => 'string',
				'group'             => 'p4en_pages_settings_group',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_pages_settings_group', 'p4en_pages_settings', $args2 );
		}

		/**
		 * Validates and sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
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
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			$has_errors = false;

			if ( $settings ) {
				if ( isset( $settings['p4en_public_api'] ) && 36 !== strlen( $settings['p4en_public_api'] ) ) {
					add_settings_error(
						'p4en_main_settings-p4en_public_api',
						esc_attr( 'p4en_main_settings-p4en_public_api' ),
						__( 'Invalid value for Public API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
				if ( isset( $settings['p4en_private_api'] ) && 36 !== strlen( $settings['p4en_private_api'] ) ) {
					add_settings_error(
						'p4en_main_settings-p4en_private_api',
						esc_attr( 'p4en_main_settings-p4en_private_api' ),
						__( 'Invalid value for Private API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
			}
			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
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
	}
}
