<?php
use App\models\Security;

$flash   = isset($flash) && is_array($flash) ? $flash : null;
$imagens = isset($imagens) && is_array($imagens) ? $imagens : [];
$csrf    = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Galeria de Fotos</h1>
      <p class="text-gray-500 text-sm mt-1">Portfólio de trabalhos realizados</p>
    </div>
    <a href="/admin/galeria/criar"
       class="bg-accent hover:bg-accent-dark text-primary font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
      + Adicionar Foto
    </a>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <?php if (empty($imagens)): ?>
  <div class="bg-white rounded-2xl p-16 text-center shadow-sm border border-gray-100">
    <p class="text-4xl mb-3">🖼️</p>
    <p class="text-gray-400 mb-4">Nenhuma foto na galeria.</p>
    <a href="/admin/galeria/criar" class="text-accent hover:underline text-sm">Adicionar primeira foto →</a>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
    <?php foreach ($imagens as $img): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
      <div class="aspect-square overflow-hidden bg-gray-100">
        <img src="/uploads/<?= Security::e((string)$img['arquivo']) ?>"
             alt="<?= Security::e($img['titulo']) ?>"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
      </div>
      <div class="p-3">
        <p class="text-xs font-medium text-gray-700 truncate"><?= Security::e($img['titulo']) ?></p>
        <div class="flex items-center justify-between mt-2">
          <span class="text-xs px-2 py-0.5 rounded-full <?= $img['ativo'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
            <?= $img['ativo'] ? 'Ativa' : 'Inativa' ?>
          </span>
          <div class="flex gap-1">
            <a href="/admin/galeria/<?= (int)$img['id'] ?>/editar"
               class="text-xs text-blue-500 hover:text-blue-700 p-1 rounded transition-colors">
              ✏️
            </a>
            <form method="POST" action="/admin/galeria/<?= (int)$img['id'] ?>/deletar"
                  onsubmit="return confirm('Excluir esta imagem?')" class="inline">
              <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
              <button type="submit" class="text-xs text-red-400 hover:text-red-600 p-1 rounded transition-colors">🗑️</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
