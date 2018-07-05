<?php

namespace P4EN\Models;

if ( ! class_exists( 'Fields_Model' ) ) {

	/**
	 * Class Fields_Model
	 */
	class Fields_Model {

		private $fields_option = 'planet4-en-fields';

		/**
		 * @param $id
		 *
		 * @return array
		 */
		public function get_field( $id ) {
			$options = get_option( $this->fields_option );

			if ( isset( $options['fields'] ) && ! empty( $options['fields'] ) ) {
				$fields = $options['fields'];
				foreach ( $fields as $field ) {
					if ( $field['id'] == $id ) {
						return $field;
					}
				}
			}

			return [];
		}

		/**
		 * @param $field
		 *
		 * @return bool
		 */
		public function add_field( $field ) {

			$options = get_option( $this->fields_option );
			if ( isset( $options ) ) {
				$fields   = array_values( $options );
				$fields[] = $field;
				$updated  = update_option( $this->fields_option, $fields );

				return $updated;
			}

			return false;
		}

		/**
		 * @param $field
		 *
		 * @return bool
		 */
		public function update_field( $field ) {
			$options = get_option( $this->fields_option );

			if ( isset( $options ) ) {
				$fields = array_values( $options );
				$index  = false;
				for ( $i = 0; $i < count( $fields ); $i ++ ) {
					if ( $fields[ $i ]['id'] == $field['id'] ) {
						$index = $i;
						break;
					}
				}
				if ( $index >= 0 ) {
					$fields[ $index ] = $field;
					$updated          = update_option( $this->fields_option, $fields );

					return $updated;
				}
			}

			return false;
		}

		/**
		 * @param $id
		 *
		 * @return bool
		 */
		public function delete_field( $id ) {
			$options = get_option( $this->fields_option );
			if ( isset( $options ) ) {
				$fields  = $options;
				$fields  =
					array_filter( $fields, function ( $e ) use ( $id ) {
						return $e['id'] != $id;
					} );
				$updated = update_option( $this->fields_option, $fields );

				return $updated;
			}

			return false;
		}
	}
}
