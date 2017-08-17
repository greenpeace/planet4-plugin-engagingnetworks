<?php

if ( ! class_exists( 'P4EN_Ensapi_Controller' ) ) {

	/**
	 * Class P4EN_Ensapi_Controller
	 */
	class P4EN_Ensapi_Controller {

		const ENS_AUTH_URL  = 'https://www.e-activist.com/ens/service/authenticate';
		const ENS_PAGES_URL = 'https://www.e-activist.com/ens/service/page';
		const ENS_PAGES_DEFAULT = 'PET';      // Retrieve all petitions by default.
		const ENS_CALL_TIMEOUT = 7;

		/**
		 * P4EN_Ensapi_Controller constructor.
		 */
		public function __construct() {}

		/**
		 * Authenticates usage of ENS API calls.
		 *
		 * @param string $ens_private_token The private api token to be used in order to authenticate for ENS API.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function authenticate( $ens_private_token ) {

			$url = self::ENS_AUTH_URL;

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_post( $url, [
				'headers' => [
					'Content-Type' => 'application/json; charset=UTF-8',
				],
				'body' => $ens_private_token,
				'timeout' => self::ENS_CALL_TIMEOUT,
			] );

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();

			} elseif ( is_array( $response ) && WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}
			return $response;
		}

		/**
		 * Gets all the information on the available pages built in EN.
		 *
		 * @param string $ens_auth_token The authentication token to be used in all following ENS API calls.
		 * @param array  $params The query parameters to be added in the url.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_pages( $ens_auth_token, $params = array( 'type' => self::ENS_PAGES_DEFAULT ) ) {

			$url = self::ENS_PAGES_URL;
			$params['type'] = strtolower( $params['type'] );
			$url = add_query_arg( $params, $url );

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_get( $url, [
				'headers' => [
					'ens-auth-token' => $ens_auth_token,
				],
				'timeout' => self::ENS_CALL_TIMEOUT,
			] );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();

			} elseif ( is_array( $response ) && WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.

			}
			return $response;
		}
	}
}
