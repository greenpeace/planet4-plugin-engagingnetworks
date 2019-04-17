<?php
/**
 * Contains Enform_Fields_List_Table class declaration.
 *
 * @package P4EN
 */

namespace P4EN\Controllers;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

use P4EN\Controllers\Ensapi_Controller as Ensapi;

/**
 * Class Enform_Fields_List_Table.
 * Creates a list table for available en fields overriding WordPress List Table.
 *
 * @package P4EN\Controllers
 * @since 1.8.0
 *
 * @see \WP_List_Table
 * @link https://developer.wordpress.org/reference/classes/wp_list_table/
 */
class Enform_Fields_List_Table extends \WP_List_Table {

	/**
	 * Store errors from en api.
	 *
	 * @var string $error
	 */
	private $error;

	/**
	 * Enform_Fields_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'ajax' => false,
			]
		);

		$this->error = '';
	}

	/**
	 * Implements parent's abstract function.
	 * Prepares the list of items for displaying.
	 *
	 * @see \WP_List_Table::prepare_items
	 */
	public function prepare_items() {

		$response_data = [];
		$main_settings = get_option( 'p4en_main_settings' );
		if ( isset( $main_settings['p4en_private_api'] ) ) {
			$ens_private_token = $main_settings['p4en_private_api'];
			$ens_api           = new Ensapi( $ens_private_token );
			$response          = $ens_api->get_supporter_fields();

			if ( isset( $response['body'] ) && ! empty( $response['body'] ) ) {
				$response_data = json_decode( $response['body'], true );
			} else {
				$this->error = implode(
					[
						__( 'Could not fetch results from engaging networks', 'planet4-engagingnetworks' ),
						'<br>',
						$response,
					]
				);
			}
		}

		$columns = $this->get_columns();

		$hidden                = [];
		$sortable              = [];
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $response_data;
	}

	/**
	 * Implements parent's abstract function.
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array Columns array.
	 */
	public function get_columns() {
		$columns = [
			'id'       => __( 'Id', 'planet4-engagingnetworks' ),
			'name'     => __( 'Name', 'planet4-engagingnetworks' ),
			'tag'      => __( 'Tag', 'planet4-engagingnetworks' ),
			'property' => __( 'Property', 'planet4-engagingnetworks' ),
			'actions'  => __( 'Actions', 'planet4-engagingnetworks' ),
		];

		return $columns;
	}

	/**
	 * Generates content for a column that does not have each own function defined.
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string Content for column.
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'name':
			case 'property':
			case 'tag':
				return $item[ $column_name ];
		}
	}

	/**
	 * Generates content for the actions column.
	 *
	 * @param array $item Column data.
	 *
	 * @return string Content for actions column.
	 */
	public function column_actions( $item ) : string {
		return '<button disabled>' . __( 'Add', 'planet4-engagingnetworks' ) . '</button>';
	}

	/**
	 * Overrides parent function to disable nonce generation, bulk actions and pagination.
	 * Used to display errors (if any) that come from en api.
	 *
	 * @see \WP_List_Table::display_tablenav
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( ! empty( $this->error ) && 'top' === $which ) {
			echo '<div><p>' . esc_html( $this->error ) . '</p></div>';
		}
	}
}
