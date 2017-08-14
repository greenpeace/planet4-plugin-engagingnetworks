<?php

if ( ! class_exists( 'P4EN_Controller' ) ) {

	/**
	 * Class P4EN_Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	class P4EN_Controller {

		/** @var P4EN_View $view */
		private $view;


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param P4EN_View $view
		 */
		public function __construct( P4EN_View $view ) {
			$this->view = $view;
		}

		/**
		 * Load the menu & submenus for the plugin
		 */
		public function load_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {

				add_menu_page(
					'EngagingNetworks',
					'EngagingNetworks',
					'edit_pages',
					P4EN_PLUGIN_SLUG_NAME,
					array( $this, 'prepare_pages' ),
					P4EN_ADMIN_DIR . 'images/logo_menu_page_16x16.jpg'
				);

				add_submenu_page(
					P4EN_PLUGIN_SLUG_NAME,
					__( 'EN Pages DataTable', 'planet4-engagingnetworks' ),
					__( 'EN Pages DataTable', 'planet4-engagingnetworks' ),
					'edit_pages',
					'pages-datatable',
					array( $this, 'prepare_pages_datatable' )
				);

				if ( current_user_can( 'manage_options' ) ) {

					add_submenu_page(
						P4EN_PLUGIN_SLUG_NAME,
						__( 'Settings', 'planet4-engagingnetworks' ),
						__( 'Settings', 'planet4-engagingnetworks' ),
						'manage_options',
						'settings',
						array( $this, 'prepare_settings' )
					);
				} else {
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'planet4-engagingnetworks' ),'Permission Denied Error',
						array(
							'response' => WP_Http::OK,
							'back_link' => true,
						)
					);
				}

				add_action( 'admin_init', array( $this, 'register_settings' ) );
			}
		}

		/**
		 *
		 */
		public function prepare_pages() {
			$this->view->pages( [
				'pages' => [],
			] );
		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function prepare_pages_datatable() {

			$pages = [];
			$pages_settings = get_option( 'p4en_pages_settings' );

			if ( ! isset( $pages_settings['p4en_pages_subtype'] ) ) {
				$pages_settings['p4en_pages_subtype'] = 'pet';          // Retrieve the all petitions by default
			}
			$params = '?type=' . strtolower( $pages_settings['p4en_pages_subtype'] );
			$params .= isset( $pages_settings['p4en_pages_status'] ) && 'all' !== $pages_settings['p4en_pages_status'] ? '&status=' . $pages_settings['p4en_pages_status'] : '';

			$main_settings = get_option( 'p4en_main_settings' );
			if ( isset( $main_settings['p4en_private_api'] ) ) {

				$private_api_token = $main_settings['p4en_private_api'];

				$response = wp_remote_post( ENS_AUTH_URL, [
					'headers' => [
						'Content-Type' => 'application/json; charset=UTF-8',
					],
					'body' => $private_api_token,
				] );

				if ( is_wp_error( $response ) ) {
					echo $response->get_error_message();

				} elseif ( is_array( $response ) && WP_Http::OK !== $response['response']['code'] ) {
					echo $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed

				} else {
					$body = json_decode( $response['body'], true );
					$ens_auth_token = $body['ens-auth-token'];
					$url = ENS_GET_PAGES . $params;

					$response = wp_remote_get( $url, [
						'headers' => [
							'ens-auth-token' => $ens_auth_token,
						],
					] );
					$pages = json_decode( $response['body'], true );
				}
			} else {
				wp_die(
					'<div class="error is-dismissible">' .
					'<u>' . __( 'Plugin Settings Error!', 'planet4-engagingnetworks' ) . '</u><br /><br />' . __( 'Plugin Settings are not configured well!', 'planet4-engagingnetworks' ) . '<br />' .
					'</div>', 'Plugin Settings Error', array(
						'response' => WP_Http::OK,
						'back_link' => true,
					)
				);
			}

			$this->prepare_pages__datatable_output( $pages );
			// Provide hook for other plugins.
			$pages = apply_filters( 'p4en_filter_pages_datatable_output', $pages );

			$this->view->pages_datatable( [
				'pages' => $pages,
				'pages_settings' => $pages_settings,
				'subtypes' => P4EN_Page_Controller::SUBTYPES,
				'statuses' => P4EN_Page_Controller::STATUSES,
			] );
		}

		/**
		 *
		 * @param array $pages
		 */
		public function prepare_pages__datatable_output( &$pages ) {
			foreach ( $pages as &$page ) {

				$page['campaignStatus'] = ucfirst( $page['campaignStatus'] );
				if ( ! $page['subType'] ) {
					$page['subType'] = strtoupper( $page['type'] );
				}

				switch ( $page['type'] ) {
					case 'dc':
						switch ( $page['subType'] ) {
							case 'DCF':
								$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/data/1' );
								break;
							case 'PET':
								$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
								break;
							default:
								$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
						}
						break;
					case 'nd':
						$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/donation/1' );
						break;
				}
			}
		}

		/**
		 * Render the settings page of the plugin.
		 */
		public function prepare_settings() {
			$this->view->settings( [
				'settings' => get_option( 'p4en_main_settings' ),
				'available_languages' => P4EN_LANGUAGES,
			] );
		}

		/**
		 * Loads the saved language.
		 */
		public function load_locale() : string {
			$main_settings = get_option( 'p4en_main_settings' );
			return isset( $main_settings['p4en_lang'] ) ? $main_settings['p4en_lang'] : '';
		}

		/**
		 * Register and store the settings and their data.
		 */
		public function register_settings() {
			$args = array(
				'type'              => 'string',
				'group'             => 'p4en_main_settings_group',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_main_settings_group', 'p4en_main_settings', $args );

			$args2 = array(
				'type'              => 'string',
				'group'             => 'p4en_pages_settings_group',
				'description'       => 'Planet 4 - EngagingNetworks settings',
				'sanitize_callback' => array( $this, 'valitize' ),
				'show_in_rest'      => false,
			);

			register_setting( 'p4en_pages_settings_group', 'p4en_pages_settings', $args2 );
		}

		/**
		 * Validates and sanitizes the settings input.
		 * @param $settings array
		 *
		 * @return mixed
		 */
		public function valitize( $settings ) : array {
			if ( $this->validate( $settings ) ) {
				$this->sanitize( $settings );
			}

			return $settings;
		}

		/**
		 * Validates the settings input.
		 * @param $settings array
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			$has_errors = false;

			if ( $settings ) {
				if ( isset( $settings['p4en_public_api'] ) && 36 !== strlen( $settings['p4en_public_api'] ) ) {
					add_settings_error(
						'p4en_main_settings-p4en_public_api',
						esc_attr( 'p4en_main_settings-p4en_public_api' ),
						__( 'Invalid value for Public API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
				if ( isset( $settings['p4en_private_api'] ) && 36 !== strlen( $settings['p4en_private_api'] ) ) {
					add_settings_error(
						'p4en_main_settings-p4en_private_api',
						esc_attr( 'p4en_main_settings-p4en_private_api' ),
						__( 'Invalid value for Private API', 'planet4-engagingnetworks' ),
						'error'
					);
					$has_errors = true;
				}
			}
			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 * @param $settings array
		 *
		 * @return array
		 */
		public function sanitize( $settings ) : array {
			if ( $settings ) {
				foreach ( $settings as $name => $setting ) {
					$settings[ $name ] = sanitize_text_field( $setting );
				}
			}
			return $settings;
		}

		/**
		 * Load assets only on the admin pages of the plugin.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function load_admin_assets( $hook ) {
			// Load the assets only on the plugin's pages.
			if ( strpos( $hook, P4EN_PLUGIN_SLUG_NAME ) === false ) {
				return;
			}

			wp_enqueue_script( 'p4en_jquery', '//code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );

			if ( strpos( $hook, 'pages-datatable' ) !== false ) {
				wp_enqueue_style( 'p4en_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', array(), '4.0.0-alpha.6' );
				wp_enqueue_style( 'p4en_datatables_bootstrap', 'https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css', array( 'p4en_bootstrap' ), '1.10.15' );

				wp_enqueue_script( 'p4en_datatables', 'https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js', array( 'p4en_jquery' ), '1.10.15', true );
				wp_enqueue_script( 'p4en_datatables_bootstrap', 'https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js', array( 'p4en_datatables' ), '1.10.15', true );
			}

			wp_enqueue_style( 'p4en_admin_style', P4EN_ADMIN_DIR . 'css/admin.css', array(), '0.1' );
			wp_enqueue_script( 'p4en_admin_script', P4EN_ADMIN_DIR . 'js/admin.js', array(), '0.1', true );

		}

		/**
		 * Initialize internationalization (i18n) for this plugin.
		 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
		 */
		public function init_i18n() {
			load_plugin_textdomain( 'planet4-engagingnetworks', false, P4EN_PLUGIN_DIRNAME . '/languages/' );
		}
	}
}
