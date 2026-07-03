<?php use App\models\{ConfiguracaoModel, Security}; ?>

<!-- ==================== NAVEGAÇÃO ==================== -->
<header id="header" class="fixed top-0 w-full z-40 transition-all duration-300" role="banner">
  <nav class="container mx-auto px-4 py-4 flex items-center justify-between">
    <a href="/" class="flex items-center gap-3" aria-label="Início">
      <?php $logo = ConfiguracaoModel::get('empresa_logo'); ?>
      <?php if ($logo): ?>
        <img src="/uploads/<?= Security::e((string)$logo) ?>"
             alt="<?= Security::e(ConfiguracaoModel::get('empresa_nome')) ?>"
             class="h-12 w-auto object-contain">
      <?php else: ?>
        <span class="font-serif text-2xl font-bold text-white tracking-wide flex items-center gap-2">
          💈 <?= Security::e(ConfiguracaoModel::get('empresa_nome', 'Barbearia')) ?>
        </span>
      <?php endif; ?>
    </a>

    <ul class="hidden md:flex items-center gap-8 text-white text-sm font-medium" role="list">
      <li><a href="#sobre"       class="hover:text-accent transition-colors">Sobre</a></li>
      <li><a href="#servicos"    class="hover:text-accent transition-colors">Serviços</a></li>
      <li><a href="#galeria"     class="hover:text-accent transition-colors">Galeria</a></li>
      <li><a href="#agendamento" class="bg-accent hover:bg-accent-dark text-primary font-semibold px-5 py-2 rounded-full transition-colors">Agendar</a></li>
    </ul>

    <button id="menuBtn" class="md:hidden text-white p-2" aria-label="Abrir menu" aria-expanded="false">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </nav>

  <div id="mobileMenu" class="hidden md:hidden bg-primary/95 backdrop-blur-sm px-4 pb-4">
    <ul class="flex flex-col gap-4 text-white text-sm font-medium" role="list">
      <li><a href="#sobre"       class="block py-2 hover:text-accent transition-colors">Sobre</a></li>
      <li><a href="#servicos"    class="block py-2 hover:text-accent transition-colors">Serviços</a></li>
      <li><a href="#galeria"     class="block py-2 hover:text-accent transition-colors">Galeria</a></li>
      <li><a href="#agendamento" class="block py-2 text-accent font-semibold">Agendar Horário</a></li>
    </ul>
  </div>
</header>

<!-- ==================== HERO ==================== -->
<?php $heroImage = ConfiguracaoModel::get('hero_image'); ?>
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden bg-primary">

  <?php if ($heroImage): ?>
  <!-- Imagem de fundo configurada pelo admin -->
  <div class="absolute inset-0">
    <img src="/uploads/<?= Security::e($heroImage) ?>"
         alt="Fundo da barbearia"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-primary/70"></div>
  </div>
  <?php else: ?>
  <!-- Fundo padrão (sem imagem) -->
  <div class="absolute inset-0">
    <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary-light to-primary"></div>
    <div class="absolute inset-0 opacity-5"
         style="background-image:repeating-linear-gradient(45deg,#c9a84c 0,#c9a84c 1px,transparent 0,transparent 50%);background-size:20px 20px;"></div>
  </div>
  <?php endif; ?>

  <div class="relative z-10 container mx-auto px-4 text-center">
    <p class="text-accent font-medium tracking-[0.3em] uppercase text-sm mb-6">
      Estilo &amp; Tradição
    </p>
    <h1 class="font-serif text-5xl md:text-7xl lg:text-8xl font-bold text-white leading-tight mb-6">
      <?= Security::e(ConfiguracaoModel::get('empresa_nome', 'Barbearia')) ?>
    </h1>
    <p class="text-white/75 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
      <?= Security::e(ConfiguracaoModel::get('empresa_descricao', 'Cortes modernos e clássicos para o homem contemporâneo.')) ?>
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="#agendamento"
         class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-primary font-semibold px-8 py-4 rounded-full transition-all duration-300 hover:shadow-lg hover:shadow-accent/30 hover:-translate-y-0.5">
        Agendar Horário
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </a>
      <a href="#servicos"
         class="inline-flex items-center gap-2 border-2 border-white/30 hover:border-accent text-white font-semibold px-8 py-4 rounded-full transition-all duration-300">
        Ver Serviços
      </a>
    </div>
  </div>

  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
    <svg class="w-6 h-6 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </div>
