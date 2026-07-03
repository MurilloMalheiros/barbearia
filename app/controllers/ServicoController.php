<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{ServicoModel, AdminModel, Session, Security};

final class ServicoController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $servicos = ServicoModel::allAdmin();
        $flash    = $this->getFlash();
        $csrf     = Session::generateCsrfToken();
        $this->renderWithLayout('admin', 'admin/servicos/index', compact('servicos', 'flash', 'csrf'));
    }

    public function create(): void
    {
        $this->requireAuth();
        $flash   = $this->getFlash();
        $csrf    = Session::generateCsrfToken();
        $servico = null;
        $this->renderWithLayout('admin', 'admin/servicos/form', compact('servico', 'flash', 'csrf'));
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $nome     = trim($this->post('nome', ''));
        $descricao = trim($this->post('descricao', ''));
        $icone    = trim($this->post('icone', ''));
        $preco    = $this->post('preco', '');
        $duracao  = (int)$this->post('duracao_minutos', 60);
        $ordem    = (int)$this->post('ordem', 0);

        if (empty($nome)) {
            $this->flash('error', 'O nome do serviço é obrigatório.');
            $this->redirect('/admin/servicos/criar');
        }

        ServicoModel::insert([
            'nome'             => $nome,
            'descricao'        => $descricao ?: null,
            'icone'            => $icone ?: null,
            'preco'            => $preco !== '' ? (float)str_replace(',', '.', $preco) : null,
            'duracao_minutos'  => $duracao,
            'ordem'            => $ordem,
        ]);

        AdminModel::log((int)Session::get('admin_id'), 'servico_criado', $nome, $this->clientIp());
        $this->flash('success', 'Serviço cadastrado com sucesso!');
        $this->redirect('/admin/servicos');
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $servico = ServicoModel::find($id);
        if (!$servico) $this->abort(404);

        $flash = $this->getFlash();
        $csrf  = Session::generateCsrfToken();
        $this->renderWithLayout('admin', 'admin/servicos/form', compact('servico', 'flash', 'csrf'));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $servico = ServicoModel::find($id);
        if (!$servico) $this->abort(404);

        $nome    = trim($this->post('nome', ''));
        $preco   = $this->post('preco', '');
        $duracao = (int)$this->post('duracao_minutos', 60);

        if (empty($nome)) {
            $this->flash('error', 'O nome é obrigatório.');
            $this->redirect("/admin/servicos/{$id}/editar");
        }

        ServicoModel::update($id, [
            'nome'             => $nome,
            'descricao'        => trim($this->post('descricao', '')) ?: null,
            'icone'            => trim($this->post('icone', '')) ?: null,
            'preco'            => $preco !== '' ? (float)str_replace(',', '.', $preco) : null,
            'duracao_minutos'  => $duracao,
            'ordem'            => (int)$this->post('ordem', 0),
            'ativo'            => (bool)$this->post('ativo', false),
        ]);

        AdminModel::log((int)Session::get('admin_id'), 'servico_atualizado', "ID {$id}", $this->clientIp());
        $this->flash('success', 'Serviço atualizado!');
        $this->redirect('/admin/servicos');
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        ServicoModel::delete($id);
        AdminModel::log((int)Session::get('admin_id'), 'servico_excluido', "ID {$id}", $this->clientIp());
        $this->flash('success', 'Serviço excluído.');
        $this->redirect('/admin/servicos');
    }
}
