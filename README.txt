=== GRT Booking ===
Contributors: ridhwanahsann
Tags: booking, reservation, hotel, room
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A comprehensive room reservation system for WordPress.

== Description ==

GRT Booking is a lightweight and powerful room reservation plugin. It allows administrators to define available booking periods and provides a frontend form for users to check room availability.

**Features:**
*   Responsive booking form via shortcode [grt_booking_form].
*   AJAX-powered availability checking.
*   Admin settings for minimum/maximum stay.
*   Customizable submit button text.
*   Date range management for room availability.
*   **Smart Date Picker**: Only admin-defined available dates are selectable in the frontend form.

== Installation ==

1.  Upload the `grt-booking` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to Settings > GRT Booking to configure the plugin.
4.  **Important**: Add availability ranges in the admin settings. The frontend form will NOT allow date selection until availability is defined.
5.  Use the shortcode `[grt_booking_form]` on any page or post to display the booking form.

== Usage ==

1.  **Admin Configuration:**
    *   Navigate to **Settings > GRT Booking**.
    *   Set the Minimum and Maximum stay duration.
    *   Customize the "Submit Button Text".
    *   Use the "Availability Management" section to add dates when the room is available.

2.  **Frontend Display:**
    *   Create a new page or edit an existing one.
    *   Insert the shortcode: `[grt_booking_form]`
    *   Publish the page.

== Frequently Asked Questions ==

= How do I add availability? =
Go to Settings > GRT Booking and use the "Add Available Date Range" form.

= Can I customize the form style? =
Yes, the plugin uses `assets/css/grt-booking.css`. You can override these styles in your theme's CSS.

== Screenshots ==

1.  Admin Settings Page
2.  Frontend Booking Form

== Changelog ==

= 1.0.0 =
*   Initial release.