</section>

<!-- ==================== SOBRE ==================== -->
<section id="sobre" class="py-24 bg-cream reveal">
  <div class="container mx-auto px-4">
    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-accent font-medium tracking-widest uppercase text-xs mb-4">Nossa História</p>
        <h2 class="font-serif text-4xl md:text-5xl font-bold text-primary mb-6 leading-tight">
          Arte &amp; precisão <br>em cada corte
        </h2>
        <p class="text-primary/70 text-lg leading-relaxed mb-6">
          Combinamos técnicas tradicionais de barbearia com as tendências mais modernas para garantir que você
          saia daqui sempre com a melhor versão de si mesmo.
        </p>
        <p class="text-primary/70 leading-relaxed mb-8">
          Cada detalhe importa: o corte, a barba, o acabamento. Nossa equipe é apaixonada pelo que faz
          e comprometida com a sua satisfação.
        </p>
        <div class="grid grid-cols-3 gap-6">
          <div class="text-center">
            <p class="font-serif text-4xl font-bold text-accent">10+</p>
            <p class="text-primary/60 text-sm mt-1">Anos de experiência</p>
          </div>
          <div class="text-center">
            <p class="font-serif text-4xl font-bold text-accent">5k+</p>
            <p class="text-primary/60 text-sm mt-1">Clientes satisfeitos</p>
          </div>
          <div class="text-center">
            <p class="font-serif text-4xl font-bold text-accent">100%</p>
            <p class="text-primary/60 text-sm mt-1">Satisfação garantida</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <?php $diferenciais = [
          ['icon' => '✂️', 'title' => 'Cortes Precisos',    'desc' => 'Técnicas apuradas para cortes que valorizam seu estilo e tipo de rosto.'],
          ['icon' => '🪒', 'title' => 'Barba Impecável',    'desc' => 'Modelagem e hidratação da barba com navalha e toalha quente.'],
          ['icon' => '💈', 'title' => 'Ambiente Exclusivo', 'desc' => 'Espaço confortável e reservado para você relaxar enquanto se cuida.'],
          ['icon' => '⭐', 'title' => 'Produtos Premium',   'desc' => 'Utilizamos apenas produtos de alta qualidade para cuidar do seu cabelo e barba.'],
        ]; foreach ($diferenciais as $d): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
          <div class="text-3xl mb-3"><?= $d['icon'] ?></div>
          <h3 class="font-semibold text-primary mb-2"><?= $d['title'] ?></h3>
          <p class="text-primary/60 text-sm leading-relaxed"><?= $d['desc'] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ==================== SERVIÇOS ==================== -->
<?php if (!empty($servicos)): ?>
<section id="servicos" class="py-24 bg-primary reveal">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <p class="text-accent font-medium tracking-widest uppercase text-xs mb-4">O que oferecemos</p>
      <h2 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">Nossos Serviços</h2>
      <p class="text-white/60 max-w-xl mx-auto">Do corte clássico ao tratamento completo, temos tudo para manter você sempre impecável.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
      <?php foreach ($servicos as $srv): ?>
      <div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-accent/40 rounded-2xl p-8 transition-all duration-300 hover:-translate-y-1 group">
        <?php if ($srv['icone']): ?>
        <div class="text-4xl mb-4"><?= Security::e($srv['icone']) ?></div>
        <?php endif; ?>
        <h3 class="font-serif text-xl font-bold text-white mb-3"><?= Security::e($srv['nome']) ?></h3>
        <?php if ($srv['descricao']): ?>
        <p class="text-white/60 leading-relaxed text-sm mb-4"><?= Security::e($srv['descricao']) ?></p>
        <?php endif; ?>
        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/10">
          <?php if ($srv['preco']): ?>
          <span class="text-accent font-bold text-lg">
            R$ <?= number_format((float)$srv['preco'], 2, ',', '.') ?>
          </span>
          <?php endif; ?>
          <?php if ($srv['duracao_minutos']): ?>
          <span class="text-white/40 text-xs">⏱ <?= (int)$srv['duracao_minutos'] ?> min</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== GALERIA ==================== -->
