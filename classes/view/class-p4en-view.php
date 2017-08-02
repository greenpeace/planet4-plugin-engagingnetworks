<?php

if ( ! class_exists( 'P4EN_View' ) ) {

	/**
	 * Class P4EN_View
	 */
	final class P4EN_View {

		/** @var P4EN_View $instance */
		private static $instance;

		/**
		 * Singleton Class Pattern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			! isset( self::$instance ) and self::$instance = new self;
			return  self::$instance;
		}

		/**
		 * Creates the plugin's View object.
		 */
		private function __construct() {}

		/**
		 * Render the main dashboard page of the plugin.
		 */
		public function dashboard() {
			$this->view_template( __FUNCTION__, [] );
		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function settings() {
			$this->view_template( __FUNCTION__, [
				'settings' => get_option( 'p4en_settings' ),
				'langs' => P4EN_LANGUAGES,
			] );
		}

		/**
		 *
		 * @param $template_name
		 * @param $data
		 */
		public function view_template( $template_name, $data ) {
			Timber::render( [ $template_name . '.twig' ], $data );
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
}
