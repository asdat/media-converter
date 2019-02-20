<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'default' => env('QUEUE_DRIVER', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 28830,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANS_HOST', 'localhost'),
            'queue' => 'default',
            'retry_after' => 90,
        ],

        /*'gearman' => [
            'driver' => 'gearman',
            'host'   => env('GEARMAN_HOST', 'localhost'),
            'queue'  => 'default',
            'port'   => 4730,
            'timeout' => 1000, //milliseconds
        ],*/
        
        'nsq' => [
            'driver' => 'nsq',
            'queue' => 'default',

            /*
             |--------------------------------------------------------------------------
             | Nsqd host  nsqlookup host
             |--------------------------------------------------------------------------
             |
             */
            'connection'       => [
                'nsqd_url' => array_filter(explode(',', env('NSQSD_URL', '127.0.0.1:9150'))),
                'nsqlookup_url' => array_filter(explode(',', env('NSQLOOKUP_URL', '127.0.0.1:4161'))),
            ],

            /*
              |--------------------------------------------------------------------------
              | Nsq Config
              |--------------------------------------------------------------------------
              |
              */
            'options' => [
                //Update RDY state (indicate you are ready to receive N messages)
                'rdy' => 1,
                'cl'  => 1
            ],

            /*
              |--------------------------------------------------------------------------
              | Nsq identify
              |--------------------------------------------------------------------------
              |
              */
            'identify' => [
                'user_agent' => 'nsq-client',
            ],

            /*
              |--------------------------------------------------------------------------
              | Swoole Client Params
              |--------------------------------------------------------------------------
              |
              */
            'client' => [
                'options' => [
                    'open_length_check'     => true,
                    'package_max_length'    => 2048000,
                    'package_length_type'   => 'N',
                    'package_length_offset' => 0,
                    'package_body_offset'   => 4
                ]
            ],
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => 'your-public-key',
            'secret' => 'your-secret-key',
            'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
            'queue' => 'your-queue-name',
            'region' => 'us-east-1',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'dsn' => env('RABBITMQ_DSN', null),

            /*
             * Could be one a class that implements \Interop\Amqp\AmqpConnectionFactory for example:
             *  - \EnqueueAmqpExt\AmqpConnectionFactory if you install enqueue/amqp-ext
             *  - \EnqueueAmqpLib\AmqpConnectionFactory if you install enqueue/amqp-lib
             *  - \EnqueueAmqpBunny\AmqpConnectionFactory if you install enqueue/amqp-bunny
             */
            'factory_class' => Enqueue\AmqpLib\AmqpConnectionFactory::class,
            'host' => env('RABBITMQ_HOST', '127.0.0.1'),
            'port' => env('RABBITMQ_PORT', 5672),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'login' => env('RABBITMQ_LOGIN', 'rabbitmq'),
            'password' => env('RABBITMQ_PASSWORD', 'rabbitmq'),
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'retry_after' => 28830,

            'options' => [
                'exchange' => [
                    'name' => env('RABBITMQ_EXCHANGE_NAME'),

                    /*
                     * Determine if exchange should be created if it does not exist.
                     */
                    'declare' => env('RABBITMQ_EXCHANGE_DECLARE', true),

                    /*
                     * Read more about possible values at https://www.rabbitmq.com/tutorials/amqp-concepts.html
                     */
                    'type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
                    'passive' => env('RABBITMQ_EXCHANGE_PASSIVE', false),
                    'durable' => env('RABBITMQ_EXCHANGE_DURABLE', true),
                    'auto_delete' => env('RABBITMQ_EXCHANGE_AUTODELETE', false),
                    'arguments' => env('RABBITMQ_EXCHANGE_ARGUMENTS'),
                ],
                'queue' => [

                    /*
                     * Determine if queue should be created if it does not exist.
                     */
                    'declare' => env('RABBITMQ_QUEUE_DECLARE', true),

                    /*
                     * Determine if queue should be binded to the exchange created.
                     */
                    'bind' => env('RABBITMQ_QUEUE_DECLARE_BIND', true),

                    /*
                     * Read more about possible values at https://www.rabbitmq.com/tutorials/amqp-concepts.html
                     */
                    'passive' => env('RABBITMQ_QUEUE_PASSIVE', false),
                    'durable' => env('RABBITMQ_QUEUE_DURABLE', true),
                    'exclusive' => env('RABBITMQ_QUEUE_EXCLUSIVE', false),
                    'auto_delete' => env('RABBITMQ_QUEUE_AUTODELETE', false),
                    'arguments' => env('RABBITMQ_QUEUE_ARGUMENTS'),
                ],
            ],

            /*
             * Determine the number of seconds to sleep if there's an error communicating with rabbitmq
             * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
             */

            'sleep_on_error' => env('RABBITMQ_ERROR_SLEEP', 5),

            /*
             * Optional SSL params if an SSL connection is used
             * Using an SSL connection will also require to configure your RabbitMQ to enable SSL. More details can be founds here: https://www.rabbitmq.com/ssl.html
             */

            'ssl_params' => [
                'ssl_on' => env('RABBITMQ_SSL', false),
                'cafile' => env('RABBITMQ_SSL_CAFILE', null),
                'local_cert' => env('RABBITMQ_SSL_LOCALCERT', null),
                'local_key' => env('RABBITMQ_SSL_LOCALKEY', null),
                'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                'passphrase' => env('RABBITMQ_SSL_PASSPHRASE', null),
            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];