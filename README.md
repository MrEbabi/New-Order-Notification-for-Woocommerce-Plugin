# Woocommerce New Order Notification Plugin

Woocommerce custom order page for showing a popup notification with sound when a new order received. 

Usage: 

- Download the current version from release folder and upload it to wp-content/plugins using FTP or using Admin Panel -> Plugins -> Add New -> Upload Plugin, then extract there.

or

- Download: https://wordpress.org/plugins/new-order-notification-for-woocommerce/

or

- Wordpress Admin Panel -> Plugins -> Add New -> Search -> "New Order Notification for Woocommerce" -> Install -> Activate

---

Version 1.0.0:

- New Order Notification Popup with Sound effect in a custom order page at admin panel.
- Settings for New Order Notification Popup strings, refresh time of order controller and music file url.

Version 1.0.1:

- Small bug fixes.
- CSS additions.
- README is more detailed now.

Version 1.0.2:

- Settings field for selection of order statuses that the plugin will notify.
- CSS fixes.
- Small bug fixes.

Version 1.0.3:

- "Alert only for orders that contain specific products" option is added.
- You may enter the product ids one by one from the related settings field.
- Small bug and CSS fixes.

Version 1.1.0:

- Reported bug fixed for WooCommerce Shops that have not received any (0) or enough (<10) orders yet.
- An information message is added for WooCommerce Shops that have not received any orders.
- Auto refresh with every 5 seconds to detect the first order of a very new WooCommerce shop.

Version 1.1.1:

- Separate tab (submenu) for settings page.
- Access control changes: Shop manager and Administrator can access the New Order Notification page.
- Access control changes: Only Administrator can access the Settings page.
- Warning message if WooCommerce plugin is not installed or activated.

Version 1.1.2:

- Better CSS for Settings page.
- Small bug fixes, speed optimizations.

Version 1.2.0:

- Sound playing problem when another tab is focused on the browser is solved.
- Audio loop feature is added.
- Better product ID selection with dropdown options.
- Improvement of order status selection in Settings to show all order statuses including custom statuses.
- Time zone problem of order date is solved.
- Better format for order date.
- Better CSS for Settings page.

Version 1.2.1:

- Bug fixes for reported PHP errors.
- New Order Notifciation page is now accessable for roles: Super Admin, Admin, Editor, Author and Shop Manager.

Version 1.3.0

- Change new order detection solution for better performance.
- Add audio play test feature to New Order Notification page.
- Add user role settings for access management in New Order Notification page.
- Add popup preview button to settings page.
- Shorter setting names with on hover information boxes.
- Change recent orders table to show only the selected order statuses.
- Other small performance improvements.
- Some CSS changes for both pages.

Version 1.3.1:

- Get default settings for date and time format from Wordpress Settings.
- Change table column names and popup texts.

Version 1.3.2 & 1.3.3

- Fix PHP Error for user roles.
- Add responsive css for new order popup.
- Fix reported bugs.
- Fix PHP Warnings for empty arrays.
- Fix User Role restriction error.
- Fix user role and product id removal error in settings.

---

v2.0 todo:
- Usage instructions like browser and device type, browser settings for autoplay (Wordpress Support)
- Order notification popup title with order status (New Order -> Pending/On-Hold/Processing..) +++
- Order notification popup second acknowledge button to provide order status change functionality +++
- Better UI/UX for custom order page and new order popup +++
