// Basic demo data and UI behaviors
const usesStoredUserPrefs = !window.isLoggedIn;
window.__serverHistory = window.__serverHistory || [];
window.__serverInventory = window.__serverInventory || [];
window.__clientStateCache = window.__clientStateCache || {};

function cloneClientStateValue(value) {
  if (typeof value === 'undefined') return undefined;
  try { return JSON.parse(JSON.stringify(value)); } catch { return value; }
}

async function loadClientState() {
  try {
    const r = await fetch('/index.php?page=api&action=getClientState');
    if (!r.ok) throw new Error('http');
    const d = await r.json();
    window.__clientStateCache = (d && d.items && typeof d.items === 'object') ? d.items : {};
  } catch {
    window.__clientStateCache = window.__clientStateCache || {};
  }
  try { document.dispatchEvent(new CustomEvent('pi.clientStateLoaded')); } catch {}
  return window.__clientStateCache;
}

function readClientState(key, fallbackValue) {
  try {
    const cache = window.__clientStateCache || {};
    if (Object.prototype.hasOwnProperty.call(cache, key)) return cloneClientStateValue(cache[key]);
  } catch {}
  return cloneClientStateValue(fallbackValue);
}

function persistClientState(key, value, shouldDelete) {
  try {
    fetch('/index.php?page=api&action=saveClientState', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(shouldDelete ? { key, delete: true } : { key, value })
    }).catch(() => {});
  } catch {}
}

function writeClientState(key, value, opts) {
  const options = opts || {};
  const shouldDelete = !!options.delete;
  try {
    if (shouldDelete) delete window.__clientStateCache[key];
    else window.__clientStateCache[key] = cloneClientStateValue(value);
  } catch {}
  if (options.persist !== false) persistClientState(key, value, shouldDelete);
}

const state = {
  locale: document.documentElement.lang || 'en',
  liveDropEnabled: true,
  balance: 0,
  gems: 0,
  level: 1,
  currency: 'USD',
  rates: null, // { base:'USD', date:'YYYY-MM-DD', rates:{ EUR: n, HUF: n, ... } }
  cases: (window.serverData && window.serverData.cases) || [],
  communityCases: (window.serverData && window.serverData.communityCases) || [],
  featuredCases: (window.serverData && window.serverData.featuredCases) || [],
  eventCases: (window.serverData && window.serverData.eventCases) || [],
  homeEvents: (window.serverData && window.serverData.homeEvents) || [],
  spinnerProducts: (window.serverData && window.serverData.spinnerProducts) || [],
  homeBundles: (window.serverData && window.serverData.homeBundles) || [],
  drops: [
    { name: 'Neon Gloves', value: 39.5 },
    { name: 'Violet Dagger', value: 129.0 },
    { name: 'Pulse AK', value: 12.3 },
    { name: 'Spectra AWP', value: 7.9 },
    { name: 'Nova Knife', value: 220.0 },
  ],
  chat: [],
  products: {}, // name -> priceUSD catalog
};

// Unified notification helper (SweetAlert2 if available)
function notify(type, keyOrText, repl){
  try {
    const t = (typeof window.___ === 'function') ? window.___(String(keyOrText)) : String(keyOrText);
    let msg = t;
    if (repl && typeof repl === 'object') {
      Object.keys(repl).forEach(k => { msg = msg.replace(new RegExp('\\{'+k+'\\}', 'g'), String(repl[k])); });
    }
    if (window.Swal) {
      Swal.fire({ icon: type || 'info', title: msg, timer: 1800, showConfirmButton: false });
    } else {
      // Fall back silently without blocking UI if SweetAlert is unavailable
      console.warn('[notify]', msg);
    }
  } catch {
    try { console.warn('[notify:fallback]', String(keyOrText)); } catch {}
  }
}

// Balance persistence helpers
function readStoredBalance() {
  return null;
}
function writeStoredBalance(val) {
  return val;
}

// Fallback demo data if serverData is missing/empty
if (!Array.isArray(state.cases) || state.cases.length === 0) {
  state.cases = [
    { id: 1, title: 'Starter Sparks', price: 2.99, tag: 'starter', img: '/assets/images/case-1.svg', risk: 'low' },
    { id: 2, title: 'Neon Rush', price: 6.49, tag: 'hot', img: '/assets/images/case-2.svg', risk: 'medium' },
    { id: 3, title: 'Limited Pulse', price: 12.99, tag: 'limited', img: '/assets/images/case-3.svg', risk: 'high' },
    { id: 4, title: 'Daily Boost', price: 3.49, tag: 'starter', img: '/assets/images/case-1.svg', risk: 'low' },
    { id: 5, title: 'Violet Vault', price: 24.99, tag: 'limited', img: '/assets/images/case-3.svg', risk: 'very-high' },
    { id: 6, title: 'Hyper Nova', price: 49.99, tag: 'hot', img: '/assets/images/case-2.svg', risk: 'medium' },
  ];
}
if ((!window.serverData || !Array.isArray(window.serverData.eventCases)) && (!Array.isArray(state.eventCases) || state.eventCases.length === 0)) {
  state.eventCases = [
    { id: 101, title: 'Disco Drop', price: 7.99, tag: 'limited', img: '/assets/images/case-2.svg', risk: 'medium' },
    { id: 102, title: 'Neon Nights', price: 15.49, tag: 'hot', img: '/assets/images/case-3.svg', risk: 'high' },
    { id: 103, title: 'Violet Vault+', price: 29.99, tag: 'limited', img: '/assets/images/case-1.svg', risk: 'very-high' },
    { id: 104, title: 'Daily Boost+', price: 5.49, tag: 'starter', img: '/assets/images/case-1.svg', risk: 'low' },
    { id: 105, title: 'Hyper Nova X', price: 54.99, tag: 'hot', img: '/assets/images/case-2.svg', risk: 'medium' },
    { id: 106, title: 'Pulse Deluxe', price: 19.99, tag: 'limited', img: '/assets/images/case-3.svg', risk: 'high' },
  ];
}
if (!Array.isArray(state.homeEvents) || state.homeEvents.length === 0) {
  const now = new Date();
  const mk = (days, hour, title, description, color) => {
    const start = new Date(now.getTime() + days * 24 * 3600 * 1000);
    start.setHours(hour, 0, 0, 0);
    const end = new Date(start.getTime() + 2 * 3600 * 1000);
    const fmt = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}:00`;
    return { id: `evt-${title}`, title, description, href: '/index.php?page=events', start_at: fmt(start), end_at: fmt(end), color };
  };
  state.homeEvents = [
    mk(1, 19, 'Night Drop Warmup', 'Limited preview event with bonus rewards.', '#4da3ff'),
    mk(3, 20, 'Weekend Neon Event', 'Weekend event with highlighted community rewards.', '#18b38a'),
    mk(6, 18, 'Bundle Rush Hour', 'Timed event focused on bundle and case drops.', '#ff8a3d'),
    mk(9, 21, 'Late Night Case Clash', 'Community opening session and live reward rotation.', '#ff5c8a'),
    mk(14, 17, 'Spring Reward Wave', 'Seasonal event window with stacked drops.', '#ffd166'),
  ];
}
if (!Array.isArray(state.communityCases) || state.communityCases.length === 0) {
  state.communityCases = Array.isArray(state.cases) ? [...state.cases] : [];
}
if (!Array.isArray(state.featuredCases) || state.featuredCases.length === 0) {
  state.featuredCases = Array.isArray(state.cases) ? state.cases.slice(0, 5) : [];
}
if (!Array.isArray(state.homeBundles) || state.homeBundles.length === 0) {
  state.homeBundles = (Array.isArray(state.spinnerProducts) ? state.spinnerProducts : [])
    .filter((p) => (p.product_type || 'product') === 'bundle')
    .map((p) => ({ id: p.id, name: p.title, item_count: 0, value: Number(p.value || 0) }));
}
if (!Array.isArray(state.homeBundles)) {
  state.homeBundles = [];
}
if (state.homeBundles.length < 5) {
  const fallbackBundles = [
    {
      id: 'bundle-starter',
      name: 'Starter Saver Pack',
      item_count: 3,
      value: 9.99,
      contents: [
        { type: 'product', name: 'Starter Key', quantity: 1 },
        { type: 'product', name: 'XP Booster', quantity: 1 },
        { type: 'category', name: 'Starter Skins', quantity: 1 },
      ],
    },
    {
      id: 'bundle-neon',
      name: 'Neon Weekend Bundle',
      item_count: 4,
      value: 14.99,
      contents: [
        { type: 'product', name: 'Neon Gloves', quantity: 2 },
        { type: 'case', name: 'Neon Rush', quantity: 1 },
        { type: 'category', name: 'Weekend Picks', quantity: 1 },
      ],
    },
    {
      id: 'bundle-vip',
      name: 'VIP Bronze Bundle',
      item_count: 5,
      value: 19.99,
      contents: [
        { type: 'product', name: 'Bronze Token', quantity: 3 },
        { type: 'bundle', name: 'Starter Saver Pack', quantity: 1 },
        { type: 'category', name: 'VIP Rewards', quantity: 1 },
      ],
    },
    {
      id: 'bundle-event',
      name: 'Event Booster Pack',
      item_count: 4,
      value: 24.99,
      contents: [
        { type: 'case', name: 'Event Crate', quantity: 1 },
        { type: 'product', name: 'Booster Ticket', quantity: 2 },
        { type: 'category', name: 'Event Drops', quantity: 1 },
      ],
    },
    {
      id: 'bundle-premium',
      name: 'Premium Reward Crate',
      item_count: 6,
      value: 34.99,
      contents: [
        { type: 'product', name: 'Premium Voucher', quantity: 2 },
        { type: 'bundle', name: 'VIP Bronze Bundle', quantity: 1 },
        { type: 'case', name: 'Violet Vault', quantity: 1 },
        { type: 'category', name: 'Premium Inventory', quantity: 1 },
      ],
    },
  ];
  const existingIds = new Set(state.homeBundles.map((bundle) => String(bundle.id || bundle.name || '')));
  fallbackBundles.forEach((bundle) => {
    if (state.homeBundles.length >= 5) return;
    const key = String(bundle.id || bundle.name || '');
    if (existingIds.has(key)) return;
    state.homeBundles.push({ ...bundle });
    existingIds.add(key);
  });
}

// Currency & FX helpers (balance and prices are defined in USD by default)
const FX_STORAGE_KEY = 'pi.fx';
function readStoredFx() {
  try { const raw = localStorage.getItem(FX_STORAGE_KEY); return raw ? JSON.parse(raw) : null; } catch { return null; }
}
function writeStoredFx(obj) { try { localStorage.setItem(FX_STORAGE_KEY, JSON.stringify(obj)); } catch { /* ignore */ } }
function getTodayStr() { return new Date().toISOString().slice(0,10); }

async function ensureFxRates() {
  // Try cache first (same day)
  const cached = readStoredFx();
  const today = getTodayStr();
  if (cached && cached.base === 'USD' && cached.date === today && cached.rates) {
    state.rates = cached; return cached;
  }
  // Free API: Frankfurter.app (no key). We'll fetch latest with base USD.
  // Endpoint: https://api.frankfurter.app/latest?from=USD
  try {
    const res = await fetch('https://api.frankfurter.app/latest?from=USD');
    if (!res.ok) throw new Error('fx fetch failed');
    const data = await res.json();
    const fx = { base: data.base || 'USD', date: data.date || today, rates: data.rates || {} };
    // include base=USD rate as 1
    fx.rates['USD'] = 1;
    state.rates = fx; writeStoredFx(fx); return fx;
  } catch(e) {
    // Fallback static guess to avoid breaking UI
    const fallback = { base:'USD', date: today, rates: { USD:1, EUR:0.92, HUF:360 } };
    state.rates = fallback; writeStoredFx(fallback); return fallback;
  }
}

function setCurrency(cur) {
  state.currency = cur || 'USD';
  if (usesStoredUserPrefs) {
    writeClientState('guest.currency', state.currency);
  }
  // Update UI dependent on currency
  updateBalanceDisplay();
  refreshPriceDisplays();
  try {
    document.dispatchEvent(new CustomEvent('pi.currencyChanged', { detail: { currency: state.currency } }));
  } catch {}
}

function convertFromUSD(amountUSD, targetCurrency) {
  const n = Number(amountUSD) || 0;
  const cur = targetCurrency || state.currency || 'USD';
  const r = (state.rates && state.rates.rates && state.rates.rates[cur]);
  const rate = (typeof r === 'number' && isFinite(r)) ? r : 1; // if missing, assume 1
  return n * rate;
}

function convertToUSD(amount, fromCurrency){
  const n = Number(amount) || 0;
  const cur = fromCurrency || state.currency || 'USD';
  if (!state.rates || !state.rates.rates) return n; // best effort
  const rate = state.rates.rates[cur];
  if (typeof rate !== 'number' || !isFinite(rate) || rate === 0) return n;
  return n / rate;
}

// Ensure USD payout corresponds to at least the minimum displayable amount in the current currency
function adjustPayoutUSDForCurrencyMin(payoutUSD){
  const cur = state.currency || 'USD';
  const isHuf = cur === 'HUF';
  const minUnitDisplay = isHuf ? 0.1 : 0.001; // in target currency
  const currentInCur = convertFromUSD(payoutUSD, cur);
  if (currentInCur > 0 && currentInCur < minUnitDisplay) {
    const neededUSD = convertToUSD(minUnitDisplay, cur);
    return Number(Math.max(payoutUSD, neededUSD).toFixed(6));
  }
  return payoutUSD;
}

function formatPriceUSDToDisplay(amountUSD, currency) {
  let loc = document.documentElement.lang || 'en';
  const cur = currency || state.currency || 'USD';
  let val = convertFromUSD(amountUSD, cur);
  const isHuf = cur === 'HUF';
  // Clamp tiny positive values to displayable min unit per currency
  const minUnit = isHuf ? 0.1 : 0.001; // HUF: 0.1 Ft, EUR/USD: 0.001
  const isTinyPositive = val > 0 && val < minUnit;
  if (isTinyPositive) val = minUnit;
  if (isHuf) {
    const n = Number(val);
    if (!isFinite(n)) return '0 Ft';
    if (n > 0 && n < 0.1) return '0.1 Ft';
    const rounded = isTinyPositive ? Math.round(n * 10) / 10 : Math.round(n);
    const parts = String(rounded).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    return `${parts.join('.')} Ft`;
  }
  try {
    const opts = { style: 'currency', currency: cur, minimumFractionDigits: 3, maximumFractionDigits: 3 };
    return new Intl.NumberFormat(loc, opts).format(val);
  } catch {
    const n = Number(val);
    if (!isFinite(n)) {
      const sym = cur === 'EUR' ? '€' : (cur === 'HUF' ? 'Ft' : '$');
      return `${sym}0`;
    }
    const sym = cur === 'EUR' ? '€' : '$';
    return `${sym}${(n > 0 && n < 0.001 ? 0.001 : n).toFixed(3)}`;
  }
}

function formatPrice(v) { // kept for compatibility; now interprets v as USD amount
  return formatPriceUSDToDisplay(v, state.currency);
}

// Gems formatter: integer with space-grouped thousands (e.g., 1 000; 10 000; 1 000 000)
function formatGems(amount){
  const n = Math.trunc(Number(amount)||0);
  const sign = n < 0 ? '-' : '';
  const abs = Math.abs(n);
  const s = String(abs).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  return sign + s;
}
try { window.formatGems = formatGems; } catch {}

// Pricing rules
const SALE_MIN_USD = 0.001; // base min quick sell payout
const SALE_MAX_USD = 0.005; // base max quick sell payout
const SPIN_WIN_CHANCE = 0.10; // 10% default winning chance
// Micro-amount formatter for very small USD/EUR values (0.001–0.005 USD)
function formatPriceMicroUSDToDisplay(amountUSD){
  const cur = state.currency || 'USD';
  // For HUF keep default formatting (usually 0 Ft for micro-USD amounts)
  if (cur === 'HUF') return formatPriceUSDToDisplay(amountUSD, cur);
  const loc = document.documentElement.lang || 'en';
  const val = convertFromUSD(amountUSD, cur);
  try {
    return new Intl.NumberFormat(loc, { style:'currency', currency: cur, minimumFractionDigits: 3, maximumFractionDigits: 3 }).format(val);
  } catch {
    const n = Number(val);
    if (!isFinite(n)) return formatPriceUSDToDisplay(amountUSD, cur);
    const sym = cur === 'EUR' ? '€' : '$';
    return `${sym}${n.toFixed(3)}`;
  }
}
function getQuickSellMultiplier(level){
  const lv = Math.max(1, Math.min(10, Number(level)||1));
  // Scale linearly from 1.0 at Lv1 to 2.0 at Lv10
  return 1 + ((lv - 1) / 9);
}
function getSalePriceUSD(/* baseUSD */) {
  // Base micro payout
  let min = SALE_MIN_USD;
  let max = SALE_MAX_USD;
  // Scale by level
  try {
    const lv = (window.state && Number(window.state.level)) || 1;
    const mul = getQuickSellMultiplier(lv);
    min *= mul; max *= mul;
  } catch {}
  const val = min + Math.random() * (max - min);
  return Number(val.toFixed(6));
}
function labelQuickSellButton(btn, baseUSD) {
  if (!btn) return;
  let payout = getSalePriceUSD(baseUSD);
  payout = adjustPayoutUSDForCurrencyMin(payout);
  btn.dataset.payoutUsd = String(payout);
  const isHu = (document.querySelector('html')?.lang || 'en') === 'hu';
  const label = (typeof window.$tKey==='function') ? $tKey('ui.quickSell') : (isHu ? 'Gyors eladás' : 'Quick Sell');
  // Use micro formatter so values like 0.001 don't collapse to 0.00
  btn.innerHTML = `<span class=\"winIconSell\" aria-hidden=\"true\">💸</span> ${label} <span class=\"ms-1 text-success\">+${formatPriceMicroUSDToDisplay(payout)}</span>`;
}

