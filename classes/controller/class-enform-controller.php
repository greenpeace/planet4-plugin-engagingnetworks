<?php

namespace P4EN\Controllers;

use P4BKS\Controllers\Blocks\Controller as Block_Controller;

if ( ! class_exists( 'ENForm_Controller' ) ) {

	/**
	 * Class ENForm_Controller
	 *
	 * @package P4EN\Controllers
	 */
	class ENForm_Controller extends Block_Controller {

		/** @const string BLOCK_NAME */
		const BLOCK_NAME = 'enform';

		/**
		 * Shortcode UI setup for the ENForm shortcode.
		 *
		 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
		 */
		public function prepare_fields() {
			$fields = [];

			// Define the Shortcode UI arguments.
			$shortcode_ui_args = [
				'label'         => __( 'Engaging Networks Form', 'planet4-blocks-backend' ),
				'listItemImage' => '<img src="' . esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enform.png' ) . '" />',
				'attrs'         => $fields,
				'post_type'     => P4EN_ALLOWED_PAGETYPE,
			];

			shortcode_ui_register_for_shortcode( 'shortcake_' . self::BLOCK_NAME, $shortcode_ui_args );
		}

		/**
		 * Callback for the shortcode.
		 * It renders the shortcode based on supplied attributes.
		 *
		 * @param array  $fields This contains array of all data added.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode block of campaign thumbnail.
		 *
		 * @since 0.1.0
		 *
		 * @return string All the data used for the html.
		 */
		public function prepare_template( $fields, $content, $shortcode_tag ) : string {

			$data = [
				'fields' => $fields,
			];

			// Shortcode callbacks must return content, hence, output buffering	here.
			ob_start();
			$this->view->block( self::BLOCK_NAME, $data, 'twig', P4EN_INCLUDES_DIR );

			return ob_get_clean();
		}
	}
}
