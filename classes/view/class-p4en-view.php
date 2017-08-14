<?php

if ( ! class_exists( 'P4EN_View' ) ) {

	/**
	 * Class P4EN_View
	 */
	class P4EN_View {

		private $template_dir = P4EN_INCLUDES_DIR;
		/**
		 * Creates the plugin's View object.
		 */
		public function __construct() {}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 *
		 * @param array|string $template_name
		 * @param array  $data
		 * @param string $sub_dir
		 *
		 * @return bool|string The returned output
		 */
		private function get_template( $template_name, $data, $sub_dir = '' ) {
			return Timber::compile( [ $this->template_dir . $sub_dir . $template_name . '.twig' ], $data );
		}

		/**
		 * Render the main page of the plugin.
		 * @param $data array All the data needed to render the template.
		 *
		 * @return bool|string The returned output
		 */
		public function get_pages( $data ) {
			return $this->get_template( __FUNCTION__, $data );
		}

		/**
		 * Render the settings page of the plugin.
		 * @param $data array All the data needed to render the template.
		 *
		 * @return bool|string The returned output
		 */
		public function get_settings( $data ) {
			return $this->get_template( __FUNCTION__, $data );
		}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 *
		 * @param array|string $template_name
		 * @param array  $data
		 * @param string $sub_dir
		 */
		private function view_template( $template_name, $data, $sub_dir = '' ) {
			Timber::render( [ $this->template_dir . $sub_dir . $template_name . '.twig' ], $data );
		}

		/**
		 * Render the main page of the plugin.
		 * @param $data array All the data needed to render the template.
		 */
		public function pages( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Render the main page of the plugin.
		 * @param $data array All the data needed to render the template.
		 */
		public function pages_datatable( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Render the settings page of the plugin.
		 * @param $data array All the data needed to render the template.
		 */
		public function settings( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}
	}
}
