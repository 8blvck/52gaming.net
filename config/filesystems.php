<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'posts' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/posts',
        ],

        'games' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/games',
        ],

        'games_thumb' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/games/thumb',
        ],

        'images' => [
            'driver' => 'local',
            'root'   => public_path() . '/img',
        ],

        'icons' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/icons',
        ],

        'users' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/users',
        ],

        'works' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/works',
        ],

        'orders' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/order',
        ],

        'orders_types' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/order/types',
        ],

        'medals' => [
            'driver' => 'local',
            'root'   => public_path() . '/img/dota/ranks',
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

    ],

];
