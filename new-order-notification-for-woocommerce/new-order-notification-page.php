<?php

add_action('woocommerce_checkout_order_processed', 'detect_new_order_on_checkout_v2');

function detect_new_order_on_checkout_v2($order_id)
{
    $options = get_option('_order_id_for_new_order_notification');
    if (!$options) {
        add_option('_order_id_for_new_order_notification', array(
            'order_id' => $order_id
        ));
    } else {
        update_option('_order_id_for_new_order_notification', array(
            'order_id' => $order_id
        ));
    }
}

function getNewOrderNotificationSettings()
{
    global $wp_roles;
    $wcOrderStatuses = array_keys(wc_get_order_statuses());
    //
    $musicUrl = plugins_url('assets/order-music.mp3', __FILE__);
    $refreshTime = 30;
    $popupTitle = "Order Notification - New Order";
    $popupHeader = "Check Order Details: ";
    $popupConfirmation = "ACKNOWLEDGE NOTIFICATION";
    $orderStatuses = $wcOrderStatuses;
    $productIds = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => array('product', 'product_variation'),
        'fields' => 'ids',
    ));
    $userRoles = array_keys($wp_roles->roles);
    $recentOrderTableLimit = 20;
    $recentOrderTableStatuses = $wcOrderStatuses;
    //
    $options = get_option('_non_v2_alert_options');
    if ($options) {
        if ($options['mp3_url']) {
            $musicUrl = $options['mp3_url'];
        }
        if ($options['refresh_time']) {
            $refreshTime = $options['refresh_time'];
        }
        if ($options['order_header']) {
            $popupTitle = $options['order_header'];
        }
        if ($options['order_text']) {
            $popupHeader = $options['order_text'];
        }
        if ($options['confirm']) {
            $popupConfirmation = $options['confirm'];
        }
        if ($options['statuses']) {
            $orderStatuses = $options['statuses'];
        }
        if ($options['product_ids']) {
            $productIds = $options['product_ids'];
        }
        if ($options['user_roles']) {
            $userRoles = $options['user_roles'];
        }
        if ($options['show_order_num']) {
            $recentOrderTableLimit = $options['show_order_num'];
        }
        if ($options['show_order_statuses']) {
            $recentOrderTableStatuses = $options['show_order_statuses'];
        }
        //
        update_option('_non_v2_alert_options', array(
            'mp3_url' => $musicUrl,
            'refresh_time' => $refreshTime,
            'order_header' => $popupTitle,
            'order_text' => $popupHeader,
            'confirm' => $popupConfirmation,
            'statuses' => $orderStatuses,
            'product_ids' => $productIds,
            'user_roles' => $userRoles,
            'show_order_num' => $recentOrderTableLimit,
            'show_order_statuses' => $recentOrderTableStatuses
        ));
    } else {
        add_option('_non_v2_alert_options', array(
            'mp3_url' => $musicUrl,
            'refresh_time' => $refreshTime,
            'order_header' => $popupTitle,
            'order_text' => $popupHeader,
            'confirm' => $popupConfirmation,
            'statuses' => $orderStatuses,
            'product_ids' => $productIds,
            'user_roles' => $userRoles,
            'show_order_num' => $recentOrderTableLimit,
            'show_order_statuses' => $recentOrderTableStatuses
        ));
    }
    return get_option('_non_v2_alert_options');
}

function checkIfUserRestricted($userRoles)
{
    $user = wp_get_current_user();
    $isRestrictedUserRole = true;
    if (is_array($userRoles) && count($userRoles) > 0) {
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
    }
    return $isRestrictedUserRole;
}

