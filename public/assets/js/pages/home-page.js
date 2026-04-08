(function () {
  const dataEl = document.getElementById('home-page-data');
  if (!dataEl) return;
  try {
    window.serverData = window.serverData || {};
    Object.assign(window.serverData, JSON.parse(dataEl.textContent || '{}'));
  } catch {}
})();
