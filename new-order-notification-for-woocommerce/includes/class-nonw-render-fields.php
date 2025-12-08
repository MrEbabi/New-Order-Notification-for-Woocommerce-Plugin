<?php
if (!defined('ABSPATH')) exit;

class NONW_Render_Fields
{

    public static function text($args)
    {
        $opt = get_option(NONW_Settings::OPTION);
        $value = $opt[$args['label_for']] ?? '';
        echo "<input type='text' name='nonw_settings[{$args['label_for']}]' value='" . esc_attr($value) . "' class='regular-text'>";
    }

    public static function number($args)
    {
        $opt = get_option(NONW_Settings::OPTION);
        $value = $opt[$args['label_for']] ?? '';

        $min = $args['min'] ?? 1;
        $max = $args['max'] ?? '';

        echo "<input type='number' min='{$min}' max='{$max}' 
               name='nonw_settings[{$args['label_for']}]'
               value='" . esc_attr($value) . "' 
               class='small-text'>";
    }

    public static function multi_checkbox_statuses()
    {
        $opt = get_option(NONW_Settings::OPTION);
        $selected = $opt['statuses'] ?? [];

        foreach (wc_get_order_statuses() as $key => $label) {
            $checked = in_array($key, $selected) ? 'checked' : '';
            echo "<label><input type='checkbox' name='nonw_settings[statuses][]' value='{$key}' {$checked}> {$label}</label><br>";
        }
    }

    public static function multi_select_roles()
    {
        $opt = get_option(NONW_Settings::OPTION);
        $selected = $opt['roles'] ?? [];
        $roles = wp_roles()->roles;

        echo "<select multiple name='nonw_settings[roles][]' style='height:140px;width:280px;'>";
        foreach ($roles as $key => $role) {
            $sel = in_array($key, $selected) ? 'selected' : '';
            echo "<option value='{$key}' {$sel}>{$role['name']}</option>";
        }
        echo "</select>";
    }

    public static function multi_select_products()
    {
        $opt = get_option(NONW_Settings::OPTION);
        $selected = $opt['product_ids'] ?? [];

        $products = wc_get_products(['limit' => -1, 'return' => 'ids']);

        echo "<select multiple name='nonw_settings[product_ids][]' style='height:200px;width:300px;'>";
        foreach ($products as $id) {
            $sel = in_array($id, $selected) ? 'selected' : '';
            echo "<option value='{$id}' {$sel}>{$id} — " . get_the_title($id) . "</option>";
        }
        echo "</select>";
    }
}