<?php

declare(strict_types=1);

/**
 * Configuração principal da aplicação
 * Carrega variáveis do .env e define constantes globais
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
try {
    $dotenv->load();
    $dotenv->required([
        'APP_ENV', 'APP_URL', 'APP_SECRET',
        'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
    ]);
} catch (\Dotenv\Exception\InvalidPathException $e) {
    http_response_code(500);
    die('Arquivo .env não encontrado. Configure o ambiente antes de prosseguir.');
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');

define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

define('BASE_PATH',    dirname(__DIR__));
define('APP_PATH',     BASE_PATH . '/app');
define('CONFIG_PATH',  BASE_PATH . '/config');
define('PUBLIC_PATH',  BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH',  STORAGE_PATH . '/uploads');
define('LOG_PATH',     STORAGE_PATH . '/logs');

define('APP_URL', rtrim($_ENV['APP_URL'] ?? '', '/'));

define('MAX_IMAGE_SIZE', (int)($_ENV['MAX_IMAGE_SIZE'] ?? 10485760));  // 10 MB

define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTS',  ['jpg', 'jpeg', 'png', 'webp']);

define('SESSION_NAME',     $_ENV['SESSION_NAME']    ?? 'barbearia_sess');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));
define('APP_SECRET',       $_ENV['APP_SECRET']);

define('LOGIN_MAX_ATTEMPTS',    (int)($_ENV['LOGIN_MAX_ATTEMPTS']    ?? 5));
define('LOGIN_LOCKOUT_MINUTES', (int)($_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15));

return [
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'debug'    => APP_DEBUG,
    'url'      => APP_URL,
    'name'     => $_ENV['APP_NAME']     ?? 'Barbearia',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo',
];