<?php if (!empty($imagens)): ?>
<section id="galeria" class="py-24 bg-cream reveal">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <p class="text-accent font-medium tracking-widest uppercase text-xs mb-4">Portfólio</p>
      <h2 class="font-serif text-4xl md:text-5xl font-bold text-primary mb-4">Nossos Trabalhos</h2>
      <p class="text-primary/60 max-w-xl mx-auto">Cada corte conta uma história. Confira alguns dos nossos trabalhos.</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-w-6xl mx-auto">
      <?php foreach ($imagens as $img): ?>
      <button type="button"
              class="group relative aspect-square overflow-hidden rounded-xl bg-gray-200 cursor-pointer"
              onclick="abrirModal(<?= htmlspecialchars(json_encode([
                'titulo'    => $img['titulo'],
                'descricao' => $img['descricao'] ?? '',
                'arquivo'   => '/uploads/' . $img['arquivo'],
              ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)"
              aria-label="Ver: <?= Security::e($img['titulo']) ?>">
        <img src="/uploads/<?= Security::e((string)$img['arquivo']) ?>"
             alt="<?= Security::e($img['titulo']) ?>"
             loading="lazy"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/60 transition-all duration-300 flex items-end">
          <p class="opacity-0 group-hover:opacity-100 text-white text-xs font-medium p-3 transition-opacity duration-300 line-clamp-2">
            <?= Security::e($img['titulo']) ?>
          </p>
        </div>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ==================== AGENDAMENTO ==================== -->
