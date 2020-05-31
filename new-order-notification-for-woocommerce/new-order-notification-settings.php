<?php

function new_order_notification_settings()
{
    $user = wp_get_current_user();
    if ( !in_array( 'administrator', (array) $user->roles ) ) 
    {
        echo "<br><br><h2>Only Administrator can see Settings page.</h2>";
        return;
    }
    
    $_order_statuses = array_values(wc_get_order_statuses());
    $_order_status_names = array();
    $_order_status_name_index = 0;
    foreach($_order_statuses as $_order_status)
    {    
        $name = trim($_order_status);
        $name = str_replace(' ', '_', $name);
        $name = strtolower($name);
        array_push($_order_status_names, $name);
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
        $order_statuses = $_order_status_names;
        
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
    
    $allProductIds = get_posts( array(
      'posts_per_page' => -1,
      'post_type' => array('product','product_variation'),
      'fields' => 'ids',
    ) );
    
    $content = "<br><div class='settings-area'>";
    $content .= "<table id='settings-new-order-notification'>";
    $content .= "<form action='' method='post' id='notificationSettingsForm'>";
    $content .= "<tr><th><span style='font-size:18px'>Settings for Notifications</span></th></tr>";
    $content .= "<tr><th>Alert only for orders containing products with ID: </th><th><select name='inputForProductIds'><option value='' disabled selected></option>";
    foreach($allProductIds as $productId)
    {
        $content .= "<option value='".esc_html($productId)."'>".esc_html(get_the_title($productId))."</option>";
    }
    $content .= "</select></th></tr>";    
    
    $content .= "<tr><th>Refresh Time (in seconds): </th><th><input type='number' min='0' step='1' name='inputForTime' placeholder='".esc_html($options['refresh_time'])."'></th></tr>";
    $content .= "<tr><th>MP3 File URL (ends with .mp3): </th><th><input type='text' name='inputForMp3' placeholder='".esc_html($options['mp3_url'])."'></th></tr>";
    $content .= "<tr><th>Notification Header: </th><th><input type='text' name='inputForHeader' placeholder='".esc_html($options['order_header'])."'></th></tr>";
    $content .= "<tr><th>Notification Text: </th><th><input type='text' name='inputForText' placeholder='".esc_html($options['order_text'])."'></th></tr>";
    $content .= "<tr><th>Confirmation Text: </th><th><input type='text' name='inputForConfirm' placeholder='".esc_html($options['confirm'])."'></th></tr>";
    $content .= "<tr><th>Notification Order Statuses: </th><th>";
    foreach($_order_statuses as $_order_status)
    {
        if(in_array ($_order_status_names[$_order_status_name_index], $order_statuses))
        $content .= "<input type='checkbox' name='".esc_html($_order_status_names[$_order_status_name_index])."' value='".esc_html($_order_status_names[$_order_status_name_index])."' checked>". esc_html ($_order_status) ."<br>";
        else
        $content .= "<input type='checkbox' name='".esc_html($_order_status_names[$_order_status_name_index])."' value='".esc_html($_order_status_names[$_order_status_name_index])."'>". esc_html ($_order_status) ."<br>";
        $_order_status_name_index++;
    }
    $content .= wp_nonce_field('notification_settings_form', 'nonce_of_notificationSettingsForm');
    $content .= "<tr><th><input type='submit' value='Reset to Default' name='resetSettings'></th><th><input type='submit' value='Save Settings' name='saveSettings'></th></tr>";
    $content .= "</form></table></div>";
    
    $productLoop = 0;
    if(count($product_ids))
    {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Alert for Products with IDs:</span></th></tr>";
        while($productLoop < count($product_ids))
        {
            $content .= "<tr><th><input type='checkbox' value='".esc_html($options['product_ids'][$productLoop])."' name='selectProductId[]'>".esc_html($options['product_ids'][$productLoop])."</th></tr>";
            $productLoop++;
        }
        $content .= wp_nonce_field('notification_settings_form_2', 'nonce_of_notificationSettingsForm_2');
        $content .= "<tr><th><input type='submit' value='Remove Selected IDs' name='removeIds'></th></tr>";
        $content .= "</form></table></div>";
    }
    else
    {
        $content .= "<div class='settings-area-id'>";
        $content .= "<table id='settings-new-order-notification'>";
        $content .= "<tr><th><h4>No Product IDs provided, all products are included.</h4></th></tr>";
        $content .= "</table></div>";
    }
    
    $isPosted = false;
    
    if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm'], 'notification_settings_form'))
    {
        if(isset($_POST['saveSettings']))
        {
            $order_statuses = array();
            $count_products = count($product_ids);
            if( isset($_POST['inputForTime']) && !empty($_POST['inputForTime']) ) $refreshTime = sanitize_text_field($_POST['inputForTime']);
            if( isset($_POST['inputForMp3']) && !empty($_POST['inputForMp3']) ) $musicUrlMp3 = sanitize_text_field($_POST['inputForMp3']);
            if( isset($_POST['inputForHeader']) && !empty($_POST['inputForHeader']) ) $order_header = sanitize_text_field($_POST['inputForHeader']);
            if( isset($_POST['inputForText']) && !empty($_POST['inputForText']) ) $order_text = sanitize_text_field($_POST['inputForText']);
            if( isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm']) ) $confirm = sanitize_text_field($_POST['inputForConfirm']);
            
            foreach($_order_status_names as $_order_status_name) 
            {
                if( isset($_POST[$_order_status_name]) && !empty($_POST[$_order_status_name]) ) array_push($order_statuses, sanitize_text_field($_POST[$_order_status_name]));
            }

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
            'statuses'  =>  $_order_status_names,
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
