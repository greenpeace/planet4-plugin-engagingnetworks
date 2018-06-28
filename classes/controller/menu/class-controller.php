<?php

namespace P4EN\Controllers\Menu;

use P4EN\Views\View;

if ( ! class_exists( 'Controller' ) ) {

	/**
	 * Class Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	abstract class Controller {

		const ERROR   = 0;
		const WARNING = 1;
		const NOTICE  = 2;
		const SUCCESS = 3;

		/** @var View $view */
		protected $view;
		/** @var array $messages */
		protected $messages = [];


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param View $view The view object.
		 */
		public function __construct( View $view ) {
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
		 * @return mixed Array if validation is ok, false if validation fails.
		 */
		public function valitize( $settings ) {
			if ( $this->validate( $settings ) ) {
				$this->sanitize( $settings );
				return $settings;
			} else {
				return $settings;
			}
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		abstract public function validate( $settings ) : bool;

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		abstract public function sanitize( &$settings );

		/**
		 * Display an escaped error message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function error( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Error', 'planet4-engagingnetworks' ),
					'type'    => self::ERROR,
					'classes' => 'p4en_error_message',
				] );
			}
		}

		/**
		 * Display an escaped warning message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function warning( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Warning', 'planet4-engagingnetworks' ),
					'type'    => self::WARNING,
					'classes' => 'p4en_warning_message',
				] );
			}
		}

		/**
		 * Display an escaped notice message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function notice( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Notice', 'planet4-engagingnetworks' ),
					'type'    => self::NOTICE,
					'classes' => 'p4en_notice_message',
				] );
			}
		}

		/**
		 * Display an escaped success message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function success( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Success', 'planet4-engagingnetworks' ),
					'type'    => self::SUCCESS,
					'classes' => 'p4en_success_message',
				] );
			}
		}

		/**
		 * Get underscore template from filesystem.
		 *
		 * @param string $template Template name.
		 *
		 * @return bool|string
		 */
		protected function get_template( $template ) {

			$template = P4EN_PLUGIN_DIR . '/admin/templates/' . $template . '.tpl.php';
			if ( file_exists( $template ) ) {
				$contents = file_get_contents( $template );

				return false !== $contents ? $contents : '';
			}

			return '';
		}
	}
}
