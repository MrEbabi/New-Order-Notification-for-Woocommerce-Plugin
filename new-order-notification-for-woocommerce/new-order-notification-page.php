<?php

add_action('woocommerce_new_order', 'detect_new_order_on_checkout_v2', 10, 2);
add_action('woocommerce_checkout_order_created', 'detect_new_order_on_checkout_v2', 10, 1);
add_action('woocommerce_store_api_checkout_order_created', 'detect_new_order_on_checkout_v2', 10, 1);

function detect_new_order_on_checkout_v2($orderOrId, $maybeOrder = null)
{
    if ($orderOrId instanceof WC_Order) {
        $order = $orderOrId;
    } else {
        $order = $maybeOrder instanceof WC_Order ? $maybeOrder : wc_get_order($orderOrId);
    }
    if (!$order) {
        return;
    }
    $orderId = $order->get_id();
    update_option('_order_id_for_new_order_notification', $orderId);
}

function getNewOrderNotificationSettings()
{
    //
    $musicUrl = plugins_url('assets/order-music.mp3', __FILE__);
    $refreshTime = 30;
    $wcOrderStatuses = array_keys(wc_get_order_statuses());
    $orderStatuses = $wcOrderStatuses;
    $productIds = wc_get_products([
        'limit'  => -1,
        'return' => 'ids',
        'type'   => ['simple', 'variable'],
        'status' => 'publish',
    ]);
    global $wpRoles;
    $userRoles = array_keys($wpRoles->roles);
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
        echo "<br><br><h2>" . __("You don't have permission to see New Order Notification page.", 'new-order-notification-for-woocommerce') . "</h2>";
    }
    return $isRestrictedUserRole;
}

