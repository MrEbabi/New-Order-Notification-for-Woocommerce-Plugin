<?php

add_action( 'admin_menu', 'new_order_notification' );

function new_order_notification() {
    add_menu_page( 'New Order Notification', 'New Order Notification', 'manage_woocommerce' , 'new_order_notification' , 'new_order_notification_menu', 'dashicons-warning' , '54');
	add_submenu_page('new_order_notification', 'Settings', 'Settings', 'manage_woocommerce', 'new_order_notification_settings', 'new_order_notification_settings');
}

function new_order_notification_menu()
{
    $checkArgs = array('status' => array_keys( wc_get_order_statuses() ), );
    $checkOrders = wc_get_orders($checkArgs);
    $numberOfOrders = count($checkOrders);
    
    if(($numberOfOrders) == 0)
    {
        echo "<h1>You have not received any orders yet. This page will be refreshed for every 5 seconds to check if your first order is received.</h1>";
        header("Refresh: 5");
        return;
    }
    else if($numberOfOrders < 10 && $numberOfOrders > 0)
    {
        $query = new WC_Order_Query( array(
        'limit' => $numberOfOrders,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
        ) );
        $last_order = $query->get_orders();
        
        $recent_orders = wc_get_orders( array(
            'limit' => $numberOfOrders,
            'orderby' => 'date',
            'order' => 'DESC',
        ) );
        
        $query = new WC_Order_Query( array(
            'offset'    =>  1,
            'limit' => $numberOfOrders-1,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ) );
        $to_check_orders = $query->get_orders();
    }
    else
    {
        $query = new WC_Order_Query( array(
        'limit' => 6,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
        ) );
        $last_order = $query->get_orders();
        
        $recent_orders = wc_get_orders( array(
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ) );
        
        $query = new WC_Order_Query( array(
            'offset'    =>  1,
            'limit' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ) );
        $to_check_orders = $query->get_orders();
    }
    
    $options = get_option('_new_order_option');
    if(!$options)
    {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
        $refreshTime = 30;
        $order_header = "Order Notification - New Order";
        $order_text = "Check Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        $product_ids = array ();
        //pending = 1, on-hold = 3, processing = 5, pending&on-hold = 4, pending&processing = 6, on-hold&processing = 8, pending&on-hold&processing = 9
        $order_statuses = 9;
        
        add_option('_new_order_option', array(
        'last_order'   =>  $last_order[0],
        'check_deleted' =>  $to_check_orders,
        'refresh_time'  =>  $refreshTime,
        'mp3_url'   =>  $musicUrlMp3,
        'order_header'  =>  $order_header,
        'order_text'    =>  $order_text,
        'confirm'   =>  $confirm,
        'statuses'  =>  $order_statuses,
        'product_ids'   =>  $product_ids,
        ));
    }
    else
    {
        $musicUrlMp3 = $options['mp3_url'];
        $refreshTime = $options['refresh_time'];
        $order_header = $options['order_header'];
        $order_text = $options['order_text'];
        $confirm = $options['confirm'];
        $order_statuses = $options['statuses'];
        $product_ids = $options['product_ids'];
    }
    
    $alertForThisProduct = false;
    $isAllProducts = true;
    if(count($product_ids) != 0)
    {
        $isAllProducts = false;
    }
    $isNew = false;
    $isAnyDeletion = false;
    if(in_array($last_order[0], $options['check_deleted'])) $isAnyDeletion = true;
    
    if($last_order[0] != $options['last_order'] && (!$isAnyDeletion))
    {
        $lastOrder = wc_get_order( $last_order[0] );
        if(!$isAllProducts)
        {
            foreach( $lastOrder->get_items() as $item_id => $item )
            {
                $product_id = $item->get_product_id();
                $variation_id = $item->get_variation_id();
                if(in_array($product_id, $product_ids) || in_array($variation_id, $product_ids))
                {
                    $alertForThisProduct = true;
                }
            }
        }
        
        if($options['statuses'] == 9 && ($isAllProducts || $alertForThisProduct) )
        {
            $isNew = true;
        }
        if($options['statuses'] == 8 && ($isAllProducts || $alertForThisProduct))
        {
            if($lastOrder->get_status() == 'on-hold' || $lastOrder->get_status() == 'processing' && ($isAllProducts || $alertForThisProduct)) $isNew = true;
        }
        if($options['statuses'] == 6)
        {
            if($lastOrder->get_status() == 'pending' || $lastOrder->get_status() == 'processing' && ($isAllProducts || $alertForThisProduct)) $isNew = true;
        }
        if($options['statuses'] == 5 && ($isAllProducts || $alertForThisProduct))
        {
            if($lastOrder->get_status() == 'processing' ) $isNew = true;
        }
        if($options['statuses'] == 4 && ($isAllProducts || $alertForThisProduct))
        {
            if($lastOrder->get_status() == 'pending' || $lastOrder->get_status() == 'on-hold') $isNew = true;
        }
        if($options['statuses'] == 3 && ($isAllProducts || $alertForThisProduct))
        {
            if($lastOrder->get_status() == 'pending') $isNew = true;
        }
        if($options['statuses'] == 1 && ($isAllProducts || $alertForThisProduct))
        {
            if($lastOrder->get_status() == 'pending') $isNew = true;
        }
    }
    
    $websiteUrl = get_site_url();
    $websiteUrl .= "/wp-admin/post.php?post=";
    $websiteUrl .= $last_order[0];
    $websiteUrl .= "&action=edit";
    
    if($isNew)
    {
        ?>
        <script type='text/javascript'>
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
        
        $popupcontent = "<audio controls autoplay><source src='".esc_html($options['mp3_url'])."' type='audio/mpeg'>Your browser does not support the audio element.</audio>";
        $popupcontent .= "<div class='popup'><div class='cnt223'><h1>".esc_html($order_header)."</h1><p>".esc_html($order_text)." <a href='".esc_html($websiteUrl)."' target='_blank'>".esc_html($last_order[0])."</a><br/><br/><a href='' class='close'>".esc_html($confirm)."</a></p></div></div>";
        echo $popupcontent;
        
        update_option('_new_order_option', array(
            'last_order'   =>  $last_order[0],
            'check_deleted' =>  $to_check_orders,
            'refresh_time'  =>  $refreshTime,
            'mp3_url'   =>  $musicUrlMp3,
            'order_header'  =>  $order_header,
            'order_text'    =>  $order_text,
            'confirm'   =>  $confirm,
            'statuses'  =>  $order_statuses,
            'product_ids'   =>  $product_ids,
            ));
    }
    $content = "<h1>New Order Notification for Woocommerce</h1>";
    $content .= "<table id='customers-new-order-notification'>";
    $content .= "<tr><th>Recent Orders</th></tr>";
    $content .= "<tr><th>Order No</th><th>Order Date</th><th>Order Status</th><th>Check Details</th></tr>";
    foreach($recent_orders as $recent_order)
    {
        $order_id = $recent_order->ID;
        $order_date = $recent_order->date_created;
        $order_date = substr($order_date, 0, 19);
        $order_date = explode('T', $order_date);

        $order_status = $recent_order->status;
        $order_link = get_site_url();
        $order_link .= "/wp-admin/post.php?post=";
        $order_link .= $order_id;
        $order_link .= "&action=edit";
        
        $content .= "<tr><td>".esc_html($order_id)."</td><td>".esc_html($order_date[0])." - ".esc_html($order_date[1])."</td><td>".esc_html(ucfirst($order_status))."</td><td><a href='".esc_html($order_link)."'>Order ".esc_html($order_id)."</a></td></tr>";
    }
    
    $content .= "</table><br><hr>";
    
    if(!$isNew && !$isPosted)
    {
        $time = $options['refresh_time'];
        header("Refresh:".esc_html($time)."");
    }
    
    $content .= "To be warned when a new order received, keep this page opened in your browser.";
    echo $content;
}


?>
