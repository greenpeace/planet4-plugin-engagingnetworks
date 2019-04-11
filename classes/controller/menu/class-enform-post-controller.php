<?php
/**
 * Enform Post Controller class
 *
 * @package P4EN
 */

namespace P4EN\Controllers\Menu;

if ( ! class_exists( 'Enform_Post_Controller' ) ) {

	/**
	 * Class Enform_Post_Controller
	 *
	 * Creates and registers p4_en custom post type.
	 * Also add filters for p4_en list page.
	 */
	class Enform_Post_Controller extends Controller {

		const POST_TYPE = 'p4en_form';

		/**
		 * Hooks all the needed functions to load the class.
		 */
		public function load() {
			parent::load();
			// Register the hooks.
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'init', [ $this, 'register_post_type' ] );
			add_filter( 'post_row_actions', [ $this, 'modify_post_row_actions' ], 10, 2 );

			add_action( 'add_meta_boxes', [ $this, 'add_meta_box_selected' ] );
			add_action( 'cmb2_admin_init', [ $this, 'add_fields_meta_box' ] );
			add_action( 'cmb2_admin_init', [ $this, 'add_questions_meta_box' ] );
			add_action( 'cmb2_admin_init', [ $this, 'add_optins_meta_box' ] );
		}

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {

				add_submenu_page(
					P4EN_PLUGIN_SLUG_NAME,
					__( 'All EN Forms', 'planet4-engagingnetworks' ),
					__( 'All EN Forms', 'planet4-engagingnetworks' ),
					'edit_posts',
					'edit.php?post_type=' . self::POST_TYPE
				);

				add_submenu_page(
					P4EN_PLUGIN_SLUG_NAME,
					__( 'Add New', 'planet4-engagingnetworks' ),
					__( 'Add New', 'planet4-engagingnetworks' ),
					'edit_posts',
					'post-new.php?post_type=' . self::POST_TYPE
				);

			}
		}

		/**
		 * Register en forms custom post type.
		 */
		public function register_post_type() {

			$labels = array(
				'name'               => _x( 'Engaging Network Forms', 'en forms', 'planet4-engagingnetworks' ),
				'singular_name'      => _x( 'Engaging Network Form', 'en form', 'planet4-engagingnetworks' ),
				'menu_name'          => _x( 'En Forms Menu', 'admin menu', 'planet4-engagingnetworks' ),
				'name_admin_bar'     => _x( 'En Form', 'add new on admin bar', 'planet4-engagingnetworks' ),
				'add_new'            => _x( 'Add New', 'en form', 'planet4-engagingnetworks' ),
				'add_new_item'       => __( 'Add New EN Form', 'planet4-engagingnetworks' ),
				'new_item'           => __( 'New EN Form', 'planet4-engagingnetworks' ),
				'edit_item'          => __( 'Edit EN Form', 'planet4-engagingnetworks' ),
				'view_item'          => __( 'View EN Form', 'planet4-engagingnetworks' ),
				'all_items'          => __( 'All EN Forms', 'planet4-engagingnetworks' ),
				'search_items'       => __( 'Search EN Forms', 'planet4-engagingnetworks' ),
				'parent_item_colon'  => __( 'Parent EN Forms:', 'planet4-engagingnetworks' ),
				'not_found'          => __( 'No en forms found.', 'planet4-engagingnetworks' ),
				'not_found_in_trash' => __( 'No en forms found in Trash.', 'planet4-engagingnetworks' ),
			);

			register_post_type(
				self::POST_TYPE,
				[
					'labels'              => $labels,
					'description'         => __( 'EN Forms', 'planet4-engagingnetworks' ),
					'rewrite'             => false,
					'query_var'           => false,
					'public'              => false,
					'publicly_queryable'  => true,
					'capability_type'     => 'page',
					'has_archive'         => true,
					'hierarchical'        => false,
					'menu_position'       => null,
					'exclude_from_search' => true,
					'map_meta_cap'        => true,
					// necessary in order to use WordPress default custom post type list page.
					'show_ui'             => true,
					// hide it from menu, as we are using custom submenu pages.
					'show_in_menu'        => false,
					'supports'            => [ 'title' ],
				]
			);
		}

		/**
		 * Filter for post_row_actions. Alters edit action link and removes Quick edit action.
		 *
		 * @param string[] $actions An array of row action links. Defaults are
		 *                          'Edit', 'Quick Edit', 'Restore', 'Trash',
		 *                          'Delete Permanently', 'Preview', and 'View'.
		 * @param \WP_Post $post The post object.
		 *
		 * @return array  The filtered actions array.
		 */
		public function modify_post_row_actions( $actions, $post ) {

			// Check if post is of p4en_form_post type.
			if ( self::POST_TYPE === $post->post_type ) {

				/*
				 * Hide Quick Edit.
				 */
				$custom_actions = [
					'inline hide-if-no-js' => '',
				];

				$actions = array_merge( $actions, $custom_actions );
			}

			return $actions;
		}

		/**
		 * Creates a Metabox on the side of the Add/Edit EN Form.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function add_meta_box_selected( $post ) {
			add_meta_box(
				'meta-box-selected',
				__( 'Selected Components', 'planet4-engagingnetworks' ),
				[ $this, 'view_meta_box_selected' ],
				[ self::POST_TYPE ],
				'normal',
				'high',
				$post
			);
		}

		/**
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function view_meta_box_selected( $post ) {
			$this->view->selected_meta_box( [
				'components' => [
					[
						'name'     => 'email',
						'type'     => 'email',
						'label'    => 'Email',
						'value'    => 'example@example.com',
						'required' => true,
						'hidden'   => false,
					],
				],
				'domain'     => 'planet4-engagingnetworks',
			] );
		}

		/**
		 *
		 */
		public function add_fields_meta_box() {
			$prefix = self::POST_TYPE . '-fields-';

			$meta_box = new_cmb2_box(
				[
					'id'           => $prefix . 'metabox',
					'title'        => __( 'Fields', 'planet4-engagingnetworks' ),
					'object_types' => [ self::POST_TYPE ],
				]
			);

			$meta_box->add_field(
				[
					'name' => __( 'Available Fields', 'planet4-engagingnetworks' ),
					'desc' => __( 'Available EN Customer Fields', 'planet4-engagingnetworks' ),
					'id'   => $prefix . 'name',
					'type' => 'multicheck',
					'options' => [
						'check1' => 'Check One',
						'check2' => 'Check Two',
						'check3' => 'Check Three',
					],
				]
			);
		}

		/**
		 *
		 */
		public function add_questions_meta_box() {
			$prefix = self::POST_TYPE . '-questions-';

			$meta_box = new_cmb2_box(
				[
					'id'           => $prefix . 'metabox',
					'title'        => __( 'Questions', 'planet4-engagingnetworks' ),
					'object_types' => [ self::POST_TYPE ],
				]
			);

			$meta_box->add_field(
				[
					'name' => __( 'Available Questions', 'planet4-engagingnetworks' ),
					'desc' => __( 'Available EN Customer Questions', 'planet4-engagingnetworks' ),
					'id'   => $prefix . 'name',
					'type' => 'multicheck',
					'options' => [
						'check1' => 'Check One',
						'check2' => 'Check Two',
						'check3' => 'Check Three',
					],
				]
			);
		}

		/**
		 *
		 */
		public function add_optins_meta_box() {
			$prefix = self::POST_TYPE . '-optins-';

			$meta_box = new_cmb2_box(
				[
					'id'           => $prefix . 'metabox',
					'title'        => __( 'Opt-ins', 'planet4-engagingnetworks' ),
					'object_types' => [ self::POST_TYPE ],
				]
			);

			$meta_box->add_field(
				[
					'name' => __( 'Available Opt-ins', 'planet4-engagingnetworks' ),
					'desc' => __( 'Available EN Customer Opt-ins', 'planet4-engagingnetworks' ),
					'id'   => $prefix . 'name',
					'type' => 'multicheck',
					'options' => [
						'check1' => 'Check One',
						'check2' => 'Check Two',
						'check3' => 'Check Three',
					],
				]
			);
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $settings The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $settings ): bool {
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {
		}

	}
}
