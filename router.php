<?php
/**
 * Router para o servidor built-in do PHP (desenvolvimento)
 * php -S localhost:8833 -t public router.php
 */

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . '/public' . $uri;

// Serve arquivo estático se existir
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        readfile($file);
        return true;
    }
    return false; // Deixa o PHP servir diretamente
}

// Tudo mais vai para o Front Controller
require_once __DIR__ . '/public/index.php';
