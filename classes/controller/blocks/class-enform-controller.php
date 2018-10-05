<?php

namespace P4EN\Controllers\Blocks;

use P4EN\Controllers\Ensapi_Controller;
use P4EN\Controllers\Menu\Pages_Datatable_Controller;

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
			$pages   = [];
			$options = [];

			// Get EN pages only on admin panel.
			if ( is_admin() ) {
				$main_settings = get_option( 'p4en_main_settings' );

				if ( isset( $main_settings['p4en_private_api'] ) ) {
					$ens_private_token = $main_settings['p4en_private_api'];
					$ens_api           = new Ensapi_Controller( $ens_private_token );
					$pages             = $ens_api->get_pages_by_types_status( self::ENFORM_PAGE_TYPES, 'live' );
					uasort( $pages, function ( $a, $b ) {
						return ( $a['name'] ?? '' ) <=> ( $b['name'] ?? '' );
					} );
				}

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
							'label'   => Pages_Datatable_Controller::SUBTYPES[ $type ]['subType'],
							'options' => $group_options,
						];
					}
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

			$available_fields = $this->sync_fields();

			if ( $available_fields ) {
				foreach ( $available_fields as $available_field ) {
					$attr_parts = [
						$available_field['id'],
						$available_field['name'],
						( $available_field['mandatory'] ? 'true' : 'false' ),
						str_replace( ' ', '-', $available_field['label'] ),
						$available_field['type'],
					];

					$args = [
						'label'       => $available_field['name'],
						'description' => $available_field['label'],
						'attr'        => strtolower( implode( '_', $attr_parts ) ),
						'type'        => 'checkbox',
					];
					if ( $available_field['mandatory'] ) {
						$args['value'] = 'true';
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
		 * Get all the data that will be needed to render the block correctly.
		 *
		 * @param array  $fields This is the array of fields of the block.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode tag of the block.
		 *
		 * @return array The data to be passed in the View.
		 */
		public function prepare_data( $fields, $content, $shortcode_tag ) : array {

			$fields = $this->ignore_unused_attributes( $fields, $shortcode_tag );
			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					if ( 'en_page_id' !== $key ) {
						$attr_parts     = explode( '_', $key );
						$fields[ $key ] = [
							'id'        => $attr_parts[0],
							'name'      => $attr_parts[1],
							'mandatory' => $attr_parts[2],
							'label'     => str_replace( '-', ' ', $attr_parts[3] ),
							'type'      => $attr_parts[4],
							'value'     => $value,
						];
					}
				}
			}

			$data = [];
			$this->handle_submit( $data );

			$data = array_merge( $data, [
				'fields'          => $fields,
				'second_page_msg' => __( 'Thanks for signing!', 'planet4-engagingnetworks' ),
				'domain'          => 'planet4-engagingnetworks',
			] );

			return $data;
		}

		/**
		 * Sync supporter fields between EN and Planet4.
		 *
		 * @return array Associative array of fields if sync was successful or empty array otherwise.
		 */
		public function sync_fields() : array {
			$main_settings = get_option( 'p4en_main_settings' );

			if ( isset( $main_settings['p4en_private_api'] ) ) {
				$ens_private_token = $main_settings['p4en_private_api'];
				$ens_api           = new Ensapi_Controller( $ens_private_token );
				$response          = $ens_api->get_supporter_fields();

				if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
					$supporter_fields = json_decode( $response['body'], true );

					foreach ( $supporter_fields as $field_data ) {
						if ( 'Not Tagged' !== $field_data['tag'] ) {
							$available_fields[] = [
								'id'        => $field_data['id'],
								'name'      => $field_data['property'],
								'mandatory' => false,
								'label'     => $field_data['name'],
								'type'      => strpos( $field_data['property'], 'country' ) === false ? 'text' : 'country',
							];
						}
					}
					return $available_fields;
				}
			}
			return [];
		}

		/**
		 * Handle form submit.
		 *
		 * @param array $data The data that we will get from the form submission.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function handle_submit( &$data ) : bool {
			// CSRF protection.
			$nonce_action          = 'enform_submit';
			$nonce                 = wp_create_nonce( $nonce_action );
			$data['nonce_action']  = $nonce_action;
			$data['enform_submit'] = 0;
			$data['error_msg']     = '';

			if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

				if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
					$data['error_msg'] = __( 'Invalid nonce!', 'planet4-engagingnetworks' );
					return false;
				} else {
					$en_page_id = $_POST['en_page_id'];
					$result = $this->valitize( [
						'en_page_id' => $en_page_id,
					] );

					if ( false === $result ) {
						$data['error_msg'] = __( 'Invalid input!', 'planet4-engagingnetworks' );
						return false;
					}

					$main_settings = get_option( 'p4en_main_settings' );
					if ( isset( $main_settings['p4en_private_api'] ) ) {
						$ens_private_token = $main_settings['p4en_private_api'];
						$ens_api           = new Ensapi_Controller( $ens_private_token );

						$fields = [];
						foreach ( $_POST as $key => $value ) {
							if ( false !== strpos( $key, 'supporter_' ) ) {
								$fields[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
							}
						}

						$response = $ens_api->process_page( $en_page_id, $fields );
						if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
							$data = json_decode( $response['body'], true );
							$data['enform_submit'] = 1;
						} else {
							$data['error_msg'] = $response;
							return false;
						}
					}
				}
			}
			return true;
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $input ) : bool {
			if ( ! isset( $input['en_page_id'] ) || $input['en_page_id'] <= 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {
			if ( isset( $input['en_page_id'] ) ) {
				$input['en_page_id'] = sanitize_text_field( $input['en_page_id'] );
			}
		}
	}
}
