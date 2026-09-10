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
            background: transparent;
            color: var(--text);
            overscroll-behavior: none;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* ============================================================
           PARALLAX BACKGROUND
           ============================================================ */
        .parallax-bg {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: -1;
            background: #000;
        }

        .parallax-stage {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 320px;
            height: 178px;
            transform-origin: center center;
            transform: translate(-50%, -50%) scale(var(--scale, 1));
        }

        .parallax-layer {
            position: absolute;
            left: 0;
            width: 100%;
            background-repeat: repeat-x;
            image-rendering: pixelated;
            pointer-events: none;
            will-change: background-position;
            backface-visibility: hidden;
        }

        .layer-sky {
            top: 0; height: 200px;
            background-image: url("Day/background_1_day.png");
            background-size: 100% 100%;
            background-repeat: no-repeat;
            z-index: 1;
        }
        .layer-clouds-back  { bottom: 0; height: 120px; background-image: url("Day/background_2_day.png"); z-index: 2; }
        .layer-clouds-front { bottom: 0; height: 115px; background-image: url("Day/background_3_day.png"); z-index: 3; }
        .layer-water        { bottom: 0; height: 70px;  background-image: url("Day/background_5_day.png"); z-index: 4; }
        .layer-terrain      { bottom: 0; height: 89px;  background-image: url("Day/background_4_day.png"); z-index: 5; }
        .layer-grass        { bottom: 0; height: 40px;  background-image: url("Day/background_6_day.png"); z-index: 6; }

        [data-theme="dark"] .layer-sky          { background-image: url("Night/background_1_night.png"); }
        [data-theme="dark"] .layer-clouds-back  { background-image: url("Night/background_2_night.png"); }
        [data-theme="dark"] .layer-clouds-front { background-image: url("Night/background_3_night.png"); }
        [data-theme="dark"] .layer-water        { background-image: url("Night/background_5_night.png"); }
        [data-theme="dark"] .layer-terrain      { background-image: url("Night/background_4_night.png"); }
        [data-theme="dark"] .layer-grass        { background-image: url("Night/background_6_night.png"); }

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

            [data-theme="dark"] .pdf-page canvas {
                filter: invert(1) hue-rotate(180deg);
            }
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
                font-size: 1.6rem;
                line-height: 1;
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
            .mobile-controls .nav-btn {
                max-width: 56px;
                padding: 10px 4px;
                font-size: 1.35rem;
            }
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

        .container:fullscreen .download-btn-desktop,
        .container:fullscreen .download-btn,
        body.pseudo-fullscreen .download-btn-desktop,
        body.pseudo-fullscreen .download-btn {
            display: none !important;
        }

        /* ---------- Fullscreen: hide/show controls ---------- */
        .fs-hide-toggle { display: none; }
        .fs-show-bar { display: none; }

        .container:fullscreen .fs-hide-toggle,
        body.pseudo-fullscreen .fs-hide-toggle {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .container:fullscreen.fs-idle .toolbar,
        body.pseudo-fullscreen.fs-idle .toolbar,
        .container:fullscreen.fs-idle .jump-row,
        body.pseudo-fullscreen.fs-idle .jump-row {
            display: none !important;
        }

        .container:fullscreen.fs-idle .fs-show-bar,
        body.pseudo-fullscreen.fs-idle .fs-show-bar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 16px;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 999px;
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            z-index: 100001;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
            animation: fsShowBarIn 0.2s ease-out;
        }
        .container:fullscreen.fs-idle .fs-show-bar:hover,
        body.pseudo-fullscreen.fs-idle .fs-show-bar:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        @keyframes fsShowBarIn {
            from { opacity: 0; transform: translate(-50%, -8px); }
            to   { opacity: 1; transform: translate(-50%, 0); }
        }

        @media (max-width: 768px) {
            .fs-hide-toggle,
            .fs-show-bar { display: none !important; }
        }

        /* ============================================================
           FAB + About modal
           ============================================================ */
        .about-fab{
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: none;
            padding: 0;
            overflow: hidden;
            cursor: pointer;
            background: var(--download);
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            z-index: 99999;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.2s ease;
        }
        .about-fab:hover{ transform: scale(1.06); box-shadow: 0 6px 14px rgba(0,0,0,0.3); }
        .about-fab:focus-visible{ outline: 3px solid var(--text); outline-offset: 3px; }

        .about-fab-img{
            width: 100%;
            height: 100%;
            display:flex;
            align-items:center;
            justify-content:center;
            object-fit: contain;
        }

        .about-overlay{
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 24px;
            z-index: 100000;
        }
        .about-overlay[hidden]{ display:none; }

        .about-modal{
            background: var(--panel);
            border-radius: 3px;
            border-top: 6px solid var(--download);
            padding: 28px 30px;
            max-width: 480px;
            width: 100%;
            position: relative;
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
            transition: background-color 0.2s ease;
        }

        .about-modal-close{
            position: absolute;
            top: 14px;
            right: 14px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: var(--border);
            color: var(--text);
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
        }
        .about-modal-close:hover{ background: var(--accent); color: #fff; }

        .about-modal h2{
            font-family:'Roboto Condensed', sans-serif;
            font-size: 1.2rem;
            margin: 0 0 10px;
            color: var(--download);
            padding-right: 24px;
        }

        .about-modal p{
            margin: 0 0 18px;
            font-size: 0.92rem;
            color: var(--text-soft);
        }

        .facebook-placeholder{
            display:inline-flex;
            align-items:center;
            gap: 10px;
            text-decoration:none;
            color: var(--text);
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 3px;
            transition: border-color 0.15s ease, color 0.15s ease;
        }
        .facebook-placeholder:hover{ border-color: var(--accent); color: var(--accent); }
        .facebook-placeholder .placeholder-thumb{
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--border);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size: 0.55rem;
            font-weight: 700;
            color: var(--text);
            flex-shrink:0;
        }

        .theme-toggle-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
            margin: 0 0 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .theme-toggle-row + .theme-toggle-row{ margin-top: -18px; }

        .theme-toggle-label{
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text);
        }

        .theme-toggle{
            position: relative;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            border: 2px solid var(--border);
            background: var(--bg);
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .theme-toggle-thumb{
            position: absolute;
            top: 1px;
            left: 1px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--text-soft);
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .theme-toggle[aria-checked="true"]{
            background: var(--download);
            border-color: var(--download);
        }
        .theme-toggle[aria-checked="true"] .theme-toggle-thumb{
            transform: translateX(20px);
            background: #fff;
        }
        .theme-toggle:focus-visible{
            outline: 2px solid var(--text);
            outline-offset: 2px;
        }

        @media (max-width: 768px){
            .about-fab{
                width: 52px;
                height: 52px;
                right: 12px;
                bottom: calc(var(--controls-height) + env(safe-area-inset-bottom, 0px) + 12px);
            }
        }

        /* "Click me →" hint */
        .fab-hint {
            position: fixed;
            bottom: calc(24px + 32px);
            right:  calc(24px + 64px + 14px);
            transform: translateY(50%);
            white-space: nowrap;
            padding: 8px 14px;
            border-radius: 999px;
            font: 600 13px/1 'Roboto', sans-serif;
            color: #fff;
            background: #1f2430;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .25);
            z-index: 99998;
            pointer-events: none;
            opacity: 0;
            animation: fabHintIn .45s ease-out .6s forwards,
                       fabHintBob 1.8s ease-in-out 1.2s infinite;
            transition: opacity .3s ease, transform .3s ease;
        }
        .fab-hint::after {
            content: "";
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-left-color: #1f2430;
        }
        [data-theme="dark"] .fab-hint        { background: #f1f3f7; color: #12151c; }
        [data-theme="dark"] .fab-hint::after { border-left-color: #f1f3f7; }

        @keyframes fabHintIn {
            from { opacity: 0; transform: translateY(50%) translateX(-8px); }
            to   { opacity: 1; transform: translateY(50%) translateX(0);    }
        }
        @keyframes fabHintBob {
            0%, 100% { transform: translateY(50%) translateX(0); }
            50%      { transform: translateY(50%) translateX(6px); }
        }
        .fab-hint.is-hidden {
            animation: none;
            opacity: 0;
            transform: translateY(50%) translateX(-8px);
        }
        @media (max-width: 768px) {
            .fab-hint {
                bottom: calc(var(--controls-height) + env(safe-area-inset-bottom, 0px) + 12px + 26px);
                right:  calc(12px + 52px + 12px);
                font-size: 12px;
                padding: 7px 12px;
            }
        }

        .credit-marquee{
            margin-top: 18px;
            padding: 10px 0;
            border-top: 1px solid var(--border);
            overflow: hidden;
            position: relative;
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
            mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
        }
        .credit-marquee-track{
            display: inline-flex;
            gap: 60px;
            white-space: nowrap;
            will-change: transform;
            animation: creditScroll 18s linear infinite;
        }
        .credit-marquee-item{
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-soft);
            opacity: 0.85;
        }
        .credit-marquee:hover .credit-marquee-track{ animation-play-state: paused; }
        @keyframes creditScroll{
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }
        @media (prefers-reduced-motion: reduce){
            .credit-marquee-track{ animation: none; overflow-x: auto; }
        }

        body.modal-open{ overflow: hidden; }

        @media (prefers-reduced-motion: reduce){
            *{ transition: none !important; animation: none !important; }
        }
    </style>
</head>
<body>

<!-- Parallax background -->
<div class="parallax-bg">
    <div class="parallax-stage" id="parallaxStage">
        <div class="parallax-layer layer-sky"          data-speed="0" data-tile="320"></div>
        <div class="parallax-layer layer-clouds-back"  data-speed="2" data-tile="160"></div>
        <div class="parallax-layer layer-clouds-front" data-speed="3" data-tile="160"></div>
        <div class="parallax-layer layer-water"        data-speed="4" data-tile="172"></div>
        <div class="parallax-layer layer-terrain"      data-speed="4" data-tile="172"></div>
        <div class="parallax-layer layer-grass"        data-speed="6" data-tile="151"></div>
    </div>
</div>

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
    <a class="back-link" href=".\">&larr; Back to RevSpecs</a>
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
                    <button class="btn fs-hide-toggle" id="hideToolbarBtn" type="button" title="Hide controls">
                        <span aria-hidden="true">▲</span> Hide
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

    <!-- "Show controls" pill (visible only in fullscreen when controls are hidden) -->
    <button class="fs-show-bar" id="showToolbarBtn" type="button" aria-label="Show controls">
        <span aria-hidden="true">▼</span> Show controls
    </button>
</div>

<!-- Floating About button -->
<button class="about-fab" id="aboutBtn" aria-haspopup="dialog" aria-expanded="false" aria-controls="aboutOverlay">
  <img src="specsLogo.png" class="about-fab-img" alt="SPECS logo">
</button>

<!-- "Click me →" hint -->
<span class="fab-hint" id="fabHint" aria-hidden="true">Click me&nbsp;→</span>

<div class="about-overlay" id="aboutOverlay" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" hidden>
  <div class="about-modal">
    <button class="about-modal-close" id="aboutClose" aria-label="Close">&times;</button>
    <h2 id="aboutTitle">Gordon College &amp; SPECS</h2>
    <p>
      The Society of Programming Enthusiasts in Computer Science (SPECS) is an organization under the GCCCS
    </p>

    <div class="theme-toggle-row">
      <span class="theme-toggle-label" id="themeToggleLabel">Dark mode</span>
      <button class="theme-toggle" id="themeToggle" role="switch" aria-checked="false" aria-labelledby="themeToggleLabel">
        <span class="theme-toggle-thumb"></span>
      </button>
    </div>

    <div class="theme-toggle-row">
      <span class="theme-toggle-label" id="musicToggleLabel">Music</span>
      <button class="theme-toggle" id="musicToggle" role="switch" aria-checked="false" aria-labelledby="musicToggleLabel">
        <span class="theme-toggle-thumb"></span>
      </button>
    </div>

    <a class="facebook-placeholder" href="https://www.facebook.com/gcccsSPECS" target="_blank" rel="noopener">
      <span class="placeholder-thumb">FB</span>
      SPECS' Official Facebook Page
    </a>

    <div class="credit-marquee" aria-label="Music credit">
      <div class="credit-marquee-track">
        <span class="credit-marquee-item">♪ Clair de Lune — Claude Debussy</span>
        <span class="credit-marquee-item" aria-hidden="true">♪ Clair de Lune — Claude Debussy</span>
      </div>
    </div>
  </div>
</div>

<audio id="bgm" loop preload="auto">
  <source src="Music/Clair.mp3" type="audio/mpeg">
</audio>

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

    function setControlsHidden(hidden) {
        fullscreenTarget.classList.toggle('fs-idle', !!hidden);
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

    ['fullscreenchange', 'webkitfullscreenchange'].forEach(function(evt) {
        document.addEventListener(evt, function() {
            updateFullscreenUI();
            if (!isFullscreenActive()) setControlsHidden(false);
            scheduleRelayout(100);
        });
    });

    updateFullscreenUI();

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

        document.getElementById('hideToolbarBtn')?.addEventListener('click', function() {
            if (isFullscreenActive()) setControlsHidden(true);
        });
        document.getElementById('showToolbarBtn')?.addEventListener('click', function() {
            setControlsHidden(false);
        });

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

<script>
/* ============================================================
   FAB + modal + theme + music + parallax
   (runs independently of the PDF viewer)
   ============================================================ */

/* ---------- Modal helper ---------- */
const openOverlays = [];

function setupModal(overlay, { onOpen, onClose } = {}) {
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
    if (onOpen) onOpen();
    const focusable = getFocusable();
    (focusable[0] || overlay).focus();
  }

  function close() {
    if (overlay.hidden) return;
    overlay.hidden = true;
    document.body.classList.remove('modal-open');
    if (onClose) onClose();
    if (lastFocused) lastFocused.focus();
  }

  overlay.addEventListener('keydown', (e) => {
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

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) close();
  });

  const controller = { overlay, open, close };
  openOverlays.push(controller);
  return controller;
}

document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  openOverlays.forEach(({ overlay, close }) => { if (!overlay.hidden) close(); });
});

