-- Application schema (MySQL/MariaDB)

-- Users & auth
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  display_name VARCHAR(100) NULL,
  locale VARCHAR(8) NOT NULL DEFAULT 'en',
  avatar_url VARCHAR(255) NULL,
  email_verified_at DATETIME NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Additional (manual) alterations introduced by feature update (company profile & ownership linkage)
-- Run once if fields not present:
-- ALTER TABLE users ADD COLUMN is_company TINYINT(1) NOT NULL DEFAULT 0 AFTER is_admin;
-- ALTER TABLE users ADD COLUMN vat_number VARCHAR(64) NULL AFTER is_company;
-- (Optional) Backfill created_by for existing offers/cases/badges if needed.

-- Badges
CREATE TABLE IF NOT EXISTS badges (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_badges (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  badge_id INT NOT NULL,
  earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_badge (user_id, badge_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (badge_id) REFERENCES badges(id),
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reference partners (optional for offers)
CREATE TABLE IF NOT EXISTS partners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  website VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OAuth providers
CREATE TABLE IF NOT EXISTS oauth_providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  provider_id INT NOT NULL,
  provider_user_id VARCHAR(191) NOT NULL,
  access_token TEXT NULL,
  refresh_token TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_provider_user (provider_id, provider_user_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (provider_id) REFERENCES oauth_providers(id),
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories for cases
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cases
CREATE TABLE IF NOT EXISTS cases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  title VARCHAR(150) NOT NULL,
  base_price_cents INT NOT NULL,
  tag VARCHAR(30) NULL,
  category_id INT NULL,
  img VARCHAR(255) NULL,
  risk VARCHAR(32) NULL,
  is_event TINYINT(1) NOT NULL DEFAULT 0,
  required_level INT NOT NULL DEFAULT 1,
  is_community TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Offers (catalog)
CREATE TABLE IF NOT EXISTS offers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partner_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price_cents INT NOT NULL DEFAULT 0,
  tag VARCHAR(30) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (partner_id) REFERENCES partners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Case items
CREATE TABLE IF NOT EXISTS case_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  offer_id INT NULL,
  title VARCHAR(150) NOT NULL,
  value_cents INT NOT NULL,
  weight INT NOT NULL DEFAULT 1,
  stock_type ENUM('infinite','finite','codes') NOT NULL DEFAULT 'infinite',
  stock_qty INT NULL,
  stock_reserved INT NOT NULL DEFAULT 0,
  stock_consumed INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (case_id) REFERENCES cases(id),
  FOREIGN KEY (offer_id) REFERENCES offers(id),
  INDEX(case_id), INDEX(offer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional one-time codes for items (used when stock_type='codes')
CREATE TABLE IF NOT EXISTS item_codes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  case_item_id INT NOT NULL,
  code VARCHAR(191) NOT NULL UNIQUE,
  status ENUM('new','reserved','claimed','disabled') NOT NULL DEFAULT 'new',
  reserved_at DATETIME NULL,
  claimed_at DATETIME NULL,
  claimed_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (case_item_id) REFERENCES case_items(id),
  FOREIGN KEY (claimed_by) REFERENCES users(id),
  INDEX(case_item_id), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User sessions (for dashboard table)
CREATE TABLE IF NOT EXISTS user_sessions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NULL,
  ip VARCHAR(45) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User inventory (claimed items)
CREATE TABLE IF NOT EXISTS user_inventory (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_title VARCHAR(150) NOT NULL,
  item_value_cents INT NOT NULL DEFAULT 0,
  source_case_id INT NULL,
  code_id BIGINT NULL,
  status ENUM('active','redeemed','expired','sold') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (source_case_id) REFERENCES cases(id),
  FOREIGN KEY (code_id) REFERENCES item_codes(id),
  INDEX(user_id), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- History of case opens
CREATE TABLE IF NOT EXISTS case_open_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  case_id INT NULL,
  case_title VARCHAR(150) NOT NULL,
  won_item_title VARCHAR(150) NOT NULL,
  won_value_cents INT NOT NULL DEFAULT 0,
  coupon_code VARCHAR(64) NULL,
  status ENUM('claimed','sold') NOT NULL,
  currency CHAR(3) NULL,
  price_paid_cents INT NULL,
  open_tx_id BIGINT NULL,
  sold_tx_id BIGINT NULL,
  sold_amount_cents INT NULL,
  sold_amount_milli BIGINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (case_id) REFERENCES cases(id),
  INDEX(open_tx_id), INDEX(sold_tx_id),
  INDEX(user_id), INDEX(case_id), INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Marketplace offers (exchange)
CREATE TABLE IF NOT EXISTS market_offers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_title VARCHAR(150) NOT NULL,
  item_value_cents INT NOT NULL DEFAULT 0,
  requested_value_cents INT NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status ENUM('open','matched','cancelled') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX(user_id), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DB-backed replacement for browser local state (guest + authenticated session/user state)
CREATE TABLE IF NOT EXISTS client_state (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  owner_type VARCHAR(16) NOT NULL,
  owner_key VARCHAR(191) NOT NULL,
  state_key VARCHAR(191) NOT NULL,
  state_value LONGTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_owner_state (owner_type, owner_key, state_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wallets and financial transactions
CREATE TABLE IF NOT EXISTS user_wallets (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  balance_cents BIGINT NOT NULL DEFAULT 0,
  balance_milli BIGINT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_user_currency (user_id, currency),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Immutable ledger; negative amount_cents means debit
CREATE TABLE IF NOT EXISTS wallet_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  user_id INT NOT NULL,
  amount_cents BIGINT NOT NULL,
  amount_milli BIGINT NOT NULL,
  currency CHAR(3) NOT NULL,
  type ENUM('deposit','withdraw','purchase','sale','bonus','adjustment','refund') NOT NULL,
  reference_type VARCHAR(50) NULL,
  reference_id BIGINT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (wallet_id) REFERENCES user_wallets(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX(wallet_id), INDEX(user_id), INDEX(currency), INDEX(reference_type), INDEX(reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ranks / levels
CREATE TABLE IF NOT EXISTS ranks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(64) NOT NULL,
  min_points INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events calendar entries
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  href VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- i18n key-value store
CREATE TABLE IF NOT EXISTS translations_kv (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(191) NOT NULL,
  locale VARCHAR(8) NOT NULL,
  value TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_key_locale (`key`, locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gems (shards) balance and transactions
CREATE TABLE IF NOT EXISTS user_gems (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  balance BIGINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gem_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount BIGINT NOT NULL,
  type ENUM('award','spend','adjust') NOT NULL,
  reference_type VARCHAR(50) NULL,
  reference_id BIGINT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX(user_id), INDEX(type), INDEX(reference_type), INDEX(reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Page/section-based localized content (HTML allowed), editable via admin Content tab
CREATE TABLE IF NOT EXISTS content_sections (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  page VARCHAR(64) NOT NULL,
  section VARCHAR(64) NOT NULL,
  locale VARCHAR(8) NOT NULL,
  title VARCHAR(191) NULL,
  body MEDIUMTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_page_section_locale (page, section, locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Daily spinner state (server-authoritative): per-user quota, bonus spins, and shard collectibles
CREATE TABLE IF NOT EXISTS user_spin_state (
  user_id INT NOT NULL,
  used_today INT NOT NULL DEFAULT 0,
  last_reset_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  lock_until DATETIME NULL,
  collectibles INT NOT NULL DEFAULT 0,
  bonus INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  modified_by INT NULL,
  isDeleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data
INSERT INTO partners (name, category, website) VALUES
  ('City Coffee', 'food', 'https://example.com'),
  ('Fit&Go Gym', 'wellness', 'https://example.com'),
  ('FuelUp', 'mobility', 'https://example.com');

INSERT INTO offers (partner_id, title, description, price_cents, tag) VALUES
  (1, 'Free 10th Coffee', 'Collect stamps and redeem a free coffee.', 0, 'starter'),
  (2, '10% Off Monthly Pass', 'Guaranteed discount at partner gyms.', 0, 'limited'),
  (3, 'Fuel Cashback 1%', 'Cashback coupon at FuelUp stations.', 0, 'hot');

INSERT INTO categories (slug, name) VALUES
 ('starter','Starter'), ('limited','Limited'), ('hot','Hot')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Seed cases with proper category linkage
INSERT INTO cases (slug, title, base_price_cents, tag, category_id)
SELECT 'starter-sparks', 'Starter Sparks', 299, 'starter', c.id FROM categories c WHERE c.slug='starter'
ON DUPLICATE KEY UPDATE title=VALUES(title), base_price_cents=VALUES(base_price_cents), tag=VALUES(tag), category_id=VALUES(category_id);

INSERT INTO cases (slug, title, base_price_cents, tag, category_id)
SELECT 'neon-rush', 'Neon Rush', 649, 'hot', c.id FROM categories c WHERE c.slug='hot'
ON DUPLICATE KEY UPDATE title=VALUES(title), base_price_cents=VALUES(base_price_cents), tag=VALUES(tag), category_id=VALUES(category_id);

INSERT INTO cases (slug, title, base_price_cents, tag, category_id)
SELECT 'limited-pulse', 'Limited Pulse', 1299, 'limited', c.id FROM categories c WHERE c.slug='limited'
ON DUPLICATE KEY UPDATE title=VALUES(title), base_price_cents=VALUES(base_price_cents), tag=VALUES(tag), category_id=VALUES(category_id);

-- Seed case items using subselects to keep referential integrity stable
INSERT INTO case_items (case_id, offer_id, title, value_cents, weight)
SELECT (SELECT id FROM cases WHERE slug='starter-sparks'), (SELECT id FROM offers WHERE title='Free 10th Coffee'), 'Coffee Stamp x1', 0, 50
WHERE NOT EXISTS (
  SELECT 1 FROM case_items WHERE case_id=(SELECT id FROM cases WHERE slug='starter-sparks') AND title='Coffee Stamp x1'
);

INSERT INTO case_items (case_id, offer_id, title, value_cents, weight)
SELECT (SELECT id FROM cases WHERE slug='starter-sparks'), (SELECT id FROM offers WHERE title='Free 10th Coffee'), 'Coffee Stamp x2', 0, 25
WHERE NOT EXISTS (
  SELECT 1 FROM case_items WHERE case_id=(SELECT id FROM cases WHERE slug='starter-sparks') AND title='Coffee Stamp x2'
);

INSERT INTO case_items (case_id, offer_id, title, value_cents, weight)
SELECT (SELECT id FROM cases WHERE slug='neon-rush'), (SELECT id FROM offers WHERE title='10% Off Monthly Pass'), 'Gym Discount 10%', 0, 25
WHERE NOT EXISTS (
  SELECT 1 FROM case_items WHERE case_id=(SELECT id FROM cases WHERE slug='neon-rush') AND title='Gym Discount 10%'
);

INSERT INTO case_items (case_id, offer_id, title, value_cents, weight)
SELECT (SELECT id FROM cases WHERE slug='limited-pulse'), (SELECT id FROM offers WHERE title='Fuel Cashback 1%'), 'Fuel Cashback', 0, 10
WHERE NOT EXISTS (
  SELECT 1 FROM case_items WHERE case_id=(SELECT id FROM cases WHERE slug='limited-pulse') AND title='Fuel Cashback'
);

INSERT INTO oauth_providers (name) VALUES ('google'), ('discord'), ('facebook'), ('x'), ('steam')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO ranks (code, name, min_points) VALUES
 ('recruit','Recruit',0), ('bronze','Bronze',100), ('silver','Silver',300), ('gold','Gold',600)
ON DUPLICATE KEY UPDATE name=VALUES(name), min_points=VALUES(min_points);

-- Seed admin user (password will be set by seed.php)
INSERT INTO users (email, password_hash, display_name, locale, is_admin)
VALUES ('admin@admin.com', '$2y$10$placeholderhashforadmin', 'Administrator', 'en', 1)
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name), locale=VALUES(locale), is_admin=VALUES(is_admin);

-- Translations are seeded by database/seed.php

-- Notes:
-- - user_wallets + wallet_transactions provide a full ledger for purchases/sales/bonuses.
-- - case_items supports finite and code-based stock; item_codes store unique redeemables.
-- - case_open_history includes price_paid_cents and tx references for reconciliation.

