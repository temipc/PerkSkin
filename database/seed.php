<?php
// Seed SQLite database with schema and sample data

$dbPath = dirname(__DIR__) . '/database/webdb.sqlite';

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("PRAGMA foreign_keys = ON");

// Create tables
$tables = <<<SQL
CREATE TABLE IF NOT EXISTS perksin_users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255),
  display_name VARCHAR(100),
  locale VARCHAR(8) NOT NULL DEFAULT 'en',
  avatar_url VARCHAR(255),
  email_verified_at DATETIME,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS perksin_badges (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS perksin_user_badges (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  badge_id INTEGER NOT NULL,
  earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0,
  UNIQUE(user_id, badge_id),
  FOREIGN KEY(user_id) REFERENCES perksin_users(id),
  FOREIGN KEY(badge_id) REFERENCES perksin_badges(id)
);

CREATE TABLE IF NOT EXISTS perksin_categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS perksin_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug VARCHAR(120) NOT NULL UNIQUE,
  title VARCHAR(150) NOT NULL,
  base_price_cents INTEGER NOT NULL,
  tag VARCHAR(30),
  category_id INTEGER,
  img VARCHAR(255),
  risk VARCHAR(32),
  is_event INTEGER NOT NULL DEFAULT 0,
  required_level INTEGER NOT NULL DEFAULT 1,
  is_community INTEGER NOT NULL DEFAULT 1,
  is_featured INTEGER NOT NULL DEFAULT 0,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY(category_id) REFERENCES perksin_categories(id)
);

CREATE TABLE IF NOT EXISTS perksin_partners (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  website VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS perksin_offers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  partner_id INTEGER NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  price_cents INTEGER NOT NULL DEFAULT 0,
  tag VARCHAR(30),
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY(partner_id) REFERENCES perksin_partners(id)
);

CREATE TABLE IF NOT EXISTS perksin_case_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_id INTEGER NOT NULL,
  offer_id INTEGER,
  title VARCHAR(150) NOT NULL,
  value_cents INTEGER NOT NULL,
  weight INTEGER NOT NULL DEFAULT 1,
  stock_type TEXT NOT NULL DEFAULT 'infinite',
  stock_qty INTEGER,
  stock_reserved INTEGER NOT NULL DEFAULT 0,
  stock_consumed INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY(case_id) REFERENCES perksin_cases(id),
  FOREIGN KEY(offer_id) REFERENCES perksin_offers(id)
);

CREATE TABLE IF NOT EXISTS perksin_translations_kv (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  key VARCHAR(255) NOT NULL,
  locale VARCHAR(8) NOT NULL,
  value TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(key, locale)
);

