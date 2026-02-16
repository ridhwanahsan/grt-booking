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

        for (var i = 0; i < availableRanges.length; i++) {
            var range = availableRanges[i];
            if (dateString >= range.start_date && dateString <= range.end_date && range.status === 'available') {
                isAvailable = true;
                break;
            }
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
            children: $('#grt-children').val()
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
