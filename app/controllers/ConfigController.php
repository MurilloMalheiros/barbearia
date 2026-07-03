<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{ConfiguracaoModel, AdminModel, Security, Session};

final class ConfigController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $config = ConfiguracaoModel::all();
        $flash  = $this->getFlash();
        $csrf   = Session::generateCsrfToken();
        $this->renderWithLayout('admin', 'admin/configuracoes', compact('config', 'flash', 'csrf'));
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $allowed = [
            'empresa_nome', 'empresa_whatsapp', 'empresa_descricao',
            'seo_title', 'seo_description', 'seo_keywords',
            'google_analytics', 'endereco', 'email_contato',
            'instagram', 'facebook',
        ];

        $configs = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $configs[$key] = trim($_POST[$key]);
            }
        }

        // Upload de logo
        if (!empty($_FILES['empresa_logo']['name'])) {
            $errors = Security::validateUpload($_FILES['empresa_logo'], 'image');
            if (empty($errors)) {
                $caminho = Security::moveUpload($_FILES['empresa_logo'], 'logos');
                if ($caminho) {
                    $configs['empresa_logo'] = $caminho;
                }
            } else {
                $this->flash('error', 'Erro no logo: ' . implode(' ', $errors));
                $this->redirect('/admin/configuracoes');
            }
        }

        // Upload de imagem do hero
        if (!empty($_FILES['hero_image']['name'])) {
            $errors = Security::validateUpload($_FILES['hero_image'], 'image');
            if (empty($errors)) {
                $caminho = Security::moveUpload($_FILES['hero_image'], 'hero');
                if ($caminho) {
                    $configs['hero_image'] = $caminho;
                }
            } else {
                $this->flash('error', 'Erro na imagem do hero: ' . implode(' ', $errors));
                $this->redirect('/admin/configuracoes');
            }
        }

        // Upload de OG image
        if (!empty($_FILES['og_image']['name'])) {
            $errors = Security::validateUpload($_FILES['og_image'], 'image');
            if (empty($errors)) {
                $caminho = Security::moveUpload($_FILES['og_image'], 'logos');
                if ($caminho) {
                    $configs['og_image'] = $caminho;
                }
            }
        }

        ConfiguracaoModel::saveMany($configs);
        AdminModel::log((int)Session::get('admin_id'), 'config_atualizada', '', $this->clientIp());
        $this->flash('success', 'Configurações salvas com sucesso!');
        $this->redirect('/admin/configuracoes');
    }

    public function updateSenha(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $senhaAtual = $this->post('senha_atual', '');
        $novaSenha  = $this->post('nova_senha', '');
        $confirma   = $this->post('confirma_senha', '');

        if (strlen($novaSenha) < 8) {
            $this->flash('error', 'A nova senha deve ter ao menos 8 caracteres.');
            $this->redirect('/admin/configuracoes');
        }

        if ($novaSenha !== $confirma) {
            $this->flash('error', 'As senhas não coincidem.');
            $this->redirect('/admin/configuracoes');
        }

        $adminId = (int)Session::get('admin_id');
        $admin   = \App\models\Database::fetchOne(
            'SELECT senha_hash FROM administradores WHERE id = $1',
            [$adminId]
        );

        if (!$admin || !\App\models\AdminModel::verifyPassword($senhaAtual, $admin['senha_hash'])) {
            $this->flash('error', 'Senha atual incorreta.');
            $this->redirect('/admin/configuracoes');
        }

        AdminModel::updateSenha($adminId, $novaSenha);
        AdminModel::log($adminId, 'senha_alterada', '', $this->clientIp());
        $this->flash('success', 'Senha alterada com sucesso!');
        $this->redirect('/admin/configuracoes');
    }
}
