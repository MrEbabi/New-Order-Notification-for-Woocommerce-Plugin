<?php

function new_order_notification_settings()
{
    $order_statuses = array();
	$product_ids = get_posts( array(
      'posts_per_page' => -1,
      'post_type' => array('product','product_variation'),
      'fields' => 'ids',
    ) );
	global $wp_roles;
    $roles = $wp_roles->roles;
    $roleValues = array_keys($roles);
    $user_roles = $roleValues;
    $order_status_map = wc_get_order_statuses();
    $order_status_keys = array_keys($order_status_map);
    $order_status_values = array_values($order_status_map);
	
    $options = get_option('__new_order_option');
    if(!$options)
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
            'user_roles'   =>  $user_roles,
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
        $user_roles = $options['user_roles'];
    }
    
    $user = wp_get_current_user();
    if ( !in_array( 'administrator', (array) $user->roles ) ) 
    {
        echo "<br><br><h2>You don't have permission to see the Settings page.</h2>";
        return;
    }
    
    $checkOrders = array();
    $checkArgs = array('status' => array_keys( wc_get_order_statuses() ), );
    $checkOrders = wc_get_orders($checkArgs);
    $numberOfOrders = count($checkOrders);
    
    $allProductIds = get_posts( array(
      'posts_per_page' => -1,
      'post_type' => array('product','product_variation'),
      'fields' => 'ids',
    ) );
    
    $content = "<br><div class='settings-area'>";
    $content .= "<table id='settings-new-order-notification'>";
    $content .= "<form action='' method='post' id='notificationSettingsForm'>";
    $content .= "<tr><th><span style='font-size:18px'>Settings for Notifications</span></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>Product IDs: <span class='tooltiptext'>When you select product ids, the recent orders and alerts will be restricted with orders that contain selected product ids. </span></div></th><th><select multiple name='inputForProductIds[]'><option value='' disabled selected></option>";
    
    foreach($allProductIds as $productId) {
        if(is_array($product_ids) && count($product_ids) && count($allProductIds) != count($product_ids)) {
            if(!in_array($productId, $product_ids)) {
                $content .= "<option value='".esc_html($productId)."'>".esc_html(get_the_title($productId))."</option>";
            }
        } else {
            $content .= "<option value='".esc_html($productId)."'>".esc_html(get_the_title($productId))."</option>";
        }
    }
    $content .= "</select></th></tr>";    
    
    $content .= "<tr><th><div class='tooltip'>Refresh Time: <span class='tooltiptext'>Enter the refresh time in seconds, to check whether a new order is received or not. </span></div></th><th><input type='number' min='0' step='1' name='inputForTime' value='".esc_html($options['refresh_time'])."'></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>MP3 File URL: <span class='tooltiptext'>You can upload any .mp3 file using Media section in admin panel then copy the file URL and paste here to use it as alert media. </span></div></th><th><input type='text' name='inputForMp3' value='".esc_html($options['mp3_url'])."'></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>Notification Header: <span class='tooltiptext'>Enter the header text that is shown in the notification popup. </span></div></th><th><input type='text' name='inputForHeader' value='".esc_html($options['order_header'])."'></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>Notification Text: <span class='tooltiptext'>Enter the text that is shown in the notification popup just before the Order ID.</span></div></th><th><input type='text' name='inputForText' value='".esc_html($options['order_text'])."'></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>Confirmation Text: <span class='tooltiptext'>Enter the confirmation text which closes the notification popup.</span></div></th><th><input type='text' name='inputForConfirm' value='".esc_html($options['confirm'])."'></th></tr>";
    
    $content .= "<tr><th><div class='tooltip'>Notification Order Statuses: <span class='tooltiptext'>The new order alert and recent orders table works for the selected order statuses from this option.</span></div></th><th>";
    $_order_status_name_index = 0;
    foreach($order_status_keys as $order_status_key) {
        if(in_array ($order_status_key, $order_statuses)) {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='".esc_html($order_status_key)."' checked>". esc_html ($order_status_values[$_order_status_name_index]) ."<br>";
        }
        else {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='".esc_html($order_status_key)."'>". esc_html ($order_status_values[$_order_status_name_index]) ."<br>";
        }
        $_order_status_name_index++;
    }
    
    $content .= "<tr><th><div class='tooltip'>New Order Notification Page Roles: <span class='tooltiptext'>Select the user roles which can access the New Order Notification Page.</span></div></th><th><select name='inputForRoles[]' multiple><option value='' disabled selected></option>";
    $index = 0;
    foreach($roles as $role) {
        $roleValue = $roleValues[$index];
        $roleName = $role['name'];
        if(is_array($user_roles) && count($user_roles) && count($roles) != count($user_roles)) {
            if(!in_array($roleValue, $user_roles)) {
                $content .= "<option value='".esc_html($roleValue)."'>".esc_html($roleName)."</option>";
            }
        } else {
            $content .= "<option value='".esc_html($roleValue)."'>".esc_html($roleName)."</option>";
        }
        $index++;
    }
    $content .= "</select></th></tr>";
    
    $content .= wp_nonce_field('notification_settings_form', 'nonce_of_notificationSettingsForm');
    $content .= "<tr><th><input type='submit' value='Reset to Default' name='resetSettings'></th><th><input type='submit' value='Save Settings' name='saveSettings'></th></tr></form></table>";
    $content .= "<script type='text/javascript'>
                    jQuery(function($){
                        $('#openPreview').click(function(){
                            $('.popup').show();
                        });
                        $('#closePreview').click(function(){
                            $('.popup').hide();
                        });
                    });
                </script>
                <br><h2>Preview Alert Popup</h2><input type='button' id='openPreview' value='Preview'>";
    $content .= "</div>";
    $content .= "<div class='popup'><div class='cnt223'><h1>".esc_html($order_header)."</h1><p>".esc_html($options['order_text'])." <a href='#' target='_blank'>X</a><br/><br/><a href='' id='closePreview'>".esc_html($options['confirm'])."</a></p></div></div>";
    
    $productLoop = 0;
    if(is_array($product_ids) && count($product_ids) && count($allProductIds) != count($product_ids)) {
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
    else {
        $content .= "<div class='settings-area-id'>";
        $content .= "<table id='settings-new-order-notification'>";
        $content .= "<tr><th><h4>No Product IDs provided, all products are included.</h4></th></tr>";
        $content .= "</table></div>";
    }
    
    if(is_array($user_roles) && count($user_roles) && count($roles) != count($user_roles)) {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Permitted User Roles:</span></th></tr>";
        $roleLoop = 0;
        while($roleLoop < count($user_roles)) {
            $content .= "<tr><th><input type='checkbox' value='".esc_html($options['user_roles'][$roleLoop])."' name='selectUserRole[]'>".esc_html($options['user_roles'][$roleLoop])."</th></tr>";
            $roleLoop++;
        }
        $content .= wp_nonce_field('notification_settings_form_3', 'nonce_of_notificationSettingsForm_3');
        $content .= "<tr><th><input type='submit' value='Remove Selected User Roles' name='removeUserRoles'></th></tr>";
        $content .= "</form></table></div>";
    }
    else {
        $content .= "<div class='settings-area-id'>";
        $content .= "<table id='settings-new-order-notification'>";
        $content .= "<tr><th><h4>No User Roles provided, all user roles are permitted.</h4></th></tr>";
        $content .= "</table></div>";
    }
    
    $isPosted = false;
    
    if(isset($_POST['nonce_of_notificationSettingsForm'])) {
        if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm'], 'notification_settings_form') && isset($_POST['saveSettings'])) {
            $count_statuses = count($order_statuses);
            $count_products = count($product_ids);
            $count_roles = count($user_roles);
            if( isset($_POST['inputForTime']) && !empty($_POST['inputForTime']) ) $refreshTime = sanitize_text_field($_POST['inputForTime']);
            if( isset($_POST['inputForMp3']) && !empty($_POST['inputForMp3']) ) $musicUrlMp3 = sanitize_text_field($_POST['inputForMp3']);
            if( isset($_POST['inputForHeader']) && !empty($_POST['inputForHeader']) ) $order_header = sanitize_text_field($_POST['inputForHeader']);
            if( isset($_POST['inputForText']) && !empty($_POST['inputForText']) ) $order_text = sanitize_text_field($_POST['inputForText']);
            if( isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm']) ) $confirm = sanitize_text_field($_POST['inputForConfirm']);
            
            if( isset($_POST['inputForStatuses']) && !empty($_POST['inputForStatuses']) ) {
                $order_statuses = $_POST['inputForStatuses'];
            }

            if( isset($_POST['inputForProductIds']) && !empty($_POST['inputForProductIds']) ) {
                $index = 0;
                foreach($_POST['inputForProductIds'] as $inputForProductId) {
                    $product_ids[$count_products+$index] = sanitize_text_field($inputForProductId);
                    $index++;
                }
            }
            
            if( isset($_POST['inputForRoles']) && !empty($_POST['inputForRoles']) ) {
                $index = 0;
                foreach($_POST['inputForRoles'] as $inputForRole) {
                    $user_roles[$count_roles+$index] = sanitize_text_field($inputForRole);
                    $index++;
                }
            }
            
            update_option('__new_order_option', array(
                'refresh_time'  =>  $refreshTime,
                'mp3_url'   =>  $musicUrlMp3,
                'order_header'  =>  $order_header,
                'order_text'    =>  $order_text,
                'confirm'   =>  $confirm,
                'statuses'  =>  $order_statuses,
                'product_ids'   =>  $product_ids,
                'user_roles'    =>  $user_roles
            ));
            $isPosted = true;
            header("Refresh:0");
        }
        if(isset($_POST['resetSettings'])) {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3',__FILE__ );
            $refreshTime = 30;
            $order_header = "Order Notification - New Order";
            $order_text = "Check Order Details: ";
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
            $product_ids = get_posts( array(
              'posts_per_page' => -1,
              'post_type' => array('product','product_variation'),
              'fields' => 'ids',
            ) );
        	global $wp_roles;
            $roles = $wp_roles->roles;
            $roleValues = array_keys($roles);
            
            update_option('__new_order_option', array(
                'refresh_time'  =>  $refreshTime,
                'mp3_url'   =>  $musicUrlMp3,
                'order_header'  =>  $order_header,
                'order_text'    =>  $order_text,
                'confirm'   =>  $confirm,
                'statuses'  =>  $order_status_keys,
                'product_ids'   =>  $product_ids,
                'user_roles'    =>  $roleValues
            ));
            $isPosted = true;
            header("Refresh:0");
        }
    }
    
    if(isset($_POST['nonce_of_notificationSettingsForm_2'])) {
        if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_2'], 'notification_settings_form_2') && isset($_POST['selectProductId']) && !empty($_POST['selectProductId'])) {
            foreach($_POST['selectProductId'] as $checkedBox) {
                if (($key = array_search($checkedBox, $product_ids)) !== false) {
                    unset($product_ids[$key]);
                }                
            }
            
            $product_ids = array_values($product_ids);
            
            update_option('__new_order_option', array(
                'refresh_time'  =>  $refreshTime,
                'mp3_url'   =>  $musicUrlMp3,
                'order_header'  =>  $order_header,
                'order_text'    =>  $order_text,
                'confirm'   =>  $confirm,
                'statuses'  =>  $order_statuses,
                'product_ids'   =>  $product_ids,
                'user_roles'    =>  $user_roles
            ));
            $isPosted = true;
            header("Refresh:0");
        }
    }
    
    if(isset($_POST['nonce_of_notificationSettingsForm_3'])) {
        if(wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_3'], 'notification_settings_form_3') && isset($_POST['selectUserRole']) && !empty($_POST['selectUserRole'])) {
            foreach($_POST['selectUserRole'] as $checkedBox) {
                if (($key = array_search($checkedBox, $user_roles)) !== false) {
                    unset($user_roles[$key]);
                }                
            }
            
            $user_roles = array_values($user_roles);
            
            update_option('__new_order_option', array(
                'refresh_time'  =>  $refreshTime,
                'mp3_url'   =>  $musicUrlMp3,
                'order_header'  =>  $order_header,
                'order_text'    =>  $order_text,
                'confirm'   =>  $confirm,
                'statuses'  =>  $order_statuses,
                'product_ids'   =>  $product_ids,
                'user_roles'    =>  $user_roles
            ));
            $isPosted = true;
            header("Refresh:0");
        }
    }
    echo $content;
}

?>