CREATE TABLE IF NOT EXISTS perksin_events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATE NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS perksin_user_sessions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  start_at DATETIME NOT NULL,
  end_at DATETIME,
  ip VARCHAR(45),
  active INTEGER NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  isDeleted INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_user_wallets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  balance_cents INTEGER NOT NULL DEFAULT 0,
  balance_milli INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_wallet_transactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  wallet_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  amount_cents INTEGER NOT NULL DEFAULT 0,
  amount_milli INTEGER NOT NULL DEFAULT 0,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  type VARCHAR(32),
  reference_type VARCHAR(32),
  reference_id INTEGER,
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(wallet_id) REFERENCES perksin_user_wallets(id),
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_user_gems (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  balance INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_gem_transactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  amount INTEGER NOT NULL,
  type VARCHAR(32),
  reference_type VARCHAR(32),
  reference_id INTEGER,
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_case_open_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  case_title VARCHAR(255),
  won_item_title VARCHAR(255),
  won_value_cents INTEGER,
  coupon_code VARCHAR(255),
  status VARCHAR(32),
  currency VARCHAR(8) DEFAULT 'USD',
  price_paid_cents INTEGER,
  open_tx_id INTEGER,
  sold_tx_id INTEGER,
  sold_amount_cents INTEGER,
  sold_amount_milli INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_user_inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  item_title VARCHAR(255),
  item_value_cents INTEGER,
  code_id INTEGER,
  status VARCHAR(32),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_market_offers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner_type VARCHAR(16) NOT NULL,
  owner_key VARCHAR(191) NOT NULL,
  item_title VARCHAR(150) NOT NULL,
  item_value_cents INTEGER NOT NULL DEFAULT 0,
  requested_value_cents INTEGER NOT NULL DEFAULT 0,
  currency VARCHAR(3) NOT NULL DEFAULT 'USD',
  status VARCHAR(16) NOT NULL DEFAULT 'open',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS perksin_user_spin_state (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  used_today INTEGER NOT NULL DEFAULT 0,
  last_reset_date DATE,
  lock_until DATETIME,
  collectibles INTEGER NOT NULL DEFAULT 0,
  bonus INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  FOREIGN KEY(user_id) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_item_codes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_item_id INTEGER NOT NULL,
  code VARCHAR(255) NOT NULL UNIQUE,
  status VARCHAR(32) NOT NULL DEFAULT 'new',
  claimed_at DATETIME,
  claimed_by INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(case_item_id) REFERENCES perksin_case_items(id),
  FOREIGN KEY(claimed_by) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_content_sections (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  page VARCHAR(100) NOT NULL,
  section VARCHAR(100) NOT NULL,
  locale VARCHAR(8) NOT NULL DEFAULT 'en',
  title VARCHAR(255),
  body TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER,
  UNIQUE(page, section, locale),
  FOREIGN KEY(created_by) REFERENCES perksin_users(id),
  FOREIGN KEY(modified_by) REFERENCES perksin_users(id)
);

CREATE TABLE IF NOT EXISTS perksin_client_state (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  owner_type VARCHAR(16) NOT NULL,
  owner_key VARCHAR(191) NOT NULL,
  state_key VARCHAR(191) NOT NULL,
  state_value TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(owner_type, owner_key, state_key)
);

CREATE TABLE IF NOT EXISTS perksin_ranks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(100) NOT NULL,
  min_points INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified_at DATETIME,
  created_by INTEGER,
  modified_by INTEGER
);
SQL;

try {
  $pdo->exec($tables);
  echo "✓ Schema created.\n";
} catch (Throwable $e) {
  echo "✗ Schema error: " . $e->getMessage() . "\n";
  die();
}

// Seed admin user
$adminUser = 'admin@admin.com';
$adminPassPlain = 'admin123';
$adminHash = password_hash($adminPassPlain, PASSWORD_BCRYPT);

try {
  $pdo->prepare("INSERT OR IGNORE INTO perksin_users (email, password_hash, display_name, locale, is_admin) VALUES (?, ?, ?, ?, ?)")
      ->execute([$adminUser, $adminHash, 'Administrator', 'en', 1]);
  echo "✓ Admin user created (admin@admin.com / admin123).\n";
} catch (Throwable $e) {
  echo "✗ Admin error: " . $e->getMessage() . "\n";
}

// Seed translations
try {
  $basePath = dirname(__DIR__) . '/app/Translations';
  foreach (['en.php' => 'en', 'hu.php' => 'hu'] as $file => $locale) {
    $arr = include $basePath . '/' . $file;
    foreach ($arr as $key => $val) {
      $pdo->prepare("INSERT OR REPLACE INTO perksin_translations_kv (key, locale, value) VALUES (?, ?, ?)")
          ->execute([$key, $locale, $val]);
    }
    echo "✓ Translations loaded ($locale).\n";
  }
} catch (Throwable $e) {
  echo "- Translations: " . $e->getMessage() . "\n";
}

// Seed cases
try {
  $configCases = include dirname(__DIR__) . '/app/Config/cases.php';
  if (!empty($configCases['cases'])) {
    foreach ($configCases['cases'] as $c) {
      $slug = strtolower(preg_replace('~[^a-z0-9]+~', '-', $c['title']));
      $pdo->prepare("INSERT OR IGNORE INTO perksin_cases (slug, title, base_price_cents, tag) VALUES (?, ?, ?, ?)")
          ->execute([$slug, $c['title'], (int)round($c['price'] * 100), $c['tag']]);
      
      $cid = (int)$pdo->query("SELECT id FROM perksin_cases WHERE slug = '" . str_replace("'", "''", $slug) . "' LIMIT 1")->fetchColumn();
      
      if (!empty($c['items']) && $cid > 0) {
        foreach ($c['items'] as $it) {
          $pdo->prepare("INSERT OR IGNORE INTO perksin_case_items (case_id, title, value_cents, weight) VALUES (?, ?, ?, ?)")
              ->execute([$cid, $it['name'], (int)round(($it['value'] ?? 0) * 100), 1]);
        }
      }
    }
    echo "✓ Cases and items seeded.\n";
  }
} catch (Throwable $e) {
  echo "- Cases: " . $e->getMessage() . "\n";
}

// Seed events
try {
  $pdo->exec("INSERT OR IGNORE INTO perksin_events (date, title, description) VALUES
    (DATE('now'), 'Launch Promo', 'Opening week specials.'),
    (DATE('now', '+7 days'), 'Weekly Challenge', 'Complete missions for rewards.')");
  echo "✓ Events seeded.\n";
} catch (Throwable $e) {
  echo "- Events: " . $e->getMessage() . "\n";
}

echo "\n✓✓✓ Database ready!\n";
echo "SQLite database: " . $dbPath . "\n";
echo "Login: admin@admin.com / admin123\n";
