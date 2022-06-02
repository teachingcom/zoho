<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | Zoho's Client id for OAuth process
    |
    */
    'client_id' => env('ZOHO_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    |
    | Zoho's Client secret for OAuth process
    |
    */
    'client_secret' => env('ZOHO_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | REDIRECT URI
    |--------------------------------------------------------------------------
    |
    | this is were we should handle the OAuth tokens after registering your
    | Zoho client
    |
    */
    'redirect_uri' => env('ZOHO_REDIRECT_URI', config('app.url') . '/zoho/oauth2callback'),

    /*
    |--------------------------------------------------------------------------
    | CURRENT USER EMAIL
    |--------------------------------------------------------------------------
    |
    | Zoho's email address that will be used to interact with API
    |
    */
    'current_user_email' => env('ZOHO_CURRENT_USER_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | LOGGER CHANNEL NAME
    |--------------------------------------------------------------------------
    |
    | The SDK writes log entries for tracing and debugging. Enter the channel
    | to which you would like the SDK log entries to be written.
    |
    */
    'logger_channel' => env('ZOHO_LOGGER_CHANNEL', 'default'),

    /*
    |--------------------------------------------------------------------------
    | DATABASE CONNECTION NAME
    |--------------------------------------------------------------------------
    |
    | Enter the database connection name where Zoho OAuth tokens are to be
    | persisted.
    |
    */
    'db_connection_name' => env('ZOHO_DB_CONNECTION_NAME', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | DATACENTER NAME
    |--------------------------------------------------------------------------
    |
    | Enter the name of the datacenter you wish to communicate with. There are
    | regional datacenters and each offers a developer, sandbox, and production
    | environment:
    |
    | - United States: Developer => `us_dev`, Sandbox => `us_sdb`, Production => `us_prd`
    | - Australia: Developer => `au_dev`, Sandbox => `au_sdb`, Production => `au_prd`
    | - China: Developer => `cn_dev`, Sandbox => `cn_sdb`, Production => `cn_prd`
    | - Japan: Developer => `jp_dev`, Sandbox => `jp_sdb`, Production => `jp_prd`
    | - India: Developer => `in_dev`, Sandbox => `in_sdb`, Production => `in_prd`
    | - United Kingdom: Developer => `uk_dev`, Sandbox => `uk_sdb`, Production => `uk_prd`
    |
    */
    'datacenter_name' => env('ZOHO_DATACENTER_NAME', 'us_prd'),

    /*
    |--------------------------------------------------------------------------
    | Zoho Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Zoho's views, such as the callback
    | verification screen, will be available from. You're free to tweak
    | this path according to your preferences and application design.
    |
    */
    'oauth_scope' => env('ZOHO_OAUTH_SCOPE', 'aaaserver.profile.READ,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL'),

    /*
    |--------------------------------------------------------------------------
    | AUTO-REFRESH FIELDS
    |--------------------------------------------------------------------------
    |
    | Whether you prefer the SDK to automatically refresh fields.
    |
    */
    'auto_refresh_fields' => env('ZOHO_AUTO_REFRESH_FIELDS', false),

    /*
    |--------------------------------------------------------------------------
    | PICK LIST VALIDATION
    |--------------------------------------------------------------------------
    |
    | Whether you prefer the SDK to validate pick lists.
    |
    */
    'pick_list_validation' => env('ZOHO_PICK_LIST_VALIDATION', true),

    /*
    |--------------------------------------------------------------------------
    | OAUTH GRANT TOKEN
    |--------------------------------------------------------------------------
    |
    | Provide an OAuth grant token if you have one.
    |
    */
    'grant_token' => env('ZOHO_GRANT_TOKEN'),
];
