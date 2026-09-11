<?php
define('ASSET_VER', '1.0.0');
$subjects = include 'subjects-config.php';
$years = [1 => ['subjects' => []], 2 => ['subjects' => []], 3 => ['subjects' => []]];
foreach ($subjects as $code => $info) {
    $years[$info['year']]['subjects'][] = ['code' => $code, 'name' => $info['name']];
}
?><!DOCTYPE html>
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
<link rel="stylesheet" href="assets/css/index.css?v=<?= ASSET_VER ?>">
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
  <header class="ambient-fade">
    <div>
      <h1 id="revspecsTitle" title="Click for ambient mode">RevSpecs</h1>
    </div>
    <p class="tagline">SPECS reviewer library, sorted by year. Pick a subject to open its set.</p>
  </header>
  <time class="digital-clock ambient-fade" id="digitalClock" aria-label="Current time">00:00:00</time>

  <div class="tabs ambient-fade" role="tablist" aria-label="Select year level">
    <button class="tab" role="tab" data-year="1" aria-selected="true">1st Year</button>
    <button class="tab" role="tab" data-year="2" aria-selected="false">2nd Year</button>
    <button class="tab" role="tab" data-year="3" aria-selected="false">3rd Year</button>
  </div>

  <div class="panel ambient-fade" data-year="1" id="panel">
    <div class="panel-year-bar" data-year="1"></div>
    <p class="panel-instruction">Click a subject and choose between a reviewer or a quiz.</p>
    <div class="subject-grid" id="subjectGrid"></div>
  </div>

  <footer class="ambient-fade">
    RevSpecs A.Y. 2026 - 2027 — built for Gordon College BSCS Students.
    <p class="credit">Developed and Proposed by Perez, Emilio James — 2nd Year Representative.</p>
  </footer>
</div>

<button class="about-fab ambient-fade" id="aboutBtn" aria-haspopup="dialog" aria-expanded="false" aria-controls="aboutOverlay">
  <img src="specsLogo.png" class="about-fab-img" alt="SPECS logo">
</button>

<span class="fab-hint ambient-fade" id="fabHint" aria-hidden="true">Click me&nbsp;→</span>

<span class="ambient-exit-hint" id="ambientExitHint" aria-hidden="true">pahinga ka muna, and enjoy the view :)</span>

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

<script type="application/json" id="yearsData"><?= json_encode($years, JSON_UNESCAPED_SLASHES) ?></script>

<script src="assets/js/parallax.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/fab.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/index.js?v=<?= ASSET_VER ?>" defer></script>

</body>
</html>
