<?php

$settings = [
    'database' => [
        'mongo' => [
            'host'     => '[dockerName]_mongo',
            'port'     => '27017',
            'username' => '',
            'password' => '',
            'dbname'   => '[dbName]',
        ],
    ],
    'application' => [
        'repoDir'        => __DIR__ . '/../../app/repositories/',
        'servicesDir'    => __DIR__ . '/../../app/services/',
        'viewsDir'       => __DIR__ . '/../../app/views/',
        'modelsDir'      => __DIR__ . '/../../app/models/',
        'controllersDir' => __DIR__ . '/../../app/controllers/',
        'libraryDir'     => __DIR__ . '/../../app/library/',
        'baseUri'        => '[baseUrl]',
    ],
    
];