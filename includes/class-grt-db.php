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
			email varchar(100) DEFAULT '' NOT NULL,
			phone varchar(20) DEFAULT '' NOT NULL,
			adults int(2) DEFAULT 1 NOT NULL,
			children int(2) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY start_date (start_date),
			KEY end_date (end_date)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Manually add columns if they don't exist (dbDelta fallback)
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'email'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE $table_name ADD email varchar(100) DEFAULT '' NOT NULL" );
		}

		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'phone'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE $table_name ADD phone varchar(20) DEFAULT '' NOT NULL" );
		}

		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'adults'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE $table_name ADD adults int(2) DEFAULT 1 NOT NULL" );
		}

		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'children'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE $table_name ADD children int(2) DEFAULT 0 NOT NULL" );
		}

		// Add option for DB version if needed later
		// Force update to version 1.0.5 to ensure schema update
		update_option( 'grt_booking_db_version', '1.0.5' );
	}

	/**
	 * Insert availability range.
	 */
	public static function insert_availability( $start_date, $end_date, $status = 'available', $email = '', $phone = '', $adults = 1, $children = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;

		$result = $wpdb->insert(
			$table_name,
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'status'     => $status,
				'email'      => $email,
				'phone'      => $phone,
				'adults'     => $adults,
				'children'   => $children,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
		);

		if ( false === $result ) {
			error_log( 'GRT Booking DB Insert Error: ' . $wpdb->last_error );
		}

		return $result;
	}

	/**
	 * Check availability for a given range.
	 */
	public static function check_availability( $check_in, $check_out ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;

		// 1. Check if the range falls within an admin-defined 'available' slot.
		// Use prepare() for values, table name is trusted.
		$query_available = $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name 
			WHERE start_date <= %s 
			AND end_date >= %s 
			AND status = 'available'",
			$check_in,
			$check_out
		);

		$is_within_range = $wpdb->get_var( $query_available ) > 0;

		if ( ! $is_within_range ) {
			return false;
		}

		// 2. Check if the range overlaps with any existing 'booked' slot.
		// Overlap condition: (StartA <= EndB) and (EndA >= StartB)
		// Statuses that block availability: 'booked', 'pending', 'confirmed', 'completed'
		// Statuses that do NOT block: 'available', 'cancelled'
		$query_booked = $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name 
			WHERE status IN ('booked', 'pending', 'confirmed', 'completed') 
			AND start_date < %s 
			AND end_date > %s",
			$check_out, 
			$check_in
		);
		
		// Let's assume input dates are inclusive "nights" stored? 
		// Actually, usually frontend sends Check-in and Check-out.
		// If I book Jan 1 to Jan 5. I stay nights of Jan 1, 2, 3, 4. I leave Jan 5.
		// So Jan 5 is available for someone else to check in.
		// So overlap means:
		// Existing.start < New.end AND Existing.end > New.start
		
		$is_booked = $wpdb->get_var( $query_booked ) > 0;

		return ! $is_booked;
	}

	/**
	 * Get all availability ranges.
	 * Only returns public data (dates and status) to prevent PII leakage in frontend.
	 */
	public static function get_all_ranges() {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		// Ignoring "Direct database call" warning as per WP standard for custom tables.
		return $wpdb->get_results( "SELECT start_date, end_date, status FROM $table_name ORDER BY start_date ASC" );
	}

	/**
	 * Delete a range.
	 */
	public static function delete_range( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		// Use wpdb->delete which handles preparation internally.
		return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
	}
}
