<?php

namespace P4EN\Controllers\Menu;

if ( ! class_exists( 'Fields_Settings_Controller' ) ) {

	/**
	 * Class Fields_Settings_Controller
	 */
	class Fields_Settings_Controller extends Pages_Controller {


		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			if ( current_user_can( 'manage_options' ) ) {
				add_submenu_page(
					P4EN_PLUGIN_SLUG_NAME,
					__( 'Field Settings', 'planet4-engagingnetworks' ),
					__( 'Field Settings', 'planet4-engagingnetworks' ),
					'manage_options',
					'fields-settings',
					array( $this, 'prepare_page' )
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the page.
		 */
		public function prepare_page() {

			add_action( 'admin_print_footer_scripts', [ $this, 'print_admin_footer_scripts' ], 1 );
			wp_register_script( 'en-app', P4EN_ADMIN_DIR . '/js/en_app.js',
				[
					'jquery',
					'wp-api',
					'wp-backbone',
				], '0.1', true );
			wp_enqueue_script( 'en-app' );


			$data   = [];

			$data = array_merge( $data, [
				'messages'       => $this->messages,
				'domain'         => 'planet4-engagingnetworks',
			] );

			$this->view->view_template( 'fields_settings', $data );
		}

		/**
		 * Load underscore templates to footer.
		 */
		public function print_admin_footer_scripts() {
			echo $this->get_template( 'fields_settings' );
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		public function sanitize( &$settings ) {
		}
	}
}
