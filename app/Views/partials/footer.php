<?php /** @var callable $t */ ?>
</main>
<footer class="siteFooter mt-5 py-4">
  <div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-6">
      <p class="mb-1">© <?= date('Y') ?> PerkSpin</p>
      <p class="mb-1"><?php echo htmlspecialchars($t('footer.credit') ?? 'Created by Temesvári László 2026 & TEMI PC Kft.'); ?></p>
      <p class="text-muted small mb-0"><?php echo htmlspecialchars($t('footer.legal') ?? 'Legal note: this is a commercial template. Ensure compliance with local laws before selling; liability and license terms are subject to agreement.'); ?></p>
        <?php
          $foot = function_exists('getContentSection') ? getContentSection('global','footer', $locale ?? (isset($GLOBALS['locale'])?$GLOBALS['locale']:'en')) : null;
          $footHtml = trim((string)($foot['body'] ?? ''));
        ?>
        <?php if ($footHtml !== ''): ?>
          <div class="text-muted small mb-0"><?= $footHtml ?></div>
        <?php else: ?>
          <p class="text-muted small mb-0"><?= $t('footer.disclaimer') ?></p>
        <?php endif; ?>
      </div>
      <div class="col-md-6 d-flex justify-content-md-end gap-3 align-items-center">
        <a class="footerLink" href="#privacy"><?= $t('footer.privacy') ?></a>
        <a class="footerLink" href="#terms"><?= $t('footer.terms') ?></a>
        <a class="footerLink" href="#contact"><?= $t('footer.contact') ?></a>
      </div>
    </div>
  </div>
</footer>
<!-- Insufficient Funds Modal -->
<div class="modal fade" id="insufficientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
  <h5 class="modal-title"><?= ___('Insufficient balance') ?></h5>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
  <p class="mb-3"><?= ___('Add more funds to open the case.') ?></p>
        <div class="d-flex gap-2">
          <button class="btn btnPrimary" data-bs-toggle="modal" data-bs-target="#topUpModal"><?= ___('Top up now') ?></button>
          <button class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
        </div>
      </div>
    </div>
  </div>
  </div>
<!-- Case Open Result Modal -->
<div class="modal fade" id="caseOpenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
  <h5 class="modal-title"><?= ___('You opened the case') ?></h5>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="small labelPrimary"><?= ___('Won item') ?></div>
          <div class="h4 mb-1" id="caseOpenResultName">—</div>
          <div class="small labelPrimary"><?= ___('Coupon code') ?></div>
          <div class="h6" id="caseOpenCoupon">—</div>
        </div>
        <div class="d-flex justify-content-center gap-2">
          <button class="btn btnPrimary" id="btnClaimPrize"><span class="winIconTake" aria-hidden="true">📦</span> <?= ___('Take') ?></button>
          <button class="btn btn-outline-light" id="btnSellPrize"><span class="winIconSell" aria-hidden="true">💸</span> <?= ___('Quick sell') ?></button>
        </div>
        <hr class="my-3"/>
        <div>
          <div class="small text-white mb-2"><?= ___('Possible contents') ?></div>
          <div class="d-flex flex-wrap gap-2" id="caseItemsList"></div>
        </div>
      </div>
    </div>
  </div>
  </div>
<!-- Top Up Modal -->
<div class="modal fade" id="topUpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
  <h5 class="modal-title"><?= ___('Top up balance') ?></h5>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
  <p class="text-muted"><?= ___('Choose an amount:') ?></p>
        <div class="d-flex justify-content-center gap-2">
          <button class="btn btnPrimary" data-topup="5">+ 5</button>
          <button class="btn btnPrimary" data-topup="10">+ 10</button>
          <button class="btn btnPrimary" data-topup="25">+ 25</button>
        </div>
      </div>
    </div>
  </div>
  </div>
