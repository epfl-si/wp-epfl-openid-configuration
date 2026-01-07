This plugin permits to automatically configure the OpenID Connect Generic Client to work with the EPFL Accred Entra plugin

It needs the [OpenID Connect Generic Client](https://github.com/epfl-si/openid-connect-generic) and the [EPFL Accred Entra](https://github.com/epfl-si/wordpress.plugin.accred.entra) plugins to work.

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
        OIDC_LOGIN_TYPE: "button" # Set to "auto" instead to not show login form and directly be redirected to OIDC authentication
        OIDC_CLIENT_ID: "<YOUR_CLIENT_ID>"
        OIDC_SCOPE: "openid profile email <YOUR_CLIENT_ID>/.default"
        OIDC_ENDPOINT_LOGIN: "https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/authorize"
        OIDC_ENDPOINT_TOKEN: "https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/token"
        OIDC_ENDPOINT_USERINFO: "https://api.epfl.ch/v1/oidc/userinfo"
        OIDC_ENFORCE_PRIVACY: true
        OIDC_LINK_EXISTING_USERS: true
        OIDC_CREATE_IF_DOES_NOT_EXIST: false
        #OIDC_HIDE_LOGIN_FORM: true   <= Uncomment this to prevent user to log in with Wordpress user/password
```

## Usage in test / production ##

Use the Wordpress Operator to add this option for `openid_connect_generic_settings` key in the database.

```php
array(
    'login_type' => 'button', # Set to 'auto' instead to not show login form and directly be redirected to OIDC authentication
    'client_id' => '<YOUR_CLIENT_ID>',
    'scope' => 'openid profile email <YOUR_CLIENT_ID>/.default',
    'endpoint_login' => 'https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/authorize',
    'endpoint_token' => 'https://login.microsoftonline.com/f6c2556a-c4fb-4ab1-a2c7-9e220df11c43/oauth2/v2.0/token',
    'endpoint_userinfo' => 'https://api.epfl.ch/v1/oidc/userinfo',
    'enforce_privacy' => '1',
    'link_existing_users' => '1',
    'create_if_does_not_exist' => '',
    'client_secret' => '',
    'identity_key' => 'given_name',
    'nickname_key' => 'uniqueid',
    'email_format' => '{email}',
    'hide_login_form' => '', # Set to '1' instead to prevent user to log in with Wordpress user/password
)
```