<?php

if (!class_exists('P4EN_View')) {

	/**
	 * Class P4EN_View
	 */
	final class P4EN_View {

		// Properties
		private static $instance;

		/**
		 * Singleton Class Patern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			!isset( self::$instance ) AND self::$instance = new self;
			return  self::$instance;
		}

		/**
		 * Creates the plugin's controller object.
		 */
		private function __construct() {}

		/**
		 * Render the main dashboard page of the plugin.
		 */
		public function render_dashboard() {
			require_once P4EN_INCLUDES_DIR . '/dashboard.twig';
			new P4EN_Uninstall_Controller();

		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function render_settings() {
			require_once P4EN_INCLUDES_DIR . '/settings.twig';
		}
	}
}