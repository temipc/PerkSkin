(function () {
  const btn = document.getElementById('btnOpenThisCase');
  if (!btn) return;

  if (!window.isLoggedIn) {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      const modal = document.getElementById('authModal');
      if (modal && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modal).show();
      } else {
        location.href = '/index.php?page=home#login';
      }
    });
    return;
  }

  function getToday() { try { return new Date().toISOString().slice(0, 10); } catch { return ''; } }
  function getLevel() { try { return Math.max(1, Math.min(10, Number(window.state?.level) || 1)); } catch { return 1; } }
  function totalAllowed() { return Math.min(5, getLevel()); }
  function getCaseKey() { return 'caseSpin.' + (btn.getAttribute('data-title') || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }
  function readCaseState() {
    const key = getCaseKey();
    try {
      const state = typeof window.readClientState === 'function' ? window.readClientState(key, null) : null;
      return { key, data: state || { date: getToday(), used: 0, paid: false } };
    } catch {
      return { key, data: { date: getToday(), used: 0, paid: false } };
    }
  }
  function writeCaseState(nextState) { try { if (typeof window.writeClientState === 'function') window.writeClientState(getCaseKey(), nextState); } catch {} }
  function ensureToday() { const current = readCaseState().data; if (current.date !== getToday()) writeCaseState({ date: getToday(), used: 0, paid: false }); }
  function remainingSpins() { const current = readCaseState().data; const used = current.date === getToday() ? (Number(current.used) || 0) : 0; return Math.max(0, totalAllowed() - used); }
  function isPaid() { const current = readCaseState().data; return current.date === getToday() ? !!current.paid : false; }
  function setPaid() { const current = readCaseState().data; writeCaseState({ date: getToday(), used: current.date === getToday() ? (Number(current.used) || 0) : 0, paid: true }); }
  function consumeOne() { const current = readCaseState().data; const used = (current.date === getToday() ? (Number(current.used) || 0) : 0) + 1; writeCaseState({ date: getToday(), used, paid: !!current.paid }); }
  function updateCaseSpinLabel() {
    const el = document.getElementById('caseSpinInfo');
    if (!el) return;
    const left = remainingSpins();
    const total = totalAllowed();
    const text = typeof window.$tKey === 'function' ? window.$tKey('cases.freeSpinsToday', { left: String(left), total: String(total) }) : `Free spins today: ${left}/${total}`;
    el.innerHTML = `<span class="badge rounded-pill bg-info text-dark px-3 py-2 fw-semibold">${text}</span>`;
  }
  function refreshBtnDisabled() {
    ensureToday();
    const price = Number(btn.getAttribute('data-price')) || 0;
    const balance = typeof window.state?.balance === 'number' ? window.state.balance : 0;
    const gems = typeof window.state?.gems === 'number' ? window.state.gems : 0;
    const reqLevel = Number(btn.getAttribute('data-req-level')) || 1;
    const levelOk = (window.state?.level || getLevel()) >= reqLevel;
    const gemCost = Math.max(0, Math.round(price * 1000 / 10) * 10);
    const canPay = balance >= price || gems >= gemCost;
    const eventStart = btn.getAttribute('data-event-start') || '';
    const eventEnd = btn.getAttribute('data-event-end') || '';
    const now = Date.now();
    const startTs = eventStart ? new Date(String(eventStart).replace(' ', 'T')).getTime() : null;
    const endTs = eventEnd ? new Date(String(eventEnd).replace(' ', 'T')).getTime() : null;
    const eventBlocked = (startTs && now < startTs) || (endTs && now > endTs);
    btn.disabled = eventBlocked || !levelOk || (!isPaid() && !canPay) || (isPaid() && remainingSpins() <= 0);
  }

  window.__caseMarkPaid = function () {
    setPaid();
    refreshBtnDisabled();
    updateCaseSpinLabel();
  };

  document.addEventListener('pi:balanceUpdated', refreshBtnDisabled);
  document.addEventListener('pi:levelUpdated', () => {
    updateCaseSpinLabel();
    refreshBtnDisabled();
  });
  document.addEventListener('pi.clientStateLoaded', () => {
    updateCaseSpinLabel();
    refreshBtnDisabled();
  });

  btn.addEventListener('click', function () {
    const price = Number(this.getAttribute('data-price')) || 0;
    const reqLevel = Number(this.getAttribute('data-req-level')) || 1;
    const title = this.getAttribute('data-title') || '';
    const eventStart = this.getAttribute('data-event-start') || '';
    const eventEnd = this.getAttribute('data-event-end') || '';
    const eventTitle = this.getAttribute('data-event-title') || '';
    ensureToday();
    if (eventStart || eventEnd) {
      const now = Date.now();
      const startTs = eventStart ? new Date(String(eventStart).replace(' ', 'T')).getTime() : null;
      const endTs = eventEnd ? new Date(String(eventEnd).replace(' ', 'T')).getTime() : null;
      if ((startTs && now < startTs) || (endTs && now > endTs)) {
        if (window.notify) window.notify('warning', eventTitle ? `${eventTitle} window only` : 'This event case can only be opened during the active event window');
        return;
      }
    }
    if ((window.state?.level || 1) < reqLevel) {
      if (window.notify) window.notify('warning', 'You need at least Lv {level} for this case', { level: reqLevel });
      return;
    }
    const paid = isPaid();
    const spinsLeft = remainingSpins();
    const gems = typeof window.state?.gems === 'number' ? window.state.gems : 0;
    const gemCost = Math.max(0, Math.round(price * 1000 / 10) * 10);
    if (!paid && window.state.balance < price && gems < gemCost) {
      const insufficientModal = document.getElementById('insufficientModal');
      if (insufficientModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(insufficientModal).show();
      return;
    }
    if (paid && spinsLeft <= 0) {
      if (window.notify) window.notify('info', 'No spins left today for this case');
      return;
    }

    let items = [];
    try { items = JSON.parse(this.getAttribute('data-items') || '[]'); } catch {}
    if (!Array.isArray(items) || items.length === 0) items = [{ name: 'Coffee x1', value: 1.0 }, { name: 'Gym -10%', value: 0 }, { name: 'Fuel 1%', value: 0 }];
    const reel = document.querySelector('.spinnerReel');
    const viewport = document.querySelector('.spinnerViewport');

    function showResult(name) {
      const pick = items.find((item) => (item.name || item.title) === name) || items[Math.floor(Math.random() * items.length)];
      let priceUSD = 0;
      let priceGems = 0;
      if (!isPaid()) {
        const balance = typeof window.state?.balance === 'number' ? window.state.balance : 0;
        if (balance >= price) priceUSD = price;
        else if (gems >= gemCost) priceGems = gemCost;
      }
      const result = {
        name: pick.name || pick.title || name || 'Item',
        value: Number(pick.value || 0),
        caseTitle: title,
        priceUSD,
        priceGems,
        coupon: window.randomCoupon ? window.randomCoupon('CASE') : ('CASE-' + Math.random().toString(36).slice(2, 8).toUpperCase()),
      };
      if (typeof window.showCaseOpenModal === 'function') window.showCaseOpenModal(result, items);
    }

    if (reel && viewport) {
      reel.innerHTML = '';
      const base = items.map((item) => ({ text: item.name || item.title || 'Item' }));
      const pool = Array.from({ length: 12 }).flatMap(() => base);
      pool.forEach((item) => {
        const el = document.createElement('div');
        el.className = 'spinnerItem';
        el.textContent = item.text;
        reel.appendChild(el);
      });
      const itemWidth = 132;
      const visibleCenter = (viewport.clientWidth / 2) - 60;
      const targetIndex = (base.length * (4 + Math.floor(Math.random() * 3))) + Math.floor(Math.random() * base.length);
      const to = -((targetIndex * itemWidth) - visibleCenter);
      const duration = 2200;
      if (typeof reel.animate === 'function') {
        const animation = reel.animate([{ transform: 'translateX(0px)' }, { transform: `translateX(${to}px)` }], { duration, easing: 'cubic-bezier(0.08, 0.9, 0.2, 1)', fill: 'forwards' });
        animation.onfinish = () => {
          reel.style.transform = `translateX(${to}px)`;
          consumeOne();
          updateCaseSpinLabel();
          refreshBtnDisabled();
          showResult(pool[targetIndex % pool.length].text);
        };
      } else {
        reel.style.transition = `transform ${duration}ms cubic-bezier(0.08, 0.9, 0.2, 1)`;
        reel.style.transform = `translateX(${to}px)`;
        setTimeout(() => {
          reel.style.transition = '';
          consumeOne();
          updateCaseSpinLabel();
          refreshBtnDisabled();
          showResult(pool[targetIndex % pool.length].text);
        }, duration + 30);
      }
      return;
    }
    const pick = items[Math.floor(Math.random() * items.length)];
    consumeOne();
    updateCaseSpinLabel();
    refreshBtnDisabled();
    showResult(pick.name || pick.title || 'Item');
  });

  const info = document.createElement('div');
  info.id = 'caseSpinInfo';
  info.className = 'd-inline-flex align-items-center';
  btn.parentElement?.appendChild(info);
  updateCaseSpinLabel();
  refreshBtnDisabled();
})();
