<?php

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: 3306,
        'name' => getenv('DB_NAME') ?: 'data_marketplace',
        'user' => getenv('DB_USER') ?: 'dmuser',
        'pass' => getenv('DB_PASS') ?: 'dmpass',
    ],

    // 👉 Thêm phần này
    'provider_db' => [
        'host' => getenv('PROVIDER_DB_HOST') ?: 'db_provider',
        'port' => getenv('PROVIDER_DB_PORT') ?: 3306,
        'name' => getenv('PROVIDER_DB_NAME') ?: 'ev_data_marketplace',
        'user' => getenv('PROVIDER_DB_USER') ?: 'ev_user',
        'pass' => getenv('PROVIDER_DB_PASS') ?: 'ev_pass',
    ],
];
