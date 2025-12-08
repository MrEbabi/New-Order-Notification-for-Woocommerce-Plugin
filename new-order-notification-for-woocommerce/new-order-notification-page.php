<?php

if (!defined('ABSPATH')) {
    exit;
}

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

    update_option('_order_id_for_new_order_notification', $order->get_id());
}

function getNewOrderNotificationSettings()
{
    if (class_exists('NONW_Settings') && method_exists('NONW_Settings', 'defaults')) {
        $defaults = NONW_Settings::defaults();
        if (empty($defaults['mp3_url'])) {
            $defaults['mp3_url'] = plugins_url('assets/order-music.mp3', __FILE__);
        }
        $option_name = NONW_Settings::OPTION;
    } else {
        global $wp_roles;
        $defaults = [
            'refresh_time' => 30,
            'mp3_url' => plugins_url('assets/order-music.mp3', __FILE__),
            'order_header' => 'Order Notification - New Order',
            'order_text' => 'Check Order Details:',
            'confirm' => 'ACKNOWLEDGE THIS NOTIFICATION',
            'statuses' => array_keys(wc_get_order_statuses()),
            'roles' => array_keys($wp_roles->roles),
            'product_ids' => wc_get_products([
                'limit' => -1,
                'return' => 'ids',
                'type' => ['simple', 'variable'],
                'status' => 'publish',
            ]),
            'show_order_num' => 20
        ];
        $option_name = '_non_v2_alert_options';
    }

    $stored = get_option($option_name, []);
    if (!is_array($stored)) {
        $stored = [];
    }

    if ($option_name !== '_non_v2_alert_options' && empty($stored)) {
        $legacy = get_option('_non_v2_alert_options', []);
        if (is_array($legacy) && !empty($legacy)) {
            $stored = $legacy;
        }
    }

    $settings = wp_parse_args($stored, $defaults);

    $settings['refresh_time'] = max(1, intval($settings['refresh_time'] ?? 30));
    $settings['show_order_num'] = max(1, intval($settings['show_order_num'] ?? 20));

    $settings['statuses'] = array_values((array)($settings['statuses'] ?? []));
    $settings['roles'] = array_values((array)($settings['roles'] ?? []));
    $settings['product_ids'] = array_map('intval', (array)($settings['product_ids'] ?? []));

    if (empty($settings['mp3_url'])) {
        $settings['mp3_url'] = plugins_url('assets/order-music.mp3', __FILE__);
    }

    if (!isset($settings['user_roles'])) {
        $settings['user_roles'] = $settings['roles'];
    }

    if (!isset($settings['show_order_statuses']) || empty($settings['show_order_statuses'])) {
        $settings['show_order_statuses'] = $settings['statuses'];
    }

    if (empty($settings['order_header'])) {
        $settings['order_header'] = 'Order Notification - New Order';
    }

    if (empty($settings['order_text'])) {
        $settings['order_text'] = 'Check Order Details:';
    }

    if (empty($settings['confirm'])) {
        $settings['confirm'] = 'ACKNOWLEDGE THIS NOTIFICATION';
    }

    return $settings;
}

function checkIfUserRestricted($userRoles)
{
    $user = wp_get_current_user();
    $restricted = true;

    foreach ($userRoles as $role) {
        if (in_array($role, (array)$user->roles, true)) {
            $restricted = false;
            break;
        }
    }

    if ($restricted) {
        echo "<br><br><h2>" . esc_html__("You don't have permission to see New Order Notification page.", 'new-order-notification-for-woocommerce') . "</h2>";
    }

    return $restricted;
}

