<?php

return [
    'DB_TYPE'     => '_DB_TYPE_',
    'DB_HOST'     => '_DB_HOST_',
    'DB_USERNAME' => '_DB_USERNAME_',
    'DB_PASSWORD' => '_DB_PASSWORD_',
    'DB_NAME'     => '_DB_NAME_',
    'DB_PREFIX'   => '_DB_PREFIX_',
    'P_CONNECT'   => false,
    'shared' => [
        'Request' => \ForkBB\Core\Request::class,
        'DBLoader' => [
            'class' => \ForkBB\Core\DBLoader::class,
            'db_host'     => '%DB_HOST%',
            'db_username' => '%DB_USERNAME%',
            'db_password' => '%DB_PASSWORD%',
            'db_name'     => '%DB_NAME%',
            'db_prefix'   => '%DB_PREFIX%',
            'p_connect'   => '%P_CONNECT%',
        ],
        'DB' => [
            'factory method' => '@DBLoader:load',
            'type' => '%DB_TYPE%',
        ],
        'Install' => [
            'class' => \ForkBB\Core\Install::class,
            'request' => '@Request',
        ],
        'firstAction' => '@Install:start',
    ],
    'multiple'  => [],
];