function getRecentOrderTable($orders)
{
    $pageTitle = "New Order Notification for Woocommerce";
    $columnOrderNo = "Order No";
    $columnOrderDate = "Order Date";
    $columnOrderStatus = "Order Status";
    $columnOrderEdit = "Edit Order";
    //
    $options = get_option('_non_v2_order_table_options');
    //
    if ($options) {
        if ($options['page_title']) {
            $pageTitle = $options['page_title'];
        }
        if ($options['column_order_no']) {
            $columnOrderNo = $options['column_order_no'];
        }
        if ($options['column_order_date']) {
            $columnOrderDate = $options['column_order_date'];
        }
        if ($options['column_order_status']) {
            $columnOrderStatus = $options['column_order_status'];
        }
        if ($options['column_order_edit']) {
            $columnOrderEdit = $options['column_order_edit'];
        }
        //
        update_option('_non_v2_order_table_options', array(
            'page_title' => $pageTitle,
            'column_order_no' => $columnOrderNo,
            'column_order_date' => $columnOrderDate,
            'column_order_status' => $columnOrderStatus,
            'column_order_edit' => $columnOrderEdit,
        ));
    } else {
        //
        add_option('_non_v2_order_table_options', array(
            'page_title' => $pageTitle,
            'column_order_no' => $columnOrderNo,
            'column_order_date' => $columnOrderDate,
            'column_order_status' => $columnOrderStatus,
            'column_order_edit' => $columnOrderEdit,
        ));
    }
    //
    $content = "<h1>" . esc_html($pageTitle) . "</h1>";
    $content .= "<table id='customers-new-order-notification'>";
    $content .= "<tr>
                    <th>" . esc_html($columnOrderNo) . "</th>
                    <th>" . esc_html($columnOrderDate) . "</th>
                    <th>" . esc_html($columnOrderStatus) . "</th>
                    <th>" . esc_html($columnOrderEdit) . "</th>
                 </tr>";
    //
    foreach ($orders as $recent_order) {
        $orderId = $recent_order->get_id();
        $order = wc_get_order($orderId);
        $orderDate = $order->get_date_created();
        $orderLink = get_site_url() . "/wp-admin/post.php?post=" . $orderId . "&action=edit";
        $orderStatus = wc_get_order_statuses()["wc-" . $recent_order->get_status()];
        $orderNumber = $order->get_order_number();

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $formatter = $dateFormat . " - " . $timeFormat;

        $content .= "<tr>
                        <td>" . esc_html($orderNumber) . "</td>
                        <td>" . esc_html($orderDate->date($formatter)) . "</td>
                        <td>" . esc_html($orderStatus) . "</td>
                        <td>
                            <div style='display: flex; justify-content: space-evenly;'>
                                <button class='btn' type='submit' name='showOrderEditPopupButton' onclick='showOrderEditPopupButton(this.value)' value=" . esc_html($orderId) . ">
                                    <i class='fas fa-eye'></i>
                                </button>
                                <a href='" . esc_html($orderLink) . "' target='_blank'><i class='fas fa-link'></i></a>
                            </div>
                        </td>
                    </tr>";
        ?>
        <script type="text/javascript">
            var modal = document.getElementById("popupEditModal");
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            function showOrderEditPopupButton(orderId) {
                var data = {
                    'action': 'my_action_2',
                    'orderId': orderId
                };
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery(function ($) {
                        var editPopup = $(response);
                        editPopup.appendTo(document.body);
                        modal = document.getElementById("popupEditModal");
                        modal.style.display = "block";
                    });
                });
            }
        </script>
        <?php
    }
    return $content;
}

function showOrderEditPopup($orderId)
{
    $orderText = "Order";
    $billingText = "Billing Details";
    $shippingText = "Shipping Details";
    $emailText = "Email";
    $phoneText = "Phone";
    $noteText = "Customer Note";
    $paymentText = "Payment Details";
    $productText = "Product Details";
    //
    $productName = "Product";
    $productQuantity = "Quantity";
    $productTotal = "Total";

    $order = wc_get_order($orderId);

    $itemContent = "";
    foreach ($order->get_items() as $item_id => $item) {
        $name = $item->get_name();
        $quantity = $item->get_quantity();
        $total = $item->get_total();

        $itemContent .= "<table id='popup-new-order-notification'>
                            <tr>
                                <th>" . esc_html($productName) . "</th>
                                <th>" . esc_html($productQuantity) . "</th>
                                <th>" . esc_html($productTotal) . "</th>
                            </tr>
                            <tr>
                                <td>" . esc_html($name) . "</td>
                                <td>" . esc_html($quantity) . "</td>
                                <td>" . esc_html($total) . "</td>
                            </tr>
                        </table>";
    }

    $popupEditModal = "<div id='popupEditModal' class='popupEditModal'>
              <div class='popupEditContent'>
                  <div class='popupEditHeader'>
                        <mark class='popupEditStatus'>
                                <span class='popupEditStatusText'>" . esc_html($order->get_status()) . "</span>
                        </mark>
                        <h2>" . esc_html($orderText) . " #" . esc_html($orderId) . "</h2>
                  </div>
                  <div>
                        <div style='min-height: 200px;'>
                            <div style='width: 50%; float: left;'>
                                <h2 class='popupEditAddressHeader'>" . esc_html($billingText) . "</h2>
                                <strong>" . $order->get_formatted_billing_address() . "</strong>
                            </div>
                            <div style='width: 50%; float: right;'>
                                <h2 class='popupEditAddressHeader'>" . esc_html($shippingText) . "</h2>
                                <strong>" . $order->get_formatted_shipping_address() . "</strong>
                            </div>
                        </div>
                        <div style='min-height: 200px;'>
                            <div style='width: 50%; float: left;'>
                                <h2 class='popupEditAddressHeader'>" . esc_html($emailText) . "</h2>
                                <strong>" . esc_html($order->get_billing_email()) . "</strong>
                                <h2 class='popupEditAddressHeader'>" . esc_html($phoneText) . "</h2>
                                <strong>" . esc_html($order->get_billing_phone()) . "</strong>
                            </div>
                            <div style='width: 50%; float: right'>
                                <div>
                                    <h2 class='popupEditAddressHeader'>" . esc_html($noteText) . "</h2>
                                    <strong>" . esc_html($order->get_customer_note()) . "</strong>
                                </div>
                                <br/>
                                <h2 class='popupEditAddressHeader'>" . esc_html($paymentText) . "</h2>
                                <div style='width: 50%; float: left;'>
                                    <strong>" . esc_html($order->get_payment_method_title()) . "</strong>
                                </div>
                                <div style='width: 50%; float: right;'>
                                    <strong>" . $order->get_formatted_order_total() . "</strong>
                                </div> 
                            </div>
                        </div>
                        <div style='min-height: 200px;'>                            
                        <h2 class='popupEditAddressHeader'>" . esc_html($productText) . "</h2>
                            " . $itemContent . "
                        </div>
                  </div>
              </div>
          </div>";

    echo $popupEditModal;
}

