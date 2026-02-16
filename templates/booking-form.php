<div class="grt-booking-container">
	<h2 class="grt-booking-title"><?php esc_html_e( 'Reserve Your Room', 'grt-booking' ); ?></h2>
	
	<form id="grt-booking-form" class="grt-form">
		
		<div class="grt-form-row">
			<div class="grt-form-group">
				<label for="grt-checkin"><?php esc_html_e( 'Check-in Date', 'grt-booking' ); ?> <span class="required">*</span></label>
				<input type="text" id="grt-checkin" name="check_in" class="grt-datepicker-frontend" required autocomplete="off" placeholder="<?php esc_attr_e('Select Date', 'grt-booking'); ?>">
			</div>

			<div class="grt-form-group">
				<label for="grt-checkout"><?php esc_html_e( 'Check-out Date', 'grt-booking' ); ?> <span class="required">*</span></label>
				<input type="text" id="grt-checkout" name="check_out" class="grt-datepicker-frontend" required autocomplete="off" placeholder="<?php esc_attr_e('Select Date', 'grt-booking'); ?>">
			</div>
		</div>

		<div class="grt-form-row">
			<div class="grt-form-group">
				<label for="grt-adults"><?php esc_html_e( 'Adults', 'grt-booking' ); ?></label>
				<input type="number" id="grt-adults" name="adults" min="1" max="10" value="1">
			</div>

			<div class="grt-form-group">
				<label for="grt-children"><?php esc_html_e( 'Children', 'grt-booking' ); ?></label>
				<input type="number" id="grt-children" name="children" min="0" max="10" value="0">
			</div>
		</div>

		<div class="grt-form-actions">
			<button type="submit" id="grt-submit-btn" class="grt-submit-btn">
				<span class="grt-btn-text"><?php echo esc_html( $submit_text ); ?></span>
				<span class="grt-spinner"></span>
			</button>
		</div>

		<div id="grt-form-message" class="grt-message"></div>

	</form>
</div>
