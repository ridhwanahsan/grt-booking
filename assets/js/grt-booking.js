jQuery(document).ready(function($) {
    
    // Parse available ranges
    var availableRanges = grt_booking_obj.available_ranges || [];

    // Helper to check if a date is available
    function isDateAvailable(date) {
        // Format date to YYYY-MM-DD
        var year = date.getFullYear();
        var month = ("0" + (date.getMonth() + 1)).slice(-2);
        var day = ("0" + date.getDate()).slice(-2);
        var dateString = year + "-" + month + "-" + day;

        var isAvailable = false;
        var isBooked = false;

        // 1. Check if date falls within ANY 'available' range
        for (var i = 0; i < availableRanges.length; i++) {
            var range = availableRanges[i];
            if (dateString >= range.start_date && dateString <= range.end_date && range.status === 'available') {
                isAvailable = true;
                break;
            }
        }

        // 2. Check if date falls within ANY 'booked' range (Collision Check)
        if (isAvailable) {
            for (var j = 0; j < availableRanges.length; j++) {
                var range = availableRanges[j];
                // Note: Using strict < end_date for hotel logic (checkout day is usually free), 
                // but matching backend logic: start < check_out AND end > check_in
                // If a date is 'booked' from Jan 5 to Jan 10.
                // Jan 5 is booked. Jan 6, 7, 8, 9 are booked.
                // Jan 10 is usually available for checkout/new checkin.
                
                // Let's stick to the logic: If date >= start AND date < end, it is occupied.
                if (range.status === 'booked') {
                     if (dateString >= range.start_date && dateString < range.end_date) {
                         isBooked = true;
                         break;
                     }
                     // Special case: If Check-in matches a booked Start Date, it's occupied (cannot double book start)
                     if (dateString === range.start_date) {
                         isBooked = true;
                         break;
                     }
                }
            }
        }

        if (isBooked) {
            return [false, "booked-date"]; // Return false to disable
        }

        return [isAvailable, ""];
    }

    // Initialize Datepickers
    $("#grt-checkin").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0,
        beforeShowDay: isDateAvailable,
        onSelect: function(selectedDate) {
            // Set min date for checkout
            var date = new Date(selectedDate);
            date.setDate(date.getDate() + 1);
            $("#grt-checkout").datepicker("option", "minDate", date);
            
            // Clear checkout if invalid
            var checkoutDate = $("#grt-checkout").val();
             if (checkoutDate && checkoutDate <= selectedDate) {
                $("#grt-checkout").val("");
            }
        }
    });

    $("#grt-checkout").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 1,
        beforeShowDay: isDateAvailable
    });

    // Form Submission
    $('#grt-booking-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('.grt-submit-btn');
        var $spinner = $form.find('.grt-spinner');
        var $message = $('#grt-form-message');
        
        // Reset message
        $message.hide().removeClass('success error').text('');
        
        // Client-side validation
        var checkin = $('#grt-checkin').val();
        var checkout = $('#grt-checkout').val();
        
        if (!checkin || !checkout) {
            $message.addClass('error').text(grt_booking_obj.messages.empty_fields).show();
            return;
        }
        
        if (checkin >= checkout) {
            $message.addClass('error').text(grt_booking_obj.messages.invalid_dates).show();
            return;
        }

        // Prepare data
        var data = {
            action: 'grt_check_availability',
            security: grt_booking_obj.nonce,
            check_in: checkin,
            check_out: checkout,
            adults: $('#grt-adults').val(),
            children: $('#grt-children').val(),
            email: email,
            phone: phone
        };

        // Disable button and show spinner
        $btn.prop('disabled', true);
        $spinner.show();

        // AJAX Request
        $.post(grt_booking_obj.ajax_url, data, function(response) {
            $btn.prop('disabled', false);
            $spinner.hide();
            
            if (response.success) {
                $message.addClass('success').text(response.data.message).fadeIn();
                // Optional: Clear form or redirect
                // $form[0].reset();
            } else {
                $message.addClass('error').text(response.data.message || 'An error occurred.').fadeIn();
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.hide();
            $message.addClass('error').text('Server error. Please try again.').fadeIn();
        });
    });

});
