(function () {
  const widget = document.getElementById('donateWidget');
  const toggle = document.getElementById('donateToggle');
  const modalEl = document.getElementById('donateModal');
  if (!widget || !toggle || !modalEl) return;

  const modal = window.bootstrap ? new bootstrap.Modal(modalEl) : null;
  toggle.addEventListener('click', () => widget.classList.toggle('collapsed'));
  document.getElementById('donateOpenBtn')?.addEventListener('click', () => modal?.show());

  window.generateRevolutLink = function (amountUSD, note) {
    try {
      const base = 'https://revolut.me/';
      let payTarget = String(window.revolutPayTarget || 'temilaci85').trim();
      if (payTarget.startsWith('@')) payTarget = payTarget.slice(1);
      const amountMinor = Math.round((Number(amountUSD) || 0) * 100);
      let query = `amount=${encodeURIComponent(String(amountMinor))}&currency=USD`;
      if (String(note || '').trim() !== '') {
        query += `&note=${encodeURIComponent(String(note).replace(/\r?\n/g, ' ').trim())}`;
      }
      return `${base}${encodeURIComponent(payTarget || 'temilaci85')}?${query}`;
    } catch {
      return null;
    }
  };

  const linkArea = document.getElementById('donateLinkArea');
  const linkInput = document.getElementById('donateLinkInput');
  const openLinkBtn = document.getElementById('donateOpenLink');
  const copyLinkBtn = document.getElementById('donateCopyLink');
  const toastMsg = window.___ ? window.___('DonateCopied') : 'Link copied to clipboard';

  document.querySelectorAll('.amountBtn').forEach((button) => {
    button.addEventListener('click', async (event) => {
      const amount = event.currentTarget.getAttribute('data-amount');
      const note = (document.getElementById('donateNote')?.value || '').trim();
      const url = window.generateRevolutLink(amount, note);
      if (!url) return;
      if (linkInput) linkInput.value = url;
      if (linkArea) linkArea.style.display = 'block';
      try {
        await navigator.clipboard.writeText(url);
        Swal.fire({ icon: 'success', title: toastMsg, timer: 1000, showConfirmButton: false });
      } catch {}
      try {
        window.open(url, '_blank');
      } catch {}
    });
  });

  openLinkBtn?.addEventListener('click', () => {
    const url = linkInput?.value || '';
    if (url) window.open(url, '_blank');
  });

  copyLinkBtn?.addEventListener('click', async () => {
    const url = linkInput?.value || '';
    if (!url) return;
    try {
      await navigator.clipboard.writeText(url);
      Swal.fire({ icon: 'success', title: toastMsg, timer: 1000, showConfirmButton: false });
    } catch {
      alert(toastMsg);
    }
  });
})();
