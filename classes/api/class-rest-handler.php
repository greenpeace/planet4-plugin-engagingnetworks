<?php

namespace P4EN\Api;


/**
 * WP REST API interface.
 */
class REST_Handler {

	private $fields_option = 'planet4-en-fields';

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

		$fields_controller = new Fields_Controller();

		/**
		 * Get a single form's fields.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/planet4-engaging-networks/v1/fields
		 * @method  WP_REST_Server::READABLE ( GET )
		 *
		 * @params  string  en_url  required , url of the en page.
		 *
		 * @returns \WP_Error | \WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/fields', array(
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => [ $fields_controller, 'get_fields' ],
		) );


		/**
		 * Add a single location.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/store-locator-plus/<v2+>/locations
		 * @method  WP_REST_Server::EDITABLE ( POST, PUT, PATCH )
		 *
		 * @params  string  sl_store        required , name of store
		 * @params  string  <field_slug>    optional, other store data. Field slugs can match base or extended data fields.
		 *
		 * @returns WP_Error | WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/fields', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => [ $fields_controller, 'add_field' ],
			'permission_callback' => [ $this, 'is_allowed' ],
			'args'                => array(//				'sl_store'  => array( 'required'    => true ),
			)
		) );

		/**
		 * Get a single form's fields.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/planet4-engaging-networks/<v1+>/form/get_fields
		 * @method  WP_REST_Server::READABLE ( GET )
		 *
		 * @params  string  en_url  required , url of the en page.
		 *
		 * @returns \WP_Error | \WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/fields/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $fields_controller, 'get_field' ],
			'permission_callback' => [ $this, 'is_allowed' ],
		) );


		/**
		 * Update a single location.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/planet4-engaging-networks/v1/fields/<id>
		 * @method  WP_REST_Server::EDITABLE ( POST, PUT, PATCH )
		 *
		 * @params  string  sl_store        required , name of store
		 * @params  string  <field_slug>    optional, other store data. Field slugs can match base or extended data fields.
		 *
		 * @returns WP_Error | WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/fields/(?P<id>\d+)', [
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => [ $fields_controller, 'update_field' ],
			'permission_callback' => [ $this, 'is_allowed' ],
		] );

		/**
		 * Delete a single location.
		 *
		 * Requires authentication.
		 *
		 * @route   wp-json/planet4-engaging-networks/v1/fields/<id>
		 * @method  WP_REST_Server::DELETABLE ( DELETE )
		 *
		 * @returns WP_Error | WP_REST_Reponse
		 */
		register_rest_route( P4_REST_SLUG . '/' . $version, '/fields/(?P<id>\d+)', [
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => [ $fields_controller, 'delete_field' ],
			'permission_callback' => [ $this, 'is_allowed' ],
		] );

	}

	/**
	 * Check if user is allowed to access api routes.
	 *
	 * @return bool
	 */
	public function is_allowed() {
		return current_user_can( 'manage_options' );
	}
}
