(function () {
  function moneyIcon() {
    return '<span class="exchangeActionGlyph" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h16v10H4z"/><path d="M8 12h8"/><circle cx="12" cy="12" r="2.4"/></svg></span>';
  }

  const t = (key, fallback) => {
    try {
      if (typeof window.___ === 'function') return window.___(key);
    } catch {}
    return fallback || key;
  };
  const fmt = (usd) => (typeof formatPriceUSDToDisplay === 'function' ? formatPriceUSDToDisplay(Number(usd) || 0) : ('$' + (Number(usd) || 0).toFixed(3)));
  const body = document.getElementById('exOffersBody');
  if (!body) return;

  const info = document.getElementById('exPagerInfo');
  const prev = document.getElementById('exPrev');
  const next = document.getElementById('exNext');
  const pageSize = 10;
  let page = 1;
  let offers = [];
  let inventory = [];

  const elName = document.getElementById('exItemName');
  const elVal = document.getElementById('exItemValue');
  const elAsk = document.getElementById('exAskValue');

  const bidModalEl = document.getElementById('exchangeBidModal');
  const bidModal = bidModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(bidModalEl) : null;
  const reviewModalEl = document.getElementById('exchangeReviewModal');
  const reviewModal = reviewModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(reviewModalEl) : null;

  function notify(kind, message) {
    if (window.notify) window.notify(kind, message);
    else if (window.Swal) Swal.fire({ icon: kind === 'error' ? 'error' : 'success', title: message, timer: 1800, showConfirmButton: false });
  }

  function bidLabel(bid) {
    if (!bid) return '-';
    if (bid.bid_type === 'cash') return `${t('Money amount', 'Money amount')}: ${fmt((Number(bid.bid_value_cents || 0) / 100))}`;
    if (bid.bid_type === 'gems') return `${t('Gems', 'Gems')}: ${Number(bid.gem_amount || 0)}`;
    return bid.bid_title || '-';
  }

  function bidValueLabel(bid) {
    if (!bid) return '-';
    if (bid.bid_type === 'gems') return `${Number(bid.gem_amount || 0)} ${t('Gems', 'Gems')}`;
    return fmt(Number(bid.bid_value_cents || 0) / 100);
  }

  function loadInventory() {
    if (!window.isLoggedIn) {
      inventory = [];
      return Promise.resolve();
    }
    return fetch('/index.php?page=api&action=listInventory')
      .then((response) => (response.ok ? response.json() : Promise.reject()))
      .then((payload) => {
        inventory = Array.isArray(payload?.items) ? payload.items.map((item) => ({
          id: Number(item.id || 0),
          name: item.item_title || '-',
          valueUSD: Number(item.item_value_cents || 0) / 100,
          coupon: item.coupon_code || '',
        })) : [];
      })
      .catch(() => { inventory = []; });
  }

  function renderInventorySelect() {
    if (!elName) return;
    elName.innerHTML = '';
    inventory.forEach((item) => {
      const option = document.createElement('option');
      option.value = String(item.id);
      option.text = item.coupon ? `${item.name} [${item.coupon}] (${fmt(item.valueUSD)})` : `${item.name} (${fmt(item.valueUSD)})`;
      option.dataset.name = item.name;
      option.dataset.valueUsd = String(item.valueUSD || 0);
      elName.appendChild(option);
    });
    try {
      if (typeof initPrettySelect === 'function') initPrettySelect(document);
    } catch {}
    refreshValueFromSelection();
  }

  function refreshValueFromSelection() {
    const selected = elName?.options?.[elName.selectedIndex];
    const usd = Number(selected?.dataset?.valueUsd || 0);
    if (elVal) elVal.value = usd ? usd.toFixed(3) : '';
  }

  function renderReviewRows(offer) {
    const tbody = document.getElementById('exchangeReviewBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const ownName = document.getElementById('exchangeReviewOwnName');
    const ownValue = document.getElementById('exchangeReviewOwnValue');
    const ownAsk = document.getElementById('exchangeReviewOwnAsk');
    const hint = document.getElementById('exchangeReviewHint');
    if (ownName) ownName.textContent = offer?.name || '-';
    if (ownValue) ownValue.textContent = `${t('Offer', 'Offer')}: ${fmt(offer?.valueUSD || 0)}`;
    if (ownAsk) ownAsk.textContent = `${t('Ask', 'Ask')}: ${fmt(offer?.askUSD || 0)}`;
    if (hint) hint.textContent = `${t('Listed value', 'Listed value')}: ${fmt(offer?.valueUSD || 0)} • ${t('Requested', 'Requested')}: ${fmt(offer?.askUSD || 0)}`;
    const bids = Array.isArray(offer?.bids) ? offer.bids : [];
    if (!bids.length) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-white-50 small">${t('No offers to show', 'No offers to show')}</td></tr>`;
      return;
    }
    bids.forEach((bid) => {
      const tr = document.createElement('tr');
      const status = String(bid.status || 'pending');
      const worthClass = bid.bid_type === 'gems'
        ? 'bg-primary'
        : ((Number(bid.bid_value_cents || 0) / 100) >= Number(offer?.askUSD || 0) ? 'bg-success' : 'bg-warning text-dark');
      tr.innerHTML = `
        <td>${new Date(bid.created_at || Date.now()).toLocaleString()}</td>
        <td><div class="text-white">${bidLabel(bid)}</div><div class="small text-white-50">${bid.bid_type || '-'}</div></td>
        <td><span class="badge ${worthClass}">${bidValueLabel(bid)}</span></td>
        <td>${status}</td>
        <td class="text-end">
          ${status === 'pending' ? `<button class="btn btn-sm btnPrimary" data-act="accept-bid" data-bid-id="${bid.id}">${t('Accept', 'Accept')}</button> <button class="btn btn-sm btn-outline-light" data-act="reject-bid" data-bid-id="${bid.id}">${t('Reject', 'Reject')}</button>` : ''}
        </td>
      `;
      tr.querySelectorAll('button[data-act]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const action = btn.getAttribute('data-act') === 'accept-bid' ? 'accept_bid' : 'reject_bid';
          fetch('/index.php?page=api&action=updateMarketOffer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: offer.id, action, bid_id: Number(btn.getAttribute('data-bid-id') || 0) }),
          })
            .then(async (response) => {
              const payload = await response.json().catch(() => ({}));
              if (!response.ok || payload?.error) throw new Error(payload?.error || 'request_failed');
              return payload;
            })
            .then(() => loadInventory().then(renderInventorySelect).then(loadOffers))
            .catch((err) => {
              const key = err?.message === 'expired_item' ? t('Expired items cannot be traded', 'Expired items cannot be traded') : t('Save failed', 'Save failed');
              notify('error', key);
            });
        });
      });
      tbody.appendChild(tr);
    });
  }

  function openBidModal(offer) {
    if (!bidModal) return;
    document.getElementById('exchangeBidOfferId').value = String(offer.id || '');
    document.getElementById('exchangeBidOfferTarget').textContent = `${offer.name || '-'} • ${fmt(offer.valueUSD || 0)}`;
    const inv = document.getElementById('exchangeBidInventory');
    if (inv) {
      inv.innerHTML = inventory
        .filter((item) => item.id !== Number(offer.inventory_item_id || 0))
        .map((item) => `<option value="${item.id}">${item.coupon ? `${item.name} [${item.coupon}]` : item.name} (${fmt(item.valueUSD)})</option>`)
        .join('');
    }
    document.getElementById('exchangeBidType').value = 'inventory';
    document.getElementById('exchangeBidCash').value = '';
    document.getElementById('exchangeBidGems').value = '';
    updateBidTypeUi();
    bidModal.show();
  }

  function openReviewModal(offer) {
    if (!reviewModal) return;
    document.getElementById('exchangeReviewOfferId').value = String(offer.id || '');
    renderReviewRows(offer);
    reviewModal.show();
  }

  function render() {
    body.innerHTML = '';
    const totalPages = Math.max(1, Math.ceil(offers.length / pageSize));
    if (page > totalPages) page = totalPages;
    const start = (page - 1) * pageSize;
    offers.slice(start, start + pageSize).forEach((offer) => {
      const tr = document.createElement('tr');
      const pendingCount = Number(offer.pendingBidCount || 0);
      const statusText = offer.status === 'open'
        ? `<span class="badge bg-info">${t('Open', 'Open')}</span>${pendingCount ? ` <span class="badge bg-warning text-dark">${pendingCount}</span>` : ''}`
        : `<span class="badge bg-secondary">${offer.status}</span>`;
      tr.innerHTML = `
        <td>${new Date(offer.ts || Date.now()).toLocaleString()}</td>
        <td>${offer.name || '-'}</td>
        <td>${fmt(offer.valueUSD || 0)}</td>
        <td>${fmt(offer.askUSD || 0)}</td>
        <td>${statusText}</td>
        <td class="text-end">
          ${offer.status === 'open' && offer.canBid ? `<button class="btn btn-sm exchangeMoneyBtn" data-act="bid" title="${t('Place bid', 'Place bid')}" aria-label="${t('Place bid', 'Place bid')}">${moneyIcon()}</button>` : ''}
          ${offer.status === 'open' && offer.canReview ? `<button class="btn btn-sm btn-outline-light" data-act="review">${t('Received offers', 'Received offers')}</button>` : ''}
          ${offer.status === 'open' && offer.canClose ? ` <button class="btn btn-sm btn-outline-light" data-act="close">${t('Close offer', 'Close offer')}</button>` : ''}
        </td>`;
      tr.querySelectorAll('button[data-act]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const action = btn.getAttribute('data-act');
          if (action === 'bid') {
            openBidModal(offer);
            return;
          }
          if (action === 'review') {
            openReviewModal(offer);
            return;
          }
          fetch('/index.php?page=api&action=updateMarketOffer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: offer.id, action: 'close' }),
          }).then((response) => (response.ok ? loadOffers() : Promise.reject())).catch(() => {});
        });
      });
      body.appendChild(tr);
    });
    info.textContent = `${page} / ${Math.max(1, Math.ceil(offers.length / pageSize))}`;
    prev.disabled = page <= 1;
    next.disabled = page >= Math.max(1, Math.ceil(offers.length / pageSize));
  }

  function loadOffers() {
    return fetch('/index.php?page=api&action=listMarketOffers')
      .then((response) => (response.ok ? response.json() : Promise.reject()))
      .then((payload) => {
        const items = Array.isArray(payload?.items) ? payload.items : [];
        offers = items.map((item) => ({
          id: Number(item.id || 0),
          name: item.item_title || '-',
          valueUSD: Number(item.item_value_cents || 0) / 100,
          askUSD: Number(item.requested_value_cents || 0) / 100,
          inventory_item_id: Number(item.inventory_item_id || 0),
          status: item.status || 'open',
          ts: Date.parse(item.created_at || '') || Date.now(),
          canClose: !!item.can_close,
          canBid: !!item.can_bid,
          canReview: !!item.can_review_bids,
          pendingBidCount: Array.isArray(item.bids) ? item.bids.filter((bid) => String(bid.status || '') === 'pending').length : (Number(item.has_pending_bids) || 0),
          bids: Array.isArray(item.bids) ? item.bids : [],
        }));
        render();
      })
      .catch(() => {});
  }

  function updateBidTypeUi() {
    const type = document.getElementById('exchangeBidType')?.value || 'inventory';
    document.getElementById('exchangeBidInventoryWrap')?.classList.toggle('d-none', type !== 'inventory');
    document.getElementById('exchangeBidCashWrap')?.classList.toggle('d-none', type !== 'cash');
    document.getElementById('exchangeBidGemsWrap')?.classList.toggle('d-none', type !== 'gems');
  }

  prev?.addEventListener('click', () => { if (page > 1) { page -= 1; render(); } });
  next?.addEventListener('click', () => { const max = Math.max(1, Math.ceil(offers.length / pageSize)); if (page < max) { page += 1; render(); } });
  elName?.addEventListener('change', refreshValueFromSelection);
  document.getElementById('exchangeBidType')?.addEventListener('change', updateBidTypeUi);

  document.getElementById('exCreateOffer')?.addEventListener('click', () => {
    const selected = elName?.options?.[elName.selectedIndex];
    if (!selected) {
      notify('error', t('Pick an item from inventory', 'Pick an item from inventory'));
      return;
    }
    const askUSD = Number(elAsk?.value || 0);
    fetch('/index.php?page=api&action=saveMarketOffer', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ inventory_item_id: Number(selected.value || 0), requested_value_usd: askUSD, currency: 'USD' }),
    })
      .then(async (response) => {
        const payload = await response.json().catch(() => ({}));
        if (!response.ok || payload?.error) throw new Error(payload?.error || 'request_failed');
        return payload;
      })
      .then(() => {
        if (elAsk) elAsk.value = '';
        return loadOffers();
      })
      .catch((err) => {
        const key = err?.message === 'expired_item' ? t('Expired items cannot be traded', 'Expired items cannot be traded') : t('Save failed', 'Save failed');
        notify('error', key);
      });
  });

  document.getElementById('exchangeBidSubmit')?.addEventListener('click', () => {
    const offerId = Number(document.getElementById('exchangeBidOfferId')?.value || 0);
    const bidType = document.getElementById('exchangeBidType')?.value || 'inventory';
    const payload = { offer_id: offerId, bid_type: bidType };
    if (bidType === 'inventory') payload.inventory_item_id = Number(document.getElementById('exchangeBidInventory')?.value || 0);
    if (bidType === 'cash') payload.cash_value_usd = Number(document.getElementById('exchangeBidCash')?.value || 0);
    if (bidType === 'gems') payload.gem_amount = Number(document.getElementById('exchangeBidGems')?.value || 0);
    fetch('/index.php?page=api&action=saveMarketBid', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data?.error) throw new Error(data?.error || 'request_failed');
        return data;
      })
      .then(() => {
        bidModal?.hide();
        return loadOffers();
      })
      .catch((err) => {
        const map = {
          cannot_bid_own_offer: t('You cannot make an offer to yourself', 'You cannot make an offer to yourself'),
          expired_item: t('Expired items cannot be traded', 'Expired items cannot be traded'),
          existing_pending_bid: t('You already have a pending offer here', 'You already have a pending offer here'),
        };
        notify('error', map[err?.message] || t('Save failed', 'Save failed'));
      });
  });

  document.getElementById('exClear')?.addEventListener('click', () => { if (elAsk) elAsk.value = ''; });

  loadInventory()
    .then(renderInventorySelect)
    .then(loadOffers);
})();
