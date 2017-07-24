<?php
/**
 * Plugin Name: Planet4 - EngagingNetworks
 * Description: Connects Planet4 with the Engaging Networks platform.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-engagingnetworks
 * Version: 0.0.1
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-engagingnetworks
 *
 * License:     GPLv3
 * Copyright (C) 2017 Greenpeace International
 */

// Exit if accessed directly.
defined('ABSPATH') OR die('Direct access is forbidden !');

/* ========================
	  C O N S T A N T S
   ======================== */

if (!defined('P4EN_PLUGIN_DIRNAME'))    define('P4EN_PLUGIN_DIRNAME',   dirname(plugin_basename(__FILE__)));
if (!defined('P4EN_PLUGIN_NAME'))       define('P4EN_PLUGIN_NAME',      'Planet4 - EngagingNetworks');
if (!defined('P4EN_PLUGIN_SHORT_NAME')) define('P4EN_PLUGIN_SHORT_NAME','EngagingNetworks');
if (!defined('P4EN_PLUGIN_TEXTDOMAIN')) define('P4EN_PLUGIN_TEXTDOMAIN','planet4-engagingnetworks');


/* ========================
	  L O A D  F I L E S
   ======================== */



/* =============================
      R E Q U I R E M E N T S
   ============================= */

$P4EN_minimum_php_version = '7.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 *
 * @return boolean true if version check passed. If false, displays an error.
 */
function P4EN_php_version_check() {
	global $P4EN_minimum_php_version;

	if (version_compare(phpversion(), $P4EN_minimum_php_version) < 0)
		return false;
	return true;
}

/* =================================
      I N I T I A L I Z A T I O N
   ================================= */

/**
 * Initialize this plugin.
 *
 * @return void
 */
function P4EN_init() {
	add_action( 'admin_menu', 'P4EN_admin_menu_load');
}

/**
 * Load the menu & submenus for the plugin
 */
function P4EN_admin_menu_load() {
	$current_user = wp_get_current_user();

	if(in_array("administrator", $current_user->roles) || in_array("editor", $current_user->roles)) {

		add_menu_page(
			P4EN_PLUGIN_SHORT_NAME,
			P4EN_PLUGIN_SHORT_NAME,
			'edit_dashboard',
			'engaging-networks',
			'dashboard_page',
			'none'
		);

		add_submenu_page(
			'engaging-networks',
			esc_html__( 'Settings', P4EN_PLUGIN_TEXTDOMAIN ),
			esc_html__( 'Settings', P4EN_PLUGIN_TEXTDOMAIN ),
			'manage_options',
			'engaging-networks-settings',
			'settings_page'
		);
	}
}

/**
 * View the main dashboard page of the plugin.
 */
function dashboard_page() {
	echo '<p>Dashboard Page</p>';
}

/**
 * View the settings page of the plugin.
 */
function settings_page() {
	echo '<p>Settings Page</p>';
}

/**
 * Initialize internationalization (i18n) for this plugin.
 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function P4EN_init_i18n() {
	load_plugin_textdomain(P4EN_PLUGIN_TEXTDOMAIN, false, P4EN_PLUGIN_DIRNAME . '/languages/');
}

// If we are on the admin panel
if ( is_admin() ) {
	// Run the version check.
	// If it is successful, continue with initialization for this plugin
	if ( P4EN_php_version_check() ) {
		add_action('init','P4EN_init');
		add_action('plugins_loaded','P4EN_init_i18n');    // Initialize internationalization

	} else {
		wp_die('<div class="updated fade">' .
		        __( '<u>Error!</u><br/><br/>Plugin <strong>'.P4EN_PLUGIN_NAME.'</strong> requires a newer version of PHP to be running.', P4EN_PLUGIN_TEXTDOMAIN ) .
		        '<br/>' . __( 'Minimal version of PHP required: ', P4EN_PLUGIN_TEXTDOMAIN ) . '<strong>' . $P4EN_minimum_php_version . '</strong>' .
		        '<br/>' . __( 'Your server\'s PHP version: ', P4EN_PLUGIN_TEXTDOMAIN ) . '<strong>' . phpversion() . '</strong>' .
		        '</div>', 'Plugin Activation Error', array( 'response'=>200, 'back_link'=>TRUE ) );
	}
}