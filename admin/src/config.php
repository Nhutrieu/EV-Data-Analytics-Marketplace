<?php
return [
  'db' => [
    'host' => getenv('DB_HOST') ?: 'db',   // trùng với service name trong docker-compose
    'user' => getenv('DB_USER') ?: 'dmuser',
    'pass' => getenv('DB_PASS') ?: 'dmpass',
    'name' => getenv('DB_NAME') ?: 'data_marketplace',
    'port' => getenv('DB_PORT') ?: 3306,
  ],
  'encrypt_key' => getenv('ENCRYPT_KEY') ?: 'change_this_32_chars_min',
  'provider_share_pct' => 70
];
