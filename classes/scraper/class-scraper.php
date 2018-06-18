<?php

namespace P4EN\Scraper;

if ( ! class_exists( 'Scraper' ) ) {

	/**
	 * Class Scraper
	 *
	 * This class contains functionality to scrape an engaging networks page.
	 */
	class Scraper {

		/**
		 * Fetch an engaging network page and extract it's fields.
		 * @param $url
		 *
		 * @return array
		 */
		public function get( $url ) {
			$args = array(
				'httpversion' => '1.1',
				'headers'     => array( 'Connection' => 'keep-alive' ),
				'sslverify'   => false,
				'timeout'     => 60,
			);

			$request = wp_safe_remote_get( $url, $args );

			if ( is_wp_error( $request ) ) {
				wp_die( $request->get_error_message() );
			}
			$body = wp_remote_retrieve_body( $request );

			$dom    = $this->create_dom_document( $body );
			$fields = $this->extract_fields( $dom, ENSelectors::FORM_FIELDS_XPATH );

			return $fields;

		}

		/**
		 * Parse a string and create a dom document after doing some html clean up.
		 *
		 * @param string $content The page/string to be parsed.
		 *
		 * @return \DOMDocument
		 */
		public function create_dom_document( $content ) {
			$content = trim( $content );

			$content = preg_replace(
				[
					"'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is",
					"'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is",
					"'<\s*noscript[^>]*[^/]>(.*?)<\s*/\s*noscript\s*>'is",
					"'<\s*noscript\s*>(.*?)<\s*/\s*noscript\s*>'is",
				], [
				"",
				"",
				"",
				"",
			], $content );

			// Remove comments comment
			$content = preg_replace( '/\/\*(?!-)[\x00-\xff]*?\*\//', "", $content );
			$content = preg_replace( '/<!--(.*)-->/Uis', '', $content );

			$dom                     = new \DOMDocument();
			$dom->preserveWhiteSpace = false;

			// Set this to avoid errors because of html5 tags.
			// DOM does not support html5.
			libxml_use_internal_errors( true );
			$dom->loadHtml( $content );
			libxml_use_internal_errors( false );

			return $dom;
		}

		/**
		 * Extract the form fields from an engaging network page.
		 *
		 * @param \DOMDocument $dom          The dom document
		 * @param string       $xpath_query  Xpath query
		 *
		 * @return array
		 */
		public function extract_fields( \DOMDocument $dom, $xpath_query ) {
			$fields = [];
			$xpath  = new \DOMXPath( $dom );
			$nodes  = $xpath->query( $xpath_query );
			foreach ( $nodes as $node ) {
				$field            = new \stdClass();
				$attributes       = $node->attributes;
				$field->name      = $attributes->getNamedItem( 'name' )->nodeValue;
				$field->type      = $this->decide_type( $node );
				$field->mandatory = $this->is_mandatory( $node );
				$field->label     = $this->get_field_label( $node );
				$field->id        = $this->get_field_id( $node );
				$fields[]         = $field;
			}

			return $fields;
		}

		/**
		 * Decide the type of the form field based on the html tags.
		 *
		 * @param \DOMNode $node The form field.
		 *
		 * @return string
		 */
		private function decide_type( \DOMNode $node ) {
			$field_type = 'string';
			if ( "input" === $node->nodeName ) {
				$attributes = $node->attributes;
				$type       = $attributes->getNamedItem( 'type' )->nodeValue;

				switch ( $type ) {
					case 'checkbox':
						$field_type = 'boolean';
						break;
					case 'text':
						$field_type = 'string';
						break;
				}
			} elseif ( "select" === $node->nodeName ) {
				$field_type = 'string';
			}

			return $field_type;
		}

		/**
		 * Decide if a form field is mandatory or not.
		 *
		 * @param \DOMNode $node The form field.
		 *
		 * @return bool
		 */
		private function is_mandatory( \DOMNode $node ) {
			$node        = $this->get_parent( $node, 2 );
			$attributes  = $node->attributes;
			$css_classes = $attributes->getNamedItem( 'class' )->nodeValue;
			$css_classes = explode( ' ', $css_classes );

			return in_array( 'en__mandatory', $css_classes );
		}

		/**
		 * Get the label of a form field.
		 *
		 * @param \DOMNode $node The form field.
		 *
		 * @return string
		 */
		private function get_field_label( \DOMNode $node ) {
			$label_tag = null;
			if ( "input" === $node->nodeName ) {
				$attributes = $node->attributes;
				$type       = $attributes->getNamedItem( 'type' )->nodeValue;

				switch ( $type ) {
					case 'checkbox':
						$label_tag = $node->nextSibling;
						break;
					case 'text':
						$label_tag = $node->parentNode->previousSibling->previousSibling;
						break;
				}
			} elseif ( "select" === $node->nodeName ) {
				$label_tag = $node->parentNode->previousSibling->previousSibling;
			}

			return is_null( $label_tag ) ? '' : trim( $label_tag->textContent );
		}

		/**
		 * Get the id of a form field.
		 *
		 * @param \DOMNode $node    The form field.
		 *
		 * @return null|string
		 */
		private function get_field_id( \DOMNode $node ) {
			$id = '';
			if ( "input" === $node->nodeName ) {
				$attributes = $node->attributes;
				$type       = $attributes->getNamedItem( 'type' )->nodeValue;

				switch ( $type ) {
					case 'checkbox':
						$parent_div = $this->get_parent( $node, 3 );
						break;
					case 'text':
						$parent_div = $this->get_parent( $node, 2 );
						break;
				}
			} elseif ( "select" === $node->nodeName ) {
				$parent_div = $this->get_parent( $node, 2 );
			}

			if ( ! is_null( $parent_div ) ) {
				$attributes  = $parent_div->attributes;
				$css_classes = $attributes->getNamedItem( 'class' )->nodeValue;
				$css_classes = explode( ' ', $css_classes );
				$arr = array_values( preg_grep( "/^en__field--\d+$/", $css_classes ) );

				if ( count( $arr ) > 0 ) {
					$id = preg_replace( "/[^0-9]/", "", $arr[0] );
				}
			}

			return $id;
		}

		/**
		 * Get the parent nodes of a specific node.
		 *
		 * @param \DOMNode $node    The node for which parent node will be returned.
		 * @param int      $levels  Number of levels to go up.
		 *
		 * @return \DOMNode|null
		 */
		private function get_parent( \DOMNode $node, $levels ) {

			if ( is_null( $node ) || ! $node instanceof \DOMNode ) {
				return null;
			}
			for ( $i = 0; $i < $levels; $i ++ ) {
				$node = $node->parentNode;
				if ( is_null( $node ) ) {
					return null;
				}
			}

			return $node;
		}
	}
}
