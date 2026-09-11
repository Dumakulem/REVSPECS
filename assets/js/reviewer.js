(function () {
  if (typeof pdfjsLib === 'undefined') {
    document.getElementById('pdfContainer').innerHTML =
      '<div class="status error">PDF library failed to load. Please refresh the page.</div>';
    return;
  }

  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

  const PDF_PATH = window.REVIEWER_PDF_PATH;

  let isMobile = window.innerWidth <= 768;
  const DPR = window.devicePixelRatio || 1;
  let pdfDoc = null;
  let currentPage = 1;
  let zoomLevel = 1;
  const pageCanvases = new Map();
  const loadingPages = new Set();
  const container = document.getElementById('pdfContainer');
  const statusEl = document.getElementById('status');

  const fullscreenTarget = document.querySelector('.container');
  let pseudoFullscreen = false;

  const ICON_EXPAND =
    '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" ' +
    'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/>' +
    '<path d="M8 21H5a2 2 0 0 1-2-2v-3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
  const ICON_COMPRESS =
    '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" ' +
    'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M16 3v3a2 2 0 0 0 2 2h3"/>' +
    '<path d="M8 21v-3a2 2 0 0 0-2-2H3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>';

  function currentFsElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
  }

  function isFullscreenActive() {
    return !!currentFsElement() || pseudoFullscreen;
  }

  function setControlsHidden(hidden) {
    fullscreenTarget.classList.toggle('fs-idle', !!hidden);
  }

  function updateFullscreenUI() {
    const active = isFullscreenActive();

    document.querySelectorAll('.fs-icon').forEach(el => {
      el.innerHTML = active ? ICON_COMPRESS : ICON_EXPAND;
    });

    const label = document.getElementById('fullscreenLabel');
    if (label) label.textContent = active ? 'Exit' : 'Fullscreen';

    document.querySelectorAll('.fs-btn').forEach(btn => {
      btn.title = active ? 'Exit fullscreen' : 'Enter fullscreen';
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function enterPseudoFullscreen() {
    pseudoFullscreen = true;
    document.body.classList.add('pseudo-fullscreen');
    updateFullscreenUI();
    scheduleRelayout(50);
  }

  function exitPseudoFullscreen() {
    pseudoFullscreen = false;
    document.body.classList.remove('pseudo-fullscreen');
    setControlsHidden(false);
    updateFullscreenUI();
    scheduleRelayout(50);
  }

  async function toggleFullscreen() {
    if (pseudoFullscreen) { exitPseudoFullscreen(); return; }

    const fsEl = currentFsElement();
    if (fsEl) {
      try {
        if (document.exitFullscreen) await document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
      } catch (err) {
        console.warn('Exit fullscreen failed:', err);
      }
      return;
    }

    const request = fullscreenTarget.requestFullscreen || fullscreenTarget.webkitRequestFullscreen;
    if (!request) { enterPseudoFullscreen(); return; }

    try {
      const result = fullscreenTarget.requestFullscreen
        ? fullscreenTarget.requestFullscreen({ navigationUI: 'hide' })
        : fullscreenTarget.webkitRequestFullscreen();
      if (result && typeof result.then === 'function') await result;
    } catch (err) {
      console.warn('Native fullscreen unavailable, using fallback:', err);
      enterPseudoFullscreen();
    }
  }

  ['fullscreenchange', 'webkitfullscreenchange'].forEach(evt => {
    document.addEventListener(evt, () => {
      updateFullscreenUI();
      if (!isFullscreenActive()) setControlsHidden(false);
      scheduleRelayout(100);
    });
  });

  updateFullscreenUI();

  let resizeTimer = null;
  function scheduleRelayout(delay) {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (!pdfDoc) return;
      isMobile = window.innerWidth <= 768;
      pageCanvases.clear();
      loadingPages.clear();
      container.innerHTML = '';
      renderPage(currentPage).then(() => {
        scrollToPage(currentPage);
        renderPage(currentPage + 1);
      });
    }, delay || 300);
  }

  const loadTimeout = setTimeout(() => {
    if (!pdfDoc) {
      container.innerHTML =
        '<div class="status error">Loading timed out. The PDF may be unavailable or the path is incorrect.</div>';
    }
  }, 15000);

  async function init() {
    try {
      pdfDoc = await pdfjsLib.getDocument(PDF_PATH).promise;
      clearTimeout(loadTimeout);

      document.getElementById('totalPages').textContent = pdfDoc.numPages;
      document.getElementById('jumpInput').max = pdfDoc.numPages;
      document.getElementById('mobilePageIndicator').textContent = `1/${pdfDoc.numPages}`;

      await renderPage(1);
      if (statusEl) statusEl.remove();
      renderPage(2);
      renderPage(3);

      setupEventListeners();
      updatePageIndicator(1);
    } catch (err) {
      console.error(err);
      clearTimeout(loadTimeout);
      container.innerHTML = `<div class="status error">Failed to load PDF: ${err.message || 'Please check the file path.'}</div>`;
    }
  }

  async function renderPage(pageNum) {
    if (pageNum > pdfDoc.numPages || pageCanvases.has(pageNum) || loadingPages.has(pageNum)) return;
    loadingPages.add(pageNum);

    const pageDiv = document.createElement('div');
    pageDiv.className = 'pdf-page';
    pageDiv.id = `page-${pageNum}`;
    pageDiv.dataset.pageNum = pageNum;
    pageDiv.innerHTML = '<div class="page-loading">Loading page ' + pageNum + '...</div>';
    container.appendChild(pageDiv);

    try {
      const page = await pdfDoc.getPage(pageNum);
      const baseViewport = page.getViewport({ scale: 1 });

      let fitWidth;
      if (isMobile) {
        fitWidth = window.innerWidth;
      } else {
        fitWidth = container.clientWidth - 32;
        const maxFitWidth = isFullscreenActive() ? 1200 : 800;
        if (fitWidth > maxFitWidth) fitWidth = maxFitWidth;
      }

      const fitScale = fitWidth / baseViewport.width;
      const displayScale = fitScale * zoomLevel;
      const renderScale = displayScale * DPR;

      const displayWidth = Math.round(baseViewport.width * displayScale);
      const displayHeight = Math.round(baseViewport.height * displayScale);
      const viewport = page.getViewport({ scale: renderScale });

      const canvas = document.createElement('canvas');
      canvas.width = viewport.width;
      canvas.height = viewport.height;
      canvas.style.width = displayWidth + 'px';
      canvas.style.height = displayHeight + 'px';

      const ctx = canvas.getContext('2d');
      ctx.scale(DPR, DPR);
      await page.render({
        canvasContext: ctx,
        viewport: page.getViewport({ scale: displayScale })
      }).promise;

      pageDiv.innerHTML = '';
      pageDiv.appendChild(canvas);
      pageCanvases.set(pageNum, canvas);
    } catch (err) {
      console.error(`Error rendering page ${pageNum}:`, err);
      pageDiv.innerHTML = '<div class="page-loading error">Failed to load page</div>';
    } finally {
      loadingPages.delete(pageNum);
    }
  }

  function setupEventListeners() {
    document.getElementById('prevBtn')?.addEventListener('click', () => navigateToPage(currentPage - 1));
    document.getElementById('nextBtn')?.addEventListener('click', () => navigateToPage(currentPage + 1));
    document.getElementById('mobilePrevBtn')?.addEventListener('click', () => navigateToPage(currentPage - 1));
    document.getElementById('mobileNextBtn')?.addEventListener('click', () => navigateToPage(currentPage + 1));

    document.getElementById('fullscreenBtn')?.addEventListener('click', toggleFullscreen);
    document.getElementById('mobileFullscreenBtn')?.addEventListener('click', toggleFullscreen);

    document.getElementById('hideToolbarBtn')?.addEventListener('click', () => {
      if (isFullscreenActive()) setControlsHidden(true);
    });
    document.getElementById('showToolbarBtn')?.addEventListener('click', () => {
      setControlsHidden(false);
    });

    document.getElementById('jumpBtn')?.addEventListener('click', () => {
      const n = parseInt(document.getElementById('jumpInput').value, 10);
      if (n) navigateToPage(n);
    });
    document.getElementById('jumpInput')?.addEventListener('keypress', e => {
      if (e.key === 'Enter') {
        const n = parseInt(e.target.value, 10);
        if (n) navigateToPage(n);
      }
    });

    document.getElementById('zoomIn')?.addEventListener('click', () => changeZoom(0.25));
    document.getElementById('zoomOut')?.addEventListener('click', () => changeZoom(-0.25));
    document.getElementById('mobileZoomIn')?.addEventListener('click', () => changeZoom(0.5));
    document.getElementById('mobileZoomOut')?.addEventListener('click', () => changeZoom(-0.5));

    let scrollTicking = false;
    container.addEventListener('scroll', () => {
      if (scrollTicking) return;
      scrollTicking = true;
      requestAnimationFrame(() => {
        const { scrollTop, clientHeight, scrollHeight } = container;
        if (scrollTop + clientHeight > scrollHeight - 500) {
          const nextPageToLoad = pageCanvases.size + loadingPages.size + 1;
          if (nextPageToLoad <= pdfDoc.numPages) renderPage(nextPageToLoad);
        }
        updateCurrentPageFromScroll();
        scrollTicking = false;
      });
    });

    document.addEventListener('keydown', e => {
      const tag = (e.target && e.target.tagName) || '';
      const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
      if (e.key === 'ArrowRight') navigateToPage(currentPage + 1);
      else if (e.key === 'ArrowLeft') navigateToPage(currentPage - 1);
      else if ((e.key === 'f' || e.key === 'F') && !typing) toggleFullscreen();
    });

    let touchStartX = 0, touchStartY = 0, isSwiping = false;
    container.addEventListener('touchstart', e => {
      if (e.touches.length === 1) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = false;
      }
    }, { passive: true });
    container.addEventListener('touchmove', e => {
      if (e.touches.length === 1) {
        const dx = e.touches[0].clientX - touchStartX;
        const dy = e.touches[0].clientY - touchStartY;
        if (Math.abs(dx) > 10 && Math.abs(dx) > Math.abs(dy)) isSwiping = true;
      }
    }, { passive: true });
    container.addEventListener('touchend', e => {
      if (e.changedTouches.length !== 1) return;
      const dx = e.changedTouches[0].clientX - touchStartX;
      const dy = e.changedTouches[0].clientY - touchStartY;
      if (!isSwiping && Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) {
        if (dx > 0) navigateToPage(currentPage - 1);
        else navigateToPage(currentPage + 1);
      }
    }, { passive: true });

    window.addEventListener('resize', () => scheduleRelayout(300));
  }

  function changeZoom(delta) {
    const newZoom = zoomLevel + delta;
    if (newZoom >= 0.5 && newZoom <= 3) {
      zoomLevel = newZoom;
      document.getElementById('zoomLabel').textContent = Math.round(zoomLevel * 100) + '%';
      pageCanvases.clear();
      loadingPages.clear();
      container.innerHTML = '<div class="status">Re-rendering...</div>';
      renderPage(currentPage).then(() => {
        const s = container.querySelector('.status');
        if (s) s.remove();
        if (currentPage > 1) renderPage(currentPage - 1);
        renderPage(currentPage + 1);
        scrollToPage(currentPage);
      });
    }
  }

  function navigateToPage(pageNum) {
    if (pageNum < 1 || pageNum > pdfDoc.numPages) return;
    if (!pageCanvases.has(pageNum)) {
      renderPage(pageNum).then(() => scrollToPage(pageNum));
    } else {
      scrollToPage(pageNum);
    }
  }

  function scrollToPage(pageNum) {
    const el = document.getElementById(`page-${pageNum}`);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      updatePageIndicator(pageNum);
    }
  }

  function updateCurrentPageFromScroll() {
    const pages = container.querySelectorAll('.pdf-page');
    const containerTop = container.getBoundingClientRect().top;
    for (const page of pages) {
      const rect = page.getBoundingClientRect();
      if (rect.bottom > containerTop && rect.top < containerTop + 200) {
        updatePageIndicator(parseInt(page.dataset.pageNum));
        break;
      }
    }
  }

  function updatePageIndicator(pageNum) {
    currentPage = pageNum;
    document.getElementById('currentPageNum').textContent = pageNum;
    document.getElementById('jumpInput').value = pageNum;
    document.getElementById('prevBtn').disabled = pageNum === 1;
    document.getElementById('nextBtn').disabled = pageNum === pdfDoc.numPages;
    document.getElementById('mobilePageIndicator').textContent = `${pageNum}/${pdfDoc.numPages}`;
    document.getElementById('mobilePrevBtn').disabled = pageNum === 1;
    document.getElementById('mobileNextBtn').disabled = pageNum === pdfDoc.numPages;
  }

  init();
})();