add_action('wp_ajax_my_action_2', 'my_action_2');

function my_action_2()
{
    showOrderEditPopup($_POST['orderId']);
    wp_die();
}

function getRecentOrders($limit, $statuses)
{
    return wc_get_orders(array(
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => $statuses,
    ));
}

function checkNewOrder($settings)
{
    $anyNewOrder = get_option('_order_id_for_new_order_notification');
    if (!$anyNewOrder) {
        return;
    }
    $shouldAlert = false;
    // get new order
    $newOrder = wc_get_order($anyNewOrder['order_id']);
    // check product options
    $productIds = $settings['product_ids'];
    $alertForThisProduct = false;
    $isAllProducts = true;
    if (is_array($productIds) && count($productIds) != 0) {
        $isAllProducts = false;
    }
    if (!$isAllProducts) {
        foreach ($newOrder->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            if (in_array($product_id, $productIds) || in_array($variation_id, $productIds)) {
                $alertForThisProduct = true;
            }
        }
    }
    // check order status
    $orderStatuses = $settings['statuses'];
    $alertForThisStatus = false;
    $newOrderStatus = "wc-" . $newOrder->get_status();
    if (in_array($newOrderStatus, $orderStatuses)) {
        $alertForThisStatus = true;
    }
    // decide to show alert
    if ($alertForThisStatus && $alertForThisProduct) {
        $shouldAlert = true;
    }
    if ($shouldAlert) {
        // get popup variables
        $musicUrlMp3 = $settings['mp3_url'];
        $popupTitle = $settings['order_header'];
        $popupHeader = $settings['order_text'];
        $newOrderId = $newOrder->get_id();
        $orderEditLink = get_site_url() . "/wp-admin/post.php?post=" . $newOrderId . "&action=edit";
        $popupConfirmation = $settings['confirm'];
        //
        $audio = "<audio id='audioAlert' controls loop>
                      <source src='" . esc_html($musicUrlMp3) . "' type='audio/ogg'>
                      <source src='" . esc_html($musicUrlMp3) . "' type='audio/mpeg'>
                      Your browser does not support the audio element.
                  </audio>";
        $popupContent = "<div class='popup'><div class='cnt223'><h1>" . esc_html($popupTitle) . "</h1><p>" . esc_html($popupHeader) . " <a href='" . esc_html($orderEditLink) . "' target='_blank'>" . esc_html($newOrderId) . "</a><br/><br/><a href='' class='close'>" . esc_html($popupConfirmation) . "</a></p></div></div>";
        //
        echo $audio;
        echo $popupContent;
        echo "<script type='text/javascript'>
                    window.focus();
                    //
                    jQuery(function ($) {
                        var overlay = $('<div id=\"overlay\"></div>');
                        overlay.show();
                        var video = document.getElementById('audioAlert');
                        video.oncanplaythrough = function() {
                            video.play();
                        }
                        overlay.appendTo(document.body);
                        $('.popup').show();
                        $('.close').click(function () {
                            $('.popup').hide();
                            overlay.appendTo(document.body).remove();
                            video.pause();
                            return false;
                        });
                        $('.x').click(function () {
                            $('.popup').hide();
                            overlay.appendTo(document.body).remove();
                            video.pause();
                            return false;
                        });
                    });
                </script>";
        delete_option('_order_id_for_new_order_notification');
    }
}

add_action('new_order_notification', 'my_action_javascript'); // Write our JS below here

function my_action_javascript()
{
    ?>
    <script type="text/javascript">
        var data = {
            'action': 'my_action',
            'whatever': 1234
        };
        jQuery.post(ajaxurl, data, function (response) {
            alert('Got this from the server: ' + response);
        });
    </script>
    <?php
}

add_action('wp_ajax_my_action', 'my_action');

function my_action()
{
    $settings = getNewOrderNotificationSettings();
    checkNewOrder($settings);
    echo 10;
    wp_die(); // this is required to terminate immediately and return a proper response
}

function new_order_notification_v2()
{
    echo "This is beta version for New Order Notification v2.0. Settings page and new order detection is not ready for this version.";
    //
    $settings = getNewOrderNotificationSettings();
    $isRestricted = checkIfUserRestricted($settings['user_roles']);
    if ($isRestricted) {
        return;
    }
    // get recent orders
    $orders = getRecentOrders($settings['show_order_num'], $settings['statuses']);
    // display recent order table
    echo getRecentOrderTable($orders);
    // check new order and show popup
    checkNewOrder($settings);
}