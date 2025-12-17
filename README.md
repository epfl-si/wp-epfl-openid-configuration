This plugin permits to automatically configure the OpenID Connect Generic Client to work with the EPFL Accred Entra plugin

It needs the `OpenID Connect Generic Client` and the `EPFL Accred Entra` plugins to work.

## Functionalities ##

This plugin add multiple filters definied in OpenID Connect Generic Client to:
- permit PKCE authentification for single page applications
- call the EPFL Accred Entra plugin to verify roles and update users
- initialize the `openid_connect_generic_settings` option in database based on environment variables for developpement

## Usage in developpement ##

Update the Wordpress docker-compose to add these environment variables:

```yaml
  php:
    [...]
    environment:
        OIDC_LOGIN_TYPE: "button"
        OIDC_CLIENT_ID: "<YOUR_CLIENT_ID>"
        OIDC_SCOPE: "openid profile email <YOUR_CLIENT_ID>/.default"
        OIDC_ENDPOINT_LOGIN: "https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/authorize"
        OIDC_ENDPOINT_TOKEN: "https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/token"
        OIDC_ENFORCE_PRIVACY: true
        OIDC_LINK_EXISTING_USERS: true
        OIDC_CREATE_IF_DOES_NOT_EXIST: false
        #OIDC_DEBUG_SHOW_LOGIN_FORM: true   <= Uncomment this to show the old login button, for debug purpose
```

## Usage in test / production ##

Use the Wordpress Operator to add this option for `openid_connect_generic_settings` key in the database:

```php
array(
    'login_type' => 'button',
    'client_id' => '<YOUR_CLIENT_ID>',
    'client_secret' => '',
    'scope' => 'openid profile email <YOUR_CLIENT_ID>/.default',
    'endpoint_login' => 'https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/authorize',
    'endpoint_token' => 'https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/token',
    'enforce_privacy' => '1',
    'link_existing_users' => '1',
    'identity_key' => 'given_name',
    'nickname_key' => 'uniqueid',
    'email_format' => '{email}',
    'endpoint_userinfo' => '',
    'endpoint_end_session' => '',
    'acr_values' => '',
    'no_sslverify' => '',
    'http_request_timeout' => '',
    'displayname_format' => '',
    'identify_with_username' => '',
    'state_time_limit' => '',
    'alternate_redirect_uri' => '',
    'token_refresh_enable' => '',
    'create_if_does_not_exist' => '',
    'redirect_user_back' => '',
    'redirect_on_logout' => '',
    'enable_logging' => '',
    'log_limit' => '',
)
```