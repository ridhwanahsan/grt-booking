<?php
/**
 * Shortcode Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GRT_Booking_Shortcode {

	/**
	 * Initialize hooks
	 */
	public function init() {
		add_shortcode( 'grt_booking_form', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register assets
	 */
	public function register_assets() {
		// Only enqueue on front-end
		if ( is_admin() ) {
			return;
		}

		wp_register_style( 'grt-booking-css', GRT_BOOKING_PLUGIN_URL . 'assets/css/grt-booking.css', array(), GRT_BOOKING_VERSION );
		// Register jQuery UI style
		wp_register_style( 'jquery-ui-style', GRT_BOOKING_PLUGIN_URL . 'assets/css/jquery-ui.css', array(), '1.13.2' );
		
		wp_register_script( 'grt-booking-js', GRT_BOOKING_PLUGIN_URL . 'assets/js/grt-booking.js', array( 'jquery', 'jquery-ui-datepicker' ), GRT_BOOKING_VERSION, true );

		// Get availability ranges
		$ranges = GRT_Booking_DB::get_all_ranges();
		
		// Localize script
		wp_localize_script( 'grt-booking-js', 'grt_booking_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'grt_booking_nonce' ),
			'available_ranges' => $ranges,
			'messages' => array(
				'empty_fields' => __( 'Please fill in all required fields.', 'grt-booking' ),
				'invalid_dates' => __( 'Invalid date selection.', 'grt-booking' ),
			)
		));
	}

	/**
	 * Render shortcode
	 */
	public function render_shortcode( $atts ) {
		// Enqueue assets
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'grt-booking-css' );
		wp_enqueue_script( 'grt-booking-js' );

		// Get options
		$options = get_option( 'grt_booking_settings' );
		$submit_text = isset( $options['submit_text'] ) ? $options['submit_text'] : 'CHECK AVAILABILITY';
		
		ob_start();
		include GRT_BOOKING_PLUGIN_DIR . 'templates/booking-form.php';
		return ob_get_clean();
	}
}
