    <?php

add_action( 'admin_menu', 'new_order_notification' );

function new_order_notification() {
    add_menu_page( 'New Order Notification', 'New Order Notification', 'manage_options' , 'new_order_notification' , 'new_order_notification_menu', 'dashicons-warning' , '153');
}

function new_order_notification_menu()
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
    
    $options = get_option('_new_order_option');
    if(!$options)
    {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
        $refreshTime = 10;
        $order_header = "Order Notification - New Order";
        $order_text = "Check Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
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
    }
    
    $isNew = false;
    $isAnyDeletion = false;
    if(in_array($last_order[0], $options['check_deleted'])) $isAnyDeletion = true;
    
    if($last_order[0] != $options['last_order'] && (!$isAnyDeletion))
    {
        $lastOrder = wc_get_order( $last_order[0] );
        echo("<script>console.log('PHP: ".$lastOrder->get_status()."');</script>");
        
        if($options['statuses'] == 9)
        {
            $isNew = true;
        }
        if($options['statuses'] == 8)
        {
            if($lastOrder->get_status() == 'on-hold' || $lastOrder->get_status() == 'processing') $isNew = true;
        }
        if($options['statuses'] == 6)
        {
            if($lastOrder->get_status() == 'pending' || $lastOrder->get_status() == 'processing') $isNew = true;
        }
        if($options['statuses'] == 5)
        {
            if($lastOrder->get_status() == 'processing') $isNew = true;
        }
        if($options['statuses'] == 4)
        {
            if($lastOrder->get_status() == 'pending' || $lastOrder->get_status() == 'on-hold') $isNew = true;
        }
        if($options['statuses'] == 3)
        {
            if($lastOrder->get_status() == 'pending') $isNew = true;
        }
        if($options['statuses'] == 1)
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
        $popupcontent .= "<div class='popup'><div class='cnt223'><h1>".$order_header."</h1><p>".$order_text." <a href='".esc_html($websiteUrl)."' target='_blank'>".esc_html($last_order[0])."</a><br/><br/><a href='' class='close'>".$confirm."</a></p></div></div>";
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
    
    $content .= "<table id='settings-new-order-notification'>";
    $content .= "<form action='' method='post' id='notificationSettingsForm'>";
    $content .= "<tr><th><span style='font-size:18px'>Settings for Notifications</span></th></tr>";
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
    $content .= "</table></form>";
    
    $isPosted = false;
    
    if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm'], 'notification_settings_form'))
    {
        if(isset($_POST['saveSettings']))
        {
            $order_statuses = 0;
            if( isset($_POST['inputForTime']) && !empty($_POST['inputForTime']) ) $refreshTime = sanitize_text_field($_POST['inputForTime']);
            if( isset($_POST['inputForMp3']) && !empty($_POST['inputForMp3']) ) $musicUrlMp3 = sanitize_text_field($_POST['inputForMp3']);
            if( isset($_POST['inputForHeader']) && !empty($_POST['inputForHeader']) ) $order_header = sanitize_text_field($_POST['inputForHeader']);
            if( isset($_POST['inputForText']) && !empty($_POST['inputForText']) ) $order_text = sanitize_text_field($_POST['inputForText']);
            if( isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm']) ) $confirm = sanitize_text_field($_POST['inputForConfirm']);
            if( isset($_POST['inputForPending']) && !empty($_POST['inputForPending']) ) $order_statuses += sanitize_text_field($_POST['inputForPending']);
            if( isset($_POST['inputForOnhold']) && !empty($_POST['inputForOnhold']) ) $order_statuses += sanitize_text_field($_POST['inputForOnhold']);
            if( isset($_POST['inputForProcessing']) && !empty($_POST['inputForProcessing']) ) $order_statuses += sanitize_text_field($_POST['inputForProcessing']);
            
            update_option('_new_order_option', array(
            'last_order'   =>  $last_order[0],
            'check_deleted' =>  $to_check_orders,
            'refresh_time'  =>  $refreshTime,
            'mp3_url'   =>  $musicUrlMp3,
            'order_header'  =>  $order_header,
            'order_text'    =>  $order_text,
            'confirm'   =>  $confirm,
            'statuses'  =>  $order_statuses,
            ));
            $isPosted = true;
            header("Refresh:0");
        }
        if(isset($_POST['resetSettings']))
        {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
            $refreshTime = 10;
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

    if(!$isNew && !$isPosted)
    {
        $time = $options['refresh_time'];
        header("Refresh:".esc_html($time)."");
    }
    
    echo $content;
}


?>
