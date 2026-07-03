<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{AdminModel, Security, Session};

final class AuthController extends BaseController
{
    public function loginForm(): void
    {
        if (Session::has('admin_id')) {
            $this->redirect('/admin/dashboard');
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }

        $csrf  = Session::generateCsrfToken();
        $flash = $this->getFlash();
        $this->render('admin/login', compact('csrf', 'flash'));
    }

    public function loginPost(): void
    {
        $this->validateCsrf();

        $email = trim($this->post('email', ''));
        $senha = $this->post('senha', '');
        $ip    = $this->clientIp();

        if (Security::isIpBlocked($ip)) {
            $this->flash('error', 'Acesso temporariamente bloqueado. Tente novamente mais tarde.');
            $this->redirect('/admin/login');
        }

        if (empty($email) || empty($senha)) {
            Security::registerLoginAttempt($ip);
            $this->flash('error', 'Preencha todos os campos.');
            $this->redirect('/admin/login');
        }

        $admin = AdminModel::findByEmail($email);

        // Timing-safe: sempre executa verifyPassword para evitar timing attack
        $senhaValida = $admin && AdminModel::verifyPassword($senha, $admin['senha_hash']);

        if (!$admin || !$senhaValida) {
            Security::registerLoginAttempt($ip);
            $this->flash('error', 'E-mail ou senha incorretos.');
            $this->redirect('/admin/login');
        }

        Security::clearLoginAttempts($ip);
        Session::regenerate();
        Session::set('admin_id',        (int)$admin['id']);
        Session::set('admin_nome',      $admin['nome']);
        Session::set('_last_activity',  time());

        AdminModel::registrarLogin((int)$admin['id'], $ip);
        AdminModel::log((int)$admin['id'], 'login', 'Login realizado', $ip);

        $this->redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        if (Session::has('admin_id')) {
            AdminModel::log(
                (int)Session::get('admin_id'),
                'logout',
                'Logout realizado',
                $this->clientIp()
            );
        }
        Session::destroy();
        $this->redirect('/admin/login');
    }
}
