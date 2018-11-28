<?php
/**
 * EN Form class
 *
 * @package P4EN
 */

namespace P4EN\Controllers\Blocks;

use P4EN\Controllers\Ensapi_Controller as Ensapi;
use P4EN\Controllers\Menu\Pages_Datatable_Controller;

if ( ! class_exists( 'ENForm_Controller' ) ) {

	/**
	 * Class ENForm_Controller
	 *
	 * @package P4EN\Controllers\Blocks
	 */
	class ENForm_Controller extends Controller {

		/** @const string BLOCK_NAME */
		const BLOCK_NAME = 'enform';
		/** @const array ENFORM_PAGE_TYPES */
		const ENFORM_PAGE_TYPES = [ 'PET', 'ND', 'EMS' ];

		/** @var Ensapi $ensapi */
		private $ens_api = null;

		/**
		 * Hooks all the needed functions to load the block.
		 */
		public function load() {
			parent::load();
			add_action( 'admin_print_footer_scripts-post.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_print_footer_scripts-post-new.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_assets' ] );
		}

		/**
		 * Load assets only on the admin pages of the plugin.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function load_admin_assets( $hook ) {
			if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
				return;
			}

			wp_enqueue_style( 'p4en_admin_style_blocks', P4EN_ADMIN_DIR . 'css/admin_en.css', [], '0.1' );
			add_action(
				'enqueue_shortcode_ui',
				function () {
					wp_enqueue_script( 'en-ui-heading-view', P4EN_ADMIN_DIR . 'js/en_ui_heading_view.js', [ 'shortcode-ui' ], '0.1', true );
					wp_enqueue_script( 'en-ui', P4EN_ADMIN_DIR . 'js/en_ui.js', [ 'shortcode-ui' ], '0.1', true );
				}
			);
		}

		/**
		 * Load underscore templates to footer.
		 */
		public function print_admin_footer_scripts() {
			echo $this->get_template( 'en-ui' ); // WPCS: XSS ok.
		}

		/**
		 * Shortcode UI setup for the ENForm shortcode.
		 *
		 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
		 */
		public function prepare_fields() {
			$pages   = [];
			$options = [];

			// Get EN pages only on admin panel.
			if ( is_admin() ) {
				$main_settings = get_option( 'p4en_main_settings' );

				if ( isset( $main_settings['p4en_private_api'] ) ) {
					$ens_private_token = $main_settings['p4en_private_api'];
					$this->ens_api     = new Ensapi( $ens_private_token );
					$pages             = $this->ens_api->get_pages_by_types_status( self::ENFORM_PAGE_TYPES, 'live' );
					uasort( $pages, function ( $a, $b ) {
						return ( $a['name'] ?? '' ) <=> ( $b['name'] ?? '' );
					} );
				}

				$options = [
					[
						'value' => '0',
						'label' => __( '- Select Page -', 'planet4-engagingnetworks' ),
					],
				];
				if ( $pages ) {
					foreach ( $pages as $type => $group_pages ) {
						$group_options = [];
						foreach ( $group_pages as $page ) {
							$group_options[] = [
								'value' => (string) $page['id'],
								'label' => (string) $page['name'],
							];
						}
						$options[] = [
							'label'   => Pages_Datatable_Controller::SUBTYPES[ $type ]['subType'],
							'options' => $group_options,
						];
					}
				}
			}

			$fields = [
				[
					'label'       => __( 'Engaging Network Live Pages', 'planet4-engagingnetworks' ),
					'description' => $pages ? __( 'Select the Live EN page that this form will be submitted to.', 'planet4-engagingnetworks' ) : __( 'Check your EngagingNetworks settings!', 'planet4-engagingnetworks' ),
					'attr'        => 'en_page_id',
					'type'        => 'select',
					'options'     => $options,
				],
				[
					'attr'              => 'en_form_style',
					'label'             => __( 'What style of form do you need?', 'planet4-engagingnetworks' ),
					'type'              => 'p4en_radio',
					'options'           => [
						[
							'value' => 'full-width',
							'label' => __( 'Full Width', 'planet4-engagingnetworks' ),
							'desc'  => 'Best for use inside pages and posts.',
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidth.png' ),
						],
						[
							'value' => 'full-width-bg',
							'label' => __( 'Full width background', 'planet4-engagingnetworks' ),
							'desc'  => 'This options has a background image that expands the full width of the browser.',
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidthbg.png' ),
						],
					],
				],
				[
					'label'       => __( 'Background', 'planet4-engagingnetworks' ),
					'attr'        => 'background',
					'type'        => 'attachment',
					'libraryType' => [ 'image' ],
					'addButton'   => __( 'Select Background Image', 'planet4-engagingnetworks' ),
					'frameTitle'  => __( 'Select Background Image', 'planet4-engagingnetworks' ),
				],
			];

			// Get supporter fields from EN and use them on the fly.
			$supporter_fields = $this->get_supporter_fields();

			if ( $supporter_fields ) {
				foreach ( $supporter_fields as $supporter_field ) {
					$attr_parts = [
						$supporter_field['id'],
						$supporter_field['name'],
						( $supporter_field['mandatory'] ? 'true' : 'false' ),
						str_replace( ' ', '-', $supporter_field['label'] ),
						$supporter_field['type'],
					];

					$args = [
						'label'       => $supporter_field['name'],
						'description' => $supporter_field['label'],
						'attr'        => strtolower( implode( '_', $attr_parts ) ),
						'type'        => 'checkbox',
					];
					if ( $supporter_field['mandatory'] ) {
						$args['value'] = 'true';
					}
					$fields[] = $args;
				}
			}

			// Define the Shortcode UI arguments.
			$shortcode_ui_args = [
				'label'         => __( 'Engaging Networks Form', 'planet4-engagingnetworks' ),
				'listItemImage' => '<img src="' . esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enform.png' ) . '" />',
				'attrs'         => $fields,
				'post_type'     => P4EN_ALLOWED_PAGETYPE,
			];

			shortcode_ui_register_for_shortcode( 'shortcake_' . self::BLOCK_NAME, $shortcode_ui_args );
		}

		/**
		 * Get all the data that will be needed to render the block correctly.
		 *
		 * @param array  $fields This is the array of fields of the block.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode tag of the block.
		 *
		 * @return array The data to be passed in the View.
		 */
		public function prepare_data( $fields, $content, $shortcode_tag ) : array {
			$excluded_fields = [ 'en_page_id', 'en_form_style', 'background' ];

			$fields = $this->ignore_unused_attributes( $fields, $excluded_fields );

			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					if ( ! in_array( $key, $excluded_fields ) ) {
						$attr_parts     = explode( '_', $key );
						$fields[ $key ] = [
							'id'        => $attr_parts[0],
							'name'      => $attr_parts[1],
							'mandatory' => $attr_parts[2],
							'label'     => str_replace( '-', ' ', $attr_parts[3] ),
							'type'      => $attr_parts[4],
							'value'     => $value,
						];
					}
				}
			}

			if ( isset( $fields['background'] ) ) {
				$image_id                    = '' !== $fields['background'] ? $fields['background'] : $p4_happy_point_bg_image;
				$img_meta                    = wp_get_attachment_metadata( $image_id );
				$fields['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
				$fields['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
				$fields['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
			}
			$fields['default_image']     = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';

			$data = [];
			// If user is logged in.
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();

				// If we have not intialized yet the Ensapi_Controller then do it here.
				if ( ! $this->ens_api ) {
					$main_settings = get_option( 'p4en_main_settings' );
					if ( isset( $main_settings['p4en_private_api'] ) ) {
						$ens_private_token = $main_settings['p4en_private_api'];
						$this->ens_api     = new Ensapi( $ens_private_token );
					}
				}
				$response = $this->ens_api->get_supporter_by_email( $current_user->user_email );

				if ( is_array( $response ) && $response['body'] ) {
					$supporter         = json_decode( $response['body'], true );
					$data['supporter'] = $supporter;
				}
			}

			$this->handle_submit( $data );

			$data = array_merge( $data, [
				'fields'          => $fields,
				'second_page_msg' => __( 'Thanks for signing!', 'planet4-engagingnetworks' ),
				'domain'          => 'planet4-engagingnetworks',
			] );

			return $data;
		}

		/**
		 * Retrieve supporter fields from EN and prepare them for use in P4.
		 *
		 * @return array Associative array of supporter fields if retrieval from EN was successful or empty array otherwise.
		 */
		public function get_supporter_fields() : array {
			if ( $this->ens_api ) {
				$response = $this->ens_api->get_supporter_fields();

				if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
					$en_supporter_fields = json_decode( $response['body'], true );

					foreach ( $en_supporter_fields as $en_supporter_field ) {
						if ( 'Not Tagged' !== $en_supporter_field['tag'] ) {
							$supporter_fields[] = [
								'id'        => $en_supporter_field['id'],
								'name'      => $en_supporter_field['property'],
								'mandatory' => false,
								'label'     => $en_supporter_field['name'],
								'type'      => strpos( $en_supporter_field['property'], 'country' ) === false ? 'text' : 'country',
							];
						}
					}
					return $supporter_fields;
				}
			}
			return [];
		}

		/**
		 * Handle form submit.
		 *
		 * @param array $data The data that we will get from the form submission.
		 *
		 * @return bool True if validation is ok, false if validation fails or if it is not a POST.
		 */
		public function handle_submit( &$data ) : bool {
			if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				// CSRF protection.
				$nonce_action          = 'enform_submit';
				$nonce                 = wp_create_nonce( $nonce_action );
				$data['nonce_action']  = $nonce_action;
				$data['enform_submit'] = 0;
				$data['error_msg']     = '';

				if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
					$data['error_msg'] = __( 'Invalid nonce!', 'planet4-engagingnetworks' );
					return false;
				} else {
					$en_page_id = $_POST['en_page_id'];
					$result = $this->valitize( [
						'en_page_id' => $en_page_id,
					] );

					if ( false === $result ) {
						$data['error_msg'] = __( 'Invalid input!', 'planet4-engagingnetworks' );
						return false;
					}

					if ( $this->ens_api ) {
						$fields = [];
						foreach ( $_POST as $key => $value ) {
							if ( false !== strpos( $key, 'supporter_' ) ) {
								$fields[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
							}
						}

						$response = $this->ens_api->process_page( $en_page_id, $fields );
						if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
							$data = json_decode( $response['body'], true );
							$data['enform_submit'] = 1;
						} else {
							$data['error_msg'] = $response;
							return false;
						}
					}
				}
				return true;
			}
			return false;
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $input ) : bool {
			if ( ! isset( $input['en_page_id'] ) || $input['en_page_id'] <= 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {
			if ( isset( $input['en_page_id'] ) ) {
				$input['en_page_id'] = sanitize_text_field( $input['en_page_id'] );
			}
		}
	}
}
