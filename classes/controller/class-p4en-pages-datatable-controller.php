<?php

if ( ! class_exists( 'P4EN_Pages_Datatable_Controller' ) ) {

	/**
	 * Class P4EN_Pages_Datatable_Controller
	 */
	class P4EN_Pages_Datatable_Controller extends P4EN_Pages_Controller {

		/** @var P4EN_View $view */
		protected $view;

		/**
		 * P4EN_Pages_Datatable_Controller constructor.
		 *
		 * @param P4EN_View $view
		 */
		public function __construct( P4EN_View $view ) {
			$this->view = $view;
		}

		/**
		 * Pass all needed data to the view object for the datatable page.
		 */
		public function prepare_pages_datatable() {
			$pages = [];
			$params = [];
			$main_settings = get_option( 'p4en_main_settings' );
			$pages_settings = get_option( 'p4en_pages_settings' );

			if ( $pages_settings ) {
				if ( isset( $pages_settings['p4en_pages_subtype'] ) && $pages_settings['p4en_pages_subtype'] ) {
					$params['type'] = $pages_settings['p4en_pages_subtype'];
				}
				if ( isset( $pages_settings['p4en_pages_status'] ) && 'all' !== $pages_settings['p4en_pages_status'] ) {
					$params['status'] = $pages_settings['p4en_pages_status'];
				}
			}

			if ( isset( $main_settings['p4en_private_api'] ) ) {
				$ens_api = new P4EN_Ensapi_Controller();
				$ens_private_token = $main_settings['p4en_private_api'];

				$response = $ens_api->authenticate( $ens_private_token );
				if ( is_array( $response ) && $response['body'] ) {
					// Communication with ENS API is authenticated.
					$body = json_decode( $response['body'], true );
					$ens_auth_token = $body['ens-auth-token'];

					$response = $ens_api->get_pages( $ens_auth_token, $params );
					if ( is_array( $response ) ) {
						$pages = json_decode( $response['body'], true );
					} else {
						echo esc_html( $response );
					}
				} else {
					echo esc_html( $response );
				}
			} else {
				wp_die(
					'<div class="error is-dismissible">' .
					'<u>' . esc_html__( 'Plugin Settings Error!', 'planet4-engagingnetworks' ) . '</u><br /><br />' . esc_html__( 'Plugin Settings are not configured well!', 'planet4-engagingnetworks' ) . '<br />' .
					'</div>', 'Plugin Settings Error', array(
						'response' => WP_Http::OK,
						'back_link' => true,
					)
				);
			}

			$this->filter_pages_datatable( $pages );
			// Provide hook for other plugins to be able to filter the datatable output.
			$pages = apply_filters( 'p4en_filter_pages_datatable', $pages );

			$this->view->pages_datatable( [
				'pages' => $pages,
				'pages_settings' => $pages_settings,
				'subtypes' => self::SUBTYPES,
				'statuses' => self::STATUSES,
			] );
		}

		/**
		 * Filter the output for the datatable page.
		 *
		 * @param array $pages The pages array retrieved from the ENS API call.
		 */
		public function filter_pages_datatable( &$pages ) {

			foreach ( $pages as &$page ) {
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
}