/* About modal */
const aboutBtn = document.getElementById('aboutBtn');
const aboutOverlay = document.getElementById('aboutOverlay');
const aboutClose = document.getElementById('aboutClose');

const aboutModal = setupModal(aboutOverlay, {
  onOpen: () => aboutBtn.setAttribute('aria-expanded', 'true'),
  onClose: () => aboutBtn.setAttribute('aria-expanded', 'false'),
});

aboutBtn.addEventListener('click', () => aboutModal.open(aboutBtn));
aboutClose.addEventListener('click', () => aboutModal.close());

/* "Click me →" hint */
(function () {
  var HINT_KEY     = 'revspecs-fab-hint-seen';
  var AUTO_HIDE_MS = 9000;

  var hint = document.getElementById('fabHint');
  if (!hint) return;

  try {
    if (localStorage.getItem(HINT_KEY) === '1') { hint.remove(); return; }
  } catch (e) {}

  var dismissed = false;

  function dismiss() {
    if (dismissed) return;
    dismissed = true;
    hint.classList.add('is-hidden');
    setTimeout(function () { hint.remove(); }, 400);
    try { localStorage.setItem(HINT_KEY, '1'); } catch (e) {}
  }

  var timer = setTimeout(dismiss, AUTO_HIDE_MS);

  aboutBtn.addEventListener('click', function () {
    clearTimeout(timer);
    dismiss();
  }, { once: true });
})();

