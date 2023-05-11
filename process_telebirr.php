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
        $payment = $_GET['payment'];

        // Validate the ID parameter
        if (isset($id) && !empty($id) && is_numeric($id) && isset($payment) && !empty($payment)) {
            // Sanitize the ID parameter to prevent SQL injection
            $id = intval($id);
            // Update the payment column in the wp_telebirr table
            global $wpdb;
                $table_name = $wpdb->prefix . 'telebirr';
            $entry = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE id = '" . $id . "'");
            if (isset($entry)) {
            if($payment == "success"){
                $wpdb->update($table_name,array( 'status'=>'approved'),array('id'=>$id));
                print_r( "payment success");
            }
            else{
                $wpdb->update($table_name,array( 'status'=>'pending'),array('id'=>$id));
                print_r( "payment success");
            }
            }
            else{
                print_r( "No User Found with the given ID parameter.");
            }
            
           
        } else {
            // Invalid or missing ID parameter
            print_r( "Invalid ID parameter.");
        }
    } else {
        wp_redirect(wp_login_url());
    }
} else {
    wp_redirect(wp_login_url());
}
