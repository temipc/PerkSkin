(function () {
  const dataEl = document.getElementById('app-bootstrap-data');
  let boot = {};
  try {
    boot = dataEl ? JSON.parse(dataEl.textContent || '{}') : {};
  } catch {}

  window.__basePath = boot.basePath || '';
  window.__locale = boot.locale || 'en';
  window.isLoggedIn = !!boot.isLoggedIn;
  window.isAdmin = !!boot.isAdmin;
  window._baseTranslations = window._baseTranslations || { en: {}, hu: {} };
  window._i18nLoaded = false;

  window.___ = function (text) {
    try {
      const loc = String(window.__locale || 'en').toLowerCase();
      const pack = window._baseTranslations?.[loc] || {};
      if (typeof pack[text] === 'string' && pack[text] !== '') return pack[text];
    } catch {}
    return text;
  };

  window.$tKey = function (key, repl) {
    try {
      const loc = String(window.__locale || 'en').toLowerCase();
      let value = window._baseTranslations?.[loc]?.[key] ?? window._baseTranslations?.en?.[key] ?? key;
      if (repl && typeof repl === 'object') {
        Object.keys(repl).forEach((token) => {
          value = String(value).replace(new RegExp('\\{' + token + '\\}', 'g'), String(repl[token]));
        });
      }
      return value;
    } catch {
      return key;
    }
  };

  try {
    fetch('/index.php?page=api&action=listTranslations')
      .then((response) => (response.ok ? response.json() : Promise.reject()))
      .then((payload) => {
        if (payload && typeof payload === 'object') {
          window._baseTranslations = payload;
        }
        window._i18nLoaded = true;
      })
      .catch(() => {
        window._i18nLoaded = true;
      });
  } catch {
    window._i18nLoaded = true;
  }
})();
