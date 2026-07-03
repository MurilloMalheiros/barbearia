<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{
    AgendamentoModel,
    ServicoModel,
    HorarioModel,
    Security,
    Session,
    AdminModel,
};

final class AgendamentoController extends BaseController
{
    // ─────────────────────────────────────────
    // API PÚBLICA: retorna slots disponíveis
    // GET /agendamento/horarios?data=YYYY-MM-DD
    // ─────────────────────────────────────────
    public function horariosDisponiveis(): void
    {
        $data = trim($this->query('data', ''));

        // Valida formato de data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $this->json(['error' => 'Data inválida.'], 400);
        }

        $ts = strtotime($data);
        if ($ts === false) {
            $this->json(['error' => 'Data inválida.'], 400);
        }

        // Não permite datas no passado
        if ($ts < strtotime(date('Y-m-d'))) {
            $this->json(['slots' => []]);
        }

        $diaSemana = (int)date('w', $ts); // 0=Dom, 6=Sab
        $todos     = HorarioModel::gerarSlots($diaSemana);
        $ocupados  = AgendamentoModel::slotsOcupados($data);

        // Normaliza ocupados para H:i
        $ocupadosNorm = array_map(fn($h) => substr($h, 0, 5), $ocupados);

        $disponiveis = array_values(array_filter(
            $todos,
            fn($slot) => !in_array($slot, $ocupadosNorm, true)
        ));

        $this->json(['slots' => $disponiveis]);
    }

    // ─────────────────────────────────────────
    // Formulário de agendamento (público)
    // POST /agendamento
    // ─────────────────────────────────────────
    public function store(): void
    {
        // CSRF
        $token = $this->post('_csrf_token', '');
        if (!Session::validateCsrf($token)) {
            $this->json(['error' => 'Requisição inválida (CSRF).'], 403);
        }

        $nome      = trim($this->post('cliente_nome', ''));
        $telefone  = preg_replace('/\D/', '', $this->post('cliente_telefone', ''));
        $servicoId = (int)$this->post('servico_id', 0);
        $data      = trim($this->post('data_agendamento', ''));
        $horario   = trim($this->post('horario', ''));
        $obs       = trim($this->post('observacoes', ''));

        // Validações
        $errors = [];

        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 200) {
            $errors[] = 'Nome inválido (3–200 caracteres).';
        }

        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            $errors[] = 'Telefone inválido. Informe DDD + número (10 ou 11 dígitos).';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || strtotime($data) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Data inválida ou no passado.';
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $horario)) {
            $errors[] = 'Horário inválido.';
        }

        if (!empty($errors)) {
            $this->json(['error' => implode(' ', $errors)], 422);
        }

        // Verifica se o slot ainda está disponível
        if (!AgendamentoModel::slotDisponivel($data, $horario)) {
            $this->json(['error' => 'Este horário já foi reservado. Por favor, escolha outro.'], 409);
        }

        // Verifica se o horário pertence a um slot válido
        $ts        = strtotime($data);
        $diaSemana = (int)date('w', $ts);
        $slotsValidos = HorarioModel::gerarSlots($diaSemana);

        if (!in_array($horario, $slotsValidos, true)) {
            $this->json(['error' => 'Horário não disponível para este dia.'], 422);
        }

        AgendamentoModel::inserir([
            'cliente_nome'      => $nome,
            'cliente_telefone'  => $telefone,
            'servico_id'        => $servicoId ?: null,
            'data_agendamento'  => $data,
            'horario'           => $horario,
            'observacoes'       => $obs ?: null,
        ]);

        $this->json(['success' => true, 'message' => 'Agendamento realizado! Em breve entraremos em contato para confirmar.']);
    }

    // ─────────────────────────────────────────
    // Admin: lista agendamentos
    // ─────────────────────────────────────────
    public function index(): void
    {
        $this->requireAuth();

        $filtros = [
            'data'    => trim($this->query('data', '')),
            'status'  => trim($this->query('status', '')),
            'periodo' => trim($this->query('periodo', '')),
        ];

        // Remove filtros vazios
        $filtros = array_filter($filtros, fn($v) => $v !== '');

        $agendamentos = AgendamentoModel::listar($filtros);
        $totais       = AgendamentoModel::totaisPorStatus();
        $flash        = $this->getFlash();
        $csrf         = Session::generateCsrfToken();

        $this->renderWithLayout('admin', 'admin/agendamentos/index', compact(
            'agendamentos', 'totais', 'flash', 'csrf', 'filtros'
        ));
    }

    // ─────────────────────────────────────────
    // Admin: atualiza status de agendamento
    // POST /admin/agendamentos/{id}/status
    // ─────────────────────────────────────────
    public function updateStatus(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $status = trim($this->post('status', ''));
        $allowed = ['pendente', 'confirmado', 'cancelado', 'concluido'];

        if (!in_array($status, $allowed, true)) {
            $this->flash('error', 'Status inválido.');
            $this->redirect('/admin/agendamentos');
        }

        if (!AgendamentoModel::atualizarStatus($id, $status)) {
            $this->flash('error', 'Agendamento não encontrado.');
            $this->redirect('/admin/agendamentos');
        }

        AdminModel::log(
            (int)Session::get('admin_id'),
            'agendamento_status',
            "ID {$id} → {$status}",
            $this->clientIp()
        );

        $this->flash('success', 'Status atualizado com sucesso!');
        $this->redirect('/admin/agendamentos');
    }

    // ─────────────────────────────────────────
    // Admin: cancela / exclui agendamento
    // POST /admin/agendamentos/{id}/deletar
    // ─────────────────────────────────────────
    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        AgendamentoModel::delete($id);
        AdminModel::log(
            (int)Session::get('admin_id'),
            'agendamento_excluido',
            "ID {$id}",
            $this->clientIp()
        );

        $this->flash('success', 'Agendamento excluído.');
        $this->redirect('/admin/agendamentos');
    }
}
