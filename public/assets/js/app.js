/**
 * Barbearia — JavaScript principal
 * Vanilla JS puro, sem dependências externas
 */

'use strict';

// ─── Navbar scroll ───────────────────────────────────────────
(function () {
  const header = document.getElementById('header');
  if (!header) return;

  const onScroll = () => {
    header.classList.toggle('scrolled', window.scrollY > 30);
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ─── Menu mobile ─────────────────────────────────────────────
(function () {
  const btn  = document.getElementById('menuBtn');
  const menu = document.getElementById('mobileMenu');
  if (!btn || !menu) return;

  btn.addEventListener('click', () => {
    const isOpen = !menu.classList.contains('hidden');
    menu.classList.toggle('hidden', isOpen);
    btn.setAttribute('aria-expanded', String(!isOpen));
  });

  // Fecha ao clicar em link
  menu.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => {
      menu.classList.add('hidden');
      btn.setAttribute('aria-expanded', 'false');
    })
  );
})();

// ─── Smooth scroll ───────────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});

// ─── Scroll Reveal ───────────────────────────────────────────
(function () {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  els.forEach(el => observer.observe(el));
})();

// ─── Modal Galeria ───────────────────────────────────────────
function abrirModal(data) {
  const modal   = document.getElementById('modal');
  const content = document.getElementById('modalContent');
  const info    = document.getElementById('modalInfo');
  if (!modal) return;

  content.innerHTML = `<img src="${escHtml(data.arquivo)}" alt="${escHtml(data.titulo)}" class="w-full max-h-[70vh] object-contain rounded-xl">`;

  info.innerHTML = data.titulo
    ? `<p class="text-white font-semibold text-lg">${escHtml(data.titulo)}</p>` +
      (data.descricao ? `<p class="text-white/50 text-sm mt-1">${escHtml(data.descricao)}</p>` : '')
    : '';

  modal.classList.remove('hidden');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function fecharModal() {
  const modal = document.getElementById('modal');
  if (!modal) return;
  modal.classList.add('hidden');
  modal.classList.remove('show');
  document.body.style.overflow = '';
}

// Fecha modal com Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') fecharModal();
});

// ─── Agendamento Online ──────────────────────────────────────
(function () {
  const form       = document.getElementById('formAgendamento');
  const dateInput  = document.getElementById('data_agendamento');
  const slotsDiv   = document.getElementById('slotsContainer');
  const horarioHidden = document.getElementById('horarioSelecionado');
  const btnAgendar = document.getElementById('btnAgendar');
  const msgSucesso = document.getElementById('agendamentoSucesso');
  const msgSuccessText = document.getElementById('agendamentoSucessoMsg');
  const msgErro    = document.getElementById('agendamentoErro');

  if (!form || !dateInput || !slotsDiv) return;

  // Busca slots ao mudar data
  dateInput.addEventListener('change', async () => {
    const data = dateInput.value;
    if (!data) return;

    horarioHidden.value = '';
    slotsDiv.innerHTML = '<p class="text-white/40 text-sm italic">Buscando horários disponíveis...</p>';

    try {
      const res  = await fetch(`/agendamento/horarios?data=${encodeURIComponent(data)}`);
      const json = await res.json();

      if (!res.ok || json.error) {
        slotsDiv.innerHTML = `<p class="text-red-400 text-sm">${escHtml(json.error || 'Erro ao buscar horários.')}</p>`;
        return;
      }

      if (!json.slots || json.slots.length === 0) {
        slotsDiv.innerHTML = '<p class="text-white/40 text-sm italic">Nenhum horário disponível nesta data.</p>';
        return;
      }

      slotsDiv.innerHTML = '<div class="flex flex-wrap gap-2"></div>';
      const container = slotsDiv.querySelector('div');

      json.slots.forEach(slot => {
        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'slot-btn';
        btn.textContent = slot;
        btn.addEventListener('click', () => {
          container.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
          horarioHidden.value = slot;
        });
        container.appendChild(btn);
      });
    } catch {
      slotsDiv.innerHTML = '<p class="text-red-400 text-sm">Erro de comunicação. Tente novamente.</p>';
    }
  });

  // Submit
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!horarioHidden.value) {
      mostrarErro('Selecione um horário disponível.');
      return;
    }

    btnAgendar.disabled    = true;
    btnAgendar.textContent = 'Enviando...';

    try {
      const formData = new FormData(form);
      const res      = await fetch('/agendamento', {
        method: 'POST',
        body:   formData,
      });
      const json = await res.json();

      if (json.success) {
        form.reset();
        slotsDiv.innerHTML    = '<p class="text-white/40 text-sm italic">Selecione uma data para ver os horários.</p>';
        horarioHidden.value   = '';
        msgErro.classList.add('hidden');
        msgSucesso.classList.remove('hidden');
        if (msgSuccessText) msgSuccessText.textContent = json.message;
        msgSucesso.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        mostrarErro(json.error || 'Erro ao realizar agendamento.');
      }
    } catch {
      mostrarErro('Erro de comunicação. Verifique sua conexão e tente novamente.');
    } finally {
      btnAgendar.disabled    = false;
      btnAgendar.textContent = 'Confirmar Agendamento';
    }
  });

  function mostrarErro(msg) {
    msgErro.textContent = msg;
    msgErro.classList.remove('hidden');
    msgErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
})();

// ─── Helper: escapa HTML ─────────────────────────────────────
function escHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