function getRecentOrderTable($settings)
{
    $orders = wc_get_orders(array(
        'limit' => $settings['show_order_num'],
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => $settings['statuses'],
        'type' => 'shop_order',
    ));

    //
    $content = "<table id='customers-new-order-notification'>";
    $content .= "<tr>
                    <th>" . __('Order ID', 'new-order-notification-for-woocommerce') . "</th>
                    <th>" . __('Date', 'new-order-notification-for-woocommerce') . "</th>
                    <th>" . __('Status', 'new-order-notification-for-woocommerce') . "</th>
                    <th>" . __('Preview/Edit', 'new-order-notification-for-woocommerce') . "</th>
                 </tr>";
    //
    foreach ($orders as $recentOrder) {
        $orderId = $recentOrder->get_id();
        $order = wc_get_order($orderId);
        $orderDate = $order->get_date_created();
        $orderLink = get_site_url() . "/wp-admin/post.php?post=" . $orderId . "&action=edit";
        $orderStatus = wc_get_order_statuses()["wc-" . strtolower($recentOrder->get_status())];
        $orderNumber = $order->get_order_number();

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $formatter = $dateFormat . " - " . $timeFormat;

        $content .= "<tr>
                        <td>" . esc_html($orderNumber) . "</td>
                        <td>" . esc_html($orderDate->date($formatter)) . "</td>
                        <td id='non-status-" . esc_html($orderId) . "'>" . esc_html($orderStatus) . "</td>
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
            window.onclick = function (event) {
                const modal = document.getElementById("popupEditModal");
                if (event.target == modal) {
                    modal.remove();
                    modal.style.display = "none";
                }
            }

            function showOrderEditPopupButton(orderId) {
                const data = {
                    'action': 'show_order_edit_popup_action',
                    'orderId': orderId,
                    'security': NewOrderNotif.nonce
                };
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery(function ($) {
                        const editPopup = $(response);
                        editPopup.appendTo(document.body);
                        const modal = document.getElementById("popupEditModal");
                        modal.style.display = "block";
                    });
                });
            }

            function orderEditStatus() {
                const orderId = document.getElementById('popupOrderId').value;
                const status = document.getElementById('popupStatus').value;
                const data = {
                    'action': 'order_edit_status_action',
                    'orderId': orderId,
                    'status': status,
                    'security': NewOrderNotif.nonce
                };
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery(function ($) {
                        const modal = document.getElementById("popupEditModal");
                        modal.remove();
                        const columnId = 'non-status-' + orderId;
                        const orderColumn = document.getElementById(columnId);
                        orderColumn.innerText = response;
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
    $order = wc_get_order($orderId);

    $itemContent = "";
    foreach ($order->get_items() as $itemId => $item) {
        $name = $item->get_name();
        $quantity = $item->get_quantity();
        $total = $item->get_total();

        $itemContent .= "<table id='popup-new-order-notification'>
                            <tr>
                                <th>" . __('Product', 'new-order-notification-for-woocommerce') . "</th>
                                <th>" . __('Quantity', 'new-order-notification-for-woocommerce') . "</th>
                                <th>" . __('Total', 'new-order-notification-for-woocommerce') . "</th>
                            </tr>
                            <tr>
                                <td>" . esc_html($name) . "</td>
                                <td>" . esc_html($quantity) . "</td>
                                <td>" . esc_html($total) . "</td>
                            </tr>
                        </table>";
    }

    $statusContent = "";
    $index = 0;
    $wcOrderStatuses = array_keys(wc_get_order_statuses());
    foreach (wc_get_order_statuses() as $orderStatus) {
        if (strtolower($orderStatus) == strtolower($order->get_status())) {
            $statusContent .= "<option selected disabled value='" . esc_html($wcOrderStatuses[$index]) . "'>" . esc_html($orderStatus) . "</option>";
        } else {
            $statusContent .= "<option value='" . esc_html($wcOrderStatuses[$index]) . "'>" . esc_html($orderStatus) . "</option>";
        }
        $index++;
    }

    $popupEditModal = "<div id='popupEditModal' class='popupEditModal'>
              <div class='popupEditContent'>
                  <div class='popupEditHeader'>
                        <mark class='popupEditStatus'>
                                <span class='popupEditStatusText'>" . esc_html($order->get_status()) . "</span>
                        </mark>
                        <h2>" . __('Order', 'new-order-notification-for-woocommerce') . " #" . esc_html($orderId) . "</h2>
                  </div>
                  <div>
                        <div style='min-height: 180px;'>
                            <div style='width: 50%; float: left;'>
                                <h2 class='popupEditAddressHeader'>" . __('Billing Details', 'new-order-notification-for-woocommerce') . "</h2>
                                <strong>" . $order->get_formatted_billing_address() . "</strong>
                            </div>
                            <div style='width: 50%; float: right;'>
                                <h2 class='popupEditAddressHeader'>" . __('Shipping Details', 'new-order-notification-for-woocommerce') . "</h2>
                                <strong>" . $order->get_formatted_shipping_address() . "</strong>
                            </div>
                        </div>
                        <div style='min-height: 180px;'>
                            <div style='width: 50%; float: left;'>
                                <h2 class='popupEditAddressHeader'>" . __('Email', 'new-order-notification-for-woocommerce') . "</h2>
                                <strong>" . esc_html($order->get_billing_email()) . "</strong>
                                <h2 class='popupEditAddressHeader'>" . __('Phone', 'new-order-notification-for-woocommerce') . "</h2>
                                <strong>" . esc_html($order->get_billing_phone()) . "</strong>
                            </div>
                            <div style='width: 50%; float: right'>
                                <div>
                                    <h2 class='popupEditAddressHeader'>" . __('Customer Note', 'new-order-notification-for-woocommerce') . "</h2>
                                    <strong>" . esc_html($order->get_customer_note()) . "</strong>
                                </div>
                                <br/>
                                <h2 class='popupEditAddressHeader'>" . __('Payment Details', 'new-order-notification-for-woocommerce') . "</h2>
                                <div style='width: 50%; float: left;'>
                                    <strong>" . esc_html($order->get_payment_method_title()) . "</strong>
                                </div>
                                <div style='width: 50%; float: right;'>
                                    <strong>" . $order->get_formatted_order_total() . "</strong>
                                </div> 
                            </div>
                        </div>
                        <div style='min-height: 180px;'>                            
                        <h2 class='popupEditAddressHeader'>" . __('Product Details', 'new-order-notification-for-woocommerce') . "</h2>
                            " . $itemContent . "
                        </div>
                        <div style='min-height: 100pxpx;'>                            
                        <h2 class='popupEditAddressHeader'>" . __('Change Order Status', 'new-order-notification-for-woocommerce') . "</h2> 
                            <input id='popupOrderId' type='hidden' value='" . esc_html($orderId) . "'/>
                            <select id='popupStatus' name='popupStatusSelection'>  
                            " . $statusContent . "
                            </select>
                            <input class='popupStatusChangeButton' onclick='orderEditStatus()' value='" . __('Update', 'new-order-notification-for-woocommerce') . "' type='submit' />
                        </div>
                  </div>
              </div>
          </div>";
    echo $popupEditModal;
}

add_action('wp_ajax_show_order_edit_popup_action', 'show_order_edit_popup_action');

function show_order_edit_popup_action()
{

    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'noneni_action')) {
        wp_send_json_error('Invalid nonce.');
    }
    if (!current_user_can('edit_shop_orders')) {
        wp_send_json_error('Unauthorized.');
    }
    $orderId = isset($_POST['orderId']) ? absint($_POST['orderId']) : 0;
    if (!$order = wc_get_order($orderId)) {
        wp_send_json_error('Could not find order.');
    }
    $orderId = $_POST['orderId'];
    showOrderEditPopup($orderId);
    //
    wp_die();
}

