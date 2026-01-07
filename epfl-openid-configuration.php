<?php
/*
    Plugin Name: EPFL-OpenID-Configuration
    Description: EPFL OpenID-Configuration
    Version:     1.0.0
    Author:      EPFL SI
*/

function plugin_activate_openid_configuration() {
    // Get settings from database
    $settings = get_option('openid_connect_generic_settings', array());

    // Set plugin options based on system env variables, if defined
    $openid_possible_env_keys = [
        'OIDC_LOGIN_TYPE',
        'OIDC_CLIENT_ID',
        'OIDC_CLIENT_SECRET', # Should stay empty for single page app (SPA)
        'OIDC_SCOPE',
        'OIDC_ENDPOINT_LOGIN',
        'OIDC_ENDPOINT_USERINFO',
        'OIDC_ENDPOINT_TOKEN',
        'OIDC_ENDPOINT_END_SESSION', # Should stay empty
        'OIDC_ACR_VALUES',
        'OIDC_ENFORCE_PRIVACY',
        'OIDC_LINK_EXISTING_USERS',
        'OIDC_CREATE_IF_DOES_NOT_EXIST',
        'OIDC_REDIRECT_USER_BACK',
        'OIDC_REDIRECT_ON_LOGOUT',
        'OIDC_ENABLE_LOGGING',
        'OIDC_LOG_LIMIT',
        'OIDC_HIDE_LOGIN_FORM'
    ];
    foreach ($openid_possible_env_keys as $openid_env_key) {
        $option_value = getenv($openid_env_key);
        if ($option_value === false ) {
            continue;
        }
        if ($option_value === "true") {
            $option_value = true;
        }
        if ($option_value === "false") {
            $option_value = false;
        }
        $option_key = strtolower(str_replace('OIDC_', '', $openid_env_key));
        error_log($option_key . " => " . $option_value);
        $settings[$option_key] = $option_value;
    }

    // Set other settings values:
    // - nickname_key : Sciper ID
    // - identity_key : Entra given name
    // - email_format : Entra given email
    $settings['nickname_key'] = 'uniqueid';
    $settings['identity_key'] = 'given_name';
    $settings['email_format'] = '{email}';
    update_option('openid_connect_generic_settings', $settings);
}

// Swicth to PKCE workflow if no secret has been provided : used for single page apps (SAP) configuration
add_filter('openid-connect-generic-auth-url', function( $url ) {
    if (get_option('client_secret') !== false) {
        return $url;
    }
    // Generate a random string for the code challenge
    $code_challenge = bin2hex(random_bytes(64));
    $hash = hash('sha256', $code_challenge, true);
    $code_challenge = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    $url.= '&code_challenge=' . $code_challenge;
    session_start();
    $_SESSION['code_verifier'] = $code_challenge;
    session_write_close();
    return $url;
});
add_filter('openid-connect-generic-alter-request', function( $request, $operation ) {
    if ( $operation != 'get-authentication-token' && $operation != 'refresh-token' ) {
        return $request;
    }
    if (get_option('client_secret') !== false) {
        return $request;
    }
    unset($request['body']['client_secret']);
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
    $request['headers']['Origin'] = home_url();
    session_start();
    $request['body']['code_verifier'] = $_SESSION['code_verifier'];
    return $request;
}, 10, 2);

// Save access_token to the session, to be able to get authorizations from api.epfl.ch
add_filter('openid-connect-modify-token-response-before-validation', function($token_response ) {
    if ( is_wp_error( $token_response ) ) {
        return $token_response;
    }
    if (isset( $token_response['access_token'])) {
        session_start();
        $_SESSION['access_token'] = $token_response['access_token'];
        session_write_close();
    }
    return $token_response;
});

// Add check for Accred plugin before validation OpenID login
// Update user data based on token
add_filter('openid-connect-generic-user-login-test', function( $result, $user_claim ) {
    session_start();
    $access_token = $_SESSION['access_token'];
    do_action("openid_save_user", $access_token, $user_claim);
    return $result;
}, 10, 2);

// Update OpenID login button text
add_filter('openid-connect-generic-login-button-text', function( $text ) {
    $text = __('Login EPFL');
    return $text;
});

// Update plugin configuration fields:
// - hide openID client secret
// - set identity_key, nickname_key, email_format and endpoint_userinfo readonly
// - add field hide login form
add_filter('openid-connect-generic-settings-fields', function( $fields ) {
    unset($fields["client_secret"]);
    $fields['identity_key']['disabled'] = true;
    $fields['nickname_key']['disabled'] = true;
    $fields['email_format']['disabled'] = true;
    $fields['hide_login_form'] = array(
        'title' => __('Hide login form'),
        'description' => __('Prevent user to log in with Wordpress user/password'),
        'type' => 'checkbox',
        'section' => 'authorization_settings',
    );
    // $fields['endpoint_userinfo']['disabled'] = true;
    return $fields;
});

$settings = get_option('openid_connect_generic_settings', array());
if (isset($settings['hide_login_form']) && $settings['hide_login_form'] == true) {
    // Disable login form to force login with OpenID Connect
    function hide_login_form() {
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
    add_action('login_footer', 'hide_login_form', 99);
}

/************ Activation ********************/
register_activation_hook(__FILE__, 'plugin_activate_openid_configuration');