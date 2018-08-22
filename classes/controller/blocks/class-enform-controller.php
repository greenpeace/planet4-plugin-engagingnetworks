<?php

namespace P4EN\Controllers\Blocks;

use P4EN\Controllers\Ensapi_Controller;
use P4EN\Controllers\Menu\Pages_Controller;
use P4EN\Models\Fields_Model;

if ( ! class_exists( 'ENForm_Controller' ) ) {

	/**
	 * Class ENForm_Controller
	 *
	 * @package P4EN\Controllers\Blocks
	 */
	class ENForm_Controller extends Controller {

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
			$pages   = $ens_api->get_pages_by_types_status( self::ENFORM_PAGE_TYPES, 'live' );
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
					'label'       => __( 'Engaging Network Live Pages', 'planet4-engagingnetworks' ),
					'description' => $pages ? __( 'Select the Live EN page that this form will be submitted to.', 'planet4-engagingnetworks' ) : __( 'Check your EngagingNetworks settings!', 'planet4-engagingnetworks' ),
					'attr'        => 'en_page_id',
					'type'        => 'select',
					'options'     => $options,
				],
			];

			$available_fields = ( new Fields_Model() )->get_fields();

			if ( $available_fields ) {
				foreach ( $available_fields as $available_field ) {
					$args = [
						'label'       => $available_field['name'],
						'description' => $available_field['label'],
						'attr'        => strtolower( $available_field['name'] . '_' . $available_field['label'] . '_' . $available_field['type'] . '_' . $available_field['id'] ),
						'type'        => 'checkbox',
					];
					if ( $available_field['mandatory'] ) {
						$args['disabled'] = 'true';
						$args['value']    = 'true';
					}
					$fields[] = $args;
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

			$fields = $this->ignore_unused_attributes( $fields, $shortcode_tag );
			if ( $fields ) {
				foreach ( $fields as $name => $value ) {
					if ( 'en_page_id' !== $name ) {
						$attr_parts      = explode( '_', $name );
						$fields[ $name ] = [
							'label' => $attr_parts[1],
							'type'  => $attr_parts[2],
							'id'    => $attr_parts[3],
							'value' => $value,
						];
					}
				}
			}
			$data = [
				'fields'    => $fields,
				'countries' => [
					__( 'Greece',      'planet4-engagingnetworks' ),
					__( 'Netherlands', 'planet4-engagingnetworks' ),
				],
				'domain' => 'planet4-engagingnetworks',
			];

			// Shortcode callbacks must return content, hence, output buffering	here.
			ob_start();
			$this->view->block( self::BLOCK_NAME, $data, 'twig', P4EN_INCLUDES_DIR );

			return ob_get_clean();
		}
	}
}
