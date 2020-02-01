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

---

To-Do: 
- A video tutorial for example usage.
- Audio file upload button instead of link field
- Setup option for activating on standard order page or with a custom order page: after activation ask user to choose one, then record inside options and let users to change it on the settings page.
- Detailed notification popup like standard Woocommerce order preview.
- Order notificiation popup title with order status (New Order -> Pending, On-Hold, Processing).
- Change order status while acknowledging.
- Show order statuses with selected Woocommerce language and provide translation feature.
