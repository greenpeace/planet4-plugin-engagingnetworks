<?php
/**
 * Fields model
 *
 * @package P4EN
 */

namespace P4EN\Models;

if ( ! class_exists( 'Fields_Model' ) ) {

	/**
	 * Class Fields_Model
	 *
	 * Handles CRUD operations of fields for database persistence.
	 * Fields are stored in wp_options table as an array of objects.
	 *
	 * A single field has the below structure:
	 * {
	 *   default_value: "address 20"
	 *   hidden: "Y"
	 *   id: "28118"
	 *   label: "label for use in enform"
	 *   name: "Address 1"
	 * }
	 */
	class Fields_Model extends Model {

		/**
		 * WordPress option name in which saved data are persisted.
		 *
		 * @var string
		 */
		private $fields_option = 'planet4-en-fields';

		/**
		 * Allowed attributes for each field.
		 *
		 * @var array
		 */
		private $allowed_attributes = [
			'default_value',
			'hidden',
			'id',
			'label',
			'name',
		];

		/**
		 * Retrieve a field by id.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return array
		 */
		public function get_field( $id ) {
			$options = get_option( $this->fields_option );

			if ( isset( $options['fields'] ) && ! empty( $options['fields'] ) ) {
				$fields = $options['fields'];
				foreach ( $fields as $field ) {
					if ( (int) $field['id'] === (int) $id ) {
						return $field;
					}
				}
			}

			return [];
		}

		/**
		 * Retrieve all the fields.
		 *
		 * @return array
		 */
		public function get_fields() : array {
			$options = get_option( $this->fields_option );
			$fields  = $options ? array_values( $options ) : [];
			return $fields;
		}

		/**
		 * Add field.
		 *
		 * @param array $field Field attributes.
		 *
		 * @return bool
		 */
		public function add_field( $field ) {

			$options = get_option( $this->fields_option );      // Added default value for the first time.
			if ( is_array( $options ) || false === $options ) {
				$fields = array_values( $options );
				$index  = -1;
				for ( $i = 0; $i < count( $fields ); $i ++ ) {
					if ( (int) $fields[ $i ]['id'] === (int) $field['id'] ) {
						$index = $i;
						break;
					}
				}

				if ( $index >= 0 ) {
					return false;
				} else {
					$fields[] = $field;
					$updated  = update_option( $this->fields_option, $fields );

					return $updated;
				}
			}

			return false;
		}

		/**
		 * Update field.
		 *
		 * @param array $field Field attributes.
		 *
		 * @return bool
		 */
		public function update_field( $field ) {
			$options = get_option( $this->fields_option );

			if ( is_array( $options ) ) {
				$fields        = array_values( $options );
				$index         = -1;
				$fields_length = count( $fields );
				for ( $i = 0; $i < $fields_length; $i ++ ) {
					if ( (int) $fields[ $i ]['id'] === (int) $field['id'] ) {
						$index = $i;
						break;
					}
				}
				if ( $index >= 0 ) {
					$fields[ $index ] = $this->filter_attributes( $field );
					$updated          = update_option( $this->fields_option, $fields );

					return $updated;
				}
			}

			return false;
		}

		/**
		 * Remove fields from en field that are not defined in the allowed fields array.
		 *
		 * @param array $field An assosiative array containing the en field attributes.
		 *
		 * @return array
		 */
		private function filter_attributes( $field ) {
			return array_filter(
				$field,
				function ( $k ) {
					return in_array( $k, $this->allowed_attributes );
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		/**
		 * Delete field.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return bool
		 */
		public function delete_field( $id ) {
			$options = get_option( $this->fields_option );
			if ( is_array( $options ) ) {
				$fields  = $options;
				$fields  =
					array_filter(
						$fields,
						function ( $e ) use ( $id ) {
							return (int) $e['id'] !== (int) $id;
						}
					);
				$updated = update_option( $this->fields_option, $fields );

				return $updated;
			}

			return false;
		}
	}
}
