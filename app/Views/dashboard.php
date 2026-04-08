<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<script>
  // Preload base translations from server to avoid empty Languages table on first paint
  window._baseTranslations = <?php echo json_encode(isset($baseTranslations)?$baseTranslations:['en'=>[],'hu'=>[]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
  window._baseTranslationsLoaded = true;
</script>

<section class="dashboardSection">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
  <h1 class="h4 mb-0"><?= ___('Dashboard') ?></h1>
  <a href="/index.php?page=home" class="btn btn-outline-light"><?= ___('Back to home') ?></a>
      </div>
    </div>

    <div class="sectionFrame mb-3">
      <ul class="nav nav-tabs navTabsDark" role="tablist" data-i18n-skip>
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tabDash" data-bs-toggle="tab" data-bs-target="#paneDash" type="button" role="tab" aria-selected="true"><?= ___('Overview') ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabProfile" data-bs-toggle="tab" data-bs-target="#paneProfile" type="button" role="tab" aria-selected="false"><?= ___('Profile') ?></button>
        </li>
        <?php if (!empty($_SESSION['is_admin'])): ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabLang" data-bs-toggle="tab" data-bs-target="#paneLang" type="button" role="tab" aria-selected="false"><?= ___('Languages') ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabContent" data-bs-toggle="tab" data-bs-target="#paneContent" type="button" role="tab" aria-selected="false"><?= ___('Content') ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabEvents" data-bs-toggle="tab" data-bs-target="#paneEvents" type="button" role="tab" aria-selected="false"><?= ___('Events') ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabAdminCatalog" data-bs-toggle="tab" data-bs-target="#paneAdminCatalog" type="button" role="tab" aria-selected="false"><?= ___('Catalog') ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tabBuilder" data-bs-toggle="tab" data-bs-target="#paneBuilder" type="button" role="tab" aria-selected="false"><?= ___('Builder') ?></button>
        </li>
        <?php endif; ?>
      </ul>
      <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="paneDash" role="tabpanel">
          <h2 class="sectionTitle h6 mb-3"><?= ___('Quick stats') ?></h2>
          <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card p-3 text-center">
            <div class="small text-muted"><?= ___('Balance') ?></div>
            <div class="h4"><span class="statsValue" id="dbBalance" data-price-usd="0" data-price-target="text">0</span></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3 text-center">
            <div class="small text-muted"><?= ___('Claimed items') ?></div>
            <div class="h4"><span class="statsValue" id="dbClaimed">0</span></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3 text-center">
            <div class="small text-muted"><?= ___('Sold items') ?></div>
            <div class="h4"><span class="statsValue" id="dbSold">0</span></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3 text-center">
            <div class="small text-muted"><?= ___('Opened cases') ?></div>
            <div class="h4"><span class="statsValue" id="dbOpened">0</span></div>
          </div>
        </div>
          </div>
          <div class="mt-3">
            <h2 class="sectionTitle h6 mb-3"><?= ___('Transactions') ?></h2>
            <div class="table-responsive">
              <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Time') ?></th>
                        <th><?= ___('Type') ?></th>
                        <th><?= ___('Description') ?></th>
                        <th><?= ___('Amount') ?></th>
                      </tr>
                    </thead>
                <tbody id="dbTxBody"></tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="small text-muted"><?= ___('rows_per_page') ?></div>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-light" id="dbTxPrev">Prev</button>
                <span class="small" id="dbTxPageInfo">1 / 1</span>
                <button class="btn btn-sm btn-outline-light" id="dbTxNext">Next</button>
              </div>
            </div>
          </div>
          <div class="mt-4">
            <h2 class="sectionTitle h6 mb-3"><?= ___('Opened cases') ?></h2>
            <div class="table-responsive">
              <table class="table table-dark table-sm align-middle">
                  <thead>
                    <tr>
                      <th><?= ___('Time') ?></th>
                      <th><?= ___('Case') ?></th>
                      <th><?= ___('Prize') ?></th>
                      <th><?= ___('Settlement') ?></th>
                      <th><?= ___('Coupon') ?></th>
                      <th><?= ___('Delta') ?></th>
                      <th><?= ___('Status') ?></th>
                    </tr>
                  </thead>
                <tbody id="dbHistoryBody"></tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="small text-muted">10 sor / oldal</div>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-light" id="dbPrev">Prev</button>
                <span class="small" id="dbPageInfo">1 / 1</span>
                <button class="btn btn-sm btn-outline-light" id="dbNext">Next</button>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="paneProfile" role="tabpanel">
              <h2 class="sectionTitle h6 mb-3"><?= ___('Profile settings') ?></h2>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card p-3">
                <div class="mb-2 small text-muted"><?= ___('Display name') ?></div>
                <input type="text" class="form-control formControlDark" id="dbDisplayName" placeholder="Player" />
                <div class="mt-3 d-flex align-items-center gap-2">
                  <label class="small text-muted mb-0"><?= ___('Use as company') ?></label>
                  <label class="prettySwitch mb-0"><input type="checkbox" id="dbIsCompany"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card p-3">
                <div class="mb-2 small text-muted"><?= ___('Language') ?></div>
                <div class="prettySelect" data-target="dbLocaleWrap">
                  <select id="dbLocale" class="nativeSelect">
                    <option value="en">English</option>
                    <option value="hu">Magyar</option>
                  </select>
                  <span class="selectDisplay"></span>
                  <span class="selectArrow"></span>
                  <div class="dropdownPanel"></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card p-3">
                <div class="mb-2 small text-muted"><?= ___('Currency') ?></div>
                <div class="prettySelect" data-target="dbCurrencyWrap">
                  <select id="dbCurrency" class="nativeSelect">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="HUF">HUF</option>
                  </select>
                  <span class="selectDisplay"></span>
                  <span class="selectArrow"></span>
                  <div class="dropdownPanel"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <div class="card p-3">
                <div class="mb-2 small text-muted"><?= ___('Billing details') ?></div>
                <div class="row g-2">
                  <div class="col-12"><input id="billName" class="form-control formControlDark" placeholder="<?= ___('Name/Company') ?>" /></div>
                  <div class="col-12 d-none" id="billVatWrap"><input id="billVat" class="form-control formControlDark" placeholder="<?= ___('VAT number') ?>" /></div>
                  <div class="col-8"><input id="billCity" class="form-control formControlDark" placeholder="<?= ___('City') ?>" /></div>
                  <div class="col-4"><input id="billZip" class="form-control formControlDark" placeholder="<?= ___('Postal code') ?>" /></div>
                  <div class="col-12"><input id="billAddr" class="form-control formControlDark" placeholder="<?= ___('Street, no') ?>" /></div>
                  <div class="col-12">
                    <select id="billCountry" class="form-select formControlDark">
                      <option value="HU">Magyarország</option>
                      <option value="US">United States</option>
                      <option value="GB">United Kingdom</option>
                      <option value="DE">Germany</option>
                      <option value="AT">Austria</option>
                      <option value="RO">Romania</option>
                      <option value="SK">Slovakia</option>
                      <option value="SI">Slovenia</option>
                      <option value="HR">Croatia</option>
                      <option value="PL">Poland</option>
                      <option value="FR">France</option>
                      <option value="ES">Spain</option>
                      <option value="IT">Italy</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="small text-muted"><?= ___('Shipping details') ?></div>
                  <label class="prettySwitch mb-0"><input type="checkbox" id="shipSame"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  <span class="small ms-2"><?= ___('Same as billing') ?></span>
                </div>
                <div class="row g-2">
                  <div class="col-12"><input id="shipName" class="form-control formControlDark" placeholder="Név" /></div>
                  <div class="col-8"><input id="shipCity" class="form-control formControlDark" placeholder="Város" /></div>
                  <div class="col-4"><input id="shipZip" class="form-control formControlDark" placeholder="Irányítószám" /></div>
                  <div class="col-12"><input id="shipAddr" class="form-control formControlDark" placeholder="Utca, házszám" /></div>
                  <div class="col-12">
                    <select id="shipCountry" class="form-select formControlDark">
                      <option value="HU">Magyarország</option>
                      <option value="US">United States</option>
                      <option value="GB">United Kingdom</option>
                      <option value="DE">Germany</option>
                      <option value="AT">Austria</option>
                      <option value="RO">Romania</option>
                      <option value="SK">Slovakia</option>
                      <option value="SI">Slovenia</option>
                      <option value="HR">Croatia</option>
                      <option value="PL">Poland</option>
                      <option value="FR">France</option>
                      <option value="ES">Spain</option>
                      <option value="IT">Italy</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <div class="card p-3">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="small text-muted"><?= ___('Two-factor authentication') ?></div>
                    <div class="small" id="twoFAStatus"><?= ___('Disabled') ?></div>
                  </div>
                  <label class="prettySwitch mb-0"><input type="checkbox" id="twoFA"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                </div>
                <div class="text-muted small mt-2"><?= ___('Protect your account with Google Authenticator or any TOTP-compatible app.') ?></div>
                <div class="twoFactorSetupPanel d-none mt-3" id="twoFASetupPanel">
                  <div class="small text-muted mb-2"><?= ___('Scan this QR code in Google Authenticator or any compatible app.') ?></div>
                  <div class="twoFactorQrWrap mb-3">
                    <canvas id="twoFAQrCanvas" width="192" height="192"></canvas>
                  </div>
                  <div class="small text-muted mb-2"><?= ___('1. Add the account manually in your authenticator app with this secret.') ?></div>
                  <div class="twoFactorSecretBox mb-2" id="twoFASecret">-</div>
                  <div class="small text-muted mb-2"><?= ___('2. Or copy the setup URI if your app supports it.') ?></div>
                  <input type="text" class="form-control formControlDark mb-3" id="twoFAUri" readonly>
                  <div class="small text-muted mb-2"><?= ___('3. Enter the 6-digit code to activate two-factor authentication.') ?></div>
                  <div class="d-flex flex-column flex-sm-row gap-2">
                    <input type="text" class="form-control formControlDark" id="twoFACode" inputmode="numeric" maxlength="6" placeholder="123456">
                    <button class="btn btnPrimary" id="twoFAConfirmBtn"><?= ___('Verify code') ?></button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card p-3">
                <div class="mb-2 small text-muted"><?= ___('Logins') ?></div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th><?= ___('Start') ?></th>
                        <th><?= ___('End') ?></th>
                        <th><?= ___('IP') ?></th>
                        <th><?= ___('Active') ?></th>
                      </tr>
                    </thead>
                    <tbody id="dbSessionsBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="small text-muted" id="dbSessPageInfo">1 / 1</div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="dbSessPrev">Prev</button>
                    <button class="btn btn-sm btn-outline-light" id="dbSessNext">Next</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <button class="btn btnPrimary adminIconBtn" id="dbSaveProfile" title="<?= ___('Save') ?>" aria-label="<?= ___('Save') ?>">
              <span class="adminGlyph" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>
              </span>
            </button>
          </div>
        </div>
        <?php if (!empty($_SESSION['is_admin'])): ?>
        <div class="tab-pane fade" id="paneAdminCatalog" role="tabpanel">
          <h2 class="sectionTitle h6 mb-3"><?= ___('Manage Products, Cases, and Badges') ?></h2>
          <div class="sectionFrame">
            <ul class="nav nav-tabs navTabsDark mb-3" role="tablist" data-i18n-skip>
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tabCatalogCases" data-bs-toggle="tab" data-bs-target="#paneCatalogCases" type="button" role="tab" aria-selected="true"><?= ___('Boxes') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabCatalogProducts" data-bs-toggle="tab" data-bs-target="#paneCatalogProducts" type="button" role="tab" aria-selected="false"><?= ___('Products') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabCatalogCategories" data-bs-toggle="tab" data-bs-target="#paneCatalogCategories" type="button" role="tab" aria-selected="false"><?= ___('Categories') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabCatalogBundles" data-bs-toggle="tab" data-bs-target="#paneCatalogBundles" type="button" role="tab" aria-selected="false"><?= ___('Bundles') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabCatalogBadges" data-bs-toggle="tab" data-bs-target="#paneCatalogBadges" type="button" role="tab" aria-selected="false"><?= ___('Badges') ?></button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="paneCatalogCases" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small text-muted"><?= ___('Boxes') ?></div>
                  </div>
                  <button class="btn btn-sm btnPrimary" id="caseNewBtn"><?= ___('New') ?></button>
                </div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Title') ?></th>
                        <th><?= ___('Base price (USD)') ?></th>
                        <th><?= ___('Level') ?></th>
                        <th><?= ___('Tag') ?></th>
                        <th><?= ___('Risk') ?></th>
                        <th><?= ___('Sections') ?></th>
                        <th><?= ___('Actions') ?></th>
                      </tr>
                    </thead>
                    <tbody id="caseBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted"><?= ___('rows_per_page') ?></div>
                    <select id="casePerPage" class="form-select form-select-sm formControlDark" style="width:auto">
                      <option value="10" selected>10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="casePrev">Prev</button>
                    <span class="small" id="casePageInfo">1 / 1</span>
                    <button class="btn btn-sm btn-outline-light" id="caseNext">Next</button>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="paneCatalogProducts" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small text-muted"><?= ___('Products') ?></div>
                  </div>
                  <button class="btn btn-sm btnPrimary" id="prodNewBtn"><?= ___('New') ?></button>
                </div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Name') ?></th>
                        <th><?= ___('Category') ?></th>
                        <th><?= ___('Type') ?></th>
                        <th><?= ___('Spinner') ?></th>
                        <th><?= ___('Price (USD)') ?></th>
                        <th><?= ___('Actions') ?></th>
                      </tr>
                    </thead>
                    <tbody id="prodBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted"><?= ___('rows_per_page') ?></div>
                    <select id="prodPerPage" class="form-select form-select-sm formControlDark" style="width:auto">
                      <option value="10" selected>10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="prodPrev">Prev</button>
                    <span class="small" id="prodPageInfo">1 / 1</span>
                    <button class="btn btn-sm btn-outline-light" id="prodNext">Next</button>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="paneCatalogCategories" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small text-muted"><?= ___('Product categories') ?></div>
                  </div>
                  <button class="btn btn-sm btnPrimary" id="productCategoryNewBtn"><?= ___('New') ?></button>
                </div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Name') ?></th>
                        <th><?= ___('Slug') ?></th>
                        <th><?= ___('Actions') ?></th>
                      </tr>
                    </thead>
                    <tbody id="productCategoryBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted"><?= ___('rows_per_page') ?></div>
                    <select id="categoryPerPage" class="form-select form-select-sm formControlDark" style="width:auto">
                      <option value="10" selected>10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="categoryPrev">Prev</button>
                    <span class="small" id="categoryPageInfo">1 / 1</span>
                    <button class="btn btn-sm btn-outline-light" id="categoryNext">Next</button>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="paneCatalogBundles" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small text-muted"><?= ___('Product bundles') ?></div>
                  </div>
                  <button class="btn btn-sm btnPrimary" id="productBundleNewBtn"><?= ___('New') ?></button>
                </div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Name') ?></th>
                        <th><?= ___('Actions') ?></th>
                      </tr>
                    </thead>
                    <tbody id="productBundleBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted"><?= ___('rows_per_page') ?></div>
                    <select id="bundlePerPage" class="form-select form-select-sm formControlDark" style="width:auto">
                      <option value="10" selected>10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="bundlePrev">Prev</button>
                    <span class="small" id="bundlePageInfo">1 / 1</span>
                    <button class="btn btn-sm btn-outline-light" id="bundleNext">Next</button>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="paneCatalogBadges" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="small text-muted"><?= ___('Badges') ?></div>
                  </div>
                  <button class="btn btn-sm btnPrimary" id="badgeNewBtn"><?= ___('New') ?></button>
                </div>
                <div class="table-responsive">
                  <table class="table table-dark table-sm align-middle">
                    <thead>
                      <tr>
                        <th><?= ___('Code') ?></th>
                        <th><?= ___('Name') ?></th>
                        <th><?= ___('Actions') ?></th>
                      </tr>
                    </thead>
                    <tbody id="badgeBody"></tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted"><?= ___('rows_per_page') ?></div>
                    <select id="badgePerPage" class="form-select form-select-sm formControlDark" style="width:auto">
                      <option value="10" selected>10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light" id="badgePrev">Prev</button>
                    <span class="small" id="badgePageInfo">1 / 1</span>
                    <button class="btn btn-sm btn-outline-light" id="badgeNext">Next</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="productEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <h3 class="h5 mb-0" id="productEditorTitle"><?= ___('Product editor') ?></h3>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="productModalId">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="small text-muted d-block mb-1"><?= ___('Name') ?></label>
                      <input id="productModalNameInput" class="form-control formControlDark" placeholder="<?= ___('Product name') ?>" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Category') ?></label>
                      <select id="productModalCategoryInput" class="form-select formControlDark"></select>
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Type') ?></label>
                      <select id="productModalTypeInput" class="form-select formControlDark">
                        <option value="product">product</option>
                        <option value="badge">badge</option>
                        <option value="bundle">bundle</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Price (USD)') ?></label>
                      <input id="productModalPriceInput" type="number" step="0.01" min="0" class="form-control formControlDark" placeholder="0.00" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Valid from') ?></label>
                      <input id="productModalValidFromInput" type="datetime-local" class="form-control formControlDark" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Valid until') ?></label>
                      <input id="productModalValidUntilInput" type="datetime-local" class="form-control formControlDark" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Home spinner') ?></label>
                      <div class="d-flex align-items-center gap-2 mt-2">
                        <label class="prettySwitch mb-0">
                          <input type="checkbox" id="productModalSpinnerInput">
                          <span class="switchTrack"><span class="switchThumb"></span></span>
                        </label>
                        <label class="small mb-0" for="productModalSpinnerInput"><?= ___('Use on homepage spinner') ?></label>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="small text-muted d-block mb-1"><?= ___('Description') ?></label>
                      <textarea id="productModalDescriptionInput" class="form-control formControlDark" rows="4" placeholder="<?= ___('Describe the usage conditions of the product') ?>"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="productModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="productModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="badgeEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <h3 class="h5 mb-0" id="badgeEditorTitle"><?= ___('Badge editor') ?></h3>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="badgeModalId">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Code') ?></label>
                      <input id="badgeModalCodeInput" class="form-control formControlDark" placeholder="<?= ___('Badge code') ?>" />
                    </div>
                    <div class="col-md-8">
                      <label class="small text-muted d-block mb-1"><?= ___('Name') ?></label>
                      <input id="badgeModalNameInput" class="form-control formControlDark" placeholder="<?= ___('Badge name') ?>" />
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="badgeModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="badgeModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="categoryEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <h3 class="h5 mb-0" id="categoryEditorTitle"><?= ___('Category editor') ?></h3>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="categoryModalId">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="small text-muted d-block mb-1"><?= ___('Name') ?></label>
                      <input id="categoryModalNameInput" class="form-control formControlDark" placeholder="<?= ___('Category name') ?>" />
                    </div>
                    <div class="col-md-6">
                      <label class="small text-muted d-block mb-1"><?= ___('Slug') ?></label>
                      <input id="categoryModalSlugInput" class="form-control formControlDark" placeholder="entertainment" />
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="categoryModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="categoryModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="bundleEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <div>
                    <h3 class="h5 mb-1" id="bundleEditorTitle"><?= ___('Bundle editor') ?></h3>
                  <div class="small text-muted"><?= ___('Edit the bundle name and the included products, categories, boxes, or nested bundles.') ?></div>
                  </div>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="bundleModalId">
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="small text-muted d-block mb-1"><?= ___('Name') ?></label>
                      <input id="bundleModalNameInput" class="form-control formControlDark" placeholder="<?= ___('Bundle name') ?>" />
                    </div>
                  </div>
                  <hr class="border-secondary my-4">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                      <div class="h6 mb-1"><?= ___('Bundle items') ?></div>
                      <div class="small text-muted"><?= ___('Select products, categories, boxes, or nested bundles for the active bundle.') ?></div>
                    </div>
                    <button class="btn btn-sm btnPrimary" id="bundleItemNewBtn" type="button"><?= ___('Add item') ?></button>
                  </div>
                  <div id="bundleModalItemsEmpty" class="small text-muted mb-3 d-none"><?= ___('Save the bundle first, then add products to it.') ?></div>
                  <div class="table-responsive">
                    <table class="table table-dark table-sm align-middle mb-0">
                      <thead>
                        <tr>
                          <th><?= ___('Type') ?></th>
                          <th><?= ___('Source') ?></th>
                          <th><?= ___('Quantity') ?></th>
                          <th><?= ___('Actions') ?></th>
                        </tr>
                      </thead>
                      <tbody id="bundleItemsBody"></tbody>
                    </table>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="bundleModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="bundleModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="caseEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <div>
                    <h3 class="h5 mb-1" id="caseEditorTitle"><?= ___('Case editor') ?></h3>
                    <div class="small text-muted"><?= ___('Edit the base information and rewards of the selected case.') ?></div>
                  </div>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="caseModalId">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="small text-muted d-block mb-1"><?= ___('Title') ?></label>
                      <input id="caseModalTitleInput" class="form-control formControlDark" placeholder="<?= ___('Box title') ?>" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Base price (USD)') ?></label>
                      <input id="caseModalPriceInput" type="number" step="0.01" min="0" class="form-control formControlDark" placeholder="0.00" />
                    </div>
                    <div class="col-md-3">
                      <label class="small text-muted d-block mb-1"><?= ___('Required level') ?></label>
                      <input id="caseModalLevelInput" type="number" min="1" max="10" class="form-control formControlDark" placeholder="1" />
                    </div>
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Tag') ?></label>
                      <select id="caseModalTagInput" class="form-select formControlDark"></select>
                    </div>
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Risk') ?></label>
                      <select id="caseModalRiskInput" class="form-select formControlDark"></select>
                    </div>
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Slug') ?></label>
                      <input id="caseModalSlugInput" class="form-control formControlDark" placeholder="neon-rush" />
                      <div class="small text-muted mt-1"><?= ___('Slug is the URL-friendly identifier of the box, for example neon-rush.') ?></div>
                    </div>
                    <div class="col-12">
                      <label class="small text-muted d-block mb-2"><?= ___('Case image') ?></label>
                      <input id="caseModalImgInput" type="hidden" />
                      <input id="caseModalImgFileInput" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="d-none" />
                      <div class="caseImageUploader" id="caseImageUploader">
                        <div class="caseImagePreview">
                          <img id="caseModalImgPreview" src="/assets/images/case-1.svg" alt="<?= ___('Case image preview') ?>" />
                          <button type="button" class="btn btn-sm btnPrimary caseImageEditBtn adminIconBtn" id="caseModalImgEditBtn" aria-label="<?= ___('Change image') ?>" title="<?= ___('Change image') ?>">
                            <span class="adminGlyph" aria-hidden="true">
                              <svg viewBox="0 0 24 24"><path d="M4 20l4.1-.8L19 8.3 15.7 5 4.8 15.9 4 20Z"/><path d="M13.9 6.8 17.2 10"/></svg>
                            </span>
                          </button>
                        </div>
                        <div class="small text-muted mt-2"><?= ___('Upload a case image. The preview keeps the image centered without stretching.') ?></div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="d-flex flex-wrap gap-4 pt-1">
                        <div class="d-flex align-items-center gap-2">
                          <label class="prettySwitch mb-0">
                            <input type="checkbox" id="caseModalCommunityInput">
                            <span class="switchTrack"><span class="switchThumb"></span></span>
                          </label>
                          <label class="small mb-0" for="caseModalCommunityInput"><?= ___('Community section') ?></label>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <label class="prettySwitch mb-0">
                            <input type="checkbox" id="caseModalFeaturedInput">
                            <span class="switchTrack"><span class="switchThumb"></span></span>
                          </label>
                          <label class="small mb-0" for="caseModalFeaturedInput"><?= ___('Featured section') ?></label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <hr class="border-secondary my-4">
                  <div class="row g-3">
                    <div class="col-lg-6">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                          <div class="h6 mb-1"><?= ___('Tag options') ?></div>
                          <div class="small text-muted"><?= ___('Manage selectable box labels.') ?></div>
                        </div>
                        <button class="btn btn-sm btnOutline" id="caseTagOptionNewBtn" type="button"><?= ___('Add tag') ?></button>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-dark table-sm align-middle mb-0">
                          <thead><tr><th><?= ___('Value') ?></th><th><?= ___('Label') ?></th><th><?= ___('Sort') ?></th><th><?= ___('Actions') ?></th></tr></thead>
                          <tbody id="caseTagOptionsBody"></tbody>
                        </table>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                          <div class="h6 mb-1"><?= ___('Risk options') ?></div>
                          <div class="small text-muted"><?= ___('Manage selectable risk levels.') ?></div>
                        </div>
                        <button class="btn btn-sm btnOutline" id="caseRiskOptionNewBtn" type="button"><?= ___('Add risk') ?></button>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-dark table-sm align-middle mb-0">
                          <thead><tr><th><?= ___('Value') ?></th><th><?= ___('Label') ?></th><th><?= ___('Sort') ?></th><th><?= ___('Actions') ?></th></tr></thead>
                          <tbody id="caseRiskOptionsBody"></tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <hr class="border-secondary my-4">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                      <div class="h6 mb-1"><?= ___('Case contents') ?></div>
                      <div class="small text-muted"><?= ___('Each case contains products or custom rewards with configurable value and weight.') ?></div>
                    </div>
                    <button class="btn btn-sm btnPrimary" id="caseModalNewItemBtn" type="button"><?= ___('Add case item') ?></button>
                  </div>
                  <div id="caseModalItemsEmpty" class="small text-muted mb-3 d-none"><?= ___('Save the case first, then add rewards to it.') ?></div>
                  <div class="table-responsive">
                    <table class="table table-dark table-sm align-middle mb-0">
                      <thead><tr><th><?= ___('Type') ?></th><th><?= ___('Source') ?></th><th><?= ___('Value (USD)') ?></th><th><?= ___('Weight') ?></th><th><?= ___('Actions') ?></th></tr></thead>
                      <tbody id="caseModalItemsBody"></tbody>
                    </table>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="caseModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="caseModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <script>
          (function(){
            const $ = (sel)=>document.querySelector(sel);
            const t = (key) => (window.___ ? window.___(key) : key);
            function toast(msg, type){ try { Swal.fire({icon:type||'success', title:msg, timer:1200, showConfirmButton:false}); } catch { console.log(msg); } }
            async function apiJson(url, payload){
              const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
              return r;
            }
            let productOptions = [];
            let productCategories = [];
            let productBundles = [];
            let caseOptions = [];
            let badgeOptions = [];
            let caseMetaOptions = { tag: [], risk: [] };
            let caseEditorModal = null;
            let productEditorModal = null;
            let badgeEditorModal = null;
            let categoryEditorModal = null;
            let bundleEditorModal = null;
            const caseEditorState = { activeId: null };
            const bundleEditorState = { activeId: null, readOnly: false };
            const catalogPager = {
              prod: { page: 1, perPage: 10 },
              case: { page: 1, perPage: 10 },
              badge: { page: 1, perPage: 10 },
              category: { page: 1, perPage: 10 },
              bundle: { page: 1, perPage: 10 }
            };
            function slugifyText(value){
              return String(value || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
            function getPagedItems(kind, items){
              const pager = catalogPager[kind];
              const list = Array.isArray(items) ? items : [];
              const perPage = Math.max(1, Number(pager.perPage) || 10);
              const totalPages = Math.max(1, Math.ceil(list.length / perPage));
              if (pager.page > totalPages) pager.page = totalPages;
              if (pager.page < 1) pager.page = 1;
              const start = (pager.page - 1) * perPage;
              return {
                items: list.slice(start, start + perPage),
                totalPages
              };
            }
            function renderPager(kind, totalPages){
              const pager = catalogPager[kind];
              const info = $(`#${kind}PageInfo`);
              const prev = $(`#${kind}Prev`);
              const next = $(`#${kind}Next`);
              const perPage = $(`#${kind}PerPage`);
              if (info) info.textContent = `${pager.page} / ${totalPages}`;
              if (prev) prev.disabled = pager.page <= 1;
              if (next) next.disabled = pager.page >= totalPages;
              if (perPage) perPage.value = String(pager.perPage);
            }
            async function loadAll(){
              try {
                const [prods,cases,badges,tags,risks,categories,bundles] = await Promise.all([
                  fetch('/index.php?page=api&action=listProducts').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listCases').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listBadges').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listCaseMetaOptions&type=tag').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listCaseMetaOptions&type=risk').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listProductCategories').then(r=>r.ok?r.json():Promise.reject()),
                  fetch('/index.php?page=api&action=listProductBundles').then(r=>r.ok?r.json():Promise.reject()),
                ]);
                productOptions = prods.items||[];
                productCategories = categories.items||[];
                productBundles = bundles.items||[];
                caseOptions = cases.items||[];
                badgeOptions = badges.items||[];
                caseMetaOptions.tag = tags.items||[];
                caseMetaOptions.risk = risks.items||[];
                renderProducts(productOptions);
                renderProductCategories(productCategories);
                renderProductBundles(productBundles);
                renderCases(caseOptions);
                renderBadges(badgeOptions);
                renderCaseMetaOptionRows('tag');
                renderCaseMetaOptionRows('risk');
                refreshCaseMetaSelect('tag');
                refreshCaseMetaSelect('risk');
              } catch {}
            }
            function actionButtons(kind, id){
              return `<div class="d-flex gap-1"><button class="btn btn-sm btnPrimary" data-save="${kind}" data-id="${id||''}">${t('Save')}</button><button class="btn btn-sm btn-outline-danger" data-delete="${kind}" data-id="${id||''}">${t('Delete')}</button></div>`;
            }
            function escapeHtml(value){
              return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
            }
            function iconSvg(name){
              const icons = {
                view: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.2 12s3.5-6 9.8-6 9.8 6 9.8 6-3.5 6-9.8 6-9.8-6-9.8-6Z"/><circle cx="12" cy="12" r="3.2"/></svg>',
                edit: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l4.1-.8L19 8.3 15.7 5 4.8 15.9 4 20Z"/><path d="M13.9 6.8 17.2 10"/></svg>',
                delete: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
                save: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>',
                duplicate: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h11v11H9z"/><path d="M4 4h11v11H4z"/></svg>',
                copy: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v11H9z"/><path d="M5 5h10v2"/><path d="M5 5v11h2"/></svg>',
                check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5 10 17l9-10"/></svg>',
              };
              return `<span class="adminGlyph">${icons[name] || icons.view}</span>`;
            }
            function iconButtons(actions){
              return `<div class="d-flex gap-1">${actions.join('')}</div>`;
            }
            function iconBtn({ tone='outline-light', title='', attrs='', icon='' }){
              return `<button class="btn btn-sm btn-${tone} adminIconBtn" title="${title}" aria-label="${title}" ${attrs}>${icon}</button>`;
            }
            function inlineCrudButtons(kind, id){
              return iconButtons([
                iconBtn({ tone:'primary', title:t('Save'), attrs:`data-save="${kind}" data-id="${id||''}"`, icon:iconSvg('save') }),
                iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete="${kind}" data-id="${id||''}"`, icon:iconSvg('delete') }),
              ]);
            }
            function productCategoryOptionsMarkup(selected){
              return [`<option value="">${t('No category')}</option>`].concat((productCategories||[]).map(c => `<option value="${c.id}" ${String(selected||'')===String(c.id)?'selected':''}>${c.name}</option>`)).join('');
            }
            function caseOptionsMarkup(selected, excludeId){
              return (caseOptions||[])
                .filter(c => String(c.id||'') !== String(excludeId||''))
                .map(c => `<option value="${c.id}" ${String(selected||'')===String(c.id)?'selected':''}>${c.title}</option>`).join('');
            }
            function bundleSourceOptionsMarkup(type, selected, currentBundleId){
              if (type === 'category') {
                return (productCategories||[]).map(cat => `<option value="${cat.id}" ${String(selected||'')===String(cat.id)?'selected':''}>${cat.name}</option>`).join('');
              }
              if (type === 'case') {
                return caseOptionsMarkup(selected, '');
              }
              if (type === 'bundle') {
                return (productBundles||[])
                  .filter(bundle => String(bundle.id||'') !== String(currentBundleId||''))
                  .map(bundle => `<option value="${bundle.id}" ${String(selected||'')===String(bundle.id)?'selected':''}>${bundle.name}</option>`).join('');
              }
              return productOptionsMarkup(selected);
            }
            function renderProducts(items){
              const body=$('#prodBody'); if(!body) return; body.innerHTML='';
              const paged = getPagedItems('prod', items);
              paged.items.forEach(it=>{
                const tr=document.createElement('tr');
                tr.innerHTML=`<td>${it.name||''}</td><td>${it.category_name||'<span class="text-muted">-</span>'}</td><td style="width:1%;white-space:nowrap">${it.product_type||'product'}</td><td style="width:1%;white-space:nowrap">${Number(it.use_home_spinner||0)?'✓':'-'}</td><td style="width:1%;white-space:nowrap">${(Number(it.price_cents||0)/100).toFixed(2)}</td><td style="width:1%">${iconButtons([
                  iconBtn({ title:t('View'), attrs:`data-view-product="${it.id||''}"`, icon:iconSvg('view') }),
                  iconBtn({ title:t('Edit'), attrs:`data-edit-product="${it.id||''}"`, icon:iconSvg('edit') }),
                  iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete="prod" data-id="${it.id||''}"`, icon:iconSvg('delete') }),
                ])}</td>`;
                body.appendChild(tr);
              });
              if (paged.items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="6" class="text-muted small">${t('No products to show')}</td>`;
                body.appendChild(tr);
              }
              renderPager('prod', paged.totalPages);
            }
            function renderProductCategories(items){
              const body = $('#productCategoryBody'); if (!body) return; body.innerHTML = '';
              const paged = getPagedItems('category', items);
              paged.items.forEach((it)=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${it.name||''}</td><td>${it.slug||'<span class="text-muted">-</span>'}</td><td style="width:1%">${iconButtons([
                  iconBtn({ title:t('View'), attrs:`data-view-category="${it.id||''}"`, icon:iconSvg('view') }),
                  iconBtn({ title:t('Edit'), attrs:`data-edit-category="${it.id||''}"`, icon:iconSvg('edit') }),
                  iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-remove-category="${it.id||''}"`, icon:iconSvg('delete') }),
                ])}</td>`;
                body.appendChild(tr);
              });
              if (paged.items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="3" class="text-muted small">${t('No categories to show')}</td>`;
                body.appendChild(tr);
              }
              renderPager('category', paged.totalPages);
            }
            function renderProductBundles(items){
              const body = $('#productBundleBody'); if (!body) return; body.innerHTML = '';
              const paged = getPagedItems('bundle', items);
              paged.items.forEach((it)=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${it.name||''}</td><td style="width:1%">${iconButtons([
                  iconBtn({ title:t('View'), attrs:`data-view-bundle="${it.id||''}"`, icon:iconSvg('view') }),
                  iconBtn({ title:t('Edit'), attrs:`data-edit-bundle="${it.id||''}"`, icon:iconSvg('edit') }),
                  iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-remove-bundle="${it.id||''}"`, icon:iconSvg('delete') }),
                ])}</td>`;
                body.appendChild(tr);
              });
              if (paged.items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="2" class="text-muted small">${t('No bundles to show')}</td>`;
                body.appendChild(tr);
              }
              renderPager('bundle', paged.totalPages);
            }
            async function loadBundleItems(bundleId){
              const body = $('#bundleItemsBody');
              const activeBundleId = Number(bundleId || bundleEditorState.activeId || 0);
              if (!body) return;
              body.innerHTML = '';
              if (!activeBundleId) return;
              try {
                const r = await fetch('/index.php?page=api&action=listProductBundleItems&bundle_id=' + encodeURIComponent(activeBundleId));
                const d = r.ok ? await r.json() : { items: [] };
                (d.items || []).forEach((it)=>{
                  const sourceType = it.source_type || 'offer';
                  const sourceRef = sourceType === 'category'
                    ? (it.source_category_id || '')
                    : sourceType === 'case'
                      ? (it.source_case_id || '')
                      : sourceType === 'bundle'
                        ? (it.source_bundle_id || '')
                        : (it.offer_id || '');
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td style="min-width:140px"><select class="form-select form-select-sm formControlDark bundle-source-type-select" data-id="${it.id||''}" data-f="source_type" ${bundleEditorState.readOnly?'disabled':''}><option value="offer" ${sourceType==='offer'?'selected':''}>${t('Product')}</option><option value="category" ${sourceType==='category'?'selected':''}>${t('Category')}</option><option value="case" ${sourceType==='case'?'selected':''}>${t('Case')}</option><option value="bundle" ${sourceType==='bundle'?'selected':''}>${t('Bundle')}</option></select></td><td style="min-width:220px"><select class="form-select form-select-sm formControlDark bundle-source-ref-select" data-id="${it.id||''}" data-f="source_ref" ${bundleEditorState.readOnly?'disabled':''}>${bundleSourceOptionsMarkup(sourceType, sourceRef, activeBundleId)}</select></td><td style="width:1%"><input class="form-control formControlDark form-control-sm" style="max-width:90px" value="${Number(it.quantity||1)}" data-id="${it.id||''}" data-f="quantity" placeholder="1" ${bundleEditorState.readOnly?'disabled':''}></td><td style="width:1%">${bundleEditorState.readOnly ? '<span class="text-muted small">-</span>' : inlineCrudButtons('bundleItem', it.id||'')}</td>`;
                  body.appendChild(tr);
                });
                if ((d.items||[]).length === 0) {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td colspan="4" class="text-muted small">${t('No bundle items to show')}</td>`;
                  body.appendChild(tr);
                }
                initCaseMetaSelects();
              } catch {}
            }
            function renderCases(items){
              const body=$('#caseBody'); if(!body) return; body.innerHTML='';
              const paged = getPagedItems('case', items);
              paged.items.forEach(it=>{
                const sections = [];
                if (Number(it.is_community||0)) sections.push(`<span class="badge bg-secondary">${t('Community')}</span>`);
                if (Number(it.is_featured||0)) sections.push(`<span class="badge bg-warning text-dark">${t('Featured')}</span>`);
                if (Number(it.is_event||0)) sections.push(`<span class="badge bg-info text-dark">${t('Event')}</span>`);
                const tr=document.createElement('tr');
                tr.innerHTML=`<td>${it.title||''}</td>
                <td style="width:1%;white-space:nowrap">${(Number(it.base_price_cents||0)/100).toFixed(2)}</td>
                <td style="width:1%;white-space:nowrap">Lv ${Number(it.required_level||1)}</td>
                <td style="width:1%;white-space:nowrap">${it.tag||'starter'}</td>
                <td style="width:1%;white-space:nowrap">${it.risk||'medium'}</td>
                <td>${sections.join(' ') || '<span class="text-muted">-</span>'}</td>
                <td style="width:1%">${iconButtons([
                  iconBtn({ title:t('View'), attrs:`data-open-case-items="${it.id||''}"`, icon:iconSvg('view') }),
                  iconBtn({ tone:'primary', title:t('Edit'), attrs:`data-open-case="${it.id||''}"`, icon:iconSvg('edit') }),
                  iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete="case" data-id="${it.id||''}"`, icon:iconSvg('delete') }),
                ])}</td>`;
                body.appendChild(tr);
              });
              if (paged.items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="7" class="text-muted small">${t('No boxes to show')}</td>`;
                body.appendChild(tr);
              }
              renderPager('case', paged.totalPages);
            }
            function renderBadges(items){
              const body=$('#badgeBody'); if(!body) return; body.innerHTML='';
              const paged = getPagedItems('badge', items);
              paged.items.forEach(it=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${it.code||''}</td><td>${it.name||''}</td><td style="width:1%">${iconButtons([
                iconBtn({ title:t('View'), attrs:`data-view-badge="${it.id||''}"`, icon:iconSvg('view') }),
                iconBtn({ title:t('Edit'), attrs:`data-edit-badge="${it.id||''}"`, icon:iconSvg('edit') }),
                iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete="badge" data-id="${it.id||''}"`, icon:iconSvg('delete') }),
              ])}</td>`; body.appendChild(tr); });
              if (paged.items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="3" class="text-muted small">${t('No badges to show')}</td>`;
                body.appendChild(tr);
              }
              renderPager('badge', paged.totalPages);
            }
            function setProductModalValues(data, readOnly){
              $('#productModalId').value = data.id ? String(data.id) : '';
              $('#productModalNameInput').value = data.name || '';
              $('#productModalCategoryInput').innerHTML = productCategoryOptionsMarkup(data.category_id || '');
              $('#productModalCategoryInput').value = data.category_id ? String(data.category_id) : '';
              $('#productModalTypeInput').value = data.product_type || 'product';
              $('#productModalSpinnerInput').checked = Number(data.use_home_spinner || 0) === 1;
              $('#productModalPriceInput').value = typeof data.priceUSD === 'number' ? data.priceUSD.toFixed(2) : (data.priceUSD || '0.00');
              $('#productModalValidFromInput').value = data.valid_from ? String(data.valid_from).replace(' ', 'T').slice(0,16) : '';
              $('#productModalValidUntilInput').value = data.valid_until ? String(data.valid_until).replace(' ', 'T').slice(0,16) : '';
              $('#productModalDescriptionInput').value = data.description || '';
              $('#productEditorTitle').textContent = data.id ? `${readOnly ? t('View') : t('Edit')} - ${data.name || ('#' + data.id)}` : t('New Product');
              ['#productModalNameInput','#productModalCategoryInput','#productModalTypeInput','#productModalPriceInput','#productModalSpinnerInput','#productModalValidFromInput','#productModalValidUntilInput','#productModalDescriptionInput'].forEach((sel)=>{ if ($(sel)) $(sel).disabled = !!readOnly; });
              $('#productModalSaveBtn').classList.toggle('d-none', !!readOnly);
              $('#productModalDeleteBtn').classList.toggle('d-none', !!readOnly || !data.id);
            }
            function setBadgeModalValues(data, readOnly){
              $('#badgeModalId').value = data.id ? String(data.id) : '';
              $('#badgeModalCodeInput').value = data.code || '';
              $('#badgeModalNameInput').value = data.name || '';
              $('#badgeEditorTitle').textContent = data.id ? `${readOnly ? t('View') : t('Edit')} - ${data.name || ('#' + data.id)}` : t('New Badge');
              ['#badgeModalCodeInput','#badgeModalNameInput'].forEach((sel)=>{ if ($(sel)) $(sel).disabled = !!readOnly; });
              $('#badgeModalSaveBtn').classList.toggle('d-none', !!readOnly);
              $('#badgeModalDeleteBtn').classList.toggle('d-none', !!readOnly || !data.id);
            }
            function setCategoryModalValues(data, readOnly){
              $('#categoryModalId').value = data.id ? String(data.id) : '';
              $('#categoryModalNameInput').value = data.name || '';
              $('#categoryModalSlugInput').value = data.slug || '';
              $('#categoryEditorTitle').textContent = data.id ? `${readOnly ? t('View') : t('Edit')} - ${data.name || ('#' + data.id)}` : t('New Category');
              ['#categoryModalNameInput','#categoryModalSlugInput'].forEach((sel)=>{ if ($(sel)) $(sel).disabled = !!readOnly; });
              $('#categoryModalSaveBtn').classList.toggle('d-none', !!readOnly);
              $('#categoryModalDeleteBtn').classList.toggle('d-none', !!readOnly || !data.id);
            }
            function setBundleModalValues(data, readOnly){
              bundleEditorState.activeId = data.id ? Number(data.id) : null;
              bundleEditorState.readOnly = !!readOnly;
              $('#bundleModalId').value = data.id ? String(data.id) : '';
              $('#bundleModalNameInput').value = data.name || '';
              $('#bundleEditorTitle').textContent = data.id ? `${readOnly ? t('View') : t('Edit')} - ${data.name || ('#' + data.id)}` : t('New Bundle');
              if ($('#bundleModalNameInput')) $('#bundleModalNameInput').disabled = !!readOnly;
              $('#bundleModalSaveBtn').classList.toggle('d-none', !!readOnly);
              $('#bundleModalDeleteBtn').classList.toggle('d-none', !!readOnly || !data.id);
              $('#bundleItemNewBtn').classList.toggle('d-none', !!readOnly);
              $('#bundleItemNewBtn').disabled = !data.id || !!readOnly;
              $('#bundleModalItemsEmpty')?.classList.toggle('d-none', !!data.id);
              if (!data.id) $('#bundleItemsBody').innerHTML = '';
            }
            function previewMarkup(title, fields, extraHtml){
              const items = (fields || []).map((field) => `
                <div class="col-md-6">
                  <div class="adminPreviewCard h-100">
                    <div class="adminPreviewLabel">${escapeHtml(field.label || '')}</div>
                    <div class="adminPreviewValue">${escapeHtml(field.value || '—')}</div>
                  </div>
                </div>
              `).join('');
              return `
                <div class="container-fluid px-0">
                  <div class="adminPreviewShell mb-3">
                    <div class="adminPreviewTitle">${escapeHtml(title || '')}</div>
                  </div>
                  <div class="row g-2">${items}</div>
                  ${extraHtml || ''}
                </div>
              `;
            }
            function showPreviewDialog(title, fields, extraHtml){
              if (!window.Swal) return;
              Swal.fire({
                title: title,
                html: previewMarkup(title, fields, extraHtml),
                customClass: { popup: 'modalDark adminPreviewPopup' },
                showConfirmButton: false,
                showCloseButton: true,
              });
            }
            async function openProductPreview(id){
              const item = productOptions.find(it => String(it.id) === String(id));
              if (!item) return;
              showPreviewDialog(item.name || t('Product'), [
                { label: t('Name'), value: item.name || '—' },
                { label: t('Category'), value: item.category_name || '—' },
                { label: t('Type'), value: item.product_type || 'product' },
                { label: t('Price'), value: (typeof window.formatPriceUSDToDisplay === 'function' ? window.formatPriceUSDToDisplay(Number(item.price_cents||0)/100) : String(Number(item.price_cents||0)/100)) },
                { label: t('Start'), value: item.valid_from || '—' },
                { label: t('End'), value: item.valid_until || '—' },
              ], item.description ? `<div class="adminPreviewPanel mt-3"><div class="adminPreviewLabel">${escapeHtml(t('Description'))}</div><div class="adminPreviewText">${escapeHtml(item.description)}</div></div>` : '');
            }
            async function openBadgePreview(id){
              const item = badgeOptions.find(it => String(it.id) === String(id));
              if (!item) return;
              showPreviewDialog(item.name || t('Badge'), [
                { label: t('Code'), value: item.code || '—' },
                { label: t('Name'), value: item.name || '—' },
              ]);
            }
            async function openCategoryPreview(id){
              const item = productCategories.find(it => String(it.id) === String(id));
              if (!item) return;
              showPreviewDialog(item.name || t('Category'), [
                { label: t('Name'), value: item.name || '—' },
                { label: t('Slug'), value: item.slug || '—' },
              ]);
            }
            async function openBundlePreview(id){
              const item = productBundles.find(it => String(it.id) === String(id));
              if (!item) return;
              let rows = [];
              try {
                const r = await fetch('/index.php?page=api&action=listProductBundleItems&bundle_id=' + encodeURIComponent(id));
                const d = r.ok ? await r.json() : { items: [] };
                rows = Array.isArray(d.items) ? d.items : [];
              } catch {}
              const extraHtml = rows.length ? `
                <div class="adminPreviewPanel mt-3">
                  <div class="adminPreviewLabel mb-2">${escapeHtml(t('Bundle items'))}</div>
                  <div class="d-grid gap-2">
                    ${rows.slice(0, 8).map((row) => {
                      const sourceType = row.source_type || 'offer';
                      const sourceLabel = sourceType === 'offer' ? 'Product' : (sourceType === 'case' ? 'Case' : (sourceType === 'category' ? 'Category' : 'Bundle'));
                      return `<div class="adminPreviewListRow"><span>${escapeHtml(String(row.quantity || 1))}x</span><span>${escapeHtml(row.title || row.offer_title || row.category_name || row.case_title || row.source_bundle_name || '—')}</span><small>${escapeHtml(t(sourceLabel))}</small></div>`;
                    }).join('')}
                  </div>
                </div>
              ` : '';
              showPreviewDialog(item.name || t('Bundle'), [
                { label: t('Name'), value: item.name || '—' },
                { label: t('Content'), value: rows.length ? `${rows.length}` : '0' },
              ], extraHtml);
            }
            async function openCasePreview(id){
              const item = caseOptions.find(it => String(it.id) === String(id));
              if (!item) return;
              let rows = [];
              try {
                const r = await fetch('/index.php?page=api&action=listCaseItems&case_id=' + encodeURIComponent(id));
                const d = r.ok ? await r.json() : { items: [] };
                rows = Array.isArray(d.items) ? d.items : [];
              } catch {}
              const imageHtml = item.img ? `<div class="adminPreviewPanel mb-3"><div class="caseImagePreview mx-auto" style="max-width:320px"><img src="${escapeHtml(item.img)}" alt="${escapeHtml(item.title || '')}"></div></div>` : '';
              const extraHtml = `${imageHtml}${rows.length ? `
                <div class="adminPreviewPanel">
                  <div class="adminPreviewLabel mb-2">${escapeHtml(t('Case contents'))}</div>
                  <div class="d-grid gap-2">
                    ${rows.slice(0, 8).map((row) => {
                      const sourceType = row.source_type || 'offer';
                      const sourceLabel = sourceType === 'offer' ? 'Product' : (sourceType === 'category' ? 'Category' : 'Bundle');
                      return `<div class="adminPreviewListRow"><span>${escapeHtml(String(row.weight || 1))}x</span><span>${escapeHtml(row.title || row.offer_title || row.category_name || row.bundle_name || '—')}</span><small>${escapeHtml(t(sourceLabel))}</small></div>`;
                    }).join('')}
                  </div>
                </div>` : ''}`;
              showPreviewDialog(item.title || t('Case'), [
                { label: t('Title'), value: item.title || '—' },
                { label: t('Price'), value: (typeof window.formatPriceUSDToDisplay === 'function' ? window.formatPriceUSDToDisplay(Number(item.base_price_cents||0)/100) : String(Number(item.base_price_cents||0)/100)) },
                { label: t('Required level'), value: String(Number(item.required_level || 1)) },
                { label: t('Tag'), value: item.tag || '—' },
                { label: t('Risk'), value: item.risk || '—' },
              ], extraHtml);
            }
            function openProductModal(id, readOnly){
              const item = productOptions.find(it => String(it.id) === String(id)) || { id:null, name:'', category_id:'', priceUSD:0 };
              setProductModalValues({ ...item, priceUSD: Number(item.price_cents||0)/100 }, !!readOnly);
              productEditorModal?.show();
            }
            function openBadgeModal(id, readOnly){
              const item = badgeOptions.find(it => String(it.id) === String(id)) || { id:null, code:'', name:'' };
              setBadgeModalValues(item, !!readOnly);
              badgeEditorModal?.show();
            }
            function openCategoryModal(id, readOnly){
              const item = productCategories.find(it => String(it.id) === String(id)) || { id:null, name:'', slug:'' };
              setCategoryModalValues(item, !!readOnly);
              categoryEditorModal?.show();
            }
            async function openBundleModal(id, readOnly){
              const item = productBundles.find(it => String(it.id) === String(id)) || { id:null, name:'' };
              setBundleModalValues(item, !!readOnly);
              if (item.id) await loadBundleItems(item.id);
              bundleEditorModal?.show();
            }
            function productOptionsMarkup(selected){
              return productOptions.map(p => `<option value="${p.id}" ${String(selected||'')===String(p.id)?'selected':''}>${p.name}</option>`).join('');
            }
            function caseMetaSelectMarkup(type, selected){
              return (caseMetaOptions[type] || []).map(opt => `<option value="${opt.option_value}" ${String(selected||'')===String(opt.option_value)?'selected':''}>${opt.option_label}</option>`).join('');
            }
            function initSelect2Field(el){
              try {
                if (!el || !window.jQuery || !window.jQuery.fn?.select2) return;
                const $el = window.jQuery(el);
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                $el.select2({
                  dropdownParent: window.jQuery('#caseEditorModal'),
                  width: '100%',
                  placeholder: t('Select'),
                });
              } catch {}
            }
            function initCaseMetaSelects(){
              initSelect2Field($('#caseModalTagInput'));
              initSelect2Field($('#caseModalRiskInput'));
              try {
                window.jQuery('.case-source-select').each((_, el) => initSelect2Field(el));
                window.jQuery('.bundle-source-ref-select, .bundle-source-type-select').each((_, el) => {
                  const $el = window.jQuery(el);
                  if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                  $el.select2({ dropdownParent: window.jQuery('#bundleEditorModal'), width:'100%' });
                });
              } catch {}
            }
            function refreshCaseMetaSelect(type){
              const target = type === 'risk' ? $('#caseModalRiskInput') : $('#caseModalTagInput');
              if (!target) return;
              const current = target.value;
              target.innerHTML = caseMetaSelectMarkup(type, current);
              if ((!current || !target.value) && target.options.length > 0) target.value = target.options[0].value;
            }
            function renderCaseMetaOptionRows(type){
              const body = type === 'risk' ? $('#caseRiskOptionsBody') : $('#caseTagOptionsBody');
              if (!body) return;
              body.innerHTML = '';
              const items = caseMetaOptions[type] || [];
              items.forEach((opt) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><input class="form-control formControlDark form-control-sm" data-meta-type="${type}" data-f="value" data-id="${opt.id}" value="${opt.option_value || ''}" placeholder="${type === 'risk' ? 'medium' : 'featured'}"></td>
                <td><input class="form-control formControlDark form-control-sm" data-meta-type="${type}" data-f="label" data-id="${opt.id}" value="${opt.option_label || ''}" placeholder="${type === 'risk' ? t('Risk label') : t('Tag label')}"></td>
                <td style="width:1%"><input class="form-control formControlDark form-control-sm" style="max-width:90px" data-meta-type="${type}" data-f="sort_order" data-id="${opt.id}" value="${Number(opt.sort_order||0)}" placeholder="0"></td>
                <td style="width:1%">${iconButtons([
                  iconBtn({ tone:'primary', title:t('Save'), attrs:`data-save="caseMetaOption" data-meta-type="${type}" data-id="${opt.id}"`, icon:iconSvg('save') }),
                  iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete="caseMetaOption" data-meta-type="${type}" data-id="${opt.id}"`, icon:iconSvg('delete') }),
                ])}</td>`;
                body.appendChild(tr);
              });
              if (items.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="4" class="text-muted small">${type === 'risk' ? t('No risk options to show') : t('No tag options to show')}</td>`;
                body.appendChild(tr);
              }
            }
            function setCaseModalValues(data){
              $('#caseModalId').value = data.id ? String(data.id) : '';
              $('#caseModalTitleInput').value = data.title || '';
              $('#caseModalPriceInput').value = typeof data.basePriceUSD === 'number' ? data.basePriceUSD.toFixed(2) : (data.basePriceUSD || '0.00');
              $('#caseModalLevelInput').value = String(Number(data.required_level || 1));
              refreshCaseMetaSelect('tag');
              refreshCaseMetaSelect('risk');
              $('#caseModalTagInput').value = data.tag || $('#caseModalTagInput')?.value || '';
              $('#caseModalRiskInput').value = data.risk || $('#caseModalRiskInput')?.value || '';
              initCaseMetaSelects();
              $('#caseModalSlugInput').value = data.slug || '';
              $('#caseModalImgInput').value = data.img || '';
              if ($('#caseModalImgPreview')) $('#caseModalImgPreview').src = data.img || '/assets/images/case-1.svg';
              $('#caseModalCommunityInput').checked = Number(data.is_community || 0) === 1;
              $('#caseModalFeaturedInput').checked = Number(data.is_featured || 0) === 1;
              $('#caseEditorTitle').textContent = data.id ? `${t('Edit')} - ${data.title || ('#' + data.id)}` : t('New Case');
              const canManageItems = !!data.id;
              $('#caseModalNewItemBtn').disabled = !canManageItems;
              $('#caseModalDeleteBtn').disabled = !canManageItems;
              $('#caseModalItemsEmpty')?.classList.toggle('d-none', canManageItems);
              if (!canManageItems) $('#caseModalItemsBody').innerHTML = '';
            }
            function renderCaseModalItems(items){
              const body = $('#caseModalItemsBody');
              if (!body) return;
              body.innerHTML = '';
              (items||[]).forEach(it => {
                const tr = document.createElement('tr');
                const sourceType = it.source_type || 'offer';
                const sourceOptions = sourceType === 'category'
                  ? (productCategories||[]).map(cat => `<option value="${cat.id}" ${String(it.category_id||'')===String(cat.id)?'selected':''}>${cat.name}</option>`).join('')
                  : sourceType === 'bundle'
                    ? (productBundles||[]).map(bundle => `<option value="${bundle.id}" ${String(it.bundle_id||'')===String(bundle.id)?'selected':''}>${bundle.name}</option>`).join('')
                    : productOptionsMarkup(it.offer_id||'');
                tr.innerHTML = `<td style="min-width:140px"><select class="form-select form-select-sm formControlDark" data-id="${it.id||''}" data-f="source_type"><option value="offer" ${sourceType==='offer'?'selected':''}>${t('Product')}</option><option value="category" ${sourceType==='category'?'selected':''}>${t('Category')}</option><option value="bundle" ${sourceType==='bundle'?'selected':''}>${t('Bundle')}</option></select></td>
                <td style="min-width:220px"><select class="form-select form-select-sm formControlDark case-source-select" data-id="${it.id||''}" data-f="source_ref">${sourceOptions}</select></td>
                <td style="width:1%"><input class="form-control formControlDark form-control-sm" style="max-width:110px" value="${(Number(it.value_cents||0)/100).toFixed(2)}" data-id="${it.id||''}" data-f="value" placeholder="0.00"></td>
                <td style="width:1%"><input class="form-control formControlDark form-control-sm" style="max-width:90px" value="${Number(it.weight||1)}" data-id="${it.id||''}" data-f="weight" placeholder="1"></td>
                <td style="width:1%">${inlineCrudButtons('caseItem', it.id||'')}</td>`;
                body.appendChild(tr);
              });
              initCaseMetaSelects();
            }
            async function loadCaseItems(caseId){
              const body = $('#caseModalItemsBody');
              if (!body || !caseId) { if (body) body.innerHTML=''; return; }
              try {
                const r = await fetch('/index.php?page=api&action=listCaseItems&case_id=' + encodeURIComponent(caseId));
                const d = r.ok ? await r.json() : { items: [] };
                renderCaseModalItems(d.items||[]);
              } catch {}
            }
            function updateCaseSourceOptions(row){
              if (!row) return;
              const type = row.querySelector(`select[data-f="source_type"]`)?.value || 'offer';
              const target = row.querySelector(`select[data-f="source_ref"]`);
              if (!target) return;
              if (type === 'category') {
                target.innerHTML = (productCategories||[]).map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
              } else if (type === 'bundle') {
                target.innerHTML = (productBundles||[]).map(bundle => `<option value="${bundle.id}">${bundle.name}</option>`).join('');
              } else {
                target.innerHTML = productOptionsMarkup('');
              }
              initCaseMetaSelects();
            }
            function updateBundleSourceOptions(row){
              if (!row) return;
              const type = row.querySelector(`select[data-f="source_type"]`)?.value || 'offer';
              const target = row.querySelector(`select[data-f="source_ref"]`);
              if (!target) return;
              const currentBundleId = Number($('#bundleModalId')?.value || bundleEditorState.activeId || 0);
              target.innerHTML = bundleSourceOptionsMarkup(type, '', currentBundleId);
              initCaseMetaSelects();
            }
            async function openCaseModal(id){
              const caseData = caseOptions.find(it => String(it.id) === String(id));
              if (!caseData) return;
              caseEditorState.activeId = Number(caseData.id);
              setCaseModalValues({
                id: caseData.id,
                title: caseData.title || '',
                basePriceUSD: Number(caseData.base_price_cents || 0) / 100,
                required_level: Number(caseData.required_level || 1),
                tag: caseData.tag || 'starter',
                risk: caseData.risk || 'medium',
                slug: caseData.slug || '',
                img: caseData.img || '',
                is_community: Number(caseData.is_community || 0),
                is_featured: Number(caseData.is_featured || 0),
                is_event: Number(caseData.is_event || 0)
              });
              await loadCaseItems(caseData.id);
              caseEditorModal?.show();
            }
            function openNewCaseModal(){
              caseEditorState.activeId = null;
              setCaseModalValues({
                id: null,
                title: '',
                basePriceUSD: 0,
                required_level: 1,
                tag: 'starter',
                risk: 'medium',
                slug: '',
                img: '',
                is_community: 1,
                is_featured: 0,
                is_event: 0
              });
              caseEditorModal?.show();
            }
            async function saveCaseFromModal(options){
              const opts = options || {};
              const payload = {
                id: $('#caseModalId')?.value ? Number($('#caseModalId').value) : null,
                title: $('#caseModalTitleInput')?.value || '',
                basePriceUSD: Number($('#caseModalPriceInput')?.value || 0),
                required_level: Number($('#caseModalLevelInput')?.value || 1),
                tag: $('#caseModalTagInput')?.value || 'starter',
                risk: $('#caseModalRiskInput')?.value || 'medium',
                slug: $('#caseModalSlugInput')?.value || '',
                img: $('#caseModalImgInput')?.value || '',
                is_community: $('#caseModalCommunityInput')?.checked ? 1 : 0,
                is_featured: $('#caseModalFeaturedInput')?.checked ? 1 : 0
              };
              const desiredSlug = payload.slug || slugifyText(payload.title);
              const r = await apiJson('/index.php?page=api&action=saveCase', payload);
              if (!r.ok) { if (!opts.silent) toast(t('error.generic'),'error'); return false; }
              await loadAll();
              if (!opts.silent) toast(t('Saved'));
              const savedCase = caseOptions.find(it => String(it.id) === String(payload.id))
                || caseOptions.find(it => (it.slug || '') === desiredSlug && (it.title || '') === payload.title)
                || caseOptions[0];
              if (savedCase?.id && opts.reopen !== false) await openCaseModal(savedCase.id);
              return true;
            }
            async function uploadCaseImageFile(file){
              if (!file) return;
              const fd = new FormData();
              fd.append('image', file);
              try {
                const resp = await fetch('/index.php?page=api&action=uploadCaseImage', { method:'POST', body: fd });
                const data = await resp.json().catch(()=>({}));
                if (!resp.ok || !data?.path) throw new Error(data?.error || 'upload_failed');
                if ($('#caseModalImgInput')) $('#caseModalImgInput').value = data.path;
                if ($('#caseModalImgPreview')) $('#caseModalImgPreview').src = data.path;
                const currentCaseId = Number($('#caseModalId')?.value || 0);
                if (currentCaseId > 0) {
                  await saveCaseFromModal({ silent: true, reopen: true });
                }
                toast(t('Saved'));
              } catch {
                toast(t('error.generic'), 'error');
              }
            }
            document.addEventListener('click', async (e)=>{
              const viewProductBtn = e.target.closest('button[data-view-product]');
              if (viewProductBtn) {
                openProductPreview(viewProductBtn.getAttribute('data-view-product'));
                return;
              }
              const editProductBtn = e.target.closest('button[data-edit-product]');
              if (editProductBtn) {
                openProductModal(editProductBtn.getAttribute('data-edit-product'), false);
                return;
              }
              const viewBadgeBtn = e.target.closest('button[data-view-badge]');
              if (viewBadgeBtn) {
                openBadgePreview(viewBadgeBtn.getAttribute('data-view-badge'));
                return;
              }
              const viewCategoryBtn = e.target.closest('button[data-view-category]');
              if (viewCategoryBtn) {
                openCategoryPreview(viewCategoryBtn.getAttribute('data-view-category'));
                return;
              }
              const editCategoryBtn = e.target.closest('button[data-edit-category]');
              if (editCategoryBtn) {
                openCategoryModal(editCategoryBtn.getAttribute('data-edit-category'), false);
                return;
              }
              const removeCategoryBtn = e.target.closest('button[data-remove-category]');
              if (removeCategoryBtn) {
                openCategoryModal(removeCategoryBtn.getAttribute('data-remove-category'), false);
                return;
              }
              const viewBundleBtn = e.target.closest('button[data-view-bundle]');
              if (viewBundleBtn) {
                await openBundlePreview(viewBundleBtn.getAttribute('data-view-bundle'));
                return;
              }
              const editBundleBtn = e.target.closest('button[data-edit-bundle]');
              if (editBundleBtn) {
                await openBundleModal(editBundleBtn.getAttribute('data-edit-bundle'), false);
                return;
              }
              const removeBundleBtn = e.target.closest('button[data-remove-bundle]');
              if (removeBundleBtn) {
                await openBundleModal(removeBundleBtn.getAttribute('data-remove-bundle'), false);
                return;
              }
              const editBadgeBtn = e.target.closest('button[data-edit-badge]');
              if (editBadgeBtn) {
                openBadgeModal(editBadgeBtn.getAttribute('data-edit-badge'), false);
                return;
              }
              const openBtn = e.target.closest('button[data-open-case]');
              if (openBtn) {
                await openCaseModal(openBtn.getAttribute('data-open-case'));
                return;
              }
              const openItemsBtn = e.target.closest('button[data-open-case-items]');
              if (openItemsBtn) {
                await openCasePreview(openItemsBtn.getAttribute('data-open-case-items'));
                return;
              }
              const btn = e.target.closest('button[data-save]');
              if (btn) {
                const kind = btn.getAttribute('data-save'); const id = btn.getAttribute('data-id');
                const row = btn.closest('tr');
                if (kind==='prod'){
                  const rowName = row?.querySelector(`input[data-f="name"][data-id="${id}"]`);
                  const rowPrice = row?.querySelector(`input[data-f="price"][data-id="${id}"]`);
                  const rowCategory = row?.querySelector(`select[data-f="category_id"][data-id="${id}"]`);
                  const payload = { id: id?Number(id):null, name: rowName?.value||'', priceUSD: Number(rowPrice?.value||0), category_id: rowCategory?.value || '' };
                  const r = await apiJson('/index.php?page=api&action=saveProduct', payload);
                  if (r.ok) { toast(t('Saved')); loadAll(); } else toast(t('error.generic'),'error');
                } else if (kind==='bundleItem'){
                  const bundleId = Number($('#bundleModalId')?.value || bundleEditorState.activeId || 0);
                  const sourceType = row?.querySelector(`select[data-f="source_type"][data-id="${id}"]`)?.value || 'offer';
                  const sourceRef = row?.querySelector(`select[data-f="source_ref"][data-id="${id}"]`)?.value || '';
                  const payload = {
                    id: id?Number(id):null,
                    bundle_id: bundleId,
                    source_type: sourceType,
                    offer_id: sourceType === 'offer' ? sourceRef : '',
                    source_category_id: sourceType === 'category' ? sourceRef : '',
                    source_case_id: sourceType === 'case' ? sourceRef : '',
                    source_bundle_id: sourceType === 'bundle' ? sourceRef : '',
                    quantity: Number(row?.querySelector(`input[data-f="quantity"][data-id="${id}"]`)?.value || 1)
                  };
                  const r = await apiJson('/index.php?page=api&action=saveProductBundleItem', payload);
                  if (r.ok) { toast(t('Saved')); loadBundleItems(bundleId); } else toast(t('error.generic'),'error');
                } else if (kind==='caseItem'){
                  const caseId = Number($('#caseModalId')?.value||0);
                  const sourceType = row?.querySelector(`select[data-f="source_type"][data-id="${id}"]`)?.value || 'offer';
                  const sourceRef = row?.querySelector(`select[data-f="source_ref"][data-id="${id}"]`)?.value || '';
                  const payload = {
                    id: id?Number(id):null,
                    case_id: caseId,
                    source_type: sourceType,
                    offer_id: sourceType === 'offer' ? sourceRef : '',
                    category_id: sourceType === 'category' ? sourceRef : '',
                    bundle_id: sourceType === 'bundle' ? sourceRef : '',
                    valueUSD: Number(row?.querySelector(`input[data-f="value"][data-id="${id}"]`)?.value || 0),
                    weight: Number(row?.querySelector(`input[data-f="weight"][data-id="${id}"]`)?.value || 1)
                  };
                  const r = await apiJson('/index.php?page=api&action=saveCaseItem', payload);
                  if (r.ok) { toast(t('Saved')); loadCaseItems(caseId); } else toast(t('error.generic'),'error');
                } else if (kind==='caseMetaOption'){
                  const type = btn.getAttribute('data-meta-type');
                  const payload = {
                    id: id ? Number(id) : null,
                    type,
                    value: row?.querySelector(`input[data-meta-type="${type}"][data-f="value"][data-id="${id}"]`)?.value || '',
                    label: row?.querySelector(`input[data-meta-type="${type}"][data-f="label"][data-id="${id}"]`)?.value || '',
                    sort_order: Number(row?.querySelector(`input[data-meta-type="${type}"][data-f="sort_order"][data-id="${id}"]`)?.value || 0)
                  };
                  const r = await apiJson('/index.php?page=api&action=saveCaseMetaOption', payload);
                  if (r.ok) { toast(t('Saved')); loadAll(); } else toast(t('error.generic'),'error');
                } else if (kind==='badge'){
                  const rowCode = row?.querySelector(`input[data-f="code"][data-id="${id}"]`);
                  const rowName = row?.querySelector(`input[data-f="name"][data-id="${id}"]`);
                  const payload = { id: id?Number(id):null, code: rowCode?.value||'', name: rowName?.value||'' };
                  const r = await apiJson('/index.php?page=api&action=saveBadge', payload);
                  if (r.ok) { toast(t('Saved')); loadAll(); } else toast(t('error.generic'),'error');
                }
                return;
              }
              const delBtn = e.target.closest('button[data-delete]');
              if (!delBtn) return;
              const kind = delBtn.getAttribute('data-delete'); const id = Number(delBtn.getAttribute('data-id')||0);
              if (!id) return;
              let confirmed = true;
              if (window.Swal) {
                const res = await Swal.fire({
                  icon: 'warning',
                  title: t('Delete item?'),
                  text: t('This action cannot be easily undone.'),
                  showCancelButton: true,
                  confirmButtonText: t('Delete'),
                  cancelButtonText: t('Cancel')
                });
                confirmed = !!res.isConfirmed;
              }
              if (!confirmed) return;
              let action = '';
              if (kind === 'prod') action = 'deleteProduct';
              else if (kind === 'productCategory') action = 'deleteProductCategory';
              else if (kind === 'productBundle') action = 'deleteProductBundle';
              else if (kind === 'bundleItem') action = 'deleteProductBundleItem';
              else if (kind === 'case') action = 'deleteCase';
              else if (kind === 'caseItem') action = 'deleteCaseItem';
              else if (kind === 'caseMetaOption') action = 'deleteCaseMetaOption';
              else if (kind === 'badge') action = 'deleteBadge';
              if (!action) return;
              const r = await apiJson('/index.php?page=api&action=' + action, { id });
              if (r.ok) {
                toast(t('Deleted'));
                if (kind === 'caseItem') loadCaseItems(caseEditorState.activeId);
                else if (kind === 'bundleItem') loadBundleItems();
                else if (kind === 'caseMetaOption') loadAll();
                else if (kind === 'case') {
                  caseEditorModal?.hide();
                  loadAll();
                } else loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#prodNewBtn')?.addEventListener('click', ()=>{ openProductModal('', false); });
            $('#caseNewBtn')?.addEventListener('click', ()=>{ openNewCaseModal(); });
            $('#badgeNewBtn')?.addEventListener('click', ()=>{ openBadgeModal('', false); });
            $('#prodPerPage')?.addEventListener('change', ()=>{ catalogPager.prod.perPage = Number($('#prodPerPage')?.value || 10); catalogPager.prod.page = 1; renderProducts(productOptions); });
            $('#casePerPage')?.addEventListener('change', ()=>{ catalogPager.case.perPage = Number($('#casePerPage')?.value || 10); catalogPager.case.page = 1; renderCases(caseOptions); });
            $('#badgePerPage')?.addEventListener('change', ()=>{ catalogPager.badge.perPage = Number($('#badgePerPage')?.value || 10); catalogPager.badge.page = 1; renderBadges(badgeOptions); });
            $('#categoryPerPage')?.addEventListener('change', ()=>{ catalogPager.category.perPage = Number($('#categoryPerPage')?.value || 10); catalogPager.category.page = 1; renderProductCategories(productCategories); });
            $('#bundlePerPage')?.addEventListener('change', ()=>{ catalogPager.bundle.perPage = Number($('#bundlePerPage')?.value || 10); catalogPager.bundle.page = 1; renderProductBundles(productBundles); });
            $('#prodPrev')?.addEventListener('click', ()=>{ catalogPager.prod.page = Math.max(1, catalogPager.prod.page - 1); renderProducts(productOptions); });
            $('#prodNext')?.addEventListener('click', ()=>{ catalogPager.prod.page += 1; renderProducts(productOptions); });
            $('#casePrev')?.addEventListener('click', ()=>{ catalogPager.case.page = Math.max(1, catalogPager.case.page - 1); renderCases(caseOptions); });
            $('#caseNext')?.addEventListener('click', ()=>{ catalogPager.case.page += 1; renderCases(caseOptions); });
            $('#badgePrev')?.addEventListener('click', ()=>{ catalogPager.badge.page = Math.max(1, catalogPager.badge.page - 1); renderBadges(badgeOptions); });
            $('#badgeNext')?.addEventListener('click', ()=>{ catalogPager.badge.page += 1; renderBadges(badgeOptions); });
            $('#categoryPrev')?.addEventListener('click', ()=>{ catalogPager.category.page = Math.max(1, catalogPager.category.page - 1); renderProductCategories(productCategories); });
            $('#categoryNext')?.addEventListener('click', ()=>{ catalogPager.category.page += 1; renderProductCategories(productCategories); });
            $('#bundlePrev')?.addEventListener('click', ()=>{ catalogPager.bundle.page = Math.max(1, catalogPager.bundle.page - 1); renderProductBundles(productBundles); });
            $('#bundleNext')?.addEventListener('click', ()=>{ catalogPager.bundle.page += 1; renderProductBundles(productBundles); });
            $('#productCategoryNewBtn')?.addEventListener('click', ()=>{ openCategoryModal('', false); });
            $('#productBundleNewBtn')?.addEventListener('click', ()=>{ openBundleModal('', false); });
            $('#bundleItemNewBtn')?.addEventListener('click', async ()=>{
              const bundleId = Number($('#bundleModalId')?.value || bundleEditorState.activeId || 0);
              if (!bundleId || !productOptions.length) return;
              await apiJson('/index.php?page=api&action=saveProductBundleItem', { bundle_id: bundleId, source_type:'offer', offer_id: productOptions[0].id, quantity: 1 });
              loadBundleItems(bundleId);
            });
            $('#caseTagOptionNewBtn')?.addEventListener('click', async ()=>{
              await apiJson('/index.php?page=api&action=saveCaseMetaOption', { type:'tag', value:`new-tag-${Date.now()}`, label:t('New Tag'), sort_order: 999 });
              loadAll();
            });
            $('#caseRiskOptionNewBtn')?.addEventListener('click', async ()=>{
              await apiJson('/index.php?page=api&action=saveCaseMetaOption', { type:'risk', value:`new-risk-${Date.now()}`, label:t('New Risk'), sort_order: 999 });
              loadAll();
            });
            $('#productModalSaveBtn')?.addEventListener('click', async ()=>{
              const payload = {
                id: $('#productModalId')?.value ? Number($('#productModalId').value) : null,
                name: $('#productModalNameInput')?.value || '',
                category_id: $('#productModalCategoryInput')?.value || '',
                product_type: $('#productModalTypeInput')?.value || 'product',
                use_home_spinner: $('#productModalSpinnerInput')?.checked ? 1 : 0,
                priceUSD: Number($('#productModalPriceInput')?.value || 0),
                valid_from: $('#productModalValidFromInput')?.value ? $('#productModalValidFromInput').value.replace('T', ' ') + ':00' : '',
                valid_until: $('#productModalValidUntilInput')?.value ? $('#productModalValidUntilInput').value.replace('T', ' ') + ':00' : '',
                description: $('#productModalDescriptionInput')?.value || ''
              };
              const r = await apiJson('/index.php?page=api&action=saveProduct', payload);
              if (r.ok) {
                toast(t('Saved'));
                productEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#productModalDeleteBtn')?.addEventListener('click', async ()=>{
              const id = Number($('#productModalId')?.value || 0);
              if (!id) return;
              const r = await apiJson('/index.php?page=api&action=deleteProduct', { id });
              if (r.ok) {
                toast(t('Deleted'));
                productEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#badgeModalSaveBtn')?.addEventListener('click', async ()=>{
              const payload = {
                id: $('#badgeModalId')?.value ? Number($('#badgeModalId').value) : null,
                code: $('#badgeModalCodeInput')?.value || '',
                name: $('#badgeModalNameInput')?.value || ''
              };
              const r = await apiJson('/index.php?page=api&action=saveBadge', payload);
              if (r.ok) {
                toast(t('Saved'));
                badgeEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#badgeModalDeleteBtn')?.addEventListener('click', async ()=>{
              const id = Number($('#badgeModalId')?.value || 0);
              if (!id) return;
              const r = await apiJson('/index.php?page=api&action=deleteBadge', { id });
              if (r.ok) {
                toast(t('Deleted'));
                badgeEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#categoryModalSaveBtn')?.addEventListener('click', async ()=>{
              const payload = {
                id: $('#categoryModalId')?.value ? Number($('#categoryModalId').value) : null,
                name: $('#categoryModalNameInput')?.value || '',
                slug: $('#categoryModalSlugInput')?.value || ''
              };
              const r = await apiJson('/index.php?page=api&action=saveProductCategory', payload);
              if (r.ok) {
                toast(t('Saved'));
                categoryEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#categoryModalDeleteBtn')?.addEventListener('click', async ()=>{
              const id = Number($('#categoryModalId')?.value || 0);
              if (!id) return;
              const r = await apiJson('/index.php?page=api&action=deleteProductCategory', { id });
              if (r.ok) {
                toast(t('Deleted'));
                categoryEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#bundleModalSaveBtn')?.addEventListener('click', async ()=>{
              const payload = {
                id: $('#bundleModalId')?.value ? Number($('#bundleModalId').value) : null,
                name: $('#bundleModalNameInput')?.value || ''
              };
              const r = await apiJson('/index.php?page=api&action=saveProductBundle', payload);
              if (r.ok) {
                const data = await r.json().catch(()=>({}));
                const savedId = Number((data && data.id) || payload.id || 0);
                toast(t('Saved'));
                await loadAll();
                if (savedId > 0) await openBundleModal(savedId, false);
                else bundleEditorModal?.hide();
              } else toast(t('error.generic'), 'error');
            });
            $('#bundleModalDeleteBtn')?.addEventListener('click', async ()=>{
              const id = Number($('#bundleModalId')?.value || 0);
              if (!id) return;
              const r = await apiJson('/index.php?page=api&action=deleteProductBundle', { id });
              if (r.ok) {
                toast(t('Deleted'));
                bundleEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#caseModalSaveBtn')?.addEventListener('click', async ()=>{ await saveCaseFromModal(); });
            $('#caseModalDeleteBtn')?.addEventListener('click', async ()=>{
              const caseId = Number($('#caseModalId')?.value || 0);
              if (!caseId) return;
              const r = await apiJson('/index.php?page=api&action=deleteCase', { id: caseId });
              if (r.ok) {
                toast(t('Deleted'));
                caseEditorModal?.hide();
                loadAll();
              } else toast(t('error.generic'), 'error');
            });
            $('#caseModalNewItemBtn')?.addEventListener('click', async ()=>{
              const caseId = Number($('#caseModalId')?.value||0);
              if (!caseId) return;
              const firstProductId = productOptions[0]?.id || '';
              if (!firstProductId) return;
              await apiJson('/index.php?page=api&action=saveCaseItem', { case_id: caseId, source_type:'offer', offer_id: firstProductId, valueUSD: 0, weight: 1 });
              loadCaseItems(caseId);
            });
            $('#caseModalImgEditBtn')?.addEventListener('click', ()=>{ $('#caseModalImgFileInput')?.click(); });
            $('#caseModalImgPreview')?.addEventListener('click', ()=>{ $('#caseModalImgFileInput')?.click(); });
            $('#caseModalImgFileInput')?.addEventListener('change', async ()=>{
              const file = $('#caseModalImgFileInput')?.files?.[0];
              await uploadCaseImageFile(file);
              if ($('#caseModalImgFileInput')) $('#caseModalImgFileInput').value = '';
            });
            document.addEventListener('DOMContentLoaded', ()=>{
              const productModalEl = $('#productEditorModal');
              if (productModalEl && document.body) document.body.appendChild(productModalEl);
              if (productModalEl && window.bootstrap) productEditorModal = bootstrap.Modal.getOrCreateInstance(productModalEl);
              const badgeModalEl = $('#badgeEditorModal');
              if (badgeModalEl && document.body) document.body.appendChild(badgeModalEl);
              if (badgeModalEl && window.bootstrap) badgeEditorModal = bootstrap.Modal.getOrCreateInstance(badgeModalEl);
              const categoryModalEl = $('#categoryEditorModal');
              if (categoryModalEl && document.body) document.body.appendChild(categoryModalEl);
              if (categoryModalEl && window.bootstrap) categoryEditorModal = bootstrap.Modal.getOrCreateInstance(categoryModalEl);
              const bundleModalEl = $('#bundleEditorModal');
              if (bundleModalEl && document.body) document.body.appendChild(bundleModalEl);
              if (bundleModalEl && window.bootstrap) bundleEditorModal = bootstrap.Modal.getOrCreateInstance(bundleModalEl);
              const modalEl = $('#caseEditorModal');
              if (modalEl && document.body) document.body.appendChild(modalEl);
              if (modalEl && window.bootstrap) {
                caseEditorModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalEl.addEventListener('shown.bs.modal', ()=>{
                  initCaseMetaSelects();
                  try {
                    window.jQuery('#caseModalTagInput, #caseModalRiskInput').off('select2:open.codex').on('select2:open.codex', () => {
                      setTimeout(() => {
                        try { document.querySelector('.select2-container--open .select2-search__field')?.focus(); } catch {}
                      }, 0);
                    });
                  } catch {}
                });
              }
              loadAll();
            });
            document.addEventListener('change', (e)=>{
              const sel = e.target.closest('select[data-f="source_type"]');
              if (!sel) return;
              if (sel.classList.contains('bundle-source-type-select')) updateBundleSourceOptions(sel.closest('tr'));
              else updateCaseSourceOptions(sel.closest('tr'));
            });
          })();
          </script>
        </div>
        <div class="tab-pane fade" id="paneBuilder" role="tabpanel">
          <div class="row g-3">
            <div class="col-lg-4">
              <div class="card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="h6 mb-1"><?= ___('Page Builder') ?></div>
                    <div class="small text-muted"><?= ___('Turn pages and menu items on or off, then compose pages from reusable modules.') ?></div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="small text-muted d-block mb-1"><?= ___('Page') ?></label>
                  <select id="builderPageSelect" class="form-select formControlDark"></select>
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="small text-muted d-block mb-1"><?= ___('Page enabled') ?></label>
                    <label class="prettySwitch mb-0"><input type="checkbox" id="builderEnabled"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  </div>
                  <div class="col-6">
                    <label class="small text-muted d-block mb-1"><?= ___('Show in menu') ?></label>
                    <label class="prettySwitch mb-0"><input type="checkbox" id="builderShowInNav"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  </div>
                  <div class="col-4">
                    <label class="small text-muted d-block mb-1"><?= ___('Guest') ?></label>
                    <label class="prettySwitch mb-0"><input type="checkbox" id="builderGuestEnabled"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  </div>
                  <div class="col-4">
                    <label class="small text-muted d-block mb-1"><?= ___('User') ?></label>
                    <label class="prettySwitch mb-0"><input type="checkbox" id="builderUserEnabled"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  </div>
                  <div class="col-4">
                    <label class="small text-muted d-block mb-1"><?= ___('Admin') ?></label>
                    <label class="prettySwitch mb-0"><input type="checkbox" id="builderAdminEnabled"><span class="switchTrack"><span class="switchThumb"></span></span></label>
                  </div>
                </div>
                <div class="mt-3">
                  <label class="small text-muted d-block mb-1"><?= ___('Menu label') ?></label>
                  <input id="builderNavLabel" class="form-control formControlDark" />
                </div>
                <div class="mt-3">
                  <label class="small text-muted d-block mb-1"><?= ___('Menu href') ?></label>
                  <input id="builderNavHref" class="form-control formControlDark" />
                </div>
                <div class="mt-3">
                  <button class="btn btnPrimary adminIconBtn adminIconBtnWide w-100" id="builderSaveAccessBtn" title="<?= ___('Save access rules') ?>" aria-label="<?= ___('Save access rules') ?>">
                    <span class="adminGlyph" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>
                    </span>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="card p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="h6 mb-1"><?= ___('Module library') ?></div>
                    <div class="small text-muted"><?= ___('Add reusable modules to the selected page. Drag the layout to reorder.') ?></div>
                  </div>
                </div>
                <div class="row g-2" id="builderModuleLibrary"></div>
              </div>
              <div class="card p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <div class="h6 mb-1"><?= ___('Page layout') ?></div>
                    <div class="small text-muted"><?= ___('Each block is a module instance. Keep the layout lean to preserve page speed.') ?></div>
                  </div>
                  <button class="btn btnPrimary adminIconBtn" id="builderSaveLayoutBtn" title="<?= ___('Save layout') ?>" aria-label="<?= ___('Save layout') ?>">
                    <span class="adminGlyph" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>
                    </span>
                  </button>
                </div>
                <div id="builderLayoutCanvas" class="d-grid gap-2"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="paneLang" role="tabpanel">
          <h2 class="sectionTitle h6 mb-3"><?= ___('Languages') ?> (EN ⇄ HU) — <?= ___('Database editor') ?></h2>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="small text-muted"><?= ___('Edit translations stored in the database. Tip: click the pencil icon, change the value, and it saves automatically when the field loses focus.') ?></div>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-light" id="langSyncBtn"><?= ___('Fill missing HU from EN') ?></button>
            </div>
          </div>
          <div class="card p-2">
            <div class="table-responsive">
              <table class="table table-dark table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-muted small">Kulcs<br><input type="text" class="form-control formControlDark form-control-sm" id="fltKey" placeholder="Szűrés…"></th>
                    <th class="text-muted small">EN<br><input type="text" class="form-control formControlDark form-control-sm" id="fltEn" placeholder="Szűrés…"></th>
                    <th class="text-muted small">HU<br><input type="text" class="form-control formControlDark form-control-sm" id="fltHu" placeholder="Szűrés…"></th>
                    <th class="text-muted small">Forrás<br><input type="text" class="form-control formControlDark form-control-sm" id="fltSrc" placeholder="Szűrés…"></th>
                    <th class="text-muted small">Szerk.</th>
                  </tr>
                </thead>
                <tbody id="langTableBody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="paneContent" role="tabpanel">
          <h2 class="sectionTitle h6 mb-3"><?= ___('Szekció tartalom szerkesztő') ?> (<?= ___('Header/Footer és oldal szekciók') ?>)</h2>
          <div class="card p-2 mb-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div>
                <label class="small text-muted mb-1 d-block"><?= ___('Oldal') ?></label>
                <select id="cntPage" class="form-select form-select-sm formControlDark" style="min-width:200px">
                  <option value="global">global</option>
                  <option value="home">home</option>
                  <option value="events">events</option>
                  <option value="missions">missions</option>
                  <option value="exchange">exchange</option>
                  <option value="dashboard">dashboard</option>
                </select>
              </div>
              <div>
                <label class="small text-muted mb-1 d-block"><?= ___('Szekció') ?></label>
                <select id="cntSection" class="form-select form-select-sm formControlDark" style="min-width:200px">
                  <option value="header">header</option>
                  <option value="footer">footer</option>
                  <option value="intro">intro</option>
                  <option value="sidebar">sidebar</option>
                </select>
              </div>
              <div>
                <label class="small text-muted mb-1 d-block"><?= ___('Nyelv') ?></label>
                <select id="cntLocale" class="form-select form-select-sm formControlDark" style="min-width:120px">
                  <option value="en">EN</option>
                  <option value="hu">HU</option>
                </select>
              </div>
              <button class="btn btn-sm btn-outline-light" id="cntLoadBtn"><?= ___('Betöltés') ?></button>
              <button class="btn btn-sm btnPrimary adminIconBtn" id="cntSaveBtn" title="<?= ___('Mentés') ?>" aria-label="<?= ___('Mentés') ?>">
                <span class="adminGlyph" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>
                </span>
              </button>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="card p-2">
                <div class="small text-muted mb-1"><?= ___('Cím') ?></div>
                <input id="cntTitle" class="form-control formControlDark" placeholder="Title" />
              </div>
            </div>
            <div class="col-md-8">
              <div class="card p-2">
                <div class="small text-muted mb-1"><?= ___('Tartalom (HTML)') ?></div>
                <textarea id="cntBody" class="form-control formControlDark" rows="10" placeholder="<p>...</p>"></textarea>
              </div>
            </div>
          </div>
          <div class="card p-2 mt-3">
          </div>
        </div>
        <div class="tab-pane fade" id="paneEvents" role="tabpanel">
          <h2 class="sectionTitle h6 mb-3"><?= ___('Events') ?></h2>
          <div class="card p-2 mt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="small text-muted"><?= ___('Manage events') ?></div>
              <button class="btn btn-sm btnPrimary" id="eventNewBtn"><?= ___('New') ?></button>
            </div>
            <div class="table-responsive">
              <table class="table table-dark table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th><?= ___('Start') ?></th>
                    <th><?= ___('End') ?></th>
                    <th><?= ___('Title') ?></th>
                    <th><?= ___('Description') ?></th>
                    <th><?= ___('Actions') ?></th>
                  </tr>
                </thead>
                <tbody id="eventBody"></tbody>
              </table>
            </div>
          </div>
          <div class="modal fade" id="eventEditorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                  <h3 class="h5 mb-0" id="eventEditorTitle"><?= ___('Event editor') ?></h3>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="eventModalId">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Start') ?></label>
                      <input id="eventModalStartInput" type="datetime-local" class="form-control formControlDark" />
                    </div>
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('End') ?></label>
                      <input id="eventModalEndInput" type="datetime-local" class="form-control formControlDark" />
                    </div>
                    <div class="col-md-4">
                      <label class="small text-muted d-block mb-1"><?= ___('Color') ?></label>
                      <input id="eventModalColorInput" type="color" class="form-control formControlDark form-control-color" value="#7c4dff" />
                    </div>
                    <div class="col-12">
                      <label class="small text-muted d-block mb-1"><?= ___('Title') ?></label>
                      <input id="eventModalTitleInput" class="form-control formControlDark" placeholder="<?= ___('Event title') ?>" />
                    </div>
                    <div class="col-12">
                      <label class="small text-muted d-block mb-1"><?= ___('Description') ?></label>
                      <textarea id="eventModalDescriptionInput" class="form-control formControlDark" rows="4" placeholder="<?= ___('Event description') ?>"></textarea>
                    </div>
                    <div class="col-12">
                      <label class="small text-muted d-block mb-1"><?= ___('Link') ?></label>
                      <input id="eventModalHrefInput" class="form-control formControlDark" placeholder="https://example.com/event" />
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-secondary d-flex justify-content-between">
                  <button type="button" class="btn btn-outline-danger" id="eventModalDeleteBtn"><?= ___('Delete') ?></button>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Close') ?></button>
                    <button type="button" class="btn btnPrimary" id="eventModalSaveBtn"><?= ___('Save') ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <script>
            (function(){
              const $ = (sel)=>document.querySelector(sel);
              const qs = $;
              let eventEditorModal = null;
              let events = [];
              function contentToast(msg, type){ try { Swal.fire({ icon:type||'success', title:msg, timer:1200, showConfirmButton:false }); } catch {} }
              function loadList(){
                const page = qs('#cntPage').value;
                fetch('/index.php?page=api&action=listContentSections&pageKey='+encodeURIComponent(page))
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(d=>{ /* Could list versions; for now, just keep editor fields */ })
                  .catch(()=>{});
              }
              function renderEvents(){
                const body = qs('#eventBody');
                if (!body) return;
                body.innerHTML = '';
                events.forEach((ev)=>{
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${ev.start_at||ev.date||''}</td><td>${ev.end_at||ev.date||''}</td><td>${ev.title||''}</td><td>${ev.description||''}</td><td style="width:1%">${iconButtons([
                    iconBtn({ title:t('Edit'), attrs:`data-edit-event="${ev.id||''}"`, icon:iconSvg('edit') }),
                    iconBtn({ tone:'outline-danger', title:t('Delete'), attrs:`data-delete-event="${ev.id||''}"`, icon:iconSvg('delete') }),
                  ])}</td>`;
                  body.appendChild(tr);
                });
                if (events.length === 0) {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td colspan="5" class="text-muted small"><?= ___('No events to show') ?></td>`;
                  body.appendChild(tr);
                }
              }
              function loadEvents(){
                fetch('/index.php?page=api&action=listEvents')
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(d=>{ events = Array.isArray(d.items) ? d.items : []; renderEvents(); })
                  .catch(()=>{ events = []; renderEvents(); });
              }
              function setEventModalValues(ev){
                qs('#eventModalId').value = ev.id ? String(ev.id) : '';
                qs('#eventModalStartInput').value = ev.start_at ? String(ev.start_at).replace(' ', 'T').slice(0,16) : (ev.date ? `${ev.date}T00:00` : '');
                qs('#eventModalEndInput').value = ev.end_at ? String(ev.end_at).replace(' ', 'T').slice(0,16) : (ev.date ? `${ev.date}T23:59` : '');
                qs('#eventModalTitleInput').value = ev.title || '';
                qs('#eventModalDescriptionInput').value = ev.description || '';
                qs('#eventModalHrefInput').value = ev.href || '';
                qs('#eventModalColorInput').value = ev.color || '#7c4dff';
                qs('#eventEditorTitle').textContent = ev.id ? `<?= ___('Edit') ?> - ${ev.title || ('#' + ev.id)}` : `<?= ___('New') ?>`;
                qs('#eventModalDeleteBtn').classList.toggle('d-none', !ev.id);
              }
              function setEditor(val){
                try {
                  if (window.tinymce?.get('cntBody')) {
                    tinymce.get('cntBody').setContent(val||'');
                  } else {
                    qs('#cntBody').value = val||'';
                  }
                } catch {
                  qs('#cntBody').value = val||'';
                }
              }
              function getEditor(){ try { return window.tinymce?.get('cntBody') ? tinymce.get('cntBody').getContent() : (qs('#cntBody').value||''); } catch { return qs('#cntBody').value||''; } }
              qs('#cntLoadBtn')?.addEventListener('click', ()=>{
                const page = qs('#cntPage').value; const section = qs('#cntSection').value; const locale = qs('#cntLocale').value;
                fetch('/index.php?page=api&action=listContentSections&pageKey='+encodeURIComponent(page))
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(d=>{
                    const items = Array.isArray(d.items)?d.items:[];
                    const hit = items.find(x=>x.page===page && x.section===section && x.locale===locale);
                    qs('#cntTitle').value = hit?.title || '';
                    setEditor(hit?.body || '');
                  })
                  .catch(()=>{ qs('#cntTitle').value=''; setEditor(''); });
              });
              qs('#cntSaveBtn')?.addEventListener('click', ()=>{
                const page = qs('#cntPage').value; const section = qs('#cntSection').value; const locale = qs('#cntLocale').value;
                const title = qs('#cntTitle').value || '';
                const body = getEditor();
                fetch('/index.php?page=api&action=saveContentSection', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ page, section, locale, title, body }) })
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(()=>{ try { Swal.fire({ icon:'success', title:'<?= ___('Saved') ?>', timer:1200, showConfirmButton:false }); } catch {} })
                  .catch(()=>{ try { Swal.fire({ icon:'error', title:'<?= ___('error.generic') ?>', timer:1500, showConfirmButton:false }); } catch {} });
              });
              qs('#eventNewBtn')?.addEventListener('click', ()=>{ setEventModalValues({ id:null, start_at:'', end_at:'', title:'', description:'', href:'', color:'#7c4dff' }); eventEditorModal?.show(); });
              qs('#eventModalSaveBtn')?.addEventListener('click', ()=>{
                fetch('/index.php?page=api&action=saveEvent', {
                  method:'POST',
                  headers:{'Content-Type':'application/json'},
                  body: JSON.stringify({
                    id: qs('#eventModalId').value ? Number(qs('#eventModalId').value) : null,
                    start_at: qs('#eventModalStartInput').value ? qs('#eventModalStartInput').value.replace('T', ' ') + ':00' : '',
                    end_at: qs('#eventModalEndInput').value ? qs('#eventModalEndInput').value.replace('T', ' ') + ':00' : '',
                    title: qs('#eventModalTitleInput').value || '',
                    description: qs('#eventModalDescriptionInput').value || '',
                    href: qs('#eventModalHrefInput').value || '',
                    color: qs('#eventModalColorInput').value || ''
                  })
                })
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(()=>{ contentToast('<?= ___('Saved') ?>'); eventEditorModal?.hide(); loadEvents(); })
                  .catch(()=>{ contentToast('<?= ___('error.generic') ?>', 'error'); });
              });
              qs('#eventModalDeleteBtn')?.addEventListener('click', ()=>{
                const id = Number(qs('#eventModalId').value || 0);
                if (!id) return;
                fetch('/index.php?page=api&action=deleteEvent', {
                  method:'POST',
                  headers:{'Content-Type':'application/json'},
                  body: JSON.stringify({ id })
                })
                  .then(r=>r.ok?r.json():Promise.reject())
                  .then(()=>{ contentToast('<?= ___('Deleted') ?>'); eventEditorModal?.hide(); loadEvents(); })
                  .catch(()=>{ contentToast('<?= ___('error.generic') ?>', 'error'); });
              });
              document.addEventListener('click', (e)=>{
                const editBtn = e.target.closest('button[data-edit-event]');
                if (editBtn) {
                  const ev = events.find(x => String(x.id) === String(editBtn.getAttribute('data-edit-event')));
                  if (ev) { setEventModalValues(ev); eventEditorModal?.show(); }
                  return;
                }
                const delBtn = e.target.closest('button[data-delete-event]');
                if (delBtn) {
                  const ev = events.find(x => String(x.id) === String(delBtn.getAttribute('data-delete-event')));
                  if (ev) { setEventModalValues(ev); }
                  qs('#eventModalDeleteBtn')?.click();
                }
              });
              document.addEventListener('DOMContentLoaded', ()=>{
                const eventModalEl = qs('#eventEditorModal');
                if (eventModalEl && document.body) document.body.appendChild(eventModalEl);
                if (eventModalEl && window.bootstrap) eventEditorModal = bootstrap.Modal.getOrCreateInstance(eventModalEl);
                loadEvents();
                try {
                  const params = new URLSearchParams(window.location.search || '');
                  if (params.get('tab') === 'events') {
                    document.getElementById('tabEvents')?.click();
                  }
                  if (params.get('open_event_editor') === '1') {
                    setEventModalValues({
                      id: null,
                      start_at: params.get('event_start') ? params.get('event_start').replace('T', ' ') + ':00' : '',
                      end_at: params.get('event_end') ? params.get('event_end').replace('T', ' ') + ':00' : '',
                      title: '',
                      description: '',
                      href: '',
                      color: '#7c4dff'
                    });
                    if (params.get('tab') === 'events') document.getElementById('tabEvents')?.click();
                    eventEditorModal?.show();
                  }
                } catch {}
              });
            })();
          </script>
        </div>
        <?php endif; ?>
      </div>
    </div>
    
  </div>
</section>

<!-- SweetAlert2 for toasts/messages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Load base translations from API (DB-backed) and re-render Languages table after load -->
<script>
  window.t = window.t || function t(key){
    return window.___ ? window.___(key) : key;
  };
  window.escapeHtml = window.escapeHtml || function escapeHtml(value){
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  };
  window.iconSvg = window.iconSvg || function iconSvg(name){
    const icons = {
      view: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.2 12s3.5-6 9.8-6 9.8 6 9.8 6-3.5 6-9.8 6-9.8-6-9.8-6Z"/><circle cx="12" cy="12" r="3.2"/></svg>',
      edit: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l4.1-.8L19 8.3 15.7 5 4.8 15.9 4 20Z"/><path d="M13.9 6.8 17.2 10"/></svg>',
      delete: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
      save: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v6h8V4"/><path d="M9 20v-6h6v6"/></svg>',
      duplicate: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h11v11H9z"/><path d="M4 4h11v11H4z"/></svg>',
      copy: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 9h10v11H9z"/><path d="M5 5h10v2"/><path d="M5 5v11h2"/></svg>',
      check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5 10 17l9-10"/></svg>',
    };
    return `<span class="adminGlyph">${icons[name] || icons.view}</span>`;
  };
  // Keep background refresh via API to include any new DB overrides
  window._baseTranslationsLoaded = true;
  fetch('/index.php?page=api&action=listTranslations')
    .then(r=> r.ok ? r.json() : Promise.reject())
    .then(d=>{ window._baseTranslations = (d && typeof d === 'object') ? d : {en:{},hu:{}}; window._baseTranslationsLoaded = true; try { if (typeof window.renderLangTable === 'function') window.renderLangTable(); } catch {} })
    .catch(()=>{ window._baseTranslationsLoaded = true; try { if (typeof window.renderLangTable === 'function') window.renderLangTable(); } catch {} });
</script>
<script>
  (function(){
  const bal = 0;
    let currentBalanceUsd = bal;
    const elBal = document.getElementById('dbBalance');
    const elOpened = document.getElementById('dbOpened');
    const elClaimed = document.getElementById('dbClaimed');
    const elSold = document.getElementById('dbSold');
    function setBalanceDisplayUSD(usd){
      const u = Number(usd)||0;
      currentBalanceUsd = u;
      let disp;
      if (typeof window.formatPriceUSDToDisplay === 'function') disp = window.formatPriceUSDToDisplay(u);
      else if (typeof window.formatPrice === 'function') disp = window.formatPrice(u);
      else disp = ('$'+u.toFixed(3));
      if (elBal) elBal.textContent = disp;
    }
    // Start with local fallback while loading server value
  setBalanceDisplayUSD(bal);
    // Try server wallet balance if logged in
    fetch('/index.php?page=api&action=walletBalance')
      .then(r=> r.ok ? r.json() : Promise.reject())
      .then(d=>{
        const hasMilli = d && d.wallet && typeof d.wallet.balance_milli !== 'undefined' && d.wallet.balance_milli !== null;
        const usd = hasMilli ? (Number(d.wallet.balance_milli||0)/1000) : (Number(d?.wallet?.balance_cents||0)/100);
        setBalanceDisplayUSD(usd);
      })
      .catch(()=>{});
    let history = [];
    // Wallet transactions (server only)
  let txs = [];
  let gemTxs = [];
    const txBody = document.getElementById('dbTxBody');
    const txPrev = document.getElementById('dbTxPrev');
    const txNext = document.getElementById('dbTxNext');
    const txInfo = document.getElementById('dbTxPageInfo');
    let txPage = 1; const txPageSize = 10;
    function renderTxPage(){
      if (!txBody) return;
      // Build merged view: wallet txs + gem txs mapped to a common shape
      const merged = [];
      // Wallet: has USD amount
      (txs||[]).forEach(tx=>{
        merged.push({
          created_at: tx.created_at,
          type: tx.type||'-',
          description: tx.description||'-',
          usd: (typeof tx.amount_milli !== 'undefined' && tx.amount_milli !== null) ? (Number(tx.amount_milli)/1000) : ((typeof tx.amount_cents !== 'undefined' && tx.amount_cents !== null) ? (Number(tx.amount_cents)/100) : 0),
          gems: 0
        });
      });
      // Gems: show as 0 USD but gem delta
      (gemTxs||[]).forEach(g=>{
        let t = (g.type||'').toLowerCase();
        // Normalize types to friendly labels
        if (t === 'award') t = 'claimed';
        if (t === 'spend') t = 'spend';
        merged.push({
          created_at: g.created_at,
          type: `gems:${t}`,
          description: g.description || (t==='claimed' ? 'Gems claimed' : 'Gems spend'),
          usd: 0,
          gems: Number(g.amount)||0
        });
      });
      // Sort by created_at desc
      merged.sort((a,b)=> new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
      const totalPages = Math.max(1, Math.ceil(merged.length/txPageSize));
      if (txPage > totalPages) txPage = totalPages;
      const start = (txPage-1)*txPageSize;
      const slice = merged.slice(start, start+txPageSize);
      txBody.innerHTML = '';
      if (slice.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="4" class="text-muted small">${window.___?___('No transactions to show'):'Nincs megjeleníthető tranzakció.'}</td>`;
        txBody.appendChild(tr);
      } else {
        slice.forEach(tx => {
          const tr = document.createElement('tr');
          const ts = tx.created_at ? new Date(tx.created_at) : new Date();
          const usd = Number(tx.usd)||0;
          const gems = Number(tx.gems)||0;
          const absDisp = (typeof window.formatPriceUSDToDisplay==='function') ? window.formatPriceUSDToDisplay(Math.abs(usd)) : ('$'+Math.abs(usd).toFixed(3));
          const amtHtml = usd > 0 ? `<span class="text-success">+${absDisp}</span>` : (usd < 0 ? `<span class="text-danger">-${absDisp}</span>` : `<span class="text-muted">${absDisp}</span>`);
          const type = (tx.type||'-');
          const desc = (tx.description||'-');
          // Gems badge
          const gemBadge = (gems !== 0) ? `<span class="badge ${gems>0?'bg-success':'bg-danger'} ms-1">${gems>0?'+':''}${(window.formatGems?window.formatGems(gems):String(gems))} 💎</span>` : '';
          tr.innerHTML = `<td>${ts.toLocaleString()}</td><td>${type}</td><td>${desc} ${gemBadge}</td><td>${amtHtml}</td>`;
          txBody.appendChild(tr);
        });
      }
      if (txInfo) txInfo.textContent = `${txPage} / ${Math.max(1, Math.ceil(merged.length/txPageSize))}`;
      if (txPrev) txPrev.disabled = (txPage<=1);
      if (txNext) txNext.disabled = (txPage>=Math.max(1, Math.ceil(merged.length/txPageSize)));
    }
    renderTxPage();
    txPrev?.addEventListener('click', ()=>{ if (txPage>1){ txPage--; renderTxPage(); } });
    txNext?.addEventListener('click', ()=>{ const max = Math.max(1, Math.ceil(txs.length/txPageSize)); if (txPage<max){ txPage++; renderTxPage(); } });
    // Load transactions from server
    // Load wallet and gem transactions in parallel, then render
    Promise.all([
      fetch('/index.php?page=api&action=walletTransactions').then(r=> r.ok ? r.json() : Promise.reject()).catch(()=>({items:[]})),
      fetch('/index.php?page=api&action=gemTransactions').then(r=> r.ok ? r.json() : Promise.reject()).catch(()=>({items:[]}))
    ]).then(([w,g])=>{
      if (Array.isArray(w.items)) txs = w.items; else txs = [];
      if (Array.isArray(g.items)) gemTxs = g.items; else gemTxs = [];
      renderTxPage();
    }).catch(()=>{ renderTxPage(); });
    // Try load from API (logged-in user). Only override if we actually got rows; otherwise keep LS fallback.
    fetch('/index.php?page=api&action=listHistory').then(r=> r.ok ? r.json() : Promise.reject()).then(d=>{
      if (Array.isArray(d.items) && d.items.length > 0) {
        history = d.items.map(x=>({
          ts: Date.parse(x.created_at),
          caseTitle: x.case_title,
          name: x.won_item_title,
          value: (x.won_value_cents||0)/100,
          status: x.status,
          coupon: x.coupon_code||null,
          soldAmountUSD: (typeof x.sold_amount_milli !== 'undefined' && x.sold_amount_milli !== null) ? ((Number(x.sold_amount_milli)||0)/1000) : (x.sold_amount_cents ? ((x.sold_amount_cents||0)/100) : undefined)
        }));
      }
      renderStats(); renderPage();
    }).catch(()=>{ renderStats(); renderPage(); });
    function renderStats(){
      if (elOpened) elOpened.textContent = String(history.length);
      if (elClaimed) elClaimed.textContent = String(history.filter(x=>x.status==='claimed').length);
      if (elSold) elSold.textContent = String(history.filter(x=>x.status==='sold').length);
    }

    const tbody = document.getElementById('dbHistoryBody');
    const prev = document.getElementById('dbPrev');
    const next = document.getElementById('dbNext');
    const pageInfo = document.getElementById('dbPageInfo');
    const pageSize = 10;
    let page = 1;
    function renderPage(){
      if (!tbody) return;
      tbody.innerHTML = '';
      const totalPages = Math.max(1, Math.ceil(history.length / pageSize));
      if (page > totalPages) page = totalPages;
      const start = (page - 1) * pageSize;
      const slice = history.slice(start, start + pageSize);
      slice.forEach((h)=>{
      const tr = document.createElement('tr');
      const d = new Date(h.ts||Date.now());
      // Settlement column shows both won value and payout if sold (gem wins have 0 elszámolás, ez szándékos)
      let settlementHtml = '-';
      const wonDisp = (typeof formatPrice === 'function') ? formatPrice(h.value||0) : ('$'+Number(h.value||0).toFixed(2));
      if (h.status === 'sold' && typeof h.soldAmountUSD === 'number') {
        const payoutDisp = (typeof formatPriceUSDToDisplay === 'function') ? formatPriceUSDToDisplay(h.soldAmountUSD) : ('$'+Number(h.soldAmountUSD).toFixed(3));
        settlementHtml = `${wonDisp} → ${payoutDisp}`;
      } else if (h.status === 'claimed') {
        settlementHtml = `${wonDisp}`;
      }
      // Change column: show +payout for sold, dash for claimed
      let changeHtml = '-';
      if (h.status === 'sold' && typeof h.soldAmountUSD === 'number') {
        const disp = (window.formatPriceUSDToDisplay ? formatPriceUSDToDisplay(h.soldAmountUSD) : ('$'+Number(h.soldAmountUSD).toFixed(3)));
        changeHtml = `<span class="text-success">+${disp}</span>`;
      } else if (h.status === 'claimed') {
        changeHtml = `<span class="text-muted">—</span>`;
      }
  // Coupon: csak akkor jelenjen meg, ha tényleges kód volt (gem esetén üres)
  const couponCode = (h.status === 'claimed' && h.coupon) ? h.coupon : '-';
  const couponCell = couponCode !== '-' ? `<div class="d-flex align-items-center gap-2"><code>${couponCode}</code><button class="btn btn-sm btn-outline-light adminIconBtn" data-copy="${couponCode}" title="${escapeHtml(t('Copy'))}" aria-label="${escapeHtml(t('Copy'))}">${iconSvg('copy')}</button></div>` : '<span class="text-muted">—</span>';
      tr.innerHTML = `<td>${d.toLocaleString()}</td><td>${h.caseTitle||'-'}</td><td>${h.name||'-'}</td><td>${settlementHtml}</td><td>${couponCell}</td><td>${changeHtml}</td><td>${h.status}</td>`;
      tbody.appendChild(tr);
      });
      // Wire copy buttons
      tbody.querySelectorAll('button[data-copy]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          const text = btn.getAttribute('data-copy');
          try {
            await navigator.clipboard.writeText(text);
            btn.innerHTML = iconSvg('check');
            setTimeout(()=>{ btn.innerHTML = iconSvg('copy'); }, 1200);
          } catch {
            showToast((window.___?___('Copy failed'):'Másolás nem sikerült.'), 'error');
          }
        });
      });
      // Update pager
      if (pageInfo) pageInfo.textContent = `${page} / ${Math.max(1, Math.ceil(history.length / pageSize))}`;
      if (prev) prev.disabled = (page <= 1);
      if (next) next.disabled = (page >= Math.max(1, Math.ceil(history.length / pageSize)));
    }
    renderPage();
    if (prev) prev.addEventListener('click', ()=>{ if (page>1) { page--; renderPage(); } });
  if (next) next.addEventListener('click', ()=>{ const max = Math.max(1, Math.ceil(history.length / pageSize)); if (page<max) { page++; renderPage(); } });
    document.addEventListener('pi.currencyChanged', ()=>{
      setBalanceDisplayUSD(currentBalanceUsd);
      renderTxPage();
      renderPage();
    });

  // profile
    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
    setVal('dbLocale', document.documentElement.lang || 'en');
    setVal('dbCurrency', window.state?.currency || 'USD');
  const companyToggle = document.getElementById('dbIsCompany');
  const vatWrap = document.getElementById('billVatWrap');
  function updateVatVisibility(){ if (!companyToggle) return; if (companyToggle.checked) { vatWrap?.classList.remove('d-none'); } else { vatWrap?.classList.add('d-none'); } }
  companyToggle?.addEventListener('change', updateVatVisibility);
    // Wire PrettySelect using shared helper
    try { if (typeof initPrettySelect === 'function') initPrettySelect(document); } catch {}
    const same = document.getElementById('shipSame');
    if (same) same.addEventListener('change', ()=>{
      const take = (id) => (document.getElementById(id)?.value||'');
      if (same.checked) {
        ['Name','City','Zip','Addr','Country'].forEach(k=>{ setVal('ship'+k, take('bill'+k)); });
      }
    });
    const saveBtn = document.getElementById('dbSaveProfile');
    if (saveBtn) saveBtn.addEventListener('click', ()=>{
      const getVal = (id) => (document.getElementById(id)?.value||'').trim();
      const newCur = getVal('dbCurrency') || 'USD';
      // push to server
      fetch('/index.php?page=api&action=saveProfile', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({
        display_name: getVal('dbDisplayName'),
        locale:getVal('dbLocale'),
        currency:newCur,
        is_company: companyToggle?.checked?1:0,
        vat_number:getVal('billVat'),
        billing_name:getVal('billName'),
        billing_city:getVal('billCity'),
        billing_zip:getVal('billZip'),
        billing_address:getVal('billAddr'),
        billing_country:getVal('billCountry'),
        shipping_name:getVal('shipName'),
        shipping_city:getVal('shipCity'),
        shipping_zip:getVal('shipZip'),
        shipping_address:getVal('shipAddr'),
        shipping_country:getVal('shipCountry')
      }) })
        .then(async r => {
          const data = await r.json().catch(()=>({}));
          if (!r.ok) throw new Error(data?.error || 'save_failed');
          return data;
        })
        .then(()=>{ showToast((window.___?___('Saved'):'Mentve.'), 'success'); })
        .catch((err)=>{
          const key = (err && err.message === 'vat_required')
            ? (window.___?___('VAT number is required for company profiles'):'VAT number is required for company profiles')
            : (window.___?___('Save failed'):'Save failed');
          showToast(key, 'error');
        });
      // apply changes
      try {
        if (typeof setCurrency === 'function') setCurrency(newCur);
        if (typeof refreshPriceDisplays === 'function') refreshPriceDisplays();
        if (typeof initTopUp?.refreshLabels === 'function') initTopUp.refreshLabels();
      } catch {}
    });
    // Initial load from server profile (overrides LS if exists)
    fetch('/index.php?page=api&action=getProfile').then(r=> r.ok?r.json():Promise.reject()).then(p=>{
      const prof = p.profile||{};
      if (prof.display_name) setVal('dbDisplayName', prof.display_name);
      if (prof.locale) setVal('dbLocale', prof.locale);
      if (prof.preferred_currency) setVal('dbCurrency', prof.preferred_currency);
      if (typeof prof.is_company !== 'undefined') { if (companyToggle) { companyToggle.checked = !!Number(prof.is_company); updateVatVisibility(); } }
      if (prof.vat_number && document.getElementById('billVat')) document.getElementById('billVat').value = prof.vat_number;
      setVal('billName', prof.billing_name || '');
      setVal('billCity', prof.billing_city || '');
      setVal('billZip', prof.billing_zip || '');
      setVal('billAddr', prof.billing_address || '');
      setVal('billCountry', prof.billing_country || 'HU');
      setVal('shipName', prof.shipping_name || '');
      setVal('shipCity', prof.shipping_city || '');
      setVal('shipZip', prof.shipping_zip || '');
      setVal('shipAddr', prof.shipping_address || '');
      setVal('shipCountry', prof.shipping_country || 'HU');
    }).catch(()=>{ updateVatVisibility(); });
    updateVatVisibility();

    // Sessions: fetch from API if logged in
    let sessions = [];
    fetch('/index.php?page=api&action=listSessions').then(r=> r.ok ? r.json() : Promise.reject()).then(d=>{
      if (Array.isArray(d.items)) {
        sessions = d.items.map(s=>({ start: Date.parse(s.start_at), end: s.end_at? Date.parse(s.end_at): null, ip: s.ip, active: !!s.active }));
      }
      renderSessions();
    }).catch(()=>{ renderSessions(); });
    const sessBody = document.getElementById('dbSessionsBody');
    const sessPrev = document.getElementById('dbSessPrev');
    const sessNext = document.getElementById('dbSessNext');
    const sessInfo = document.getElementById('dbSessPageInfo');
    let sessPage = 1; const sessPageSize = 10;
    function renderSessions(){
      if (!sessBody) return;
      const totalPages = Math.max(1, Math.ceil(sessions.length / sessPageSize));
      if (sessPage > totalPages) sessPage = totalPages;
      const start = (sessPage - 1) * sessPageSize;
      const slice = sessions.slice(start, start + sessPageSize);
      sessBody.innerHTML = '';
      slice.forEach(s => {
        const tr = document.createElement('tr');
        const fmt = (ts) => ts ? new Date(ts).toLocaleString() : '—';
        tr.innerHTML = `
          <td>${fmt(s.start)}</td>
          <td>${fmt(s.end)}</td>
          <td><code>${s.ip||'-'}</code></td>
          <td>${s.active ? '<span class="badge bg-success">Aktív</span>' : '<span class="badge bg-secondary">Lejárt</span>'}</td>
        `;
        sessBody.appendChild(tr);
      });
      if (sessInfo) sessInfo.textContent = `${sessPage} / ${totalPages}`;
      if (sessPrev) sessPrev.disabled = (sessPage <= 1);
      if (sessNext) sessNext.disabled = (sessPage >= totalPages);
    }
    renderSessions();
    sessPrev?.addEventListener('click', ()=>{ if (sessPage>1){ sessPage--; renderSessions(); } });
    sessNext?.addEventListener('click', ()=>{ const max = Math.max(1, Math.ceil(sessions.length/sessPageSize)); if (sessPage<max){ sessPage++; renderSessions(); } });

    // 2FA switch
    const twoFA = document.getElementById('twoFA');
    const twoFAStatus = document.getElementById('twoFAStatus');
    const twoFASetupPanel = document.getElementById('twoFASetupPanel');
    const twoFASecret = document.getElementById('twoFASecret');
    const twoFAUri = document.getElementById('twoFAUri');
    const twoFACode = document.getElementById('twoFACode');
    const twoFAConfirmBtn = document.getElementById('twoFAConfirmBtn');
    const twoFAQrCanvas = document.getElementById('twoFAQrCanvas');
    async function renderTwoFactorQr(uri){
      if (!twoFAQrCanvas || !uri || !window.QRCode || typeof window.QRCode.toCanvas !== 'function') return;
      try {
        await window.QRCode.toCanvas(twoFAQrCanvas, uri, {
          width: 192,
          margin: 1,
          color: {
            dark: '#e6e8ff',
            light: '#11142a'
          }
        });
      } catch {}
    }
    function setTwoFactorUi(data){
      const enabled = !!data?.enabled;
      const pending = !!data?.pending_setup;
      const locked = !!data?.locked;
      if (twoFA) {
        twoFA.checked = enabled || pending;
        twoFA.disabled = locked;
      }
      if (twoFAStatus) {
        if (locked) twoFAStatus.textContent = (window.___?___('Unavailable for this account'):'Unavailable for this account');
        else if (enabled) twoFAStatus.textContent = (window.___?___('Enabled'):'Enabled');
        else if (pending) twoFAStatus.textContent = (window.___?___('Setup pending'):'Setup pending');
        else twoFAStatus.textContent = (window.___?___('Disabled'):'Disabled');
      }
      if (twoFASetupPanel) twoFASetupPanel.classList.toggle('d-none', locked || !pending);
      if (pending) {
        if (twoFASecret) twoFASecret.textContent = data?.manual_secret || '-';
        if (twoFAUri) twoFAUri.value = data?.otp_auth_uri || '';
        renderTwoFactorQr(data?.otp_auth_uri || '');
      } else {
        if (twoFASecret) twoFASecret.textContent = '-';
        if (twoFAUri) twoFAUri.value = '';
        if (twoFACode) twoFACode.value = '';
        if (twoFAQrCanvas) {
          const ctx = twoFAQrCanvas.getContext('2d');
          if (ctx) ctx.clearRect(0, 0, twoFAQrCanvas.width, twoFAQrCanvas.height);
        }
      }
    }
    async function loadTwoFactorState(){
      try {
        const resp = await fetch('/index.php?page=api&action=getTwoFactorStatus');
        if (!resp.ok) throw new Error('two_factor_state_failed');
        const data = await resp.json();
        setTwoFactorUi(data);
      } catch {}
    }
    twoFA?.addEventListener('change', async ()=>{
      const on = !!twoFA.checked;
      twoFA.disabled = true;
      try {
        if (on) {
          const resp = await fetch('/index.php?page=api&action=beginTwoFactorSetup', { method:'POST' });
          const data = await resp.json();
          if (!resp.ok) throw new Error(data.error || 'two_factor_begin_failed');
          setTwoFactorUi({ enabled:false, pending_setup:true, manual_secret:data.manual_secret, otp_auth_uri:data.otp_auth_uri });
        } else {
          const resp = await fetch('/index.php?page=api&action=disableTwoFactor', { method:'POST' });
          const data = await resp.json().catch(()=>({}));
          if (!resp.ok) throw new Error(data.error || 'two_factor_disable_failed');
          setTwoFactorUi({ enabled:false, pending_setup:false });
          showToast((window.___?___('Two-factor authentication disabled'):'Two-factor authentication disabled'), 'success');
        }
      } catch {
        showToast((window.___?___('Two-factor authentication could not be updated'):'Two-factor authentication could not be updated'), 'error');
        loadTwoFactorState();
      } finally {
        twoFA.disabled = false;
      }
    });
    twoFAConfirmBtn?.addEventListener('click', async ()=>{
      const code = (twoFACode?.value || '').trim();
      if (!code) { showToast((window.___?___('Authenticator code is required'):'Authenticator code is required'), 'error'); return; }
      twoFAConfirmBtn.disabled = true;
      try {
        const resp = await fetch('/index.php?page=api&action=confirmTwoFactorSetup', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ code }) });
        const data = await resp.json().catch(()=>({}));
        if (!resp.ok) throw new Error(data.error || 'two_factor_confirm_failed');
        setTwoFactorUi({ enabled:true, pending_setup:false });
        showToast((window.___?___('Two-factor authentication enabled'):'Two-factor authentication enabled'), 'success');
      } catch {
        showToast((window.___?___('Invalid authenticator code'):'Invalid authenticator code'), 'error');
      } finally {
        twoFAConfirmBtn.disabled = false;
      }
    });
    loadTwoFactorState();

    // Nyelvek tab – fordítás editor (EN/HU)
  const langBody = document.getElementById('langTableBody');
    const langSyncBtn = document.getElementById('langSyncBtn');
    const fltKey = document.getElementById('fltKey');
    const fltEn = document.getElementById('fltEn');
    const fltHu = document.getElementById('fltHu');
    const fltSrc = document.getElementById('fltSrc');
  const base = (window._baseTranslations||{en:{},hu:{}});
  // IMPORTANT: dynamic registry is disabled to avoid collecting numbers/amounts/icons as keys
  function readReg(){ return {}; }
  function readDynHu(){ return {}; }
  function writeDynHu(obj){ /* no-op */ }

  function readOverrides(){ return {}; }
  function writeOverrides(obj){ /* translations persist in DB only */ }

    function showToast(msg, type){
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 1500,
          icon: type || 'success',
          title: msg
        });
      } else {
        // Fallback
        try { console.warn('[toast]', msg); } catch {}
      }
    }

    function getAllKeys(){
      const bt = (window._baseTranslations||{en:{},hu:{}});
      const keys = new Set([...Object.keys(bt.en||{}), ...Object.keys(bt.hu||{})]);
      return Array.from(keys).sort((a,b)=> a.localeCompare(b));
    }

    window.renderLangTable = function renderLangTable(){
      if (!langBody) return;
      const base = (window._baseTranslations||{en:{},hu:{}});
  const overrides = readOverrides();
  const dynHu = readDynHu();
  const reg = readReg();
      langBody.innerHTML = '';
      const allKeys = getAllKeys();
      // Apply filters (case-insensitive contains)
      const fk = (fltKey?.value||'').toLowerCase();
      const fe = (fltEn?.value||'').toLowerCase();
      const fh = (fltHu?.value||'').toLowerCase();
      const fs = (fltSrc?.value||'').toLowerCase();
      // show info row if empty
      if (!allKeys.length) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="5" class="text-muted small">Nincs megjeleníthető kulcs. Tipp: böngészd végig az oldalakat, hogy a dinamikus ___('…') kulcsok regisztrálódjanak, vagy szerkeszd a fájl alapú kulcsokat az en/hu forrásban.</td>`;
        langBody.appendChild(tr);
        return;
      }
      allKeys.forEach(key => {
        const tr = document.createElement('tr');
        const isDynamic = false; // disabled
        const enVal = ((window._baseTranslations?.en?.[key]) ?? '');
        const huBase = ((window._baseTranslations?.hu?.[key]) ?? '');
        const huEff = (overrides.hasOwnProperty(key) ? overrides[key] : huBase);
        const sources = [];
        // Filter row
        const srcStr = sources.join(',');
        if (fk && !key.toLowerCase().includes(fk)) return;
        if (fe && !String(enVal||'').toLowerCase().includes(fe)) return;
        if (fh && !String(huEff||'').toLowerCase().includes(fh)) return;
        if (fs && !srcStr.toLowerCase().includes(fs)) return;
        const rowId = 'hu_'+btoa(unescape(encodeURIComponent(key))).replace(/=+$/,'');
        tr.innerHTML = `
          <td class="small"><code>${key}</code></td>
          <td class="small">${enVal ? enVal.replace(/</g,'&lt;').replace(/>/g,'&gt;') : '<span class="text-muted">—</span>'}</td>
          <td style="min-width: 280px;">
            <input type="text" class="form-control formControlDark form-control-sm" id="${rowId}" value="${String(huEff ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}" disabled>
          </td>
          <td class="small">${sources.length ? sources.map(s=>`<span class='badge bg-secondary me-1'>${s}</span>`).join('') : '<span class="text-muted">—</span>'}</td>
          <td style="width:1%">
            <button class="btn btn-sm btn-outline-light adminIconBtn langEditBtn" data-key="${key}" data-input="${rowId}" title="${escapeHtml(t('Edit'))}" aria-label="${escapeHtml(t('Edit'))}">${iconSvg('edit')}</button>
          </td>
        `;
        langBody.appendChild(tr);
      });

      // Wiring edit buttons
      langBody.querySelectorAll('.langEditBtn').forEach(btn => {
        btn.addEventListener('click', () => {
          const inputId = btn.getAttribute('data-input');
          const key = btn.getAttribute('data-key');
          const inp = document.getElementById(inputId);
          if (!inp) return;
          inp.disabled = false;
          inp.focus();
          inp.selectionStart = 0; inp.selectionEnd = String(inp.value||'').length;
          const onBlur = async () => {
            const newVal = String(inp.value||'').trim();
            const liveBase = (window._baseTranslations || { en:{}, hu:{} });
            const baseHu = (liveBase.hu?.[key] ?? '');
            const overrides = readOverrides();
            try {
              let response;
              if (newVal === '' || newVal === baseHu) {
                if (overrides.hasOwnProperty(key)) delete overrides[key];
                response = await fetch('/index.php?page=api&action=saveTranslation', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ key, locale:'hu', value:'' }) });
              } else {
                overrides[key] = newVal;
                response = await fetch('/index.php?page=api&action=saveTranslation', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ key, locale:'hu', value:newVal }) });
              }
              const payload = await response.json().catch(()=>({}));
              if (!response.ok || payload?.error) throw new Error(payload?.error || 'save_failed');
              writeOverrides(overrides);
              const r = await fetch('/index.php?page=api&action=listTranslations');
              if (!r.ok) throw new Error('reload_failed');
              window._baseTranslations = await r.json();
              renderLangTable();
              showToast('Mentve', 'success');
            } catch {
              inp.value = String((window._baseTranslations?.hu?.[key]) ?? baseHu ?? '');
              showToast('A fordítás mentése nem sikerült', 'error');
            } finally {
              inp.disabled = true;
              inp.removeEventListener('blur', onBlur);
            }
          };
          inp.addEventListener('blur', onBlur);
        });
      });
    }

  window.renderLangTable();
  // Bind filter inputs
  [fltKey, fltEn, fltHu, fltSrc].forEach(inp => { inp?.addEventListener('input', () => window.renderLangTable()); });

    // Szinkronizálás: töltsd fel a hiányzó HU értékeket EN-ből az adatbázisban
    langSyncBtn?.addEventListener('click', async () => {
      if (window.Swal) {
        Swal.fire({ title: window.___?___('Translation sync in progress'):'Translation sync in progress', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
      }
      let changes = 0;
      try {
        const resp = await fetch('/index.php?page=api&action=syncTranslations', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ sourceLocale:'en', targetLocale:'hu' })
        });
        const payload = await resp.json().catch(()=>({}));
        if (!resp.ok) throw new Error(payload?.error || 'sync_failed');
        changes = Number(payload?.updated || 0);
        const listResp = await fetch('/index.php?page=api&action=listTranslations');
        if (listResp.ok) {
          window._baseTranslations = await listResp.json();
        }
        renderLangTable();
      } catch {
        if (window.Swal) {
          Swal.close();
          Swal.fire({ icon:'error', title: window.___?___('Save failed'):'Save failed', timer: 1800, showConfirmButton: false });
        } else {
          showToast((window.___?___('Save failed'):'Save failed'), 'error');
        }
        return;
      }
      if (window.Swal) {
        Swal.close();
        const title = window.___?___('Sync complete'):'Szinkronizálás kész';
        const msg = changes ? (window.___?___('{n} keys updated'):`${changes} kulcs frissítve`).replace('{n}', String(changes)) : (window.___?___('Nothing to update'):'Nem volt frissíteni való');
        Swal.fire({ icon: 'success', title, text: msg, timer: 1800, showConfirmButton: false });
      } else {
        showToast((window.___?___('Sync complete'):'Szinkronizálás kész'), 'success');
      }
    });
  })();
</script>
<script type="application/json" id="dashboard-builder-data"><?= json_encode([
  'pages' => $builderPages ?? [],
  'modules' => $builderModules ?? [],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/pages/dashboard-builder.js"></script>
