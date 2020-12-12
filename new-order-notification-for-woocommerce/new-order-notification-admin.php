<?php

add_action( 'woocommerce_checkout_order_processed',  'detect_new_order_on_checkout' ); 

function detect_new_order_on_checkout($order_id) {
    $options = get_option('_new_order_id_for_notification');
    if(!$options) {
        add_option('_new_order_id_for_notification', array(
            'order_id' =>  $order_id
        ));
    } else {
        update_option('_new_order_id_for_notification', array(
            'order_id'  =>  $order_id
        ));
    }
}

add_action( 'admin_menu', 'new_order_notification' );

function new_order_notification() {
    add_menu_page( 'New Order Notification', 'New Order Notification', 'delete_posts' , 'new_order_notification' , 'new_order_notification_menu', 'dashicons-warning' , '54');
	add_submenu_page('new_order_notification', 'Settings', 'Settings', 'manage_options', 'new_order_notification_settings', 'new_order_notification_settings');
}

function new_order_notification_menu() {
    $isNew = false;
    $order_statuses = array();
	$product_ids = get_posts( array(
      'posts_per_page' => -1,
      'post_type' => array('product','product_variation'),
      'fields' => 'ids',
    ) );
	$last_order = array();
	global $wp_roles;
	$roles = $wp_roles->roles;
    $roleValues = array_keys($roles);
    $user_roles = $roleValues;
	$order_status_map = wc_get_order_statuses();
    $order_status_keys = array_keys($order_status_map);
    $order_status_values = array_values($order_status_map);
	
	$options = get_option('__new_order_option');
    if($options) {
        $musicUrlMp3 = $options['mp3_url'];
        $refreshTime = $options['refresh_time'];
        $order_header = $options['order_header'];
        $order_text = $options['order_text'];
        $confirm = $options['confirm'];
        $order_statuses = $options['statuses'];
        $product_ids = $options['product_ids'];
        $user_roles = $options['user_roles'];
        
        $user = wp_get_current_user();
        $isRestrictedUserRole = true;
        if(is_array($user_roles) && count($user_roles)) {
            foreach($user_roles as $user_role) {
            if ( in_array( $user_role, $user->roles) ) {
                $isRestrictedUserRole = false;
            }
        }
        } else {
            $isRestrictedUserRole = false;
        }
        if($isRestrictedUserRole) {
            echo "<br><br><h2>You don't have permission to see New Order Notification page.</h2>";
            return;
        }
    }
    else
    {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
        $refreshTime = 30;
        $order_header = "Order Notification - New Order";
        $order_text = "Check Order Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        
        add_option('__new_order_option', array(
            'refresh_time'  =>  $refreshTime,
            'mp3_url'   =>  $musicUrlMp3,
            'order_header'  =>  $order_header,
            'order_text'    =>  $order_text,
            'confirm'   =>  $confirm,
            'statuses'  =>  $order_status_keys,
            'product_ids'   =>  $product_ids,
            'user_roles'    =>  $user_roles
        ));
    }
    
    $checkArgs = array('status' => array_keys( wc_get_order_statuses() ), );
    $checkOrders = wc_get_orders($checkArgs);
	$numberOfOrders = 0;
	if(is_array($checkOrders)) $numberOfOrders = count($checkOrders);
    
    if($numberOfOrders == 0)
    {
        echo "<h1>You have not received any orders yet.<br><br>This page will be refreshed for every 5 seconds to check if your first order is received.</h1>";
        header("Refresh: 5");
        return;
    }
    else
    {
        $any_new_order = get_option('_new_order_id_for_notification');
        if($any_new_order) {
            $_order_statuses = array_values(wc_get_order_statuses());
            $alertForThisProduct = false;
            $isAllProducts = true;
            if(is_array($product_ids) && count($product_ids) != 0) {
                $isAllProducts = false;
            }
            
            $lastOrderId = $any_new_order['order_id'];
            $lastOrder = wc_get_order($lastOrderId);
            if(!$isAllProducts) {
                foreach( $lastOrder->get_items() as $item_id => $item ) {
                    $product_id = $item->get_product_id();
                    $variation_id = $item->get_variation_id();
                    if(in_array($product_id, $product_ids) || in_array($variation_id, $product_ids)) {
                        $alertForThisProduct = true;
                    }
                }
            }
            
            $statusPrefix = "wc-";
            $_lastOrderStatus = $lastOrder->get_status();
            $_lastOrderStatus = $statusPrefix . $_lastOrderStatus;
            
            if(in_array($_lastOrderStatus, $options['statuses']) && ($isAllProducts || $alertForThisProduct)) {
                $isNew = true;
            }
            
            $websiteUrl = get_site_url();
            $websiteUrl .= "/wp-admin/post.php?post=";
            $websiteUrl .= $lastOrderId;
            $websiteUrl .= "&action=edit";
            
            if($isNew) {
                ?>
                <script type='text/javascript'>
                    window.focus();
                    jQuery(function($){
                        var overlay = $('<div id="overlay"></div>');
                        overlay.show();
                        overlay.appendTo(document.body);
                        $('.popup').show();
                        $('.close').click(function(){
                        $('.popup').hide();
                        overlay.appendTo(document.body).remove();
                        location.reload();
                        return false;
                    });
                
                    $('.x').click(function(){
                        $('.popup').hide();
                        overlay.appendTo(document.body).remove();
                        return false;
                    });
                    });
                </script>
                <?php
                $audiocontent = "<audio controls autoplay loop><source src='".esc_html($options['mp3_url'])."' type='audio/mpeg'>Your browser does not support the audio element.</audio>";
                echo $audiocontent;
                $popupcontent = "<div class='popup'><div class='cnt223'><h1>".esc_html($order_header)."</h1><p>".esc_html($options['order_text'])." <a href='".esc_html($websiteUrl)."' target='_blank'>".esc_html($lastOrderId)."</a><br/><br/><a href='' class='close'>".esc_html($options['confirm'])."</a></p></div></div>";
                echo $popupcontent;
                delete_option('_new_order_id_for_notification');
            }
        }
    }
    
    $recent_orders = wc_get_orders( array(
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
        'status'=> $options['statuses'],
    ) );
    
    $content = "<h1>New Order Notification for Woocommerce</h1>";
    $content .= "<table id='customers-new-order-notification'>";
    $content .= "<tr><th>Recent Orders</th></tr>";
    $content .= "<tr><th>Order No</th><th>Order Date</th><th>Order Status</th><th>Order Details</th></tr>";
    foreach($recent_orders as $recent_order) {        
        $order_id = $recent_order->get_id();
        $_order = wc_get_order($order_id);
        $order_date = $_order->get_date_created();
        $order_status = $recent_order->get_status();
        $order_link = get_site_url();
        $order_link .= "/wp-admin/post.php?post=";
        $order_link .= $order_id;
        $order_link .= "&action=edit";
        
        
        $statusPrefix = "wc-";
        $_orderStatus = $statusPrefix . $order_status;
        $_order_status = $order_status_map[$_orderStatus];
        
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        
        $format_order_date = $time_format . " - " . $date_format;
        
        $content .= "<tr><td>".esc_html($order_id)."</td><td>".esc_html($order_date->date($format_order_date))."</td><td>".esc_html($_order_status)."</td><td><a href='".esc_html($order_link)."' target='_blank'>Order ".esc_html($order_id)."</a></td></tr>";
    }
    
    $content .= "</table><br><hr>";
    
    if(!$isNew)
    {
        delete_option('_new_order_id_for_notification');
        $time = $options['refresh_time'];
        header("Refresh:".esc_html($time)."");
    }
    
    $content .= "<br><br><div class='main-info-bottom'><p>1 - To be warned when a new order received, keep this page opened in your browser.</p><p>2 - Recent Order Table gets Timezone and Date Format settings from Settings -> General.</p>";
    $content .= "<p>3 - You can test audio alert: </p><audio controls loop style='display: block'><source src='".esc_html($options['mp3_url'])."' type='audio/mpeg'>Your browser does not support the audio element.</audio></div>";
    echo $content;
}
