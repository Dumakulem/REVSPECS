(function () {
  const THEME_KEY = 'revspecs-theme';
  const MUSIC_KEY = 'revspecs-music-on';
  const HINT_KEY = 'revspecs-fab-hint-seen';
  const VOLUME = 0.35;

  const overlays = [];

  function setupModal(overlay, opts) {
    opts = opts || {};
    let lastFocused = null;

    function getFocusable() {
      return Array.from(
        overlay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')
      ).filter(el => el.offsetParent !== null);
    }

    function open(trigger) {
      lastFocused = trigger || document.activeElement;
      overlay.hidden = false;
      document.body.classList.add('modal-open');
      if (opts.onOpen) opts.onOpen();
      const focusable = getFocusable();
      (focusable[0] || overlay).focus();
    }

    function close() {
      if (overlay.hidden) return;
      overlay.hidden = true;
      document.body.classList.remove('modal-open');
      if (opts.onClose) opts.onClose();
      if (lastFocused) lastFocused.focus();
    }

    overlay.addEventListener('keydown', e => {
      if (e.key !== 'Tab') return;
      const focusable = getFocusable();
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    });

    overlay.addEventListener('click', e => {
      if (e.target === overlay) close();
    });

    const ctrl = { overlay, open, close };
    overlays.push(ctrl);
    return ctrl;
  }

  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    overlays.forEach(({ overlay, close }) => { if (!overlay.hidden) close(); });
  });

  window.revspecsSetupModal = setupModal;

  const aboutBtn = document.getElementById('aboutBtn');
  const aboutOverlay = document.getElementById('aboutOverlay');
  const aboutClose = document.getElementById('aboutClose');

  if (aboutBtn && aboutOverlay && aboutClose) {
    const aboutModal = setupModal(aboutOverlay, {
      onOpen: () => aboutBtn.setAttribute('aria-expanded', 'true'),
      onClose: () => aboutBtn.setAttribute('aria-expanded', 'false')
    });
    aboutBtn.addEventListener('click', () => aboutModal.open(aboutBtn));
    aboutClose.addEventListener('click', () => aboutModal.close());

    (function () {
      const hint = document.getElementById('fabHint');
      if (!hint) return;
      try {
        if (localStorage.getItem(HINT_KEY) === '1') { hint.remove(); return; }
      } catch (e) {}

      let dismissed = false;
      function dismiss() {
        if (dismissed) return;
        dismissed = true;
        hint.classList.add('is-hidden');
        setTimeout(() => hint.remove(), 400);
        try { localStorage.setItem(HINT_KEY, '1'); } catch (e) {}
      }
      const timer = setTimeout(dismiss, 9000);
      aboutBtn.addEventListener('click', () => {
        clearTimeout(timer);
        dismiss();
      }, { once: true });
    })();
  }

  const themeToggle = document.getElementById('themeToggle');
  const themeColorMeta = document.getElementById('themeColorMeta');

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      if (themeToggle) themeToggle.setAttribute('aria-checked', 'true');
      if (themeColorMeta) themeColorMeta.setAttribute('content', '#0F1E18');
    } else {
      document.documentElement.removeAttribute('data-theme');
      if (themeToggle) themeToggle.setAttribute('aria-checked', 'false');
      if (themeColorMeta) themeColorMeta.setAttribute('content', '#FFFFFF');
    }
  }

  applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const next = themeToggle.getAttribute('aria-checked') === 'true' ? 'light' : 'dark';
      applyTheme(next);
      try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
    });
  }

  window.addEventListener('storage', e => {
    if (e.key !== THEME_KEY) return;
    applyTheme(e.newValue === 'dark' ? 'dark' : 'light');
  });

  const musicBtn = document.getElementById('musicToggle');
  const bgm = document.getElementById('bgm');

  if (musicBtn && bgm) {
    bgm.volume = VOLUME;

    function syncUI() {
      const playing = !bgm.paused && !bgm.ended;
      musicBtn.setAttribute('aria-checked', playing ? 'true' : 'false');
    }

    bgm.addEventListener('play', syncUI);
    bgm.addEventListener('pause', syncUI);
    bgm.addEventListener('ended', syncUI);

    function play() {
      const p = bgm.play();
      if (p && p.catch) p.catch(() => {});
    }
    function pause() { bgm.pause(); }

    syncUI();

    try {
      if (localStorage.getItem(MUSIC_KEY) === '1') play();
    } catch (e) {}

    musicBtn.addEventListener('click', () => {
      if (bgm.paused) play(); else pause();
      try {
        localStorage.setItem(MUSIC_KEY, bgm.paused ? '0' : '1');
      } catch (e) {}
    });

    window.revspecsMusic = {
      play: () => { play(); try { localStorage.setItem(MUSIC_KEY, '1'); } catch (e) {} },
      pause: () => { pause(); try { localStorage.setItem(MUSIC_KEY, '0'); } catch (e) {} },
      isPlaying: () => !bgm.paused && !bgm.ended
    };
  }
})();