<?php

declare(strict_types=1);

/**
 * Front Controller — ponto de entrada único
 */

ob_start();

require_once dirname(__DIR__) . '/config/app.php';

\App\models\Session::start();
\App\models\Security::setSecurityHeaders();

$uri = strtok(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
    '?'
);
$uri = '/' . trim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

// Validação de sessão apenas em rotas administrativas
if (str_starts_with($uri, '/admin')) {
    \App\models\Session::start();
    if (!\App\models\Session::validate()) {
        header('Location: /admin/login');
        exit;
    }
}

require_once dirname(__DIR__) . '/routes/web.php';
