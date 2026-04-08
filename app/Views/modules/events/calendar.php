<div class="sectionFrame">
  <h2 class="sectionTitle h6 mb-3"><?= ___('Events calendar') ?></h2>
  <div class="calendarDemo">
    <div class="calendarHeader d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
      <div id="calTitle" class="h5 mb-0">-</div>
      <div class="d-flex align-items-center gap-2 ms-auto">
        <div class="btn-group btn-group-sm" role="group" aria-label="<?= ___('View') ?>">
          <button id="viewToday" class="btn btn-outline-light"><?= ___('Today') ?></button>
          <button id="viewWeek" class="btn btn-outline-light"><?= ___('Week') ?></button>
          <button id="viewMonth" class="btn btn-outline-light active"><?= ___('Month') ?></button>
        </div>
        <div class="d-flex gap-2">
          <button id="calPrev" class="btn btn-sm btn-outline-light" title="<?= ___('Previous') ?>">‹</button>
          <button id="calNext" class="btn btn-sm btn-outline-light" title="<?= ___('Next') ?>">›</button>
        </div>
      </div>
    </div>
    <div class="calendarWeekdays mb-2 small">
      <div><?= ___('Mon') ?></div><div><?= ___('Tue') ?></div><div><?= ___('Wed') ?></div><div><?= ___('Thu') ?></div><div><?= ___('Fri') ?></div><div><?= ___('Sat') ?></div><div><?= ___('Sun') ?></div>
    </div>
    <div id="calGrid" class="calendarGrid cols-7"></div>
  </div>
</div>
