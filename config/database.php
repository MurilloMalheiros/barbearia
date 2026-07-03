<?php

return [
    'driver'   => 'pgsql',
    'host'     => $_ENV['DB_HOST']    ?? '127.0.0.1',
    'port'     => $_ENV['DB_PORT']    ?? '5432',
    'dbname'   => $_ENV['DB_NAME']    ?? 'malharia_db',
    'user'     => $_ENV['DB_USER']    ?? '',
    'password' => $_ENV['DB_PASS']    ?? '',
    'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8',
    'schema'   => $_ENV['DB_SCHEMA']  ?? 'barbearia',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
