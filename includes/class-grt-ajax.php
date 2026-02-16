<?php
/**
 * AJAX Handler Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GRT_Booking_AJAX {

	/**
	 * Initialize hooks
	 */
	public function init() {
		add_action( 'wp_ajax_grt_check_availability', array( $this, 'process_booking' ) );
		add_action( 'wp_ajax_nopriv_grt_check_availability', array( $this, 'process_booking' ) );
	}

	/**
	 * Process Booking (formerly check_availability)
	 */
	public function process_booking() {
		check_ajax_referer( 'grt_booking_nonce', 'security' );

		// Validate inputs
		$check_in  = isset( $_POST['check_in'] ) ? sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) : '';
		$check_out = isset( $_POST['check_out'] ) ? sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) : '';
		$adults    = isset( $_POST['adults'] ) ? absint( wp_unslash( $_POST['adults'] ) ) : 1;
		$children  = isset( $_POST['children'] ) ? absint( wp_unslash( $_POST['children'] ) ) : 0;
		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone     = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( empty( $check_in ) || empty( $check_out ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select check-in and check-out dates.', 'grt-booking' ) ) );
		}

		// Validate dates
		$d1 = new DateTime( $check_in );
		$d2 = new DateTime( $check_out );

		if ( $d1 >= $d2 ) {
			wp_send_json_error( array( 'message' => __( 'Check-out date must be after check-in date.', 'grt-booking' ) ) );
		}

		// Check min/max stay
		$options = get_option( 'grt_booking_settings' );
		$min_stay = isset( $options['min_stay'] ) ? intval( $options['min_stay'] ) : 1;
		$max_stay = isset( $options['max_stay'] ) ? intval( $options['max_stay'] ) : 30;

		$interval = $d1->diff( $d2 );
		$nights = $interval->days;

		if ( $nights < $min_stay ) {
			/* translators: %d: Minimum number of nights */
			wp_send_json_error( array( 'message' => sprintf( __( 'Minimum stay is %d nights.', 'grt-booking' ), $min_stay ) ) );
		}

		if ( $nights > $max_stay ) {
			/* translators: %d: Maximum number of nights */
			wp_send_json_error( array( 'message' => sprintf( __( 'Maximum stay is %d nights.', 'grt-booking' ), $max_stay ) ) );
		}

		// Check database availability
		$is_available = GRT_Booking_DB::check_availability( $check_in, $check_out );

		if ( $is_available ) {
			// Perform Booking
			$inserted = GRT_Booking_DB::insert_availability( $check_in, $check_out, 'pending', $email, $phone, $adults, $children );

			if ( $inserted ) {
				$msg = isset( $options['msg_booked'] ) && ! empty( $options['msg_booked'] ) 
					? $options['msg_booked'] 
					: __( 'The room has been booked. The admin will contact you shortly.', 'grt-booking' );
				
				wp_send_json_success( array( 'message' => $msg ) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to process booking. Please try again.', 'grt-booking' ) ) );
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Sorry, the room is not available for the selected dates.', 'grt-booking' ) ) );
		}
	}
}
