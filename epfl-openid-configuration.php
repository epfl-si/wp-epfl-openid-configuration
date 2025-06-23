<?php
/*
    Plugin Name: EPFL-OpenID-Configuration
    Description: EPFL OpenID-Configuration
    Version:     1.0.0
    Author:      EPFL SI (originally Geeky Software)
*/

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

$openid_possible_env_keys = [
    'OIDC_LOGIN_TYPE',
    'OIDC_CLIENT_ID',
    'OIDC_CLIENT_SECRET',
    'OIDC_CLIENT_SCOPE',
    'OIDC_ENDPOINT_LOGIN_URL',
    'OIDC_ENDPOINT_USERINFO_URL',
    'OIDC_ENDPOINT_TOKEN_URL',
    'OIDC_ENDPOINT_LOGOUT_URL',
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
    if ( getenv($openid_env_key) !== false ) {
        define($openid_env_key, getenv($openid_env_key));
    }
}

add_filter('openid-connect-generic-login-button-text', function( $text ) {
    $text = __('Login EPFL');
    return $text;
});

add_filter('openid-connect-generic-settings-fields', function( $fields ) {
    unset($fields["client_secret"]);
    $fields['identity_key']['disabled'] = true;
    $fields['nickname_key']['disabled'] = true;
    return $fields;
});

$settings = get_option('openid_connect_generic_settings', array());
$settings['nickname_key'] = 'name';
$settings['identity_key'] = 'uniqueid';
update_option('openid_connect_generic_settings', $settings);

add_action('login_footer', 'remove_login_form', 99);
