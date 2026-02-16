<?php
/**
 * Uninstall Plugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$grt_booking_table_name = $wpdb->prefix . 'grt_booking_availability';

// Drop the table
$wpdb->query( "DROP TABLE IF EXISTS $grt_booking_table_name" );

// Delete options
delete_option( 'grt_booking_db_version' );
delete_option( 'grt_booking_settings' );
