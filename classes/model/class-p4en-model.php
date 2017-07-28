<?php

if ( ! class_exists( 'P4EN_Model' ) ) {

	/**
	 * Class P4EN_Model
	 */
	class P4EN_Model {

		// Properties
		private static $instance;

		/**
		 * Singleton Class Patern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			! isset( self::$instance ) and self::$instance = new self;

			return self::$instance;
		}

		/**
		 * Creates the plugin's Model object.
		 */
		private function __construct() {
		}
	}
}
