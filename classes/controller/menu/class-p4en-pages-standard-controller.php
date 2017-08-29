<?php

namespace P4EN\Controllers\Menu;

if ( ! class_exists( 'P4EN_Pages_Datatable_Controller' ) ) {

	/**
	 * Class P4EN_Pages_Standard_Controller
	 */
	class P4EN_Pages_Standard_Controller extends P4EN_Pages_Controller {

		/**
		 * Create menu/submenu entry.
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
			}
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
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			// TODO: Implement validate() method.
			$has_errors = false;
			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		public function sanitize( &$settings ) {
			// TODO: Implement sanitize() method.
		}
	}
}