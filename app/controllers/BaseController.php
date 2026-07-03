<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Session;
use App\models\Security;
use App\models\Logger;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            Logger::error('[View] Arquivo não encontrado', ['path' => $viewFile]);
            die('Erro interno do servidor.');
        }

        require $viewFile;
    }

    protected function renderWithLayout(string $layout, string $view, array $data = []): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        $data['content_view'] = APP_PATH . '/views/' . $view . '.php';
        $this->render('layouts/' . $layout, $data);
    }

    protected function redirect(string $url, int $code = 302): never
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    protected function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    protected function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function validateCsrf(): void
    {
        $token = $this->post('_csrf_token', '');
        if (!Session::validateCsrf($token)) {
            http_response_code(403);
            die('Requisição inválida (CSRF).');
        }
    }

    protected function requireAuth(): void
    {
        if (!Session::has('admin_id')) {
            $this->redirect('/admin/login');
        }
    }

    protected function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    protected function flash(string $type, string $message): void
    {
        Session::set('flash', ['type' => $type, 'message' => $message]);
    }

    protected function getFlash(): ?array
    {
        $flash = Session::get('flash');
        Session::remove('flash');
        return $flash;
    }

    protected function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        $messages = [400 => 'Requisição inválida', 403 => 'Acesso negado', 404 => 'Página não encontrada', 500 => 'Erro interno'];
        $msg      = $message ?: ($messages[$code] ?? 'Erro');
        $file     = APP_PATH . '/views/errors/' . $code . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        }
        exit;
    }
}