function getRecentOrderTable($settings)
{
    $orders = wc_get_orders([
        'limit' => $settings['show_order_num'],
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => $settings['show_order_statuses'],
        'type' => 'shop_order',
    ]);

    $content = "<table id='customers-new-order-notification'>
        <tr>
            <th>" . esc_html__('Order ID', 'new-order-notification-for-woocommerce') . "</th>
            <th>" . esc_html__('Date', 'new-order-notification-for-woocommerce') . "</th>
            <th>" . esc_html__('Status', 'new-order-notification-for-woocommerce') . "</th>
            <th>" . esc_html__('Preview/Edit', 'new-order-notification-for-woocommerce') . "</th>
        </tr>";

    foreach ($orders as $recentOrder) {
        $orderId = $recentOrder->get_id();
        $order = wc_get_order($orderId);
        $orderDate = $order->get_date_created();
        $orderLink = admin_url('post.php?post=' . $orderId . '&action=edit');
        $statusLabel = wc_get_order_statuses()['wc-' . strtolower($recentOrder->get_status())] ?? $recentOrder->get_status();
        $orderNumber = $order->get_order_number();

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $formatted = $orderDate ? $orderDate->date($dateFormat . ' - ' . $timeFormat) : '';

        $content .= "
        <tr>
            <td>" . esc_html($orderNumber) . "</td>
            <td>" . esc_html($formatted) . "</td>
            <td id='non-status-" . esc_attr($orderId) . "'>" . esc_html($statusLabel) . "</td>
            <td>
                <div style='display:flex;justify-content:space-evenly;'>
                    <button class='btn' type='button' onclick='showOrderEditPopupButton(this.value)' value='" . esc_attr($orderId) . "'>
                        <i class='fas fa-eye'></i>
                    </button>
                    <a href='" . esc_url($orderLink) . "' target='_blank'><i class='fas fa-link'></i></a>
                </div>
            </td>
        </tr>";
        ?>
        <script type="text/javascript">
            window.onclick = function (event) {
                const modal = document.getElementById("popupEditModal");
                if (modal && event.target === modal) {
                    modal.remove();
                }
            };

            function showOrderEditPopupButton(orderId) {
                jQuery.post(ajaxurl, {
                    action: 'show_order_edit_popup_action',
                    orderId: orderId,
                    security: NewOrderNotif.nonce
                }, function (response) {
                    jQuery(response).appendTo(document.body);
                    const modal = document.getElementById("popupEditModal");
                    if (modal) modal.style.display = "block";
                });
            }

            function orderEditStatus() {
                const orderId = document.getElementById('popupOrderId').value;
                const status = document.getElementById('popupStatus').value;

                jQuery.post(ajaxurl, {
                    action: 'order_edit_status_action',
                    orderId: orderId,
                    status: status,
                    security: NewOrderNotif.nonce
                }, function (response) {
                    const modal = document.getElementById("popupEditModal");
                    if (modal) modal.remove();
                    const col = document.getElementById("non-status-" + orderId);
                    if (col) col.innerText = response;
                });
            }
        </script>
        <?php
    }

    return $content . "</table>";
}

