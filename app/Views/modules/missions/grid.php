<div class="sectionFrame">
  <h2 class="sectionTitle h6 mb-3"><?= ___('Missions (MVP)') ?></h2>
  <div class="row g-3">
    <?php for ($i = 1; $i <= 6; $i++): ?>
      <div class="col-md-4">
        <div class="card p-3 d-flex flex-column gap-2">
          <div class="d-flex align-items-center justify-content-between">
            <div class="h6 mb-0"><?= ___('Mission') ?> #<?php echo $i; ?></div>
            <span class="badge bg-secondary"><?= ___('Level') ?>: <?php echo ($i % 3) + 1; ?></span>
          </div>
          <?php $desc = str_replace('{n}', (string)($i * 2), ___('Open {n} cases from any category.')); ?>
          <div class="text-muted small"><?= htmlspecialchars($desc) ?></div>
          <div class="mt-auto d-flex gap-2">
            <button class="btn btnPrimary btn-sm" disabled><?= ___('Start') ?></button>
            <button class="btn btn-outline-light btn-sm" disabled><?= ___('Details') ?></button>
          </div>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>
