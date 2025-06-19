<?php

function new_order_notification_settings()
{
    $allProductIds = wc_get_products([
        'limit' => -1,
        'return' => 'ids',
        'type' => ['simple', 'variable'],
        'status' => 'publish',
    ]);

    global $wp_roles;
    $roles = $wp_roles->roles;
    $roleValues = array_keys($roles);

    $orderStatusMap = wc_get_order_statuses();
    $orderStatusKeys = array_keys($orderStatusMap);
    $orderStatusValues = array_values($orderStatusMap);

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
            $orderHeader = $options['order_header'];
        } else {
            $orderHeader = "Order Notification - New Order";
        }
        if ($options['order_text']) {
            $orderText = $options['order_text'];
        } else {
            $orderText = "Check Order Details: ";
        }
        if ($options['confirm']) {
            $confirm = $options['confirm'];
        } else {
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        }
        if ($options['statuses']) {
            $orderStatuses = $options['statuses'];
        } else {
            $orderStatuses = $orderStatusKeys;
        }
        if ($options['product_ids']) {
            $productIds = $options['product_ids'];
        } else {
            $productIds = $allProductIds;
        }
        if ($options['user_roles']) {
            $userRoles = $options['user_roles'];
        } else {
            $userRoles = $roleValues;
        }
        if ($options['show_order_num']) {
            $showOrderNum = $options['show_order_num'];
        } else {
            $showOrderNum = 20;
        }
        if ($options['show_order_statuses']) {
            $showOrderStatuses = $options['show_order_statuses'];
        } else {
            $showOrderStatuses = $orderStatusKeys;
        }
        update_option('__new_order_option', array(
            'refresh_time' => $refreshTime,
            'mp3_url' => $musicUrlMp3,
            'order_header' => $orderHeader,
            'order_text' => $orderText,
            'confirm' => $confirm,
            'statuses' => $orderStatuses,
            'product_ids' => $productIds,
            'user_roles' => $userRoles,
            'show_order_num' => $showOrderNum,
            'show_order_statuses' => $showOrderStatuses
        ));

    } else {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
        $refreshTime = 30;
        $orderHeader = "Order Notification - New Order";
        $orderText = "Check Order Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        $orderStatuses = $orderStatusKeys;
        $productIds = $allProductIds;
        $userRoles = $roleValues;
        $showOrderNum = 20;
        $showOrderStatuses = $orderStatusKeys;

        add_option('__new_order_option', array(
            'refresh_time' => $refreshTime,
            'mp3_url' => $musicUrlMp3,
            'order_header' => $orderHeader,
            'order_text' => $orderText,
            'confirm' => $confirm,
            'statuses' => $orderStatuses,
            'product_ids' => $productIds,
            'user_roles' => $userRoles,
            'show_order_num' => $showOrderNum,
            'show_order_statuses' => $showOrderStatuses
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
        if (is_array($productIds) && count($productIds) && count($allProductIds) != count($productIds)) {
            if (!in_array($productId, $productIds)) {
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
    $content .= "<tr><th><div class='tooltip'>Notification Header: <span class='tooltiptext'>Enter the header text that is shown in the notification popup. </span></div></th><th><input type='text' name='inputForHeader' value='" . esc_html($orderHeader) . "'></th></tr>";
    // Notification Text Selection
    $content .= "<tr><th><div class='tooltip'>Notification Text: <span class='tooltiptext'>Enter the text that is shown in the notification popup just before the Order ID.</span></div></th><th><input type='text' name='inputForText' value='" . esc_html($orderText) . "'></th></tr>";
    // Confirmation Text Selection
    $content .= "<tr><th><div class='tooltip'>Confirmation Text: <span class='tooltiptext'>Enter the confirmation text which closes the notification popup.</span></div></th><th><input type='text' name='inputForConfirm' value='" . esc_html($confirm) . "'></th></tr>";
    // Warning Order Status Selection
    $content .= "<tr><th><div class='tooltip'>Notification Order Statuses: <span class='tooltiptext'>The new order alert works for the selected order statuses from this option.</span></div></th><th>";
    $OrderStatusNameIndex = 0;
    foreach ($orderStatusKeys as $orderStatusKey) {
        if (in_array($orderStatusKey, $orderStatuses)) {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='" . esc_html($orderStatusKey) . "' checked>" . esc_html($orderStatusValues[$OrderStatusNameIndex]) . "<br>";
        } else {
            $content .= "<input type='checkbox' name='inputForStatuses[]' value='" . esc_html($orderStatusKey) . "'>" . esc_html($orderStatusValues[$OrderStatusNameIndex]) . "<br>";
        }
        $OrderStatusNameIndex++;
    }
    // Role Selection
    $content .= "<tr><th><div class='tooltip'>New Order Notification Page Roles: <span class='tooltiptext'>Select the user roles which can access the New Order Notification Page.</span></div></th><th><select name='inputForRoles[]' multiple><option value='' disabled selected></option>";
    $index = 0;
    foreach ($roles as $role) {
        $roleValue = $roleValues[$index];
        $roleName = $role['name'];
        if (is_array($userRoles) && count($userRoles) && count($roles) != count($userRoles)) {
            if (!in_array($roleValue, $userRoles)) {
                $content .= "<option value='" . esc_html($roleValue) . "'>" . esc_html($roleName) . "</option>";
            }
        } else {
            $content .= "<option value='" . esc_html($roleValue) . "'>" . esc_html($roleName) . "</option>";
        }
        $index++;
    }
    $content .= "</select></th></tr>";
    // Show Number of Orders Selection
    $content .= "<tr><th><div class='tooltip'>Number of Orders: <span class='tooltiptext'>Enter the number of orders to display in the New Order Notification Order Table. </span></div></th><th><input type='number' min='0' max='100' step='1' name='inputForOrderNum' value='" . esc_html($showOrderNum) . "'></th></tr>";
    // Showing Order Status Selection
    $content .= "<tr><th><div class='tooltip'>List Order Statuses: <span class='tooltiptext'>The recent orders table works for the selected order statuses from this option.</span></div></th><th>";
    $showOrderStatusNameIndex = 0;
    foreach ($orderStatusKeys as $orderStatusKey) {
        if (in_array($orderStatusKey, $showOrderStatuses)) {
            $content .= "<input type='checkbox' name='inputForShowStatuses[]' value='" . esc_html($orderStatusKey) . "' checked>" . esc_html($orderStatusValues[$showOrderStatusNameIndex]) . "<br>";
        } else {
            $content .= "<input type='checkbox' name='inputForShowStatuses[]' value='" . esc_html($orderStatusKey) . "'>" . esc_html($orderStatusValues[$showOrderStatusNameIndex]) . "<br>";
        }
        $showOrderStatusNameIndex++;
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
    $content .= "<div class='popup'><div class='cnt223'><h1>" . esc_html($orderHeader) . "</h1><p>" . esc_html($orderText) . " <a href='#' target='_blank'>X</a><br/><br/><a href='' id='closePreview'>" . esc_html($confirm) . "</a></p></div></div>";

    $productLoop = 0;
    if (is_array($productIds) && count($productIds) && count($allProductIds) != count($productIds)) {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Alert for Products with IDs:</span></th></tr>";
        while ($productLoop < count($productIds)) {
            $content .= "<tr><th><input type='checkbox' value='" . esc_html($productIds[$productLoop]) . "' name='selectProductId[]'>" . esc_html($productIds[$productLoop]) . "</th></tr>";
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

    if (is_array($userRoles) && count($userRoles) && count($roles) != count($userRoles)) {
        $content .= "<div class='settings-area'>";
        $content .= "<table id='settings-new-order-notification'><form action='' method='post' id='notificationSettingsForm'> ";
        $content .= "<tr><th><span style='font-size:18px'>Permitted User Roles:</span></th></tr>";
        $roleLoop = 0;
        while ($roleLoop < count($userRoles)) {
            $content .= "<tr><th><input type='checkbox' value='" . esc_html($userRoles[$roleLoop]) . "' name='selectUserRole[]'>" . esc_html($userRoles[$roleLoop]) . "</th></tr>";
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
            $countProducts = count($productIds);
            $countRoles = count($userRoles);
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
                $orderHeader = sanitize_text_field($_POST['inputForHeader']);
            }
            // popup text selection post
            if (isset($_POST['inputForText']) && !empty($_POST['inputForText'])) {
                $orderText = sanitize_text_field($_POST['inputForText']);
            }
            // confirm text selection post
            if (isset($_POST['inputForConfirm']) && !empty($_POST['inputForConfirm'])) {
                $confirm = sanitize_text_field($_POST['inputForConfirm']);
            }
            // warn for order statuses selection post
            if (isset($_POST['inputForStatuses']) && !empty($_POST['inputForStatuses'])) {
                $orderStatuses = $_POST['inputForStatuses'];
            }
            // product id selection post
            if (isset($_POST['inputForProductIds']) && !empty($_POST['inputForProductIds'])) {
                $index = 0;
                $selectedProductIds = array();
                foreach ($_POST['inputForProductIds'] as $inputForProductId) {
                    $selectedProductIds[$index] = sanitize_text_field($inputForProductId);
                    $index++;
                }
                if (count($allProductIds) == $countProducts) {
                    $productIds = $selectedProductIds;
                } else {
                    $productIds = array_unique(array_merge($productIds, $selectedProductIds));
                }
            }
            // role selection post
            if (isset($_POST['inputForRoles']) && !empty($_POST['inputForRoles'])) {
                $index = 0;
                $selectedUserRoles = array();
                foreach ($_POST['inputForRoles'] as $inputForRole) {
                    $selectedUserRoles[$index] = sanitize_text_field($inputForRole);
                    $index++;
                }
                if (count($roleValues) == $countRoles) {
                    $userRoles = $selectedUserRoles;
                } else {
                    $userRoles = array_unique(array_merge($userRoles, $selectedUserRoles));
                }
            }
            // number of orders selection post
            if (isset($_POST['inputForOrderNum']) && !empty($_POST['inputForOrderNum'])) {
                if ($_POST['inputForOrderNum'] > 0 && $_POST['inputForOrderNum'] < 101) {
                    $showOrderNum = sanitize_text_field($_POST['inputForOrderNum']);
                } else {
                    $showOrderNum = 20;
                }
            }
            // show for order statuses selection post
            if (isset($_POST['inputForShowStatuses']) && !empty($_POST['inputForShowStatuses'])) {
                $showOrderStatuses = $_POST['inputForShowStatuses'];
            }

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $orderHeader,
                'order_text' => $orderText,
                'confirm' => $confirm,
                'statuses' => $orderStatuses,
                'product_ids' => $productIds,
                'user_roles' => $userRoles,
                'show_order_num' => $showOrderNum,
                'show_order_statuses' => $showOrderStatuses
            ));
            header("Refresh:0");
        }
        if (isset($_POST['resetSettings'])) {
            $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
            $refreshTime = 30;
            $orderHeader = "Order Notification - New Order";
            $orderText = "Check Order Details: ";
            $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
            $productIds = wc_get_products([
                'limit' => -1,
                'return' => 'ids',
                'type' => ['simple', 'variable'],
                'status' => 'publish',
            ]);
            global $wp_roles;
            $roles = $wp_roles->roles;
            $roleValues = array_keys($roles);
            $showOrderNum = 20;

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $orderHeader,
                'order_text' => $orderText,
                'confirm' => $confirm,
                'statuses' => $orderStatusKeys,
                'product_ids' => $productIds,
                'user_roles' => $roleValues,
                'show_order_num' => $showOrderNum,
                'show_order_statuses' => $orderStatusKeys
            ));
            header("Refresh:0");
        }
    }

    if (isset($_POST['nonce_of_notificationSettingsForm_2'])) {
        if (wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_2'], 'notification_settings_form_2') && isset($_POST['selectProductId']) && !empty($_POST['selectProductId'])) {
            foreach ($_POST['selectProductId'] as $checkedBox) {
                if (($key = array_search($checkedBox, $productIds)) !== false) {
                    unset($productIds[$key]);
                }
            }

            $productIds = array_values($productIds);

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $orderHeader,
                'order_text' => $orderText,
                'confirm' => $confirm,
                'statuses' => $orderStatuses,
                'product_ids' => $productIds,
                'user_roles' => $userRoles,
                'show_order_num' => $showOrderNum,
                'show_order_statuses' => $showOrderStatuses
            ));
            header("Refresh:0");
        }
    }

    if (isset($_POST['nonce_of_notificationSettingsForm_3'])) {
        if (wp_verify_nonce($_POST['nonce_of_notificationSettingsForm_3'], 'notification_settings_form_3') && isset($_POST['selectUserRole']) && !empty($_POST['selectUserRole'])) {
            foreach ($_POST['selectUserRole'] as $checkedBox) {
                if (($key = array_search($checkedBox, $userRoles)) !== false) {
                    unset($userRoles[$key]);
                }
            }

            $userRoles = array_values($userRoles);

            update_option('__new_order_option', array(
                'refresh_time' => $refreshTime,
                'mp3_url' => $musicUrlMp3,
                'order_header' => $orderHeader,
                'order_text' => $orderText,
                'confirm' => $confirm,
                'statuses' => $orderStatuses,
                'product_ids' => $productIds,
                'user_roles' => $userRoles,
                'show_order_num' => $showOrderNum,
                'show_order_statuses' => $showOrderStatuses
            ));
            header("Refresh:0");
        }
    }
    echo $content;
}

?>