add_action('wp_ajax_order_edit_status_action', 'order_edit_status_action');

function order_edit_status_action()
{
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'noneni_action')) {
        wp_send_json_error('Invalid nonce.');
    }
    if (!current_user_can('edit_shop_orders')) {
        wp_send_json_error('Unauthorized.');
    }
    $orderId = isset($_POST['orderId']) ? absint($_POST['orderId']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    if (!$order = wc_get_order($orderId)) {
        wp_send_json_error('Could not find order.');
    }
    $orderId = $_POST['orderId'];
    $status = $_POST['status'];
    //
    $order = wc_get_order($orderId);
    $order->set_status($status);
    $order->save();
    //
    echo wc_get_order_statuses()[$status];
    wp_die();
}

function checkNewOrder($settings)
{
    $maybeOrderId = get_option('_order_id_for_new_order_notification');
    if (!$maybeOrderId) {
        return false;
    }
    $shouldAlert = false;
    // check product options
    $productIds = $settings['product_ids'];
    $alertForThisProduct = false;
    $isAllProducts = true;
    if (is_array($productIds) && count($productIds) != 0) {
        $isAllProducts = false;
    }
    // get new order
    $newOrder = wc_get_order($maybeOrderId);
    if (!$isAllProducts) {
        foreach ($newOrder->get_items() as $itemId => $item) {
            $productId = $item->get_product_id();
            $variationId = $item->get_variation_id();
            if (in_array($productId, $productIds) || in_array($variationId, $productIds)) {
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
        $newOrderId = $newOrder->get_id();
        $orderEditLink = get_site_url() . "/wp-admin/post.php?post=" . $newOrderId . "&action=edit";
        //
        $audio = "<audio id='audioAlert' controls loop>
                      <source src='" . esc_html($musicUrlMp3) . "' type='audio/ogg'>
                      <source src='" . esc_html($musicUrlMp3) . "' type='audio/mpeg'>
                      Your browser does not support the audio element.
                  </audio>";
        $popupContent = "<div id='popupContent' class='popup'>
                            <div class='cnt223'>
                                <h1>" . __('Order Notification - New Order', 'new-order-notification-for-woocommerce') . "</h1>
                                <p>" . __('Check Order Details', 'new-order-notification-for-woocommerce') . " 
                                    <a href='" . esc_html($orderEditLink) . "' target='_blank'>" . esc_html($newOrderId) . "</a>
                                    <br/>
                                    <br/>
                                    <a class='close'>" . __('ACKNOWLEDGE NOTIFICATION', 'new-order-notification-for-woocommerce') . "</a>
                                </p>
                            </div>
                        </div>";
        //
        delete_option('_order_id_for_new_order_notification');
        echo $audio;
        echo $popupContent;
        echo "<script type='text/javascript'>
                    window.focus();
                    //
                    jQuery(function ($) {
                        const overlay = $('<div id=\"overlay\"></div>');
                        overlay.show();
                        const video = document.getElementById('audioAlert');
                        video.oncanplaythrough = function() {
                            video.play();
                        }
                        overlay.appendTo(document.body);
                        $('.popup').show();
                    });
                </script>";
        return true;
    }
    return false;
}

add_action('wp_ajax_re_render_recent_order_table', 're_render_recent_order_table');

function re_render_recent_order_table()
{

    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'noneni_action')) {
        wp_send_json_error('Invalid nonce.');
    }
    if (!current_user_can('edit_shop_orders')) {
        wp_send_json_error('Unauthorized.');
    }
    $settings = getNewOrderNotificationSettings();
    echo getRecentOrderTable($settings);
    wp_die();
}

