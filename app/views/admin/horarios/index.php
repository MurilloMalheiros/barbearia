<?php
use App\models\Security;

$flash   = isset($flash) && is_array($flash) ? $flash : null;
$csrf    = isset($csrf) && is_string($csrf) ? $csrf : '';
$horarios = isset($horarios) && is_array($horarios) ? $horarios : [];
?>

<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Configurar Horários</h1>
    <p class="text-gray-500 text-sm mt-1">Defina os dias e horários disponíveis para agendamento</p>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="/admin/horarios" class="space-y-4">
    <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
      💡 Configure os dias e intervalos de atendimento. Clientes só poderão agendar nos dias e horários ativos.
      Os slots são gerados automaticamente entre a hora de início e fim com o intervalo escolhido.
    </div>

    <div class="space-y-3">
      <?php foreach ($horarios as $dia => $h): ?>
      <?php $isAtivo = (bool)$h['ativo']; ?>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

          <!-- Ativar dia -->
          <div class="flex items-center gap-3 sm:w-48">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox"
                     name="dias[<?= $dia ?>][ativo]"
                     value="1"
                     <?= $isAtivo ? 'checked' : '' ?>
                     class="sr-only peer"
                     onchange="toggleDia(this, <?= $dia ?>)">
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-accent peer-focus:ring-2 peer-focus:ring-accent/30 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
            <span class="font-semibold text-gray-800"><?= Security::e($h['nome_dia']) ?></span>
          </div>

          <!-- Configurações do dia -->
          <div id="diaConfig<?= $dia ?>" class="flex flex-wrap gap-4 flex-1 <?= !$isAtivo ? 'opacity-40 pointer-events-none' : '' ?>">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Início</label>
              <input type="time"
                     name="dias[<?= $dia ?>][hora_inicio]"
                     value="<?= Security::e(substr($h['hora_inicio'], 0, 5)) ?>"
                     class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Fim</label>
              <input type="time"
                     name="dias[<?= $dia ?>][hora_fim]"
                     value="<?= Security::e(substr($h['hora_fim'], 0, 5)) ?>"
                     class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Duração do slot</label>
              <select name="dias[<?= $dia ?>][duracao_minutos]"
                      class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                <?php foreach ([30 => '30 min', 60 => '1 hora', 90 => '1h30', 120 => '2 horas'] as $mins => $label): ?>
                <option value="<?= $mins ?>" <?= (int)$h['duracao_minutos'] === $mins ? 'selected' : '' ?>>
                  <?= $label ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <?php if ($isAtivo): ?>
          <!-- Preview dos slots -->
          <?php
            $inicio = strtotime($h['hora_inicio']);
            $fim    = strtotime($h['hora_fim']);
            $dur    = (int)$h['duracao_minutos'] * 60;
            $slots  = [];
            for ($t = $inicio; $t < $fim; $t += $dur) {
                $slots[] = date('H:i', $t);
            }
          ?>
          <div class="sm:w-auto">
            <p class="text-xs text-gray-400 mb-1">Slots gerados (<?= count($slots) ?>):</p>
            <div class="flex flex-wrap gap-1">
              <?php foreach ($slots as $slot): ?>
              <span class="text-xs bg-accent/10 text-accent-dark px-2 py-0.5 rounded-full"><?= $slot ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="flex justify-end pt-2">
      <button type="submit"
              class="bg-accent hover:bg-accent-dark text-primary font-bold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-accent/20">
        Salvar Horários
      </button>
    </div>
  </form>
</div>

<script>
function toggleDia(checkbox, dia) {
  const config = document.getElementById('diaConfig' + dia);
  if (checkbox.checked) {
    config.classList.remove('opacity-40', 'pointer-events-none');
  } else {
    config.classList.add('opacity-40', 'pointer-events-none');
  }
}
</script>
