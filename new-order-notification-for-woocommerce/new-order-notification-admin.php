<?php

add_action('woocommerce_checkout_order_processed', 'detect_new_order_on_checkout');

function detect_new_order_on_checkout($orderId)
{
    $options = get_option('_new_order_id_for_notification');
    if (!$options) {
        add_option('_new_order_id_for_notification', array(
            'order_id' => $orderId
        ));
    } else {
        update_option('_new_order_id_for_notification', array(
            'order_id' => $orderId
        ));
    }
}

add_action('admin_menu', 'new_order_notification');

function new_order_notification()
{
    add_menu_page(
        'New Order Notification',
        'New Order Notification',
        'delete_posts',
        'new_order_notification',
        'new_order_notification_V2',
        'dashicons-warning',
        '54'
    );

    add_submenu_page(
        'new_order_notification',
        'Support',
        'Support',
        'delete_posts',
        'new_order_notification_support',
        'new_order_notification_support'
    );
}


function new_order_notification_menu()
{
    $isNew = false;
    $allProductIds = wc_get_products([
        'limit' => -1,
        'return' => 'ids',
        'type' => ['simple', 'variable'],
        'status' => 'publish',
    ]);
    global $wp_roles;
    $roles = $wp_roles->roles;
    $roleValues = array_keys($roles);
    $allUserRoles = $roleValues;
    $orderStatusMap = wc_get_order_statuses();
    $orderStatusKeys = array_keys($orderStatusMap);

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
            $userRoles = $allUserRoles;
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

        $user = wp_get_current_user();
        $isRestrictedUserRole = true;
        if (is_array($userRoles) && count($userRoles)) {
            foreach ($userRoles as $userRole) {
                if (in_array($userRole, $user->roles)) {
                    $isRestrictedUserRole = false;
                }
            }
        } else {
            $isRestrictedUserRole = false;
        }
        if ($isRestrictedUserRole) {
            echo "<br><br><h2>You don't have permission to see New Order Notification page.</h2>";
            return;
        }
    } else {
        $musicUrlMp3 = plugins_url('assets/order-music.mp3', __FILE__);
        $refreshTime = 30;
        $orderHeader = "Order Notification - New Order";
        $orderText = "Check Order Details: ";
        $confirm = "ACKNOWLEDGE THIS NOTIFICATION";
        $orderStatuses = $orderStatusKeys;
        $productIds = $allProductIds;
        $userRoles = $allUserRoles;
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

    $checkOrders = wc_get_orders(array('status' => $orderStatusKeys));
    $numberOfOrders = 0;
    if (is_array($checkOrders)) {
        $numberOfOrders = count($checkOrders);
    }

    if ($numberOfOrders == 0) {
        echo "<h1>You have not received any orders yet.<br><br>This page will be refreshed for every 5 seconds to check if your first order is received.</h1>";
        header("Refresh: 5");
        return;
    } else {
        $anyNewOrder = get_option('_new_order_id_for_notification');
        if ($anyNewOrder) {
            $alertForThisProduct = false;
            $isAllProducts = true;
            if (is_array($productIds) && count($productIds) != 0) {
                $isAllProducts = false;
            }

            $lastOrderId = $anyNewOrder['order_id'];
            $lastOrder = wc_get_order($lastOrderId);
            if (!$isAllProducts) {
                foreach ($lastOrder->get_items() as $itemId => $item) {
                    $productId = $item->get_product_id();
                    $variationId = $item->get_variation_id();
                    if (in_array($productId, $productIds) || in_array($variationId, $productIds)) {
                        $alertForThisProduct = true;
                    }
                }
            }

            $statusPrefix = "wc-";
            $lastOrderStatus = $lastOrder->get_status();
            $lastOrderStatus = $statusPrefix . $lastOrderStatus;

            if (in_array($lastOrderStatus, $orderStatuses) && ($isAllProducts || $alertForThisProduct)) {
                $isNew = true;
            }

            $websiteUrl = get_site_url();
            $websiteUrl .= "/wp-admin/post.php?post=";
            $websiteUrl .= $lastOrderId;
            $websiteUrl .= "&action=edit";

            if ($isNew) {
                ?>
                <script type='text/javascript'>
                    window.focus();
                    jQuery(function ($) {
                        var overlay = $('<div id="overlay"></div>');
                        overlay.show();
                        overlay.appendTo(document.body);
                        $('.popup').show();
                        $('.close').click(function () {
                            $('.popup').hide();
                            overlay.appendTo(document.body).remove();
                            location.reload();
                            return false;
                        });

                        $('.x').click(function () {
                            $('.popup').hide();
                            overlay.appendTo(document.body).remove();
                            return false;
                        });
                    });
                </script>
                <?php
                $audiocontent = "<audio controls autoplay loop><source src='" . esc_html($musicUrlMp3) . "' type='audio/mpeg'>Your browser does not support the audio element.</audio>";
                echo $audiocontent;
                $popupcontent = "<div class='popup'><div class='cnt223'><h1>" . esc_html($orderHeader) . "</h1><p>" . esc_html($orderText) . " <a href='" . esc_html($websiteUrl) . "' target='_blank'>" . esc_html($lastOrderId) . "</a><br/><br/><a href='' class='close'>" . esc_html($confirm) . "</a></p></div></div>";
                echo $popupcontent;
                delete_option('_new_order_id_for_notification');
            }
        }
    }

    $recentOrders = wc_get_orders(array(
        'limit' => $showOrderNum,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => $showOrderStatuses,
    ));

    $content = "<h1>New Order Notification for Woocommerce</h1>";
    $content .= "<table id='customers-new-order-notification'>";
    $content .= "<tr><th>Recent Orders</th></tr>";
    $content .= "<tr><th>Order ID</th><th>Order Date</th><th>Order Status</th><th>Order Details</th></tr>";

    foreach ($recentOrders as $recentOrder) {
        $orderId = $recentOrder->get_id();
        $Order = wc_get_order($orderId);
        $orderDate = $Order->get_date_created();
        $orderStatus = $recentOrder->get_status();
        $orderLink = get_site_url();
        $orderLink .= "/wp-admin/post.php?post=";
        $orderLink .= $orderId;
        $orderLink .= "&action=edit";


        $statusPrefix = "wc-";
        $Orderstatus = $statusPrefix . $orderStatus;
        $OrderStatus = $orderStatusMap[$Orderstatus];

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        $formatOrderDate = $timeFormat . " - " . $dateFormat;

        $content .= "<tr><td>" . esc_html($orderId) . "</td><td>" . esc_html($orderDate->date($formatOrderDate)) . "</td><td>" . esc_html($OrderStatus) . "</td><td><a href='" . esc_html($orderLink) . "' target='_blank'>Order " . esc_html($orderId) . "</a></td></tr>";
    }

    $content .= "</table><br><hr>";

    if (!$isNew) {
        delete_option('_new_order_id_for_notification');
        $time = $refreshTime;
        header("Refresh:" . esc_html($time) . "");
    }

    $content .= "<br><br><div class='main-info-bottom'><p>1 - To be warned when a new order received, keep this page opened in your browser.</p><p>2 - Recent Order Table gets Timezone and Date Format settings from Settings -> General.</p>";
    $content .= "<p>3 - You can test audio alert: </p><audio controls loop style='display: block'><source src='" . esc_html($options['mp3_url']) . "' type='audio/mpeg'>Your browser does not support the audio element.</audio></div>";
    echo $content;
}
