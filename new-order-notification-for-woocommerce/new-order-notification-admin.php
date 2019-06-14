<?php

add_action( 'admin_menu', 'new_order_notification' );

function new_order_notification() {
    add_menu_page( 'New Order', 'New Order', 'manage_options' , 'new_order_notification' , 'new_order_notification_menu', 'dashicons-star-filled' , '53');
    add_submenu_page('new_order_notification', 'Notification Settings', 'Notification Settings', 'manage_options', 'new_order_notification_submenu', 'new_order_notification_submenu');
}

function new_order_notification_menu()
{
    $query = new WC_Order_Query( array(
        'limit' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
    ) );
    $last_order = $query->get_orders();
    
    $query = new WC_Order_Query( array(
        'limit' => 21,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
    ) );
    $last_20_orders = $query->get_orders();
    $last_20_orders = array_shift($last_20_orders);
    
    $recent_orders = wc_get_orders( array(
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
    ) );
    
    $isCreated = get_option('_new_order_option');
    if(!$isCreated)
    {
        add_option('_new_order_option', array(
        'last_order'   =>  $last_order[0],
        ));
    }
    
    $isNew = false;
    $options = get_option('_new_order_option');
    
    if($last_order[0] != $options['last_order']) $isNew = true;
    
    $websiteUrl = get_site_url();
    $websiteUrl .= "/wp-admin/post.php?post=";
    $websiteUrl .= $last_order[0];
    $websiteUrl .= "&action=edit";
    
    $musicUrlMp3 = get_site_url();
    $musicUrlMp3 .= "/wp-content/uploads/order-music.mp3";
    
    $musicUrlOgg = get_site_url();
    $musicUrlOgg = "/wp-content/uploads/order-music.ogg";
    
    
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
        update_option('_new_order_option', array(
        'last_order'   =>  $last_order[0],
        ));
        $popupcontent = "<audio controls autoplay><source src='".esc_html($musicUrlOgg)."' type='audio/ogg'><source src='".esc_html($musicUrlMp3)."' type='audio/mpeg'>Your browser does not support the audio element.</audio>";
        $popupcontent .= "<div class='popup'><div class='cnt223'><h1>Order Notification</h1><p>New Order: <a href='".esc_html($websiteUrl)."' target='_blank'>".esc_html($last_order[0])."</a><br/><br/><a href='' class='close'>CONFIRM THE ORDER</a></p></div></div>";
        echo $popupcontent;
    }
    
    $content .= "<table id='customers'>";
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
    $content .= "</table>";
    
    echo $content;
    
    if(!$isNew)
    {
        $time = 10;
        header("Refresh:".$time."");
    }
}

function new_order_notification_submenu()
{
    echo "test";
}
?>
