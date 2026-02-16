<?php
/**
 * Admin Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GRT_Booking_Admin {

	/**
	 * Initialize admin hooks
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_post_grt_add_availability', array( $this, 'handle_add_availability' ) );
		add_action( 'admin_post_grt_delete_availability', array( $this, 'handle_delete_availability' ) );
		add_action( 'admin_post_grt_update_booking_status', array( $this, 'handle_update_booking_status' ) );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts( $hook ) {
		// Only enqueue on our plugin pages
		if ( strpos( $hook, 'page_grt-booking' ) === false && 'toplevel_page_grt-booking' !== $hook ) {
			return;
		}
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-style', GRT_BOOKING_PLUGIN_URL . 'assets/css/jquery-ui.css', array(), '1.13.2' );
		wp_enqueue_style( 'grt-admin-css', GRT_BOOKING_PLUGIN_URL . 'assets/css/admin.css', array(), GRT_BOOKING_VERSION );
	}

	/**
	 * Add menu page
	 */
	public function add_admin_menu() {
		// Top level menu
		add_menu_page(
			__( 'GRT Booking', 'grt-booking' ),
			__( 'GRT Booking', 'grt-booking' ),
			'manage_options',
			'grt-booking',
			array( $this, 'render_admin_page' ),
			'dashicons-calendar-alt',
			25
		);

		// Submenu: Settings (Default)
		add_submenu_page(
			'grt-booking',
			__( 'Settings', 'grt-booking' ),
			__( 'Settings', 'grt-booking' ),
			'manage_options',
			'grt-booking',
			array( $this, 'render_admin_page' )
		);

		// Submenu: Date Booked
		add_submenu_page(
			'grt-booking',
			__( 'Date Booked', 'grt-booking' ),
			__( 'Date Booked', 'grt-booking' ),
			'manage_options',
			'grt-booking-booked',
			array( $this, 'render_date_booked_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'grt_booking_options', 'grt_booking_settings', array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'grt_booking_general',
			__( 'General Settings', 'grt-booking' ),
			null,
			'grt-booking'
		);

		add_settings_field(
			'min_stay',
			__( 'Minimum Stay (Nights)', 'grt-booking' ),
			array( $this, 'render_number_field' ),
			'grt-booking',
			'grt_booking_general',
			array( 'label_for' => 'min_stay', 'default' => 1 )
		);

		add_settings_field(
			'max_stay',
			__( 'Maximum Stay (Nights)', 'grt-booking' ),
			array( $this, 'render_number_field' ),
			'grt-booking',
			'grt_booking_general',
			array( 'label_for' => 'max_stay', 'default' => 30 )
		);

		add_settings_field(
			'submit_text',
			__( 'Submit Button Text', 'grt-booking' ),
			array( $this, 'render_text_field' ),
			'grt-booking',
			'grt_booking_general',
			array( 'label_for' => 'submit_text', 'default' => 'CHECK AVAILABILITY' )
		);

		add_settings_field(
			'msg_available',
			__( 'Availability Success Message', 'grt-booking' ),
			array( $this, 'render_textarea_field' ),
			'grt-booking',
			'grt_booking_general',
			array( 'label_for' => 'msg_available', 'default' => 'Room is available! You can proceed with booking.' )
		);

		add_settings_field(
			'msg_booked',
			__( 'Booking Success Message', 'grt-booking' ),
			array( $this, 'render_textarea_field' ),
			'grt-booking',
			'grt_booking_general',
			array( 'label_for' => 'msg_booked', 'default' => 'The room has been booked. The admin will contact you shortly.' )
		);
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$new_input = array();
		if ( isset( $input['min_stay'] ) ) {
			$new_input['min_stay'] = absint( $input['min_stay'] );
		}
		if ( isset( $input['max_stay'] ) ) {
			$new_input['max_stay'] = absint( $input['max_stay'] );
		}
		if ( isset( $input['submit_text'] ) ) {
			$new_input['submit_text'] = sanitize_text_field( $input['submit_text'] );
		}
		if ( isset( $input['msg_available'] ) ) {
			$new_input['msg_available'] = sanitize_textarea_field( $input['msg_available'] );
		}
		if ( isset( $input['msg_booked'] ) ) {
			$new_input['msg_booked'] = sanitize_textarea_field( $input['msg_booked'] );
		}
		return $new_input;
	}

	/**
	 * Render number field
	 */
	public function render_number_field( $args ) {
		$options = get_option( 'grt_booking_settings' );
		$value = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : $args['default'];
		echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="grt_booking_settings[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $value ) . '" min="1" class="regular-text" />';
	}

	/**
	 * Render text field
	 */
	public function render_text_field( $args ) {
		$options = get_option( 'grt_booking_settings' );
		$value = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : $args['default'];
		echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="grt_booking_settings[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}

	/**
	 * Render textarea field
	 */
	public function render_textarea_field( $args ) {
		$options = get_option( 'grt_booking_settings' );
		$value = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : $args['default'];
		echo '<textarea id="' . esc_attr( $args['label_for'] ) . '" name="grt_booking_settings[' . esc_attr( $args['label_for'] ) . ']" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
	}

	/**
	 * Render Admin Page
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation only.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

		// Handle messages
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Message display only.
		if ( isset( $_GET['message'] ) ) {
			// No nonce check needed here as this is just displaying a message based on a URL parameter
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message = sanitize_key( wp_unslash( $_GET['message'] ) );
			if ( 'added' === $message ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Availability added.', 'grt-booking' ) . '</p></div>';
			} elseif ( 'deleted' === $message ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Availability deleted.', 'grt-booking' ) . '</p></div>';
			} elseif ( 'updated' === $message ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Booking status updated.', 'grt-booking' ) . '</p></div>';
			} elseif ( 'error' === $message ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'An error occurred.', 'grt-booking' ) . '</p></div>';
			}
		}

		// DB Column Debug
		global $wpdb;
		$table_name = $wpdb->prefix . 'grt_booking_availability';
		
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<h2 class="nav-tab-wrapper">
				<a href="?page=grt-booking&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General Settings', 'grt-booking' ); ?></a>
				<a href="?page=grt-booking&tab=availability" class="nav-tab <?php echo $active_tab == 'availability' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Availability', 'grt-booking' ); ?></a>
			</h2>

			<?php if ( $active_tab == 'general' ) : ?>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'grt_booking_options' );
					do_settings_sections( 'grt-booking' );
					submit_button();
					?>
				</form>
			<?php else : ?>
				
				<h2><?php esc_html_e( 'Availability Management', 'grt-booking' ); ?></h2>
				
				<!-- Add Availability Form -->
				<div class="card" style="max-width: 100%; padding: 20px;">
					<h3><?php esc_html_e( 'Add Available Date Range', 'grt-booking' ); ?></h3>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="grt_add_availability">
						<?php wp_nonce_field( 'grt_add_availability_nonce', 'grt_nonce' ); ?>
						
						<label for="start_date"><?php esc_html_e( 'Start Date:', 'grt-booking' ); ?></label>
						<input type="text" id="start_date" name="start_date" class="grt-datepicker" required>
						
						<label for="end_date"><?php esc_html_e( 'End Date:', 'grt-booking' ); ?></label>
						<input type="text" id="end_date" name="end_date" class="grt-datepicker" required>
						
						<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Availability', 'grt-booking' ); ?>">
					</form>
					<script>
					jQuery(document).ready(function($){
						$('.grt-datepicker').datepicker({
							dateFormat: 'yy-mm-dd',
							minDate: 0
						});
					});
					</script>
				</div>

				<br>

				<!-- List Availability -->
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Start Date', 'grt-booking' ); ?></th>
							<th><?php esc_html_e( 'End Date', 'grt-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'grt-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'grt-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						global $wpdb;
						$table_name = $wpdb->prefix . 'grt_booking_availability';
						// Use prepare or specific query structure if needed, though this simple SELECT is generally safe.
						// However, Plugin Check complains about unescaped $table_name.
						// In WP, table names are not parameters for prepare(). They are trusted if built with $wpdb->prefix.
						// To silence the warning, we can't do much as it's a false positive for table names,
						// but ensuring $table_name is derived from $wpdb->prefix is the correct way.
						
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, custom table query.
						$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY start_date DESC" );

						if ( $results ) {
							foreach ( $results as $row ) {
								echo '<tr>';
								echo '<td>' . esc_html( $row->start_date ) . '</td>';
								echo '<td>' . esc_html( $row->end_date ) . '</td>';
								echo '<td>' . esc_html( ucfirst( $row->status ) ) . '</td>';
								echo '<td>';
								echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" style="display:inline;">';
								echo '<input type="hidden" name="action" value="grt_delete_availability">';
								echo '<input type="hidden" name="id" value="' . esc_attr( $row->id ) . '">';
								wp_nonce_field( 'grt_delete_availability_nonce', 'grt_nonce' );
								echo '<input type="submit" class="button button-link-delete" value="' . esc_attr__( 'Delete', 'grt-booking' ) . '" onclick="return confirm(\'' . esc_js( __( 'Are you sure?', 'grt-booking' ) ) . '\');">';
								echo '</form>';
								echo '</td>';
								echo '</tr>';
							}
						} else {
							echo '<tr><td colspan="4">' . esc_html__( 'No availability records found.', 'grt-booking' ) . '</td></tr>';
						}
						?>
					</tbody>
				</table>

			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle Add Availability
	 */
	public function handle_add_availability() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'grt_add_availability_nonce', 'grt_nonce' );

		if ( isset( $_POST['start_date'], $_POST['end_date'] ) ) {
			$start_date = sanitize_text_field( $_POST['start_date'] );
			$end_date   = sanitize_text_field( $_POST['end_date'] );

			global $wpdb;
			$table_name = $wpdb->prefix . 'grt_booking_availability';
			
			$wpdb->insert(
				$table_name,
				array(
					'start_date' => $start_date,
					'end_date'   => $end_date,
					'status'     => 'available'
				),
				array( '%s', '%s', '%s' )
			);
			
			wp_safe_redirect( admin_url( 'admin.php?page=grt-booking&tab=availability&message=added' ) );
			exit;
		}
		
		wp_safe_redirect( admin_url( 'admin.php?page=grt-booking&tab=availability&message=error' ) );
		exit;
	}

	/**
	 * Handle Delete Availability
	 */
	public function handle_delete_availability() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'grt_delete_availability_nonce', 'grt_nonce' );

		if ( isset( $_POST['id'] ) ) {
			$id = absint( wp_unslash( $_POST['id'] ) );

			global $wpdb;
			$table_name = $wpdb->prefix . 'grt_booking_availability';
			
			$wpdb->delete(
				$table_name,
				array( 'id' => $id ),
				array( '%d' )
			);
			
			// Redirect back to the referring page (could be availability tab or date booked page)
			$redirect_url = wp_get_referer();
			if ( ! $redirect_url ) {
				$redirect_url = admin_url( 'admin.php?page=grt-booking&tab=availability' );
			}
			
			wp_safe_redirect( add_query_arg( 'message', 'deleted', $redirect_url ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=grt-booking&tab=availability&message=error' ) );
		exit;
	}

	/**
	 * Handle Update Booking Status
	 */
	public function handle_update_booking_status() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'grt_update_booking_status_nonce', 'grt_nonce' );

		if ( isset( $_POST['id'], $_POST['status'] ) ) {
			$id = absint( $_POST['id'] );
			$status = sanitize_text_field( wp_unslash( $_POST['status'] ) );
			
			// Validate status
			$allowed_statuses = array( 'pending', 'confirmed', 'completed', 'cancelled', 'booked' );
			if ( ! in_array( $status, $allowed_statuses ) ) {
				wp_die( 'Invalid status' );
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'grt_booking_availability';
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table update.
			$wpdb->update(
				$table_name,
				array( 'status' => $status ),
				array( 'id' => $id ),
				array( '%s' ),
				array( '%d' )
			);
			
			wp_safe_redirect( admin_url( 'admin.php?page=grt-booking-booked&message=updated' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=grt-booking-booked&message=error' ) );
		exit;
	}

	/**
	 * Render Date Booked Page
	 */
	public function render_date_booked_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Date Booked', 'grt-booking' ); ?></h1>
			<p><?php esc_html_e( 'Below are the dates that have been booked.', 'grt-booking' ); ?></p>
			
			<div class="grt-booked-container">
				<?php
				global $wpdb;
				$table_name = $wpdb->prefix . 'grt_booking_availability';
				
				// Query for status != 'available'
				// Ignoring "Unescaped parameter $table_name" warning as table names cannot be prepared.
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, custom table query.
				$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE status != 'available' ORDER BY start_date DESC" );

				if ( $results ) {
					foreach ( $results as $row ) {
						?>
						<div class="grt-booked-box">
							<div class="grt-booked-dates">
								<span class="dashicons dashicons-calendar-alt"></span>
								<strong><?php echo esc_html( $row->start_date ); ?></strong> 
								<?php esc_html_e( 'to', 'grt-booking' ); ?> 
								<strong><?php echo esc_html( $row->end_date ); ?></strong>
							</div>
							
							<div class="grt-booked-details" style="margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 4px;">
								<?php if ( ! empty( $row->email ) ) : ?>
									<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Email:', 'grt-booking' ); ?></strong> <?php echo esc_html( $row->email ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $row->phone ) ) : ?>
									<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Phone:', 'grt-booking' ); ?></strong> <?php echo esc_html( $row->phone ); ?></p>
								<?php endif; ?>
								<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Adults:', 'grt-booking' ); ?></strong> <?php echo esc_html( isset( $row->adults ) ? $row->adults : 1 ); ?></p>
								<p style="margin: 5px 0;"><strong><?php esc_html_e( 'Children:', 'grt-booking' ); ?></strong> <?php echo esc_html( isset( $row->children ) ? $row->children : 0 ); ?></p>
							</div>

							<div class="grt-booked-status">
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="display: flex; align-items: center; gap: 10px;">
									<input type="hidden" name="action" value="grt_update_booking_status">
									<input type="hidden" name="id" value="<?php echo esc_attr( $row->id ); ?>">
									<?php wp_nonce_field( 'grt_update_booking_status_nonce', 'grt_nonce' ); ?>
									
									<label for="status-<?php echo esc_attr( $row->id ); ?>"><strong><?php esc_html_e( 'Status:', 'grt-booking' ); ?></strong></label>
									<select name="status" id="status-<?php echo esc_attr( $row->id ); ?>">
										<option value="pending" <?php selected( $row->status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'grt-booking' ); ?></option>
										<option value="confirmed" <?php selected( $row->status, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'grt-booking' ); ?></option>
										<option value="completed" <?php selected( $row->status, 'completed' ); ?>><?php esc_html_e( 'Completed', 'grt-booking' ); ?></option>
										<option value="cancelled" <?php selected( $row->status, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'grt-booking' ); ?></option>
										<option value="booked" <?php selected( $row->status, 'booked' ); ?>><?php esc_html_e( 'Booked (Legacy)', 'grt-booking' ); ?></option>
									</select>
									<input type="submit" class="button button-small" value="<?php esc_attr_e( 'Update', 'grt-booking' ); ?>">
								</form>
							</div>

							<div class="grt-booked-actions" style="margin-top: 10px; text-align: right;">
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="display:inline;">
									<input type="hidden" name="action" value="grt_delete_availability">
									<input type="hidden" name="id" value="<?php echo esc_attr( $row->id ); ?>">
									<?php wp_nonce_field( 'grt_delete_availability_nonce', 'grt_nonce' ); ?>
									<input type="submit" class="button button-link-delete" value="<?php esc_attr_e( 'Remove', 'grt-booking' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to remove this booking?', 'grt-booking' ) ); ?>');">
								</form>
							</div>
							
							<div class="grt-booked-id" style="font-size: 10px; color: #aaa; margin-top: 5px;">
								ID: #<?php echo esc_html( $row->id ); ?>
							</div>
						</div>
						<?php
					}
				} else {
					echo '<div class="notice notice-info inline"><p>' . esc_html__( 'No booked dates found.', 'grt-booking' ) . '</p></div>';
				}
				?>
			</div>
		</div>
		<?php
	}
}
