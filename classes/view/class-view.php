<?php
/**
 * View class
 *
 * @package P4EN
 */

namespace P4EN\Views;

use Timber\Timber;

if ( ! class_exists( 'View' ) ) {

	/**
	 * Class View
	 */
	class View {

		/**
		 * Template dir
		 *
		 * @var string $template_dir The path to the template files.
		 */
		private $template_dir = P4EN_INCLUDES_DIR;

		/**
		 * Creates the plugin's View object.
		 */
		public function __construct() {}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 *
		 * @param array|string $template_name The file name of the template to render.
		 * @param array        $data The data to pass to the template.
		 * @param string       $sub_dir The path to a subdirectory where the template is located (relative to $template_dir).
		 *
		 * @return bool|string The returned output
		 */
		private function get_template( $template_name, $data, $sub_dir = '' ) {
			Timber::$locations = $this->template_dir;
			return Timber::compile( [ $sub_dir . $template_name . '.twig' ], $data );
		}

		/**
		 * Render the main page of the plugin.
		 *
		 * @param array $data All the data needed to render the template.
		 *
		 * @return bool|string The returned output
		 */
		public function get_pages( $data ) : string {
			return $this->get_template( __FUNCTION__, $data );
		}

		/**
		 * Render the settings page of the plugin.
		 *
		 * @param array $data All the data needed to render the template.
		 *
		 * @return bool|string The returned output
		 */
		public function get_settings( $data ) {
			return $this->get_template( __FUNCTION__, $data );
		}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 *
		 * @param array|string $template_name The file name of the template to render.
		 * @param array        $data The data to pass to the template.
		 * @param string       $sub_dir The path to a subdirectory where the template is located (relative to $template_dir).
		 */
		public function view_template( $template_name, $data, $sub_dir = '' ) {
			Timber::$locations = $this->template_dir;
			Timber::render( [ $sub_dir . $template_name . '.twig' ], $data );
		}

		/**
		 * Render the main page of the plugin.
		 *
		 * @param array $data All the data needed to render the template.
		 */
		public function pages( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Render the main page of the plugin.
		 *
		 * @param array $data All the data needed to render the template.
		 */
		public function pages_datatable( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Render the settings page of the plugin.
		 *
		 * @param array $data All the data needed to render the template.
		 */
		public function settings( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}

		/**
		 * Uses the appropriate templating engine to render a template file.
		 *
		 * @param array|string $template_name The file name of the template to render.
		 * @param array        $data The data to pass to the template.
		 * @param string       $template_ext The extension of the template (php, twig, ...).
		 * @param string       $relevant_dir The path to a subdirectory where the template is located (relative to $template_dir).
		 */
		public function block( $template_name, $data, $template_ext = 'twig', $relevant_dir = '' ) {

			if ( 'twig' === $template_ext ) {
				Timber::$locations = $this->template_dir;
				Timber::render( [ $relevant_dir . $template_name . '.' . $template_ext ], $data );
			} else {
				include_once $this->template_dir . $relevant_dir . $template_name . '.' . $template_ext;
			}
		}

		/**
		 * Displays a message.
		 *
		 * @param array $data All the data needed to render the template.
		 */
		public function message( $data ) {
			$this->view_template( __FUNCTION__, $data );
		}
	}
}
