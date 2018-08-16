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
			$pages   = $ens_api->get_pages_by_types( self::ENFORM_PAGE_TYPES );
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
					$args = [
						'label'       => $available_field['name'],
						'description' => $available_field['label'],
						'attr'        => $available_field['id'] . '_' . $available_field['name'] . '_' . $available_field['type'] . '_' . $available_field['label'],
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

			$fields = $this->filter_attributes( $fields, $shortcode_tag );

			foreach ( $fields as $name => $value ) {
				if ( 'en_page_id' !== $name ) {
					$attr_parts      = explode( '_', $name );
					$fields[ $name ] = [
						'id'    => $attr_parts[0],
						'type'  => $attr_parts[2],
						'label' => $attr_parts[3],
					];
				}
			}
			$data = [
				'fields'    => $fields,
				'countries' => [
					'Greece',
				],
				'domain'    => 'planet4-engagingnetworks',
			];

			// Shortcode callbacks must return content, hence, output buffering	here.
			ob_start();
			$this->view->block( self::BLOCK_NAME, $data, 'twig', P4EN_INCLUDES_DIR );

			return ob_get_clean();
		}

		/**
		 * Clear the fields from any user defined attributes that are not being used by the block.
		 *
		 * @param array  $fields This contains array of all data added.
		 * @param string $shortcode_tag The shortcode block of campaign thumbnail.
		 *
		 * @return array The valid fields.
		 */
		public function filter_attributes( $fields, $shortcode_tag ) : array {
			// Get all the attribute keys that are used by the block.
			$shortcode_object    = \Shortcode_UI::get_instance()->get_shortcode( $shortcode_tag );
			$shortcode_attrs     = is_array( $shortcode_object ) && is_array( $shortcode_object['attrs'] ) ? $shortcode_object['attrs'] : [];
			$shortcode_attr_keys = wp_list_pluck( $shortcode_attrs, 'attr' );

			// Filter out any attributes that are still inside the shortcode but are not being used by the block.
			foreach ( $fields as $index => $value ) {
				if ( ! in_array( $index, $shortcode_attr_keys, true ) ) {
					unset( $fields[ $index ] );
				}
			}

			return $fields;
		}
	}
}
