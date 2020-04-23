<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Shop\Customers\Customer::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'jaeger' => [
        'agent' => 'jaeger:6831',
        'sampler' => 'probabilistic',
    ],

    'micro' => [
        'api_gateway' => env('MICRO_API_GATEWAY', 'http://laracom-micro-api:8080'),
        'timeout' => env('MICRO_TIMEOUT', 3.0),
        'jwt_key' => env('MICRO_JWT_KEY', 'laracomUserTokenKeySecret'),
        'jwt_algorithms' => env('MICRO_JWT_ALGORITHMS', 'HS256'),
        'broker_host' => env('MICRO_BROKER_HOST', 'laracom-nats'),
        'broker_port' => env('MICRO_BROKER_PORT', '4222'),
        'broker_auth' => env('MICRO_BROKER_AUTH', true),
        'broker_user' => env('MICRO_BROKER_USER', 'nats'),
        'broker_pass' => env('MICRO_BROKER_USER', 'nats'),
    ]
];
