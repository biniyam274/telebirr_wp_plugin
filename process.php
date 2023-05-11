<?php


require_once('../../../wp-load.php');
require_once('telebirr_class.php');
if (is_user_logged_in()) {
    // Current user is logged in,
    // so let's get current user info
    $current_user = wp_get_current_user();

    if ($current_user != null) {
        if ($_POST['subscription'] != null) {
            $options = get_option('telebirr_options');
            $val = intval($_POST['subscription']);
            $amount = $options['value' . $val];
            $paymentFor = $options['key' . $val] . " Subscription";


            try {
                global $wpdb;
                $table_name = $wpdb->prefix . 'telebirr';
                $entry = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE user_id = '" . $current_user->ID . "'");
                if (!isset($entry)) {
                    $wpdb->insert($wpdb->prefix . 'telebirr', ['user_id' => $current_user->ID]);
                }
                $telebirr = new TelebirrClass(20, $current_user->display_name, $amount, plugin_dir_url((__FILE__)) . "process_telebirr.php?id=$wpdb->insert_id", get_home_url(), $paymentFor);
                $data = $telebirr->getPyamentUrl();
                $newData = $data['data'];
                $url = $newData['toPayUrl'];
                wp_redirect($url);
            } catch (\Throwable $th) {
                print_r($th->getMessage());
            }
        } else {
            print_r("subscription Type Not Set Properly");
        }
    } else {
        wp_redirect(wp_login_url());
    }
} else {
    wp_redirect(wp_login_url());
}
