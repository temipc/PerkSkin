<?php
$deadline = '';
if (!empty($homeEvents) && is_array($homeEvents)) {
    $nowTs = time();
    foreach ($homeEvents as $eventItem) {
        $startAt = trim((string)($eventItem['start_at'] ?? $eventItem['date'] ?? ''));
        if ($startAt === '') continue;
        $ts = strtotime(str_replace('T', ' ', $startAt));
        if ($ts !== false && $ts >= $nowTs) {
            $deadline = date('c', $ts);
            break;
        }
    }
}
if ($deadline === '') $deadline = date('c', time() + 16 * 24 * 3600);
?>
<section class="casesSection" id="eventCases">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3 smokeBg">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="sectionTitle h5 mb-0"><?= $t('cases.eventTitle') ?></h2>
      </div>
      <div class="eventHeader text-center mb-3" id="eventHeader" data-deadline="<?= htmlspecialchars($deadline) ?>">
        <div class="eventCountdown d-flex justify-content-center gap-2 mb-3">
          <div class="unit">
            <div class="num" id="cdDays">16</div>
            <div class="label"><?= $t('time.DAYS') ?></div>
          </div>
          <div class="unit">
            <div class="num" id="cdHours">12</div>
            <div class="label"><?= $t('time.HOURS') ?></div>
          </div>
          <div class="unit">
            <div class="num" id="cdMin">37</div>
            <div class="label"><?= $t('time.MIN') ?></div>
          </div>
          <div class="unit">
            <div class="num" id="cdSec">26</div>
            <div class="label"><?= $t('time.SEC') ?></div>
          </div>
        </div>
        <a href="#eventCases" class="btn btnPrimary eventCta"><?= $t('event.goToEventPage') ?></a>
      </div>
      <div class="sectionRibbon"><span><?= $t('ribbon.limitedTime') ?></span></div>
      <div class="row g-3" id="eventCasesGrid"></div>
    </div>
  </div>
</section>
