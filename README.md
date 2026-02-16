# 🏨 GRT Booking

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-GPLv2-green.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)

**GRT Booking** is a comprehensive, lightweight, and powerful room reservation system for WordPress. It provides a seamless booking experience for your customers and an easy-to-use management interface for administrators.

---

## ✨ Features

*   **📅 Smart Availability Calendar:** Frontend date pickers only allow selection of dates you've explicitly marked as available.
*   **📱 Fully Responsive:** The booking form looks great on desktops, tablets, and mobile devices.
*   **⚡ AJAX-Powered:** Instant availability checking without page reloads.
*   **🛠️ Flexible Configuration:** Set minimum and maximum stay durations to fit your business model.
*   **🎨 Customizable:** Easily change the submit button text and override styles via CSS.
*   **🔒 Secure:** Built with WordPress best practices, including Nonce verification and data sanitization.

---

## 🚀 Installation

1.  **Upload:** Upload the `grt-booking` folder to the `/wp-content/plugins/` directory.
2.  **Activate:** Go to the **Plugins** menu in WordPress and activate **GRT Booking**.
3.  **Configure:** Navigate to **Settings > GRT Booking** to set up your preferences.
4.  **Set Availability:** **Crucial Step!** Add available date ranges in the admin settings. The frontend form will not function until you define when rooms are open.
5.  **Publish:** Add the shortcode `[grt_booking_form]` to any page or post.

---

## 📖 Usage

### Admin Configuration
Navigate to **Settings > GRT Booking** to access the dashboard:
*   **General Settings:** Define stay limits (Min/Max nights) and button labels.
*   **Availability Management:** Add specific date ranges (e.g., "2024-06-01" to "2024-06-30") to make them bookable.

### Frontend Display
Simply insert the following shortcode where you want the form to appear:

```shortcode
[grt_booking_form]
```

---

## 📸 Screenshots

| Admin Settings | Frontend Form |
|:---:|:---:|
| *Manage availability and settings* | *Clean, responsive user interface* |

---

## ❓ Frequently Asked Questions

**Q: Why are all dates grayed out in the calendar?**
A: By default, no dates are available. You must go to **Settings > GRT Booking** and add an "Available Date Range" for the dates you want to be bookable.

**Q: Can I style the form?**
A: Absolutely! The plugin uses `assets/css/grt-booking.css`. You can easily override these styles in your theme's custom CSS or child theme.

---

## 📝 Changelog

### 1.0.0
*   🎉 Initial release.
*   Implemented AJAX availability checking.
*   Added smart date picker with admin-controlled availability.

---

**Contributors:** [ridhwanahsann](https://github.com/ridhwanahsann)
