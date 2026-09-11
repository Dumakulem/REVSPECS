<?php
define('ASSET_VER', '1.0.0');
$subjects = include 'subjects-config.php';
$subjectCode = $_GET['subject'] ?? '';
$subject = $subjects[$subjectCode] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RevSpecs — <?php echo htmlspecialchars($subject['name'] ?? 'Reviewer'); ?></title>
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <meta name="theme-color" id="themeColorMeta" content="#FFFFFF">
    <link rel="stylesheet" href="assets/css/base.css?v=<?= ASSET_VER ?>">
    <link rel="stylesheet" href="assets/css/parallax.css?v=<?= ASSET_VER ?>">
    <link rel="stylesheet" href="assets/css/fab.css?v=<?= ASSET_VER ?>">
    <link rel="stylesheet" href="assets/css/reviewer.css?v=<?= ASSET_VER ?>">
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

<div class="container">
    <div class="mobile-toolbar" id="mobileToolbar">
        <a href="index.php" class="back-btn">←</a>
        <span class="title"><?php echo htmlspecialchars($subject['name'] ?? 'Reviewer'); ?></span>
        <span class="page-indicator" id="mobilePageIndicator">1/1</span>
        <?php if ($subject): ?>
        <button type="button" class="icon-btn fs-btn" id="mobileFullscreenBtn"
                aria-label="Toggle fullscreen" aria-pressed="false" title="Enter fullscreen">
            <span class="fs-icon"></span>
        </button>
        <?php endif; ?>
    </div>

    <a class="back-link" href="index.php">&larr; Back to RevSpecs</a>
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
            <div class="toolbar" id="desktopToolbar">
                <div class="toolbar-left">
                    <a href="index.php" class="btn toolbar-back" title="Back to RevSpecs">← Back</a>
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

            <div id="pdfContainer">
                <div class="status" id="status">Loading reviewer...</div>
            </div>

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

    <div class="mobile-controls" id="mobileControls">
        <button class="nav-btn" id="mobilePrevBtn">←</button>
        <button class="zoom-btn" id="mobileZoomOut">−</button>
        <button class="zoom-btn" id="mobileZoomIn">+</button>
        <a href="<?php echo $subject ? htmlspecialchars($subject['pdf']) : '#'; ?>" download class="download-btn">⬇ PDF</a>
        <button class="nav-btn" id="mobileNextBtn">→</button>
    </div>

    <button class="fs-show-bar" id="showToolbarBtn" type="button" aria-label="Show controls">
        <span aria-hidden="true">▼</span> Show controls
    </button>
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

<?php if ($subject): ?>
<script>window.REVIEWER_PDF_PATH = <?php echo json_encode($subject['pdf']); ?>;</script>
<script src="assets/js/parallax.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/fab.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/reviewer.js?v=<?= ASSET_VER ?>" defer></script>
<?php else: ?>
<script src="assets/js/parallax.js?v=<?= ASSET_VER ?>" defer></script>
<script src="assets/js/fab.js?v=<?= ASSET_VER ?>" defer></script>
<?php endif; ?>

</body>
</html>
