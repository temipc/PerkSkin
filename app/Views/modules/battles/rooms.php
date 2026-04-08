<div class="sectionFrame">
  <h2 class="sectionTitle h6 mb-3"><?= ___('Battle rooms') ?></h2>
  <div class="row g-3">
    <?php foreach (($battleRooms ?? []) as $room): ?>
      <div class="col-md-4">
        <div class="card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h6 mb-0"><?= htmlspecialchars($room['name'] ?? '') ?></div>
            <span class="badge bg-secondary"><?= htmlspecialchars($room['status'] ?? '') ?></span>
          </div>
          <div class="small text-muted mb-1"><?= htmlspecialchars($room['players'] ?? '') ?></div>
          <div class="small text-muted mb-3"><?= ___('Entry') ?>: <?= htmlspecialchars($room['entry'] ?? '') ?></div>
          <button class="btn btnPrimary btn-sm mt-auto" disabled><?= ___('Coming soon') ?></button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
