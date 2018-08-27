<?php

namespace P4EN\Controllers;

if ( ! class_exists( 'Ensapi_Controller' ) ) {

	/**
	 * Class Ensapi_Controller
	 */
	class Ensapi_Controller {

		const ENS_BASE_URL       = 'https://www.e-activist.com/ens/service';
		const ENS_AUTH_URL       = self::ENS_BASE_URL . '/authenticate';
		const ENS_SUPPORTER_URL  = self::ENS_BASE_URL . '/supporter';
		const ENS_PAGES_URL      = self::ENS_BASE_URL . '/page';
		const ENS_TYPES_DEFAULT  = 'PET';         // Retrieve all petitions by default.
		const ENS_STATUS_DEFAULT = 'all';
		const ENS_CALL_TIMEOUT   = 10;            // Seconds after which the api call will timeout if not responded.

		/** @var $ens_auth_token */
		private $ens_auth_token = '';


		/**
		 * Ensapi_Controller constructor.
		 *
		 * @param string $ens_private_token The private api token to be used in order to authenticate for ENS API.
		 */
		public function __construct( $ens_private_token ) {
			$this->authenticate( $ens_private_token );
		}

		/**
		 * Returns the auth token. If communication is not authenticated then the auth token is an empty string.
		 *
		 * @return string The auth token.
		 */
		public function is_authenticated() : string {
			return $this->ens_auth_token;
		}

		/**
		 * Authenticates usage of ENS API calls.
		 *
		 * @param string $ens_private_token The private api token to be used in order to authenticate for ENS API.
		 */
		private function authenticate( $ens_private_token ) {

			// Get cached auth token.
			$this->ens_auth_token = get_transient( 'ens_auth_token' );

			if ( ! $this->ens_auth_token ) {
				$url = self::ENS_AUTH_URL;
				// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
				$response = wp_safe_remote_post( $url, [
					'headers' => [
						'Content-Type' => 'application/json; charset=UTF-8',
					],
					'body'    => $ens_private_token,
					'timeout' => self::ENS_CALL_TIMEOUT,
				] );

				if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {                   // Communication with ENS API is authenticated.
					$body                 = json_decode( $response['body'], true );
					$expiration           = (int) ( $body['expires'] / 1000 );                      // Time period in seconds to keep the ens_auth_token before refreshing. Typically 1 hour.
					$this->ens_auth_token = $body['ens-auth-token'];
					set_transient( 'ens_auth_token', $this->ens_auth_token, $expiration );
				}
			}
		}

		/**
		 * Retrieves all EN pages whose type is included in the $types array.
		 *
		 * @param array  $types Array with the types of the EN pages to be retrieved.
		 * @param string $status The status of the EN pages to be retrieved.
		 *
		 * @return array Array with data of the retrieved EN pages.
		 */
		public function get_pages_by_types_status( $types, $status = 'all' ) : array {
			$pages = [];
			if ( $types ) {
				$params['status'] = $status;
				foreach ( $types as $type ) {
					$params['type']   = $type;
					$response         = $this->get_pages( $params );
					if ( is_array( $response ) && $response['body'] ) {
						$pages[ $params['type'] ] = json_decode( $response['body'], true );
					}
				}
			}
			return $pages;
		}

		/**
		 * Gets all the information on the available pages built in EN.
		 *
		 * @param array $params The query parameters to be added in the url.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_pages( $params = array( 'type' => self::ENS_TYPES_DEFAULT, 'status' => self::ENS_STATUS_DEFAULT ) ) {

			$url = add_query_arg( [
				'type'   => strtolower( $params['type'] ),
				'status' => $params['status'],
			], self::ENS_PAGES_URL );

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_get( $url, [
				'headers' => [
					'ens-auth-token' => $this->ens_auth_token,
				],
				'timeout' => self::ENS_CALL_TIMEOUT,
			] );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.

			}
			return $response;
		}

		/**
		 * Process an EN Page.
		 *
		 * @param int $page_id The id of the EN page that the submitted data will be sent to.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function process_page( $page_id ) {
			$url = self::ENS_PAGES_URL . '/' . $page_id . '/process';

			$current_user = wp_get_current_user();
			$body = [
				'demo' => true,
				'supporter' => [
					'First Name' => $current_user->first_name,
					'Last Name' => $current_user->last_name,
					'Email Address' => $current_user->user_email,
					'Address 1' => 'address',
					'Postal Code' => '24100',
					'Country' => 'GR',
				],
				'contactMessage' => [
					'subject' => '',
					'message' => '',
				],
			];

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_post( $url, [
				'headers' => [
					'ens-auth-token' => $this->ens_auth_token,
					'Content-Type'   => 'application/json; charset=UTF-8',
				],
				'body' => $body,
				'timeout' => self::ENS_CALL_TIMEOUT,
			] );

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}
			return $response;
		}
	}
}