// Level helpers
function getLevelStateKey() {
  return 'level';
}
function getLevelManualStateKey() {
  return 'levelManual';
}
function readStoredLevelValue(fallbackValue) {
  const direct = readClientState(getLevelStateKey(), null);
  if (direct != null && direct !== '') return direct;
  const legacy = readClientState('guest.level', null);
  if (legacy != null && legacy !== '') return legacy;
  return fallbackValue;
}
function readStoredLevelManualFlag() {
  const direct = readClientState(getLevelManualStateKey(), null);
  if (direct != null && direct !== '') return String(direct) === '1';
  const legacy = readClientState('guest.levelManual', null);
  return String(legacy || '0') === '1';
}
function writeStoredLevel(lv){ writeClientState(getLevelStateKey(), String(lv)); }
function setManualLevelMode(enabled) {
  const val = enabled ? '1' : '0';
  writeClientState(getLevelManualStateKey(), val);
  // clean up earlier guest-prefixed keys if they exist
  writeClientState('guest.levelManual', null, { delete: true });
  if (enabled) writeClientState('guest.level', null, { delete: true });
}
function readHistory(){
  if (window.isLoggedIn) return Array.isArray(window.__serverHistory) ? window.__serverHistory : [];
  const items = readClientState('guest.history', []);
  return Array.isArray(items) ? items : [];
}
function calcLevelFromHistory(){
  const hist = readHistory();
  const opens = hist.length; // simple: total events
  const lv = Math.min(10, 1 + Math.floor(opens / 5));
  return Math.max(1, lv);
}
function refreshLevel(){
  // Allow manual override from DB-backed client state for both guests and logged-in users
  const manual = readStoredLevelManualFlag();
  if (manual) {
    const stored = Number(readStoredLevelValue(state.level || 1)) || state.level || 1;
    const lv = Math.max(1, Math.min(10, stored));
    if (lv !== state.level) { state.level = lv; writeStoredLevel(lv); }
    updateLevelDisplay();
    return;
  }
  const lv = calcLevelFromHistory();
  if (lv !== state.level) { state.level = lv; writeStoredLevel(lv); updateLevelDisplay(); }
}
function updateLevelDisplay(){
  const el = document.getElementById('playerLevel');
  const pill = document.getElementById('levelPill');
  const icon = document.getElementById('playerRankIcon');
  if (el) {
    const r = getRankInfo(state.level);
    el.textContent = `${r.name} (Lv ${state.level}/10)`;
  }
  if (pill) {
    const all = ['rank-recruit','rank-private','rank-corporal','rank-sergeant','rank-staff','rank-warrant','rank-lieutenant','rank-captain','rank-major','rank-general'];
    all.forEach(c => pill.classList.remove(c));
    const r = getRankInfo(state.level);
    pill.classList.add(r.cls);
    if (icon) {
      // Fallback ikon a ranghoz, ha CSS ::before nem elég
      const iconMap = {
        'rank-recruit':'☆', 'rank-private':'★', 'rank-corporal':'★', 'rank-sergeant':'★★', 'rank-staff':'★★',
        'rank-warrant':'★★★', 'rank-lieutenant':'✪', 'rank-captain':'✪', 'rank-major':'✪✪', 'rank-general':'✪✪✪'
      };
      icon.textContent = iconMap[r.cls] || '★';
    }
  }
}

// Rank mapping for levels (1..10)
function getRankInfo(level){
  const lv = Math.max(1, Math.min(10, Number(level)||1));
  const map = [
    { name:'Recruit', cls:'rank-recruit' },
    { name:'Private', cls:'rank-private' },
    { name:'Corporal', cls:'rank-corporal' },
    { name:'Sergeant', cls:'rank-sergeant' },
    { name:'Staff Sgt', cls:'rank-staff' },
    { name:'Warrant Off.', cls:'rank-warrant' },
    { name:'Lieutenant', cls:'rank-lieutenant' },
    { name:'Captain', cls:'rank-captain' },
    { name:'Major', cls:'rank-major' },
    { name:'General', cls:'rank-general' },
  ];
  return map[lv-1];
}

function normalizeCasePrices() {
  const clamp = (p) => Math.min(1000, Math.max(1, Number(p) || 1));
  if (Array.isArray(state.cases)) state.cases = state.cases.map(c => ({ ...c, price: clamp(c.price) }));
  if (Array.isArray(state.communityCases)) state.communityCases = state.communityCases.map(c => ({ ...c, price: clamp(c.price) }));
  if (Array.isArray(state.featuredCases)) state.featuredCases = state.featuredCases.map(c => ({ ...c, price: clamp(c.price) }));
  if (Array.isArray(state.eventCases)) state.eventCases = state.eventCases.map(c => ({ ...c, price: clamp(c.price) }));
}
function slugify(s) { return String(s).toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,''); }
function goToCase(title) { const slug = slugify(title); window.location.href = `/index.php?page=case&slug=${encodeURIComponent(slug)}`; }

// Level gating rules per case
function getCaseRequiredLevel(c){
  const explicit = Number(c?.required_level || 0);
  if (explicit > 0) return Math.max(1, Math.min(10, explicit));
  let req = 1;
  const risk = (c.risk||'low').toLowerCase();
  if (risk === 'medium') req = Math.max(req, 3);
  else if (risk === 'high') req = Math.max(req, 5);
  else if (risk === 'very-high') req = Math.max(req, 8);
  if ((c.tag||'').toLowerCase() === 'limited') req = Math.max(req, 5);
  return req;
}

// Map a numeric level (1..10) to header rank-* class to reuse colors
function getRankClassForLevel(level){
  const lv = Math.max(1, Math.min(10, Number(level)||1));
  const rankClasses = ['rank-recruit','rank-private','rank-corporal','rank-sergeant','rank-staff','rank-warrant','rank-lieutenant','rank-captain','rank-major','rank-general'];
  return rankClasses[lv-1];
}

function tKeyOrFallback(key, fallback, repl) {
  try {
    if (typeof window.$tKey === 'function') return window.$tKey(key, repl || {});
  } catch {}
  let text = fallback;
  if (repl && typeof repl === 'object') {
    Object.keys(repl).forEach((k) => {
      text = String(text).replace(new RegExp('\\{'+k+'\\}', 'g'), String(repl[k]));
    });
  }
  return text;
}

function getCaseRiskLabel(risk) {
  const normalized = String(risk || 'low').toLowerCase();
  if (normalized === 'medium') return tKeyOrFallback('cases.risk.medium', 'Medium');
  if (normalized === 'high') return tKeyOrFallback('cases.risk.high', 'High');
  if (normalized === 'very-high' || normalized === 'veryhigh') return tKeyOrFallback('cases.risk.veryHigh', 'Very High');
  return tKeyOrFallback('cases.risk.low', 'Low');
}

function getCaseTagLabel(tag) {
  const normalized = String(tag || '').toLowerCase();
  if (normalized === 'limited') return tKeyOrFallback('tag.limited', 'Limited');
  if (normalized === 'hot') return tKeyOrFallback('tag.hot', 'Hot');
  if (normalized === 'starter') return tKeyOrFallback('tag.starter', 'Starter');
  return tag || '';
}

function getCaseLevelText(level) {
  return tKeyOrFallback('cases.levelShort', 'Lv {level}', { level: String(level) });
}

function getCaseNeedsLevelText(level) {
  return tKeyOrFallback('cases.requiresLevel', 'Requires Lv {level}', { level: String(level) });
}

function getCaseLevelHintText(level) {
  return tKeyOrFallback('cases.levelRequiredLabel', 'Level required: {level}', { level: String(level) });
}

function getCaseRiskTooltipText(label) {
  return tKeyOrFallback('cases.riskLabel', 'Risk: {risk}', { risk: String(label) });
}

let heroSwiper = null;
let communitySwiper = null;
let featuredSwiper = null;

function initHeroSwiper() {
  if (!window.Swiper || !document.querySelector('.heroSwiper') || heroSwiper) return;
  heroSwiper = new Swiper('.heroSwiper', {
    loop: true,
    autoplay: { delay: 3000 },
    pagination: { el: '.swiper-pagination', clickable: true }
  });
}

