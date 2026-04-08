<?php /** @var callable $t */ /** @var array $case */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>

<section class="casesSection">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3">
      <?php
        $risk = strtolower($case['risk'] ?? 'low');
        $riskClass = $risk === 'low' ? 'low' : ($risk === 'medium' ? 'mid' : ($risk === 'high' ? 'high' : 'veryhigh'));
        if ($risk === 'low') $riskKey = 'cases.risk.low';
        elseif ($risk === 'medium') $riskKey = 'cases.risk.medium';
        elseif ($risk === 'high') $riskKey = 'cases.risk.high';
        elseif ($risk === 'veryhigh') $riskKey = 'cases.risk.veryHigh';
        else $riskKey = 'cases.risk.low';
        $riskLabel = $t($riskKey);
        $reqLevel = max(1, min(10, (int)($case['required_level'] ?? 1)));
        $loggedIn = !empty($_SESSION['user_id']);
        $eventAccess = is_array($case['event_access'] ?? null) ? $case['event_access'] : ['is_active' => true, 'event_title' => '', 'start_at' => '', 'end_at' => ''];
        $eventStart = (string)($eventAccess['start_at'] ?? '');
        $eventEnd = (string)($eventAccess['end_at'] ?? '');
        $eventActive = !empty($eventAccess['is_active']) || empty($case['is_event']);
      ?>
      <div class="row g-4 align-items-stretch caseSingleHeroRow">
        <div class="col-lg-6">
          <div class="caseSingleHeroColumn h-100">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
              <h1 class="h4 mb-0 d-flex align-items-center gap-2">
                <?= htmlspecialchars($case['title']) ?>
                <span class="riskBadge <?= $riskClass ?>"><?= htmlspecialchars($riskLabel) ?></span>
              </h1>
              <span class="priceTag h5 mb-0" data-price-usd="<?= number_format((float)$case['price'], 2, '.', '') ?>" data-price-target="text"><?= number_format((float)$case['price'], 2) ?></span>
            </div>
            <div class="caseHeroBanner caseHeroBannerSquare" style="--case-hero-image:url('<?= htmlspecialchars($case['img']) ?>');">
              <div class="caseHeroOverlay">
                <div class="caseHeroCopy text-white">
                  <div class="caseHeroTitle"><?= htmlspecialchars($case['title']) ?></div>
                  <div class="small"><?= htmlspecialchars($t('cases.createdByCommunity')) ?></div>
                  <div class="opacity-75 small"><?= htmlspecialchars($t('cases.transparencyGuaranteed')) ?></div>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3">
              <div class="text-white small"><?= htmlspecialchars($t('cases.steamRequired')) ?></div>
              <?php if ($loggedIn): ?>
                <button class="btn btnPrimary" id="btnOpenThisCase"
                  data-title="<?= htmlspecialchars($case['title']) ?>"
                  data-price="<?= number_format((float)$case['price'], 2, '.', '') ?>"
                  data-req-level="<?= $reqLevel ?>"
                  data-event-start="<?= htmlspecialchars($eventStart) ?>"
                  data-event-end="<?= htmlspecialchars($eventEnd) ?>"
                  data-event-title="<?= htmlspecialchars((string)($eventAccess['event_title'] ?? '')) ?>"
                  title="<?= htmlspecialchars($t('cases.requiresLevel', ['level' => $reqLevel])) ?>"
                  data-items='<?= json_encode($case['items'] ?? []) ?>'><?= $t('cases.openThis') ?></button>
              <?php else: ?>
                <button class="btn btnPrimary" id="btnOpenThisCase"
                  data-login-required="1"
                  data-title="<?= htmlspecialchars($case['title']) ?>"
                  data-price="<?= number_format((float)$case['price'], 2, '.', '') ?>"
                  data-req-level="<?= $reqLevel ?>"
                  data-event-start="<?= htmlspecialchars($eventStart) ?>"
                  data-event-end="<?= htmlspecialchars($eventEnd) ?>"
                  data-event-title="<?= htmlspecialchars((string)($eventAccess['event_title'] ?? '')) ?>"
                  data-items='<?= json_encode($case['items'] ?? []) ?>'><?= $t('cases.signInToOpen') ?></button>
              <?php endif; ?>
            </div>
            <?php if (!empty($case['is_event'])): ?>
              <div class="small text-white mt-2">
                <?= htmlspecialchars(($eventAccess['event_title'] ?? '') !== '' ? $eventAccess['event_title'] : $t('Event')) ?>
                <?php if ($eventStart !== '' && $eventEnd !== ''): ?>
                  • <?= htmlspecialchars($eventStart) ?> - <?= htmlspecialchars($eventEnd) ?>
                <?php endif; ?>
                <?php if (!$eventActive): ?>
                  • <?= htmlspecialchars($t('event.goToEventPage')) ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="spinnerDemo caseSingleSpinnerCard h-100">
            <div class="spinnerViewport" style="height:120px;">
              <div class="spinnerReel" style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; gap:12px; padding:12px;">
                <?php foreach (($case['items'] ?? []) as $it): ?>
                  <div class="spinnerItem" style="width:120px; height:96px; display:flex; align-items:center; justify-content:center;"><?= htmlspecialchars($it['name']) ?></div>
                <?php endforeach; ?>
              </div>
              <div class="spinnerPointer"></div>
            </div>
            <div class="small text-white mt-3"><?= htmlspecialchars($t('cases.requiresLevel', ['level' => $reqLevel])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="sectionFrame mb-3">
      <h2 class="sectionTitle h6 mb-3"><?= htmlspecialchars($t('cases.lastTopItems')) ?></h2>
      <div class="row g-3">
        <?php foreach (array_slice(($case['items'] ?? []), 0, 6) as $it): ?>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-2 h-100">
              <div class="caseThumb mb-2 position-relative">
                <span class="infoIconWrap hasTooltip">
                  <span class="infoIcon">i</span>
                  <span class="infoTooltip"><span class="itn"><?= htmlspecialchars($it['name']) ?></span> • <?= htmlspecialchars($t('cases.topTag')) ?> • <span data-price-usd="<?= number_format((float)($it['value'] ?? 0), 2, '.', '') ?>" data-price-target="inner"><?= number_format((float)($it['value'] ?? 0), 2) ?></span></span>
                </span>
                <img src="/assets/images/case-2.svg" alt="<?= htmlspecialchars($it['name']) ?>" loading="lazy">
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="caseTitle"><?= htmlspecialchars($it['name']) ?></div>
                  <div class="tag"><?= htmlspecialchars($t('cases.topTag')) ?></div>
                </div>
                <div class="priceTag" data-price-usd="<?= number_format((float)($it['value'] ?? 0), 2, '.', '') ?>" data-price-target="inner"><?= number_format((float)($it['value'] ?? 0), 2) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sectionFrame mb-3 skinsGrid">
      <h2 class="sectionTitle h6 mb-3"><?= htmlspecialchars($t('cases.itemsInCase')) ?></h2>
      <div class="row g-3">
        <?php foreach (array_slice(($case['items'] ?? []), 0, 18) as $it): ?>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 p-2">
              <div class="caseThumb mb-2 position-relative">
                <span class="infoIconWrap hasTooltip">
                  <span class="infoIcon">i</span>
                  <span class="infoTooltip"><span class="itn"><?= htmlspecialchars($it['name']) ?></span> • <?= htmlspecialchars($t('cases.minTag')) ?> • <span data-price-usd="<?= number_format((float)($it['value'] ?? 0), 2, '.', '') ?>" data-price-target="inner"><?= number_format((float)($it['value'] ?? 0), 2) ?></span></span>
                </span>
                <img src="/assets/images/case-3.svg" alt="<?= htmlspecialchars($it['name']) ?>" loading="lazy">
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="caseTitle"><?= htmlspecialchars($it['name']) ?></div>
                  <div class="tag"><?= htmlspecialchars($t('cases.minTag')) ?></div>
                </div>
                <div class="priceTag" data-price-usd="<?= number_format((float)($it['value'] ?? 0), 2, '.', '') ?>" data-price-target="inner"><?= number_format((float)($it['value'] ?? 0), 2) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/pages/case-page.js"></script>
