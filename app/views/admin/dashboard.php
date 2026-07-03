<?php
use App\models\Security;

$agendamentosHoje     = isset($agendamentosHoje) && is_array($agendamentosHoje) ? $agendamentosHoje : [];
$totalMesAgendamentos = isset($totalMesAgendamentos) ? (int)$totalMesAgendamentos : 0;
$totalVisitas         = isset($totalVisitas) ? (int)$totalVisitas : 0;
$visitasHoje          = isset($visitasHoje) ? (int)$visitasHoje : 0;
$totalImagens         = isset($totalImagens) ? (int)$totalImagens : 0;
$totaisAgendamentos   = isset($totaisAgendamentos) && is_array($totaisAgendamentos) ? $totaisAgendamentos : [];
$ultimasVisitas       = isset($ultimasVisitas) && is_array($ultimasVisitas) ? $ultimasVisitas : [];
?>

<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Visão geral da barbearia</p>
  </div>

  <!-- Cards principais -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-medium text-gray-500">Agendamentos Hoje</p>
        <span class="text-2xl">📅</span>
      </div>
      <p class="text-3xl font-bold text-gray-900"><?= count($agendamentosHoje) ?></p>
      <p class="text-xs text-gray-400 mt-1">confirmados e pendentes</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-medium text-gray-500">Agendamentos no Mês</p>
        <span class="text-2xl">📆</span>
      </div>
      <p class="text-3xl font-bold text-gray-900"><?= $totalMesAgendamentos ?></p>
      <p class="text-xs text-gray-400 mt-1">este mês</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-medium text-gray-500">Visitas ao Site</p>
        <span class="text-2xl">👁️</span>
      </div>
      <p class="text-3xl font-bold text-gray-900"><?= number_format($totalVisitas) ?></p>
      <p class="text-xs text-gray-400 mt-1"><?= $visitasHoje ?> hoje</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-medium text-gray-500">Fotos na Galeria</p>
        <span class="text-2xl">🖼️</span>
      </div>
      <p class="text-3xl font-bold text-gray-900"><?= $totalImagens ?></p>
      <p class="text-xs text-gray-400 mt-1">imagens ativas</p>
    </div>
  </div>

  <!-- Status agendamentos -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <h3 class="font-semibold text-gray-700 mb-4">Status dos Agendamentos</h3>
      <div class="grid grid-cols-2 gap-3">
        <?php $statusInfo = [
          'pendente'   => ['label' => 'Pendentes',  'color' => 'yellow', 'emoji' => '⏳'],
          'confirmado' => ['label' => 'Confirmados','color' => 'blue',   'emoji' => '✅'],
          'concluido'  => ['label' => 'Concluídos', 'color' => 'green',  'emoji' => '🏁'],
          'cancelado'  => ['label' => 'Cancelados', 'color' => 'red',    'emoji' => '❌'],
        ];
        foreach ($statusInfo as $status => $info): ?>
        <div class="bg-<?= $info['color'] ?>-50 rounded-xl p-4 text-center">
          <p class="text-xl mb-1"><?= $info['emoji'] ?></p>
          <p class="text-2xl font-bold text-<?= $info['color'] ?>-700"><?= $totaisAgendamentos[$status] ?? 0 ?></p>
          <p class="text-xs text-<?= $info['color'] ?>-600"><?= $info['label'] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Agendamentos de hoje -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-700">Agenda de Hoje</h3>
        <a href="/admin/agendamentos?periodo=hoje" class="text-xs text-accent hover:underline">Ver todos →</a>
      </div>
      <?php if (empty($agendamentosHoje)): ?>
      <p class="text-gray-400 text-sm text-center py-6">Nenhum agendamento para hoje.</p>
      <?php else: ?>
      <div class="space-y-3 max-h-64 overflow-y-auto">
        <?php foreach ($agendamentosHoje as $ag): ?>
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
          <span class="text-accent font-bold text-sm w-12 shrink-0"><?= substr(Security::e($ag['horario']), 0, 5) ?></span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate"><?= Security::e($ag['cliente_nome']) ?></p>
            <p class="text-xs text-gray-400 truncate"><?= Security::e($ag['servico_nome'] ?? 'Serviço não informado') ?></p>
          </div>
          <span class="text-xs px-2 py-1 rounded-full shrink-0
            <?= match($ag['status']) {
              'confirmado' => 'bg-blue-100 text-blue-700',
              'concluido'  => 'bg-green-100 text-green-700',
              default      => 'bg-yellow-100 text-yellow-700',
            } ?>">
            <?= Security::e($ag['status']) ?>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Últimas visitas -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h3 class="font-semibold text-gray-700 mb-4">Últimas Visitas ao Site</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-400 border-b border-gray-100">
            <th class="pb-3 font-medium">Data</th>
            <th class="pb-3 font-medium">Navegador</th>
            <th class="pb-3 font-medium">Sistema</th>
            <th class="pb-3 font-medium">Página</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($ultimasVisitas as $v): ?>
          <tr class="text-gray-600">
            <td class="py-3 text-xs text-gray-400"><?= date('d/m H:i', strtotime($v['criado_em'])) ?></td>
            <td class="py-3"><?= Security::e($v['navegador'] ?? '—') ?></td>
            <td class="py-3"><?= Security::e($v['sistema_operacional'] ?? '—') ?></td>
            <td class="py-3 max-w-xs truncate text-xs"><?= Security::e($v['pagina'] ?? '/') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
