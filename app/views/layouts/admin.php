<?php
use App\models\{Session, Security, ConfiguracaoModel};
if (!isset($content_view) || !is_string($content_view)) {
  throw new RuntimeException('View de conteúdo não definida para layout admin.');
}
$adminNome   = Security::e(Session::get('admin_nome', 'Admin'));
$empresaNome = Security::e(ConfiguracaoModel::get('empresa_nome', 'Barbearia'));
$currentUri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$navItems = [
    ['href' => '/admin/dashboard',    'label' => 'Dashboard',     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['href' => '/admin/agendamentos', 'label' => 'Agendamentos',  'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['href' => '/admin/horarios',     'label' => 'Horários',      'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['href' => '/admin/servicos',     'label' => 'Serviços',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['href' => '/admin/galeria',      'label' => 'Galeria',       'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['href' => '/admin/configuracoes','label' => 'Configurações', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · <?= $empresaNome ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: { DEFAULT: '#0f0f0f', light: '#1a1a1a' },
            accent:  { DEFAULT: '#c9a84c', dark: '#a07c2e' },
          },
          fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    .sidebar-link.active { background: rgba(201,168,76,.15); color:#c9a84c; }
    .sidebar-link.active svg { color:#c9a84c; }
  </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
<div class="flex h-screen overflow-hidden">

  <!-- SIDEBAR -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-primary transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between h-16 px-6 border-b border-white/10">
      <span class="text-white font-semibold text-sm truncate">💈 <?= $empresaNome ?></span>
      <button id="closeSidebar" class="lg:hidden text-white/60 hover:text-white">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
      <?php foreach ($navItems as $item):
        $isActive = str_starts_with($currentUri, $item['href']);
      ?>
      <a href="<?= $item['href'] ?>"
         class="sidebar-link <?= $isActive ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $item['icon'] ?>"/>
        </svg>
        <?= $item['label'] ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <div class="p-4 border-t border-white/10">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-8 h-8 bg-accent/20 rounded-full flex items-center justify-center">
          <span class="text-accent text-xs font-bold"><?= strtoupper(substr($adminNome, 0, 1)) ?></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-white text-xs font-medium truncate"><?= $adminNome ?></p>
          <p class="text-white/40 text-xs">Administrador</p>
        </div>
      </div>
      <div class="flex gap-2">
        <a href="/" target="_blank"
           class="flex-1 text-center text-xs text-white/50 hover:text-accent transition-colors py-1.5 rounded bg-white/5 hover:bg-white/10">
          Ver site
        </a>
        <a href="/admin/logout"
           class="flex-1 text-center text-xs text-white/50 hover:text-red-400 transition-colors py-1.5 rounded bg-white/5 hover:bg-white/10">
          Sair
        </a>
      </div>
    </div>
  </aside>

  <!-- Overlay mobile -->
  <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

  <!-- CONTEÚDO -->
  <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30">
      <button id="openSidebar" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Abrir menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <div class="flex-1 lg:flex-none"></div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">Olá, <?= $adminNome ?></span>
        <a href="/admin/logout" class="text-sm text-red-500 hover:text-red-700 font-medium transition-colors">Sair</a>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 lg:p-6">
      <?php require $content_view; ?>
    </main>
  </div>
</div>

<script>
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const openBtn  = document.getElementById('openSidebar');
  const closeBtn = document.getElementById('closeSidebar');

  function toggleSidebar() {
    const open = !sidebar.classList.contains('-translate-x-full');
    sidebar.classList.toggle('-translate-x-full', open);
    sidebar.classList.toggle('translate-x-0', !open);
    overlay.classList.toggle('hidden', open);
    document.body.style.overflow = open ? '' : 'hidden';
  }

  openBtn.addEventListener('click', toggleSidebar);
  closeBtn.addEventListener('click', toggleSidebar);

  setTimeout(() => {
    document.querySelectorAll('[data-flash]').forEach(el => {
      el.style.transition = 'opacity .5s';
      el.style.opacity    = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 4000);
</script>
</body>
</html>
