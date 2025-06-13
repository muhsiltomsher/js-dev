<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://shapedplugin.com/
 * @package           WP_Tabs
 *
 * Plugin Name:       WP Tabs
 * Plugin URI:        https://wptabs.com/?ref=1
 * Description:       WP Tabs is the most user-friendly, highly customizable, responsive WordPress tabs plugin to display your content in a clean organized tabbed navigation.
 * Version:           2.2.13
 * Author:            ShapedPlugin LLC
 * Author URI:        https://shapedplugin.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-expand-tabs-free
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_TABS_NAME', 'WP Tabs' );
define( 'WP_TABS_VERSION', '2.2.13' );
define( 'WP_TABS_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_TABS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_TABS_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_TABS_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'WP_TABS_SLUG', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Pro version check.
 *
 * @return boolean
 */
function is_wp_tabs_pro() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! ( is_plugin_active( 'wp-tabs-pro/wp-tabs-pro.php' ) || is_plugin_active_for_network( 'wp-tabs-pro/wp-tabs-pro.php' ) ) ) {
		return true;
	}
}

require plugin_dir_path( __FILE__ ) . 'includes/class-wp-tabs.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
function wp_tabs_pro_initialize_files() {
	if ( ! function_exists( 'is_wp_tabs_pro' ) || ! is_wp_tabs_pro() ) {
		return;
	}

	// Require all necessary files.
	require_once WP_TABS_PATH . '/admin/partials/models/classes/setup.class.php';
	require_once WP_TABS_PATH . '/admin/partials/metabox-config.php';
	require_once WP_TABS_PATH . '/admin/partials/option-config.php';
	require_once WP_TABS_PATH . '/admin/partials/tools-config.php';
}
add_action( 'after_setup_theme', 'wp_tabs_pro_initialize_files' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_wp_tabs() {

	$plugin = new SP_WP_Tabs_Free();
	$plugin->run();
}
if ( is_wp_tabs_pro() ) {
	run_wp_tabs();
}
