<?php
/**
 * Plugin Name: Planet4 - EngagingNetworks
 * Description: Connects Planet4 with the Engaging Networks platform.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-engagingnetworks
 * Version: 0.1
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-engagingnetworks
 *
 * License:     GPLv3
 * Copyright (C) 2017 Greenpeace International
 */


/**
 * Followed WordPress plugins best practices from https://developer.wordpress.org/plugins/the-basics/best-practices/
 * Followed WordPress-Core coding standards https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/
 * Followed WordPress-VIP coding standards https://vip.wordpress.com/documentation/code-review-what-we-look-for/
 *
 * TODO - Replace Singleton with Dependency Injection (DI).
 * TODO - Namespace the plugin.
 * TODO - Check security - https://developer.wordpress.org/plugins/security/
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) or die( 'Direct access is forbidden !' );

/* ========================
	  C O N S T A N T S
   ======================== */

if ( ! defined( 'P4EN_MIN_PHP_VERSION' ) )  define( 'P4EN_MIN_PHP_VERSION',     '7.0' );
if ( ! defined( 'P4EN_PLUGIN_BASENAME' ) )  define( 'P4EN_PLUGIN_BASENAME',     plugin_basename( __FILE__ ) );
if ( ! defined( 'P4EN_PLUGIN_DIRNAME' ) )   define( 'P4EN_PLUGIN_DIRNAME',      dirname( P4EN_PLUGIN_BASENAME ) );
if ( ! defined( 'P4EN_PLUGIN_DIR' ) )       define( 'P4EN_PLUGIN_DIR',          WP_PLUGIN_DIR . '/' . P4EN_PLUGIN_DIRNAME );
if ( ! defined( 'P4EN_PLUGIN_NAME' ) )      define( 'P4EN_PLUGIN_NAME',         'Planet4 - EngagingNetworks' );
if ( ! defined( 'P4EN_PLUGIN_SHORT_NAME' ) )define( 'P4EN_PLUGIN_SHORT_NAME',   'EngagingNetworks' );
if ( ! defined( 'P4EN_PLUGIN_SLUG_NAME' ) ) define( 'P4EN_PLUGIN_SLUG_NAME',    'planet4-engagingnetworks' );
if ( ! defined( 'P4EN_INCLUDES_DIR' ) )     define( 'P4EN_INCLUDES_DIR',        P4EN_PLUGIN_DIR . '/includes' );
if ( ! defined( 'P4EN_ADMIN_DIR' ) )        define( 'P4EN_ADMIN_DIR',           plugins_url( P4EN_PLUGIN_DIRNAME . '/admin' ) );
if ( ! defined( 'P4EN_PUBLIC_DIR' ) )       define( 'P4EN_PUBLIC_DIR',          plugins_url( P4EN_PLUGIN_DIRNAME . '/public' ) );
if ( ! defined( 'P4EN_LANGUAGES' ) )        define( 'P4EN_LANGUAGES',           [ 'en_US' => 'English', 'el_GR' => 'Ελληνικά' ] );
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )   define( 'WP_UNINSTALL_PLUGIN',      P4EN_PLUGIN_BASENAME );


/* ========================
	  L O A D  F I L E S
   ======================== */

/**
 * Auto-loads files whose classes have Controller, View, Model in their names.
 * Class names need to be prefixed with P4EN and should use capitalized words
 * separated by underscores. Any acronyms should be all upper case.
 * https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#naming-conventions
 */
spl_autoload_register(
	function ( $class_name ) {

		if ( strpos( $class_name, 'P4EN' ) !== false ) {
			$file_name = '/class-' . str_replace( '_', '-', strtolower( $class_name ) );

			if ( strpos( $class_name, 'Controller' ) !== false ) {
				require_once 'classes/controller/' . $file_name . '.php';
			} elseif ( strpos( $class_name, 'View' ) !== false ) {
				require_once 'classes/view/' . $file_name . '.php';
			} elseif ( strpos( $class_name, 'Model' ) !== false ) {
				require_once 'classes/model/' . $file_name . '.php';
			} else {
				require_once 'classes/' . $file_name . '.php';
			}
		}
	}
);


/* =================================
      I N I T I A L I Z A T I O N
   ================================= */

/**
 * We make use of MVC architecture and the Singleton creational pattern.
 */

// $model= P4EN_Model::get_instance();
$controller = P4EN_Base_Controller::get_instance();
$view = P4EN_View::get_instance();
$init_controller = P4EN_Init_Controller::get_instance();
$init_controller->init( $controller, $view );
