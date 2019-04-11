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
					'publicly_queryable'  => false,
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

				]
			);
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'init', [ $this, 'register_post_type' ] );
			add_filter( 'post_row_actions', [ $this, 'modify_post_row_actions' ], 10, 2 );
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