<!-- Win Modal -->
<div class="modal fade" id="winModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
  <h5 class="modal-title d-flex align-items-center gap-2"><span class="winIconTake" title="<?= ___('Take') ?>">📦</span> <?= ___('Prize') ?></h5>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <div class="display-6" id="winName"><?= ___('Item name') ?></div>
          <div class="h4 text-success" id="winValue" data-price-usd="0" data-price-target="text">0</div>
        </div>
        <div class="d-flex justify-content-center gap-2">
          <button class="btn btnPrimary" id="btnTakePrize"><span class="winIconTake" aria-hidden="true">📦</span> <?= ___('Take') ?></button>
          <button class="btn btn-outline-light" id="btnQuickSell"><span class="winIconSell" aria-hidden="true">💸</span> <?= ___('Quick sell') ?></button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Auth Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
        <ul class="nav nav-tabs navTabsDark" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabSignIn" data-bs-toggle="tab" data-bs-target="#paneSignIn" type="button" role="tab"><?= ___('Sign in') ?></button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tabSignUp" data-bs-toggle="tab" data-bs-target="#paneSignUp" type="button" role="tab"><?= ___('Sign up') ?></button>
          </li>
        </ul>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="tab-content">
          <div class="tab-pane fade show active" id="paneSignIn" role="tabpanel">
            <div id="authPrimaryStep">
              <div class="mb-3">
                <label class="form-label"><?= ___('Email') ?></label>
                <input type="email" id="authEmail" class="form-control formControlDark" placeholder="name@company.com">
              </div>
              <div class="mb-3">
                <label class="form-label"><?= ___('Password') ?></label>
                <input type="password" id="authPassword" class="form-control formControlDark" placeholder="••••••••">
              </div>
              <button id="authLoginBtn" class="btn btnPrimary w-100"><?= ___('Sign in') ?></button>
              <div class="hrOr my-3 text-center text-muted"><?= ___('or') ?></div>
              <div class="socialRow d-grid gap-2">
                <button class="btn btnOutline"><?= ___('Sign in with Steam') ?></button>
                <div class="d-grid d-md-grid gap-2 gridSocial">
                  <button class="btn btnOutline google">Google</button>
                  <button class="btn btnOutline discord">Discord</button>
                  <button class="btn btnOutline facebook">Facebook</button>
                  <button class="btn btnOutline x"></button>
                </div>
              </div>
            </div>
            <div id="authTwoFactorStep" class="d-none">
              <div class="small text-white mb-3"><?= ___('Enter the 6-digit code from your authenticator app to finish signing in.') ?></div>
              <div class="mb-3">
                <label class="form-label"><?= ___('Authenticator code') ?></label>
                <input type="text" id="authOtpCode" class="form-control formControlDark" placeholder="123456" inputmode="numeric" maxlength="6" autocomplete="one-time-code">
              </div>
              <button id="authVerify2FABtn" class="btn btnPrimary w-100 mb-2"><?= ___('Verify code') ?></button>
              <button id="authBackBtn" class="btn btnOutline w-100"><?= ___('Back') ?></button>
            </div>
          </div>
          <div class="tab-pane fade" id="paneSignUp" role="tabpanel">
            <div class="mb-2">
              <label class="form-label"><?= ___('Display name') ?></label>
              <input type="text" id="regDisplay" class="form-control formControlDark" placeholder="Player">
            </div>
            <div class="mb-2">
              <label class="form-label"><?= ___('Email') ?></label>
              <input type="email" id="regEmail" class="form-control formControlDark" placeholder="name@company.com">
            </div>
            <div class="mb-3">
              <label class="form-label"><?= ___('Password') ?></label>
              <input type="password" id="regPassword" class="form-control formControlDark" placeholder="<?= ___('Create password') ?>">
            </div>
            <button id="authRegisterBtn" class="btn btnPrimary w-100"><?= ___('Create account') ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script src="/assets/js/qrcode.min.js"></script>
<script src="/assets/js/core/bootstrap.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/features/auth-modal.js"></script>
<!-- Floating Donate Widget -->
<div class="donateWidget" id="donateWidget" aria-hidden="false">
  <div class="panel position-relative">
    <button class="closeToggle" id="donateToggle" title="Toggle">×</button>
    <div class="title"><?= ___('Donate') ?></div>
      <div class="small text-white mb-2"><?= ___('Donate note') ?></div>
      <div class="mt-3">
        <button class="btn btn-sm btnPrimary" id="donateOpenBtn"><?= ___('Donate') ?></button>
      </div>
  </div>
</div>

<!-- Donate Modal -->
<div class="modal fade" id="donateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header border-0">
        <h5 class="modal-title"><?= ___('Donate via') ?> Revolut</h5>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-2"><?= ___('Donate note') ?></p>
        <img src="/assets/images/revolut_qr.png" alt="Revolut QR" class="qrImage" />
        <div class="mt-3"><small class="text-white"><?= ___('Revolut label') ?></small></div>
        <div class="mt-2"></div>
        <div class="donateNoteText mb-2"><small class="text-white"><?= htmlspecialchars(___('donate.note_instruction') ?? 'Write a short sentence: what is this donation for (e.g. "Add a new gameplay event"). This will be added to the Revolut payment note.') ?></small></div>
        <div class="donateNoteArea mb-2 text-center">
          <textarea id="donateNote" class="form-control formControlDark" placeholder="<?= htmlspecialchars(___('donate.placeholder') ?? 'Short note (one sentence)') ?>" rows="2"></textarea>
        </div>
        <div class="donateAmounts mt-3 d-flex justify-content-center gap-2">
          <button class="btn btn-outline-light amountBtn" data-amount="2">$2</button>
          <button class="btn btn-outline-light amountBtn" data-amount="5">$5</button>
          <button class="btn btn-outline-light amountBtn" data-amount="10">$10</button>
          <button class="btn btn-outline-light amountBtn" data-amount="25">$25</button>
        </div>
        <div id="donateLinkArea" class="mt-3" style="display:none;">
          <input id="donateLinkInput" class="form-control formControlDark" readonly />
          <div class="d-flex justify-content-center gap-2 mt-2">
            <button id="donateOpenLink" class="btn btnPrimary btn-sm"><?= htmlspecialchars(___('ui.open')) ?></button>
            <button id="donateCopyLink" class="btn btn-outline-light btn-sm"><?= htmlspecialchars(___('DonateCopy')) ?></button>
          </div>
        </div>
        <!-- NOTE: amounts shown above. Clicking an amount opens Revolut and pre-fills the amount; link is also shown for copying. -->
      </div>
    </div>
  </div>
</div>

<script src="/assets/js/features/donate-widget.js"></script>
</body>
</html>
