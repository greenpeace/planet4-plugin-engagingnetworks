<?php

namespace P4EN\Controllers;

use P4BKS\Controllers\Blocks\Controller as Block_Controller;
use P4EN\Controllers\Menu\Pages_Controller;
use P4EN\Models\Fields_Model;

if ( ! class_exists( 'ENForm_Controller' ) ) {

	/**
	 * Class ENForm_Controller
	 *
	 * @package P4EN\Controllers
	 */
	class ENForm_Controller extends Block_Controller {

		/** @const string BLOCK_NAME */
		const BLOCK_NAME = 'enform';
		/** @const array ENFORM_PAGE_TYPES */
		const ENFORM_PAGE_TYPES = [ 'PET', 'ND' ];

		/**
		 * Shortcode UI setup for the ENForm shortcode.
		 *
		 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
		 */
		public function prepare_fields() {
			$ens_api = new Ensapi_Controller();
			$pages = $ens_api->get_pages_by_types( self::ENFORM_PAGE_TYPES );
			uasort( $pages, function ( $a, $b ) {
				return ($a['name'] ?? '') <=> ($b['name'] ?? '');
			} );

			$options = [
				[
					'value' => '0',
					'label' => __( '- Select Page -', 'planet4-engagingnetworks' ),
				],
			];
			if ( $pages ) {
				foreach ( $pages as $type => $group_pages ) {
					$group_options = [];
					foreach ( $group_pages as $page ) {
						$group_options[] = [
							'value' => (string) $page['id'],
							'label' => (string) $page['name'],
						];
					}
					$options[] = [
						'label'   => Pages_Controller::SUBTYPES[ $type ]['subType'],
						'options' => $group_options,
					];
				}
			}

			$fields = [
				[
					'label'       => __( 'Engaging Network Pages', 'planet4-engagingnetworks' ),
					'description' => $pages ? __( 'Select the EN page that this form will be submitted to.', 'planet4-engagingnetworks' ) : __( 'Check your EngagingNetworks settings!', 'planet4-engagingnetworks' ),
					'attr'        => 'en_page_id',
					'type'        => 'select',
					'options'     => $options,
				],
			];

			$available_fields = ( new Fields_Model() )->get_fields();

			if ( $available_fields ) {
				foreach ( $available_fields as $available_field ) {
					$fields[] = [
						'label'       => $available_field['name'],
						'description' => $available_field['label'],
						'attr'        => $available_field['name'] . '_' . $available_field['id'],
						'type'        => 'checkbox',
					];
				}
			}

			// Define the Shortcode UI arguments.
			$shortcode_ui_args = [
				'label'         => __( 'Engaging Networks Form', 'planet4-engagingnetworks' ),
				'listItemImage' => '<img src="' . esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enform.png' ) . '" />',
				'attrs'         => $fields,
				'post_type'     => P4EN_ALLOWED_PAGETYPE,
			];

			shortcode_ui_register_for_shortcode( 'shortcake_' . self::BLOCK_NAME, $shortcode_ui_args );
		}

		/**
		 * Get all the data that will be needed to render the block correctly.
		 *
		 * @param array  $fields This is the array of fields of the block.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode tag of the block.
		 *
		 * @return array The data to be passed in the View.
		 */
		public function prepare_data( $fields, $content, $shortcode_tag ) : array {

			$data = [
				'fields' => $fields,
			];
			return $data;
		}

		/**
		 * Callback for the shortcode.
		 * It renders the shortcode based on supplied attributes.
		 *
		 * @param array  $fields This is the array of fields of this block.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode tag of the block.
		 *
		 * @since 0.1.0
		 *
		 * @return string All the data used for the html.
		 */
		public function prepare_template( $fields, $content, $shortcode_tag ) : string {
			$data = $this->prepare_data( $fields, $content, $shortcode_tag );
			// Shortcode callbacks must return content, hence, output buffering here.
			ob_start();
			$this->view->block( self::BLOCK_NAME, $data, 'twig', P4EN_INCLUDES_DIR );
			return ob_get_clean();
		}
	}
}
