<?php

/*
 * Plugin Name: telebirr
 * Description:       Handle All Telebirr Payment Requirments.
 * Version:           1.0.0
 * Author:            Biniyam Abera
 * Author URI:        https://www.biniyam.space/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://www.biniyam.space/plugins/
 * Text Domain:       my-plugin
 * Domain Path:       /languages
 */


if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

function telebirr_install()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'telebirr';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
		id int(9) NOT NULL AUTO_INCREMENT,
        user_id int(9) NOT NULL,
        status ENUM ('pending', 'approved') DEFAULT 'pending',
		PRIMARY KEY  (id)
	) $charset_collate;";

  $wpdb->query($sql);
}

function telebirr_uninstall()
{
  global $wpdb;
  $sql = "DROP TABLE " . $wpdb->prefix . 'telebirr;';
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $wpdb->query($sql);
}


function telebirr_payment_form()
{
  // Generate the HTML for the payment form
  $html = " 
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;900&display=swap');

    input {
      caret-color: red;
    }


    .telebirr-container {
      margin:0 auto;       
      width: 350px;
      height: 500px;
      border-radius: 20px;
      padding: 40px;
      box-sizing: border-box;
      background: #ecf0f3;
    }

    .brand-logo {
      height: 128px;
      width: 256px;
      background: url('https://www.ethiotelecom.et/wp-content/uploads/2021/04/TeleBirr-Logo.svg');
      background-repeat:no-repeat;
      background-position: center center;

      margin: auto;
      border-radius: 10%;
      box-sizing: border-box;
    }

    .brand-title {
      margin-top: 10px;
      font-weight: 900;
      font-size: 1.8rem;
      color: #1DA1F2;
      letter-spacing: 1px;
    }

    .inputs {
      text-align: left;
      margin-top: 30px;
    }

    label, input, button {
      display: block;
      width: 100%;
      padding: 0;
      border: none;
      outline: none;
      box-sizing: border-box;
    }

    label {
      margin-bottom: 4px;
    }

    label:nth-of-type(2) {
      margin-top: 12px;
    }

    input::placeholder {
      color: gray;
    }

    input[type='text'] {
      background: #ecf0f3;
      padding: 10px;
      padding-left: 20px;
      height: 50px;
      font-size: 14px;
      border-radius: 50px;
      box-shadow: inset 6px 6px 6px #cbced1, inset -6px -6px 6px white;
    }

    button {
      color: white;
      margin-top: 20px;
      background: #1DA1F2;
      height: 40px;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 900;
      box-shadow: 6px 6px 6px #cbced1, -6px -6px 6px white;
      transition: 0.5s;
    }

    button:hover {
      box-shadow: none;
    }

    a {
      position: absolute;
      font-size: 8px;
      bottom: 4px;
      right: 4px;
      text-decoration: none;
      color: black;
      background: yellow;
      border-radius: 10px;
      padding: 2px;
    }

    h1 {
      position: absolute;
      top: 0;
      left: 0;
    }
</style>
    ";
  $html .= "<form method='POST' action='" . plugin_dir_url((__FILE__)) . "process.php'>";
  $subscription = "";
  $options = get_option('telebirr_options');
  for($i = 1; $i <= 3; $i++) { 
    $isChecked = $i == 1?"checked":""; 
    $subscription .= '<div style=" display:inline;"> <input type="radio" value="'.$i.'" name="subscription" '. $isChecked.' >  <span> '. $options['key'.$i]."( ".$options['value'.$i]." )".' </span> </div>';
   }
  $html .= '
    <div class="telebirr-container">
  <div class="brand-logo"></div>
  <div class="brand-title">Pay With Telebirr</div>
  <div class="">
    <label>Select Subscription</label>
    '.$subscription.'
    <button type="submit">Pay</button>
  </div>
</div>
    ';
  $html .= '</form>';

  return $html;
}






// Add the payment form to a WordPress page using a shortcode
function telebirr_payment_form_shortcode()
{
  $html = telebirr_payment_form();
  return $html;
}

add_shortcode('telebirr_payment_form_shortcode', 'telebirr_payment_form_shortcode');

//  Setting Section