add_action('wp_ajax_detect_new_order', 'detect_new_order');

function detect_new_order()
{
    $settings = getNewOrderNotificationSettings();
    if (!checkNewOrder($settings)) {
        echo "No new order found.";
    } else {
        echo "New order detected";
    }
    wp_die();
}

function new_order_notification_V2()
{
    $settings = getNewOrderNotificationSettings();
    $isRestricted = checkIfUserRestricted($settings['user_roles']);
    if ($isRestricted) {
        return;
    }
    // display page header
    echo "<h1 id='new-order-notification-header'>" . __('New Order Notification for Woocommerce', 'new-order-notification-for-woocommerce') . "</h1>";
    echo "<h3>" . __('Settings page is not ready for this page. You may still use the old New Order Notification Page listed as subpage in the plugin.', 'new-order-notification-for-woocommerce') . "</h3>";
    // check new order and show popup
    echo "<div id='newOrderDetectDiv' style='display: flex'>
            <p id='activateNewOrderDetectText'>" . __('Activate new order alert: ', 'new-order-notification-for-woocommerce') . "</p>
              <button id='activateNewOrderDetect' class='btn' onclick='loopForNewOrderDetection(" . esc_html($settings['refresh_time'] * 1000) . ")'>
                <i id='activateNewOrderDetectIcon' class='fas fa-toggle-off fa-2x'></i>
              </button>
          </div>";
    // display recent order table
    echo getRecentOrderTable($settings);
    ?>
    <script type='text/javascript'>
        function loopForNewOrderDetection(loopDuration) {
            document.getElementById("activateNewOrderDetectIcon").setAttribute("class", "fas fa-toggle-on fa-2x");
            document.getElementById("activateNewOrderDetectText").innerText = "<?php echo _e('New Order Alert activated.', 'new-order-notification-for-woocommerce') ?>";
            const detectNewOrderAction = {
                'action': 'detect_new_order',
                'security': NewOrderNotif.nonce
            };
            jQuery.post(ajaxurl, detectNewOrderAction, function (response) {
                if (response != 0) {
                    jQuery(function ($) {
                        const newOrderPopup = $(response);
                        newOrderPopup.insertAfter("#newOrderDetectDiv");
                        $('.close').click(function () {
                            $('.popup').hide();
                            document.getElementById('overlay').remove();
                            document.getElementById('audioAlert').pause();
                            document.getElementById("customers-new-order-notification").remove();
                            newOrderPopup.remove();
                            const reRenderRecentOrderTableAction = {
                                'action': 're_render_recent_order_table',
                                'security': NewOrderNotif.nonce
                            }
                            jQuery.post(ajaxurl, reRenderRecentOrderTableAction, function (response) {
                                const recentOrderTable = $(response);
                                recentOrderTable.insertAfter("#newOrderDetectDiv")
                            });
                            return false;
                        });
                    });
                }
                return setTimeout(() => loopForNewOrderDetection(loopDuration), loopDuration);
            });
        }
    </script>
    <?php
}