<?php
use App\models\Security;

$flash   = isset($flash) && is_array($flash) ? $flash : null;
$servico = isset($servico) && is_array($servico) ? $servico : null;
$csrf    = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="max-w-2xl space-y-6">
  <div class="flex items-center gap-3">
    <a href="/admin/servicos" class="text-gray-400 hover:text-gray-600 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900">
        <?= $servico ? 'Editar Serviço' : 'Novo Serviço' ?>
      </h1>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <form method="POST"
        action="<?= $servico ? '/admin/servicos/' . (int)$servico['id'] : '/admin/servicos' ?>"
        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
    <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div class="sm:col-span-2">
        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1.5">Nome do serviço *</label>
        <input type="text" id="nome" name="nome"
               value="<?= Security::e($servico['nome'] ?? '') ?>"
               placeholder="Ex: Corte de Cabelo"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent transition-colors"
               required maxlength="200">
      </div>

      <div>
        <label for="icone" class="block text-sm font-medium text-gray-700 mb-1.5">Ícone (emoji)</label>
        <input type="text" id="icone" name="icone"
               value="<?= Security::e($servico['icone'] ?? '') ?>"
               placeholder="✂️"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 text-2xl"
               maxlength="10">
        <p class="text-xs text-gray-400 mt-1">Cole um emoji para representar o serviço.</p>
      </div>

      <div>
        <label for="preco" class="block text-sm font-medium text-gray-700 mb-1.5">Preço (R$)</label>
        <input type="number" id="preco" name="preco"
               value="<?= Security::e((string)($servico['preco'] ?? '')) ?>"
               placeholder="35.00"
               step="0.01" min="0"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
      </div>

      <div>
        <label for="duracao_minutos" class="block text-sm font-medium text-gray-700 mb-1.5">Duração</label>
        <select id="duracao_minutos" name="duracao_minutos"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
          <?php foreach ([30 => '30 minutos', 45 => '45 minutos', 60 => '1 hora', 90 => '1h 30min', 120 => '2 horas'] as $mins => $label): ?>
          <option value="<?= $mins ?>" <?= (int)($servico['duracao_minutos'] ?? 60) === $mins ? 'selected' : '' ?>>
            <?= $label ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1.5">Ordem de exibição</label>
        <input type="number" id="ordem" name="ordem"
               value="<?= (int)($servico['ordem'] ?? 0) ?>"
               min="0"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
      </div>

      <div class="sm:col-span-2">
        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1.5">Descrição</label>
        <textarea id="descricao" name="descricao" rows="3"
                  placeholder="Descreva o serviço..."
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 resize-none"><?= Security::e($servico['descricao'] ?? '') ?></textarea>
      </div>

      <?php if ($servico): ?>
      <div class="sm:col-span-2">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="ativo" value="1" <?= $servico['ativo'] ? 'checked' : '' ?>
                 class="w-4 h-4 text-accent border-gray-300 rounded focus:ring-accent/30">
          <span class="text-sm font-medium text-gray-700">Serviço ativo (visível no site)</span>
        </label>
      </div>
      <?php endif; ?>
    </div>

    <div class="flex gap-3 pt-2">
      <button type="submit"
              class="bg-accent hover:bg-accent-dark text-primary font-bold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-accent/20">
        <?= $servico ? 'Salvar Alterações' : 'Cadastrar Serviço' ?>
      </button>
      <a href="/admin/servicos"
         class="px-6 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors text-sm">
        Cancelar
      </a>
    </div>
  </form>
</div>