function telebirr_settings_init()
{

  // Register a new setting for "telebirr" page.
  register_setting('telebirr', 'telebirr_options');

  // Register a new section in the "telebirr" page.
  add_settings_section(
    'telebirr_section_developers',
    __('Set Telebirr API here.', 'telebirr'),
    'telebirr_section_developers_callback',
    'telebirr'
  );

  // Register a new section in the "telebirr" page.
  add_settings_section(
    'telebirr_section_subscriptions',
    __('Subscription Type', 'telebirr'),
    'telebirr_section_subscriptions_callback',
    'telebirr'
  );

  // Register a new section in the "telebirr" page.
  add_settings_section(
    'telebirr_section_users',
    __('Paid Users List Here', 'telebirr'),
    'telebirr_section_users_callback',
    'telebirr'
  );



  // Register a new field in the "telebirr_section_subscriptions" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_telebirr_subscriptions_table', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('Telebirr Subscribtion Type', 'telebirr'),
    'telebirr_field_telebirr_subscriptions_table_cb',
    'telebirr',
    'telebirr_section_subscriptions',
    array(
      'label_for'         => array(
        'key1'=>'value1',
        'key2'=>'value2',
        'key3'=>'value3'
    ),
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );



  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_telebirr_users_table', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('Telebirr Subscribers List', 'telebirr'),
    'telebirr_field_telebirr_users_table_cb',
    'telebirr',
    'telebirr_section_users',
    array(
      'label_for'         => 'telebirr_field_telebirr_users_table',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );



  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_api_key', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('API Key', 'telebirr'),
    'telebirr_field_api_key_cb',
    'telebirr',
    'telebirr_section_developers',
    array(
      'label_for'         => 'telebirr_field_api_key',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );

  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_api_id', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('API ID', 'telebirr'),
    'telebirr_field_api_id_cb',
    'telebirr',
    'telebirr_section_developers',
    array(
      'label_for'         => 'telebirr_field_api_id',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );
  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_api_url', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('API Url', 'telebirr'),
    'telebirr_field_api_url_cb',
    'telebirr',
    'telebirr_section_developers',
    array(
      'label_for'         => 'telebirr_field_api_url',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );

  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_api_shortcode', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('API Shortcode', 'telebirr'),
    'telebirr_field_api_shortcode_cb',
    'telebirr',
    'telebirr_section_developers',
    array(
      'label_for'         => 'telebirr_field_api_shortcode',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );



  // Register a new field in the "telebirr_section_developers" section, inside the "telebirr" page.
  add_settings_field(
    'telebirr_field_public_key', // As of WP 4.6 this value is used only internally.
    // Use $args' label_for to populate the id inside the callback.
    __('Public Key', 'telebirr'),
    'telebirr_field_public_key_cb',
    'telebirr',
    'telebirr_section_developers',
    array(
      'label_for'         => 'telebirr_field_public_key',
      'class'             => 'telebirr_row',
      'telebirr_custom_data' => 'custom',
    )
  );
}

/**
 * Register our telebirr_settings_init to the admin_init action hook.
 */
add_action('admin_init', 'telebirr_settings_init');


/**
 * Custom option and settings:
 *  - callback functions
 */


/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function telebirr_section_developers_callback($args)
{
?>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Follow the white rabbit.', 'telebirr'); ?></p>
<?php
}


/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function telebirr_section_subscriptions_callback($args)
{
?>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Add type of subscriptions for payment.', 'telebirr'); ?></p>
<?php
}


/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function telebirr_section_users_callback($args)
{
?>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('List of users that have subscribed telebirr to use this website.', 'telebirr'); ?></p>
<?php
}

function set_option_value_pairs($option_name, $value_key_pairs)
{
  // Get the existing option value
  $existing_value = get_option($option_name, array());

  // Merge the existing value with the new value-key pairs
  $new_value = array_merge($existing_value, $value_key_pairs);

  // Update the option with the new value
  update_option($option_name, $new_value);
}


/**
 * @param array $args
 */
function telebirr_field_telebirr_subscriptions_table_cb($args)
{

  $options = get_option('telebirr_options');
  foreach($args['label_for'] as $key => $value) { 
    ?>
<fieldset>
    <label for="<?php echo esc_attr("$key"); ?>">Type </label>
    <input id="<?php echo esc_attr("$key"); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr("$key"); ?>]" type='text' value="<?php if (isset($options["$key"])) echo ($options["$key"]); ?>" />
    <label for="<?php echo esc_attr("$value"); ?>">Price</label>
    <input id="<?php echo esc_attr("$value"); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr("$value"); ?>]" type='text' value="<?php if (isset($options["$value"])) echo ($options["$value"]); ?>" />
  </fieldset>
    <?php
  }
}


