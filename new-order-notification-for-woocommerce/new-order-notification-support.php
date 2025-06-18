<?php

function new_order_notification_support()
{
    $user = wp_get_current_user();

    $content = "<h1>New Order Notification for Woocommerce - Support</h1>";
    $content .= "<div class='support-main'>
                     <p class='support-title'>Dear " . esc_html($user->user_login) . ", 
                     </br></br>
                     For almost three years, I am trying to improve the plugin by adding new features and solving the reported bugs.
                     </br></br>
                     On the plugin support page on Wordpress, I see that many users wants custom work about the plugin. Therefore, I often get emails about paid support or just to contribute for future developments. So, I decided to create a page for Support.
                     </br></br>
                     With this page, you may reach and support me about this plugin and also other Wordpress/Woocommerce issues that you have.
                     </br></br>
                     Below, you can see my freelancer profile and personal website and check my portfolio and work experience.
                     </br></br>
                     Hope you liked the plugin. Best regards.
                     </br></br> 
                     </p>
                     <div class='support-card'>
                     <img src='https://avatars.githubusercontent.com/u/40281221' alt='Plugin Developer' style='width:50%'>
                     <h1>Eyup Gulsen</h1>
                     <p class='support-title'>Software Engineer</p>
                     <p class='support-title'>Freelance Wordpress/Woocommerce Developer</p>
                     <div style='margin: 24px 0;'>
                         <a class='support' href='https://www.linkedin.com/in/ey%C3%BCp-sabri-g%C3%BCl%C5%9Fen-809837186/' target='_blank'><i class='fa fa-linkedin-in'></i></a>  
                         <a class='support' href='https://mrebabi.com/iletisim/' target='_blank'><i class='fas fa-envelope'></i></a>  
                         <a class='support' href='https://mrebabi.com/' target='_blank'><i class='fas fa-globe'></i></a>  
                     </div>
                     <p>
                        <a href ='https://mrebabi.com/donate/' target='_blank'>
                            <button class='support'>Donate Crypto</button>
                        </a>
                     </p>
                 </div>
                 </div>";
    echo $content;
}