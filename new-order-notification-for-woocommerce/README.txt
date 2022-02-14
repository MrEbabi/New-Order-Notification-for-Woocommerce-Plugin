=== New Order Notification for Woocommerce ===
Contributors: mrebabi
Author URI: https://github.com/MrEbabi
Tags: woocommerce, woocommerce order page, woocommerce notification, woocommerce order notification, woocommerce new order notification, woocommerce new order popup
Requires at least: 3.1
Tested up to: 5.7
Stable tag: 1.4.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: new-order-notification-for-woocommerce

Woocommerce custom order page with recent orders for showing a popup notification with sound when a new order received.

== Description==
* POPUP NOTIFICATION WHEN A NEW ORDER RECEIVED

* NOTIFICATION KEEPS SHOWING UNTIL YOU ACKNOWLEDGE THE NEW ORDER

* SET YOUR ALARM SOUND TO PLAY WITH POPUP NOTIFICATION

* SET THE REFRESH TIME OF NEW ORDER PAGE

* SET ALERT FOR ORDERS THAT CONTAIN SELECTED PRODUCTS


**Woocommerce custom order page with recent orders for showing a popup notification with sound when a new order received.**

1. New Order Notification for WooCommerce is providing shop managers and administrators to see the recent orders on a custom order page.
2. This order page is customized to popup a notification when a new order received.
3. While the popup notification is showing to the admin, a music file also plays to alert admin.
4. This music keeps playing and popup keeps showing until the admin confirms the new order.
5. There are settings for this custom order page, popup notification and music file.
6. You may edit the string fields in popup notification.
7. You may edit the refresh time of custom order page (time to check if new order is received).
8. You may add a link to change the music file (.mp3 extension).
9. You may set product ID rules for order notification. (alert only if order contains product X)

**To ask new properties or report bugs, kindly inform globalcoupons@mrebabi.com**

== Installation ==
1. Upload the entire 'new-order-notification-for-woocommerce' folder to the '/wp-content/plugins/' directory or upload as a zip file then extract it to the '/wp-content/plugins/'
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look at your admin bar to see new section: New Order.
4. Open the New Order page and enjoy the plugin!

== Frequently Asked Questions ==
= Does this plugin work with newest Wordpress and Woocommerce version and also older versions? =
Yes, the plugin is tested with Wordpress 5.3 and Woocommerce 3.8.1 and works fine. Yet, you are welcome to inform us about any bugs so we can fix them.

= Can we still use standard Woocommerce Orders page while using this plugin? =
Yes, but the notification system only works with custom order page named New Order.

== Screenshots ==
1. New Order Notification Recent Orders Table
2. Popup Notification Preview When a New Order Received
3. Settings for Notifications

== Changelog ==
**=1.4.0=**
-Fix Product Id selection and User Role selection in the settings.
-Add settings for number of orders to show in recent order table.
-Add settings for order statuses that will be shown in recent order table.
-Optimize the plugin by refactoring the source code.

**=1.3.3=**
-Fix PHP Error for user roles.
-Add responsive css for new order popup.
-Fix reported bugs.

**=1.3.2=**
-Fix PHP Warnings.
-Fix User Role restriction error.
-Fix user role and product id removal error.

**=1.3.1=**
-Get default settings for date and time format from Wordpress Settings.
-Change table column names and popup texts.

**=1.3.0=**
-Change new order detection solution for better performance.
-Add audio play test feature to New Order Notification page.
-Add user role settings for access management in New Order Notification page.
-Add popup preview button to settings page.
-Shorter setting names with on hover information boxes.
-Change recent orders table to show only the selected order statuses.
-Other small performance improvements.
-Some CSS changes for both pages.

**=1.2.1=**
-Bug fixes for reported PHP errors.
-New Order Notifciation page is now accessable for roles: Super Admin, Admin, Editor, Author and Shop Manager.

**=1.2.0=**
-Sound playing problem when another tab is focused on the browser is solved.
-Audio loop feature is added.
-Better product ID selection with dropdown options.
-Improvement of order status selection in Settings to show all order statuses including custom statuses.
-Time zone problem of order date is solved.
-Better format for order date.
-Better CSS for Settings page.

**=1.1.2=**
-Better CSS for Popup and Settings page.
-Small bug fixes and speed optimization.

**=1.1.1=**
-Separate tab (submenu) for settings page.
-Access control changes: Shop manager and Administrator can access the New Order Notification page.
-Access control changes: Only Administrator can access the Settings page.

**=1.1.0=**
-Reported bug fixed for WooCommerce Shops that have not received any (0) or enough (<10) orders yet.
-An information message is added for WooCommerce Shops that have not received any orders.
-Auto refresh with every 5 seconds to detect the first order of a very new WooCommerce shop.

**=1.0.3=**
-"Alert only for orders that contain specific products" option is added.
-You may enter the product ids one by one from the related settings field.
-Small bug and CSS fixes.

**=1.0.2=**
-Settings providing selection of order statuses that the plugin will notify.
-Small bug fixes.
-CSS fixes.

**=1.0.1=**
-Small bug fixes.
-CSS additions.
-README is more detailed now.

**=1.0.0=**
-Hello World. This is the first version of the New Order Notification for Woocommerce.
-Initialized the source code.
-So it begins...