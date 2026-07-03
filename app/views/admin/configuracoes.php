<?php
use App\models\Security;

$flash  = isset($flash) && is_array($flash) ? $flash : null;
$config = isset($config) && is_array($config) ? $config : [];
$csrf   = isset($csrf) && is_string($csrf) ? $csrf : '';
?>

<div class="space-y-8">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
    <p class="text-gray-500 text-sm mt-1">Personalize sua barbearia</p>
  </div>

  <?php if (!empty($flash)): ?>
  <div data-flash class="p-4 rounded-xl text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' ?>">
    <?= Security::e($flash['message']) ?>
  </div>
  <?php endif; ?>

  <!-- Dados da empresa -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-800 mb-5 pb-3 border-b border-gray-100">🏪 Dados da Barbearia</h2>

    <form method="POST" action="/admin/configuracoes" enctype="multipart/form-data" class="space-y-5">
      <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome da barbearia</label>
          <input type="text" name="empresa_nome"
                 value="<?= Security::e($config['empresa_nome'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
                 maxlength="150">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp (com DDI, ex: 5511999999999)</label>
          <input type="text" name="empresa_whatsapp"
                 value="<?= Security::e($config['empresa_whatsapp'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
                 maxlength="20">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail de contato</label>
          <input type="email" name="email_contato"
                 value="<?= Security::e($config['email_contato'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Endereço</label>
          <input type="text" name="endereco"
                 value="<?= Security::e($config['endereco'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
                 maxlength="300">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Descrição (aparece no hero/site)</label>
          <textarea name="empresa_descricao" rows="3"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 resize-none"
                    maxlength="500"><?= Security::e($config['empresa_descricao'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Instagram (URL completa)</label>
          <input type="url" name="instagram"
                 value="<?= Security::e($config['instagram'] ?? '') ?>"
                 placeholder="https://instagram.com/suabarbearia"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Facebook (URL completa)</label>
          <input type="url" name="facebook"
                 value="<?= Security::e($config['facebook'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
        </div>
      </div>

      <!-- Uploads -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4 border-t border-gray-100">
        <!-- Logo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Logo</label>
          <?php if (!empty($config['empresa_logo'])): ?>
          <div class="mb-2">
            <img src="/uploads/<?= Security::e($config['empresa_logo']) ?>" alt="Logo atual" class="h-16 w-auto object-contain rounded-lg border border-gray-200 p-1">
          </div>
          <?php endif; ?>
          <input type="file" name="empresa_logo" accept=".jpg,.jpeg,.png,.webp"
                 class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent/10 file:text-accent file:font-medium hover:file:bg-accent/20 transition-colors">
          <p class="text-xs text-gray-400 mt-1">Aparece no topo do site. JPG, PNG, WebP.</p>
        </div>

        <!-- Imagem do Hero -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">🖼️ Imagem de Fundo (Hero)</label>
          <?php if (!empty($config['hero_image'])): ?>
          <div class="mb-2">
            <img src="/uploads/<?= Security::e($config['hero_image']) ?>" alt="Hero atual" class="h-16 w-auto object-cover rounded-lg border border-gray-200">
          </div>
          <?php endif; ?>
          <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp"
                 class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent/10 file:text-accent file:font-medium hover:file:bg-accent/20 transition-colors">
          <p class="text-xs text-gray-400 mt-1">Fundo da seção principal da landing page. Recomendado: 1920×1080 px.</p>
        </div>

        <!-- OG Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Imagem para redes sociais (OG)</label>
          <?php if (!empty($config['og_image'])): ?>
          <div class="mb-2">
            <img src="/uploads/<?= Security::e($config['og_image']) ?>" alt="OG atual" class="h-16 w-auto object-cover rounded-lg border border-gray-200">
          </div>
          <?php endif; ?>
          <input type="file" name="og_image" accept=".jpg,.jpeg,.png,.webp"
                 class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent/10 file:text-accent file:font-medium hover:file:bg-accent/20 transition-colors">
          <p class="text-xs text-gray-400 mt-1">Imagem exibida ao compartilhar o link. 1200×630 px.</p>
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="bg-accent hover:bg-accent-dark text-primary font-bold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-accent/20">
          Salvar Configurações
        </button>
      </div>
    </form>
  </div>

  <!-- SEO -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-800 mb-5 pb-3 border-b border-gray-100">🔍 SEO & Analytics</h2>
    <form method="POST" action="/admin/configuracoes" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Título da página (title tag)</label>
          <input type="text" name="seo_title" value="<?= Security::e($config['seo_title'] ?? '') ?>"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
                 maxlength="200">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Meta description</label>
          <textarea name="seo_description" rows="2"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30 resize-none"
                    maxlength="300"><?= Security::e($config['seo_description'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Keywords</label>
          <input type="text" name="seo_keywords" value="<?= Security::e($config['seo_keywords'] ?? '') ?>"
                 placeholder="barbearia, corte, barba..."
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Google Analytics ID</label>
          <input type="text" name="google_analytics" value="<?= Security::e($config['google_analytics'] ?? '') ?>"
                 placeholder="G-XXXXXXXXXX"
                 class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30">
        </div>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-accent hover:bg-accent-dark text-primary font-bold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-accent/20">
          Salvar SEO
        </button>
      </div>
    </form>
  </div>

  <!-- Alterar Senha -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-800 mb-5 pb-3 border-b border-gray-100">🔑 Alterar Senha</h2>
    <form method="POST" action="/admin/configuracoes/senha" class="space-y-4 max-w-md">
      <input type="hidden" name="_csrf_token" value="<?= Security::e($csrf) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Senha atual</label>
        <input type="password" name="senha_atual" autocomplete="current-password"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
               required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nova senha (mín. 8 caracteres)</label>
        <input type="password" name="nova_senha" autocomplete="new-password" minlength="8"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
               required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar nova senha</label>
        <input type="password" name="confirma_senha" autocomplete="new-password" minlength="8"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent/30"
               required>
      </div>
      <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-8 py-3 rounded-xl transition-colors">
        Alterar Senha
      </button>
    </form>
  </div>
</div>
