<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$moduleMap = [
    'events.header' => __DIR__ . '/modules/events/header.php',
    'events.calendar' => __DIR__ . '/modules/events/calendar.php',
];
$renderedLayout = is_array($pageLayout ?? null) ? $pageLayout : [];
?>

<section class="pageSection eventsSection">
  <div class="container-fluid px-4">
    <?php foreach ($renderedLayout as $layoutItem): ?>
      <?php $moduleKey = (string)($layoutItem['module'] ?? ''); $modulePath = $moduleMap[$moduleKey] ?? null; ?>
      <?php if ($modulePath && is_file($modulePath)) include $modulePath; ?>
    <?php endforeach; ?>
  </div>
</section>

<script type="application/json" id="events-page-data"><?= json_encode(['events' => $events ?? []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/pages/events-page.js"></script>
