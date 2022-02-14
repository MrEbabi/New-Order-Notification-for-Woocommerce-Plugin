<?php

function new_order_notification_settings()
{
    $allProductIds = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => array('product', 'product_variation'),
        'fields' => 'ids',
    ));

    global $wp_roles;
    $roles = $wp_roles->roles;
    $roleValues = array_keys($roles);

    $order_status_map = wc_get_order_statuses();
    $order_status_keys = array_keys($order_status_map);
    $order_status_values = array_values($order_status_map);

    $options = get_option('__new_order_option');
    if ($options) {
        if ($options['mp3_url']) {
            $musicUrlMp3 = $options['mp3_url'];
        } else {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
        }
        if ($options['refresh_time']) {
            $refreshTime = $options['refresh_time'];
        } else {
            $refreshTime = 30;
        }
        if ($options['order_header']) {
            $order_header = $options['order_header'];
        } else {
            $order_header = "Order Notification - New Order";
        }
        if ($options['order_text']) {
            $order_text = $options['order_text'];
        } else {
            $order_text = "Check Order Details: ";
        }
        if ($options['confirm']) {
            $confirm = $options['confirm'];
        } else {
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        }
        if ($options['statuses']) {
            $order_statuses = $options['statuses'];
        } else {
            $order_statuses = $order_status_keys;
        }
        if ($options['product_ids']) {
            $product_ids = $options['product_ids'];
        } else {
            $product_ids = $allProductIds;
        }
        if ($options['user_roles']) {
            $user_roles = $options['user_roles'];
        } else {
            $user_roles = $roleValues;
        }
        if ($options['show_order_num']) {
            $show_order_num = $options['show_order_num'];
        } else {
            $show_order_num = 20;
        }
        if ($options['show_order_statuses']) {
            $show_order_statuses = $options['show_order_statuses'];
        } else {
            $show_order_statuses = $order_status_keys;
        }
        update_option('__new_order_option', array(
            'refresh_time' => $refreshTime,
            'mp3_url' => $musicUrlMp3,
            'order_header' => $order_header,
            'order_text' => $order_text,
            'confirm' => $confirm,
            'statuses' => $order_statuses,
            'product_ids' => $product_ids,
            'user_roles' => $user_roles,
            'show_order_num' => $show_order_num,
            'show_order_statuses' => $show_order_statuses
        ));

    } else {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
        $refreshTime = 30;
        $order_header = "Order Notification - New Order";
        $order_text = "Check Order Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        $order_statuses = $order_status_keys;
        $product_ids = $allProductIds;
        $user_roles = $roleValues;
        $show_order_num = 20;
        $show_order_statuses = $order_status_keys;

        add_option('__new_order_option', array(
            'refresh_time' => $refreshTime,
            'mp3_url' => $musicUrlMp3,
            'order_header' => $order_header,
            'order_text' => $order_text,
            'confirm' => $confirm,
            'statuses' => $order_statuses,
            'product_ids' => $product_ids,
            'user_roles' => $user_roles,
            'show_order_num' => $show_order_num,
            'show_order_statuses' => $show_order_statuses
        ));
    }

    $user = wp_get_current_user();
    if (!in_array('administrator', (array)$user->roles)) {
        echo "<br><br><h2>You don't have permission to see the Settings page.</h2>";
        return;
    }

    $content = "<br><div class='settings-area'>";
    $content .= "<table id='settings-new-order-notification'>";
    $content .= "<form action='' method='post' id='notificationSettingsForm'>";
    $content .= "<tr><th><span style='font-size:18px'>Settings for Notifications</span></th></tr>";
    // Product Ids Selection
    $content .= "<tr><th><div class='tooltip'>Product IDs: <span class='tooltiptext'>When you select product ids, the recent orders and alerts will be restricted with orders that contain selected product ids. </span></div></th><th><select multiple name='inputForProductIds[]'><option value='' disabled selected></option>";
    foreach ($allProductIds as $productId) {
        if (is_array($product_ids) && count($product_ids) && count($allProductIds) != count($product_ids)) {
            if (!in_array($productId, $product_ids)) {
                $content .= "<option value='" . esc_html($productId) . "'>" . esc_html(get_the_title($productId)) . "</option>";
            }
        } else {
            $content .= "<option value='" . esc_html($productId) . "'>" . esc_html(get_the_title($productId)) . "</option>";
        }
    }
    $content .= "</select></th></tr>";
    // Refresh Time Selection
    $content .= "<tr><th><div class='tooltip'>Refresh Time: <span class='tooltiptext'>Enter the refresh time in seconds, to check whether a new order is received or not. </span></div></th><th><input type='number' min='0' step='1' name='inputForTime' value='" . esc_html($refreshTime) . "'></th></tr>";
    // MP3 Url Selection
    $content .= "<tr><th><div class='tooltip'>MP3 File URL: <span class='tooltiptext'>You can upload any .mp3 file using Media section in admin panel then copy the file URL and paste here to use it as alert media. </span></div></th><th><input type='text' name='inputForMp3' value='" . esc_html($musicUrlMp3) . "'></th></tr>";
    // Notification Header Selection
    $content .= "<tr><th><div class='tooltip'>Notification Header: <span class='tooltiptext'>Enter the header text that is shown in the notification popup. </span></div></th><th><input type='text' name='inputForHeader' value='" . esc_html($order_header) . "'></th></tr>";
    // Notification Text Selection
    $content .= "<tr><th><div class='tooltip'>Notification Text: <span class='tooltiptext'>Enter the text that is shown in the notification popup just before the Order ID.</span></div></th><th><input type='text' name='inputForText' value='" . esc_html($order_text) . "'></th></tr>";
    // Confirmation Text Selection
    $content .= "<tr><th><div class='tooltip'>Confirmation Text: <span class='tooltiptext'>Enter the confirmation text which closes the notification popup.</span></div></th><th><input type='text' name='inputForConfirm' value='" . esc_html($confirm) . "'></th></tr>";
    // Warning Order Status Selection
    $content .= "<tr><th><div class='tooltip'>Notification Order Statuses: <span class='tooltiptext'>The new order alert works for the selected order statuses from this option.</span></div></th><th>";
    $_order_status_name_index = 0;
    foreach ($order_status_keys as $order_status_key) {
        if (in_array($order_status_key, $order_statuses)) {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='" . esc_html($order_status_key) . "' checked>" . esc_html($order_status_values[$_order_status_name_index]) . "<br>";
        } else {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='" . esc_html($order_status_key) . "'>" . esc_html($order_status_values[$_order_status_name_index]) . "<br>";
        }
        $_order_status_name_index++;
    }
    // Role Selection
    $content .= "<tr><th><div class='tooltip'>New Order Notification Page Roles: <span class='tooltiptext'>Select the user roles which can access the New Order Notification Page.</span></div></th><th><select name='inputForRoles[]' multiple><option value='' disabled selected></option>";
    $index = 0;
    foreach ($roles as $role) {
        $roleValue = $roleValues[$index];
        $roleName = $role['name'];
        if (is_array($user_roles) && count($user_roles) && count($roles) != count($user_roles)) {
            if (!in_array($roleValue, $user_roles)) {
                $content .= "<option value='" . esc_html($roleValue) . "'>" . esc_html($roleName) . "</option>";
            }
        } else {
            $content .= "<option value='" . esc_html($roleValue) . "'>" . esc_html($roleName) . "</option>";
        }
        $index++;
    }
    $content .= "</select></th></tr>";
    // Show Number of Orders Selection
    $content .= "<tr><th><div class='tooltip'>Number of Orders: <span class='tooltiptext'>Enter the number of orders to display in the New Order Notification Order Table. </span></div></th><th><input type='number' min='0' max='100' step='1' name='inputForOrderNum' value='" . esc_html($show_order_num) . "'></th></tr>";
    // Showing Order Status Selection
    $content .= "<tr><th><div class='tooltip'>List Order Statuses: <span class='tooltiptext'>The recent orders table works for the selected order statuses from this option.</span></div></th><th>";
    $show_order_status_name_index = 0;
    foreach ($order_status_keys as $order_status_key) {
        if (in_array($order_status_key, $show_order_statuses)) {
            $content .= "<input type='checkbox' name='inputForShowStatuses[]' value='" . esc_html($order_status_key) . "' checked>" . esc_html($order_status_values[$show_order_status_name_index]) . "<br>";
        } else {
            $content .= "<input type='checkbox' name='inputForShowStatuses[]' value='" . esc_html($order_status_key) . "'>" . esc_html($order_status_values[$show_order_status_name_index]) . "<br>";
        }
        $show_order_status_name_index++;
    }

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
    $content .= "<div class='popup'><div class='cnt223'><h1>" . esc_html($order_header) . "</h1><p>" . esc_html($order_text) . " <a href='#' target='_blank'>X</a><br/><br/><a href='' id='closePreview'>" . esc_html($confirm) . "</a></p></div></div>";

    $productLoop = 0;
    if (is_array($product_ids) && count($product_ids) && count($allProductIds) != count($product_ids)) {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Alert for Products with IDs:</span></th></tr>";
        while ($productLoop < count($product_ids)) {
            $content .= "<tr><th><input type='checkbox' value='" . esc_html($product_ids[$productLoop]) . "' name='selectProductId[]'>" . esc_html($product_ids[$productLoop]) . "</th></tr>";
            $productLoop++;
        }
        $content .= wp_nonce_field('notification_settings_form_2', 'nonce_of_notificationSettingsForm_2');
        $content .= "<tr><th><input type='submit' value='Remove Selected IDs' name='removeIds'></th></tr>";
        $content .= "</form></table></div>";
    } else {
        $content .= "<div class='settings-area-id'>";
        $content .= "<table id='settings-new-order-notification'>";
        $content .= "<tr><th><h4>No Product IDs provided, all products are included.</h4></th></tr>";
        $content .= "</table></div>";
    }

    if (is_array($user_roles) && count($user_roles) && count($roles) != count($user_roles)) {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Permitted User Roles:</span></th></tr>";
        $roleLoop = 0;
        while ($roleLoop < count($user_roles)) {
            $content .= "<tr><th><input type='checkbox' value='" . esc_html($user_roles[$roleLoop]) . "' name='selectUserRole[]'>" . esc_html($user_roles[$roleLoop]) . "</th></tr>";
            $roleLoop++;
        }
        $content .= wp_nonce_field('notification_settings_form_3', 'nonce_of_notificationSettingsForm_3');
        $content .= "<tr><th><input type='submit' value='Remove Selected User Roles' name='removeUserRoles'></th></tr>";
        $content .= "</form></table></div>";
    } else {
        $content .= "<div class='settings-area-id'>";
        $content .= "<table id='settings-new-order-notification'>";
        $content .= "<tr><th><h4>No User Roles provided, all user roles are permitted.</h4></th></tr>";
        $content .= "</table></div>";
    }

    if (isset($_POST['nonce_of_notificationSettingsForm'])) {
        if (wp_verify_nonce($_POST['nonce_of_notificationSettingsForm'], 'notification_settings_form') && isset($_POST['saveSettings'])) {
            $count_products = count($product_ids);
            $count_roles = count($user_roles);
            // refresh time selection post
            if (isset($_POST['inputForTime']) && !empty($_POST['inputForTime'])) {
                $refreshTime = sanitize_text_field($_POST['inputForTime']);
            }
            // mp3 url selection post
            if (isset($_POST['inputForMp3']) && !empty($_POST['inputForMp3'])) {
                $musicUrlMp3 = sanitize_text_field($_POST['inputForMp3']);
            }
            // header text selection post
            if (isset($_POST['inputForHeader']) && !empty($_POST['inputForHeader'])) {
                $order_header = sanitize_text_field($_POST['inputForHeader']);
            }
            // popup text selection post
            if (isset($_POST['inputForText']) && !empty($_POST['inputForText'])) {
                $order_text = sanitize_text_field($_POST['inputForText']);
            }
            // confirm text selection post
            if (isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm'])) {
                $confirm = sanitize_text_field($_POST['inputForConfirm']);
            }
            // warn for order statuses selection post
            if (isset($_POST['inputForStatuses']) && !empty($_POST['inputForStatuses'])) {
                $order_statuses = $_POST['inputForStatuses'];
            }
            // product id selection post
            if (isset($_POST['inputForProductIds']) && !empty($_POST['inputForProductIds'])) {
                $index = 0;
                $selected_product_ids = array();
                foreach ($_POST['inputForProductIds'] as $inputForProductId) {
                    $selected_product_ids[$index] = sanitize_text_field($inputForProductId);
                    $index++;
                }
                if (count($allProductIds) == $count_products) {
                    $product_ids = $selected_product_ids;
                } else {
                    $product_ids = array_unique(array_merge($product_ids, $selected_product_ids));
                }
            }
            // role selection post
            if (isset($_POST['inputForRoles']) && !empty($_POST['inputForRoles'])) {
                $index = 0;
                $selected_user_roles = array();
                foreach ($_POST['inputForRoles'] as $inputForRole) {
                    $selected_user_roles[$index] = sanitize_text_field($inputForRole);
                    $index++;
                }
                if (count($roleValues) == $count_roles) {
                    $user_roles = $selected_user_roles;
                } else {
                    $user_roles = array_unique(array_merge($user_roles, $selected_user_roles));
                }
            }
            // number of orders selection post
            if (isset($_POST['inputForOrderNum']) && !empty($_POST['inputForOrderNum'])) {
                if ($_POST['inputForOrderNum'] > 0 && $_POST['inputForOrderNum'] < 101) {
                    $show_order_num = sanitize_text_field($_POST['inputForOrderNum']);
                } else {
                    $show_order_num = 20;
                }
            }
            // show for order statuses selection post
            if (isset($_POST['inputForShowStatuses']) && !empty($_POST['inputForShowStatuses'])) {
                $show_order_statuses = $_POST['inputForShowStatuses'];
            }

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $order_header,
                'order_text' => $order_text,
                'confirm' => $confirm,
                'statuses' => $order_statuses,
                'product_ids' => $product_ids,
                'user_roles' => $user_roles,
                'show_order_num' => $show_order_num,
                'show_order_statuses' => $show_order_statuses
            ));
            header("Refresh:0");
        }
        if (isset($_POST['resetSettings'])) {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
            $refreshTime = 30;
            $order_header = "Order Notification - New Order";
            $order_text = "Check Order Details: ";
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
            $product_ids = get_posts(array(
                'posts_per_page' => -1,
                'post_type' => array('product', 'product_variation'),
                'fields' => 'ids',
            ));
            global $wp_roles;
            $roles = $wp_roles->roles;
            $roleValues = array_keys($roles);
            $show_order_num = 20;

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $order_header,
                'order_text' => $order_text,
                'confirm' => $confirm,
                'statuses' => $order_status_keys,
                'product_ids' => $product_ids,
                'user_roles' => $roleValues,
                'show_order_num' => $show_order_num,
                'show_order_statuses' => $order_status_keys
            ));
            header("Refresh:0");
        }
    }

    if (isset($_POST['nonce_of_notificationSettingsForm_2'])) {
        if (wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_2'], 'notification_settings_form_2') && isset($_POST['selectProductId']) && !empty($_POST['selectProductId'])) {
            foreach ($_POST['selectProductId'] as $checkedBox) {
                if (($key = array_search($checkedBox, $product_ids)) !== false) {
                    unset($product_ids[$key]);
                }
            }

            $product_ids = array_values($product_ids);

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $order_header,
                'order_text' => $order_text,
                'confirm' => $confirm,
                'statuses' => $order_statuses,
                'product_ids' => $product_ids,
                'user_roles' => $user_roles,
                'show_order_num' => $show_order_num,
                'show_order_statuses' => $show_order_statuses
            ));
            header("Refresh:0");
        }
    }

    if (isset($_POST['nonce_of_notificationSettingsForm_3'])) {
        if (wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_3'], 'notification_settings_form_3') && isset($_POST['selectUserRole']) && !empty($_POST['selectUserRole'])) {
            foreach ($_POST['selectUserRole'] as $checkedBox) {
                if (($key = array_search($checkedBox, $user_roles)) !== false) {
                    unset($user_roles[$key]);
                }
            }

            $user_roles = array_values($user_roles);

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $order_header,
                'order_text' => $order_text,
                'confirm' => $confirm,
                'statuses' => $order_statuses,
                'product_ids' => $product_ids,
                'user_roles' => $user_roles,
                'show_order_num' => $show_order_num,
                'show_order_statuses' => $show_order_statuses
            ));
            header("Refresh:0");
        }
    }
    echo $content;
}

?>
