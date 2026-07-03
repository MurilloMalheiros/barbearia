<!DOCTYPE html>
<?php
$csrf  = isset($csrf) && is_string($csrf) ? $csrf : '';
$flash = isset($flash) && is_array($flash) ? $flash : null;
?>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: {
        primary: { DEFAULT: '#0f0f0f' },
        accent:  { DEFAULT: '#c9a84c', dark: '#a07c2e' },
      }}}
    }
  </script>
</head>
<body class="bg-primary min-h-screen flex items-center justify-center font-sans antialiased">
  <div class="w-full max-w-md px-4">
    <div class="text-center mb-8">
      <p class="text-5xl mb-3">💈</p>
      <h1 class="text-white font-bold text-2xl">Área Administrativa</h1>
      <p class="text-white/40 text-sm mt-1">Barbearia — Painel de Controle</p>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded-xl text-sm text-center <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-green-500/20 text-green-300 border border-green-500/30' ?>">
      <?= \App\models\Security::e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" class="bg-white/5 border border-white/10 rounded-3xl p-8 space-y-5" novalidate>
      <input type="hidden" name="_csrf_token" value="<?= \App\models\Security::e($csrf) ?>">

      <div>
        <label for="email" class="block text-white/70 text-sm font-medium mb-2">E-mail</label>
        <input type="email" id="email" name="email" autocomplete="email"
               class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 rounded-xl px-4 py-3 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
               placeholder="admin@barbearia.com.br" required>
      </div>

      <div>
        <label for="senha" class="block text-white/70 text-sm font-medium mb-2">Senha</label>
        <div class="relative">
          <input type="password" id="senha" name="senha" autocomplete="current-password"
                 class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 rounded-xl px-4 py-3 pr-12 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                 placeholder="••••••••" required>
          <button type="button" id="toggleSenha"
                  class="absolute inset-y-0 right-3 text-white/40 hover:text-white transition-colors"
                  aria-label="Mostrar/ocultar senha">
            <svg id="eyeOff" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
            <svg id="eyeOn" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit"
              class="w-full bg-accent hover:bg-accent-dark text-primary font-bold py-3.5 rounded-xl transition-all duration-300 hover:shadow-lg hover:shadow-accent/30">
        Entrar
      </button>
    </form>

    <p class="text-center mt-6">
      <a href="/" class="text-white/30 hover:text-white/60 text-sm transition-colors">← Voltar ao site</a>
    </p>
  </div>

  <script>
    const senhaInput = document.getElementById('senha');
    const eyeOff     = document.getElementById('eyeOff');
    const eyeOn      = document.getElementById('eyeOn');
    document.getElementById('toggleSenha').addEventListener('click', () => {
      const show = senhaInput.type === 'password';
      senhaInput.type = show ? 'text' : 'password';
      eyeOff.classList.toggle('hidden', show);
      eyeOn.classList.toggle('hidden', !show);
    });
  </script>
</body>
</html>
