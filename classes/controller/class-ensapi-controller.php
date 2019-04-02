<?php
/**
 * ENSAPI Controller class
 *
 * @package P4EN
 */

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
		const ENS_TYPES_DEFAULT  = 'PET';           // Retrieve all petitions by default.
		const ENS_STATUS_DEFAULT = 'all';
		const ENS_CACHE_TTL      = 600;             // Time in seconds to cache the response of an ENS api call.
		const ENS_CALL_TIMEOUT   = 10;              // Seconds after which the api call will timeout if not responded.

		/**
		 * ENS Auth Token for private user.
		 *
		 * @var $ens_auth_token
		 */
		private $ens_auth_token = '';

		/**
		 * ENS Auth Token for public user.
		 *
		 * @var $ens_auth_public_token
		 */
		private $ens_auth_public_token = '';


		/**
		 * Ensapi_Controller constructor.
		 *
		 * @param string $ens_private_token The private api token to be used in order to authenticate for ENS API.
		 * @param bool   $private_user        Defines if a token for a private user is passed.
		 */
		public function __construct( $ens_private_token, $private_user = true ) {
			$token_type = $private_user ? 'ens_auth_token' : 'ens_auth_public_token';
			$this->authenticate( $ens_private_token, $token_type );
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
		 * @param string $token_name        Defines the token name.
		 */
		private function authenticate( $ens_private_token, $token_name ) {

			// Get cached auth token.
			$ens_auth_token = get_transient( $token_name );

			if ( ! $ens_auth_token ) {
				$url = self::ENS_AUTH_URL;
				// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
				$response = wp_safe_remote_post(
					$url,
					[
						'headers' => [
							'Content-Type' => 'application/json; charset=UTF-8',
						],
						'body'    => $ens_private_token,
						'timeout' => self::ENS_CALL_TIMEOUT,
					]
				);

				if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {                   // Communication with ENS API is authenticated.
					$body           = json_decode( $response['body'], true );
					$expiration     = (int) ( $body['expires'] / 1000 );                      // Time period in seconds to keep the ens_auth_token before refreshing. Typically 1 hour.
					$ens_auth_token = $body['ens-auth-token'];
					set_transient( $token_name, $ens_auth_token, $expiration );
				}
			}
			$this->$token_name = $ens_auth_token;
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
					$params['type'] = $type;
					$response       = $this->get_pages( $params );
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
		public function get_pages( $params = array(
			'type'   => self::ENS_TYPES_DEFAULT,
			'status' => self::ENS_STATUS_DEFAULT,
		) ) {

			$response = get_transient( 'ens_pages_response_' . implode( '_', $params ) );
			if ( ! $response ) {
				$url = add_query_arg(
					[
						'type'   => strtolower( $params['type'] ),
						'status' => $params['status'],
					],
					self::ENS_PAGES_URL
				);

				// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
				$response = wp_safe_remote_get(
					$url,
					[
						'headers' => [
							'ens-auth-token' => $this->ens_auth_token,
						],
						'timeout' => self::ENS_CALL_TIMEOUT,
					]
				);

				if ( is_wp_error( $response ) ) {
					return $response->get_error_message() . ' ' . $response->get_error_code();

				} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
					return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.

				}
				set_transient( 'ens_pages_response_' . implode( '_', $params ), $response, self::ENS_CACHE_TTL );
			}
			return $response;
		}

		/**
		 * Process an EN Page.
		 *
		 * @param int   $page_id The id of the EN page that the submitted data will be sent to.
		 * @param array $fields The submitted fields which will be passed to the body of the API call.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function process_page( $page_id, $fields ) {
			$url = self::ENS_PAGES_URL . '/' . $page_id . '/process';

			// If Email address is found then supporter exists and its data will be updated with the values
			// inside the supporter key. Else a new supporter with this Email address will be created by EN.
			$supporter_keys_fields = [
				'Title'         => 'supporter.title',
				'First name'    => 'supporter.firstName',
				'Last name'     => 'supporter.lastName',
				'Address 1'     => 'supporter.address1',
				'Address 2'     => 'supporter.address2',
				'City'          => 'supporter.city',
				'Country'       => 'supporter.country',
				'Postcode'      => 'supporter.postcode',
				'Email'         => 'supporter.emailAddress',
				'Phone Number'  => 'supporter.phoneNumber',
				'Date of Birth' => 'supporter.birthday',
				'questions'     => 'supporter.questions',
			];

			// Supporter fields are updated only if they exist as fields within the submitted form.
			foreach ( $supporter_keys_fields as $api_key => $field_name ) {
				if ( isset( $fields[ $field_name ] ) ) {
					$supporter[ $api_key ] = $fields[ $field_name ];
				}
			}

			$body = [
				'supporter' => $supporter ?? [],
			];

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_post(
				$url,
				[
					'headers' => [
						'ens-auth-token' => $this->ens_auth_token,
						'Content-Type'   => 'application/json; charset=UTF-8',
					],
					'body'    => wp_json_encode( $body ),
					'timeout' => self::ENS_CALL_TIMEOUT,
				]
			);

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}
			return $response;
		}

		/**
		 * Gets all the supporter fields that exist in the EN client account.
		 *
		 * @return array|string Array with the fields or a message if something goes wrong.
		 */
		public function get_supporter_fields() {
			$response = get_transient( 'ens_supporter_fields_response' );
			if ( ! $response ) {
				$url = self::ENS_SUPPORTER_URL . '/fields';

				// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
				$response = wp_safe_remote_get(
					$url,
					[
						'headers' => [
							'ens-auth-token' => $this->ens_auth_token,
							'Content-Type'   => 'application/json; charset=UTF-8',
						],
						'timeout' => self::ENS_CALL_TIMEOUT,
					]
				);

				// Authentication failure.
				if ( is_wp_error( $response ) ) {
					return $response->get_error_message() . ' ' . $response->get_error_code();

				} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
					return $response['response']['message'] . ' ' . $response['response']['code'];
				}
				set_transient( 'ens_supporter_fields_response', $response, self::ENS_CACHE_TTL );
			}
			return $response;
		}

		/**
		 * Gets all the supporter questions/optins that exist in the EN client account.
		 *
		 * @return array|string Array with the fields or a message if something goes wrong.
		 */
		public function get_supporter_questions() {
			$response = get_transient( 'ens_supporter_questions_response' );
			if ( ! $response ) {
				$url = self::ENS_SUPPORTER_URL . '/questions';

				// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
				$response = wp_safe_remote_get(
					$url,
					[
						'headers' => [
							'ens-auth-token' => $this->ens_auth_token,
							'Content-Type'   => 'application/json; charset=UTF-8',
						],
						'timeout' => self::ENS_CALL_TIMEOUT,
					]
				);

				// Authentication failure.
				if ( is_wp_error( $response ) ) {
					return $response->get_error_message() . ' ' . $response->get_error_code();

				} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
					return $response['response']['message'] . ' ' . $response['response']['code'];
				}
				set_transient( 'ens_supporter_questions_response', $response, self::ENS_CACHE_TTL );
			}
			return $response;
		}

		/**
		 * Authenticates usage of ENS API calls.
		 *
		 * @param string $email The supporter's email address.
		 * @param bool   $include_questions True if we want to include the supporters data for questions/optins.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_supporter_by_email( $email, $include_questions = true ) {

			$url = add_query_arg(
				[
					'email'            => $email,
					'includeQuestions' => $include_questions ? 'true' : 'false',
				],
				self::ENS_SUPPORTER_URL
			);

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_get(
				$url,
				[
					'headers' => [
						'ens-auth-token' => $this->ens_auth_token,
						'Content-Type'   => 'application/json; charset=UTF-8',
					],
					'timeout' => self::ENS_CALL_TIMEOUT,
				]
			);

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}
			return $response;
		}

		/**
		 * Get session token for public user.
		 *
		 * @return mixed EN Service Token.
		 */
		public function get_public_session_token() {
			if ( ! $this->ens_auth_public_token ) {
				$main_settings     = get_option( 'p4en_main_settings' );
				$ens_private_token = $main_settings['p4en_frontend_private_api'];
				$this->authenticate( $ens_private_token, 'ens_auth_public_token' );
			}

			return $this->ens_auth_public_token;
		}
	}
}
