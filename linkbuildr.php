<?php
/**
 * Plugin Name: Linkbuildr
 * Plugin URI: https://ftf.agency/tools/linkbuildr/
 * Description: Automated content promotion. Share your content with the people who care the most, automatically.
 * Version: 1.3
 * Requires at least: 5.2
 * Requires PHP:      5.3
 * Author: ftf.agency
 * Author URI: https://ftf.agency/
 * License: GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: linkbuildr
 * Domain Path: /languages
 *
 * @package Linkbuildr
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'LINKBUILDR_NAME', 'Linkbuildr' );
define( 'LINKBUILDR_REQUIRED_PHP_VERSION', '5.3' );
define( 'LINKBUILDR_REQUIRED_WP_VERSION', '3.1' );

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function linkbuildr_requirements_met() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, LINKBUILDR_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, LINKBUILDR_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function linkbuildr_requirements_error() {
	global $wp_version;

	require_once dirname( __FILE__ ) . '/views/requirements-error.php';
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( linkbuildr_requirements_met() ) {

	require_once __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php';
	require_once __DIR__ . '/classes/class-module-abstract.php';
	require_once __DIR__ . '/classes/class-linkbuildr.php';
	require_once __DIR__ . '/classes/class-linkbuildr-settings.php';
	require_once __DIR__ . '/classes/class-linkbuildr-migration.php';
	require_once __DIR__ . '/classes/class-linkbuildr-events.php';
	require_once __DIR__ . '/classes/class-linkbuildr-api.php';

	if ( class_exists( 'Linkbuildr' ) ) {
		$GLOBALS['linkbuildr'] = Linkbuildr::get_instance();
		register_activation_hook( __FILE__, array( $GLOBALS['linkbuildr'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['linkbuildr'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'linkbuildr_requirements_error' );
}
