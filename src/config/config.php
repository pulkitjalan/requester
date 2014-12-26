<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Use SSL
    |--------------------------------------------------------------------------
    |
    | If not protocol is provided with the url this option will cause 'https'
    | to be used, false will cause 'http' to be used
    |
    */
    'secure' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable to disable SSL verification
    |--------------------------------------------------------------------------
    |
    | Guzzle enables by default
    |
    */
    'verify' => true,

    /*
    |--------------------------------------------------------------------------
    | Make request asynchronously (see README for more info)
    |--------------------------------------------------------------------------
    |
    | Advisable to leave it false and use the async method per request
    |
    */
    'async' => false,

    /*
    |--------------------------------------------------------------------------
    | Retry options
    |--------------------------------------------------------------------------
    |
    | Uses the guzzle retry subscriber
    | https://github.com/guzzle/retry-subscriber
    |
    */
    'retry' => [
        /*
        |--------------------------------------------------------------------------
        | Number of times to retry
        |--------------------------------------------------------------------------
        |
        | False to disable retry
        |
        */
        'times' => 5,

        /*
        |--------------------------------------------------------------------------
        | Delay between retry requests
        |--------------------------------------------------------------------------
        |
        | In milliseconds
        |
        */
        'delay' => 10,

        /*
        |--------------------------------------------------------------------------
        | Retry on http code
        |--------------------------------------------------------------------------
        |
        | Which http response codes will trigger a retry
        |
        */
        'on' => [500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging options
    |--------------------------------------------------------------------------
    |
    | Log guzzle requests using the log-subscriber
    | https://github.com/guzzle/log-subscriber
    |
    */
    'log' => [
        /*
        |--------------------------------------------------------------------------
        | Enable logging
        |--------------------------------------------------------------------------
        */
        'enabled' => false,

        /*
        |--------------------------------------------------------------------------
        | Logging file
        |--------------------------------------------------------------------------
        |
        | Logs to Laravels default (storage_path().'/logs/laravel.log')
        | Modify to log to a different file
        |
        */
        'file' => '',

        /*
        |--------------------------------------------------------------------------
        | Logging format
        |--------------------------------------------------------------------------
        |
        | The Log Subscriber currently supports [CLF, DEBUG, SHORT]
        | See https://github.com/guzzle/log-subscriber for more info
        | Can also set a custom format
        |
        */
        'format' => 'CLF',
    ],

];
