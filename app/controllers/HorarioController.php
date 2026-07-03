<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{HorarioModel, AdminModel, Session};

final class HorarioController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $horarios = HorarioModel::todos();
        $flash    = $this->getFlash();
        $csrf     = Session::generateCsrfToken();

        $this->renderWithLayout('admin', 'admin/horarios/index', compact('horarios', 'flash', 'csrf'));
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $dias = $_POST['dias'] ?? [];

        // Processa os 7 dias da semana
        for ($i = 0; $i <= 6; $i++) {
            $dadosDia = $dias[$i] ?? [];

            $ativo          = !empty($dadosDia['ativo']);
            $horaInicio     = $dadosDia['hora_inicio']     ?? '08:00';
            $horaFim        = $dadosDia['hora_fim']        ?? '18:00';
            $duracaoMinutos = (int)($dadosDia['duracao_minutos'] ?? 60);

            // Validações básicas
            if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio)) $horaInicio = '08:00';
            if (!preg_match('/^\d{2}:\d{2}$/', $horaFim))    $horaFim    = '18:00';
            if (!in_array($duracaoMinutos, [30, 60, 90, 120], true)) $duracaoMinutos = 60;

            // Hora início deve ser antes do fim
            if (strtotime($horaInicio) >= strtotime($horaFim)) {
                $this->flash('error', 'A hora de início deve ser menor que a hora de fim.');
                $this->redirect('/admin/horarios');
            }

            HorarioModel::salvar($i, [
                'hora_inicio'     => $horaInicio,
                'hora_fim'        => $horaFim,
                'duracao_minutos' => $duracaoMinutos,
                'ativo'           => $ativo,
            ]);
        }

        AdminModel::log(
            (int)Session::get('admin_id'),
            'horarios_atualizados',
            '',
            $this->clientIp()
        );

        $this->flash('success', 'Horários atualizados com sucesso!');
        $this->redirect('/admin/horarios');
    }
}
