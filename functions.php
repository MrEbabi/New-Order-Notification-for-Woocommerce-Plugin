add_action( 'admin_menu', 'new_order_notification' );
add_menu_page( 'New Order', 'New Order', 'manage_options' , 'new_order_notification' , 'new_order_notification_menu', 'dashicons-star-filled' , '53');

function new_order_notification_menu()
{
    ?>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.8.2.js"></script>
    
    <style type="text/css">
        #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #000;
        filter:alpha(opacity=70);
        -moz-opacity:0.7;
        -khtml-opacity: 0.7;
        opacity: 0.7;
        z-index: 100;
        display: none;
        }
        .cnt223 a{
        text-decoration: none;
        }
        .popup{
        width: 100%;
        margin: 0 auto;
        display: none;
        position: fixed;
        z-index: 101;
        }
        .cnt223{
        min-width: 300px;
        width: 300px;
        min-height: 150px;
        margin: 10px auto;
        background: #f3f3f3;
        position: relative;
        z-index: 103;
        padding: 15px 35px;
        border-radius: 5px;
        box-shadow: 0 2px 5px #000;
        }
        .cnt223 p{
        clear: both;
            color: #555555;
            /* text-align: justify; */
            font-size: 20px;
            font-family: sans-serif;
        }
        .cnt223 p a{
        color: #d91900;
        font-weight: bold;
        }
        .cnt223 .x{
        float: right;
        height: 35px;
        left: 22px;
        position: relative;
        top: -25px;
        width: 34px;
        }
        .cnt223 .x:hover{
        cursor: pointer;
        }
        #customers {
          font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
          margin-top: 2%;
        }
        
        #customers td, #customers th {
          border: 1px solid #ddd;
          padding: 8px;
        }
        
        #customers tr:nth-child(even){background-color: #f2f2f2;}
        
        #customers tr:hover {background-color: #ddd;}
        
        #customers th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: center;
          background-color: #4CAF50;
          color: white;
        }
    </style>

    <?php
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
        $(function(){
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
