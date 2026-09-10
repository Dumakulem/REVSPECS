<?php
$subjects = include 'subjects-config.php';
$subjectCode = $_GET['subject'] ?? '';
$subject = $subjects[$subjectCode] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RevSpecs — <?php echo htmlspecialchars($subject['name'] ?? 'Reviewer'); ?></title>
    <script>
      (function () {
        try {
          var stored = localStorage.getItem('revspecs-theme');
          var wantsDark = stored ? stored === 'dark'
                                 : window.matchMedia('(prefers-color-scheme: dark)').matches;
          if (wantsDark) document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
      })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        :root {
            --toolbar-height: 56px;
            --controls-height: 60px;
            --bg: #f0f2f5;
            --panel: #ffffff;
            --text: #1b4332;
            --text-soft: #5c8374;
            --border: #d1e7dd;
            --accent: #e67e22;
            --accent-dark: #cf6c1b;
            --download: #27ae60;
            --toolbar-bg: #f8f9fa;
            --viewer-bg: #e9ecef;
            /* paper = the blank area behind a PDF page */
            --paper-bg: #ffffff;
            --paper-shadow: 0 2px 8px rgba(0,0,0,0.15);
            color-scheme: light;
        }

        [data-theme="dark"] {
            --bg: #0F1E18;
            --panel: #16281F;
            --text: #E9F5EE;
            --text-soft: #93BCA9;
            --border: #2A4638;
            --accent: #F0964C;
            --accent-dark: #E67E22;
            --download: #35C97A;
            --toolbar-bg: #1b2f25;
            --viewer-bg: #0a1410;
            --paper-bg: #1a1a1a;
            --paper-shadow: 0 2px 10px rgba(0,0,0,0.55);
            color-scheme: dark;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: var(--bg);
            color: var(--text);
            overscroll-behavior: none;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* ---------- Desktop ---------- */
        @media (min-width: 769px) {
            .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
            .back-link {
                display: inline-block; font-family: 'Roboto Condensed', sans-serif;
                font-weight: 700; font-size: 0.9rem; color: var(--text-soft);
                text-decoration: none; margin-bottom: 12px;
            }
            .back-link:hover { color: var(--accent); }
            header {
                background: var(--panel); border-radius: 12px; padding: 20px;
                margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                transition: background-color 0.2s ease;
            }
            h1 { margin: 0 0 4px; font-family: 'Roboto Condensed', sans-serif; }
            .tagline { color: var(--text-soft); font-size: 0.9rem; margin: 0; }
            .viewer-card {
                background: var(--panel); border-radius: 12px; padding: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                transition: background-color 0.2s ease;
            }
            .toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: nowrap;
                gap: 12px;
                margin-bottom: 16px;
                padding: 10px 14px;
                background: var(--toolbar-bg);
                border-radius: 8px;
                border: 1px solid var(--border);
                transition: background-color 0.2s ease, border-color 0.2s ease;
            }
            .toolbar-left, .toolbar-right {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: nowrap;
                min-width: 0;
            }
            .toolbar-left { flex-shrink: 1; }
            .toolbar-right { flex-shrink: 0; }
            .toolbar .btn,
            .toolbar .download-btn-desktop { padding: 8px 12px; }
            .toolbar .fs-toggle { padding: 8px 12px; }

            #pdfContainer {
                display: flex; flex-direction: column; align-items: stretch; gap: 16px;
                max-height: 80vh; overflow-y: auto; overflow-x: auto; padding: 16px;
                background: var(--viewer-bg); border-radius: 8px; -webkit-overflow-scrolling: touch;
                transition: background-color 0.2s ease;
            }
            .pdf-page {
                background: var(--paper-bg); box-shadow: var(--paper-shadow);
                border-radius: 4px; overflow: hidden; flex-shrink: 0;
                margin: 0 auto;
                transition: background-color 0.2s ease;
            }
            .pdf-page canvas { display: block; }

            /* ============================================================
               DARK MODE FOR THE PDF PAGES THEMSELVES
               Smart-invert: flip luminance, then rotate hue back 180° so
               colored photos and diagrams don't turn into their negative.
               Runs on the GPU, so toggling the theme is instant.
               ============================================================ */
            [data-theme="dark"] .pdf-page canvas {
                filter: invert(1) hue-rotate(180deg);
            }
            /* Blend the invert seam against any page padding the canvas
               doesn't cover (e.g. during re-render) */
            [data-theme="dark"] .pdf-page { isolation: isolate; }

            .jump-row {
                margin-top: 12px; display: flex; align-items: center; justify-content: center;
                gap: 8px; font-size: 0.85rem; color: var(--text-soft);
            }
            .jump-row input {
                width: 60px; padding: 6px; border: 2px solid var(--border);
                border-radius: 6px; text-align: center; font-family: inherit;
                background: var(--panel); color: var(--text);
            }
            footer { margin-top: 20px; text-align: center; font-size: 0.8rem; color: var(--text-soft); }
            .mobile-toolbar, .mobile-controls { display: none; }
        }

        /* ---------- Mobile ---------- */
        @media (max-width: 768px) {
            body { height: 100vh; height: 100dvh; overflow: hidden; position: fixed; width: 100%; }
            .container { height: 100vh; height: 100dvh; display: flex; flex-direction: column; padding: 0; }

            .back-link, header, #desktopToolbar, .jump-row, footer { display: none; }

            .mobile-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: var(--panel);
                padding: 0 12px;
                height: var(--toolbar-height);
                border-bottom: 1px solid var(--border);
                box-shadow: 0 2px 4px rgba(0,0,0,0.08);
                z-index: 10;
                flex-shrink: 0;
                transition: background-color 0.2s ease;
            }
            .mobile-toolbar .back-btn {
                background: none;
                border: none;
                color: var(--text);
                font-size: 1.6rem;
                cursor: pointer;
                padding: 0 8px;
                text-decoration: none;
                display: flex;
                align-items: center;
            }
            .mobile-toolbar .title {
                flex: 1;
                min-width: 0;
                text-align: center;
                font-family: 'Roboto Condensed', sans-serif;
                font-weight: 700;
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                margin: 0 8px;
            }
            .mobile-toolbar .page-indicator {
                font-size: 0.8rem;
                color: var(--text-soft);
                white-space: nowrap;
                flex-shrink: 0;
            }
            .viewer-card { flex: 1; display: flex; flex-direction: column; min-height: 0; }
            #pdfContainer {
                flex: 1; overflow-y: auto; overflow-x: auto;
                background: #525659;
                padding: 8px 0; -webkit-overflow-scrolling: touch; display: flex;
                flex-direction: column; align-items: stretch; min-height: 0;
                touch-action: pan-x pan-y pinch-zoom;
            }
            .pdf-page {
                margin: 4px auto; box-shadow: var(--paper-shadow);
                background: var(--paper-bg); flex-shrink: 0; overflow: hidden;
                transition: background-color 0.2s ease;
            }
            .pdf-page canvas { display: block; }

            /* Same smart-invert on phones */
            [data-theme="dark"] .pdf-page canvas {
                filter: invert(1) hue-rotate(180deg);
            }

            .mobile-controls {
                display: flex;
                align-items: center;
                justify-content: space-around;
                gap: 4px;
                background: var(--panel);
                padding: 6px 8px;
                padding-bottom: calc(6px + env(safe-area-inset-bottom));
                border-top: 1px solid var(--border);
                box-shadow: 0 -2px 4px rgba(0,0,0,0.08);
                min-height: var(--controls-height);
                flex-shrink: 0;
                z-index: 10;
                transition: background-color 0.2s ease;
            }
            .mobile-controls .nav-btn {
                flex: 1;
                max-width: 80px;
                padding: 10px;
                background: var(--accent);
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 700;
                font-family: 'Roboto Condensed', sans-serif;
                font-size: 0.85rem;
                cursor: pointer;
                touch-action: manipulation;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
            }
            .mobile-controls .nav-btn:active { background: var(--accent-dark); transform: scale(0.95); }
            .mobile-controls .nav-btn:disabled { opacity: 0.4; background: var(--border); color: var(--text-soft); }
            .mobile-controls .zoom-btn {
                background: var(--panel);
                border: 2px solid var(--border);
                color: var(--text);
                width: 44px;
                height: 44px;
                flex-shrink: 0;
                border-radius: 50%;
                font-size: 1.3rem;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                touch-action: manipulation;
            }
            .mobile-controls .zoom-btn:active { background: var(--border); }
            .mobile-controls .download-btn {
                background: var(--download);
                color: white;
                height: 44px;
                border-radius: 22px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                font-size: 0.9rem;
                padding: 0 12px;
                gap: 4px;
                touch-action: manipulation;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .mobile-controls .download-btn:active { background: var(--accent-dark); }
        }

        /* ---------- Extra-narrow phones ---------- */
        @media (max-width: 360px) {
            .mobile-controls { gap: 2px; padding-left: 4px; padding-right: 4px; }
            .mobile-controls .nav-btn { max-width: 56px; padding: 10px 4px; font-size: 0.78rem; }
            .mobile-controls .zoom-btn { width: 40px; height: 40px; font-size: 1.1rem; }
            .mobile-controls .download-btn { padding: 0 8px; font-size: 0.78rem; height: 40px; border-radius: 20px; }
            .mobile-toolbar .title { font-size: 0.9rem; }
        }

        /* Shared button styles */
        .btn {
            background: var(--panel);
            border: 2px solid var(--border);
            color: var(--text);
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 700;
            font-size: 0.875rem;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            white-space: nowrap;
        }
        .btn:hover { background: var(--accent); border-color: var(--accent); color: white; }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn:disabled:hover { background: var(--panel); border-color: var(--border); color: var(--text); }
        .download-btn-desktop {
            background: var(--download);
            border: none;
            color: white;
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 700;
            font-size: 0.875rem;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.2s;
        }
        .download-btn-desktop:hover { background: var(--accent-dark); }

        .page-loading { padding: 20px; text-align: center; color: var(--text-soft); }
        .status { padding: 40px 0; text-align: center; color: var(--text-soft); }
        .error { color: #c0392b; }
        [data-theme="dark"] .error { color: #ff8a80; }

        /* ---------- Fullscreen toggle ---------- */
        .fs-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .fs-icon { display: inline-flex; align-items: center; }
        .fs-icon svg { display: block; }

        .mobile-toolbar .icon-btn {
            background: none;
            border: none;
            color: var(--text);
            padding: 8px 6px;
            margin-left: 4px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            touch-action: manipulation;
        }
        .mobile-toolbar .icon-btn:active { background: var(--border); }

        /* ---------- Toolbar back button ---------- */
        .toolbar-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-shrink: 0;
            text-decoration: none;
        }

        /* ---------- Fullscreen layout ---------- */
        .container:fullscreen,
        body.pseudo-fullscreen .container {
            max-width: none;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            background: var(--bg);
        }
        body.pseudo-fullscreen { overflow: hidden; }
        body.pseudo-fullscreen .container {
            position: fixed;
            inset: 0;
            z-index: 9999;
        }
        .container::backdrop { background: #000; }

        .container:fullscreen .back-link,
        .container:fullscreen header,
        .container:fullscreen footer,
        body.pseudo-fullscreen .back-link,
        body.pseudo-fullscreen header,
        body.pseudo-fullscreen footer {
            display: none;
        }

        .container:fullscreen .viewer-card,
        body.pseudo-fullscreen .viewer-card {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 8px;
            border-radius: 0;
            box-shadow: none;
        }
        @media (max-width: 768px) {
            .container:fullscreen .viewer-card,
            body.pseudo-fullscreen .viewer-card { padding: 0; }
        }

        .container:fullscreen #pdfContainer,
        body.pseudo-fullscreen #pdfContainer {
            flex: 1;
            min-height: 0;
            max-height: none;
            border-radius: 0;
        }

        .container:fullscreen .toolbar,
        .container:fullscreen .jump-row,
        body.pseudo-fullscreen .toolbar,
        body.pseudo-fullscreen .jump-row {
            flex-shrink: 0;
        }

        /* Hide the download button while in fullscreen */
        .container:fullscreen .download-btn-desktop,
        .container:fullscreen .download-btn,
        body.pseudo-fullscreen .download-btn-desktop,
        body.pseudo-fullscreen .download-btn {
            display: none !important;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Mobile Toolbar -->
    <div class="mobile-toolbar" id="mobileToolbar">
        <a href=".\" class="back-btn">←</a>
        <span class="title"><?php echo htmlspecialchars($subject['name'] ?? 'Reviewer'); ?></span>
        <span class="page-indicator" id="mobilePageIndicator">1/1</span>
        <?php if ($subject): ?>
        <button type="button" class="icon-btn fs-btn" id="mobileFullscreenBtn"
                aria-label="Toggle fullscreen" aria-pressed="false" title="Enter fullscreen">
            <span class="fs-icon"></span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Desktop Header -->
    <a class="back-link" href=".\>&larr; Back to RevSpecs</a>
    <header>
        <h1><?php echo htmlspecialchars($subject['name'] ?? 'Reviewer not found'); ?></h1>
        <p class="tagline">
            <?php echo $subject ? 'Scroll through the pages or use the navigation controls.' : 'That subject code isn\'t in subjects-config.php.'; ?>
        </p>
    </header>

    <div class="viewer-card">
        <?php if (!$subject): ?>
            <div class="status error">
                No reviewer found for "<?php echo htmlspecialchars($subjectCode); ?>".<br>
                <span style="font-size:0.85rem;">Check the subject code against subjects-config.php.</span>
            </div>
        <?php else: ?>
            <!-- Desktop Toolbar -->
            <div class="toolbar" id="desktopToolbar">
                <div class="toolbar-left">
                    <a href=".\" class="btn toolbar-back" title="Back to RevSpecs">← Back</a>
                    <button class="btn" id="prevBtn">← Prev</button>
                    <span>Page <strong id="currentPageNum">1</strong> of <span id="totalPages">?</span></span>
                    <button class="btn" id="nextBtn">Next →</button>
                </div>
                <div class="toolbar-right">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button class="btn" id="zoomOut">−</button>
                        <span id="zoomLabel" style="min-width:40px; text-align:center;">100%</span>
                        <button class="btn" id="zoomIn">+</button>
                    </div>
                    <button class="btn fs-toggle fs-btn" id="fullscreenBtn" type="button"
                            aria-pressed="false" title="Enter fullscreen">
                        <span class="fs-icon"></span><span id="fullscreenLabel">Fullscreen</span>
                    </button>
                    <a href="<?php echo htmlspecialchars($subject['pdf']); ?>" download class="download-btn-desktop">⬇ Download</a>
                </div>
            </div>

            <!-- PDF Container -->
            <div id="pdfContainer">
                <div class="status" id="status">Loading reviewer...</div>
            </div>

            <!-- Jump to page (desktop only) -->
            <div class="jump-row">
                Jump to page
                <input type="number" id="jumpInput" min="1" value="1">
                <button class="btn" id="jumpBtn">Go</button>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        RevSpecs · Reviewer PDFs are uploaded by SPECS officers, not students.
    </footer>

    <!-- Mobile Bottom Controls -->
    <div class="mobile-controls" id="mobileControls">
        <button class="nav-btn" id="mobilePrevBtn">←</button>
        <button class="zoom-btn" id="mobileZoomOut">−</button>
        <button class="zoom-btn" id="mobileZoomIn">+</button>
        <a href="<?php echo $subject ? htmlspecialchars($subject['pdf']) : '#'; ?>" download class="download-btn">⬇ PDF</a>
        <button class="nav-btn" id="mobileNextBtn">→</button>
    </div>
</div>

<?php if ($subject): ?>
<script>
(function() {
    if (typeof pdfjsLib === 'undefined') {
        document.getElementById('pdfContainer').innerHTML = '<div class="status error">PDF library failed to load. Please refresh the page.</div>';
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_PATH = <?php echo json_encode($subject['pdf']); ?>;
    let isMobile = window.innerWidth <= 768;
    const DPR = window.devicePixelRatio || 1;
    let pdfDoc = null;
    let currentPage = 1;
    let zoomLevel = 1;
    const pageCanvases = new Map();
    const loadingPages = new Set();
    const container = document.getElementById('pdfContainer');
    const statusEl = document.getElementById('status');

    /* ================= Fullscreen ================= */
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

    function updateFullscreenUI() {
        const active = isFullscreenActive();

        document.querySelectorAll('.fs-icon').forEach(function(el) {
            el.innerHTML = active ? ICON_COMPRESS : ICON_EXPAND;
        });

        const label = document.getElementById('fullscreenLabel');
        if (label) label.textContent = active ? 'Exit' : 'Fullscreen';

        document.querySelectorAll('.fs-btn').forEach(function(btn) {
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

    ['fullscreenchange', 'webkitfullscreenchange'].forEach(function(evt) {
        document.addEventListener(evt, function() {
            updateFullscreenUI();
            scheduleRelayout(100);
        });
    });

    updateFullscreenUI();

    /* ================= Theme sync =================
       Inline <head> script already applied the theme at paint time.
       This listener picks up toggles from index.php in another tab.
       The PDF "dark mode" is pure CSS (filter on canvas), so no
       re-render is needed — the filter just starts/stops applying. */
    window.addEventListener('storage', function (e) {
        if (e.key !== 'revspecs-theme') return;
        const dark = e.newValue === 'dark';
        if (dark) document.documentElement.setAttribute('data-theme', 'dark');
        else document.documentElement.removeAttribute('data-theme');
    });

    /* ================= Layout / rendering ================= */
    let resizeTimer = null;
    function scheduleRelayout(delay) {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (!pdfDoc) return;
            isMobile = window.innerWidth <= 768;
            pageCanvases.clear();
            loadingPages.clear();
            container.innerHTML = '';
            renderPage(currentPage).then(function() {
                scrollToPage(currentPage);
                renderPage(currentPage + 1);
            });
        }, delay || 300);
    }

    const loadTimeout = setTimeout(() => {
        if (!pdfDoc) {
            container.innerHTML = '<div class="status error">Loading timed out. The PDF may be unavailable or the path is incorrect.</div>';
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

        document.getElementById('jumpBtn')?.addEventListener('click', () => {
            const n = parseInt(document.getElementById('jumpInput').value, 10);
            if (n) navigateToPage(n);
        });
        document.getElementById('jumpInput')?.addEventListener('keypress', (e) => {
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
                    if (nextPageToLoad <= pdfDoc.numPages) {
                        renderPage(nextPageToLoad);
                    }
                }
                updateCurrentPageFromScroll();
                scrollTicking = false;
            });
        });

        document.addEventListener('keydown', (e) => {
            const tag = (e.target && e.target.tagName) || '';
            const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

            if (e.key === 'ArrowRight') navigateToPage(currentPage + 1);
            else if (e.key === 'ArrowLeft') navigateToPage(currentPage - 1);
            else if ((e.key === 'f' || e.key === 'F') && !typing) toggleFullscreen();
        });

        let touchStartX = 0, touchStartY = 0, isSwiping = false;
        container.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isSwiping = false;
            }
        }, { passive: true });
        container.addEventListener('touchmove', (e) => {
            if (e.touches.length === 1) {
                const dx = e.touches[0].clientX - touchStartX;
                const dy = e.touches[0].clientY - touchStartY;
                if (Math.abs(dx) > 10 && Math.abs(dx) > Math.abs(dy)) {
                    isSwiping = true;
                }
            }
        }, { passive: true });
        container.addEventListener('touchend', (e) => {
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
                if (container.querySelector('.status')) container.querySelector('.status').remove();
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
</script>
<?php endif; ?>
</body>
</html>