<section id="agendamento" class="py-24 bg-primary reveal">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <p class="text-accent font-medium tracking-widest uppercase text-xs mb-4">Reserve seu horário</p>
      <h2 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">Agendar Online</h2>
      <p class="text-white/60 max-w-xl mx-auto">Escolha o dia, horário e serviço desejado. É rápido e fácil!</p>
    </div>

    <?php if (empty($diasAtivos)): ?>
    <div class="max-w-lg mx-auto text-center bg-white/5 rounded-2xl p-12 border border-white/10">
      <p class="text-4xl mb-4">🗓️</p>
      <p class="text-white/70 text-lg">Agendamento online em breve. Entre em contato pelo WhatsApp!</p>
    </div>
    <?php else: ?>

    <!-- Formulário de agendamento -->
    <div class="max-w-2xl mx-auto">
      <div id="agendamentoSucesso" class="hidden bg-green-500/20 border border-green-500/40 text-green-300 rounded-2xl p-6 mb-6 text-center">
        <p class="text-2xl mb-2">✅</p>
        <p class="font-semibold text-lg" id="agendamentoSucessoMsg">Agendamento realizado!</p>
      </div>

      <div id="agendamentoErro" class="hidden bg-red-500/20 border border-red-500/40 text-red-300 rounded-2xl p-4 mb-6 text-center" id="agendamentoErroMsg">
      </div>

      <form id="formAgendamento" class="bg-white/5 border border-white/10 rounded-3xl p-8 space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= Security::e(App\models\Session::generateCsrfToken()) ?>">

        <!-- Data -->
        <div>
          <label for="data_agendamento" class="block text-white/80 text-sm font-medium mb-2">
            📅 Data desejada
          </label>
          <input type="date"
                 id="data_agendamento"
                 name="data_agendamento"
                 min="<?= date('Y-m-d') ?>"
                 class="w-full bg-white/10 border border-white/20 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                 required>
          <p class="text-white/40 text-xs mt-1">Dias disponíveis são habilitados automaticamente.</p>
        </div>

        <!-- Horários disponíveis -->
        <div>
          <label class="block text-white/80 text-sm font-medium mb-2">⏰ Horário disponível</label>
          <div id="slotsContainer" class="min-h-[48px]">
            <p class="text-white/40 text-sm italic">Selecione uma data para ver os horários disponíveis.</p>
          </div>
          <input type="hidden" id="horarioSelecionado" name="horario" required>
        </div>

        <!-- Serviço -->
        <?php if (!empty($servicos)): ?>
        <div>
          <label for="servico_id" class="block text-white/80 text-sm font-medium mb-2">✂️ Serviço (opcional)</label>
          <select id="servico_id" name="servico_id"
                  class="w-full bg-white/10 border border-white/20 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-accent transition-colors">
            <option value="" class="bg-primary">Selecione um serviço...</option>
            <?php foreach ($servicos as $srv): ?>
            <option value="<?= (int)$srv['id'] ?>" class="bg-primary">
              <?= Security::e($srv['nome']) ?>
              <?php if ($srv['preco']): ?> — R$ <?= number_format((float)$srv['preco'], 2, ',', '.') ?><?php endif; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <!-- Nome e Telefone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="cliente_nome" class="block text-white/80 text-sm font-medium mb-2">👤 Seu nome</label>
            <input type="text" id="cliente_nome" name="cliente_nome" placeholder="Nome completo"
                   class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 rounded-xl px-4 py-3 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                   required minlength="3" maxlength="200">
          </div>
          <div>
            <label for="cliente_telefone" class="block text-white/80 text-sm font-medium mb-2">📱 WhatsApp / Telefone</label>
            <input type="tel" id="cliente_telefone" name="cliente_telefone" placeholder="(11) 99999-9999"
                   class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 rounded-xl px-4 py-3 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                   required>
          </div>
        </div>

        <!-- Observações -->
        <div>
          <label for="observacoes" class="block text-white/80 text-sm font-medium mb-2">💬 Observações (opcional)</label>
          <textarea id="observacoes" name="observacoes" rows="2"
                    placeholder="Alguma preferência ou informação adicional..."
                    class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 rounded-xl px-4 py-3 focus:outline-none focus:border-accent transition-colors resize-none"></textarea>
        </div>

        <button type="submit" id="btnAgendar"
                class="w-full bg-accent hover:bg-accent-dark text-primary font-bold text-lg py-4 rounded-2xl transition-all duration-300 hover:shadow-lg hover:shadow-accent/30 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
          Confirmar Agendamento
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ==================== CONTATO ==================== -->
<section id="contato" class="py-24 bg-cream relative overflow-hidden reveal">
  <div class="container mx-auto px-4 text-center">
    <p class="text-accent font-medium tracking-widest uppercase text-xs mb-4">Fale Conosco</p>
    <h2 class="font-serif text-4xl md:text-5xl font-bold text-primary mb-6">
      Tem alguma dúvida?
    </h2>
    <p class="text-primary/60 text-lg max-w-xl mx-auto mb-10">
      Entre em contato pelo WhatsApp ou visite-nos. Ficamos felizes em atendê-lo!
    </p>

    <?php $wa = ConfiguracaoModel::get('empresa_whatsapp'); ?>
    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center flex-wrap">
      <?php if ($wa): ?>
      <a href="https://wa.me/<?= Security::e(preg_replace('/\D/', '', $wa)) ?>?text=Olá!%20Gostaria%20de%20mais%20informações."
         target="_blank" rel="noopener noreferrer"
         class="inline-flex items-center gap-3 bg-green-500 hover:bg-green-600 text-white font-semibold text-lg px-10 py-5 rounded-full transition-all duration-300 hover:shadow-2xl hover:shadow-green-500/30 hover:-translate-y-1">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Falar no WhatsApp
      </a>
      <?php endif; ?>

      <?php $instagram = ConfiguracaoModel::get('instagram'); if ($instagram): ?>
      <a href="<?= Security::e($instagram) ?>" target="_blank" rel="noopener noreferrer"
         class="inline-flex items-center gap-3 border-2 border-primary/20 hover:border-accent text-primary font-semibold text-lg px-10 py-5 rounded-full transition-all duration-300 hover:-translate-y-1">
        Instagram
      </a>
      <?php endif; ?>
    </div>

    <?php $endereco = ConfiguracaoModel::get('endereco'); if ($endereco): ?>
    <p class="mt-8 text-primary/50 text-sm">📍 <?= Security::e($endereco) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- ==================== RODAPÉ ==================== -->
<footer class="bg-primary py-10">
  <div class="container mx-auto px-4 text-center">
    <p class="font-serif text-2xl font-bold text-white mb-2">
      💈 <?= Security::e(ConfiguracaoModel::get('empresa_nome', 'Barbearia')) ?>
    </p>
    <p class="text-white/40 text-sm mb-6"><?= Security::e(ConfiguracaoModel::get('empresa_descricao', '')) ?></p>
    <p class="text-white/20 text-xs">
      &copy; <?= date('Y') ?> <?= Security::e(ConfiguracaoModel::get('empresa_nome', 'Barbearia')) ?> — Todos os direitos reservados
    </p>
  </div>
</footer>

<!-- ==================== MODAL GALERIA ==================== -->
<div id="modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-label="Visualizar imagem">
  <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="fecharModal()"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-4xl w-full">
      <button onclick="fecharModal()"
              class="absolute top-4 right-4 text-white/60 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-all"
              aria-label="Fechar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
      <div id="modalContent" class="rounded-2xl overflow-hidden"></div>
      <div id="modalInfo" class="mt-4 text-center"></div>
    </div>
  </div>
</div>