function initCommunitySwiper() {
  if (!window.Swiper || !document.querySelector('.carouselSwiper')) return;
  try {
    if (communitySwiper) communitySwiper.destroy(true, true);
  } catch {}
  const navPrev = document.querySelector('.carouselPrev');
  const navNext = document.querySelector('.carouselNext');
  communitySwiper = new Swiper('.carouselSwiper', {
    slidesPerView: 1.2,
    spaceBetween: 12,
    breakpoints: { 576: { slidesPerView: 2.2 }, 768: { slidesPerView: 3.2 }, 992: { slidesPerView: 4.2 } },
    loop: true,
    autoplay: { delay: 2500, disableOnInteraction: false },
    navigation: navPrev && navNext ? { prevEl: navPrev, nextEl: navNext } : undefined,
    observer: true,
    observeParents: true
  });
}

function initFeaturedSwiper() {
  if (!window.Swiper || !document.querySelector('.featuredSwiper')) return;
  try {
    if (featuredSwiper) featuredSwiper.destroy(true, true);
  } catch {}
  const fPrev = document.querySelector('.featuredPrev');
  const fNext = document.querySelector('.featuredNext');
  featuredSwiper = new Swiper('.featuredSwiper', {
    slidesPerView: 1,
    spaceBetween: 12,
    loop: true,
    autoplay: { delay: 3500, disableOnInteraction: false },
    navigation: fPrev && fNext ? { prevEl: fPrev, nextEl: fNext } : undefined,
    observer: true,
    observeParents: true
  });
}

function initSwipers() {
  initHeroSwiper();
  initCommunitySwiper();
  initFeaturedSwiper();
}

// Reveal on view (IntersectionObserver)
let revealObserver = null;
function initOnViewReveal() {
  if (revealObserver) return;
  const opts = { threshold: 0.1, rootMargin: '0px 0px -10% 0px' };
  revealObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('show');
        obs.unobserve(e.target);
      }
    });
  }, opts);
  registerReveal(document);
}

function registerReveal(root) {
  if (!revealObserver) return;
  const scope = root instanceof Element ? root : document;
  // include already-marked `.reveal` elements too, so they also get observed
  const targets = scope.querySelectorAll('.reveal, .sectionFrame, .card, .dropItem, .bannerCard, .spinnerDemo, .eventHeader');
  targets.forEach((el, i) => {
    // ensure `.reveal` is present for CSS transition
    if (!el.classList.contains('reveal')) {
      el.classList.add('reveal');
    }
    // if not yet shown, observe it and set a small stagger
    if (!el.classList.contains('show')) {
      if (!el.style.transitionDelay) el.style.transitionDelay = `${Math.min(i * 40, 240)}ms`;
      revealObserver.observe(el);
    }
  });
}

// PrettySelect helper (reuse across pages)
function initPrettySelect(root){
  const scope = root instanceof Element ? root : document;
  scope.querySelectorAll('.prettySelect').forEach((wrap)=>{
    if (wrap.dataset.psBound === '1') return;
    const select = wrap.querySelector('select');
    const display = wrap.querySelector('.selectDisplay');
    const panel = wrap.querySelector('.dropdownPanel');
    if (!select || !display || !panel) return;
    const sync = () => { try { display.textContent = select.options[select.selectedIndex].text; } catch {} };
    // find nearest card/sectionFrame to raise stacking when open
    const findElevateNode = () => {
      let n = wrap.parentElement;
      while (n) {
        if (n.classList && (n.classList.contains('card') || n.classList.contains('sectionFrame'))) return n;
        n = n.parentElement;
      }
      return null;
    };
    const elevateNode = findElevateNode();
    const open = () => {
      panel.innerHTML = '';
      [...select.options].forEach((opt) => {
        const item = document.createElement('div');
        item.className = 'dropdownItem' + (opt.selected ? ' active' : '');
        item.textContent = opt.text;
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          select.value = opt.value; select.dispatchEvent(new Event('change')); close();
        });
        panel.appendChild(item);
      });
      wrap.classList.add('open');
      if (elevateNode) {
        elevateNode.dataset.prevZ = elevateNode.style.zIndex || '';
        elevateNode.style.zIndex = '1090';
        // avoid clipping by hidden overflow
        if (getComputedStyle(elevateNode).overflow !== 'visible') {
          elevateNode.dataset.prevOverflow = elevateNode.style.overflow || '';
          elevateNode.style.overflow = 'visible';
        }
      }
    };
    const close = () => {
      wrap.classList.remove('open');
      if (elevateNode) {
        if (elevateNode.dataset.prevZ !== undefined) elevateNode.style.zIndex = elevateNode.dataset.prevZ;
        if (elevateNode.dataset.prevOverflow !== undefined) elevateNode.style.overflow = elevateNode.dataset.prevOverflow;
        delete elevateNode.dataset.prevZ; delete elevateNode.dataset.prevOverflow;
      }
    };
    wrap.addEventListener('click', (e) => { e.stopPropagation(); wrap.classList.contains('open') ? close() : open(); });
    document.addEventListener('click', close);
    select.addEventListener('change', sync);
    select.addEventListener('mousedown', (e) => { e.preventDefault(); });
    select.addEventListener('click', (e) => { e.preventDefault(); });
    sync();
    wrap.dataset.psBound = '1';
  });
}
try { window.initPrettySelect = initPrettySelect; } catch {}

// i18n refresh no-ops: translations now fully DB-driven
function i18nRefresh(){
  // server-side $t and window.$tKey() handle translations
}

// Live Drop (4 slot, falling + shift-left)
let liveDropIntervalId = null;
let liveDropPaused = false;
let liveDropVisible = [];
let liveDropFeedIndex = 0;

function goToCaseSlug(slug) {
  if (!slug) return;
  window.location.href = `/index.php?page=case&slug=${encodeURIComponent(slug)}`;
}

function ensureDropHasSlug(drop) {
  // Prefer existing slug, else derive from title/name
  if (drop.slug) return drop;
  const title = drop.title || drop.caseTitle || drop.name || '';
  return { ...drop, slug: slugify(title) };
}

function createDropItemEl(d, idxForWow = 0) {
  const el = document.createElement('div');
  const isWow = idxForWow < 3; // first few highlighted
  el.className = 'dropItem' + (isWow ? ' wow' : '');
  const title = d.title || d.caseTitle || d.name;
  const slug = d.slug || slugify(title);
  const wowLabel = (typeof window.$tKey === 'function')
    ? window.$tKey('liveDrop.wowLabel')
    : ((document.querySelector('html')?.lang || 'en') === 'hu' ? 'WOW NYEREMÉNY' : 'WOW DROP');
  el.setAttribute('role', 'button');
  el.setAttribute('tabindex', '0');
  el.dataset.slug = slug;
  el.addEventListener('click', () => goToCaseSlug(slug));
  el.addEventListener('keypress', (e) => { if (e.key === 'Enter') goToCaseSlug(slug); });
  el.innerHTML = `
    ${isWow ? `<span class="wowLabel">${wowLabel}</span>` : ''}
    <span class="thumb"></span>
    <span class="name">${title}</span>
    <span class="value">${formatPriceUSDToDisplay(d.value || 0)}</span>`;
  return el;
}

function seedLiveDropsFromCases() {
  if (Array.isArray(state.spinnerProducts) && state.spinnerProducts.length > 0) {
    state.drops = state.spinnerProducts.slice(0, 8).map((p) => ({ title: p.title, value: Number(p.value || 0), slug: slugify(p.title) }));
    try { window.state = state; } catch {}
    return;
  }
  // If drops are missing slug or not aligned to cases, seed from cases for reliable navigation
  const needSeed = !Array.isArray(state.drops) || state.drops.length === 0 || !state.drops.every(d => d.slug);
  if (!needSeed && state.drops.length >= 4) return;
  const base = (state.cases && state.cases.length) ? state.cases : [];
  if (!base.length) return;
  // Take first 5 cases and generate demo values
  state.drops = base.slice(0, 5).map((c) => ({ title: c.title, value: Number((c.price * (1 + Math.random())).toFixed(2)), slug: slugify(c.title) }));
  // Expose state globally for inline scripts (e.g., case page)
  try { window.state = state; } catch {}
}

function buildLiveDropSlots() {
  const ticker = document.getElementById('liveDropTicker');
  if (!ticker) return null;
  ticker.innerHTML = '';
    // update open buttons enable/disable state
    try { updateOpenButtonsState(); } catch {}
  ticker.classList.add('slotsMode');
  ticker.style.position = 'relative';
  const overlay = document.createElement('div');
  overlay.className = 'dropOverlay';
  const track = document.createElement('div');
  track.className = 'slotsTrack';

  // initialize visible with up to 4
  liveDropVisible = [];
  const initial = state.drops.slice(0, 4);
  initial.forEach((d, i) => {
    const el = createDropItemEl(ensureDropHasSlug(d), i);
    track.appendChild(el);
    liveDropVisible.push(d);
  });
  ticker.appendChild(track);
  ticker.appendChild(overlay);

  // hover pause
  if (!ticker.dataset.hoverBound) {
    ticker.addEventListener('mouseenter', () => { liveDropPaused = true; });
    ticker.addEventListener('mouseleave', () => { liveDropPaused = false; });
    ticker.dataset.hoverBound = '1';
  }
  return { ticker, track, overlay };
}

function getNextDrop() {
  // Rotate through available drops or synthesize from cases
  if (!state.drops || state.drops.length === 0) seedLiveDropsFromCases();
  const pool = state.drops;
  if (!pool || pool.length === 0) return null;
  const d = pool[liveDropFeedIndex % pool.length];
  liveDropFeedIndex = (liveDropFeedIndex + 1) % Math.max(1, pool.length);
  return ensureDropHasSlug(d);
}

function animateDropAndShift() {
  if (!state.liveDropEnabled || liveDropPaused) return; // skip cycle
  const ticker = document.getElementById('liveDropTicker');
  if (!ticker) return;
  const track = ticker.querySelector('.slotsTrack');
  const overlay = ticker.querySelector('.dropOverlay');
  if (!track || !overlay) return;

  const next = getNextDrop();
  if (!next) return;

    // update buttons state after render
    try { updateOpenButtonsState(); } catch {}
  // Create falling overlay item
  const fallEl = createDropItemEl(next, 0);
  fallEl.classList.add('falling');
  overlay.appendChild(fallEl);

  // Position overlay at right edge, centered vertically relative to track
  const trackRect = track.getBoundingClientRect();
  const tickerRect = ticker.getBoundingClientRect();
  const rightOffset = 12; // approximate padding
  fallEl.style.position = 'absolute';
  fallEl.style.right = rightOffset + 'px';
  fallEl.style.top = '-80px';

  // Trigger fall animation
  requestAnimationFrame(() => {
    fallEl.classList.add('fallIn');
  });

  // After fall completes, shift left
  const fallDuration = 320; // ms
  const shiftDuration = 420; // ms
  setTimeout(() => {
    // Measure shift amount = width of first child + gap
    if (track.children.length === 0) return;
    const first = track.children[0];
    const firstRect = first.getBoundingClientRect();
    let shiftPx = firstRect.width;
    const styles = getComputedStyle(track);
    const gap = parseFloat(styles.gap || styles.columnGap || '0');
    shiftPx += isNaN(gap) ? 0 : gap;

  track.style.transition = `transform ${shiftDuration}ms cubic-bezier(.22,.61,.36,1)`;
  track.style.transform = `translateY(-50%) translateX(-${shiftPx}px)`;

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      // finalize DOM: remove first, reset transform, append new static item
  track.style.transition = '';
  track.style.transform = 'translateY(-50%)';
    try { updateOpenButtonsState(); } catch {}
      if (track.firstElementChild) track.removeChild(track.firstElementChild);
      const staticEl = createDropItemEl(next, 0);
      track.appendChild(staticEl);
      // cleanup overlay
      fallEl.remove();
    };
    track.addEventListener('transitionend', onEnd);
  }, fallDuration);
}

function startLiveDropSlotsLoop() {
  stopLiveDropSlotsLoop();
  // initial delay to let layout settle
  liveDropIntervalId = setInterval(() => {
    if (!state.liveDropEnabled) return;
    animateDropAndShift();
  }, 2300);
}

function stopLiveDropSlotsLoop() {
  if (liveDropIntervalId) {
    clearInterval(liveDropIntervalId);
    liveDropIntervalId = null;
  }
}

function renderLiveDrop() {
  seedLiveDropsFromCases();
  buildLiveDropSlots();
  startLiveDropSlotsLoop();
}
    try { updateOpenButtonsState(); } catch {}
    try { i18nRefresh(); } catch {}

