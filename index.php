<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RevSpecs — SPECS Reviewer Library</title>
<script>
  (function () {
    try {
      var stored = localStorage.getItem('revspecs-theme');
      var wantsDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (wantsDark) document.documentElement.setAttribute('data-theme', 'dark');
    } catch (e) {}
  })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style"
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Condensed:wght@400;700&display=swap"
      onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Condensed:wght@400;700&display=swap"></noscript>
<meta name="theme-color" id="themeColorMeta" content="#FFFFFF">
<style>
  /* ============================================================
     CUSTOM PIXEL FONT (header only)
     ============================================================ */
  @font-face {
    font-family: 'OutlinePixel7';
    src: url('Fonts/outline_pixel-7.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
  }

  :root{
    --paper: #517a51;
    --panel: #ccf8d3;
    --ink: #c4ffe6;
    --ink-soft: #28302c;
    --line: #CDE8D5;
    --y1: #E67E22;
    --y1-dark: #CF6C1B;
    --y2: #27AE60;
    --y2-dark: #219150;
    --y3: #D35400;
    --radius: 3px;
    --overlay-tint: 27, 67, 50;
    --shadow-tint: 0, 0, 0;
    --about-panel: #c0ffca;
    --about-text: #245c33;
    --toggle-text: #517a51;
    --panel-heading: #10291b;
    color-scheme: light;
  }

  html, body { min-height: 100%; }

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

  [data-theme="dark"]{
    --paper: #0F1E18;
    --panel: #16281F;
    --ink: #E9F5EE;
    --ink-soft: #93BCA9;
    --line: #2A4638;
    --y1: #F0964C;
    --y1-dark: #E67E22;
    --y2: #35C97A;
    --y2-dark: #27AE60;
    --y3: #EC711C;
    --overlay-tint: 0, 0, 0;
    --shadow-tint: 0, 0, 0;
    --about-panel: #16281F;
    --about-text: #E9F5EE;
    --toggle-text: #517a51;
    --panel-heading: #E9F5EE;
    color-scheme: dark;
  }

  * { box-sizing: border-box; }

  body{
    margin:0;
    background:transparent;
    color:var(--ink);
    font-family:'Roboto', sans-serif;
    font-weight:400;
    line-height:1.5;
    transition: background-color 0.2s ease, color 0.2s ease;
  }

  .wrap{
    max-width: 900px;
    margin: 0 auto;
    padding: 48px 24px 80px;
  }

  header{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    border-bottom: 2px solid var(--ink);
    padding-bottom: 18px;
    margin-bottom: 20px;
    gap: 16px;
    flex-wrap: wrap;
  }

  /* ---------- HEADER: Outline Pixel-7 ---------- */
  h1{
    font-family: 'OutlinePixel7', 'Roboto Condensed', sans-serif;
    font-weight:700;
    font-size: 2.9rem;
    margin:0;
    letter-spacing: -0.01em;
    image-rendering: pixelated;
  }

  /* ---------- TAGLINE: back to Roboto ---------- */
  .tagline{
    font-size: 0.92rem;
    line-height: 1.45;
    color: var(--ink-soft);
    font-weight: 500;
    max-width: 34ch;
    text-align:right;
    margin:0;
    text-shadow:
      0 0 6px rgba(255,255,255,0.80),
      0 1px 2px rgba(0,0,0,0.35);
  }

  [data-theme="dark"] .tagline{
    color: var(--ink);
    text-shadow:
      0 0 6px rgba(0,0,0,0.85),
      0 1px 2px rgba(0,0,0,0.60);
  }

  /* +25% on tablet / laptop / desktop only (mobile stays as-is) */
  @media (min-width: 521px){
    .tagline{
      font-size: 1rem;
      max-width: 36ch;
    }
  }

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
    background: var(--y2);
    box-shadow: 0 4px 10px rgba(var(--shadow-tint), 0.25);
    z-index: 100;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.2s ease;
  }

  .about-fab:hover{ transform: scale(1.06); box-shadow: 0 6px 14px rgba(var(--shadow-tint), 0.3); }
  .about-fab:focus-visible{ outline: 3px solid var(--ink); outline-offset: 3px; }

  .about-fab-img{
    width: 100%;
    height: 100%;
    display:flex;
    align-items:center;
    justify-content:center;
    color: #fff;
    font-family:'Roboto Condensed', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-align:center;
  }

  .about-overlay{
    position: fixed;
    inset: 0;
    background: rgba(var(--overlay-tint), 0.55);
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 24px;
    z-index: 200;
  }

  .about-overlay[hidden]{ display:none; }

  .about-modal{
    background: var(--about-panel);
    border-radius: var(--radius);
    border-top: 6px solid var(--y2);
    padding: 28px 30px;
    max-width: 480px;
    width: 100%;
    position: relative;
    box-shadow: 0 12px 30px rgba(var(--shadow-tint), 0.3);
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
    background: var(--line);
    color: var(--ink);
    font-size: 1rem;
    line-height: 1;
    cursor: pointer;
  }

  .about-modal-close:hover{ background: var(--y3); color: #fff; }

  .about-modal h2{
    font-family:'Roboto Condensed', sans-serif;
    font-size: 1.2rem;
    margin: 0 0 10px;
    color: var(--y2);
    padding-right: 24px;
  }

  .about-modal p{
    margin: 0 0 18px;
    font-size: 0.92rem;
    color: var(--about-text);
  }

  .facebook-placeholder{
    display:inline-flex;
    align-items:center;
    gap: 10px;
    text-decoration:none;
    color: var(--about-text);
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid var(--line);
    padding: 8px 14px;
    border-radius: var(--radius);
    transition: border-color 0.15s ease, color 0.15s ease;
  }

  .facebook-placeholder:hover{ border-color: var(--y1); color: var(--y1); }

  .facebook-placeholder .placeholder-thumb{
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--line);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 0.55rem;
    font-weight: 700;
    color: var(--about-text);
    flex-shrink:0;
  }

  /* Toggle rows (dark mode + music) */
  .theme-toggle-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 12px;
    margin: 0 0 18px;
    padding-top: 16px;
    border-top: 1px solid var(--line);
  }

  .theme-toggle-row + .theme-toggle-row{
    margin-top: -18px;
  }

  .theme-toggle-label{
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--toggle-text);
  }

  .theme-toggle{
    position: relative;
    width: 44px;
    height: 24px;
    border-radius: 999px;
    border: 2px solid var(--line);
    background: var(--paper);
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
    background: var(--ink-soft);
    transition: transform 0.2s ease, background-color 0.2s ease;
  }

  .theme-toggle[aria-checked="true"]{
    background: var(--y2);
    border-color: var(--y2);
  }

  .theme-toggle[aria-checked="true"] .theme-toggle-thumb{
    transform: translateX(20px);
    background: #fff;
  }

  .theme-toggle:focus-visible{
    outline: 2px solid var(--ink);
    outline-offset: 2px;
  }

  @media (max-width: 520px){
    .about-fab{ width: 56px; height: 56px; bottom: 16px; right: 16px; }
  }

  /* ---------- Choice modal ---------- */
  .choice-modal h2{
    font-family:'Roboto Condensed', sans-serif;
    font-size: 1.15rem;
    margin: 0 0 4px;
    color: var(--panel-heading);
    padding-right: 24px;
  }

  .choice-modal .choice-code{
    display:block;
    font-family:'Roboto Condensed', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--ink-soft);
    margin-bottom: 18px;
  }

  .choice-row{
    display:flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .choice-btn{
    flex: 1 1 160px;
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    gap: 4px;
    border: none;
    border-radius: var(--radius);
    padding: 16px 18px;
    cursor:pointer;
    color: #fff;
    font-family:'Roboto', sans-serif;
    text-align:left;
    transition: background-color 0.15s ease;
  }

  .choice-btn .choice-label{
    font-family:'Roboto Condensed', sans-serif;
    font-weight:700;
    font-size: 0.95rem;
  }

  .choice-btn .choice-sub{
    font-size: 0.78rem;
    opacity: 0.9;
  }

  .choice-btn.reviewer{ background: var(--y1); }
  .choice-btn.reviewer:hover{ background: var(--y1-dark); }
  .choice-btn.quiz{ background: var(--y2); }
  .choice-btn.quiz:hover{ background: var(--y2-dark); }

  .tabs{
    display:flex;
    gap: 6px;
    margin-bottom: 0;
  }

  .tab{
    font-family:'Roboto Condensed', sans-serif;
    font-size: 0.85rem;
    font-weight:700;
    background: var(--panel);
    border: 2px solid var(--line);
    border-bottom: none;
    color: var(--ink-soft);
    padding: 10px 18px 12px;
    border-radius: var(--radius) var(--radius) 0 0;
    cursor: pointer;
    position: relative;
    top: 2px;
    transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
  }

  .tab:hover{ color: var(--ink); }
  .tab:focus-visible{ outline: 2px solid var(--ink); outline-offset: 2px; }

  .tab[aria-selected="true"][data-year="1"]{ color:#fff; background: var(--y1); border-color: var(--y1); }
  .tab[aria-selected="true"][data-year="2"]{ color:#fff; background: var(--y2); border-color: var(--y2); }
  .tab[aria-selected="true"][data-year="3"]{ color:#fff; background: var(--y3); border-color: var(--y3); }

  .panel{
    background: var(--panel);
    border: 2px solid var(--line);
    border-radius: 0 var(--radius) var(--radius) var(--radius);
    padding: 28px;
    transition: background-color 0.2s ease, border-color 0.2s ease;
  }

  .panel-year-bar{
    height: 5px;
    margin: -28px -28px 24px;
    border-radius: 0;
  }
  .panel-year-bar[data-year="1"]{ background: var(--y1); }
  .panel-year-bar[data-year="2"]{ background: var(--y2); }
  .panel-year-bar[data-year="3"]{ background: var(--y3); }

  .panel-instruction{
    margin: 0 0 20px;
    font-size: 0.95rem;
    color: var(--ink-soft);
  }

  .subject-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
  }

  .subject-card{
    display:block;
    text-align:left;
    background: var(--paper);
    border: 1px solid var(--line);
    border-left: 4px solid var(--ink-soft);
    padding: 14px 16px;
    cursor:pointer;
    font-family:'Roboto', sans-serif;
    color: var(--ink);
    transition: transform 0.12s ease, border-color 0.12s ease, background-color 0.2s ease, color 0.2s ease;
  }

  .subject-card:hover{ transform: translateX(2px); }
  .subject-card:focus-visible{ outline: 2px solid var(--ink); outline-offset: 2px; }

  .subject-code{
    display:block;
    font-family:'Roboto Condensed', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: var(--ink-soft);
    margin-bottom: 4px;
  }

  .subject-name{
    display:block;
    font-size: 0.98rem;
    font-weight: 500;
  }

  [data-year="1"] .subject-card{ border-left-color: var(--y1); }
  [data-year="2"] .subject-card{ border-left-color: var(--y2); }
  [data-year="3"] .subject-card{ border-left-color: var(--y3); }

  /* ---------- FOOTER: back to Roboto ---------- */
  footer{
    margin-top: 36px;
    font-size: 0.8rem;
    line-height: 1.55;
    color: var(--ink-soft);
    font-weight: 500;
    border-top: 1px solid var(--line);
    padding-top: 14px;
    text-shadow:
      0 0 6px rgba(255,255,255,0.80),
      0 1px 2px rgba(0,0,0,0.35);
  }

  [data-theme="dark"] footer{
    color: var(--ink);
    text-shadow:
      0 0 6px rgba(0,0,0,0.85),
      0 1px 2px rgba(0,0,0,0.60);
  }

  footer .credit{
    margin: 6px 0 0;
    font-size: 0.76rem;
    color: inherit;
    opacity: 0.92;
  }

  /* +25% on tablet / laptop / desktop only (mobile stays as-is) */
  @media (min-width: 521px){
    footer{ font-size: 1rem; }
    footer .credit{ font-size: 0.95rem; }
  }

  body.modal-open{ overflow: hidden; }

  @media (prefers-reduced-motion: reduce){
    *{ transition: none !important; }
  }

  @media (max-width: 520px){
    header{ flex-direction: column; align-items: flex-start; }
    .tagline{ text-align:left; }
    .tabs{ width:100%; }
    .tab{ flex:1; text-align:center; padding: 10px 8px 12px; }
  }

  /* "Click me →" hint for the About FAB */
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
    z-index: 99;
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

  @media (max-width: 520px) {
    .fab-hint {
      bottom: calc(16px + 28px);
      right:  calc(16px + 56px + 14px);
      font-size: 12px;
      padding: 7px 12px;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .fab-hint { animation: fabHintIn .01s forwards; }
  }

  /* ============================================================
   Scrolling music credit inside the About modal
   ============================================================ */
.credit-marquee{
  margin-top: 18px;
  padding: 10px 0;
  border-top: 1px solid var(--line);
  overflow: hidden;
  position: relative;

  -webkit-mask-image: linear-gradient(
    to right,
    transparent 0,
    #000 20px,
    #000 calc(100% - 20px),
    transparent 100%
  );
  mask-image: linear-gradient(
    to right,
    transparent 0,
    #000 20px,
    #000 calc(100% - 20px),
    transparent 100%
  );
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
  color: var(--about-text);
  opacity: 0.85;
}

.credit-marquee:hover .credit-marquee-track{
  animation-play-state: paused;
}

@keyframes creditScroll{
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}

@media (prefers-reduced-motion: reduce){
  .credit-marquee-track{
    animation: none;
    overflow-x: auto;
  }
}
</style>
</head>
<body>

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

<div class="wrap">
  <header>
    <h1>RevSpecs</h1>
    <p class="tagline">SPECS reviewer library, sorted by year. Pick a subject to open its set.</p>
  </header>

  <div class="tabs" role="tablist" aria-label="Select year level">
    <button class="tab" role="tab" data-year="1" aria-selected="true">1st Year</button>
    <button class="tab" role="tab" data-year="2" aria-selected="false">2nd Year</button>
    <button class="tab" role="tab" data-year="3" aria-selected="false">3rd Year</button>
  </div>

  <div class="panel" data-year="1" id="panel">
    <div class="panel-year-bar" data-year="1"></div>
    <p class="panel-instruction">Click a subject and choose between a reviewer or a quiz.</p>
    <div class="subject-grid" id="subjectGrid"></div>
  </div>

  <footer>
    RevSpecs A.Y. 2026 - 2027 — built for Gordon College BSCS Students.
    <p class="credit">Developed and Proposed by Perez, Emilio James — 2nd Year Representative.</p>
  </footer>
</div>

<!-- Floating About button -->
<button class="about-fab" id="aboutBtn" aria-haspopup="dialog" aria-expanded="false" aria-controls="aboutOverlay">
  <img src="specsLogo.png" class="about-fab-img" alt="SPECS logo">
</button>

<!-- "Click me →" hint that points at the FAB above -->
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

     <!-- Scrolling music credit -->
    <div class="credit-marquee" aria-label="Music credit">
      <div class="credit-marquee-track">
        <span class="credit-marquee-item">♪ Clair de Lune — Claude Debussy</span>
        <span class="credit-marquee-item" aria-hidden="true">♪ Clair de Lune — Claude Debussy</span>
      </div>

    </div>
  </div>
</div>

<div class="about-overlay" id="choiceOverlay" role="dialog" aria-modal="true" aria-labelledby="choiceTitle" hidden>
  <div class="about-modal choice-modal">
    <button class="about-modal-close" id="choiceClose" aria-label="Close">&times;</button>
    <span class="choice-code" id="choiceCode"></span>
    <h2 id="choiceTitle"></h2>
    <div class="choice-row">
      <button class="choice-btn reviewer" id="choiceReviewer" type="button">
        <span class="choice-label"><span aria-hidden="true"></span>Reviewer</span>
        <span class="choice-sub">Open the written reviewer</span>
      </button>
      <button class="choice-btn quiz" id="choiceQuiz" type="button">
        <span class="choice-label"><span aria-hidden="true"></span>Quiz</span>
        <span class="choice-sub">Take a quiz!</span>
      </button>
    </div>
  </div>
</div>

<audio id="bgm" loop preload="auto">
  <source src="Music/Clair.mp3" type="audio/mpeg">
</audio>

<script>
  const QUIZ_PAGE = 'quiz.html';
  const REVIEWER_PAGE = 'reviewer.php';

  const YEARS = <?php
    $subjects = include 'subjects-config.php';
    $years = [1 => ['subjects' => []], 2 => ['subjects' => []], 3 => ['subjects' => []]];
    foreach ($subjects as $code => $info) {
        $years[$info['year']]['subjects'][] = ['code' => $code, 'name' => $info['name']];
    }
    echo json_encode($years, JSON_UNESCAPED_SLASHES);
  ?>;

  const tabs = document.querySelectorAll('.tab');
  const panel = document.getElementById('panel');
  const grid = document.getElementById('subjectGrid');

  function renderYear(year){
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
      card.addEventListener('click', () => { openChoice(subject, card); });
      grid.appendChild(card);
    });
  }

  function selectTab(tab){
    tabs.forEach(t => t.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    renderYear(tab.dataset.year);
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => selectTab(tab));
  });

  document.querySelector('.tabs').addEventListener('keydown', (e) => {
    const tabArr = Array.from(tabs);
    const currentIndex = tabArr.indexOf(document.activeElement);
    if (currentIndex === -1) return;

    let newIndex = null;
    if (e.key === 'ArrowRight') newIndex = (currentIndex + 1) % tabArr.length;
    else if (e.key === 'ArrowLeft') newIndex = (currentIndex - 1 + tabArr.length) % tabArr.length;
    else if (e.key === 'Home') newIndex = 0;
    else if (e.key === 'End') newIndex = tabArr.length - 1;

    if (newIndex !== null) {
      e.preventDefault();
      tabArr[newIndex].focus();
      selectTab(tabArr[newIndex]);
    }
  });

  renderYear(1);

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

  const aboutBtn = document.getElementById('aboutBtn');
  const aboutOverlay = document.getElementById('aboutOverlay');
  const aboutClose = document.getElementById('aboutClose');

  const aboutModal = setupModal(aboutOverlay, {
    onOpen: () => aboutBtn.setAttribute('aria-expanded', 'true'),
    onClose: () => aboutBtn.setAttribute('aria-expanded', 'false'),
  });

  aboutBtn.addEventListener('click', () => aboutModal.open(aboutBtn));
  aboutClose.addEventListener('click', () => aboutModal.close());

  /* "Click me →" hint for the FAB. */
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

  const choiceOverlay = document.getElementById('choiceOverlay');
  const choiceClose = document.getElementById('choiceClose');
  const choiceCode = document.getElementById('choiceCode');
  const choiceTitle = document.getElementById('choiceTitle');
  const choiceReviewerBtn = document.getElementById('choiceReviewer');
  const choiceQuizBtn = document.getElementById('choiceQuiz');

  const choiceModal = setupModal(choiceOverlay);

  function openChoice(subject, trigger){
    choiceCode.textContent = subject.code;
    choiceTitle.textContent = subject.name;

    choiceReviewerBtn.onclick = () => {
      window.open(`${REVIEWER_PAGE}?subject=${encodeURIComponent(subject.code)}`, '_self');
      choiceModal.close();
    };
    choiceQuizBtn.onclick = () => {
      const quizUrl = `${QUIZ_PAGE}?subject=${encodeURIComponent(subject.code)}&name=${encodeURIComponent(subject.name)}`;
      window.open(quizUrl, '_self');
      choiceModal.close();
    };

    choiceModal.open(trigger);
  }

  choiceClose.addEventListener('click', () => choiceModal.close());

  /* Dark mode toggle */
  const THEME_KEY = 'revspecs-theme';
  const themeToggle = document.getElementById('themeToggle');
  const themeColorMeta = document.getElementById('themeColorMeta');

  function applyTheme(theme){
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      themeToggle.setAttribute('aria-checked', 'true');
      themeColorMeta.setAttribute('content', '#0F1E18');
    } else {
      document.documentElement.removeAttribute('data-theme');
      themeToggle.setAttribute('aria-checked', 'false');
      themeColorMeta.setAttribute('content', '#FFFFFF');
    }
  }

  applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');

  themeToggle.addEventListener('click', () => {
    const next = themeToggle.getAttribute('aria-checked') === 'true' ? 'light' : 'dark';
    applyTheme(next);
    try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
  });

  /* Parallax */
  const parallaxStage = document.getElementById('parallaxStage');
  const STAGE_W = 320;
  const STAGE_H = 178;

  function fitParallaxStage() {
      const scale = Math.max(innerWidth / STAGE_W, innerHeight / STAGE_H);
      parallaxStage.style.setProperty('--scale', scale);
  }

  addEventListener('resize', fitParallaxStage);
  fitParallaxStage();

  const parallaxLayers = [
      ...document.querySelectorAll('.parallax-layer')
  ].map(el => ({
      el,
      speed: parseFloat(el.dataset.speed) || 0,
      tile: parseFloat(el.dataset.tile) || 0,
      offset: 0
  }));

  let parallaxLast = performance.now();

  function tickParallax(now) {
      const dt = (now - parallaxLast) / 1000;
      parallaxLast = now;

      for (const layer of parallaxLayers) {
          if (layer.tile > 0) {
              layer.offset = (layer.offset + layer.speed * dt) % layer.tile;
          }
          layer.el.style.backgroundPositionX = (-layer.offset).toFixed(2) + 'px';
      }

      requestAnimationFrame(tickParallax);
  }

  requestAnimationFrame(tickParallax);

  /* ============================================================
     Background music (MP3) — toggle lives inside the About modal,
     below the dark mode toggle. Icon (aria-checked) reflects REAL
     playback state: on/green when audio is playing, off/red when
     silent. No hidden first-gesture listeners.
     ============================================================ */
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
      if (p && p.catch) p.catch(function () { /* blocked — stays red */ });
    }
    function pause() { bgm.pause(); }

    syncUI();

    /* Attempt autoplay on load for returning users. Browser will
       usually block it unless the user has engaged with the site. */
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
</script>

</body>
</html>
