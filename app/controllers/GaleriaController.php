<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{ImagemModel, AdminModel, Security, Session};

final class GaleriaController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $imagens = ImagemModel::allAdmin();
        $flash   = $this->getFlash();
        $csrf    = Session::generateCsrfToken();
        $this->renderWithLayout('admin', 'admin/galeria/index', compact('imagens', 'flash', 'csrf'));
    }

    public function create(): void
    {
        $this->requireAuth();
        $flash  = $this->getFlash();
        $csrf   = Session::generateCsrfToken();
        $imagem = null;
        $this->renderWithLayout('admin', 'admin/galeria/form', compact('imagem', 'flash', 'csrf'));
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (empty($_FILES['arquivo']['name'])) {
            $this->flash('error', 'Selecione uma imagem.');
            $this->redirect('/admin/galeria/criar');
        }

        $errors = Security::validateUpload($_FILES['arquivo'], 'image');
        if (!empty($errors)) {
            $this->flash('error', implode(' ', $errors));
            $this->redirect('/admin/galeria/criar');
        }

        $titulo   = trim($this->post('titulo', ''));
        $descricao = trim($this->post('descricao', ''));

        if (empty($titulo)) {
            $this->flash('error', 'O título é obrigatório.');
            $this->redirect('/admin/galeria/criar');
        }

        $caminho = Security::moveUpload($_FILES['arquivo'], 'galeria');
        if (!$caminho) {
            $this->flash('error', 'Erro ao fazer upload da imagem.');
            $this->redirect('/admin/galeria/criar');
        }

        ImagemModel::insert([
            'titulo'        => $titulo,
            'descricao'     => $descricao ?: null,
            'arquivo'       => $caminho,
            'mime_type'     => $_FILES['arquivo']['type'],
            'tamanho_bytes' => $_FILES['arquivo']['size'],
            'admin_id'      => (int)Session::get('admin_id'),
        ]);

        AdminModel::log((int)Session::get('admin_id'), 'imagem_adicionada', $titulo, $this->clientIp());
        $this->flash('success', 'Imagem adicionada à galeria!');
        $this->redirect('/admin/galeria');
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $imagem = ImagemModel::find($id);
        if (!$imagem) $this->abort(404);

        $flash = $this->getFlash();
        $csrf  = Session::generateCsrfToken();
        $this->renderWithLayout('admin', 'admin/galeria/form', compact('imagem', 'flash', 'csrf'));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $imagem = ImagemModel::find($id);
        if (!$imagem) $this->abort(404);

        $data = [
            'titulo'    => trim($this->post('titulo', '')),
            'descricao' => trim($this->post('descricao', '')) ?: null,
            'ativo'     => (bool)$this->post('ativo', false),
            'ordem'     => (int)$this->post('ordem', 0),
        ];

        if (empty($data['titulo'])) {
            $this->flash('error', 'O título é obrigatório.');
            $this->redirect("/admin/galeria/{$id}/editar");
        }

        // Troca de imagem opcional
        if (!empty($_FILES['arquivo']['name'])) {
            $errors = Security::validateUpload($_FILES['arquivo'], 'image');
            if (!empty($errors)) {
                $this->flash('error', implode(' ', $errors));
                $this->redirect("/admin/galeria/{$id}/editar");
            }

            $caminho = Security::moveUpload($_FILES['arquivo'], 'galeria');
            if ($caminho) {
                $data['arquivo'] = $caminho;
                // Remove arquivo antigo
                $this->removeFile($imagem['arquivo']);
            }
        }

        ImagemModel::update($id, $data);
        AdminModel::log((int)Session::get('admin_id'), 'imagem_atualizada', "ID {$id}", $this->clientIp());
        $this->flash('success', 'Imagem atualizada!');
        $this->redirect('/admin/galeria');
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $imagem = ImagemModel::find($id);
        if ($imagem) {
            $this->removeFile($imagem['arquivo']);
            ImagemModel::delete($id);
        }

        AdminModel::log((int)Session::get('admin_id'), 'imagem_excluida', "ID {$id}", $this->clientIp());
        $this->flash('success', 'Imagem excluída.');
        $this->redirect('/admin/galeria');
    }

    /** Serve upload de imagem com proteção contra path traversal */
    public function serve(string $path): void
    {
        // Previne path traversal
        $path     = ltrim($path, '/');
        $realBase = realpath(PUBLIC_PATH . '/uploads');
        $filePath = realpath(PUBLIC_PATH . '/uploads/' . $path);

        if (!$filePath || !str_starts_with($filePath, $realBase)) {
            http_response_code(404);
            exit;
        }

        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            exit;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        if (!in_array($mimeType, ALLOWED_IMAGE_MIMES, true)) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    private function removeFile(string $arquivo): void
    {
        $path = PUBLIC_PATH . '/uploads/' . ltrim($arquivo, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
