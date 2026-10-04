(function () {
  const THEME_KEY = 'revspecs-theme';
  const MUSIC_KEY = 'revspecs-music-on';
  const HINT_KEY = 'revspecs-fab-hint-seen';
  const VOLUME = 0.35;
  const TRACKS = [
    { src: 'Music/Clair.mp3', label: 'Clair de Lune — Claude Debussy' },
    { src: 'Music/Snowy.mp3', label: 'Snowdin — Toby Fox' },
    { src: 'Music/Nocturne op. 9 no.2.mp3', label: 'Nocturne op. 9 no. 2 — Frédéric Chopin' }
  ];

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
  const musicPlay = document.getElementById('musicPlay');
  const musicPrevious = document.getElementById('musicPrevious');
  const musicNext = document.getElementById('musicNext');
  const currentTrack = document.getElementById('currentTrack');
  const currentTrackDuplicate = document.getElementById('currentTrackDuplicate');

  [musicPrevious, musicPlay, musicNext].forEach(button => {
    if (!button) return;
    button.addEventListener('pointerup', () => {
      requestAnimationFrame(() => {
        if (document.activeElement === button) button.blur();
      });
    });
  });

  if (musicBtn && bgm) {
    bgm.volume = VOLUME;
    let trackIndex = 0;

    function setTrack(index, shouldPlay) {
      trackIndex = (index + TRACKS.length) % TRACKS.length;
      const track = TRACKS[trackIndex];
      bgm.src = track.src;
      bgm.load();
      if (currentTrack) currentTrack.textContent = `♪ ${track.label}`;
      if (currentTrackDuplicate) currentTrackDuplicate.textContent = `♪ ${track.label}`;
      if (shouldPlay) play();
    }

    function syncUI() {
      const playing = !bgm.paused && !bgm.ended;
      musicBtn.setAttribute('aria-checked', playing ? 'true' : 'false');
      if (musicPlay) {
        const icon = musicPlay.querySelector('.music-play-icon');
        if (icon) icon.classList.toggle('is-playing', playing);
      }
      if (musicPlay) musicPlay.setAttribute('aria-label', playing ? 'Pause current track' : 'Play current track');
    }

    bgm.addEventListener('play', syncUI);
    bgm.addEventListener('pause', syncUI);
    bgm.addEventListener('ended', syncUI);
    bgm.addEventListener('ended', () => setTrack(trackIndex + 1, true));

    function play() {
      const p = bgm.play();
      if (p && p.catch) p.catch(() => {});
    }
    function pause() { bgm.pause(); }

    setTrack(0, false);
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
    if (musicPlay) musicPlay.addEventListener('click', () => {
      if (bgm.paused) play(); else pause();
    });
    if (musicPrevious) musicPrevious.addEventListener('click', () => setTrack(trackIndex - 1, true));
    if (musicNext) musicNext.addEventListener('click', () => setTrack(trackIndex + 1, true));

    window.revspecsMusic = {
      play: () => { play(); try { localStorage.setItem(MUSIC_KEY, '1'); } catch (e) {} },
      pause: () => { pause(); try { localStorage.setItem(MUSIC_KEY, '0'); } catch (e) {} },
      isPlaying: () => !bgm.paused && !bgm.ended
    };
  }
})();