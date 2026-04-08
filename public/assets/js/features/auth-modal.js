(function () {
  const qs = (selector) => document.querySelector(selector);
  const alertBox = (msg, type) => {
    try {
      Swal.fire({ icon: type || 'info', title: msg, timer: 1500, showConfirmButton: false });
    } catch {
      console.warn(msg);
    }
  };

  const primaryStep = qs('#authPrimaryStep');
  const twoFactorStep = qs('#authTwoFactorStep');
  if (!primaryStep || !twoFactorStep) return;

  const showPrimaryStep = () => {
    primaryStep.classList.remove('d-none');
    twoFactorStep.classList.add('d-none');
    const otp = qs('#authOtpCode');
    if (otp) otp.value = '';
  };

  const showTwoFactorStep = () => {
    primaryStep.classList.add('d-none');
    twoFactorStep.classList.remove('d-none');
    qs('#authOtpCode')?.focus();
  };

  qs('#authLoginBtn')?.addEventListener('click', async () => {
    const email = (qs('#authEmail')?.value || '').trim();
    const password = (qs('#authPassword')?.value || '').trim();
    if (!email || !password) {
      alertBox(window.___ ? window.___('Missing credentials') : 'Missing credentials', 'warning');
      return;
    }
    try {
      const response = await fetch('/index.php?page=api&action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ email, password }),
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(payload.error || 'login_failed');
      if (payload.requires_2fa) {
        showTwoFactorStep();
        alertBox(window.___ ? window.___('Two-factor code required') : 'Two-factor code required', 'info');
        return;
      }
      alertBox(window.___ ? window.___('Signed in') : 'Signed in', 'success');
      setTimeout(() => location.reload(), 500);
    } catch {
      alertBox(window.___ ? window.___('Invalid credentials') : 'Invalid credentials', 'error');
    }
  });

  qs('#authVerify2FABtn')?.addEventListener('click', async () => {
    const code = (qs('#authOtpCode')?.value || '').trim();
    if (!code) {
      alertBox(window.___ ? window.___('Authenticator code is required') : 'Authenticator code is required', 'warning');
      return;
    }
    try {
      const response = await fetch('/index.php?page=api&action=verifyTwoFactor', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ code }),
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(payload.error || 'verify_failed');
      alertBox(window.___ ? window.___('Signed in') : 'Signed in', 'success');
      setTimeout(() => location.reload(), 500);
    } catch {
      alertBox(window.___ ? window.___('Invalid authenticator code') : 'Invalid authenticator code', 'error');
    }
  });

  qs('#authBackBtn')?.addEventListener('click', showPrimaryStep);

  qs('#authRegisterBtn')?.addEventListener('click', async () => {
    const display_name = (qs('#regDisplay')?.value || '').trim() || 'Player';
    const email = (qs('#regEmail')?.value || '').trim();
    const password = (qs('#regPassword')?.value || '').trim();
    if (!email || !password) {
      alertBox(window.___ ? window.___('Missing credentials') : 'Missing credentials', 'warning');
      return;
    }
    try {
      const response = await fetch('/index.php?page=api&action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ email, password, display_name }),
      });
      if (!response.ok) throw new Error('register_failed');
      alertBox(window.___ ? window.___('Account created') : 'Account created', 'success');
      setTimeout(() => location.reload(), 500);
    } catch {
      alertBox(window.___ ? window.___('Registration failed') : 'Registration failed', 'error');
    }
  });

  document.getElementById('authModal')?.addEventListener('hidden.bs.modal', showPrimaryStep);
})();
