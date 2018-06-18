<?php

namespace P4EN\Api;

use P4EN\Scraper\Scraper;

/**
 * WP REST API interface.
 */
class REST_Handler {

	/**
	 * Initialize class if all checks are ok.
	 */
	public function initialize() {
		// If WP REST API is not enabled, exit.
		if ( ! defined( 'REST_API_VERSION' ) ) {
			return;
		}

		// Need at least REST API version 2.
		if ( version_compare( REST_API_VERSION, '2.0', '<' ) ) {
			return;
		}

		$this->set_rest_hooks();
	}

	/**
	 * Action for the wp rest api initialization.
	 */
	private function set_rest_hooks() {
		add_action( 'rest_api_init', array( $this, 'setup_rest' ) );
	}

	/**
	 * Setup rest endpoints if REST_REQUEST is defined.
	 */
	public function setup_rest() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$this->setup_rest_endpoints();
		}
	}

	/**
	 * Setup the REST endpoints for en plugin.
	 */
	private function setup_rest_endpoints() {
		$version = 'v1';

		/**
		 * Get a single form's fields.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/planet4-engaging-networks/<v1+>/form/get_fields
		 * @method  WP_REST_Server::CREATABLE ( POST )
		 *
		 * @params  string  en_url  required , url of the en page.
		 *
		 * @returns \WP_Error | \WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/form/get_fields', array(
			'methods'  => \WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'get_form_fields' ),
		) );

	}

	/**
	 * Get a form's fields by scraping the engaging network page.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error| \WP_REST_Response
	 */
	public function get_form_fields( \WP_REST_Request $request ) {

		$scraper = new Scraper();
		$fields  = $scraper->get( $request['en_url'] );

		$response_data = array(
			'fields'       => $fields,
		);
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}
}
