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
		// We rely on dbDelta for schema updates to avoid direct DB query warnings.
		// If dbDelta fails, we might need to revisit, but for now we trust the standard WP mechanism.
		
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table insert.
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

		return $result;
	}

	/**
	 * Check availability for a given range.
	 */
	public static function check_availability( $check_in, $check_out ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;

		// 1. Check if the range falls within an admin-defined 'available' slot.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- Table name cannot be prepared.
		$is_within_range = $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name 
				WHERE start_date <= %s 
				AND end_date >= %s 
				AND status = 'available'",
				$check_in,
				$check_out
			)
		) > 0;

		if ( ! $is_within_range ) {
			return false;
		}

		// 2. Check if the range overlaps with any existing 'booked' slot.
		// Overlap condition: (StartA <= EndB) and (EndA >= StartB)
		// Statuses that block availability: 'booked', 'pending', 'confirmed', 'completed'
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- Table name cannot be prepared.
		$is_booked = $wpdb->get_var( 
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name 
				WHERE status IN ('booked', 'pending', 'confirmed', 'completed') 
				AND start_date < %s 
				AND end_date > %s",
				$check_out, 
				$check_in
			)
		) > 0;

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
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- Table name cannot be prepared.
		return $wpdb->get_results( "SELECT start_date, end_date, status FROM $table_name ORDER BY start_date ASC" );
	}

	/**
	 * Delete a range.
	 */
	public static function delete_range( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GRT_BOOKING_DB_TABLE;
		// Use wpdb->delete which handles preparation internally.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table delete.
		return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
	}
}
