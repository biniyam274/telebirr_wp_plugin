<?php


require_once('../../../wp-load.php');
require_once('telebirr_class.php');
if (is_user_logged_in()) {
    // Current user is logged in,
    // so let's get current user info
    $current_user = wp_get_current_user();

    if ($current_user != null) {

        // Get the ID parameter from the GET request
        $id = $_GET['id'];

        // Validate the ID parameter
        if (isset($id) && !empty($id) && is_numeric($id)) {
            // Sanitize the ID parameter to prevent SQL injection
            $id = intval($id);
            // Update the payment column in the wp_telebirr table
            $sql = "UPDATE " . $wpdb->prefix . "telebirr SET payment = Approved WHERE uset_id = $id";

            $wpdb->query($sql);
        } else {
            // Invalid or missing ID parameter
            echo "Invalid ID parameter.";
        }
    } else {
        wp_redirect(wp_login_url());
    }
} else {
    wp_redirect(wp_login_url());
}
