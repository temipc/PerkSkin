<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$moduleMap = [
    'exchange.header' => __DIR__ . '/modules/exchange/header.php',
    'exchange.market' => __DIR__ . '/modules/exchange/market.php',
];
$renderedLayout = is_array($pageLayout ?? null) ? $pageLayout : [];
?>

<section class="pageSection exchangeSection">
  <div class="container-fluid px-4">
    <?php foreach ($renderedLayout as $layoutItem): ?>
      <?php $moduleKey = (string)($layoutItem['module'] ?? ''); $modulePath = $moduleMap[$moduleKey] ?? null; ?>
      <?php if ($modulePath && is_file($modulePath)) include $modulePath; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/pages/exchange-page.js"></script>