function renderCases() {
  const grid = document.getElementById('casesGrid');
  if (!grid) return;
  const priceFilter = document.getElementById('priceFilter').value;
  const tagFilter = document.getElementById('tagFilter').value;
  const min = 0; const max = 100000;

  const filtered = state.cases.filter((c) => {
    const priceBand = c.price < 5 ? 'low' : c.price < 15 ? 'mid' : 'high';
     const priceOk = (priceFilter === 'all' || priceFilter === priceBand) && (c.price >= min && c.price <= max);
    const tagOk = tagFilter === 'all' || tagFilter === c.tag;
    return priceOk && tagOk;
  });

  grid.innerHTML = '';
  filtered.forEach((c) => {
    const col = document.createElement('div');
    col.className = 'col-6 col-md-4 col-lg-2';
    const riskLabel = getCaseRiskLabel(c.risk);
    const riskClassName = c.risk === 'low' ? 'low' : c.risk === 'medium' ? 'mid' : c.risk === 'high' ? 'high' : 'veryhigh';
    const riskClass = riskClassName === 'low' ? '' : (' ' + riskClassName);
    const reqLevel = getCaseRequiredLevel(c);
    const tagLabel = getCaseTagLabel(c.tag);
    col.innerHTML = `
      <div class="card h-100 p-2">
        <div class="caseThumb mb-2 position-relative">
          <span class="infoIconWrap hasTooltip">
            <span class="infoIcon">i</span>
            <span class="infoTooltip">${c.title} • ${tagLabel} • ${formatPriceUSDToDisplay(c.price)}</span>
          </span>
          <img src="${c.img}" alt="${c.title}" loading="lazy">
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <div>
              <div class="caseTitle">${c.title}</div>
            <div class="d-flex gap-1 align-items-center flex-wrap">
              <div class="tag">${tagLabel}</div>
              <div class="riskBadge ${riskClassName}">${riskLabel}</div>
              <div class="levelBadge ${getRankClassForLevel(reqLevel)}">${getCaseLevelText(reqLevel)}</div>
            </div>
          </div>
          <div class="text-end">
            <div class="priceTag">${formatPriceUSDToDisplay(c.price)}</div>
            <div class="small text-white">${getCaseLevelHintText(reqLevel)}</div>
          </div>
        </div>
        <div class="mt-2 hasTooltip">
          <div class="riskBar">
            <span class="riskMarker${riskClass}" style="left: ${c.risk === 'low' ? 7 : c.risk === 'medium' ? 35 : c.risk === 'high' ? 63 : 90}%"></span>
            <span class="riskHandle"></span>
          </div>
          <div class="tooltipBox">${getCaseRiskTooltipText(riskLabel)}</div>
        </div>
          <button class="btn btnPrimary mt-2 w-100" data-title="${c.title}" data-price="${c.price}" data-req-level="${reqLevel}" title="${getCaseNeedsLevelText(reqLevel)}" onclick="handleOpenCaseClick(this)">${tKeyOrFallback('ui.open', 'Open')}</button>
      </div>`;
    grid.appendChild(col);
  });
  if (typeof registerReveal === 'function') registerReveal(grid);
  try { updateOpenButtonsState(); } catch {}
  try { i18nRefresh(); } catch {}
}

function renderEventCases() {
  const grid = document.getElementById('eventCasesGrid');
  const header = document.getElementById('eventHeader');
  if (!grid) return;
  grid.innerHTML = '';
  const upcomingEvents = (Array.isArray(state.homeEvents) ? state.homeEvents : [])
    .map((eventItem) => {
      const start = eventItem.start_at ? new Date(String(eventItem.start_at).replace(' ', 'T')) : (eventItem.date ? new Date(`${eventItem.date}T00:00:00`) : null);
      const end = eventItem.end_at ? new Date(String(eventItem.end_at).replace(' ', 'T')) : start;
      return { ...eventItem, _start: start, _end: end };
    })
    .filter((eventItem) => eventItem._end && !Number.isNaN(eventItem._end.getTime()) && eventItem._end.getTime() >= Date.now())
    .sort((a, b) => (a._start?.getTime() || 0) - (b._start?.getTime() || 0));
  if (header && upcomingEvents[0]?._start) {
    header.setAttribute('data-deadline', upcomingEvents[0]._start.toISOString());
  }
  if (!upcomingEvents.length) {
    grid.innerHTML = `<div class="col-12"><div class="text-white small">${typeof window.___ === 'function' ? window.___('No events to show') : 'No events to show'}</div></div>`;
    return;
  }
  const eventCases = Array.isArray(state.eventCases) ? state.eventCases : [];
  const activeEvent = upcomingEvents.find((eventItem) => eventItem._start && eventItem._end && eventItem._start.getTime() <= Date.now() && eventItem._end.getTime() >= Date.now()) || null;
  const nearestEvent = activeEvent || upcomingEvents[0] || null;
  const dateFmt = new Intl.DateTimeFormat(document.documentElement.lang || 'hu', { year: 'numeric', month: 'short', day: 'numeric' });
  const timeFmt = new Intl.DateTimeFormat(document.documentElement.lang || 'hu', { hour: '2-digit', minute: '2-digit' });
  if (eventCases.length) {
    eventCases.forEach((caseItem) => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-xl-3';
      const reqLevel = getCaseRequiredLevel(caseItem);
      const tagLabel = getCaseTagLabel(caseItem.tag);
      const isActive = !!(nearestEvent && activeEvent);
      const start = nearestEvent?._start || null;
      const end = nearestEvent?._end || start;
      const timeRange = start && end ? `${dateFmt.format(start)} • ${timeFmt.format(start)} - ${timeFmt.format(end)}` : '';
      const openLabel = isActive ? (typeof window.___ === 'function' ? window.___('Open') : 'Open') : (typeof window.___ === 'function' ? window.___('Starts soon') : 'Starts soon');
      const openAttrs = isActive
        ? `data-title="${caseItem.title}" data-price="${caseItem.price}" data-req-level="${reqLevel}" onclick="handleOpenCaseClick(this)"`
        : 'disabled';
      col.innerHTML = `
        <div class="card h-100 p-2 eventPromoCard">
          <div class="caseThumb mb-2 position-relative">
            <img src="${caseItem.img}" alt="${caseItem.title}" loading="lazy">
          </div>
          <div class="caseTitle text-white">${caseItem.title}</div>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 mb-2">
            <div class="tag">${tagLabel}</div>
            <div class="priceTag">${formatPriceUSDToDisplay(caseItem.price || 0)}</div>
          </div>
          <div class="small text-white mb-2">${nearestEvent?.title || (typeof window.___ === 'function' ? window.___('Event') : 'Event')}</div>
          <div class="small text-white-50 mb-3">${timeRange}</div>
          <div class="d-flex gap-2 mt-auto">
            <a href="/index.php?page=case&slug=${encodeURIComponent(caseItem.slug || slugifyTitle(caseItem.title || ''))}" class="btn btnOutline btn-sm flex-fill">${typeof window.___ === 'function' ? window.___('View') : 'View'}</a>
            <button class="btn btnPrimary btn-sm flex-fill" ${openAttrs}>${openLabel}</button>
          </div>
        </div>`;
      grid.appendChild(col);
    });
  } else {
  upcomingEvents.forEach((eventItem) => {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-xl-4';
    const start = eventItem._start;
    const end = eventItem._end || start;
    const timeRange = start && end ? `${dateFmt.format(start)} • ${timeFmt.format(start)} - ${timeFmt.format(end)}` : '';
    col.innerHTML = `
      <div class="card h-100 p-3 eventPromoCard">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
          <div>
            <div class="caseTitle">${eventItem.title || (typeof window.___ === 'function' ? window.___('Event') : 'Event')}</div>
            <div class="tag mt-2">${timeRange}</div>
          </div>
          <div class="eventDotLarge" style="background:${eventItem.color || '#7c4dff'}"></div>
        </div>
        <div class="small text-white mb-3">${eventItem.description || ''}</div>
        <div class="d-flex align-items-center justify-content-between mt-auto gap-2">
          <div class="small text-white">${start ? timeFmt.format(start) : ''}</div>
          <div class="d-flex gap-2">
            <a href="${eventItem.href || '/index.php?page=events'}" class="btn btnOutline btn-sm">${typeof window.___ === 'function' ? window.___('View') : 'View'}</a>
            <a href="/index.php?page=events" class="btn btnPrimary btn-sm">${typeof window.___ === 'function' ? window.___('Open') : 'Open'}</a>
          </div>
        </div>
      </div>`;
    grid.appendChild(col);
  });
  }
  if (typeof registerReveal === 'function') registerReveal(grid);
  try { updateOpenButtonsState(); } catch {}
  try { i18nRefresh(); } catch {}
}

function slugifyTitle(value) {
  return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

// Case open flow helpers
function showInsufficientModal() {
  const el = document.getElementById('insufficientModal');
  if (!el || !window.bootstrap) {
    // fallback notification via SweetAlert/alert
  notify('warning', (typeof window.$tKey==='function') ? $tKey('wallet.insufficient') : 'Insufficient balance');
    return;
  }
  bootstrap.Modal.getOrCreateInstance(el).show();
}
function handleOpenCaseClick(btn) {
  const title = btn.getAttribute('data-title');
  const price = Number(btn.getAttribute('data-price')) || 0;
  const reqLv = Number(btn.getAttribute('data-req-level')) || 1;
  if ((state.level||1) < reqLv) {
    const msg = (typeof window.$tKey==='function') ? $tKey('cases.needLevel', { level: String(reqLv) }) : 'You need at least Lv {level} for this case';
    notify('warning', msg, { level: String(reqLv) });
    return;
  }
  // Allow opening if either USD balance covers price or gems cover gemCost (1000 gems per $1, rounded to 10)
  const gemCost = Math.max(0, Math.round(price * 1000 / 10) * 10);
  const canPay = (state.balance >= price) || ((Number(state.gems)||0) >= gemCost);
  if (!canPay) { showInsufficientModal(); return; }
  goToCase(title);
}

// Case open result modal control
function showCaseOpenModal(result, items=[]) {
  const el = document.getElementById('caseOpenModal');
  if (!el || !window.bootstrap) return;
  const nameEl = document.getElementById('caseOpenResultName');
  const couponEl = document.getElementById('caseOpenCoupon');
  const list = document.getElementById('caseItemsList');
  if (nameEl) nameEl.textContent = result?.name || '—';
  if (couponEl) couponEl.textContent = result?.coupon || '—';
  if (list) {
    list.innerHTML = '';
    items.slice(0, 12).forEach((it) => {
      const tag = document.createElement('span');
      tag.className = 'badge badgePrimary';
      tag.textContent = it.name || it.title || 'Item';
      list.appendChild(tag);
    });
  }
  const inst = bootstrap.Modal.getOrCreateInstance(el);
  // Bind actions
  const btnClaim = document.getElementById('btnClaimPrize');
  const btnSell = document.getElementById('btnSellPrize');
  if (btnClaim) btnClaim.onclick = () => {
    if (!window.isLoggedIn) { try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {} return; }
    saveHistoryEntry(result, 'claimed');
    try { addToInventory(result?.name || 'Item', Number(result?.value || 0)); } catch {}
    if (window.isLoggedIn) {
      try {
        const priceUSD = Number(result?.priceUSD||0) || 0;
        const priceGems = Number(result?.priceGems||0) || 0;
        fetch('/index.php?page=api&action=recordCaseHistory', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ...result, status:'claimed', priceUSD, priceGems }) })
          .then(r=> r.ok ? r : r.json().then(e=>Promise.reject(e)))
          .then(()=> {
            // If this was the paid first spin (USD or gems), mark paid locally
            if (typeof window.__caseMarkPaid === 'function' && (priceUSD > 0 || priceGems > 0)) { try { window.__caseMarkPaid(); } catch {} }
            // No balance change for claimed, but if priceUSD>0 we deducted: refresh wallet to reflect the deduction
            if (priceUSD > 0) return fetch('/index.php?page=api&action=walletBalance');
            // If gems were used, refresh gems balance
            if (priceGems > 0) return fetch('/index.php?page=api&action=gemsBalance');
          })
          .then(r=> r && r.ok ? r.json() : null)
          .then(d=>{
            if (d && d.wallet) {
              state.balance = Number(d.wallet.balance_cents||0)/100; updateBalanceDisplay();
            } else if (d && typeof d.balance === 'number') {
              state.gems = d.balance; updateGemsDisplay();
            }
          })
          .catch((e)=>{
            if (e && e.error === 'insufficient_gems') {
              notify('warning', (typeof window.$tKey==='function') ? $tKey('wallet.insufficientGems') : 'Insufficient gems');
            } else {
              notify('error', (typeof window.$tKey==='function') ? $tKey('error.generic') : 'Operation failed');
            }
          });
      } catch {}
    }
    inst.hide();
  };
  if (btnSell) {
    // pre-label with computed payout
    labelQuickSellButton(btnSell, Number(result?.value || 0));
    btnSell.onclick = () => {
      if (!window.isLoggedIn) { try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {} return; }
      const vStr = btnSell.dataset.payoutUsd;
      let payout = vStr ? Number(vStr) : getSalePriceUSD(Number(result?.value || 0));
      payout = adjustPayoutUSDForCurrencyMin(payout);
      if (window.isLoggedIn) {
        const priceUSD = Number(result?.priceUSD||0) || 0;
        const priceGems = Number(result?.priceGems||0) || 0;
        fetch('/index.php?page=api&action=recordCaseHistory', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ...result, status:'sold', soldAmountUSD: payout, priceUSD, priceGems }) })
          .then(r=> r.ok ? r : r.json().then(e=>Promise.reject(e)))
          .then(()=> {
            // If first paid spin, mark paid after server accepted (USD or gems)
            if (typeof window.__caseMarkPaid === 'function' && (priceUSD > 0 || priceGems > 0)) { try { window.__caseMarkPaid(); } catch {} }
            // Refresh balances depending on what changed
            if (priceUSD > 0) return fetch('/index.php?page=api&action=walletBalance');
            if (priceGems > 0) return fetch('/index.php?page=api&action=gemsBalance');
          })
          .then(r=>r.ok?r.json():Promise.reject())
          .then(d=>{
            if (d && d.wallet) {
              const wm = (typeof d.wallet.balance_milli !== 'undefined' && d.wallet.balance_milli !== null) ? Number(d.wallet.balance_milli)/1000 : Number(d.wallet.balance_cents||0)/100;
              state.balance = wm; updateBalanceDisplay();
            } else if (d && typeof d.balance === 'number') {
              state.gems = d.balance; updateGemsDisplay();
            }
          })
          .catch((e)=>{
            if (e && e.error === 'insufficient_gems') {
              notify('warning', (typeof window.$tKey==='function') ? $tKey('wallet.insufficientGems') : 'Insufficient gems');
            } else {
              notify('error', (typeof window.$tKey==='function') ? $tKey('error.generic') : 'Operation failed');
            }
          });
      } else {
        // Guests cannot receive money
        try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {}
        return;
      }
      saveHistoryEntry({ ...result, soldAmountUSD: payout }, 'sold');
      inst.hide();
    };
  }
  inst.show();
}

