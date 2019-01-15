<?php
/**
 * Class ApiTest
 *
 * Test fields rest endpoints.
 *
 * @package P4EN
 */

/**
 * ApiTest class.
 */
class ApiTest extends P4_TestCase {

	/**
	 * The REST server.
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Namespaced route
	 *
	 * @var string
	 */
	protected $namespaced_route = '/planet4-engaging-networks/v1/fields';

	/**
	 * Slug for the API
	 *
	 * @var string
	 */
	protected $api_slug = 'planet4-engaging-networks';

	/**
	 * Set up variables and needed options for testing fields endpoints.
	 */
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server;
		$this->server   = $wp_rest_server;

		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		do_action( 'rest_api_init' );

		// Create a user with administrator role.
		$this->factory->user->create(
			[
				'role'       => 'administrator',
				'user_login' => 'p4_admin',
			]
		);
		$user = get_user_by( 'login', 'p4_admin' );
		wp_set_current_user( $user->ID );
		add_option( 'planet4-en-fields', [] );
	}


	/**
	 * Test that routes have been registered.
	 */
	public function test_register_route() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->namespaced_route, $routes );
	}

	/**
	 * Test get fields endpoint.
	 *
	 * @covers       \P4EN\Controllers\Api\Fields_Controller::get_fields
	 */
	public function test_name_route() {

		$request  = new \WP_REST_Request( 'GET', '/' . $this->api_slug . '/v1/fields' );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
	}

	/**
	 * Test get fields endpoint.
	 *
	 * @covers       \P4EN\Controllers\Api\Fields_Controller::get_fields
	 */
	public function test_get_fields() {

		$fields = $this->get_mock_fields();
		foreach ( $fields as $field ) {
			$request = new \WP_REST_Request( 'POST', '/' . $this->api_slug . '/v1/fields' );
			$request->add_header( 'Content-Type', 'application/json' );
			$request->set_body( wp_json_encode( $field ) );
			$response = $this->server->dispatch( $request );
		}

		$request  = new \WP_REST_Request( 'GET', '/' . $this->api_slug . '/v1/fields' );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertEquals( count( $fields ), count( $data ) );
	}

	/**
	 * Test add new field endpoint.
	 *
	 * @covers       \P4EN\Controllers\Api\Fields_Controller::add_field
	 * @dataProvider fields_provider
	 * @param array $field       Field data.
	 * @param int   $status_code Http response status code.
	 */
	public function test_add_field( $field, $status_code ) {
		$request = new \WP_REST_Request( 'POST', '/' . $this->api_slug . '/v1/fields' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $field ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $status_code, $response->get_status() );
	}

	/**
	 * Test update field endpoint.
	 *
	 * @covers       \P4EN\Controllers\Api\Fields_Controller::update_field
	 */
	public function test_update_field() {

		$field   = [
			'id'        => 1,
			'name'      => 'field-name',
			'mandatory' => true,
			'type'      => 'text',
		];
		$request = new \WP_REST_Request( 'POST', '/' . $this->api_slug . '/v1/fields' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $field ) );
		$response = $this->server->dispatch( $request );

		$field   = [
			'id'        => 1,
			'name'      => 'field-name-updated',
			'mandatory' => true,
			'type'      => 'text',
		];
		$request = new \WP_REST_Request( 'PUT', '/' . $this->api_slug . '/v1/fields/1' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $field ) );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test update field endpoint.
	 *
	 * @covers       \P4EN\Controllers\Api\Fields_Controller::delete_field
	 */
	public function test_delete_field() {
		$field   = [
			'id'        => 1,
			'name'      => 'field-name',
			'mandatory' => true,
			'type'      => 'text',
		];
		$request = new \WP_REST_Request( 'POST', '/' . $this->api_slug . '/v1/fields' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $field ) );
		$response = $this->server->dispatch( $request );

		$request = new \WP_REST_Request( 'DELETE', '/' . $this->api_slug . '/v1/fields/1' );
		$request->add_header( 'Content-Type', 'application/json' );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
	}


	/**
	 * Mock fields data provider.
	 *
	 * @return array
	 */
	private function get_mock_fields() {
		return [
			[
				'id'        => 1,
				'name'      => 'field-name',
				'mandatory' => true,
				'type'      => 'text',
			],
			[
				'id'        => 2,
				'name'      => 'field-name2',
				'mandatory' => true,
				'type'      => 'text',
			],
		];
	}

	/**
	 * Fields data provider.
	 *
	 * @return array
	 */
	public function fields_provider() {
		return
			[
				[
					[
						'id'   => 1,
						'name' => 'field-name',
					],
					400,
				],
				[
					[
						'id'        => 1,
						'name'      => 'field_name',
						'mandatory' => true,
					],
					400,
				],
				[
					[
						'id'        => 1,
						'name'      => 'field-name',
						'mandatory' => 'invalid',
						'type'      => 'country',
					],
					400,
				],
				[
					[
						'id'        => 1,
						'name'      => 'field-name',
						'mandatory' => false,
						'type'      => 'invalid',
					],
					400,
				],
				[
					[
						'id'        => 1,
						'name'      => 'field-name',
						'mandatory' => true,
						'type'      => 'country',
					],
					201,
				],
				[
					[
						'id'        => 1,
						'name'      => 'field-name',
						'mandatory' => false,
						'type'      => 'country',
					],
					201,
				],
			];
	}
}
