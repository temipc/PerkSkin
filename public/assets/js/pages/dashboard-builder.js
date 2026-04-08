(function () {
  const dataEl = document.getElementById('dashboard-builder-data');
  const pageSelect = document.getElementById('builderPageSelect');
  const layoutCanvas = document.getElementById('builderLayoutCanvas');
  const moduleLibrary = document.getElementById('builderModuleLibrary');
  if (!dataEl || !pageSelect || !layoutCanvas || !moduleLibrary) return;

  let payload = { pages: [], modules: [] };
  try { payload = JSON.parse(dataEl.textContent || '{}'); } catch {}
  let pages = Array.isArray(payload.pages) ? payload.pages : [];
  const modules = Array.isArray(payload.modules) ? payload.modules : [];
  const state = { pageKey: '', layout: [] };
  let sortable = null;
  const t = (key, fallback) => {
    try {
      if (typeof window.___ === 'function') return window.___(key);
    } catch {}
    return fallback || key;
  };

  const refs = {
    enabled: document.getElementById('builderEnabled'),
    showInNav: document.getElementById('builderShowInNav'),
    guest: document.getElementById('builderGuestEnabled'),
    user: document.getElementById('builderUserEnabled'),
    admin: document.getElementById('builderAdminEnabled'),
    navLabel: document.getElementById('builderNavLabel'),
    navHref: document.getElementById('builderNavHref'),
  };

  function notify(type, text) {
    try { Swal.fire({ icon: type, title: text, timer: 1200, showConfirmButton: false }); } catch {}
  }
  function iconSvg(name) {
    const icons = {
      duplicate: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h11v11H9z"/><path d="M4 4h11v11H4z"/></svg>',
      remove: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
    };
    return `<span class="adminGlyph">${icons[name] || icons.duplicate}</span>`;
  }

  function currentPage() {
    return pages.find((item) => String(item.page_key) === String(state.pageKey)) || null;
  }

  function renderPageOptions() {
    pageSelect.innerHTML = '';
    pages.forEach((page) => {
      const option = document.createElement('option');
      option.value = page.page_key;
      option.textContent = page.page_key;
      pageSelect.appendChild(option);
    });
    if (!state.pageKey && pages[0]) state.pageKey = pages[0].page_key;
    pageSelect.value = state.pageKey;
  }

  function renderLibrary() {
    moduleLibrary.innerHTML = '';
    const activePageKey = state.pageKey;
    modules.filter((item) => item.page === activePageKey).forEach((item) => {
      const col = document.createElement('div');
      col.className = 'col-md-6';
      col.innerHTML = `
        <div class="border rounded p-3 h-100">
          <div class="fw-semibold mb-1">${item.name}</div>
          <div class="small text-muted mb-3">${item.description || ''}</div>
          <button class="btn btn-sm btnPrimary" type="button">${t('Add module')}</button>
        </div>`;
      col.querySelector('button')?.addEventListener('click', () => {
        state.layout.push({ module: item.key, settings: {} });
        renderLayout();
      });
      moduleLibrary.appendChild(col);
    });
  }

  function renderAccess() {
    const page = currentPage();
    const access = page?.access || {};
    const rawLabel = access.nav_label || '';
    const displayLabel = rawLabel.startsWith('t:') && typeof window.$tKey === 'function' ? window.$tKey(rawLabel.slice(2)) : rawLabel;
    refs.enabled.checked = !!access.enabled;
    refs.showInNav.checked = !!access.show_in_nav;
    refs.guest.checked = !!access.guest_enabled;
    refs.user.checked = !!access.user_enabled;
    refs.admin.checked = !!access.admin_enabled;
    refs.navLabel.value = displayLabel;
    refs.navHref.value = access.nav_href || '';
  }

  function renderLayout() {
    layoutCanvas.innerHTML = '';
    state.layout.forEach((item, index) => {
      const moduleMeta = modules.find((module) => module.key === item.module);
      const card = document.createElement('div');
      card.className = 'border rounded p-3 d-flex align-items-center justify-content-between gap-3';
      card.dataset.index = String(index);
      card.innerHTML = `
        <div>
          <div class="fw-semibold">${moduleMeta?.name || item.module}</div>
          <div class="small text-muted">${item.module}</div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-light adminIconBtn" type="button" data-act="duplicate" title="${t('Duplicate')}" aria-label="${t('Duplicate')}">${iconSvg('duplicate')}</button>
          <button class="btn btn-sm btn-outline-danger adminIconBtn" type="button" data-act="remove" title="${t('Remove')}" aria-label="${t('Remove')}">${iconSvg('remove')}</button>
        </div>`;
      card.querySelector('[data-act="duplicate"]')?.addEventListener('click', () => {
        state.layout.splice(index + 1, 0, { module: item.module, settings: { ...(item.settings || {}) } });
        renderLayout();
      });
      card.querySelector('[data-act="remove"]')?.addEventListener('click', () => {
        state.layout.splice(index, 1);
        renderLayout();
      });
      layoutCanvas.appendChild(card);
    });

    if (!state.layout.length) {
      const empty = document.createElement('div');
      empty.className = 'small text-muted';
      empty.textContent = t('No modules on this page yet.');
      layoutCanvas.appendChild(empty);
    }

    if (window.Sortable) {
      sortable?.destroy?.();
      sortable = Sortable.create(layoutCanvas, {
        animation: 150,
        onEnd(event) {
          const moved = state.layout.splice(event.oldIndex, 1)[0];
          state.layout.splice(event.newIndex, 0, moved);
          renderLayout();
        },
      });
    }
  }

  function loadPage(pageKey) {
    state.pageKey = pageKey;
    const page = currentPage();
    state.layout = Array.isArray(page?.layout) ? [...page.layout] : [];
    renderAccess();
    renderLibrary();
    renderLayout();
  }

  pageSelect.addEventListener('change', () => loadPage(pageSelect.value));

  document.getElementById('builderSaveLayoutBtn')?.addEventListener('click', async () => {
    const response = await fetch('/index.php?page=api&action=saveBuilderLayout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ page_key: state.pageKey, layout: state.layout }),
    });
    if (!response.ok) {
      notify('error', t('Layout save failed'));
      return;
    }
    const page = currentPage();
    if (page) page.layout = [...state.layout];
    notify('success', t('Layout saved'));
  });

  document.getElementById('builderSaveAccessBtn')?.addEventListener('click', async () => {
    const access = {
      enabled: refs.enabled.checked,
      show_in_nav: refs.showInNav.checked,
      guest_enabled: refs.guest.checked,
      user_enabled: refs.user.checked,
      admin_enabled: refs.admin.checked,
      nav_label: refs.navLabel.value,
      nav_href: refs.navHref.value,
    };
    const response = await fetch('/index.php?page=api&action=saveBuilderAccess', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ page_key: state.pageKey, access }),
    });
    if (!response.ok) {
      notify('error', t('Access save failed'));
      return;
    }
    const page = currentPage();
    if (page) page.access = { ...(page.access || {}), ...access };
    notify('success', t('Access rules saved'));
  });

  renderPageOptions();
  loadPage(state.pageKey);
})();
