<?php
/*
Plugin Name: New Order Notification for Woocommerce
Description: Woocommerce custom order page with recent orders for showing a popup notification with sound when a new order received.
Version: 2.1.0
Author: Mr.Ebabi
Author URI: https://github.com/MrEbabi
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: new-order-notification-for-woocommerce
WC requires at least: 2.5
WC tested up to: 9.9.4
*/

if (!defined('ABSPATH')) {
    exit;
}

define('NONW_PLUGIN_FILE', __FILE__);
define('NONW_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('NONW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NONW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NONW_VERSION', '2.1.0');

if (!class_exists('New_Order_Notification_For_WooCommerce')) {

    final class New_Order_Notification_For_WooCommerce
    {
        private static $instance = null;

        public static function instance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct()
        {
            $this->includes();

            $this->load_textdomain();

            if (class_exists('NONW_Settings')) {
                NONW_Settings::init();
            }

            if (class_exists('NONW_Settings_Page') && method_exists('NONW_Settings_Page', 'init')) {
                NONW_Settings_Page::init();
            }

            add_action('admin_init', array($this, 'check_woocommerce_dependency'));
            add_filter('plugin_action_links_' . NONW_PLUGIN_BASENAME, array($this, 'settings_link'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
            add_action('admin_head', array($this, 'inline_nonce_js'));
            add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        }

        private function includes()
        {
            require_once NONW_PLUGIN_DIR . 'new-order-notification-admin.php';
            require_once NONW_PLUGIN_DIR . 'new-order-notification-page.php';
            require_once NONW_PLUGIN_DIR . 'new-order-notification-support.php';

            if (file_exists(NONW_PLUGIN_DIR . 'includes/class-nonw-settings.php')) {
                require_once NONW_PLUGIN_DIR . 'includes/class-nonw-settings.php';
            }
            if (file_exists(NONW_PLUGIN_DIR . 'includes/class-nonw-render-fields.php')) {
                require_once NONW_PLUGIN_DIR . 'includes/class-nonw-render-fields.php';
            }
            if (file_exists(NONW_PLUGIN_DIR . 'includes/class-nonw-settings-page.php')) {
                require_once NONW_PLUGIN_DIR . 'includes/class-nonw-settings-page.php';
            }
        }

        public function load_textdomain()
        {
            load_plugin_textdomain(
                'new-order-notification-for-woocommerce',
                false,
                dirname(NONW_PLUGIN_BASENAME) . '/languages'
            );
        }

        public function check_woocommerce_dependency()
        {
            if (!is_admin() || !current_user_can('activate_plugins')) {
                return;
            }

            if (!function_exists('is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('admin_notices', array($this, 'woocommerce_required_notice'));
                deactivate_plugins(NONW_PLUGIN_BASENAME);

                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }

        public function woocommerce_required_notice()
        {
            ?>
            <style>
                #toplevel_page_new_order_notification {
                    display: none;
                }
            </style>
            <div class="notice notice-error">
                <p>
                    <?php
                    esc_html_e(
                        'Sorry, but New Order Notification for Woocommerce requires the WooCommerce plugin to be installed and activated.',
                        'new-order-notification-for-woocommerce'
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        public function settings_link($links)
        {
            $settings_url = admin_url('admin.php?page=new_order_notification_settings');
            $links[] = '<a href="' . esc_url($settings_url) . '">' .
                esc_html__('Settings', 'new-order-notification-for-woocommerce') .
                '</a>';
            return $links;
        }

        public function enqueue_admin_assets($hook)
        {
            $screen = get_current_screen();
            if (!$screen) {
                return;
            }

            if (strpos($screen->id, 'new_order_notification') === false
                && $screen->id !== 'toplevel_page_new_order_notification'
            ) {
                return;
            }

            wp_enqueue_style(
                'new-order-notification-admin-css',
                NONW_PLUGIN_URL . 'assets/new-order-notification.css',
                array(),
                NONW_VERSION
            );

            wp_enqueue_script('jquery');

            wp_enqueue_style(
                'nonw-fontawesome',
                'https://use.fontawesome.com/releases/v5.8.1/css/all.css',
                array(),
                '5.8.1'
            );
        }

        public function inline_nonce_js()
        {
            $screen = get_current_screen();
            if (!$screen) {
                return;
            }

            if ($screen->id !== 'toplevel_page_new_order_notification') {
                return;
            }
            ?>
            <script type="text/javascript">
                var NewOrderNotif = {
                    ajax_url: "<?php echo esc_js(admin_url('admin-ajax.php')); ?>",
                    nonce: "<?php echo esc_js(wp_create_nonce('noneni_action')); ?>"
                };
            </script>
            <?php
        }

        public function declare_hpos_compatibility()
        {
            if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'custom_order_tables',
                    NONW_PLUGIN_FILE,
                    true
                );
            }
        }
    }
}

add_action('wp_ajax_nonw_apply_defaults', function () {
    check_ajax_referer('nonw_defaults_nonce', 'nonce');

    update_option('nonw_settings', NONW_Settings::defaults());

    wp_send_json_success();
});


function nonw_on_activation()
{
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        deactivate_plugins(NONW_PLUGIN_BASENAME);
        wp_die(
            esc_html__(
                'New Order Notification for Woocommerce requires WooCommerce to be installed and active.',
                'new-order-notification-for-woocommerce'
            ),
            esc_html__('Plugin dependency check', 'new-order-notification-for-woocommerce'),
            array('back_link' => true)
        );
    }
}

register_activation_hook(NONW_PLUGIN_FILE, 'nonw_on_activation');

New_Order_Notification_For_WooCommerce::instance();
