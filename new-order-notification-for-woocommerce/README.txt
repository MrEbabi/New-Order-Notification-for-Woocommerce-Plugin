=== New Order Notification for WooCommerce ===
Contributors: mrebabi
Author URI: https://github.com/MrEbabi
Tags: woocommerce, order notification, admin alert, popup notification, sound alert, new order
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: new-order-notification-for-woocommerce

Instant popup and sound alerts for new WooCommerce orders — never miss a sale again.

== Description ==

New Order Notification for WooCommerce helps shop managers and administrators see recent orders on a custom order page and get notified instantly when a new order is received.

* Popup notification with sound alert when a new order is received.
* Notification stays visible until you acknowledge the new order.
* Custom alarm sound (MP3 URL) for the popup notification.
* Alert only for orders that contain selected products (product ID rules).
* Custom order table with recent orders, order preview and status change.

1. The plugin provides a custom order page where shop managers and admins can see recent orders.
2. This page shows a popup notification whenever a new order is received.
3. While the popup is visible, a sound is played to alert the admin.
4. The popup and the sound continue until the admin confirms the new order.
5. You can configure the refresh time, popup texts and the sound file URL.
6. You can restrict alerts to specific product IDs only.
7. You can control which order statuses and user roles are used for notifications.
8. You can control how many recent orders are shown in the custom order table.

To request new features or report bugs, please contact: info@mrebabi.com

== Installation ==

1. Upload the entire `new-order-notification-for-woocommerce` folder to the `/wp-content/plugins/` directory, or upload the ZIP file via the WordPress “Plugins > Add New” screen.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. In the admin menu, look for the new section: “New Order”.
4. Open the “New Order” page and enjoy instant notifications.

== Frequently Asked Questions ==

= Does this plugin work with the latest WordPress and WooCommerce versions? =

Yes. The plugin is tested with WordPress 6.9 and recent WooCommerce versions. You are welcome to report any issues so they can be fixed quickly.

= Can we still use the standard WooCommerce Orders page while using this plugin? =

Yes. You can still use the default WooCommerce Orders page. The popup and sound notification system works on the custom “New Order” page provided by this plugin.

== Screenshots ==

1. New Order Notification recent orders table.
2. Popup notification preview when a new order is received.
3. Quick View / Status Update for recent orders.
4. Settings page for notifications.

== Changelog ==

= 2.1.0 =
* Refactored main plugin bootstrap for better compatibility with modern WordPress and WooCommerce.
* Improved WooCommerce dependency checks and HPOS compatibility declaration.
* Cleaned up admin asset loading and plugin structure.
* Settings page updated.
* Removed old Notification page.

= 2.0.5 =
* Bug fixed.
* Donate link removed.

= 2.0.4 =
* Tested with latest WordPress and WooCommerce versions.

= 2.0.3 =
* WordPress and WooCommerce versions updated.
* Security issues fixed.

= 2.0.2 =
* WordPress and WooCommerce versions updated.

= 2.0.1 =
* Beta version moved as main page.
* Previous notification page moved as old page.
* Introduced support page.

= 2.0.0 =
* Beta version of newly designed New Order Notification page.
* New CSS for recent order table.
* Order preview and status change features.
* Activation for sound and popup alerts.
* Async calls instead of page refreshing.

= 1.4.0 =
* Fixed Product ID selection and User Role selection in settings.
* Added settings for number of orders to show in recent orders table.
* Added settings for order statuses that will be shown in recent orders table.
* Optimized the plugin by refactoring source code.

= 1.3.3 =
* Fixed PHP error for user roles.
* Added responsive CSS for new order popup.
* Fixed reported bugs.

= 1.3.2 =
* Fixed PHP warnings.
* Fixed user role restriction error.
* Fixed user role and product ID removal error.

= 1.3.1 =
* Used default settings for date and time format from WordPress Settings.
* Changed table column names and popup texts.

= 1.3.0 =
* Changed new order detection solution for better performance.
* Added audio play test feature to New Order Notification page.
* Added user role settings for access management in New Order Notification page.
* Added popup preview button to settings page.
* Shorter setting names with hover information boxes.
* Changed recent orders table to show only the selected order statuses.
* Other small performance improvements.
* Some CSS changes for both pages.

= 1.2.1 =
* Bug fixes for reported PHP errors.
* New Order Notification page is now accessible for roles: Super Admin, Admin, Editor, Author and Shop Manager.

= 1.2.0 =
* Fixed sound playing problem when another tab is focused in the browser.
* Added audio loop feature.
* Better product ID selection with dropdown options.
* Improved order status selection in Settings to show all order statuses including custom statuses.
* Fixed time zone problem of order date.
* Better formatted order date.
* Better CSS for Settings page.

= 1.1.2 =
* Better CSS for Popup and Settings page.
* Small bug fixes and speed optimization.

= 1.1.1 =
* Separate tab (submenu) for settings page.
* Access control changes: Shop Manager and Administrator can access the New Order Notification page.
* Access control changes: Only Administrator can access the Settings page.

= 1.1.0 =
* Fixed reported bug for WooCommerce shops that have not received any (0) or enough (<10) orders yet.
* Added an information message for WooCommerce shops that have not received any orders.
* Auto refresh every 5 seconds to detect the first order of a very new WooCommerce shop.

= 1.0.3 =
* Added “Alert only for orders that contain specific products” option.
* You may enter the product IDs one by one from the related settings field.
* Small bug and CSS fixes.

= 1.0.2 =
* Settings for selecting order statuses that the plugin will notify.
* Small bug fixes.
* CSS fixes.

= 1.0.1 =
* Small bug fixes.
* CSS additions.
* README made more detailed.

= 1.0.0 =
* First release of New Order Notification for WooCommerce.
