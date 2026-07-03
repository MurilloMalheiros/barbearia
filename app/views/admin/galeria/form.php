<?php
use App\models\Security;

$flash  = isset($flash) && is_array($flash) ? $flash : null;
$imagem = isset($imagem) && is_array($imagem) ? $imagem : null;
$csrf   = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="max-w-2xl space-y-6">
  <div class="flex items-center gap-3">
    <a href="/admin/galeria" class="text-gray-400 hover:text-gray-600 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900"><?= $imagem ? 'Editar Foto' : 'Adicionar Foto' ?></h1>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <form method="POST"
        action="<?= $imagem ? '/admin/galeria/' . (int)$imagem['id'] : '/admin/galeria' ?>"
        enctype="multipart/form-data"
        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
    <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">

    <!-- Preview / Drop Zone -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1.5">
        Imagem <?= $imagem ? '(deixe em branco para manter a atual)' : '*' ?>
      </label>

      <?php if ($imagem && $imagem['arquivo']): ?>
      <div class="mb-3">
        <img src="/uploads/<?= Security::e($imagem['arquivo']) ?>"
             alt="Atual"
             class="h-40 w-auto rounded-xl object-cover border border-gray-200">
        <p class="text-xs text-gray-400 mt-1">Imagem atual</p>
      </div>
      <?php endif; ?>

      <div id="dropZone"
           class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-accent transition-colors">
        <input type="file" id="arquivo" name="arquivo" accept=".jpg,.jpeg,.png,.webp"
               class="hidden" <?= $imagem ? '' : 'required' ?>>
        <div id="dropPreview" class="hidden mb-3">
          <img id="previewImg" src="" alt="" class="max-h-48 mx-auto rounded-lg object-contain">
        </div>
        <div id="dropText">
          <p class="text-2xl mb-2">📷</p>
          <p class="text-gray-500 text-sm">Clique ou arraste uma imagem aqui</p>
          <p class="text-gray-400 text-xs mt-1">JPG, PNG, WebP — máx. 10 MB</p>
        </div>
      </div>
    </div>

    <div>
      <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1.5">Título *</label>
      <input type="text" id="titulo" name="titulo"
             value="<?= Security::e($imagem['titulo'] ?? '') ?>"
             placeholder="Ex: Corte degradê moderno"
             class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
             required maxlength="200">
    </div>

    <div>
      <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1.5">Descrição (opcional)</label>
      <textarea id="descricao" name="descricao" rows="2"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 resize-none"><?= Security::e($imagem['descricao'] ?? '') ?></textarea>
    </div>

    <?php if ($imagem): ?>
    <div class="flex items-center gap-3">
      <input type="checkbox" id="ativo" name="ativo" value="1" <?= $imagem['ativo'] ? 'checked' : '' ?>
             class="w-4 h-4 text-accent border-gray-300 rounded">
      <label for="ativo" class="text-sm text-gray-700">Ativa (visível na galeria pública)</label>
    </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-2">
      <button type="submit"
              class="bg-accent hover:bg-accent-dark text-primary font-bold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-accent/20">
        <?= $imagem ? 'Salvar Alterações' : 'Adicionar Foto' ?>
      </button>
      <a href="/admin/galeria" class="px-6 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors text-sm">
        Cancelar
      </a>
    </div>
  </form>
</div>

<script>
  const dropZone  = document.getElementById('dropZone');
  const fileInput = document.getElementById('arquivo');
  const preview   = document.getElementById('dropPreview');
  const previewImg = document.getElementById('previewImg');
  const dropText  = document.getElementById('dropText');

  dropZone.addEventListener('click', () => fileInput.click());

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-accent', 'bg-accent/5');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-accent', 'bg-accent/5');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-accent', 'bg-accent/5');
    const file = e.dataTransfer.files[0];
    if (file) showPreview(file);
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) showPreview(fileInput.files[0]);
  });

  function showPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImg.src = e.target.result;
      preview.classList.remove('hidden');
      dropText.classList.add('hidden');
    };
    reader.readAsDataURL(file);

    // Sync com o input
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
  }
</script>
