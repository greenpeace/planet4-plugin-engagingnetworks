<?php
/**
 * Plugin Name: Planet4 - EngagingNetworks
 * Description: Connects Planet4 with the Engaging Networks platform.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-engagingnetworks
 * Version: 2.16
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-engagingnetworks
 *
 * License:     GPLv3
 * Copyright (C) 2017 Greenpeace International
 *
 * @package P4EN
 */

/**
 * Followed WordPress plugins best practices from https://developer.wordpress.org/plugins/the-basics/best-practices/
 * Followed WordPress-Core coding standards https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/
 * Followed WordPress-VIP coding standards https://vip.wordpress.com/documentation/code-review-what-we-look-for/
 * Added namespacing and PSR-4 auto-loading.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die( 'Direct access is forbidden !' );

if ( ! defined( 'P4EN_REQUIRED_PHP' ) ) {
	define( 'P4EN_REQUIRED_PHP', '7.0' );
}
if ( ! defined( 'P4EN_REQUIRED_PLUGINS' ) ) {
	define(
		'P4EN_REQUIRED_PLUGINS',
		[
			'timber' => [
				'min_version' => '1.3.0',
				'rel_path'    => 'timber-library/timber.php',
			],
		]
	);
}
if ( ! defined( 'P4EN_PLUGIN_BASENAME' ) ) {
	define( 'P4EN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'P4EN_PLUGIN_DIRNAME' ) ) {
	define( 'P4EN_PLUGIN_DIRNAME', dirname( P4EN_PLUGIN_BASENAME ) );
}
if ( ! defined( 'P4EN_PLUGIN_DIR' ) ) {
	define( 'P4EN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . P4EN_PLUGIN_DIRNAME );
}
if ( ! defined( 'P4EN_PLUGIN_NAME' ) ) {
	define( 'P4EN_PLUGIN_NAME', 'Planet4 - EngagingNetworks' );
}
if ( ! defined( 'P4EN_PLUGIN_SHORT_NAME' ) ) {
	define( 'P4EN_PLUGIN_SHORT_NAME', 'EngagingNetworks' );
}
if ( ! defined( 'P4EN_PLUGIN_SLUG_NAME' ) ) {
	define( 'P4EN_PLUGIN_SLUG_NAME', 'engagingnetworks' );
}
if ( ! defined( 'P4EN_INCLUDES_DIR' ) ) {
	define( 'P4EN_INCLUDES_DIR', P4EN_PLUGIN_DIR . '/includes/' );
}
if ( ! defined( 'P4EN_ADMIN_DIR' ) ) {
	define( 'P4EN_ADMIN_DIR', plugins_url( P4EN_PLUGIN_DIRNAME . '/admin/' ) );
}
if ( ! defined( 'P4EN_PUBLIC_DIR' ) ) {
	define( 'P4EN_PUBLIC_DIR', plugins_url( P4EN_PLUGIN_DIRNAME . '/public/' ) );
}
if ( ! defined( 'P4EN_LANGUAGES' ) ) {
	define(
		'P4EN_LANGUAGES',
		[
			'en_US' => 'English',
			'el_GR' => 'Ελληνικά',
		]
	);
}
if ( ! defined( 'P4EN_ALLOWED_PAGETYPE' ) ) {
	define(
		'P4EN_ALLOWED_PAGETYPE',
		[
			'page',
			'campaign',
		]
	);
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', P4EN_PLUGIN_BASENAME );
}
if ( ! defined( 'P4_REST_SLUG' ) ) {
	define( 'P4_REST_SLUG', 'planet4-engaging-networks' );
}

add_filter( 'timber/twig', 'add_to_twig' );

/**
 * Adds functionality to Twig.
 *
 * @param \Twig\Environment $twig The Twig environment.
 * @return \Twig\Environment
 */
function add_to_twig( $twig ) {
	// Adding functions as filters.
	$twig->addFilter(
		new Twig_SimpleFilter(
			'object_to_array',
			function ( $std_class_object ) {
				$response = [];
				foreach ( $std_class_object as $key => $value ) {
					$response[ $key ] = $value;
				}
				return $response;
			}
		)
	);

	return $twig;
}

require_once __DIR__ . '/classes/class-loader.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Load plugin
 */
P4EN\Loader::get_instance(
	[
		'P4EN\Controllers\Menu\Enform_Post_Controller',
		'P4EN\Controllers\Menu\Settings_Controller',
		'P4EN\Controllers\Blocks\ENBlock_Controller',
		'P4EN\Controllers\Api\Rest_Controller',
	],
	'P4EN\Views\View'
);
