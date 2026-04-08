<?php /** @var callable $t */ /** @var string $title */ /** @var string $locale */ ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$moduleMap = [
    'home.hero' => __DIR__ . '/modules/home/hero.php',
    'home.live-drop' => __DIR__ . '/modules/home/live-drop.php',
    'home.spinner-chat' => __DIR__ . '/modules/home/spinner-chat.php',
    'home.cases-grid' => __DIR__ . '/modules/home/cases-grid.php',
    'home.community-carousel' => __DIR__ . '/modules/home/community-carousel.php',
    'home.featured-carousel' => __DIR__ . '/modules/home/featured-carousel.php',
    'home.bundle-rewards' => __DIR__ . '/modules/home/bundle-rewards.php',
    'home.event-cases' => __DIR__ . '/modules/home/event-cases.php',
];
$renderedLayout = is_array($homeLayout ?? null) ? $homeLayout : [];
?>

<?php foreach ($renderedLayout as $layoutItem): ?>
  <?php
  $moduleKey = (string)($layoutItem['module'] ?? '');
  $modulePath = $moduleMap[$moduleKey] ?? null;
  ?>
  <?php if ($modulePath && is_file($modulePath)): ?>
    <?php include $modulePath; ?>
  <?php endif; ?>
<?php endforeach; ?>

<?php
$homePagePayload = [
    'cases' => $cases ?? [],
    'communityCases' => $communityCases ?? [],
    'featuredCases' => $featuredCases ?? [],
    'eventCases' => $eventCases ?? [],
    'homeEvents' => $homeEvents ?? [],
    'spinnerProducts' => $spinnerProducts ?? [],
    'homeBundles' => $homeBundles ?? [],
];
?>
<script>
  window.serverData = window.serverData || {};
  Object.assign(window.serverData, <?= json_encode($homePagePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
</script>
<script type="application/json" id="home-page-data"><?= json_encode($homePagePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/pages/home-page.js"></script>
