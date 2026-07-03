<?php
use App\models\Security;

$flash    = isset($flash) && is_array($flash) ? $flash : null;
$servicos = isset($servicos) && is_array($servicos) ? $servicos : [];
$csrf     = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Serviços</h1>
      <p class="text-gray-500 text-sm mt-1">Gerencie os serviços oferecidos</p>
    </div>
    <a href="/admin/servicos/criar"
       class="bg-accent hover:bg-accent-dark text-primary font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
      + Novo Serviço
    </a>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <?php if (empty($servicos)): ?>
  <div class="bg-white rounded-2xl p-16 text-center shadow-sm border border-gray-100">
    <p class="text-4xl mb-3">✂️</p>
    <p class="text-gray-400 mb-4">Nenhum serviço cadastrado.</p>
    <a href="/admin/servicos/criar" class="text-accent hover:underline text-sm">Cadastrar primeiro serviço →</a>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($servicos as $srv): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-3">
          <span class="text-3xl"><?= Security::e($srv['icone'] ?: '✂️') ?></span>
          <div>
            <h3 class="font-semibold text-gray-800"><?= Security::e($srv['nome']) ?></h3>
            <?php if ($srv['preco']): ?>
            <p class="text-accent font-bold text-sm">R$ <?= number_format((float)$srv['preco'], 2, ',', '.') ?></p>
            <?php endif; ?>
          </div>
        </div>
        <span class="text-xs px-2 py-1 rounded-full <?= $srv['ativo'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
          <?= $srv['ativo'] ? 'Ativo' : 'Inativo' ?>
        </span>
      </div>

      <?php if ($srv['descricao']): ?>
      <p class="text-gray-500 text-sm leading-relaxed mb-3 flex-1 line-clamp-2"><?= Security::e($srv['descricao']) ?></p>
      <?php endif; ?>

      <div class="flex items-center gap-1 pt-3 border-t border-gray-100 mt-auto">
        <?php if ($srv['duracao_minutos']): ?>
        <span class="text-xs text-gray-400 flex-1">⏱ <?= (int)$srv['duracao_minutos'] ?> min</span>
        <?php endif; ?>
        <a href="/admin/servicos/<?= (int)$srv['id'] ?>/editar"
           class="text-xs text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
          Editar
        </a>
        <form method="POST" action="/admin/servicos/<?= (int)$srv['id'] ?>/deletar"
              onsubmit="return confirm('Excluir este serviço?')">
          <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
          <button type="submit" class="text-xs text-red-500 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
            Excluir
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
