(function () {
  const QUIZ_PAGE = 'quiz.php';
  const REVIEWER_PAGE = 'reviewer.php';

  const tabs = document.querySelectorAll('.tab');
  const panel = document.getElementById('panel');
  const grid = document.getElementById('subjectGrid');

  let YEARS = {};
  const dataEl = document.getElementById('yearsData');
  if (dataEl) {
    try { YEARS = JSON.parse(dataEl.textContent); } catch (e) {}
  }

  function renderYear(year) {
    if (!panel || !grid || !YEARS[year]) return;
    panel.dataset.year = year;
    panel.querySelector('.panel-year-bar').dataset.year = year;

    grid.innerHTML = '';
    YEARS[year].subjects.forEach(subject => {
      const card = document.createElement('button');
      card.className = 'subject-card';
      card.type = 'button';
      card.innerHTML = `
        <span class="subject-code">${subject.code}</span>
        <span class="subject-name">${subject.name}</span>
      `;
      card.addEventListener('click', () => openChoice(subject, card));
      grid.appendChild(card);
    });
  }

  function selectTab(tab) {
    tabs.forEach(t => t.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    renderYear(tab.dataset.year);
  }

  tabs.forEach(tab => tab.addEventListener('click', () => selectTab(tab)));

  const tabBar = document.querySelector('.tabs');
  if (tabBar) {
    tabBar.addEventListener('keydown', e => {
      const arr = Array.from(tabs);
      const i = arr.indexOf(document.activeElement);
      if (i === -1) return;
      let next = null;
      if (e.key === 'ArrowRight') next = (i + 1) % arr.length;
      else if (e.key === 'ArrowLeft') next = (i - 1 + arr.length) % arr.length;
      else if (e.key === 'Home') next = 0;
      else if (e.key === 'End') next = arr.length - 1;
      if (next !== null) {
        e.preventDefault();
        arr[next].focus();
        selectTab(arr[next]);
      }
    });
  }

  renderYear(1);

  const choiceOverlay = document.getElementById('choiceOverlay');
  const choiceClose = document.getElementById('choiceClose');
  const choiceCode = document.getElementById('choiceCode');
  const choiceTitle = document.getElementById('choiceTitle');
  const choiceReviewerBtn = document.getElementById('choiceReviewer');
  const choiceQuizBtn = document.getElementById('choiceQuiz');

  const choiceModal = window.revspecsSetupModal(choiceOverlay);

  function openChoice(subject, trigger) {
    choiceCode.textContent = subject.code;
    choiceTitle.textContent = subject.name;

    choiceReviewerBtn.onclick = () => {
      window.open(`${REVIEWER_PAGE}?subject=${encodeURIComponent(subject.code)}`, '_self');
      choiceModal.close();
    };
    choiceQuizBtn.onclick = () => {
      const url = `${QUIZ_PAGE}?subject=${encodeURIComponent(subject.code)}&name=${encodeURIComponent(subject.name)}`;
      window.open(url, '_self');
      choiceModal.close();
    };

    choiceModal.open(trigger);
  }

  choiceClose.addEventListener('click', () => choiceModal.close());

  (function () {
    const titleEl = document.getElementById('revspecsTitle');
    if (!titleEl) return;
    let active = false;

    function enter() {
      if (active) return;
      active = true;
      document.body.classList.add('ambient-active');
      if (window.revspecsMusic) window.revspecsMusic.play();
      const el = document.documentElement;
      const req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
      if (req) {
        const p = req.call(el);
        if (p && p.catch) p.catch(() => {});
      }
    }

    function exit() {
      if (!active) return;
      active = false;
      document.body.classList.remove('ambient-active');
      if (document.fullscreenElement || document.webkitFullscreenElement) {
        const ex = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
        if (ex) {
          const p = ex.call(document);
          if (p && p.catch) p.catch(() => {});
        }
      }
    }

    titleEl.addEventListener('click', e => { e.stopPropagation(); enter(); });
    document.addEventListener('click', () => { if (active) exit(); });
    document.addEventListener('keydown', e => { if (active && e.key === 'Escape') exit(); });

    function onFsChange() {
      const inFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
      if (!inFs && active) exit();
    }
    document.addEventListener('fullscreenchange', onFsChange);
    document.addEventListener('webkitfullscreenchange', onFsChange);
  })();
})();