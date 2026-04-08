<?php /** @var callable $t */ /** @var string $locale */ /** @var string|null $error */ /** @var bool $twoFactorPending */ /** @var string $twoFactorEmail */ ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="loginSection">
  <div class="container-fluid px-4">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="authCard p-4">
          <h1 class="h4 mb-3"><?= $twoFactorPending ? $t('auth.twoFactorTitle') : $t('auth.loginTitle') ?></h1>
          <p class="text-muted mb-4 small">
            <?= $twoFactorPending ? htmlspecialchars(str_replace('{email}', $twoFactorEmail ?: '***', $t('auth.twoFactorSubtitle')), ENT_QUOTES, 'UTF-8') : $t('auth.loginSubtitle') ?>
          </p>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php if ($twoFactorPending): ?>
            <form method="post" action="/index.php?page=login">
              <input type="hidden" name="two_factor_step" value="1">
              <div class="mb-3">
                <label for="otp_code" class="form-label"><?= $t('auth.twoFactorCode') ?></label>
                <input type="text" name="otp_code" class="form-control formControlDark" id="otp_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="123456" required>
              </div>
              <button type="submit" class="btn btnPrimary w-100 mb-2"><?= $t('auth.verifyCode') ?></button>
              <a href="/index.php?page=logout" class="btn btnOutline w-100"><?= $t('auth.cancelSignIn') ?></a>
            </form>
          <?php else: ?>
            <form method="post" action="/index.php?page=login">
              <div class="mb-3">
                <label for="email" class="form-label"><?= $t('auth.email') ?></label>
                <input type="email" name="email" class="form-control formControlDark" id="email" placeholder="name@company.com" required>
              </div>
              <div class="mb-3 position-relative">
                <label for="password" class="form-label"><?= $t('auth.password') ?></label>
                <input type="password" name="password" class="form-control formControlDark" id="password" placeholder="••••••••" required>
                <button class="togglePwd" type="button" aria-label="Show/Hide"></button>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="prettySwitch m-0">
                  <input type="checkbox" id="rememberMe">
                  <span class="switchTrack"><span class="switchThumb"></span></span>
                  <span class="switchLabel ms-2 small"><?= $t('auth.rememberMe') ?></span>
                </label>
                <a href="#" class="small linkMuted"><?= $t('auth.forgot') ?></a>
              </div>
              <button type="submit" class="btn btnPrimary w-100 mb-2"><?= $t('auth.continue') ?></button>
              <a href="/" class="btn btnOutline w-100"><?= $t('auth.backHome') ?></a>
            </form>
            <hr class="my-4">
            <div class="text-center small text-white mb-2">Nincs fiókod?</div>
            <form method="post" action="/index.php?page=register">
              <div class="mb-2">
                <input type="text" name="display_name" class="form-control formControlDark" placeholder="Megjelenített név">
              </div>
              <div class="mb-2">
                <input type="email" name="email" class="form-control formControlDark" placeholder="Email" required>
              </div>
              <div class="mb-3">
                <input type="password" name="password" class="form-control formControlDark" placeholder="Jelszó" required>
              </div>
              <button type="submit" class="btn btnOutline w-100">Regisztráció</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
