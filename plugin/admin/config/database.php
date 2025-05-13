<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => '122.114.237.95',
            'port'        => '3306',
            'database'    => '0320duanju_95_hz',
            'username'    => '0320duanju_95_hz',
            'password'    => '29Nn7m44mpM7yi22',
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = "+08:00"',
            ]) : [],
        ],
    ],
];