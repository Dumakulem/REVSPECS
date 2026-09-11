(function () {
  const stage = document.getElementById('parallaxStage');
  if (!stage) return;

  function getTimePeriod() {
    const hour = new Date().getHours();
    if (hour >= 6 && hour < 15) return 'day';
    if (hour >= 15 && hour < 18) return 'dusk';
    return 'night';
  }

  function updateTimePeriod() {
    document.documentElement.dataset.timePeriod = getTimePeriod();
  }

  updateTimePeriod();
  setInterval(updateTimePeriod, 60000);

  const STAGE_W = 320;
  const STAGE_H = 178;

  function fit() {
    const scale = Math.max(window.innerWidth / STAGE_W, window.innerHeight / STAGE_H);
    stage.style.setProperty('--scale', scale);
  }

  window.addEventListener('resize', fit);
  fit();

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
