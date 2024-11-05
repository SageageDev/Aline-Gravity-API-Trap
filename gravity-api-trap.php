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
defined( 'ABSPATH' ) || die( 'No entry' );

define( 'GF_API_TRAP_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_API_Trap_Bootstrap', 'load' ), 5 );
 
class GF_API_Trap_Bootstrap {
 
    public static function load() {
 
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
 
        require_once( 'class-gfapitrap.php' );
 
        GFAddOn::register( 'GFAPITrap' );
    }
 
}
 
function gf_api_trap() {
    return GFAPITrap::get_instance();
}

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
    register_setting( 'gravity-api-trap', 'gravity_api_trap_primary_api_key' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_secondary_api_key' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_endpoint_url' );
    register_setting( 'gravity-api-trap', 'gravity_api_trap_timezone_id' );
}
