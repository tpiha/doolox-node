<?php  
/* 
Plugin Name: Doolox Plugin
Plugin URI: https://www.doolox.com/ 
Version: 1.0
Author: <a href="https://www.doolox.com">Doolox</a>
Description: Doolox is a free Open Source WordPress management tool and website builder available both as a SaaS and for download. It uses Doolox Plugin to login users to multiple WordPress websites over SSL without storing credentials in database. Give it a try, it's free!
*/

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/phpseclib' . PATH_SEPARATOR . dirname(__FILE__));
include('Crypt/RSA.php');
include('doolox_options.php');

define('PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD5bNEwSRE697Hj+kov3LHhZ7Ao
YvZ+TaiDBFhxhHT12Wo6EnVoW/bxPEsyMCDZl/AMCjaCzqAs46cRxqTcZbXe4Uj5
gZ5gw01KVdNjiglPkk20DBT4I90hyRdOhapybYtXCEk2x6/PZMsn4fDLM800RTyJ
EIImabypZ84PQNtP+wIDAQAB
-----END PUBLIC KEY-----');

$__doolox_connected = false;

function doolox_init() {
    connection_check();

    $data = $_POST['data'];
    if (strlen($data)) {
        $pk = strlen(get_option('dooloxpk')) ? get_option('dooloxpk') : PUBLIC_KEY;

        $rsa = new Crypt_RSA();
        $rsa->loadKey($pk);
        $data = urldecode($data);
        $data = base64_decode($data);
        $data = $rsa->decrypt($data);
        $data = base64_decode($data);
        $data = json_decode($data);

        $action  = $data->action;

        if ($action == 'login') {
            doolox_login($data);
        }
        else {
            doolox_connect($data);
        }
    }
}

function connection_check() {
    global $__doolox_connected;
    if (strlen(get_option('dooloxrnd')) && strlen(get_option('dooloxpk')) && strlen(get_option('dooloxid')) && strlen(get_option('dooloxuser'))) {
        $__doolox_connected = true;
    }
}

function connected() {
    global $__doolox_connected;
    return $__doolox_connected;
}

function doolox_connect($data) {
    if (!connected()) {
        $rand = get_option('dooloxrnd');
        update_option('dooloxrnd', (string) $data->rand);
        update_option('dooloxid', (string) $data->id);
        update_option('dooloxuser', (string) $data->username);
        update_option('dooloxpk', (string) $data->public_key);

        if ($rand != $data->rand && get_user_by('login', $data->username)) {
            $user = get_user_by('login', $data->username);
            if (!is_wp_error($user)) {
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);

                $redirect_to = user_admin_url();
                doolox_connected_callback($data->url);
                wp_safe_redirect($redirect_to);
                exit();
            }
        }
    }
    else {
        $redirect_to = user_admin_url();
        wp_safe_redirect($redirect_to);
    }
}

function doolox_connected_callback($url) {
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL            => $url . '/connected/',
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => array(
            'id' => get_option('dooloxid'),
        ),
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
}

function doolox_login($data) {
    $rand = get_option('dooloxrnd');
    $id = get_option('dooloxid');
    $username = get_option('dooloxuser');
    if ($rand != $data->rand && $id == $data->id && get_user_by('login', $username)) {
        update_option('dooloxrnd', $data->rand);

        $user = get_user_by('login', $username);

        if (!is_wp_error($user)) {
            wp_clear_auth_cookie();
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);

            $redirect_to = user_admin_url();
            wp_safe_redirect($redirect_to);
            exit();
        }
    }
}

function doolox_admin_notice() {
    if (!strlen(get_option('dooloxrnd'))) {
        echo '<div class="error">
                <p>This WordPress website is still not connected to your Doolox account. Plaese connect it or deactivate the plugin.</p>
            </div>';
    }
}

function doolox_deactivate() {
    delete_option('dooloxrnd');
    delete_option('dooloxid');
    delete_option('dooloxuser');
    delete_option('dooloxpk');
}

function doolox_add_pages() {
    add_options_page(__('Doolox','menu-doolox'), __('Doolox','doolox-settings'), 'manage_options', 'doolox-settings', 'doolox_settings_page');
}

function register_doolox_settings() {
    register_setting( 'doolox-options', 'dooloxpk'); 
    add_settings_section('doolox-main', 'Doolox Settings', 'plugin_section_text', 'doolox-settings');
    add_settings_field('doolox-field', 'Plugin Text Input', 'dooloxpk', 'doolox-settings', 'doolox-main');
} 


// Add actions
add_action('login_init', 'doolox_init');
add_action( 'admin_notices', 'doolox_admin_notice' );
register_deactivation_hook(__FILE__, 'doolox_deactivate');
add_action('admin_menu', 'doolox_add_pages');
add_action( 'admin_init', 'register_doolox_settings' );

?>