<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{
    VisitaModel,
    ImagemModel,
    ServicoModel,
    AgendamentoModel,
    AdminModel,
    Session,
};

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $totalVisitas  = VisitaModel::total();
        $visitasHoje   = VisitaModel::hoje();
        $totalImagens  = ImagemModel::totalAtivos();
        $totalServicos = count(ServicoModel::allAdmin());
        $ultimosUploads = ImagemModel::ultimos(6);

        $visitasDiarias  = VisitaModel::porDia();
        $navegadores     = VisitaModel::porNavegador();
        $sistemas        = VisitaModel::porSO();
        $ultimasVisitas  = VisitaModel::ultimas(10);

        $agendamentosHoje   = AgendamentoModel::hoje();
        $totaisAgendamentos = AgendamentoModel::totaisPorStatus();
        $totalMesAgendamentos = AgendamentoModel::totalMes();

        $flash = $this->getFlash();

        $this->renderWithLayout('admin', 'admin/dashboard', compact(
            'totalVisitas', 'visitasHoje', 'totalImagens', 'totalServicos',
            'ultimosUploads', 'visitasDiarias', 'navegadores', 'sistemas', 'ultimasVisitas',
            'agendamentosHoje', 'totaisAgendamentos', 'totalMesAgendamentos', 'flash'
        ));
    }
}
