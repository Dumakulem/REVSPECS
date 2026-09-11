<?php define('ASSET_VER', '1.0.0'); ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RevSpecs — AI Quiz</title>
<script>
(function () {
  try {
    var stored = localStorage.getItem('revspecs-theme');
    var wantsDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (wantsDark) document.documentElement.setAttribute('data-theme', 'dark');
    var hour = new Date().getHours();
    document.documentElement.setAttribute('data-time-period',
      hour >= 6 && hour < 15 ? 'day' : hour >= 15 && hour < 18 ? 'dusk' : 'night');
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
<link rel="stylesheet" href="assets/css/base.css?v=<?= ASSET_VER ?>">
<link rel="stylesheet" href="assets/css/parallax.css?v=<?= ASSET_VER ?>">
<link rel="stylesheet" href="assets/css/fab.css?v=<?= ASSET_VER ?>">
<link rel="stylesheet" href="assets/css/quiz.css?v=<?= ASSET_VER ?>">
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
  <a class="back-link" href="index.php">&larr; Back to RevSpecs</a>
  <header>
    <h1 id="quizTitle">Loading quiz…</h1>
    <div id="progressText"></div>
    <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>
  </header>
  <div id="quizBody"></div>
</div>

<button class="about-fab" id="aboutBtn" aria-haspopup="dialog" aria-expanded="false" aria-controls="aboutOverlay">
  <img src="specsLogo.png" class="about-fab-img" alt="SPECS logo">
</button>

<span class="fab-hint" id="fabHint" aria-hidden="true">Click me&nbsp;→</span>

<div class="about-overlay" id="aboutOverlay" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" hidden>
  <div class="about-modal">
    <button class="about-modal-close" id="aboutClose" aria-label="Close">&times;</button>
    <h2 id="aboutTitle">Gordon College &amp; SPECS</h2>
    <p>The Society of Programming Enthusiasts in Computer Science (SPECS) is an organization under the GCCCS</p>

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

<script src="assets/js/parallax.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/fab.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/quiz.js?v=<?= ASSET_VER ?>" defer></script>

</body>
</html>