function randomCoupon(prefix='PS') {
  const s = Math.random().toString(36).slice(2, 8).toUpperCase();
  return `${prefix}-${s}`;
}
function saveHistoryEntry(entry, status) {
  if (window.isLoggedIn) {
    try {
      const current = Array.isArray(window.__serverHistory) ? window.__serverHistory : [];
      window.__serverHistory = [{ ...entry, status, ts: Date.now() }, ...current].slice(0, 1000);
    } catch {}
    try { refreshLevel(); } catch {}
    return;
  }
  const arr = readHistory();
  arr.unshift({ ...entry, status, ts: Date.now() });
  writeClientState('guest.history', arr.slice(0, 100));
  // Update level when history changes
  try { refreshLevel(); } catch {}
}

// Reconcile inventory from history (claimed items)
function reconcileInventoryFromHistory(){
  if (window.isLoggedIn) return;
  try {
    const hist = readHistory();
    const inv = readInventory();
    const hasLike = (name, ts) => inv.some(it => it.name === name && Math.abs((it.histTs||it.ts||0) - (ts||0)) < 10_000);
    hist.filter(h => h && h.status === 'claimed').forEach(h => {
      const nm = h.name || h.title || 'Item';
      const val = Number(h.value||0);
      const ts = Number(h.ts||0);
      if (!hasLike(nm, ts)) {
        inv.unshift({ id: ts + '-' + Math.random().toString(36).slice(2,6), name: nm, valueUSD: val, ts: Date.now(), histTs: ts });
      }
    });
    writeInventory(inv.slice(0, 500));
  } catch {}
}
// Inventory helpers (claimed items)
function readInventory(){
  if (window.isLoggedIn) return Array.isArray(window.__serverInventory) ? window.__serverInventory : [];
  const items = readClientState('guest.inventory', []);
  return Array.isArray(items) ? items : [];
}
function writeInventory(list){
  if (window.isLoggedIn) { window.__serverInventory = Array.isArray(list) ? list : []; return; }
  writeClientState('guest.inventory', Array.isArray(list) ? list : []);
}
function addToInventory(name, valueUSD){
  const list = readInventory();
  list.unshift({ id: Date.now() + '-' + Math.random().toString(36).slice(2,6), name, valueUSD: Number(valueUSD)||0, ts: Date.now() });
  writeInventory(list.slice(0, 500));
}
// Expose helpers globally
try {
  window.showCaseOpenModal = showCaseOpenModal;
  window.randomCoupon = randomCoupon;
  window.updateBalanceDisplay = updateBalanceDisplay;
  window.loadClientState = loadClientState;
  window.readClientState = readClientState;
  window.writeClientState = writeClientState;
} catch {}

function openCase(id) {
  // demo: add a new drop to live ticker
  let found = state.cases.find((c) => c.id === id);
  if (!found) found = state.eventCases.find((c) => c.id === id);
  if (!found) return;
  const mult = 1 + Math.random();
  state.drops.unshift({ title: found.title, value: Number((found.price * mult).toFixed(2)), slug: slugify(found.title) });
  if (state.drops.length > 12) state.drops.pop();
  renderLiveDrop();
}

function renderCommunityCarousel() {
  const wrap = document.getElementById('carouselSlides');
  if (!wrap) return;
  wrap.innerHTML = '';
  const items = (Array.isArray(state.communityCases) && state.communityCases.length)
    ? [...state.communityCases]
    : [...state.cases];
  items.forEach((c) => {
    const slide = document.createElement('div');
    slide.className = 'swiper-slide';
    const risk = (c.risk||'low').toLowerCase();
    const riskLabel = getCaseRiskLabel(risk);
    const riskClassName = risk === 'low' ? 'low' : (risk === 'medium' ? 'mid' : (risk === 'high' ? 'high' : 'veryhigh'));
    const tagLabel = getCaseTagLabel(c.tag);
    slide.innerHTML = `
      <div class="card h-100 p-2" role="button" tabindex="0" onclick="goToCase('${c.title.replace(/'/g, "\\'")}')">
        <div class="caseThumb mb-2 position-relative">
          <span class="infoIconWrap hasTooltip">
            <span class="infoIcon">i</span>
            <span class="infoTooltip">${c.title} • ${tagLabel} • ${formatPriceUSDToDisplay(c.price)}</span>
          </span>
            <img src="${c.img}" alt="${c.title}" loading="lazy">
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <div>
              <div class="caseTitle">${c.title}</div>
            <div class="d-flex gap-1 align-items-center flex-wrap">
              <div class="tag">${tagLabel}</div>
              <span class="riskBadge ${riskClassName}">${riskLabel}</span>
              <span class="levelBadge ${getRankClassForLevel(getCaseRequiredLevel(c))}">${getCaseLevelText(getCaseRequiredLevel(c))}</span>
            </div>
          </div>
          <div class="text-end">
            <div class="priceTag">${formatPriceUSDToDisplay(c.price)}</div>
            <div class="small text-white">${getCaseLevelHintText(getCaseRequiredLevel(c))}</div>
          </div>
        </div>
      </div>`;
    wrap.appendChild(slide);
  });
  if (typeof registerReveal === 'function') registerReveal(wrap);
  try { initCommunitySwiper(); } catch {}
  try { updateOpenButtonsState(); } catch {}
}

function renderFeaturedCarousel() {
  const wrap = document.getElementById('featuredSlides');
  if (!wrap) return;
  wrap.innerHTML = '';
  const items = (Array.isArray(state.featuredCases) && state.featuredCases.length)
    ? [...state.featuredCases]
    : [...state.cases];
  if (!items.length) return;
  // First slide: featured large card + right-side small stack
  const first = items[0];
  const slide = document.createElement('div');
  slide.className = 'swiper-slide';
  const riskF = (first.risk||'low').toLowerCase();
  const riskLabelF = getCaseRiskLabel(riskF);
  const riskClassF = riskF === 'low' ? 'low' : (riskF === 'medium' ? 'mid' : (riskF === 'high' ? 'high' : 'veryhigh'));
  const tagLabelF = getCaseTagLabel(first.tag);
  slide.innerHTML = `
    <div class="row g-3">
      <div class="col-lg-8">
          <div class="card h-100 p-3" data-title="${first.title}" onclick="goToCase(this.dataset.title)">
          <div class="caseThumb caseThumbFeaturedMain mb-3 position-relative">
            <span class="infoIconWrap hasTooltip">
              <span class="infoIcon">i</span>
              <span class="infoTooltip">${first.title} • ${tagLabelF} • ${formatPriceUSDToDisplay(first.price)}</span>
            </span>
              <img src="${first.img}" alt="${first.title}" loading="lazy">
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="caseTitle" style="font-size:1.25rem">${first.title}</div>
              <div class="d-flex gap-1 align-items-center flex-wrap"><div class="tag">${tagLabelF}</div><span class="riskBadge ${riskClassF}">${riskLabelF}</span> <span class="levelBadge ${getRankClassForLevel(getCaseRequiredLevel(first))}">${getCaseLevelText(getCaseRequiredLevel(first))}</span></div>
            </div>
            <div class="text-end" style="min-width:96px">
              <div class="priceTag" style="font-size:1.25rem">${formatPriceUSDToDisplay(first.price)}</div>
              <div class="small text-white">${getCaseLevelHintText(getCaseRequiredLevel(first))}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="row g-3">
          ${items.slice(1,7).map(s => `
            <div class="col-6">
              <div class="card p-2 h-100" data-title="${s.title}" onclick="goToCase(this.dataset.title)">
                <div class=\"caseThumb caseThumbFeaturedSub mb-2 position-relative\">\n                <span class=\"infoIconWrap hasTooltip\">\n                  <span class=\"infoIcon\">i</span>\n                  <span class=\"infoTooltip\">${s.title} • ${getCaseTagLabel(s.tag)} • ${formatPrice(s.price)}</span>\n                </span>\n                <img src=\"${s.img}\" alt=\"${s.title}\">\n              </div>
                  <div class="d-flex align-items-center justify-content-between">
                  <div>
                      <div class="caseTitle">${s.title}</div>
                    <div class=\"d-flex gap-1 align-items-center flex-wrap\"><div class=\"tag\">${getCaseTagLabel(s.tag)}</div><span class=\"riskBadge ${(s.risk||'low').toLowerCase()==='low'?'low':((s.risk||'low').toLowerCase()==='medium'?'mid':((s.risk||'low').toLowerCase()==='high'?'high':'veryhigh'))}\">${getCaseRiskLabel((s.risk||'low').toLowerCase())}</span> <span class=\"levelBadge ${getRankClassForLevel(getCaseRequiredLevel(s))}\">${getCaseLevelText(getCaseRequiredLevel(s))}</span></div>
                  </div>
                  <div class=\"text-end\"><div class=\"priceTag\">${formatPriceUSDToDisplay(s.price)}</div><div class=\"small text-white\">${getCaseLevelHintText(getCaseRequiredLevel(s))}</div></div>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>`;
  wrap.appendChild(slide);
  if (typeof registerReveal === 'function') registerReveal(wrap);
  try { initFeaturedSwiper(); } catch {}
  try { i18nRefresh(); } catch {}
  try { i18nRefresh(); } catch {}
}

function renderHomeBundles() {
  const grid = document.getElementById('bundleRewardsGrid');
  if (!grid) return;
  grid.innerHTML = '';
  const items = Array.isArray(state.homeBundles) ? state.homeBundles : [];
  if (!items.length) {
    grid.innerHTML = `<div class="col-12"><div class="text-muted small">${typeof window.___ === 'function' ? window.___('No bundles to show') : 'No bundles to show'}</div></div>`;
    return;
  }
  items.slice(0, 5).forEach((bundle) => {
    const contents = Array.isArray(bundle.contents) ? bundle.contents : [];
    const displayContents = contents.slice(0, 3);
    const extraCount = Math.max(0, contents.length - displayContents.length);
    const totalUnits = contents.reduce((sum, item) => sum + Math.max(1, Number(item && item.quantity ? item.quantity : 1)), 0) || Number(bundle.item_count || 0) || contents.length;
    const containsLabel = typeof window.___ === 'function' ? window.___('Contains') : 'Contains';
    const bundleLabel = typeof window.___ === 'function' ? window.___('Bundle') : 'Bundle';
    const productLabel = typeof window.___ === 'function' ? window.___('Product') : 'Product';
    const caseLabel = typeof window.___ === 'function' ? window.___('Case') : 'Case';
    const categoryLabel = typeof window.___ === 'function' ? window.___('Category') : 'Category';
    const moreLabel = typeof window.___ === 'function' ? window.___('More') : 'More';
    const summaryLabel = displayContents.map((item) => {
      const type = String(item && item.type ? item.type : 'product').toLowerCase();
      const quantity = Math.max(1, Number(item && item.quantity ? item.quantity : 1));
      const name = String(item && item.name ? item.name : '').trim();
      const typeLabel = type === 'bundle'
        ? bundleLabel
        : (type === 'case' ? caseLabel : (type === 'category' ? categoryLabel : productLabel));
      return `
        <span class="bundleContentChip" title="${typeLabel}: ${name}">
          <strong>${quantity}x</strong> ${name || typeLabel}
        </span>
      `;
    }).join('');
    const previewLines = displayContents.map((item) => {
      const type = String(item && item.type ? item.type : 'product').toLowerCase();
      const quantity = Math.max(1, Number(item && item.quantity ? item.quantity : 1));
      const name = String(item && item.name ? item.name : '').trim();
      const typeLabel = type === 'bundle'
        ? bundleLabel
        : (type === 'case' ? caseLabel : (type === 'category' ? categoryLabel : productLabel));
      return `<div class="bundlePreviewLine"><span>${quantity}x</span><span>${name || typeLabel}</span><small>${typeLabel}</small></div>`;
    }).join('');
    const col = document.createElement('div');
    col.className = 'col-md-6 col-xl-4 col-xxl';
    col.innerHTML = `
      <div class="card h-100 p-2 bundleRewardCard">
        <div class="caseThumb mb-2 position-relative bundleThumb">
          <span class="infoIconWrap hasTooltip">
            <span class="infoIcon">i</span>
            <span class="infoTooltip">${bundle.name || bundleLabel} • ${bundleLabel} • ${formatPriceUSDToDisplay(Number(bundle.value || 0))}</span>
          </span>
          <div class="bundleThumbInner">
            <div class="bundleThumbBadge">${bundleLabel}</div>
            <div class="bundleThumbCount">${totalUnits}x</div>
            <div class="bundleThumbPreview">
              ${previewLines || `<div class="bundlePreviewLine"><span>${Number(bundle.item_count || 0)}</span><span>${productLabel}</span><small>${bundleLabel}</small></div>`}
            </div>
          </div>
        </div>
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <div class="caseTitle">${bundle.name || 'Bundle'}</div>
            <div class="d-flex gap-1 align-items-center flex-wrap">
              <div class="tag">${bundleLabel}</div>
              <span class="riskBadge high">${totalUnits}x</span>
              <span class="levelBadge rank-recruit">${Number(bundle.item_count || contents.length || 0)} ${productLabel}</span>
            </div>
          </div>
          <div class="text-end">
            <div class="priceTag">${formatPriceUSDToDisplay(Number(bundle.value || 0))}</div>
            <div class="small text-white">${containsLabel}</div>
          </div>
        </div>
        <div class="bundleContentSummary mt-2">
          ${summaryLabel || `<span class="bundleContentChip"><strong>${Number(bundle.item_count || 0)}x</strong> ${productLabel}</span>`}
          ${extraCount > 0 ? `<span class="bundleContentChip muted">+${extraCount} ${moreLabel}</span>` : ''}
        </div>
      </div>`;
    grid.appendChild(col);
  });
}

