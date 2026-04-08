<?php /** @var callable $t */ /** @var string $locale */ ?>
<?php
// Global PHP translate helper with explicit marker syntax ___('Text')
if (!function_exists('___')) {
  function ___(string $text): string {
    // Try server-side translator ($t) with text as key; fallback to original
    if (isset($GLOBALS['t']) && is_callable($GLOBALS['t'])) {
      try { $val = $GLOBALS['t']($text); if (is_string($val) && $val !== '') return $val; } catch (\Throwable $e) {}
    }
    return $text;
  }
}
// Lightweight content section fetcher (DB-backed) for header/footer integration
if (!function_exists('getContentSection')) {
  function getContentSection(string $page, string $section, string $locale): ?array {
    static $cache = [];
    $key = strtolower($page.'|'.$section.'|'.$locale);
    if (isset($cache[$key])) return $cache[$key];
    try {
      $cfg = include dirname(__DIR__, 2) . '/Config/db.php';
      
      // Support both SQLite and MySQL
      if (isset($cfg['driver']) && $cfg['driver'] === 'sqlite') {
        $pdo = new \PDO('sqlite:' . $cfg['database'], null, null, 
          [\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC]
        );
      } else {
        $pdo = new \PDO(
          sprintf('mysql:host=%s;dbname=%s;charset=%s', $cfg['host'], $cfg['db'], $cfg['charset'] ?? 'utf8mb4'),
          $cfg['user'], $cfg['pass'], [\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC]
        );
      }
      
      // Try exact locale, then fallback to EN
      $stmt = $pdo->prepare('SELECT title, body FROM content_sections WHERE page=? AND section=? AND locale=?');
      $stmt->execute([$page, $section, strtolower($locale)]);
      $row = $stmt->fetch();
      if (!$row) {
        $stmt->execute([$page, $section, 'en']);
        $row = $stmt->fetch();
      }
      $cache[$key] = $row ?: null; return $cache[$key];
    } catch (\Throwable $e) { $cache[$key] = null; return null; }
  }
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t('app.title')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <link rel="stylesheet" href="<?= ($GLOBALS['asset'])('/assets/css/styles.css') ?>">
  <script type="application/json" id="app-bootstrap-data"><?= json_encode([
    'basePath' => $GLOBALS['basePath'] ?? '',
    'locale' => $locale,
    'isLoggedIn' => !empty($_SESSION['user_id']),
    'isAdmin' => !empty($_SESSION['is_admin']),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="themeDark" data-page="<?= htmlspecialchars($_GET['page'] ?? 'home') ?>">
<header class="siteHeader">
  <div class="container-fluid d-flex align-items-center justify-content-between py-2">
    <a class="brand d-flex align-items-center gap-2 text-decoration-none" href="/">
      <span class="brandLogo" aria-hidden="true"></span>
      <?php $hdr = getContentSection('global','header', $locale); $brand = trim((string)($hdr['title'] ?? '')) ?: 'PerkSpin'; ?>
      <span class="brandName"><?= htmlspecialchars($brand) ?></span>
    </a>
    <nav class="mainNav d-none d-md-flex gap-3">
      <?php foreach (($GLOBALS['navigationItems'] ?? []) as $navItem): ?>
        <a href="<?= htmlspecialchars($navItem['href'] ?? '#') ?>" class="navLink"><?= htmlspecialchars($navItem['label'] ?? '') ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="headerActions d-flex align-items-center gap-2">
      <div id="levelPill" class="levelPill d-none d-sm-flex align-items-center gap-2 me-2">
        <span id="playerRankIcon" class="rankInsignia" aria-hidden="true">★</span>
        <span id="playerLevel">Lv 1/10</span>
      </div>
      <div class="dropdown me-2">
        <button class="btn btn-sm btn-outline-light" id="rankSetterBtn" data-bs-toggle="dropdown" aria-expanded="false" title="<?= ___('Rank setter') ?>">🎖️</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li class="dropdown-item text-muted small"><?= ___('Set level') ?></li>
          <li><hr class="dropdown-divider"></li>
          <?php for ($i=1; $i<=10; $i++): ?>
            <li><a class="dropdown-item rankSetItem" href="#" data-level="<?= $i ?>"><?= ___('Level') ?> <?= $i ?></a></li>
          <?php endfor; ?>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" id="rankSetterAuto"><?= ___('Automatic rank') ?></a></li>
        </ul>
      </div>
      <div class="balancePill d-none d-sm-flex align-items-center gap-2 me-2">
        <span class="icon icon-coin" aria-hidden="true"></span>
        <span id="balanceAmount" data-price-usd="0" data-price-target="text">0</span>
      </div>
      <div class="gemsPill d-none d-sm-flex align-items-center gap-2 me-2">
        <span class="icon icon-gem" aria-hidden="true">💎</span>
        <span id="gemsAmount">0</span>
      </div>
      <div class="currencySwitcher dropdown me-2">
        <button class="btn btn-sm btn-outline-light dropdown-toggle" id="currencyButton" data-bs-toggle="dropdown" aria-expanded="false">
          USD
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#" data-currency="USD">USD</a></li>
          <li><a class="dropdown-item" href="#" data-currency="EUR">EUR</a></li>
          <li><a class="dropdown-item" href="#" data-currency="HUF">HUF</a></li>
        </ul>
      </div>
  <button class="btn btnPrimary d-none d-sm-inline-flex" id="btnTopUp" data-bs-toggle="modal" data-bs-target="#topUpModal"><?= ___('TOP UP') ?></button>
      <div class="langSwitcher dropdown">
        <button class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <?= strtoupper($locale) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="?lang=en">English</a></li>
          <li><a class="dropdown-item" href="?lang=hu">Magyar</a></li>
        </ul>
      </div>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a class="btn btnOutline" href="/index.php?page=logout"><?= ___('Logout') ?></a>
  <?php else: ?>
    <a class="btn btnPrimary" id="loginButton" href="#" data-bs-toggle="modal" data-bs-target="#authModal"><?= $t('auth.login') ?></a>
  <?php endif; ?>
    </div>
  </div>
</header>
<main>