/**
 * @param array $args
 */
function telebirr_field_api_key_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('telebirr_options');
?>
  <input id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr($args['label_for']); ?>]" type='text' value="<?php if (isset($options[$args['label_for']])) echo ($options[$args['label_for']]); ?>" />
<?php
}




/**
 * @param array $args
 */
function telebirr_field_telebirr_users_table_cb($args)
{
  ob_start();

  global $wpdb;
  $user_ids = $wpdb->get_col("SELECT user_id FROM {$wpdb->prefix}telebirr");
  $html = "";
  if (!empty($user_ids)) {

    $users = get_users(array(
      'include' => $user_ids,
    ));

    if (!empty($users)) {
      $html .= '
           <table>
            <thead align="left" style="display: table-header-group">
            <tr>
              <th>User Name </th>
              <th>Payment Status </th>
            </tr>
            </thead>
          <tbody>
          ';
      foreach ($users as $user) {
        $payment = $wpdb->get_col("SELECT status FROM {$wpdb->prefix}telebirr WHERE user_id ='$user->ID'");
        $html .= '<tr class="item_row">';
        $html .= '<td>' . $user->display_name . '</td>';
        $html .= "<td> $payment[0] </td>";
        $html .= '</tr>';
      }
      $html .= '
           </tbody>
          </table>
           ';
    } else {
      $html .= 'No users found.';
    }
  } else {
    $html .= 'No user IDs found in the plugins table.';
  }

  ob_get_clean();
  echo $html;
}



/**
 * @param array $args
 */
function telebirr_field_api_url_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('telebirr_options');
?>
  <input id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr($args['label_for']); ?>]" type='text' value="<?php if (isset($options[$args['label_for']])) echo ($options[$args['label_for']]); ?>" />
<?php
}



/**
 * @param array $args
 */
function telebirr_field_api_shortcode_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('telebirr_options');
?>
  <input id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr($args['label_for']); ?>]" type='text' value="<?php if (isset($options[$args['label_for']])) echo ($options[$args['label_for']]); ?>" />
<?php
}




/**
 * @param array $args
 */
function telebirr_field_public_key_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('telebirr_options');
?>
  <textarea rows="10" cols="100" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr($args['label_for']); ?>]" type='text'><?php if (isset($options[$args['label_for']])) echo ($options[$args['label_for']]); ?></textarea>
<?php
}



/**
 * @param array $args
 */
function telebirr_field_api_id_cb($args)
{
  // Get the value of the setting we've registered with register_setting()
  $options = get_option('telebirr_options');
?>
  <input id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['telebirr_custom_data']); ?>" name="telebirr_options[<?php echo esc_attr($args['label_for']); ?>]" type='text' value="<?php if (isset($options[$args['label_for']])) echo ($options[$args['label_for']]); ?>" />
<?php
}


/**
 * Add the top level menu page.
 */
function telebirr_options_page()
{
  add_menu_page(
    'Telebirr',
    'Telebirr Options',
    'manage_options',
    'telebirr',
    'telebirr_options_page_html'
  );
}


/**
 * Register our telebirr_options_page to the admin_menu action hook.
 */
add_action('admin_menu', 'telebirr_options_page');


/**
 * Top level menu callback function
 */
function telebirr_options_page_html()
{
  // check user capabilities
  if (!current_user_can('manage_options')) {
    return;
  }

  // add error/update messages

  // check if the user have submitted the settings
  // WordPress will add the "settings-updated" $_GET parameter to the url
  if (isset($_GET['settings-updated'])) {
    // add settings saved message with the class of "updated"
    add_settings_error('telebirr_messages', 'telebirr_message', __('Settings Saved', 'telebirr'), 'updated');
  }

  // show error/update messages
  settings_errors('telebirr_messages');
?>
  <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
      <?php
      // output security fields for the registered setting "telebirr"
      settings_fields('telebirr');
      // output setting sections and their fields
      // (sections are registered for "telebirr", each field is registered to a specific section)
      do_settings_sections('telebirr');
      // output save settings button
      submit_button('Save Settings');
      ?>
    </form>
  </div>
<?php
}

// End of  Setting Section 

register_activation_hook(__FILE__, 'telebirr_install');
register_deactivation_hook(__FILE__, 'telebirr_uninstall');