function initFilters() {
  const price = document.getElementById('priceFilter');
  const tag = document.getElementById('tagFilter');
  if (price) price.addEventListener('change', renderCases);
  if (tag) tag.addEventListener('change', renderCases);

  // Pretty select: keep visible text in sync
  document.querySelectorAll('.prettySelect').forEach((wrap) => {
    const select = wrap.querySelector('select');
    const display = wrap.querySelector('.selectDisplay');
    const sync = () => { display.textContent = select.options[select.selectedIndex].text; };
    select.addEventListener('change', () => { 
      sync(); 
      if (document.getElementById('casesGrid')) { try { renderCases(); } catch {} }
    });
    sync();
  });

  // Custom dropdown panel (disable native select UI entirely)
  document.querySelectorAll('.prettySelect').forEach((wrap) => {
    const select = wrap.querySelector('select');
    const display = wrap.querySelector('.selectDisplay');
    const panel = wrap.querySelector('.dropdownPanel');
    const sync = () => { display.textContent = select.options[select.selectedIndex].text; };
    const open = () => {
      panel.innerHTML = '';
      [...select.options].forEach((opt) => {
        const item = document.createElement('div');
        item.className = 'dropdownItem' + (opt.selected ? ' active' : '');
        item.textContent = opt.text;
        item.addEventListener('click', () => { select.value = opt.value; select.dispatchEvent(new Event('change')); close(); });
        panel.appendChild(item);
      });
      wrap.classList.add('open');
    };
    const close = () => wrap.classList.remove('open');
    wrap.addEventListener('click', (e) => { e.stopPropagation(); wrap.classList.contains('open') ? close() : open(); });
    document.addEventListener('click', close);
    select.addEventListener('change', () => { 
      sync(); 
      if (document.getElementById('casesGrid')) { try { renderCases(); } catch {} }
    });
    // prevent accidental native open if any
    select.addEventListener('mousedown', (e) => { e.preventDefault(); });
    select.addEventListener('click', (e) => { e.preventDefault(); });
    sync();
  });
}

