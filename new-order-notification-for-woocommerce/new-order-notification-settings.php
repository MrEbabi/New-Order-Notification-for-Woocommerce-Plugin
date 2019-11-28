<?php

function new_order_notification_settings()
{
    $user = wp_get_current_user();
    if ( !in_array( 'administrator', (array) $user->roles ) ) 
    {
        echo "<br><br><h2>Only Administrator can access the Settings page.</h2>";
        return;
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
    
    $checkArgs = array('status' => array_keys( wc_get_order_statuses() ), );
    $checkOrders = wc_get_orders($checkArgs);
    $numberOfOrders = count($checkOrders);
    
    if(($numberOfOrders) == 0)
    {
        echo "<h1>You have not received any orders yet. This page will be refreshed for every 5 seconds to check if your first order is received.</h1>";
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
    }
    
    $content = "<br><br><div class='settings-area'>";
    $content .= "<table id='settings-new-order-notification'>";
    $content .= "<form action='' method='post' id='notificationSettingsForm'>";
    $content .= "<tr><th><span style='font-size:18px'>Settings for Notifications</span></th></tr>";
    $content .= "<tr><th>Alert only for orders containing products with ID: </th><th><input type='number' min=0 name='inputForProductIds' placeholder='Please enter one by one'></th></tr>";
    $content .= "<tr><th>Refresh Time (in seconds): </th><th><input type='number' min='0' step='1' name='inputForTime' placeholder='".esc_html($options['refresh_time'])."'></th></tr>";
    $content .= "<tr><th>MP3 File URL (ends with .mp3): </th><th><input type='text' name='inputForMp3' placeholder='".esc_html($options['mp3_url'])."'></th></tr>";
    $content .= "<tr><th>Notification Header: </th><th><input type='text' name='inputForHeader' placeholder='".esc_html($options['order_header'])."'></th></tr>";
    $content .= "<tr><th>Notification Text: </th><th><input type='text' name='inputForText' placeholder='".esc_html($options['order_text'])."'></th></tr>";
    $content .= "<tr><th>Confirmation Text: </th><th><input type='text' name='inputForConfirm' placeholder='".esc_html($options['confirm'])."'></th></tr>";
    //pending = 1, on-hold = 3, processing = 5, pending&on-hold = 4, pending&processing = 6, on-hold&processing = 8, pending&on-hold&processing = 9
    $content .= "<tr><th>Notification Order Statuses: </th><th>";
    if($options['statuses'] == 1 || $options['statuses'] == 4 || $options['statuses'] == 6 || $options['statuses'] == 9)
    {
        $content .= "<input type='checkbox' name='inputForPending' value='1' placeholder='Pending' checked>Pending<br>";
    }
    else
    {
        $content .= "<input type='checkbox' name='inputForPending' value='1' placeholder='Pending'>Pending<br>";
    }
    if($options['statuses'] == 3 || $options['statuses'] == 4 || $options['statuses'] == 8 || $options['statuses'] == 9)
    {
        $content .= "<input type='checkbox' name='inputForOnhold' value='3' checked>On-Hold<br>";
    }
    else
    {
        $content .= "<input type='checkbox' name='inputForOnhold' value='3'>On-Hold<br>";
    }
    if($options['statuses'] == 5 || $options['statuses'] == 6 || $options['statuses'] == 8 || $options['statuses'] == 9)
    {
        $content .= "<input type='checkbox' name='inputForProcessing' value='5' placeholder='Processing' checked>Processing</th></tr>";
    }
    else
    {
        $content .= "<input type='checkbox' name='inputForProcessing' value='5' placeholder='Processing'>Processing</th></tr>";
    }
    $content .= wp_nonce_field('notification_settings_form', 'nonce_of_notificationSettingsForm');
    $content .= "<tr><th><input type='submit' value='Reset to Default' name='resetSettings'></th><th><input type='submit' value='Save Settings' name='saveSettings'></th></tr>";
    $content .= "</form></table></div>";
    $content .= "<div class='settings-area' style='margin-left: 5%;'>";
    $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
    $content .= "<tr><th><span style='font-size:18px'>Alert for Products with IDs:</span></th></tr>";
    $productLoop = 0;
    if(count($product_ids))
    {
        while($productLoop < count($product_ids))
        {
            $content .= "<tr><th><input type='checkbox' value='".esc_html($options['product_ids'][$productLoop])."' name='selectProductId[]'>".esc_html($options['product_ids'][$productLoop])."</th></tr>";
            $productLoop++;
        }
    }
    else
    {
        $content .= "<tr><th><h3>No Product IDs provided, all products are included.</h3></th></tr>";
    }
    
    
    $content .= wp_nonce_field('notification_settings_form_2', 'nonce_of_notificationSettingsForm_2');
    $content .= "<tr><th><input type='submit' value='Remove Selected IDs' name='removeIds'></th></tr>";
    $content .= "</form></table></div>";
    
    $isPosted = false;
    
    if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm'], 'notification_settings_form'))
    {
        if(isset($_POST['saveSettings']))
        {
            $order_statuses = 0;
            $count_products = count($product_ids);
            if( isset($_POST['inputForTime']) && !empty($_POST['inputForTime']) ) $refreshTime = sanitize_text_field($_POST['inputForTime']);
            if( isset($_POST['inputForMp3']) && !empty($_POST['inputForMp3']) ) $musicUrlMp3 = sanitize_text_field($_POST['inputForMp3']);
            if( isset($_POST['inputForHeader']) && !empty($_POST['inputForHeader']) ) $order_header = sanitize_text_field($_POST['inputForHeader']);
            if( isset($_POST['inputForText']) && !empty($_POST['inputForText']) ) $order_text = sanitize_text_field($_POST['inputForText']);
            if( isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm']) ) $confirm = sanitize_text_field($_POST['inputForConfirm']);
            if( isset($_POST['inputForPending']) && !empty($_POST['inputForPending']) ) $order_statuses += sanitize_text_field($_POST['inputForPending']);
            if( isset($_POST['inputForOnhold']) && !empty($_POST['inputForOnhold']) ) $order_statuses += sanitize_text_field($_POST['inputForOnhold']);
            if( isset($_POST['inputForProcessing']) && !empty($_POST['inputForProcessing']) ) $order_statuses += sanitize_text_field($_POST['inputForProcessing']);
            if( isset($_POST['inputForProductIds']) && !empty($_POST['inputForProductIds']) ) $product_ids[$count_products] = sanitize_text_field($_POST['inputForProductIds']);
            
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
            $isPosted = true;
            header("Refresh:0");
        }
        if(isset($_POST['resetSettings']))
        {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
            $refreshTime = 30;
            $order_header = "Order Notification - New Order";
            $order_text = "Check Details: ";
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
            
            update_option('_new_order_option', array(
            'last_order'   =>  $last_order[0],
            'check_deleted' =>  $to_check_orders,
            'refresh_time'  =>  $refreshTime,
            'mp3_url'   =>  $musicUrlMp3,
            'order_header'  =>  $order_header,
            'order_text'    =>  $order_text,
            'confirm'   =>  $confirm,
            'statuses'  =>  9,
            ));
            $isPosted = true;
            header("Refresh:0");
        }
    }
    
    if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_2'], 'notification_settings_form_2'))
    {
        if(isset($_POST['selectProductId']) && !empty($_POST['selectProductId']))
        {
            foreach($_POST['selectProductId'] as $checkedBox)
            {
                if (($key = array_search($checkedBox, $product_ids)) !== false)
                {
                    unset($product_ids[$key]);
                    array_values($product_ids);
                }                
            }
            
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
            $isPosted = true;
            header("Refresh:0");
        }
    }
    
    echo $content;
}

?>
