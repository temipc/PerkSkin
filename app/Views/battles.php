<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$moduleMap = [
    'battles.header' => __DIR__ . '/modules/battles/header.php',
    'battles.hero' => __DIR__ . '/modules/battles/hero.php',
    'battles.rooms' => __DIR__ . '/modules/battles/rooms.php',
];
$renderedLayout = is_array($pageLayout ?? null) ? $pageLayout : [];
?>

<section class="pageSection">
  <div class="container-fluid px-4">
    <?php foreach ($renderedLayout as $layoutItem): ?>
      <?php $moduleKey = (string)($layoutItem['module'] ?? ''); $modulePath = $moduleMap[$moduleKey] ?? null; ?>
      <?php if ($modulePath && is_file($modulePath)) include $modulePath; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
