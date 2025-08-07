<?php
/*
    Plugin Name: EPFL-OpenID-Configuration
    Description: EPFL OpenID-Configuration
    Version:     1.0.0
    Author:      EPFL SI
*/

// Set WordPress ENVs based on system env variables with the same name
$openid_possible_env_keys = [
    'OIDC_LOGIN_TYPE',
    'OIDC_CLIENT_ID',
    'OIDC_CLIENT_SECRET',
    'OIDC_CLIENT_SCOPE',
    'OIDC_ENDPOINT_LOGIN_URL',
    'OIDC_ENDPOINT_TOKEN_URL',
    'OIDC_ENDPOINT_LOGOUT_URL', # Should usually stay empty
    'OIDC_ENDPOINT_USERINFO_URL', # Should usually stay empty
    'OIDC_ACR_VALUES',
    'OIDC_ENFORCE_PRIVACY',
    'OIDC_LINK_EXISTING_USERS',
    'OIDC_CREATE_IF_DOES_NOT_EXIST',
    'OIDC_REDIRECT_USER_BACK',
    'OIDC_REDIRECT_ON_LOGOUT',
    'OIDC_ENABLE_LOGGING',
    'OIDC_LOG_LIMIT'
];
foreach ($openid_possible_env_keys as $openid_env_key) {
    $env_value = getenv($openid_env_key);
    if ( $env_value === false ) {
        continue;
    }
    if ($env_value === "true") {
        $env_value = true;
    }
    if ($env_value === "false") {
        $env_value = false;
    }
    define($openid_env_key, $env_value);
}

// Add check for Accred plugin before validation OpenID login
// Update user data based on token
add_filter('openid-connect-generic-user-login-test', function( $result, $user_claim ) {
    do_action("openid_save_user", $user_claim);
    return $result;
}, 10, 2);

// Update OpenID login button text
add_filter('openid-connect-generic-login-button-text', function( $text ) {
    $text = __('Login EPFL');
    return $text;
});

// Set other settings values:
// - nickname_key : Sciper ID
// - identity_key : Entra given name
// - email_format : Entra given email
// - endpoint_userinfo : Set empty, otherwise user_claims are overridden and uniqueid is lost
$settings = get_option('openid_connect_generic_settings', array());
$settings['nickname_key'] = 'uniqueid';
$settings['identity_key'] = 'given_name';
$settings['email_format'] = '{email}';
$settings['endpoint_userinfo'] = '';
update_option('openid_connect_generic_settings', $settings);

// Update plugin configuration fields:
// - hide openID client secret
// - set identity_key, nickname_key, email_format and endpoint_userinfo readonly
add_filter('openid-connect-generic-settings-fields', function( $fields ) {
    unset($fields["client_secret"]);
    $fields['identity_key']['disabled'] = true;
    $fields['nickname_key']['disabled'] = true;
    $fields['email_format']['disabled'] = true;
    $fields['endpoint_userinfo']['disabled'] = true;
    return $fields;
});

$show_login_form_env = getenv('OIDC_DEBUG_SHOW_LOGIN_FORM');
if ($show_login_form_env != "true") {
    // Disable login form to force login with OpenID Connect
    function remove_login_form() {
        ?>
        <script type="text/javascript">
            (function() {
                var loginForm = document.getElementById("loginform");
                var parent = loginForm.parentNode;
                parent.removeChild(loginForm);
            })();
        </script>
        <?php
    }
    add_action('login_footer', 'remove_login_form', 99);
}