function showOrderEditPopup($orderId)
{
    $order = wc_get_order($orderId);
    if (!$order) return;

    $itemContent = "<table id='popup-new-order-notification'>
        <tr>
            <th>" . esc_html__('Product', 'new-order-notification-for-woocommerce') . "</th>
            <th>" . esc_html__('Quantity', 'new-order-notification-for-woocommerce') . "</th>
            <th>" . esc_html__('Total', 'new-order-notification-for-woocommerce') . "</th>
        </tr>";

    foreach ($order->get_items() as $item) {
        $itemContent .= "
        <tr>
            <td>" . esc_html($item->get_name()) . "</td>
            <td>" . esc_html($item->get_quantity()) . "</td>
            <td>" . wp_kses_post(wc_price($item->get_total())) . "</td>
        </tr>";
    }

    $itemContent .= "</table>";

    $statusContent = "";
    foreach (wc_get_order_statuses() as $key => $label) {
        $statusContent .= "<option value='" . esc_attr($key) . "'>" . esc_html($label) . "</option>";
    }

    echo "
    <div id='popupEditModal' class='popupEditModal'>
        <div class='popupEditContent'>
            <div class='popupEditHeader'>
                <mark class='popupEditStatus'><span class='popupEditStatusText'>" . esc_html($order->get_status()) . "</span></mark>
                <h2>" . esc_html__('Order', 'new-order-notification-for-woocommerce') . " #" . esc_html($orderId) . "</h2>
            </div>
            <div>
                <div style='min-height:180px;'>
                    <div style='width:50%;float:left;'>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Billing Details', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . wp_kses_post($order->get_formatted_billing_address()) . "</strong>
                    </div>
                    <div style='width:50%;float:right;'>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Shipping Details', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . wp_kses_post($order->get_formatted_shipping_address()) . "</strong>
                    </div>
                </div>

                <div style='min-height:180px;'>
                    <div style='width:50%;float:left;'>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Email', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . esc_html($order->get_billing_email()) . "</strong>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Phone', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . esc_html($order->get_billing_phone()) . "</strong>
                    </div>
                    <div style='width:50%;float:right;'>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Customer Note', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . esc_html($order->get_customer_note()) . "</strong>
                        <h2 class='popupEditAddressHeader'>" . esc_html__('Payment Details', 'new-order-notification-for-woocommerce') . "</h2>
                        <strong>" . esc_html($order->get_payment_method_title()) . "</strong>
                        <strong>" . wp_kses_post($order->get_formatted_order_total()) . "</strong>
                    </div>
                </div>

                <h2 class='popupEditAddressHeader'>" . esc_html__('Product Details', 'new-order-notification-for-woocommerce') . "</h2>
                $itemContent

                <h2 class='popupEditAddressHeader'>" . esc_html__('Change Order Status', 'new-order-notification-for-woocommerce') . "</h2>
                <input id='popupOrderId' type='hidden' value='" . esc_attr($orderId) . "'/>
                <select id='popupStatus'>$statusContent</select>
                <input class='popupStatusChangeButton' onclick='orderEditStatus()' value='" . esc_attr__('Update', 'new-order-notification-for-woocommerce') . "' type='button' />
            </div>
        </div>
    </div>";
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

    $orderId = absint($_POST['orderId']);
    if (!$orderId || !wc_get_order($orderId)) {
        wp_send_json_error('Could not find order.');
    }

    showOrderEditPopup($orderId);
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

    $orderId = absint($_POST['orderId']);
    $status = sanitize_text_field($_POST['status']);

    if (!$orderId || !$status || !$order = wc_get_order($orderId)) {
        wp_send_json_error('Could not find order.');
    }

    $order->set_status($status);
    $order->save();

    echo esc_html(wc_get_order_statuses()[$status] ?? $status);
    wp_die();
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
        $orderHeader = $settings['order_header'];
        $orderText = $settings['order_text'];
        $confirm = $settings['confirm'];
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
                                <h1>" . esc_html($orderHeader) . "</h1>
                                <p>" . esc_html($orderText) . " 
                                    <a href='" . esc_html($orderEditLink) . "' target='_blank'>" . esc_html($newOrderId) . "</a>
                                    <br/>
                                    <br/>
                                    <a class='close'>" . esc_html($confirm) . "</a>
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

add_action('wp_ajax_detect_new_order', 'detect_new_order');

function detect_new_order()
{
    $settings = getNewOrderNotificationSettings();

    if (!checkNewOrder($settings)) {
        echo 'No new order found.';
    } else {
        echo 'New order detected';
    }

    wp_die();
}

function new_order_notification_V2()
{
    $settings = getNewOrderNotificationSettings();

    if (checkIfUserRestricted($settings['user_roles'])) {
        return;
    }

    echo "<h1 id='new-order-notification-header'>" . esc_html__('New Order Notification for Woocommerce', 'new-order-notification-for-woocommerce') . "</h1>";
    echo "<h3>" . esc_html__('You can configure alert behavior from the Settings page of this plugin.', 'new-order-notification-for-woocommerce') . "</h3>";

    echo "
    <div id='newOrderDetectDiv' style='display:flex;align-items:center;gap:8px;'>
        <p id='activateNewOrderDetectText'>" . esc_html__('Activate new order alert: ', 'new-order-notification-for-woocommerce') . "</p>
        <button id='activateNewOrderDetect' class='btn' onclick='loopForNewOrderDetection(" . esc_attr($settings['refresh_time'] * 1000) . ")'>
            <i id='activateNewOrderDetectIcon' class='fas fa-toggle-off fa-2x'></i>
        </button>
    </div>";

    echo getRecentOrderTable($settings);
    ?>
    <script type="text/javascript">
        function loopForNewOrderDetection(loopDuration) {
            document.getElementById("activateNewOrderDetectIcon").setAttribute("class", "fas fa-toggle-on fa-2x");
            document.getElementById("activateNewOrderDetectText").innerText = "<?php echo esc_js(__('New Order Alert activated.', 'new-order-notification-for-woocommerce')); ?>";

            jQuery.post(ajaxurl, {
                action: 'detect_new_order',
                security: NewOrderNotif.nonce
            }, function (response) {

                if (response !== 0) {
                    jQuery(function ($) {
                        const popup = $(response);
                        popup.insertAfter("#newOrderDetectDiv");

                        $('.close').click(function () {
                            $('.popup').hide();

                            const overlay = document.getElementById('overlay');
                            if (overlay) overlay.remove();

                            const audio = document.getElementById('audioAlert');
                            if (audio) audio.pause();

                            const table = document.getElementById('customers-new-order-notification');
                            if (table) table.remove();

                            popup.remove();

                            jQuery.post(ajaxurl, {
                                action: 're_render_recent_order_table',
                                security: NewOrderNotif.nonce
                            }, function (tableResponse) {
                                $(tableResponse).insertAfter("#newOrderDetectDiv");
                            });

                            return false;
                        });
                    });
                }

                setTimeout(function () {
                    loopForNewOrderDetection(loopDuration);
                }, loopDuration);
            });
        }
    </script>
    <?php
}
