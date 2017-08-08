<?php

if ( ! class_exists( 'P4EN_View' ) ) {

	/**
	 * Class P4EN_View
	 */
	class P4EN_View {

		/**
		 * Creates the plugin's View object.
		 */
		public function __construct() {}

		/**
		 * Render the main page of the plugin.
		 */
		public function pages() {
			$this->view_template( __FUNCTION__, [] );
		}

		/**
		 * Render the settings page of the plugin.
		 * @param $data array All the data needed to render the template.
		 */
		public function settings( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 * @param $template_name
		 * @param $data
		 */
		private function view_template( $template_name, $data ) {
			Timber::render( [ $template_name . '.twig' ], $data );
		}
	}
}
