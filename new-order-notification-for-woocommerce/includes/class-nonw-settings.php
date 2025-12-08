<?php
if (!defined('ABSPATH')) exit;

class NONW_Settings
{

    const OPTION = 'nonw_settings';

    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'register']);
    }

    public static function register()
    {

        register_setting(
            'nonw_settings_group',
            self::OPTION,
            [
                'default' => self::defaults(),
                'sanitize_callback' => [__CLASS__, 'sanitize']
            ]
        );

        add_settings_section('nonw_general', 'General Settings', null, 'nonw_settings_general');

        add_settings_field(
            'refresh_time',
            'Refresh Time',
            ['NONW_Render_Fields', 'number'],
            'nonw_settings_general',
            'nonw_general',
            ['label_for' => 'refresh_time']
        );

        add_settings_field(
            'mp3_url',
            'Alert Sound (MP3 URL)',
            ['NONW_Render_Fields', 'text'],
            'nonw_settings_general',
            'nonw_general',
            ['label_for' => 'mp3_url']
        );

        add_settings_field(
            'order_header',
            'Popup Header',
            ['NONW_Render_Fields', 'text'],
            'nonw_settings_general',
            'nonw_general',
            ['label_for' => 'order_header']
        );

        add_settings_field(
            'order_text',
            'Popup Text Before Order ID',
            ['NONW_Render_Fields', 'text'],
            'nonw_settings_general',
            'nonw_general',
            ['label_for' => 'order_text']
        );

        add_settings_field(
            'confirm',
            'Confirmation Button Text',
            ['NONW_Render_Fields', 'text'],
            'nonw_settings_general',
            'nonw_general',
            ['label_for' => 'confirm']
        );

        add_settings_section('nonw_notifications', 'Notification Rules', null, 'nonw_settings_notifications');

        add_settings_field(
            'statuses',
            'Order Statuses',
            ['NONW_Render_Fields', 'multi_checkbox_statuses'],
            'nonw_settings_notifications',
            'nonw_notifications'
        );

        add_settings_field(
            'roles',
            'Allowed User Roles',
            ['NONW_Render_Fields', 'multi_select_roles'],
            'nonw_settings_notifications',
            'nonw_notifications'
        );

        add_settings_field(
            'show_order_num',
            'Orders to Display',
            ['NONW_Render_Fields', 'number'],
            'nonw_settings_notifications',
            'nonw_notifications',
            ['label_for' => 'show_order_num', 'min' => 1, 'max' => 200]
        );

        add_settings_section('nonw_filters', 'Product Filters', null, 'nonw_settings_filters');

        add_settings_field(
            'product_ids',
            'Filter by Product IDs',
            ['NONW_Render_Fields', 'multi_select_products'],
            'nonw_settings_filters',
            'nonw_filters'
        );

    }

    public static function defaults()
    {
        return [
            'refresh_time' => 30,
            'mp3_url' => '',
            'order_header' => 'Order Notification - New Order',
            'order_text' => 'Check Order Details:',
            'confirm' => 'ACKNOWLEDGE THIS NOTIFICATION',
            'statuses' => array_keys(wc_get_order_statuses()),
            'roles' => array_keys(wp_roles()->roles),
            'product_ids' => wc_get_products(['limit' => -1, 'return' => 'ids']),
            'show_order_num' => 20
        ];
    }

    public static function sanitize($input)
    {
        foreach ($input as &$v) {
            if (is_array($v)) {
                $v = array_map('sanitize_text_field', $v);
            } else {
                $v = sanitize_text_field($v);
            }
        }
        return $input;
    }
}