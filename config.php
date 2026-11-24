<?php

return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'u963944241_rqn',
        'user'    => getenv('DB_USER') ?: 'u963944241_rqn_admin',
        'pass'    => getenv('DB_PASS') ?: '*SWIN^H6p',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'ResQNow',
        'url'  => getenv('APP_URL') ?: 'https://localhost/resqnow/public',
    ],
    'session' => [
        'name' => 'resqnow_session',
    ],
];