function initSpinner() {
  const btn = document.getElementById('spinButton');
  const btnFast = document.getElementById('spinFastToggle');
  const reel = document.getElementById('spinnerReel');
  if (!btn || !reel) return;
  let fastMode = false;
  let pendingFastContinuation = false;
  function spinDuration() { return fastMode ? 120 : 3000; }
  const NO_WIN_WEIGHT = 10;
  const AUTO_RESPIN_DURATION_MS = 900;
  // Daily quota for homepage demo spinner with 24h lock and bonus spins via collectibles
  const DAILY_LIMIT = 10;
  const SPIN_STORE_KEY = 'pi.spin.home';
  let countdownTimerId = null;
  // Server state cache
  let serverSpinState = null; // { used_today, daily_limit, lock_until, collectibles, bonus }
  async function fetchServerSpinState(){
    if (!window.isLoggedIn) { serverSpinState = null; return null; }
    try {
      const r = await fetch('/index.php?page=api&action=spinState');
      if (!r.ok) throw new Error('http');
      const d = await r.json();
      if (d && d.state) { serverSpinState = d.state; return serverSpinState; }
    } catch {}
    return null;
  }
  async function adjustServerSpin(action, amount){
    if (!window.isLoggedIn) return null;
    try {
      const r = await fetch('/index.php?page=api&action=spinAdjust', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action, amount }) });
      if (!r.ok) throw new Error('http');
      const d = await r.json();
      if (d && d.state) { serverSpinState = d.state; return serverSpinState; }
    } catch {}
    return null;
  }
  function now(){ return Date.now(); }
  function fmtRemaining(ms){ const totalSec = Math.max(0, Math.floor(ms/1000)); const h = Math.floor(totalSec/3600); const m = Math.floor((totalSec%3600)/60); const s = totalSec%60; return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`; }
  function readSpinState(){
    if (serverSpinState) return { used: serverSpinState.used_today, lockUntil: serverSpinState.lock_until ? Date.parse(serverSpinState.lock_until) : 0, collectibles: serverSpinState.collectibles, bonus: serverSpinState.bonus };
    const d = readClientState(SPIN_STORE_KEY, { used: 0, lockUntil: 0, collectibles: 0, bonus: 0 }) || {};
    if (typeof d.lockUntil === 'undefined') d.lockUntil = 0;
    if (typeof d.collectibles === 'undefined') d.collectibles = 0;
    if (typeof d.bonus === 'undefined') d.bonus = 0;
    if (typeof d.used !== 'number') d.used = 0;
    return d;
  }
  function writeSpinState(st){ if (serverSpinState) return; writeClientState(SPIN_STORE_KEY, st); }
  function isLocked(){ const st = readSpinState(); return (st.lockUntil||0) > now(); }
  function msUntilUnlock(){ const st = readSpinState(); return Math.max(0, (st.lockUntil||0) - now()); }
  function getRemaining(){ const st = readSpinState(); return Math.max(0, DAILY_LIMIT - (Number(st.used)||0)); }
  function getBonus(){ const st = readSpinState(); return Math.max(0, Number(st.bonus)||0); }
  function getCollectibles(){ const st = readSpinState(); return Math.max(0, Number(st.collectibles)||0); }
  async function consumeOneNormal(){
    if (window.isLoggedIn) {
      const s = await adjustServerSpin('consume', 1);
      return s;
    }
    const st = readSpinState();
    st.used = Math.max(0, Number(st.used)||0) + 1;
    if (st.used >= DAILY_LIMIT) { st.lockUntil = now() + 24*3600*1000; }
    writeSpinState(st);
    return st;
  }
  async function consumeBonus(){
    if (window.isLoggedIn) {
      const s = await adjustServerSpin('consume', 1);
      return s;
    }
    const st = readSpinState(); st.bonus = Math.max(0, (Number(st.bonus)||0) - 1); writeSpinState(st); return st;
  }
  async function addCollectibleAndMaybeBonus(){
    if (window.isLoggedIn) { return await adjustServerSpin('award_collectible', 1); }
    const st = readSpinState(); st.collectibles = Math.max(0, Number(st.collectibles)||0) + 1; if (st.collectibles >= 10) { st.collectibles -= 10; st.bonus = Math.max(0, Number(st.bonus)||0) + 1; } writeSpinState(st); return st;
  }
  function startCountdown(){ if (countdownTimerId) return; countdownTimerId = setInterval(()=>{ updateQuotaUI(); if (!isLocked()) { clearInterval(countdownTimerId); countdownTimerId = null; } }, 1000); }
  async function updateQuotaUI(){
    if (window.isLoggedIn && !serverSpinState) { await fetchServerSpinState(); }
    try {
      const left = getRemaining();
      const bonus = getBonus();
      const shards = getCollectibles();
      const info = document.getElementById('spinQuotaInfo');
      const hu = (document.querySelector('html')?.lang || 'en') === 'hu';
      const base = hu ? `Napi pörgetések: ${left}/${DAILY_LIMIT}` : `Daily spins: ${left}/${DAILY_LIMIT}`;
      const extra = ` • ${hu? 'Bónusz': 'Bonus'}: ${bonus} • ${hu? 'Ikonok': 'Shards'}: ${shards}/10`;
      const locked = isLocked();
      const cd = locked ? (hu ? ` • Újra: ${fmtRemaining(msUntilUnlock())}` : ` • Next: ${fmtRemaining(msUntilUnlock())}`) : '';
      if (info) info.textContent = base + extra + cd;
      const disable = locked && bonus <= 0 && left <= 0;
      if (btn) btn.disabled = disable;
      if (btnFast) btnFast.disabled = disable;
      if (locked) startCountdown();
    } catch {}
  }
  try {
    fastMode = String(readClientState('spinner.fastMode', '0')) === '1';
    if (btnFast) btnFast.checked = fastMode;
  } catch {}
  btnFast?.addEventListener('change', () => {
    fastMode = !!btnFast.checked;
    writeClientState('spinner.fastMode', fastMode ? '1' : '0');
  });

  // Build items
  // Mix of products and shards; odds tuned to favor shards
  const rewardItems = (Array.isArray(state.spinnerProducts) && state.spinnerProducts.length > 0)
    ? state.spinnerProducts.map((p) => ({
        text: p.title || 'Product',
        rarity: p.product_type === 'badge' ? 'rare' : (p.product_type === 'bundle' ? 'epic' : 'common'),
        priceUSD: Number(p.value || 0),
        productType: p.product_type || 'product',
      })).concat([
        { text: 'Shard', rarity: 'collectible', priceUSD: 0, collectible: true },
        { text: 'Shard', rarity: 'collectible', priceUSD: 0, collectible: true },
      ])
    : [
        { text: 'Cinema -10%', rarity: 'common', priceUSD: 1.49 },
        { text: 'Fuel 3%', rarity: 'common', priceUSD: 0.99 },
        { text: 'VIP Bronze Badge', rarity: 'rare', priceUSD: 1.29 },
        { text: 'Starter Saver Bundle', rarity: 'epic', priceUSD: 3.99 },
        { text: 'Shard', rarity: 'collectible', priceUSD: 0, collectible: true },
        { text: 'Shard', rarity: 'collectible', priceUSD: 0, collectible: true },
      ];
  const noWinItem = {
    text: (typeof window.$tKey === 'function') ? window.$tKey('spinner.noWin') : ((typeof window.___ === 'function') ? window.___('No win') : 'No win'),
    rarity: 'empty',
    priceUSD: 0,
    noWin: true,
    retryLabel: (typeof window.$tKey === 'function') ? window.$tKey('spinner.autoRespin') : ((typeof window.___ === 'function') ? window.___('Auto respin') : 'Auto respin'),
  };
  const items = rewardItems.concat(Array.from({ length: NO_WIN_WEIGHT }, () => ({ ...noWinItem })));
  // Seed product catalog
  try {
    rewardItems.forEach(it => { state.products[it.text] = Number(it.priceUSD)||0; });
    window.state = state;
  } catch {}
  function buildReel(repeats=12){
    reel.innerHTML = '';
    const pool = Array.from({length: repeats}).flatMap(()=>items);
    pool.forEach((it) => {
      const el = document.createElement('div');
      el.className = 'spinnerItem';
      // Front = '?', back = content (flip on hover)
      const priceText = (typeof formatPriceUSDToDisplay === 'function') ? formatPriceUSDToDisplay(it.priceUSD||0) : ('$'+Number(it.priceUSD||0).toFixed(3));
      const icon = it.collectible
        ? '<div class="shardIcon" title="Shard">🔷</div>'
        : (it.noWin ? '<div class="shardIcon" title="No win">↻</div>' : '');
      const subline = it.noWin
        ? `<div class="small text-warning mt-1">${it.retryLabel}</div>`
        : `<div class="small text-success mt-1">${priceText}</div>`;
      el.innerHTML = `<div class="face front">?</div><div class="face back">${icon}${it.text}${subline}</div>`;
      reel.appendChild(el);
    });
    return pool;
  }
  let pool = buildReel(12);

  let animating = false;
  let lastTargetIndex = 0;
  function getTranslateX(el) {
    const st = getComputedStyle(el).transform || el.style.transform;
    if (!st || st === 'none') return 0;
    const m3d = st.match(/matrix3d\(([^)]+)\)/);
    if (m3d) { const p = m3d[1].split(','); return parseFloat(p[12]) || 0; }
    const m2d = st.match(/matrix\(([^)]+)\)/);
    if (m2d) { const p = m2d[1].split(','); return parseFloat(p[4]) || 0; }
    const tx = st.match(/translateX\(([-0-9.]+)px\)/);
    return tx ? parseFloat(tx[1]) : 0;
  }
  function performSpin(durationMs){
    if (animating) return;
    animating = true;
    // Reset and build longer track for long run
    pool = buildReel(16);
    reel.style.transition = '';
    reel.style.transform = 'translateX(0px)';
    const itemWidth = 120 + 12; // width + gap
    const visibleCenter = (document.querySelector('.spinnerViewport').clientWidth / 2) - (120 / 2);
  const loops = 5 + Math.floor(Math.random()*4); // 5..8 loops
  const targetIndex = (items.length * loops) + Math.floor(Math.random() * items.length);
  lastTargetIndex = targetIndex;
    const targetOffset = targetIndex * itemWidth;
    const translate = targetOffset - visibleCenter;
    const to = -translate;
    const from = 0;

    if (typeof reel.animate === 'function') {
      const anim = reel.animate([
        { transform: `translateX(${from}px)` },
        { transform: `translateX(${to}px)` }
      ], { duration: durationMs, easing: 'cubic-bezier(0.08, 0.9, 0.2, 1)', fill: 'forwards' });
      anim.onfinish = () => {
        reel.style.transform = `translateX(${to}px)`;
        animating = false;
        handleSpinResult(pickWin(), durationMs);
      };
      anim.oncancel = () => { animating = false; };
    } else {
      reel.style.transition = `transform ${durationMs}ms cubic-bezier(0.08, 0.9, 0.2, 1)`;
      reel.style.transform = `translateX(${to}px)`;
      setTimeout(() => {
        reel.style.transition = '';
        animating = false;
        handleSpinResult(pickWin(), durationMs);
      }, durationMs + 50);
    }
  }
  function handleSpinResult(result, durationMs){
    if (result && result.noWin) {
      if (fastMode) {
        notify('info', (document.documentElement.lang||'en')==='hu' ? 'Nem nyertél, indul az ingyenes újrapörgetés.' : 'No win, starting a free respin.');
        setTimeout(() => performSpin(Math.min(durationMs, AUTO_RESPIN_DURATION_MS)), 250);
      } else {
        notify('info', (document.documentElement.lang||'en')==='hu' ? 'Nem nyertél.' : 'No win.');
      }
      return;
    }
    showWinModal(result, {
      onTake: () => {
        if (fastMode) {
          pendingFastContinuation = true;
        }
      },
      onSell: () => {
        if (fastMode) {
          pendingFastContinuation = true;
        }
      },
      onHidden: () => {
        if (!pendingFastContinuation) return;
        pendingFastContinuation = false;
        setTimeout(() => attemptSpin(spinDuration()), 180);
      }
    });
  }
  function attemptSpin(duration){
    const doRun = async () => {
      if (window.isLoggedIn) {
        // Try to consume from server (will use bonus first or normal and lock when limit reached)
        const prev = serverSpinState; // from closure
        const before = prev ? { bonus: prev.bonus, used: prev.used_today, lock_until: prev.lock_until } : null;
        const s = await adjustServerSpin('consume', 1);
        await updateQuotaUI();
        // If still locked and no bonus used, inform and abort
        const left = getRemaining();
        if (isLocked() && left <= 0 && getBonus() <= 0) {
          notify('info', (typeof window.$tKey==='function') ? $tKey('spinner.noSpinsLeft') : ((document.documentElement.lang||'en')==='hu' ? 'Elfogytak a mai pörgetések. Nézd meg később!' : 'No spins left today. Come back later!'));
          return;
        }
        performSpin(duration);
        return;
      }
      // Guest flow using LS
      if (isLocked()) {
        if (getBonus() > 0) { await consumeBonus(); updateQuotaUI(); performSpin(duration); return; }
        notify('info', (typeof window.$tKey==='function') ? $tKey('spinner.noSpinsLeft') : ((document.documentElement.lang||'en')==='hu' ? 'Elfogytak a mai pörgetések. Nézd meg később!' : 'No spins left today. Come back later!'));
        updateQuotaUI();
        return;
      }
      const left = getRemaining();
      if (left <= 0) {
        const st = readSpinState(); st.lockUntil = now() + 24*3600*1000; writeSpinState(st); updateQuotaUI();
        notify('info', (typeof window.$tKey==='function') ? $tKey('spinner.dailyLimitReached') : ((document.documentElement.lang||'en')==='hu' ? 'Elérted a napi limitet.' : 'Daily limit reached.'));
        return;
      }
      await consumeOneNormal();
      updateQuotaUI();
      performSpin(duration);
    };
    doRun();
  }
  // default run: ~3s
  btn.addEventListener('click', () => attemptSpin(spinDuration()));

  function randomGemAward(){
    // 5000..1,000,000 in steps of 10
    const min = 5000, max = 1_000_000;
    const raw = Math.floor(Math.random() * ((max - min) / 10 + 1)) * 10 + min;
    return raw;
  }
  function pickWin() {
    const idx = lastTargetIndex % items.length;
    const base = items[idx];
    if (base.noWin) {
      return { name: base.text, value: 0, noWin: true };
    }
    const value = Number(base.priceUSD||0);
    if (base.collectible) {
      const gems = randomGemAward();
      // Do NOT credit gems yet; user must decide Keep or Sell in the modal.
      // Still award one shard collectible (towards bonus spins) immediately.
      if (window.isLoggedIn) {
        adjustServerSpin('award_collectible', 1).then(()=> updateQuotaUI()).catch(()=>{});
      } else {
        addCollectibleAndMaybeBonus();
        updateQuotaUI();
      }
  const hu = (document.documentElement.lang||'en')==='hu';
  const gstr = (typeof formatGems==='function') ? formatGems(gems) : String(gems);
  return { name: hu ? `GEM ${gstr}` : `GEM ${gstr}`, value: 0, isGems: true, gemsAmount: gems };
    }
    return { name: base.text, value };
  }
  // Initialize quota display and button state on load
  updateQuotaUI();
}

// Balance helpers & Win Modal
function updateBalanceDisplay() {
  const el = document.getElementById('balanceAmount');
  if (el) el.textContent = formatPrice(state.balance);
  // persist to storage
  writeStoredBalance(state.balance);
  // refresh open buttons enabled/disabled state
  try { updateOpenButtonsState(); } catch {}
  // notify listeners (e.g., single case page) about balance change
  try { document.dispatchEvent(new CustomEvent('pi:balanceUpdated', { detail: { balance: state.balance } })); } catch {}
}

function updateGemsDisplay(){
  const el = document.getElementById('gemsAmount');
  if (el) {
    const val = Math.max(0, Number(state.gems)||0);
    el.textContent = (typeof formatGems==='function') ? formatGems(val) : String(val);
  }
}

function showWinModal(win, hooks) {
  try {
    const opts = hooks || {};
    const nameEl = document.getElementById('winName');
    const valEl = document.getElementById('winValue');
    if (nameEl) nameEl.textContent = win?.name || 'Item';
    if (valEl) valEl.textContent = formatPrice(win?.value || 0);
    const modalEl = document.getElementById('winModal');
    if (!modalEl || !window.bootstrap) return;
    if (!showWinModal.instance) showWinModal.instance = new bootstrap.Modal(modalEl);
    let resolvedAction = null;
    modalEl.onhidden = null;
    modalEl.addEventListener('hidden.bs.modal', () => {
      if (resolvedAction === 'take' && typeof opts.onTake === 'function') opts.onTake();
      if (resolvedAction === 'sell' && typeof opts.onSell === 'function') opts.onSell();
      if (typeof opts.onHidden === 'function') opts.onHidden(resolvedAction);
    }, { once: true });
    // gombok eseményei
    const btnTake = document.getElementById('btnTakePrize');
    const btnSell = document.getElementById('btnQuickSell');
    if (btnTake) btnTake.onclick = () => {
      // If GEM reward: credit gems only on Keep now, then refresh header.
      if (win && win.isGems === true && Number(win.gemsAmount||0) > 0) {
        const amount = Math.max(0, Number(win.gemsAmount)||0);
        if (!window.isLoggedIn) {
          try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {}
          return;
        }
        fetch('/index.php?page=api&action=gemsAdjust', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ type:'award', amount, reference_type:'home_spinner_keep' }) })
          .then(r=>r.ok?r.json():Promise.reject())
          .then(d=>{ if (d && typeof d.balance==='number') { state.gems = d.balance; updateGemsDisplay(); } })
          .finally(()=>{ resolvedAction = 'take'; showWinModal.instance.hide(); });
        return;
      }
      if (!window.isLoggedIn) {
        try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {}
        return; }
      // Normal (non-gem) claimed item -> add to inventory and record
      const entry = {
        name: win?.name || 'Item',
        value: Number(win?.value || 0),
        caseTitle: 'Spinner',
        coupon: (typeof randomCoupon === 'function') ? randomCoupon('SPIN') : ('SPIN-' + Math.random().toString(36).slice(2,8).toUpperCase()),
      };
      saveHistoryEntry(entry, 'claimed');
      addToInventory(entry.name, entry.value);
      try { fetch('/index.php?page=api&action=recordCaseHistory', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ...entry, status:'claimed', priceUSD: 0 }) }); } catch {}
      resolvedAction = 'take';
      showWinModal.instance.hide();
    };
    if (btnSell) {
      // label with payout (for GEM wins value is 0 => micro payout only; gems are NOT credited on sell)
      labelQuickSellButton(btnSell, Number(win?.value || 0));
      btnSell.onclick = () => {
        if (!window.isLoggedIn) { try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {} return; }
        const vStr = btnSell.dataset.payoutUsd;
        let payout = vStr ? Number(vStr) : getSalePriceUSD(Number(win?.value || 0));
        payout = adjustPayoutUSDForCurrencyMin(payout);
        const entry = {
          name: win?.name || 'Item',
          value: Number(win?.value || 0),
          caseTitle: 'Spinner',
          soldAmountUSD: payout,
        };
        // Persist sale to server and then refresh wallet from server so header always matches
        fetch('/index.php?page=api&action=recordCaseHistory', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ...entry, status:'sold', priceUSD: 0 }) })
          .then(()=> fetch('/index.php?page=api&action=walletBalance'))
          .then(r=> r.ok ? r.json() : Promise.reject())
          .then(d=>{ if (d && d.wallet) { const wm = (typeof d.wallet.balance_milli !== 'undefined' && d.wallet.balance_milli !== null) ? Number(d.wallet.balance_milli)/1000 : Number(d.wallet.balance_cents||0)/100; state.balance = wm; updateBalanceDisplay(); } })
          .catch(()=>{ /* if server failed, do not adjust local to avoid desync */ });
        saveHistoryEntry(entry, 'sold');
        resolvedAction = 'sell';
        showWinModal.instance.hide();
      };
    }
    showWinModal.instance.show();
  } catch (e) { /* no-op */ }
}

function initChat() {
  const box = document.getElementById('chatBox');
  const input = document.getElementById('chatInput');
  const send = document.getElementById('chatSend');
  const status = document.getElementById('chatStatus');
  if (!box || !input || !send) return;

  let canChat = true;
  let pollId = null;
  const clearChatInput = () => { try { input.value = ''; } catch {} };
  clearChatInput();
  input.setAttribute('autocomplete', 'new-password');
  input.setAttribute('name', 'community_message_input');
  input.setAttribute('data-lpignore', 'true');
  input.setAttribute('data-form-type', 'other');
  input.setAttribute('autocapitalize', 'sentences');
  window.setTimeout(clearChatInput, 0);
  window.setTimeout(clearChatInput, 120);
  window.setTimeout(clearChatInput, 600);
  input.addEventListener('focus', clearChatInput);
  window.addEventListener('pageshow', clearChatInput);
  const tChat = (key, huFallback, enFallback) => {
    try {
      if (typeof window.$tKey === 'function') return window.$tKey(key);
    } catch {}
    return (document.documentElement.lang || 'en') === 'hu' ? huFallback : enFallback;
  };

  function setStatus(text) {
    if (status) status.textContent = text || '';
  }

  function render() {
    box.innerHTML = '';
    state.chat.forEach((m) => {
      const row = document.createElement('div');
      row.className = `chatRow ${m.isOwn ? 'isOwn' : 'isOther'}`;
      const el = document.createElement('div');
      el.className = 'chatMsg';
      const time = m.created_at ? new Date(m.created_at).toLocaleString([], { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }) : '';
      if (time) {
        el.dataset.chatTime = time;
        el.setAttribute('aria-label', `${m.user} ${time}`);
      }
      el.innerHTML = `<div class="chatAuthor">${m.user}</div><div class="chatText">${m.text}</div>`;
      row.appendChild(el);
      box.appendChild(row);
    });
    box.scrollTop = box.scrollHeight;
  }

  async function loadChat() {
    try {
      const r = await fetch('/index.php?page=api&action=listChatMessages');
      if (!r.ok) throw new Error('http');
      const d = await r.json();
      state.chat = Array.isArray(d?.items) ? d.items.map((item) => ({
        user: item.display_name || 'Guest',
        text: item.message || '',
        created_at: item.created_at || null,
        isOwn: !!item.is_own,
      })) : [];
      canChat = !!d?.can_chat;
      input.disabled = !canChat;
      send.disabled = !canChat;
      if (!canChat && d?.banned_until) {
        setStatus(`${tChat('chat.bannedUntil', 'A chat letiltva eddig:', 'Chat disabled until')} ${new Date(d.banned_until).toLocaleString()}`);
      } else {
        setStatus('');
      }
      render();
    } catch {
      setStatus('');
      render();
    }
  }

  async function submitChat() {
    const text = input.value.trim();
    if (!text || !canChat) return;
    send.disabled = true;
    try {
      const r = await fetch('/index.php?page=api&action=postChatMessage', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok) {
        if (d?.banned_until) {
          canChat = false;
          input.disabled = true;
          send.disabled = true;
          setStatus(`${tChat('chat.bannedUntil', 'A chat letiltva eddig:', 'Chat disabled until')} ${new Date(d.banned_until).toLocaleString()}`);
        }
        return;
      }
      input.value = '';
      await loadChat();
    } catch {
    } finally {
      if (canChat) send.disabled = false;
    }
  }

  send.addEventListener('click', submitChat);
  input.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      submitChat();
    }
  });

  loadChat();
  pollId = window.setInterval(loadChat, 7000);
  window.addEventListener('beforeunload', () => { if (pollId) window.clearInterval(pollId); }, { once: true });
}

function initLiveDropToggle() {
  const toggle = document.getElementById('toggleLiveDrop');
  const ticker = document.getElementById('liveDropTicker');
  if (!toggle || !ticker) return;
  toggle.addEventListener('change', () => {
    state.liveDropEnabled = !!toggle.checked;
    ticker.style.display = toggle.checked ? 'block' : 'none';
    if (toggle.checked) {
      startLiveDropSlotsLoop();
    } else {
      // pause loop; DOM state marad
      stopLiveDropSlotsLoop();
    }
  });
}

function initTopUp() {
  const modalEl = document.getElementById('topUpModal');
  if (!modalEl || !window.bootstrap) return;
  const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
  // Update label texts to selected currency
  function refreshTopUpLabels(){
    modalEl.querySelectorAll('[data-topup]').forEach((btn)=>{
      const usd = Number(btn.getAttribute('data-topup'))||0;
      btn.textContent = `+ ${formatPriceUSDToDisplay(usd)}`;
    });
  }
  refreshTopUpLabels();
  modalEl.querySelectorAll('[data-topup]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const amt = Number(btn.getAttribute('data-topup')) || 0;
      if (window.isLoggedIn) {
        fetch('/index.php?page=api&action=walletAdjust', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ type:'deposit', amountUSD: amt }) })
          .then(r=>r.ok?r.json():Promise.reject())
          .then(d=>{ if (d && (typeof d.balance_milli !== 'undefined' || typeof d.balance_cents !== 'undefined')) { const wm = (typeof d.wallet.balance_milli !== 'undefined' && d.wallet.balance_milli !== null) ? Number(d.wallet.balance_milli)/1000 : Number(d.wallet.balance_cents||0)/100; state.balance = wm; } else { state.balance = Number((state.balance + amt).toFixed(3)); } updateBalanceDisplay(); inst.hide(); })
          .catch(()=>{ state.balance = Number((state.balance + amt).toFixed(3)); updateBalanceDisplay(); inst.hide(); });
      } else {
        // Require login for top-up
        try { const el = document.getElementById('authModal'); if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); } catch {}
      }
    });
  });
  // expose for currency change refresh
  try { initTopUp.refreshLabels = refreshTopUpLabels; } catch {}
}

window.addEventListener('DOMContentLoaded', async () => {
  await loadClientState();
  // Apply saved profile locale and currency as defaults on login or fresh load
  if (usesStoredUserPrefs) {
    const savedCur = String(readClientState('guest.currency', '') || '').toUpperCase();
    if (savedCur) {
      setCurrency(savedCur);
      const curBtn = document.getElementById('currencyButton');
      if (curBtn) curBtn.textContent = savedCur;
    }
  }
  // normalize case prices to [1..1000] USD before rendering
  normalizeCasePrices();
  // Currency switcher wiring
  const curBtn = document.getElementById('currencyButton');
  if (curBtn) {
    const saved = (usesStoredUserPrefs ? (String(readClientState('guest.currency', state.currency || 'USD')) || state.currency || 'USD') : (state.currency||'USD')).toUpperCase();
    state.currency = saved; curBtn.textContent = saved;
    document.querySelectorAll('.currencySwitcher [data-currency]')?.forEach((a) => {
      a.addEventListener('click', async (e) => {
        e.preventDefault();
        const cur = a.getAttribute('data-currency');
        setCurrency(cur);
        if (curBtn) curBtn.textContent = cur;
        // Update topup labels too
        if (typeof initTopUp?.refreshLabels === 'function') initTopUp.refreshLabels();
        // Refresh any price displays
        refreshPriceDisplays();
      });
    });
  }

  // Ensure FX rates available before rendering prices
  ensureFxRates().then(()=>{
    refreshPriceDisplays();
    if (typeof initTopUp?.refreshLabels === 'function') initTopUp.refreshLabels();
  });
  initSwipers();
  renderLiveDrop();
  initFilters();
  renderCases();
  renderEventCases();
  renderCommunityCarousel();
  renderFeaturedCarousel();
  renderHomeBundles();
  initSpinner();
  initChat();
  initLiveDropToggle();
  // Initialize balance from storage
  const stored = readStoredBalance();
  if (stored != null) state.balance = stored;
  // If logged in, sync wallet from server (USD base)
  if (window.isLoggedIn) {
    try {
      fetch('/index.php?page=api&action=listHistory')
        .then(r=>r.ok?r.json():Promise.reject())
        .then(d=>{
          const items = Array.isArray(d?.items) ? d.items : [];
          window.__serverHistory = items.map(x=>({
            ts: Date.parse(x.created_at || '') || Date.now(),
            caseTitle: x.case_title,
            name: x.won_item_title,
            value: Number(x.won_value_cents || 0) / 100,
            status: x.status,
            coupon: x.coupon_code || null,
            soldAmountUSD: (typeof x.sold_amount_milli !== 'undefined' && x.sold_amount_milli !== null) ? (Number(x.sold_amount_milli || 0) / 1000) : ((Number(x.sold_amount_cents || 0) / 100) || undefined)
          }));
          refreshLevel();
        })
        .catch(()=>{});
    } catch {}
    try {
      fetch('/index.php?page=api&action=listInventory')
        .then(r=>r.ok?r.json():Promise.reject())
        .then(d=>{
          const items = Array.isArray(d?.items) ? d.items : [];
          window.__serverInventory = items.map(it => ({
            id: 's' + String(it.id),
            name: it.item_title,
            valueUSD: Number(it.item_value_cents || 0) / 100,
            ts: Date.parse(it.created_at || '') || Date.now()
          }));
        })
        .catch(()=>{});
    } catch {}
    try {
      fetch('/index.php?page=api&action=getProfile')
        .then(r=>r.ok?r.json():Promise.reject())
        .then(d=>{
          const profile = d?.profile || {};
          const locale = String(profile.locale || document.documentElement.lang || 'en').toLowerCase();
          const currency = String(profile.preferred_currency || state.currency || 'USD').toUpperCase();
          document.documentElement.lang = locale;
          state.locale = locale;
          setCurrency(currency);
          const curBtn = document.getElementById('currencyButton');
          if (curBtn) curBtn.textContent = currency;
          try { if (typeof initTopUp?.refreshLabels === 'function') initTopUp.refreshLabels(); } catch {}
        })
        .catch(()=>{});
    } catch {}
    try {
      fetch('/index.php?page=api&action=walletBalance')
  .then(r=>r.ok?r.json():Promise.reject())
  .then(d=>{ if (d && d.wallet) { const wm = (typeof d.wallet.balance_milli !== 'undefined' && d.wallet.balance_milli !== null) ? Number(d.wallet.balance_milli)/1000 : Number(d.wallet.balance_cents||0)/100; state.balance = wm; updateBalanceDisplay(); } })
        .catch(()=>{});
    } catch {}
    // Fetch gems balance
    try {
      fetch('/index.php?page=api&action=gemsBalance')
        .then(r=>r.ok?r.json():Promise.reject())
        .then(d=>{ if (d && typeof d.balance === 'number') { state.gems = d.balance; updateGemsDisplay(); } })
        .catch(()=>{});
    } catch {}
  }
  updateBalanceDisplay();
  updateGemsDisplay();
  // Initialize level from storage/history
  try { refreshLevel(); } catch { updateLevelDisplay(); }
  // extra passes to ensure any late-rendered buttons are updated
  try { updateOpenButtonsState(); } catch {}
  setTimeout(() => { try { updateOpenButtonsState(); } catch {} }, 300);
  initTopUp();
  initOnViewReveal();
  if (typeof registerReveal === 'function') registerReveal(document);
  // Build inventory from history so Exchange can use it
  try { reconcileInventoryFromHistory(); } catch {}
  // Event countdown
  (function initEventCountdown(){
    const header = document.getElementById('eventHeader');
    if (!header) return;
    const deadlineStr = header.getAttribute('data-deadline');
    const deadline = deadlineStr ? new Date(deadlineStr).getTime() : Date.now() + 7*24*3600*1000;
    const elD = document.getElementById('cdDays');
    const elH = document.getElementById('cdHours');
    const elM = document.getElementById('cdMin');
    const elS = document.getElementById('cdSec');
    function tick(){
      const now = Date.now();
      let diff = Math.max(0, Math.floor((deadline - now)/1000));
      const d = Math.floor(diff / 86400); diff -= d*86400;
      const h = Math.floor(diff / 3600); diff -= h*3600;
      const m = Math.floor(diff / 60); diff -= m*60;
      const s = diff;
      if (elD) elD.textContent = String(d);
      if (elH) elH.textContent = String(h).padStart(2,'0');
      if (elM) elM.textContent = String(m).padStart(2,'0');
      if (elS) elS.textContent = String(s).padStart(2,'0');
    }
    tick();
    setInterval(tick, 1000);
  })();
  // Toggle password
  const btnPwd = document.querySelector('.togglePwd');
  const inputPwd = document.getElementById('password');
  if (btnPwd && inputPwd) {
    btnPwd.addEventListener('click', () => {
      inputPwd.type = inputPwd.type === 'password' ? 'text' : 'password';
    });
  }

  document.querySelectorAll('.rankSetItem').forEach((a) => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const lv = Math.max(1, Math.min(10, Number(a.getAttribute('data-level'))||1));
      writeStoredLevel(lv);
      setManualLevelMode(true);
      state.level = lv;
      updateLevelDisplay();
      try { updateOpenButtonsState(); } catch {}
      try { document.dispatchEvent(new CustomEvent('pi:levelUpdated', { detail: { level: lv, manual: true } })); } catch {}
    });
  });
  const rankAuto = document.getElementById('rankSetterAuto');
  if (rankAuto) rankAuto.addEventListener('click', (e) => {
    e.preventDefault();
    setManualLevelMode(false);
    refreshLevel();
    try { updateOpenButtonsState(); } catch {}
    try { document.dispatchEvent(new CustomEvent('pi:levelUpdated', { detail: { level: state.level, manual: false } })); } catch {}
  });

  // i18n dynamic registry and overrides application
  try { i18nRefresh(); } catch {}
});

// Refresh visible prices across the page according to current currency
function refreshPriceDisplays() {
  try {
    // priceTag elements may contain raw text; better to re-render dynamic sections where possible
    // Trigger rerenders that build HTML from state
    try { renderCases(); } catch {}
    try { renderEventCases(); } catch {}
    try { renderCommunityCarousel(); } catch {}
    try { renderFeaturedCarousel(); } catch {}
    try { renderHomeBundles(); } catch {}
    try { renderLiveDrop(); } catch {}
    try { i18nRefresh(); } catch {}
    // Update standalone price tags with data attributes if present
    document.querySelectorAll('[data-price-usd][data-price-target]').forEach((el)=>{
      const usd = Number(el.getAttribute('data-price-usd'))||0;
      const tgt = el.getAttribute('data-price-target');
      const val = formatPriceUSDToDisplay(usd);
      if (tgt === 'text') el.textContent = val; else el.innerHTML = val;
    });
  } catch {}
}

// Update enable/disable state of open buttons based on USD balance vs USD price
function updateOpenButtonsState() {
  const bal = Number(state.balance)||0;
  const gems = Number(state.gems)||0;
  document.querySelectorAll('button[data-price]')?.forEach((btn)=>{
    // Skip case page primary open button; it has its own quota/paid-cycle logic
    if (btn.id === 'btnOpenThisCase') return;
    const p = Number(btn.getAttribute('data-price'))||0; // USD
    const reqLv = Number(btn.getAttribute('data-req-level'))||1;
    const levelOk = (state.level||1) >= reqLv;
    const gemCost = Math.max(0, Math.round(p * 1000 / 10) * 10);
    const canPay = (bal >= p) || (gems >= gemCost);
    btn.disabled = !levelOk || !canPay;
  });
}