/* Dark mode toggle */
const THEME_KEY = 'revspecs-theme';
const themeToggle = document.getElementById('themeToggle');

function applyTheme(theme){
  if (theme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    themeToggle.setAttribute('aria-checked', 'true');
  } else {
    document.documentElement.removeAttribute('data-theme');
    themeToggle.setAttribute('aria-checked', 'false');
  }
}

applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');

themeToggle.addEventListener('click', () => {
  const next = themeToggle.getAttribute('aria-checked') === 'true' ? 'light' : 'dark';
  applyTheme(next);
  try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
});

/* Cross-tab theme sync */
window.addEventListener('storage', function (e) {
  if (e.key !== 'revspecs-theme') return;
  applyTheme(e.newValue === 'dark' ? 'dark' : 'light');
});

/* Background music */
(function () {
  var MUSIC_KEY = 'revspecs-music-on';
  var VOLUME    = 0.35;

  var btn = document.getElementById('musicToggle');
  var bgm = document.getElementById('bgm');
  if (!btn || !bgm) return;

  bgm.volume = VOLUME;

  function syncUI() {
    var playing = !bgm.paused && !bgm.ended;
    btn.setAttribute('aria-checked', playing ? 'true' : 'false');
  }

  bgm.addEventListener('play',  syncUI);
  bgm.addEventListener('pause', syncUI);
  bgm.addEventListener('ended', syncUI);

  function play() {
    var p = bgm.play();
    if (p && p.catch) p.catch(function () {});
  }
  function pause() { bgm.pause(); }

  syncUI();

  try {
    if (localStorage.getItem(MUSIC_KEY) === '1') play();
  } catch (e) {}

  btn.addEventListener('click', function () {
    if (bgm.paused) play(); else pause();
    try {
      localStorage.setItem(MUSIC_KEY, bgm.paused ? '0' : '1');
    } catch (e) {}
  });
})();

/* Parallax background animation */
(function () {
  const stage = document.getElementById('parallaxStage');
  if (!stage) return;

  const STAGE_W = 320;
  const STAGE_H = 178;

  function fitStage() {
    const scale = Math.max(window.innerWidth / STAGE_W, window.innerHeight / STAGE_H);
    stage.style.setProperty('--scale', scale);
  }

  window.addEventListener('resize', fitStage);
  fitStage();

  const layers = Array.from(document.querySelectorAll('.parallax-layer')).map(el => ({
    el,
    speed: parseFloat(el.dataset.speed) || 0,
    tile: parseFloat(el.dataset.tile) || 0,
    offset: 0
  }));

  let last = performance.now();

  function tick(now) {
    const dt = (now - last) / 1000;
    last = now;

    for (const layer of layers) {
      if (layer.tile > 0) {
        layer.offset = (layer.offset + layer.speed * dt) % layer.tile;
      }
      layer.el.style.backgroundPositionX = (-layer.offset).toFixed(2) + 'px';
    }

    requestAnimationFrame(tick);
  }

  requestAnimationFrame(tick);
})();
</script>
</body>
</html>
