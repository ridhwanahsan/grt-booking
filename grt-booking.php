<?php
/**
 * Plugin Name: GRT Booking
 * Plugin URI:  https://example.com/grt-booking
 * Description: A comprehensive room reservation system.
 * Version:     1.0.0
 * Author:      ridhwanahsann
 * Author URI:  https://example.com
 * License:     GPL-2.0+
 * Text Domain: grt-booking
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Constants
define( 'GRT_BOOKING_VERSION', '1.0.0' );
define( 'GRT_BOOKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRT_BOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GRT_BOOKING_DB_TABLE', 'grt_booking_availability' );

// Include core files
require_once GRT_BOOKING_PLUGIN_DIR . 'includes/class-grt-db.php';
require_once GRT_BOOKING_PLUGIN_DIR . 'includes/class-grt-admin.php';
require_once GRT_BOOKING_PLUGIN_DIR . 'includes/class-grt-shortcode.php';
require_once GRT_BOOKING_PLUGIN_DIR . 'includes/class-grt-ajax.php';

// Activation Hook
register_activation_hook( __FILE__, array( 'GRT_Booking_DB', 'install' ) );

// Initialize Classes
function run_grt_booking() {
	// Admin
	if ( is_admin() ) {
		$plugin_admin = new GRT_Booking_Admin();
		$plugin_admin->init();
	}

	// Shortcode
	$plugin_shortcode = new GRT_Booking_Shortcode();
	$plugin_shortcode->init();

	// AJAX
	$plugin_ajax = new GRT_Booking_AJAX();
	$plugin_ajax->init();
}
add_action( 'plugins_loaded', 'run_grt_booking' );
