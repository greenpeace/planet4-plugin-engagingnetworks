<?php
/**
 * EN Form class
 *
 * @package P4EN
 */

namespace P4EN\Controllers\Blocks;

use P4EN\Controllers\Ensapi_Controller as Ensapi;
use P4EN\Controllers\Menu\Pages_Datatable_Controller;
use P4EN\Models\Questions_Model;
use Timber\Timber;

if ( ! class_exists( 'ENForm_Controller' ) ) {

	/**
	 * Class ENForm_Controller
	 *
	 * @package P4EN\Controllers\Blocks
	 */
	class ENForm_Controller extends Controller {

		/**
		 * Block name
		 *
		 * @const string BLOCK_NAME
		 */
		const BLOCK_NAME = 'enform';

    /**
		 * Page types for EN forms
		 *
		 * @const array ENFORM_PAGE_TYPES
		 */
		const ENFORM_PAGE_TYPES = [ 'PET', 'EMS' ];

		/**
		 * ENSAPI Object
		 *
		 * @var Ensapi $ensapi
		 */
		private $ens_api = null;

		/**
		 * Hooks all the needed functions to load the block.
		 */
		public function load() {
			parent::load();
			add_action( 'admin_print_footer_scripts-post.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_print_footer_scripts-post-new.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_assets' ] );
			add_action( 'wp_ajax_handle_submit', [ $this, 'handle_submit' ] );
			add_action( 'wp_ajax_nopriv_handle_submit', [ $this, 'handle_submit' ] );
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

			wp_enqueue_style( 'p4en_admin_style_blocks', P4EN_ADMIN_DIR . 'css/admin_en.css', [], '0.3' );
			add_action(
				'enqueue_shortcode_ui',
				function () {
					wp_enqueue_script( 'en-ui-heading-view', P4EN_ADMIN_DIR . 'js/en_ui_heading_view.js', [ 'shortcode-ui' ], '0.1', true );
					wp_register_script( 'en-ui', P4EN_ADMIN_DIR . 'js/en_ui.js', [ 'shortcode-ui' ], '0.3', true );

					// Localize en-ui script.
					$translation_array = array(
						'en_fields_description_1' => __( 'What kind of Information do you want to send to EN?', 'planet4-engagingnetworks' ),
						'en_fields_description_2' => __( 'Make sure to select the same fields of your Engaging Networks page / form', 'planet4-engagingnetworks' ),
					);
					wp_localize_script( 'en-ui', 'p4_en_blocks_enform_translations', $translation_array );
					wp_enqueue_script( 'en-ui' );
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
					uasort(
						$pages,
						function ( $a, $b ) {
							return ( $a['name'] ?? '' ) <=> ( $b['name'] ?? '' );
						}
					);
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
					'attr'    => 'en_form_style',
					'label'   => __( 'What style of form do you need?', 'planet4-engagingnetworks' ),
					'type'    => 'p4en_radio',
					'options' => [
						[
							'value' => 'full-width',
							'label' => __( 'Full Width', 'planet4-engagingnetworks' ),
							'desc'  => 'Best to use inside pages.',
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidth.png' ),
						],
						[
							'value' => 'full-width-bg',
							'label' => __( 'Full width background', 'planet4-engagingnetworks' ),
							'desc'  => 'This option has a background image that expands the full width of the browser.',
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidthbg.png' ),
						],
					],
				],
				[
					'label' => __( 'Title', 'planet4-engagingnetworks' ),
					'attr'  => 'title',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter title', 'planet4-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Description', 'planet4-engagingnetworks' ),
					'attr'  => 'description',
					'type'  => 'textarea',
					'meta'  => [
						'placeholder' => __( 'Enter description', 'planet4-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Button text', 'planet4-engagingnetworks' ),
					'attr'  => 'button_text',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter the text of the button', 'planet4-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Thank you Title', 'planet4-engagingnetworks' ),
					'attr'  => 'thankyou_title',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter Thank you Title', 'planet4-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Thank you Subtitle', 'planet4-engagingnetworks' ),
					'attr'  => 'thankyou_subtitle',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter Thank you Subtitle', 'planet4-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Thank you Url', 'planet4-engagingnetworks' ),
					'attr'  => 'thankyou_url',
					'type'  => 'url',
					'meta'  => [
						'placeholder' => __( 'Enter Thank you url', 'planet4-engagingnetworks' ),
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
					$args     = [
						'label' => $supporter_field['label'],
						'name'  => $supporter_field['name'],
						'attr'  => 'field__' . $supporter_field['id'],
						'type'  => 'checkbox',
					];
					$fields[] = $args;

					$args_mandatory = [
						'label' => __( 'required', 'planet4-engagingnetworks' ),
						'name'  => $supporter_field['name'] . '_mandatory',
						'attr'  => $args['attr'] . '__mandatory',
						'type'  => 'checkbox',
					];
					$fields[]       = $args_mandatory;
				}
			}

			// Get supporter fields from EN and use them on the fly.
			$questions_model     = new Questions_Model();
			$supporter_questions = $questions_model->get_questions();

			if ( $supporter_questions ) {
				foreach ( $supporter_questions as $supporter_question ) {
					$args     = [
						'label'       => $supporter_question['label'],
						'description' => 'GEN' === $supporter_question['type'] ? 'Question' : 'Opt-in',
						'attr'        => $supporter_question['id'] . '__' . $supporter_question['questionId'],
						'type'        => 'checkbox',
					];
					$fields[] = $args;

					$args_mandatory = [
						'label' => __( 'required', 'planet4-engagingnetworks' ),
						'name'  => $supporter_question['name'] . '_mandatory',
						'attr'  => $args['attr'] . '__mandatory',
						'type'  => 'checkbox',
					];
					$fields[]       = $args_mandatory;
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
			$fields = $this->ignore_unused_attributes( $fields );

			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					$attr_parts = explode( '__', $key );
					if ( 'field' === $attr_parts[0] ) {
						$fields[ $key ] = [
							'id'        => $attr_parts[1],
							'mandatory' => $fields[ $key . '__mandatory' ] ?? 'false',
							'value'     => $value,
						];
					} else {
						if ( is_numeric( $attr_parts[0] ) && 'mandatory' !== $attr_parts[ count( $attr_parts ) - 1 ] ) {
							$fields[ $attr_parts[1] ] = [
								'id'         => $attr_parts[0],
								'questionId' => $attr_parts[1],
								'mandatory'  => $fields[ $key . '__mandatory' ] ?? 'false',
								'value'      => $value,
							];
						}
					}
				}
			}

			// Get supporter fields from EN and use them on the fly.
			$supporter_fields = $this->get_supporter_fields();
			if ( $supporter_fields ) {
				foreach ( $supporter_fields as $key => $supporter_field ) {
					if ( isset( $fields[ 'field__' . $supporter_field['id'] ] ) ) {
						$fields[ 'field__' . $supporter_field['id'] ] = array_merge( $fields[ 'field__' . $supporter_field['id'] ], $supporter_field );
						unset( $fields[ 'field__' . $supporter_field['id'] . '__mandatory' ] );
					}
					unset( $supporter_fields[ $key ] );
				}
			}

			// Get supporter questions from database.
			$questions_model = new Questions_Model();
			$questions       = $questions_model->get_questions();
			if ( $questions ) {
				foreach ( $questions as $key => $question ) {
					if ( isset( $fields[ $question['questionId'] ] ) ) {
						$questions[ $question['questionId'] ] = $question;
					}
					unset( $questions[ $key ] );
				}
			}

			// Handle background image.
			if ( isset( $fields['background'] ) ) {
				$options                     = get_option( 'planet4_options' );
				$p4_happy_point_bg_image     = $options['happy_point_bg_image_id'] ?? '';
				$image_id                    = '' !== $fields['background'] ? $fields['background'] : $p4_happy_point_bg_image;
				$img_meta                    = wp_get_attachment_metadata( $image_id );
				$fields['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
				$fields['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
				$fields['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
			}
			$fields['default_image'] = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';

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

			// If there are questions and the EN supporter has previously responded to those then apply those responses.
			if ( $questions ) {
				if ( isset( $data['supporter']['questions'] ) ) {
					foreach ( (array) $data['supporter']['questions'] as $en_supporter_question ) {
						if ( isset( $questions[ $en_supporter_question['id'] ] ) ) {
							$questions[ $en_supporter_question['id'] ]['value'] = $en_supporter_question['response'];
						}
					}
				}
				$data['supporter']['questions'] = $questions;
			}

			$data = array_merge(
				$data,
				[
					'fields'          => $fields,
					'redirect_url'    => isset( $fields['thankyou_url'] ) ? filter_var( $fields['thankyou_url'], FILTER_VALIDATE_URL ) : '',
					'nonce_action'    => 'enform_submit',
					'second_page_msg' => __( 'Thanks for signing!', 'planet4-engagingnetworks' ),
					'domain'          => 'planet4-engagingnetworks',
				]
			);

			return $data;
		}

		/**
		 * Retrieve supporter fields from EN and prepare them for use in P4.
		 *
		 * @return array Associative array of supporter fields if retrieval from EN was successful or empty array otherwise.
		 */
		public function get_supporter_fields() : array {
			// If we have not initialized yet the Ensapi_Controller then do it here.
			if ( ! $this->ens_api ) {
				$main_settings = get_option( 'p4en_main_settings' );
				if ( isset( $main_settings['p4en_private_api'] ) ) {
					$ens_private_token = $main_settings['p4en_private_api'];
					$this->ens_api     = new Ensapi( $ens_private_token );
				}
			}
			if ( $this->ens_api ) {
				$response = $this->ens_api->get_supporter_fields();

				if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
					$en_supporter_fields = json_decode( $response['body'], true );

					foreach ( $en_supporter_fields as $en_supporter_field ) {
						if ( 'Not Tagged' !== $en_supporter_field['tag'] ) {
							$type = 'text';
							if ( false !== strpos( $en_supporter_field['property'], 'country' ) ) {
								$type = 'country';
							} elseif ( false !== strpos( $en_supporter_field['property'], 'emailAddress' ) ) {
								$type = 'email';        // Set the type of the email input field as email.
							}

							$supporter_fields[] = [
								'id'    => $en_supporter_field['id'],
								'name'  => $en_supporter_field['property'],
								'label' => $en_supporter_field['name'],
								'type'  => $type,
							];
						}
					}
					return $supporter_fields;
				}
			}
			return [];
		}

		/**
		 * Handle form submit asynchronously.
		 */
		public function handle_submit() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {
				$main_settings     = get_option( 'p4en_main_settings' );
				$ens_private_token = $main_settings['p4en_private_api'];
				$this->ens_api     = new Ensapi( $ens_private_token );
				$nonce             = $_POST['_wpnonce'];   // CSRF protection.

				if ( ! wp_verify_nonce( $nonce, 'enform_submit' ) ) {
					$data['error_msg'] = __( 'Invalid nonce!', 'planet4-engagingnetworks' );
				} else {
					$values = $_POST['values'] ?? [];
					$fields = $this->valitize( $values );

					if ( false === $fields ) {
						$data['error_msg'] = __( 'Invalid input!', 'planet4-engagingnetworks' );
					}
					if ( $this->ens_api ) {
						$response = $this->ens_api->process_page( $fields['en_page_id'], $fields );
						if ( is_array( $response ) && \WP_Http::OK === $response['response']['code'] && $response['body'] ) {
							$data = json_decode( $response['body'], true );
						} else {
							$data['error_msg'] = $response;
						}
					}
				}
				Timber::$locations = P4EN_INCLUDES_DIR;
				Timber::render(
					[ 'tease-thankyou.twig' ],
					[
						'title'    => $fields['thankyou_title'] ?? '',
						'subtitle' => $fields['thankyou_subtitle'] ?? '',
						'error'    => $data['error_msg'] ?? '',
					]
				);
				wp_die();
			}
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $input ) : bool {
			if (
				( ! isset( $input['en_page_id'] ) || $input['en_page_id'] <= 0 ) ||
				( ! isset( $input['supporter.emailAddress'] ) || false === filter_var( $input['supporter.emailAddress'], FILTER_VALIDATE_EMAIL ) )
			) {
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
			foreach ( $input as $key => $value ) {
				if ( 'supporter.emailAddress' === $key ) {
					$input[ $key ] = sanitize_email( $value );

				} elseif ( false !== strpos( $key, 'supporter.question.' ) ) {  // Question/Optin name is in the form of 'supporter.question.{id}'.
					$key_parts = explode( '.', $key );
					if ( isset( $key_parts[2] ) ) {
						$input['supporter.questions'][ "question.$key_parts[2]" ] = sanitize_text_field( $value );
						unset( $input[ "supporter.question.$key_parts[2]" ] );
					}
				} else {
					$input[ $key ] = sanitize_text_field( $value );
				}
			}
		}
	}
}
