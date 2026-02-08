<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Paths
    |--------------------------------------------------------------------------
    |
    | The paths for which CORS will be applied. By default, it will apply
    | to any route under the "api/*" path.
    |
    */

    'paths' => ['api/*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Here you may specify the HTTP methods that are allowed to be used when
    | making requests to your API.
    |
    */

    'allowed_methods' => ['GET, POST, PUT, DELETE, OPTIONS'], // يمكنك تخصيصه مثل ['GET', 'POST', 'PUT', 'DELETE']

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define the origins that are allowed to access your API.
    | Set it to "*" to allow all origins, or specify the allowed origins.
    |
    */

    'allowed_origins' => ['*'], // يمكنك تخصيصها لتحديد الأصوات المسموح بها

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the headers that are allowed to be sent when making
    | requests to your API.
    |
    */

    'allowed_headers' => ['Content-Type, X-Requested-With, X-Auth-Token'], // يمكنك تخصيصها كما تشاء

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | If you need to expose any headers to the client, specify them here.
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | The maximum time, in seconds, that the results of a preflight request
    | can be cached by the browser.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Whether or not the API supports credentials.
    |
    */

    'supports_credentials' => false,
];
