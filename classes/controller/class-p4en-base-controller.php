<?php

if ( ! class_exists( 'P4EN_Base_Controller' ) ) {

	/**
	 * Class P4EN_Base_Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	class P4EN_Base_Controller {

		/** @var P4EN_Base_Controller $instance */
		private static $instance;

		/**
		 * Singleton creational patern.
		 * Makes sure there is only one instance at all times.
		 */
		public static function get_instance() {

			! isset( self::$instance ) and self::$instance = new self;
			return  self::$instance;
		}

		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 */
		private function __construct() {}

		/**
		 *
		 */
		function register_settings() {
			$args = array(
				'type'              => 'string',
				'group'             => 'p4en_options',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_options', 'p4en_settings' );
		}

		/**
		 *
		 * @param $input
		 */
		public function valitize( $input ) {
			$this->validate( $input );
			$this->sanitize( $input );
		}

		/**
		 *
		 * @param $input
		 *
		 * @return bool
		 */
		public function validate( $input ) {
			return true;
		}

		/**
		 *
		 * @param $input
		 *
		 * @return mixed
		 */
		public function sanitize( $input ) {
			$input['p4en_lang'] = sanitize_text_field( $input['p4en_lang'] );
			$input['p4en_public_api'] = sanitize_text_field( $input['p4en_public_api'] );
			$input['p4en_private_api'] = sanitize_text_field( $input['p4en_private_api'] );
			return $input;
		}
	}
}
