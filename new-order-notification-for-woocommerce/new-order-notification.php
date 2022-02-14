<?php
/*
Plugin Name: New Order Notification for Woocommerce
Description: Woocommerce custom order page with recent orders for showing a popup notification with sound when a new order received.
Version: 1.4.0
Author: Mr.Ebabi
Author URI: https://github.com/MrEbabi
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: new-order-notification-for-woocommerce
WC requires at least: 2.5
WC tested up to: 5.5.2
*/

if(!defined('ABSPATH'))
{
    die;
}

defined('ABSPATH') or die('You shall not pass!');

if(!function_exists('add_action'))
{
    echo "You shall not pass!";
    exit;
}

//require woocommerce to install global coupons for woocommerce
add_action( 'admin_init', 'new_order_notification_require_woocommerce' );

function new_order_notification_require_woocommerce() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) 
    {
        add_action( 'admin_notices', 'new_order_notification_require_woocommerce_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) 
        {
            unset( $_GET['activate'] );
        }
    }
}

//throw admin notice if woocommerce is not active
function new_order_notification_require_woocommerce_notice(){
    ?>
    <style> #toplevel_page_new_order_notification{display:none;} </style>
    <div class="error"><p>Sorry, but New Order Notification for Woocommerce requires the Woocommerce plugin to be installed and activated.</p></div>
    <?php
    return;
}

//settings link for plugin page
function new_order_notification_settings_link( $links ) 
{
    if(!is_admin()) exit();

	$links[] = '<a href="' .
		admin_url( 'admin.php?page=new_order_notification_settings' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}

//css for admin panel
function new_order_notification_admin_css() 
{
	wp_register_style('new-order-notification-admin-css', plugins_url('assets/new-order-notification.css',__FILE__ ), array(), rand(111,9999), 'all');
    wp_enqueue_style('new-order-notification-admin-css');
}

add_action( 'admin_init','new_order_notification_admin_css');

function new_order_notification_admin_js()
{
    wp_register_style('new-order-notification-admin-js', 'https://code.jquery.com/jquery-1.8.2.js' , array(), rand(111,9999), 'all');
    wp_enqueue_style('new-order-notification-admin-js');
    
}

add_action( 'admin_init','new_order_notification_admin_js');

if(!class_exists('NewOrderNotification'))
{
    class NewOrderNotification
    {
        function __construct()
        {
            add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'new_order_notification_settings_link');
            require_once(dirname(__FILE__) . '/new-order-notification-admin.php');
            require_once(dirname(__FILE__) . '/new-order-notification-settings.php');
        }
    }
}

if(class_exists('NewOrderNotification'))
{
    $newOrderNotification = new NewOrderNotification();
}

register_activation_hook( __FILE__, array($newOrderNotification, '__construct'));

