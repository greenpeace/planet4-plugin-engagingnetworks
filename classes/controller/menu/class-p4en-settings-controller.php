<?php

namespace P4EN\Controllers\Menu;

if ( ! class_exists( 'P4EN_Settings_Controller' ) ) {

	/**
	 * Class P4EN_Settings_Controller
	 */
	class P4EN_Settings_Controller extends P4EN_Controller {

		/**
		 * Hooks the method that Creates the menu item for the current controller.
		 */
		public function load() {
			parent::load();
			add_filter( 'locale', array( $this, 'set_locale' ), 11, 1 );
		}

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

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
						'response' => \WP_Http::OK,
						'back_link' => true,
					)
				);
			}

			add_action( 'admin_init', array( $this, 'register_settings' ) );
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
		 * Loads the saved language.
		 */
		public function set_locale() : string {
			$main_settings = get_option( 'p4en_main_settings' );
			return isset( $main_settings['p4en_lang'] ) ? $main_settings['p4en_lang'] : '';
		}
	}
}