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

		/**
		 * Shortcode UI setup for the ENForm shortcode.
		 *
		 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
		 */
		public function prepare_fields() {
			$pages = $this->get_pages( [ 'PET', 'ND' ] );
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

			$model = new Fields_Model();
			$available_fields = $model->get_fields();

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
		 * Retrieves all EN pages whose type is included in the $types array.
		 *
		 * @param array $types Array with the types of the EN pages to be retrieved.
		 *
		 * @return array Array with data of the retrieved EN pages.
		 */
		public function get_pages( $types ) : array {
			if ( $types ) {
				$pages         = [];
				$ens_api       = new Ensapi_Controller();
				$main_settings = get_option( 'p4en_main_settings' );

				$ens_auth_token = get_transient( 'ens_auth_token' );
				// If authentication token is not cached then authenticate again and cache the token.
				if ( false === $ens_auth_token ) {
					$ens_private_token = $main_settings['p4en_private_api'];
					$response          = $ens_api->authenticate( $ens_private_token );

					if ( is_array( $response ) && $response['body'] ) {
						// Communication with ENS API is authenticated.
						$body           = json_decode( $response['body'], true );
						$ens_auth_token = $body['ens-auth-token'];
						$expiration     = (int) ( $body['expires'] / 1000 ) - time();       // Time period in seconds to keep the ens_auth_token before refreshing. Typically 1 hour.
						set_transient( 'ens_auth_token', $ens_auth_token, $expiration - time() );
					}
				}
				foreach ( $types as $type ) {
					$params['type'] = $type;
					$response       = $ens_api->get_pages( $ens_auth_token, $params );
					if ( is_array( $response ) && $response['body'] ) {
						$pages[ $params['type'] ] = json_decode( $response['body'], true );
					}
				}
			}

			return $pages;
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
