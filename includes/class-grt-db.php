<?php
/**
 * Database Handler Class
 */

class GRT_Booking_DB {

	/**
	 * Create the database table on activation.
	 */
	public static function install() {
		global $wpdb;

		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			start_date date NOT NULL,
			end_date date NOT NULL,
			status varchar(20) DEFAULT 'available' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY start_date (start_date),
			KEY end_date (end_date)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Add option for DB version if needed later
		add_option( 'grt_booking_db_version', '1.0.0' );
	}

	/**
	 * Insert availability range.
	 */
	public static function insert_availability( $start_date, $end_date, $status = 'available' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;

		return $wpdb->insert(
			$table_name,
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'status'     => $status,
			),
			array( '%s', '%s', '%s' )
		);
	}

	/**
	 * Check availability for a given range.
	 */
	public static function check_availability( $check_in, $check_out ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;

		// Logic:
		// 1. Must be within an 'available' range provided by admin.
		// 2. (If we had bookings) Must not overlap with 'booked' ranges.
		
		// For this implementation, we assume the table stores "Available" slots.
		// We check if there is ANY row where start_date <= check_in AND end_date >= check_out
		// AND status = 'available'.
		
		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name 
			WHERE start_date <= %s 
			AND end_date >= %s 
			AND status = 'available'",
			$check_in,
			$check_out
		);

		$count = $wpdb->get_var( $query );

		return $count > 0;
	}

	/**
	 * Get all availability ranges.
	 */
	public static function get_all_ranges() {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		return $wpdb->get_results( "SELECT * FROM $table_name ORDER BY start_date ASC" );
	}

	/**
	 * Delete a range.
	 */
	public static function delete_range( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
	}
}
