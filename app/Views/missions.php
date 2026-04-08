<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$moduleMap = [
    'missions.header' => __DIR__ . '/modules/missions/header.php',
    'missions.grid' => __DIR__ . '/modules/missions/grid.php',
];
$renderedLayout = is_array($pageLayout ?? null) ? $pageLayout : [];
?>

<section class="pageSection missionsSection">
  <div class="container-fluid px-4">
    <?php foreach ($renderedLayout as $layoutItem): ?>
      <?php $moduleKey = (string)($layoutItem['module'] ?? ''); $modulePath = $moduleMap[$moduleKey] ?? null; ?>
      <?php if ($modulePath && is_file($modulePath)) include $modulePath; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
