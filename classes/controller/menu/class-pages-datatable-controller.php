<?php

namespace P4EN\Controllers\Menu;

use P4EN\Controllers\Ensapi_Controller;

if ( ! class_exists( 'Pages_Datatable_Controller' ) ) {

	/**
	 * Class Pages_Datatable_Controller
	 */
	class Pages_Datatable_Controller extends Pages_Controller {

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {
				add_menu_page(
					'EngagingNetworks',
					'EngagingNetworks',
					'edit_pages',
					P4EN_PLUGIN_SLUG_NAME,
					array( $this, 'prepare_pages_datatable' ),
					P4EN_ADMIN_DIR . 'images/logo_menu_page_16x16.jpg'
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the datatable page.
		 */
		public function prepare_pages_datatable() {
			$data   = [];
			$pages  = [];
			$params = [];
			$pages_settings = [];

			$current_user = wp_get_current_user();
			$validated = $this->handle_submit( $current_user, $data );

			if ( $validated ) {
				$pages_settings = get_user_meta( $current_user->ID, 'p4en_pages_datatable_settings', true );
				if ( isset( $pages_settings['p4en_pages_subtype'] ) && $pages_settings['p4en_pages_subtype'] ) {
					$params['type'] = $pages_settings['p4en_pages_subtype'];

					if ( isset( $pages_settings['p4en_pages_status'] ) && 'all' !== $pages_settings['p4en_pages_status'] ) {
						$params['status'] = $pages_settings['p4en_pages_status'];
					}

					$ens_api = new Ensapi_Controller();
					$main_settings = get_option( 'p4en_main_settings' );

					if ( isset( $main_settings['p4en_private_api'] ) && $main_settings['p4en_private_api'] ) {
						// Check if the authentication API call is cached.
						$ens_auth_token = get_transient( 'ens_auth_token' );

						if ( false !== $ens_auth_token ) {
							$response = $ens_api->get_pages( $ens_auth_token, $params );

							if ( is_array( $response ) && $response['body'] ) {
								$pages = json_decode( $response['body'], true );
							} else {
								$this->error( $response );
							}
						} else {
							$ens_private_token = $main_settings['p4en_private_api'];
							$response = $ens_api->authenticate( $ens_private_token );

							if ( is_array( $response ) && $response['body'] ) {
								// Communication with ENS API is authenticated.
								$body           = json_decode( $response['body'], true );
								$ens_auth_token = $body['ens-auth-token'];
								// Time period in seconds to keep the ens_auth_token before refreshing. Typically 1 hour.
								$expiration     = time() + (int) ($body['expires']);

								set_transient( 'ens_auth_token', $ens_auth_token, $expiration );

								$response = $ens_api->get_pages( $ens_auth_token, $params );

								if ( is_array( $response ) && $response['body'] ) {
									$pages = json_decode( $response['body'], true );
								} else {
									$this->error( $response );
								}
							} else {
								$this->error( $response );
							}
						}
					} else {
						$this->warning( __( 'Plugin Settings are not configured well!', 'planet4-engagingnetworks' ) );
					}
				} else {
					$this->notice( __( 'Select Subtype', 'planet4-engagingnetworks' ) );
				}
			} else {
				$this->error( __( 'Changes are not saved!', 'planet4-engagingnetworks' ) );
			}

			$data = array_merge( $data, [
				'pages'          => $pages,
				'pages_settings' => $pages_settings,
				'subtypes'       => self::SUBTYPES,
				'statuses'       => self::STATUSES,
				'messages'       => $this->messages,
				'domain'         => 'planet4-engagingnetworks',
			] );

			$this->filter_pages_datatable( $data );
			// Provide hook for other plugins to be able to filter the datatable output.
			$data = apply_filters( 'p4en_filter_pages_datatable', $data );

			$this->view->pages_datatable( $data );
		}

		/**
		 * Handle form submit.
		 *
		 * @param $current_user
		 * @param $data
		 *
		 * @return bool Array if validation is ok, false if validation fails.
		 */
		public function handle_submit( $current_user, &$data ) : bool {
			// CSRF protection.
			$nonce_action = 'pages_datatable_submit';
			$nonce = wp_create_nonce( $nonce_action );
			$data['nonce_action'] = $nonce_action;
			$data['form_submit'] = 0;

			if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$data['form_submit']  = 1;

				if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
					$this->error( __( 'Nonce verification failed!', 'planet4-engagingnetworks' ) );
					return false;
				} else {
					$pages_datatable_settings = $_POST['p4en_pages_datatable_settings'];

					$pages_datatable_settings = $this->valitize( $pages_datatable_settings );
					if ( false === $pages_datatable_settings ) {
						return false;
					}

					update_user_meta( $current_user->ID, 'p4en_pages_datatable_settings', $pages_datatable_settings );

					$this->success( __( 'Changes saved!', 'planet4-engagingnetworks' ) );
				}
			}
			return true;
		}

		/**
		 * Filter the output for the datatable page.
		 *
		 * @param array $data The data array that will be passed to the View.
		 */
		public function filter_pages_datatable( &$data ) {

			if ( $data ) {
				foreach ( $data['pages'] as &$page ) {
					$page['campaignStatus'] = ucfirst( $page['campaignStatus'] );
					if ( ! $page['subType'] ) {
						$page['subType'] = strtoupper( $page['type'] );
					}

					switch ( $page['type'] ) {
						case 'dc':
							switch ( $page['subType'] ) {
								case 'DCF':
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/data/1' );
									break;
								case 'PET':
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
									break;
								default:
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
							}
							break;
						case 'nd':
							$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/donation/1' );
							break;
					}
				}
			}
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			$has_errors = false;
			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		public function sanitize( &$settings ) {
			if ( $settings ) {
				foreach ( $settings as $name => $setting ) {
					$settings[ $name ] = sanitize_text_field( $setting );
				}
			}
		}
	}
}
