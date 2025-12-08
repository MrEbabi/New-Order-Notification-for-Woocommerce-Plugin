<?php
if (!defined('ABSPATH')) exit;

class NONW_Settings_Page
{

    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'scripts']);
    }

    public static function menu()
    {
        add_submenu_page(
            'new_order_notification',
            'Settings',
            'Settings',
            'manage_options',
            'new_order_notification_settings',
            [__CLASS__, 'render']
        );
    }

    public static function scripts()
    {
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', "
            jQuery(function($){
                $('.nonw-tab-link').on('click', function(e){
                    e.preventDefault();
                    let tab = $(this).data('tab');
                    $('.nonw-tab-content').hide();
                    $('#'+tab).show();
                    $('.nonw-tab-link').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                });
                $('.nonw-tab-link:first').click();
            });
        ");
    }

    public static function render()
    {
        ?>
        <div class="wrap">
            <h1>New Order Notification – Settings</h1>

            <h2 class="nav-tab-wrapper">
                <a href="#" class="nav-tab nonw-tab-link" data-tab="tab-general">General</a>
                <a href="#" class="nav-tab nonw-tab-link" data-tab="tab-notifications">Notifications</a>
                <a href="#" class="nav-tab nonw-tab-link" data-tab="tab-filters">Filters</a>
                <a href="#" class="nav-tab nonw-tab-link" data-tab="tab-preview">Preview</a>
            </h2>

            <form action="options.php" method="post">
                <?php settings_fields('nonw_settings_group'); ?>

                <div id="tab-general" class="nonw-tab-content">
                    <?php do_settings_sections('nonw_settings_general'); ?>
                </div>
                <div id="tab-notifications" class="nonw-tab-content">
                    <?php do_settings_sections('nonw_settings_notifications'); ?>
                </div>
                <div id="tab-filters" class="nonw-tab-content">
                    <?php do_settings_sections('nonw_settings_filters'); ?>
                </div>

                <div id="tab-preview" class="nonw-tab-content">
                    <h2>Popup Preview</h2>
                    <button type="button" class="button button-primary">Show Preview</button>
                </div>

                <?php submit_button('Save Settings'); ?>
                <button type="button" id="nonw-defaults" class="button">Apply Default Settings</button>

            </form>
        </div>

        <script>
            jQuery(function ($) {
                $('#nonw-defaults').on('click', function () {
                    if (!confirm("Apply default settings?")) return;

                    $.post(ajaxurl, {
                        action: 'nonw_apply_defaults',
                        nonce: '<?php echo wp_create_nonce("nonw_defaults_nonce"); ?>'
                    }, function () {
                        location.reload();
                    });
                });
            });
        </script>

        <?php
    }
}
