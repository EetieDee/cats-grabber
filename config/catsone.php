<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'api_endpoint_add_job' => env('CATSONE_API_ENDPOINT_ADD_JOB'),
    'api_endpoint_add_custom_field' => env('CATSONE_API_ENDPOINT_ADD_CUSTOM_FIELD'),
    'api_endpoint_add_attachment' => env('CATSONE_API_ENDPOINT_ADD_ATTACHMENT'),
    'schaal_id' => env('CATSONE_SCHAAL_ID'),
    'token' => env('CATSONE_TOKEN')
];
