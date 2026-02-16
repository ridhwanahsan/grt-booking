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

		// 1. Check if the range falls within an admin-defined 'available' slot.
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
		$query_booked = $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name 
			WHERE status = 'booked' 
			AND start_date < %s 
			AND end_date > %s",
			$check_out, // EndB (Requested End) - using strict inequality for check-out logic usually, but here dates are inclusive days?
			// Usually hotel logic: Check-out date can be the Check-in date of next guest.
			// If dates are "nights":
			// Booking: Jan 1 to Jan 2 (1 night).
			// If another booking is Jan 2 to Jan 3.
			// Overlap check: 
			// Existing: [Jan 1, Jan 2]
			// New: [Jan 2, Jan 3]
			// They overlap on Jan 2.
			
			// Let's stick to standard SQL overlap for inclusive dates:
			// start_date <= check_out AND end_date >= check_in
			// However, for hotels, if check_out is the day you leave, it's usually available for next check-in.
			// So we usually compare: start_date < check_out AND end_date > check_in
			
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
