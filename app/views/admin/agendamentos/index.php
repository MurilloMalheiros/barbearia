<?php
use App\models\Security;

$flash       = isset($flash) && is_array($flash) ? $flash : null;
$filtros     = isset($filtros) && is_array($filtros) ? $filtros : [];
$agendamentos = isset($agendamentos) && is_array($agendamentos) ? $agendamentos : [];
$csrf        = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Agendamentos</h1>
      <p class="text-gray-500 text-sm mt-1">Gerencie os agendamentos dos clientes</p>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <!-- Filtros -->
  <form method="GET" action="/admin/agendamentos" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
    <div class="flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Data específica</label>
        <input type="date" name="data" value="<?= Security::e($filtros['data'] ?? '') ?>"
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Período</label>
        <select name="periodo" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
          <option value="">Todos</option>
          <option value="hoje"   <?= ($filtros['periodo'] ?? '') === 'hoje'  ? 'selected' : '' ?>>Hoje</option>
          <option value="semana" <?= ($filtros['periodo'] ?? '') === 'semana' ? 'selected' : '' ?>>Próximos 7 dias</option>
          <option value="mes"    <?= ($filtros['periodo'] ?? '') === 'mes'    ? 'selected' : '' ?>>Este mês</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
          <option value="">Todos</option>
          <option value="pendente"   <?= ($filtros['status'] ?? '') === 'pendente'   ? 'selected' : '' ?>>Pendentes</option>
          <option value="confirmado" <?= ($filtros['status'] ?? '') === 'confirmado' ? 'selected' : '' ?>>Confirmados</option>
          <option value="concluido"  <?= ($filtros['status'] ?? '') === 'concluido'  ? 'selected' : '' ?>>Concluídos</option>
          <option value="cancelado"  <?= ($filtros['status'] ?? '') === 'cancelado'  ? 'selected' : '' ?>>Cancelados</option>
        </select>
      </div>
      <button type="submit" class="bg-accent hover:bg-accent-dark text-primary font-semibold px-5 py-2 rounded-lg text-sm transition-colors">
        Filtrar
      </button>
      <?php if (!empty($filtros)): ?>
      <a href="/admin/agendamentos" class="text-gray-400 hover:text-gray-600 text-sm py-2">Limpar</a>
      <?php endif; ?>
    </div>
  </form>

  <!-- Tabela de agendamentos -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if (empty($agendamentos)): ?>
    <div class="text-center py-16">
      <p class="text-4xl mb-3">📭</p>
      <p class="text-gray-400">Nenhum agendamento encontrado.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
            <th class="px-6 py-4 font-medium">Data / Hora</th>
            <th class="px-6 py-4 font-medium">Cliente</th>
            <th class="px-6 py-4 font-medium">Contato</th>
            <th class="px-6 py-4 font-medium">Serviço</th>
            <th class="px-6 py-4 font-medium">Status</th>
            <th class="px-6 py-4 font-medium">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($agendamentos as $ag):
            $statusClasses = match($ag['status']) {
              'confirmado' => 'bg-blue-100 text-blue-700',
              'concluido'  => 'bg-green-100 text-green-700',
              'cancelado'  => 'bg-red-100 text-red-700',
              default      => 'bg-yellow-100 text-yellow-700',
            };
          ?>
          <tr class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4">
              <p class="font-semibold text-gray-800"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?></p>
              <p class="text-accent font-bold"><?= substr(Security::e($ag['horario']), 0, 5) ?></p>
            </td>
            <td class="px-6 py-4">
              <p class="font-medium text-gray-800"><?= Security::e($ag['cliente_nome']) ?></p>
            </td>
            <td class="px-6 py-4">
              <a href="https://wa.me/<?= Security::e(preg_replace('/\D/', '', $ag['cliente_telefone'])) ?>"
                 target="_blank" rel="noopener noreferrer"
                 class="text-green-600 hover:underline text-xs">
                <?= Security::e($ag['cliente_telefone']) ?>
              </a>
            </td>
            <td class="px-6 py-4 text-gray-600 text-xs">
              <?= Security::e($ag['servico_nome'] ?? '—') ?>
              <?php if ($ag['observacoes']): ?>
              <p class="text-gray-400 truncate max-w-32" title="<?= Security::e($ag['observacoes']) ?>">
                <?= Security::e(mb_substr($ag['observacoes'], 0, 40)) ?>...
              </p>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $statusClasses ?>">
                <?= Security::e($ag['status']) ?>
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-1">
                <!-- Alterar status -->
                <form method="POST" action="/admin/agendamentos/<?= (int)$ag['id'] ?>/status" class="inline">
                  <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
                  <select name="status" onchange="this.form.submit()"
                          class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none">
                    <option value="">Alterar →</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmar</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelar</option>
                  </select>
                </form>

                <!-- Excluir -->
                <form method="POST" action="/admin/agendamentos/<?= (int)$ag['id'] ?>/deletar"
                      onsubmit="return confirm('Excluir este agendamento permanentemente?')">
                  <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
                  <button type="submit" class="text-red-400 hover:text-red-600 p-1 rounded transition-colors"
                          title="Excluir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
