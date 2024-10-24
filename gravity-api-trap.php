<?php
/**
 * Plugin Name: Trap Gravity Forms for Enquire/Aline CRM
 * Plugin URI: https://github.com/sageage/gravity-api-trap
 * Author: Aaron DeMent
 * Author URI: https://github.com/sageage/gravity-api-trap
 * Description: Collect Gravity forms to Webhook
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: gravity-api-trap
*/

///die if not in true wordpress environment
defined( 'ABSPATH' ) or die( 'No entry' );

//js reset button on settings page for Form Feild = ID and add fields and ID's to array
function gravity_api_trap_load_script() {
    $plugin_dir_url = plugin_dir_url(__FILE__);
    $script_url = $plugin_dir_url . 'reset-form-field_ID.js';
    if (isset($_GET['page']) && $_GET['page'] == 'gravity-api-trap') {
        wp_enqueue_script('gapi-plugin-script', $script_url);
        wp_localize_script('gapi-plugin-script', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'gravity_api_trap_load_script' );


// Add settings page
add_action( 'admin_menu', 'gravity_api_trap_settings_page' );

function gravity_api_trap_settings_page() {
    add_options_page(
        'Gravity API Trap Settings',
        'Gravity API Trap',
        'manage_options',
        'gravity-api-trap',
        'gravity_api_trap_settings'
    );
}

// settings page
function gravity_api_trap_settings() {
    ?>
    <div class="wrap">
        <h1>Gravity API Trap Settings</h1>
      
        <form method="post" action="options.php">
            <?php settings_fields( 'gravity-api-trap' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Portal ID:</th>
                    <td><input type="text" name="gravity_api_trap_portal_id" value="<?php echo esc_attr( get_option( 'gravity_api_trap_portal_id' ) ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Form/Entry ID:</th>
                    <td><input type="text" name="gravity_api_trap_entry_ids" value="<?php echo esc_attr( get_option( 'gravity_api_trap_entry_ids' ) ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Primary API Key:</th>
                    <td><input type="text" name="gravity_api_trap_primary_api_key" value="<?php echo esc_attr( get_option( 'gravity_api_trap_primary_api_key' ) ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Secondary API Key:</th>
                    <td><input type="text" name="gravity_api_trap_secondary_api_key" value="<?php echo esc_attr( get_option( 'gravity_api_trap_secondary_api_key' ) ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Endpoint URL:</th>
                    <td><input type="text" name="gravity_api_trap_endpoint_url" value="<?php echo esc_attr( get_option( 'gravity_api_trap_endpoint_url' ) ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Timezone ID:</th>
                    <td><input type="text" name="gravity_api_trap_timezone_id" value="<?php echo esc_attr( get_option( 'gravity_api_trap_timezone_id' ) ); ?>" /></td>
                    <td>Time is an integer (need definition from Enquire on this)</td>
                </tr>
                <tr>
                    <th scope="row">Field and IDs: </th>
                    <td>
                        <div id="field-id-container">
                            <!-- This will be populated dynamically comes from the JS-->
                        </div>
                        <button id="add-field-id-button" type="button">Add Field</button>
                    </td>
                </tr>


            <!--Display Fields per Form-->
            <tr>
                <th scope="row">Selected Form IDs and Input IDs:</th>
                <td>
                    <div id="existing-form-settings"></div>
                </td>
            </tr>

            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action( 'admin_init', 'gravity_api_trap_register_settings' );

function gravity_api_trap_register_settings() {
    register_setting( 'gravity-api-trap', 'gravity_api_trap_portal_id' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_entry_ids', 'sanitize_text_field_array' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_primary_api_key' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_secondary_api_key' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_endpoint_url' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_timezone_id' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_fields', 'sanitize_text_field' );

    // Add AJAX hook to update the gravity_api_trap_fields option
    add_action( 'wp_ajax_update_gravity_api_trap_fields', 'update_gravity_api_trap_fields' );
}

// Add AJAX hook to retrieve existing form settings
add_action('wp_ajax_get_existing_form_settings', 'get_existing_form_settings');
add_action('wp_ajax_nopriv_get_existing_form_settings', 'get_existing_form_settings');


//Get the settings from the DB via ajax in the JS file
function get_existing_form_settings() {
    $form_ids = get_option('gravity_api_trap_entry_ids', []);
    $field_settings = get_option('gravity_api_trap_fields');

    $response = array(
        'form_ids' => $form_ids,
        'field_settings' => $field_settings,
    );

    wp_send_json_success($response);
}


//get the array of field settings from the database and update the gravity_api_trap_fields option
function update_gravity_api_trap_fields() {
    // Update the gravity_api_trap_fields option
    $fieldSettings = $_POST['field_settings'];
    $fields = [];
    foreach ($fieldSettings as $field) {
        $fields[$field['name']] = $field['id'];
    }
    update_option( 'gravity_api_trap_fields', $fields );

    // Return a success message
    wp_send_json_success( 'Field settings updated successfully' );
}

// Add action hook
add_action( 'gform_after_submission', 'grabentries_after_submission', 10, 2 );


function grabentries_after_submission(array $entry, array $form): void
{
    $fieldSettings = get_option('gravity_api_trap_fields');
    $fields = parseFieldSettingsToArray($fieldSettings);

    $selectedFormIds = get_option('gravity_api_trap_entry_ids', []);

    if (in_array($entry['form_id'], $selectedFormIds)) {
        $info = [];
        foreach ($fields as $fieldName => $fieldId) {
            $info[$fieldName] = $entry[$fieldId];
        }

        $data = prepareApiRequestData($info);
        $response = sendApiRequest($data);

        if (is_wp_error($response)) {
            // Handle error
            error_log('API request failed: ' . $response->get_error_message());
        } elseif ($response['response']['code'] !== 200) {
            // Handle non-200 response
            error_log('API request failed with status code ' . $response['response']['code']);
        }
    }
}

function parseFieldSettingsToArray(string $fieldSettings): array
{
    $fields = [];
    if (is_string($fieldSettings)) {
        $lines = explode("\n", $fieldSettings);
        foreach ($lines as $line) {
            list($fieldName, $fieldId) = explode('=', trim($line));
            $fields[$fieldName] = $fieldId;
        }
    } elseif (is_array($fieldSettings)) {
        $fields = $fieldSettings;
    }
    return $fields;
}
function prepareApiRequestData(array $info): array
{
    $data = [
        'Community' => [
            ['Id' => 4],
        ],
    ];
    foreach ($info as $fieldName => $fieldValue) {
        $data[$fieldName] = $fieldValue;
    }
    $data['TimeZoneId'] = get_option('gravity_api_trap_timezone_id');
    $data['PortalId'] = get_option('gravity_api_trap_portal_id');
    return $data;
}

function sendApiRequest(array $data): array
{
    $primaryApiKey = get_option('gravity_api_trap_primary_api_key');
    $secondaryApiKey = get_option('gravity_api_trap_secondary_api_key');
    $url = get_option('gravity_api_trap_endpoint_url');

    $args = [
        'method' => 'POST',
        'headers' => [
            'Authorization' => 'Bearer ' . $primaryApiKey,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($data)
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        $args['headers']['Authorization'] = 'Bearer ' . $secondaryApiKey;
        $response = wp_remote_post($url, $args);
    }

    if (is_wp_error($response)) {
        // Display an error message to the user
        add_settings_error('gravity-api-trap', 'api_request_error', 'API request failed: ' . $response->get_error_message(), 'error');
    } elseif ($response['response']['code'] !== 200) {
        // Display an error message to the user
        add_settings_error('gravity-api-trap', 'api_request_error', 'API request failed with status code ' . $response['response']['code'], 'error');
    } else {
        // Log the entire output
        error_log('API request successful: ' . print_r($response, true));
    }

    return $response;
}

?>