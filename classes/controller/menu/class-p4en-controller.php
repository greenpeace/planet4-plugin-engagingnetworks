<?php

namespace P4EN\Controllers\Menu;

use P4EN\Views\P4EN_View;

if ( ! class_exists( 'P4EN_Controller' ) ) {

	/**
	 * Class P4EN_Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	class P4EN_Controller {

		const ERROR   = 0;
		const WARNING = 1;
		const NOTICE  = 2;
		const SUCCESS = 3;

		/** @var P4EN_View $view */
		protected $view;


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param P4EN_View $view The view object.
		 */
		public function __construct( P4EN_View $view ) {
			$this->view = $view;
		}

		/**
		 * Hooks the method that Creates the menu item for the current controller.
		 */
		public function load() {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
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
