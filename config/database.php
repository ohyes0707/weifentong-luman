<?php

return [

    'fetch' => PDO::FETCH_CLASS,


    'default' => env('DB_CONNECTION', 'mysql'),


    'connections' => [

        'testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', base_path('database/database.sqlite')),
            'prefix' => env('DB_PREFIX', ''),
        ],

     // 'mysql' => [
     //     'driver' => 'mysql',
     //     'read' => [
     //         [
     //             'host' => env('DB_HOST', '192.168.7.34'),
     //             'port' => env('DB_PORT', 3306),
     //             'database' => env('DB_DATABASE', 'wft'),
     //             'username' => env('DB_USERNAME', 'Hdt8'),
     //             'password' => env('DB_PASSWORD', 'mCVzLxs70@'),
     //         ],
     //         [
     //             'host' => env('DB_HOST', '192.168.7.34'),
     //             'port' => env('DB_PORT', 3306),
     //             'database' => env('DB_DATABASE', 'wft'),
     //             'username' => env('DB_USERNAME', 'Hdt8'),
     //             'password' => env('DB_PASSWORD', 'mCVzLxs70@'),
     //         ]
     //     ],
     //     'write' => [
     //         'host' => env('DB_HOST', '192.168.7.34'),
     //         'port' => env('DB_PORT', 3306),
     //         'database' => env('DB_DATABASE', 'wft'),
     //         'username' => env('DB_USERNAME', 'Hdt8'),
     //         'password' => env('DB_PASSWORD', 'mCVzLxs70@'),
     //     ],

     //     'charset' => 'utf8',
     //     'collation' => 'utf8_unicode_ci',
     //     'prefix' => env('DB_PREFIX', ''),
     //     'timezone' => env('DB_TIMEZONE', '+08:00'),
     //     'strict' => false,
     // ],

  'mysql' => [
      'driver' => 'mysql',
      'read' => [
          [
              'host' => env('DB_HOST', '192.168.8.222'),
              'port' => env('DB_PORT', 3306),
              'database' => env('DB_DATABASE', 'wft'),
              'username' => env('DB_USERNAME', 'wft'),
              'password' => env('DB_PASSWORD', 'wft@sufun'),
          ],
          [
              'host' => env('DB_HOST', '192.168.8.222'),
              'port' => env('DB_PORT', 3306),
              'database' => env('DB_DATABASE', 'wft'),
              'username' => env('DB_USERNAME', 'wft'),
              'password' => env('DB_PASSWORD', 'wft@sufun'),
          ]
      ],
      'write' => [
          'host' => env('DB_HOST', '192.168.8.222'),
          'port' => env('DB_PORT', 3306),
          'database' => env('DB_DATABASE', 'wft'),
          'username' => env('DB_USERNAME', 'wft'),
          'password' => env('DB_PASSWORD', 'wft@sufun'),
      ],

      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix' => env('DB_PREFIX', ''),
      'timezone' => env('DB_TIMEZONE', '+08:00'),
      'strict' => false,
  ],


    ],


    'migrations' => 'migrations',

    'redis' => [

        'client' => 'predis',

        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host'     => env('REDIS_HOST', '192.168.8.222'),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'password' => env('REDIS_PASSWORD', null),
        ],

        
//        'default' => [
//            'host'     => env('REDIS_HOST', '211.159.220.192'),
//            'port'     => env('REDIS_PORT', 6380),
//            'database' => env('REDIS_DATABASE', 0),
//            'password' => env('REDIS_PASSWORD', 'j7<#d.9KhLaX)>P'),
//        ],
        
    ],

];

