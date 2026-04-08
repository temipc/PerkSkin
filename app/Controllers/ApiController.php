<?php
namespace App\Controllers;

use App\Services\Database;
use App\Services\LocalizationService;
use App\Services\PageBuilderService;
use App\Services\TwoFactorService;
use App\Controllers\AuthController as Auth;
use PDO;

class ApiController
{
    private ?Database $db;
    private LocalizationService $i18n;
    private ?TwoFactorService $twoFactor;
    private PageBuilderService $pageBuilder;

    public function __construct(LocalizationService $i18n, ?Database $db)
    {
        $this->db = $db;
        $this->i18n = $i18n;
        $this->pageBuilder = new PageBuilderService($db);
        $this->twoFactor = $db ? new TwoFactorService($db) : null;
        $this->twoFactor?->ensureSchema();
        $this->ensureUserProfileSchema();
        $this->ensureUserProfileDetailsSchema();
        $this->ensureClientStateSchema();
        $this->ensureMarketOfferSchema();
        $this->ensureChatSchema();
        $this->ensureCaseCatalogSchema();
        $this->ensureEventsSchema();
        header('Content-Type: application/json; charset=utf-8');
    }

    public function handle(): void
    {
        $action = $_GET['action'] ?? '';
        if (!$this->db) { http_response_code(503); echo json_encode(['error'=>'db_unavailable']); return; }
        switch ($action) {
            case 'saveTranslation': $this->saveTranslation(); break;
            case 'listTranslations': $this->listTranslations(); break;
            case 'syncTranslations': $this->syncTranslations(); break;
            case 'listSessions': $this->listSessions(); break;
            case 'listHistory': $this->listHistory(); break;
            case 'walletBalance': $this->walletBalance(); break;
            case 'walletTransactions': $this->walletTransactions(); break;
            case 'gemTransactions': $this->gemTransactions(); break;
            case 'walletAdjust': $this->walletAdjust(); break;
            case 'recordCaseHistory': $this->recordCaseHistory(); break;
            case 'gemsBalance': $this->gemsBalance(); break;
            case 'gemsAdjust': $this->gemsAdjust(); break;
            case 'getProfile': $this->getProfile(); break;
            case 'saveProfile': $this->saveProfile(); break;
            case 'getTwoFactorStatus': $this->getTwoFactorStatus(); break;
            case 'beginTwoFactorSetup': $this->beginTwoFactorSetup(); break;
            case 'confirmTwoFactorSetup': $this->confirmTwoFactorSetup(); break;
            case 'disableTwoFactor': $this->disableTwoFactor(); break;
            case 'listContentSections': $this->listContentSections(); break;
            case 'listEvents': $this->listEvents(); break;
            case 'saveEvent': $this->saveEvent(); break;
            case 'deleteEvent': $this->deleteEvent(); break;
            case 'listInventory': $this->listInventory(); break;
            case 'saveContentSection': $this->saveContentSection(); break;
            case 'getClientState': $this->getClientState(); break;
            case 'saveClientState': $this->saveClientState(); break;
            case 'listBuilderPages': $this->listBuilderPages(); break;
            case 'listBuilderModules': $this->listBuilderModules(); break;
            case 'saveBuilderLayout': $this->saveBuilderLayout(); break;
            case 'saveBuilderAccess': $this->saveBuilderAccess(); break;
            case 'listChatMessages': $this->listChatMessages(); break;
            case 'postChatMessage': $this->postChatMessage(); break;
            case 'spinState': $this->spinState(); break;
            case 'spinAdjust': $this->spinAdjust(); break;
            case 'listMarketOffers': $this->listMarketOffers(); break;
            case 'saveMarketOffer': $this->saveMarketOffer(); break;
            case 'saveMarketBid': $this->saveMarketBid(); break;
            case 'updateMarketOffer': $this->updateMarketOffer(); break;
            case 'uploadCaseImage': $this->uploadCaseImage(); break;
            case 'cleanupTranslations': $this->cleanupTranslations(); break;
            // Admin catalog
            case 'listProducts': $this->listProducts(); break;
            case 'saveProduct': $this->saveProduct(); break;
            case 'deleteProduct': $this->deleteProduct(); break;
            case 'listProductCategories': $this->listProductCategories(); break;
            case 'saveProductCategory': $this->saveProductCategory(); break;
            case 'deleteProductCategory': $this->deleteProductCategory(); break;
            case 'listProductBundles': $this->listProductBundles(); break;
            case 'saveProductBundle': $this->saveProductBundle(); break;
            case 'deleteProductBundle': $this->deleteProductBundle(); break;
            case 'listProductBundleItems': $this->listProductBundleItems(); break;
            case 'saveProductBundleItem': $this->saveProductBundleItem(); break;
            case 'deleteProductBundleItem': $this->deleteProductBundleItem(); break;
            case 'listCases': $this->listCases(); break;
            case 'saveCase': $this->saveCase(); break;
            case 'deleteCase': $this->deleteCase(); break;
            case 'listCaseMetaOptions': $this->listCaseMetaOptions(); break;
            case 'saveCaseMetaOption': $this->saveCaseMetaOption(); break;
            case 'deleteCaseMetaOption': $this->deleteCaseMetaOption(); break;
            case 'listCaseItems': $this->listCaseItems(); break;
            case 'saveCaseItem': $this->saveCaseItem(); break;
            case 'deleteCaseItem': $this->deleteCaseItem(); break;
            case 'listBadges': $this->listBadges(); break;
            case 'saveBadge': $this->saveBadge(); break;
            case 'deleteBadge': $this->deleteBadge(); break;
            case 'login': (new Auth($this->i18n, $this->db))->handleLoginJson(); break;
            case 'verifyTwoFactor': (new Auth($this->i18n, $this->db))->handleVerifyTwoFactorJson(); break;
            case 'register': (new Auth($this->i18n, $this->db))->handleRegisterJson(); break;
            default: echo json_encode(['ok'=>true]);
        }
    }

    private function getProfile(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $pdo = $this->db->pdo();
        try {
            $st = $pdo->prepare('
                SELECT
                    u.display_name,
                    u.locale,
                    IFNULL(u.is_company,0) as is_company,
                    u.vat_number,
                    COALESCE(p.preferred_currency, "USD") as preferred_currency,
                    p.billing_name,
                    p.billing_city,
                    p.billing_zip,
                    p.billing_address,
                    p.billing_country,
                    p.shipping_name,
                    p.shipping_city,
                    p.shipping_zip,
                    p.shipping_address,
                    p.shipping_country
                FROM perksin_users u
                LEFT JOIN perksin_user_profile_details p ON p.user_id = u.id
                WHERE u.id=? LIMIT 1
            ');
            $st->execute([$uid]);
            $row = $st->fetch() ?: [];
        } catch (\PDOException $e) {
            // Fallback for older schemas without is_company column
            try {
                $st = $pdo->prepare('SELECT display_name, locale, vat_number FROM perksin_users WHERE id=? LIMIT 1');
                $st->execute([$uid]);
                $row = $st->fetch() ?: [];
            } catch (\Throwable $e2) {
                $row = [];
            }
            // Ensure the key exists for consumers
            $row['is_company'] = $row['is_company'] ?? 0;
            $row['preferred_currency'] = $row['preferred_currency'] ?? 'USD';
        }
        echo json_encode(['profile'=>$row]);
    }

    private function getTwoFactorStatus(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $stmt = $this->db->pdo()->prepare('SELECT email, two_factor_enabled, two_factor_pending_secret FROM perksin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        if (!$user) { http_response_code(404); echo json_encode(['error'=>'not_found']); return; }

        $pendingSecret = (string)($user['two_factor_pending_secret'] ?? '');
        $email = (string)($user['email'] ?? '');
        $locked = $this->isTwoFactorLockedForEmail($email);
        echo json_encode([
            'enabled' => !$locked && !empty($user['two_factor_enabled']),
            'pending_setup' => !$locked && $pendingSecret !== '',
            'manual_secret' => !$locked && $pendingSecret !== '' ? $pendingSecret : null,
            'otp_auth_uri' => !$locked && $pendingSecret !== '' && $this->twoFactor ? $this->twoFactor->buildOtpAuthUri($email, $pendingSecret) : null,
            'locked' => $locked,
        ]);
    }

    private function beginTwoFactorSetup(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        if (!$this->twoFactor) { http_response_code(503); echo json_encode(['error'=>'unavailable']); return; }

        $uid = (int)$_SESSION['user_id'];
        $stmt = $this->db->pdo()->prepare('SELECT email FROM perksin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        if (!$user) { http_response_code(404); echo json_encode(['error'=>'not_found']); return; }
        if ($this->isTwoFactorLockedForEmail((string)($user['email'] ?? ''))) { http_response_code(403); echo json_encode(['error'=>'two_factor_locked']); return; }

        $secret = $this->twoFactor->generateSecret();
        $upd = $this->db->pdo()->prepare('UPDATE perksin_users SET two_factor_pending_secret = ? WHERE id = ?');
        $upd->execute([$secret, $uid]);

        echo json_encode([
            'ok' => true,
            'manual_secret' => $secret,
            'otp_auth_uri' => $this->twoFactor->buildOtpAuthUri((string)$user['email'], $secret),
        ]);
    }

    private function confirmTwoFactorSetup(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        if (!$this->twoFactor) { http_response_code(503); echo json_encode(['error'=>'unavailable']); return; }

        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $code = trim((string)($d['code'] ?? ''));
        if ($code === '') { http_response_code(400); echo json_encode(['error'=>'required_code']); return; }
        $stUser = $this->db->pdo()->prepare('SELECT email FROM perksin_users WHERE id = ? LIMIT 1');
        $stUser->execute([$uid]);
        $email = (string)($stUser->fetchColumn() ?: '');
        if ($this->isTwoFactorLockedForEmail($email)) { http_response_code(403); echo json_encode(['error'=>'two_factor_locked']); return; }

        $stmt = $this->db->pdo()->prepare('SELECT two_factor_pending_secret FROM perksin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $secret = (string)($stmt->fetchColumn() ?: '');
        if ($secret === '') { http_response_code(400); echo json_encode(['error'=>'setup_not_started']); return; }
        if (!$this->twoFactor->verifyCode($secret, $code)) { http_response_code(401); echo json_encode(['error'=>'invalid_two_factor_code']); return; }

        $upd = $this->db->pdo()->prepare('UPDATE perksin_users SET two_factor_enabled = 1, two_factor_secret = ?, two_factor_pending_secret = NULL, two_factor_confirmed_at = ? WHERE id = ?');
        $upd->execute([$secret, date('Y-m-d H:i:s'), $uid]);
        echo json_encode(['ok'=>true]);
    }

    private function disableTwoFactor(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $upd = $this->db->pdo()->prepare('UPDATE perksin_users SET two_factor_enabled = 0, two_factor_secret = NULL, two_factor_pending_secret = NULL, two_factor_confirmed_at = NULL WHERE id = ?');
        $upd->execute([$uid]);
        echo json_encode(['ok'=>true]);
    }

    private function isTwoFactorLockedForEmail(string $email): bool
    {
        return strtolower(trim($email)) === 'admin@admin.com';
    }

    private function saveProfile(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $display = trim((string)($d['display_name'] ?? '')) ?: null;
        $locale = strtolower(trim((string)($d['locale'] ?? '')));
        if (!in_array($locale, ['en','hu'], true)) $locale = 'en';
        $currency = strtoupper(trim((string)($d['currency'] ?? 'USD')));
        if (!in_array($currency, ['USD','EUR','HUF'], true)) $currency = 'USD';
        $isCompany = !empty($d['is_company']) ? 1 : 0;
        $vat = trim((string)($d['vat_number'] ?? ''));
        if ($isCompany && $vat === '') { http_response_code(400); echo json_encode(['error'=>'vat_required']); return; }
        $pdo = $this->db->pdo();
        $st = $pdo->prepare('UPDATE perksin_users SET display_name=COALESCE(?,display_name), locale=?, is_company=?, vat_number=? WHERE id=?');
        $st->execute([$display, $locale, $isCompany, ($vat!==''?$vat:null), $uid]);
        $st = $pdo->prepare('
            INSERT INTO perksin_user_profile_details (
                user_id, preferred_currency,
                billing_name, billing_city, billing_zip, billing_address, billing_country,
                shipping_name, shipping_city, shipping_zip, shipping_address, shipping_country
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT(user_id) DO UPDATE SET
                preferred_currency=excluded.preferred_currency,
                billing_name=excluded.billing_name,
                billing_city=excluded.billing_city,
                billing_zip=excluded.billing_zip,
                billing_address=excluded.billing_address,
                billing_country=excluded.billing_country,
                shipping_name=excluded.shipping_name,
                shipping_city=excluded.shipping_city,
                shipping_zip=excluded.shipping_zip,
                shipping_address=excluded.shipping_address,
                shipping_country=excluded.shipping_country
        ');
        $st->execute([
            $uid,
            $currency,
            $this->nullableTrim($d['billing_name'] ?? null),
            $this->nullableTrim($d['billing_city'] ?? null),
            $this->nullableTrim($d['billing_zip'] ?? null),
            $this->nullableTrim($d['billing_address'] ?? null),
            $this->nullableTrim($d['billing_country'] ?? null),
            $this->nullableTrim($d['shipping_name'] ?? null),
            $this->nullableTrim($d['shipping_city'] ?? null),
            $this->nullableTrim($d['shipping_zip'] ?? null),
            $this->nullableTrim($d['shipping_address'] ?? null),
            $this->nullableTrim($d['shipping_country'] ?? null),
        ]);
        // Refresh session flags
        $_SESSION['is_company'] = $isCompany;
        echo json_encode(['ok'=>true]);
    }

    private function parseJson(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $trimmed = trim((string)($value ?? ''));
        return $trimmed !== '' ? $trimmed : null;
    }

    private function ensureUserProfileSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $columns = [];

        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_users)')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        } else {
            $rows = $pdo->query('SHOW COLUMNS FROM perksin_users')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
        }

        if (!in_array('is_company', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_users ADD COLUMN is_company INTEGER NOT NULL DEFAULT 0'
                : 'ALTER TABLE perksin_users ADD COLUMN is_company TINYINT(1) NOT NULL DEFAULT 0');
        }

        if (!in_array('vat_number', $columns, true)) {
            $pdo->exec('ALTER TABLE perksin_users ADD COLUMN vat_number VARCHAR(64) NULL');
        }
    }

    private function ensureUserProfileDetailsSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_user_profile_details (
                    user_id INTEGER PRIMARY KEY,
                    preferred_currency VARCHAR(3) NOT NULL DEFAULT "USD",
                    billing_name VARCHAR(191) NULL,
                    billing_city VARCHAR(120) NULL,
                    billing_zip VARCHAR(32) NULL,
                    billing_address VARCHAR(255) NULL,
                    billing_country VARCHAR(8) NULL,
                    shipping_name VARCHAR(191) NULL,
                    shipping_city VARCHAR(120) NULL,
                    shipping_zip VARCHAR(32) NULL,
                    shipping_address VARCHAR(255) NULL,
                    shipping_country VARCHAR(8) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    modified_at DATETIME,
                    FOREIGN KEY(user_id) REFERENCES perksin_users(id)
                )
            ');
            return;
        }

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_user_profile_details (
                user_id INT PRIMARY KEY,
                preferred_currency VARCHAR(3) NOT NULL DEFAULT "USD",
                billing_name VARCHAR(191) NULL,
                billing_city VARCHAR(120) NULL,
                billing_zip VARCHAR(32) NULL,
                billing_address VARCHAR(255) NULL,
                billing_country VARCHAR(8) NULL,
                shipping_name VARCHAR(191) NULL,
                shipping_city VARCHAR(120) NULL,
                shipping_zip VARCHAR(32) NULL,
                shipping_address VARCHAR(255) NULL,
                shipping_country VARCHAR(8) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES perksin_users(id)
            )
        ');
    }

    private function ensureClientStateSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_client_state (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    owner_type VARCHAR(16) NOT NULL,
                    owner_key VARCHAR(191) NOT NULL,
                    state_key VARCHAR(191) NOT NULL,
                    state_value TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(owner_type, owner_key, state_key)
                )
            ');
            return;
        }

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_client_state (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                owner_type VARCHAR(16) NOT NULL,
                owner_key VARCHAR(191) NOT NULL,
                state_key VARCHAR(191) NOT NULL,
                state_value LONGTEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_owner_state (owner_type, owner_key, state_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    private function ensureMarketOfferSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_market_offers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    owner_type VARCHAR(16) NOT NULL,
                    owner_key VARCHAR(191) NOT NULL,
                    owner_user_id INTEGER NULL,
                    inventory_item_id INTEGER NULL,
                    item_title VARCHAR(150) NOT NULL,
                    item_value_cents INTEGER NOT NULL DEFAULT 0,
                    requested_value_cents INTEGER NOT NULL DEFAULT 0,
                    currency CHAR(3) NOT NULL DEFAULT "USD",
                    status VARCHAR(16) NOT NULL DEFAULT "open",
                    accepted_bid_id INTEGER NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_market_bids (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    market_offer_id INTEGER NOT NULL,
                    bidder_user_id INTEGER NOT NULL,
                    bid_type VARCHAR(16) NOT NULL DEFAULT "inventory",
                    inventory_item_id INTEGER NULL,
                    bid_title VARCHAR(150) NOT NULL,
                    bid_value_cents INTEGER NOT NULL DEFAULT 0,
                    gem_amount INTEGER NOT NULL DEFAULT 0,
                    status VARCHAR(16) NOT NULL DEFAULT "pending",
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');
            $columns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $pdo->query('PRAGMA table_info(perksin_market_offers)')->fetchAll() ?: []);
            if (!in_array('owner_user_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN owner_user_id INTEGER NULL');
            if (!in_array('inventory_item_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN inventory_item_id INTEGER NULL');
            if (!in_array('accepted_bid_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN accepted_bid_id INTEGER NULL');
            return;
        }

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_market_offers (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                owner_type VARCHAR(16) NOT NULL,
                owner_key VARCHAR(191) NOT NULL,
                owner_user_id BIGINT NULL,
                inventory_item_id BIGINT NULL,
                item_title VARCHAR(150) NOT NULL,
                item_value_cents INT NOT NULL DEFAULT 0,
                requested_value_cents INT NOT NULL DEFAULT 0,
                currency CHAR(3) NOT NULL DEFAULT "USD",
                status VARCHAR(16) NOT NULL DEFAULT "open",
                accepted_bid_id BIGINT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_market_offers_status_created (status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_market_bids (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                market_offer_id BIGINT NOT NULL,
                bidder_user_id BIGINT NOT NULL,
                bid_type VARCHAR(16) NOT NULL DEFAULT "inventory",
                inventory_item_id BIGINT NULL,
                bid_title VARCHAR(150) NOT NULL,
                bid_value_cents INT NOT NULL DEFAULT 0,
                gem_amount INT NOT NULL DEFAULT 0,
                status VARCHAR(16) NOT NULL DEFAULT "pending",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_market_bids_offer_status (market_offer_id, status),
                INDEX idx_market_bids_bidder (bidder_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        $columns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $pdo->query('SHOW COLUMNS FROM perksin_market_offers')->fetchAll() ?: []);
        if (!in_array('owner_user_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN owner_user_id BIGINT NULL');
        if (!in_array('inventory_item_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN inventory_item_id BIGINT NULL');
        if (!in_array('accepted_bid_id', $columns, true)) $pdo->exec('ALTER TABLE perksin_market_offers ADD COLUMN accepted_bid_id BIGINT NULL');
    }

    private function ensureChatSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_chat_messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    owner_type VARCHAR(16) NOT NULL,
                    owner_key VARCHAR(191) NOT NULL,
                    display_name VARCHAR(120) NOT NULL,
                    message TEXT NOT NULL,
                    original_message TEXT NULL,
                    profanity_hits INTEGER NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS perksin_chat_moderation (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    owner_type VARCHAR(16) NOT NULL,
                    owner_key VARCHAR(191) NOT NULL,
                    hit_date VARCHAR(10) NOT NULL,
                    hit_count INTEGER NOT NULL DEFAULT 0,
                    banned_until DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    modified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(owner_type, owner_key, hit_date)
                )
            ');
            return;
        }

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_chat_messages (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                owner_type VARCHAR(16) NOT NULL,
                owner_key VARCHAR(191) NOT NULL,
                display_name VARCHAR(120) NOT NULL,
                message TEXT NOT NULL,
                original_message TEXT NULL,
                profanity_hits INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_chat_messages_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS perksin_chat_moderation (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                owner_type VARCHAR(16) NOT NULL,
                owner_key VARCHAR(191) NOT NULL,
                hit_date DATE NOT NULL,
                hit_count INT NOT NULL DEFAULT 0,
                banned_until DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_chat_moderation_owner_day (owner_type, owner_key, hit_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    private function ensureCaseCatalogSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $columns = [];

        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_cases)')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        } else {
            $rows = $pdo->query('SHOW COLUMNS FROM perksin_cases')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
        }

        if (!in_array('required_level', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_cases ADD COLUMN required_level INTEGER NOT NULL DEFAULT 1'
                : 'ALTER TABLE perksin_cases ADD COLUMN required_level INT NOT NULL DEFAULT 1');
        }
        if (!in_array('is_community', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_cases ADD COLUMN is_community INTEGER NOT NULL DEFAULT 1'
                : 'ALTER TABLE perksin_cases ADD COLUMN is_community TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!in_array('is_featured', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_cases ADD COLUMN is_featured INTEGER NOT NULL DEFAULT 0'
                : 'ALTER TABLE perksin_cases ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0');
        }
        $offerColumns = [];
        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_offers)')->fetchAll();
            $offerColumns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        } else {
            $rows = $pdo->query('SHOW COLUMNS FROM perksin_offers')->fetchAll();
            $offerColumns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
        }
        if (!in_array('category_id', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_offers ADD COLUMN category_id INTEGER NULL'
                : 'ALTER TABLE perksin_offers ADD COLUMN category_id INT NULL');
        }
        if (!in_array('product_type', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? "ALTER TABLE perksin_offers ADD COLUMN product_type TEXT NOT NULL DEFAULT 'product'"
                : "ALTER TABLE perksin_offers ADD COLUMN product_type VARCHAR(16) NOT NULL DEFAULT 'product'");
        }
        if (!in_array('use_home_spinner', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_offers ADD COLUMN use_home_spinner INTEGER NOT NULL DEFAULT 0'
                : 'ALTER TABLE perksin_offers ADD COLUMN use_home_spinner TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (!in_array('description', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_offers ADD COLUMN description TEXT NULL'
                : 'ALTER TABLE perksin_offers ADD COLUMN description TEXT NULL');
        }
        if (!in_array('valid_from', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_offers ADD COLUMN valid_from TEXT NULL'
                : 'ALTER TABLE perksin_offers ADD COLUMN valid_from DATETIME NULL');
        }
        if (!in_array('valid_until', $offerColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_offers ADD COLUMN valid_until TEXT NULL'
                : 'ALTER TABLE perksin_offers ADD COLUMN valid_until DATETIME NULL');
        }
        $caseItemColumns = [];
        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_case_items)')->fetchAll();
            $caseItemColumns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        } else {
            $rows = $pdo->query('SHOW COLUMNS FROM perksin_case_items')->fetchAll();
            $caseItemColumns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
        }
        if (!in_array('source_type', $caseItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? "ALTER TABLE perksin_case_items ADD COLUMN source_type TEXT NOT NULL DEFAULT 'offer'"
                : "ALTER TABLE perksin_case_items ADD COLUMN source_type VARCHAR(16) NOT NULL DEFAULT 'offer'");
        }
        if (!in_array('category_id', $caseItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_case_items ADD COLUMN category_id INTEGER NULL'
                : 'ALTER TABLE perksin_case_items ADD COLUMN category_id INT NULL');
        }
        if (!in_array('bundle_id', $caseItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_case_items ADD COLUMN bundle_id INTEGER NULL'
                : 'ALTER TABLE perksin_case_items ADD COLUMN bundle_id INT NULL');
        }

        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_case_meta_options (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                option_type TEXT NOT NULL,
                option_value TEXT NOT NULL,
                option_label TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                isDeleted INTEGER NOT NULL DEFAULT 0,
                created_by INTEGER NULL,
                modified_by INTEGER NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                modified_at TEXT DEFAULT CURRENT_TIMESTAMP
            )');
            $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS uq_case_meta_option_type_value ON perksin_case_meta_options(option_type, option_value)');
        } else {
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_case_meta_options (
                id INT AUTO_INCREMENT PRIMARY KEY,
                option_type VARCHAR(32) NOT NULL,
                option_value VARCHAR(64) NOT NULL,
                option_label VARCHAR(128) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                isDeleted TINYINT(1) NOT NULL DEFAULT 0,
                created_by INT NULL,
                modified_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_case_meta_option_type_value (option_type, option_value)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        }

        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_product_bundles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                created_by INTEGER NULL,
                modified_by INTEGER NULL,
                isDeleted INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                modified_at TEXT DEFAULT CURRENT_TIMESTAMP
            )');
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_product_bundle_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                bundle_id INTEGER NOT NULL,
                offer_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                created_by INTEGER NULL,
                modified_by INTEGER NULL,
                isDeleted INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                modified_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(bundle_id) REFERENCES perksin_product_bundles(id),
                FOREIGN KEY(offer_id) REFERENCES perksin_offers(id)
            )');
        } else {
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_product_bundles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                created_by INT NULL,
                modified_by INT NULL,
                isDeleted TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_product_bundle_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                bundle_id INT NOT NULL,
                offer_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_by INT NULL,
                modified_by INT NULL,
                isDeleted TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_bundle_id (bundle_id),
                INDEX idx_offer_id (offer_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        }

        if ($driver === 'sqlite') {
            $bundleItemRows = $pdo->query('PRAGMA table_info(perksin_product_bundle_items)')->fetchAll();
            $bundleItemColumns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $bundleItemRows ?: []);
        } else {
            $bundleItemRows = $pdo->query('SHOW COLUMNS FROM perksin_product_bundle_items')->fetchAll();
            $bundleItemColumns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $bundleItemRows ?: []);
        }
        if (!in_array('source_type', $bundleItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? "ALTER TABLE perksin_product_bundle_items ADD COLUMN source_type TEXT NOT NULL DEFAULT 'offer'"
                : "ALTER TABLE perksin_product_bundle_items ADD COLUMN source_type VARCHAR(16) NOT NULL DEFAULT 'offer'");
        }
        if (!in_array('source_category_id', $bundleItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_category_id INTEGER NULL'
                : 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_category_id INT NULL');
        }
        if (!in_array('source_case_id', $bundleItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_case_id INTEGER NULL'
                : 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_case_id INT NULL');
        }
        if (!in_array('source_bundle_id', $bundleItemColumns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_bundle_id INTEGER NULL'
                : 'ALTER TABLE perksin_product_bundle_items ADD COLUMN source_bundle_id INT NULL');
        }

        $this->seedCaseMetaOptionDefaults();
        $this->seedDefaultProductCatalog();
    }

    private function ensureEventsSchema(): void
    {
        if (!$this->db) return;
        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_events)')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        } else {
            $rows = $pdo->query('SHOW COLUMNS FROM perksin_events')->fetchAll();
            $columns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
        }
        if (!in_array('href', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_events ADD COLUMN href VARCHAR(255) NULL'
                : 'ALTER TABLE perksin_events ADD COLUMN href VARCHAR(255) NULL');
        }
        if (!in_array('start_at', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_events ADD COLUMN start_at TEXT NULL'
                : 'ALTER TABLE perksin_events ADD COLUMN start_at DATETIME NULL');
        }
        if (!in_array('end_at', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_events ADD COLUMN end_at TEXT NULL'
                : 'ALTER TABLE perksin_events ADD COLUMN end_at DATETIME NULL');
        }
        if (!in_array('color', $columns, true)) {
            $pdo->exec($driver === 'sqlite'
                ? 'ALTER TABLE perksin_events ADD COLUMN color VARCHAR(32) NULL'
                : 'ALTER TABLE perksin_events ADD COLUMN color VARCHAR(32) NULL');
        }
        try {
            if (in_array('date', $columns, true) && in_array('start_at', $columns, true)) {
                $pdo->exec("UPDATE perksin_events SET start_at = COALESCE(start_at, date || ' 00:00:00'), end_at = COALESCE(end_at, date || ' 23:59:59') WHERE date IS NOT NULL AND TRIM(date) <> ''");
            }
        } catch (\Throwable $e) {}
        $this->seedDefaultEvents();
    }

    private function seedDefaultEvents(): void
    {
        if (!$this->db) return;
        $pdo = $this->db->pdo();
        try {
            $count = (int)($pdo->query('SELECT COUNT(*) FROM perksin_events')->fetchColumn() ?: 0);
            if ($count > 0) return;
        } catch (\Throwable $e) {
            return;
        }
        $base = new \DateTimeImmutable('tomorrow 18:00:00');
        $defaults = [
            ['Community Warmup Drop', 'Short evening event with starter rewards.', 0, '#4da3ff'],
            ['Neon Weekend Rush', 'Weekend event with boosted case openings and live drops.', 2, '#18b38a'],
            ['Bundle Bonus Hour', 'Focused event for bundle rewards and limited box rotations.', 5, '#ff8a3d'],
            ['Late Night Battle Window', 'Night session with special event cases and faster rotations.', 8, '#ff5c8a'],
            ['Spring Limited Showcase', 'Seasonal event block with highlighted community content.', 12, '#ffd166'],
        ];
        $insert = $pdo->prepare('INSERT INTO perksin_events(date, start_at, end_at, title, description, href, color) VALUES (?,?,?,?,?,?,?)');
        foreach ($defaults as [$title, $description, $offsetDays, $color]) {
            $start = $base->modify('+' . (int)$offsetDays . ' days');
            $end = $start->modify('+2 hours');
            $insert->execute([
                $start->format('Y-m-d'),
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
                $title,
                $description,
                '/index.php?page=events',
                $color,
            ]);
        }
    }

    private function seedCaseMetaOptionDefaults(): void
    {
        $pdo = $this->db->pdo();
        $defaults = [
            ['tag', 'starter', 'Starter', 10],
            ['tag', 'hot', 'Hot', 20],
            ['tag', 'limited', 'Limited', 30],
            ['risk', 'low', 'Low', 10],
            ['risk', 'medium', 'Medium', 20],
            ['risk', 'high', 'High', 30],
            ['risk', 'very-high', 'Very High', 40],
        ];
        $check = $pdo->prepare('SELECT id FROM perksin_case_meta_options WHERE option_type = ? AND option_value = ? LIMIT 1');
        $insert = $pdo->prepare('INSERT INTO perksin_case_meta_options(option_type, option_value, option_label, sort_order, isDeleted) VALUES (?,?,?,?,0)');
        foreach ($defaults as [$type, $value, $label, $sort]) {
            $check->execute([$type, $value]);
            if (!$check->fetchColumn()) {
                $insert->execute([$type, $value, $label, $sort]);
            }
        }
    }

    private function getCaseMetaOptionsMap(string $type): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT option_value, option_label FROM perksin_case_meta_options WHERE option_type = ? AND isDeleted = 0 ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$type]);
        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $items[(string)$row['option_value']] = (string)($row['option_label'] ?? $row['option_value']);
        }
        return $items;
    }

    private function seedDefaultProductCatalog(): void
    {
        $pdo = $this->db->pdo();
        $categories = [
            ['entertainment', 'Entertainment'],
            ['fuel', 'Fuel'],
            ['badge', 'Badge'],
            ['bundle', 'Bundle'],
        ];
        $catStmt = $pdo->prepare('SELECT id FROM perksin_categories WHERE slug = ? LIMIT 1');
        $catInsert = $pdo->prepare('INSERT INTO perksin_categories(slug, name, isDeleted) VALUES (?,?,0)');
        $categoryIds = [];
        foreach ($categories as [$slug, $name]) {
            $catStmt->execute([$slug]);
            $id = (int)($catStmt->fetchColumn() ?: 0);
            if ($id <= 0) {
                $catInsert->execute([$slug, $name]);
                $id = (int)$pdo->lastInsertId();
            }
            $categoryIds[$slug] = $id;
        }
        $partnerId = (int)($pdo->query('SELECT id FROM perksin_partners ORDER BY id ASC LIMIT 1')->fetchColumn() ?: 0);
        if ($partnerId <= 0) {
            $pdo->exec("INSERT INTO perksin_partners(name,category) VALUES ('Default','catalog')");
            $partnerId = (int)$pdo->lastInsertId();
        }
        $products = [
            ['Cinema -10%', 1.49, 'entertainment', 'product', 1],
            ['Cinema -20%', 2.49, 'entertainment', 'product', 1],
            ['Fuel 3%', 0.99, 'fuel', 'product', 1],
            ['Fuel 5%', 1.79, 'fuel', 'product', 1],
            ['VIP Bronze Badge', 1.29, 'badge', 'badge', 1],
            ['VIP Silver Badge', 2.29, 'badge', 'badge', 1],
            ['Starter Saver Bundle', 3.99, 'bundle', 'bundle', 1],
            ['Family Weekend Bundle', 5.49, 'bundle', 'bundle', 1],
        ];
        $check = $pdo->prepare('SELECT id FROM perksin_offers WHERE title = ? LIMIT 1');
        $insert = $pdo->prepare('INSERT INTO perksin_offers(partner_id,title,price_cents,category_id,product_type,use_home_spinner,is_active,isDeleted) VALUES (?,?,?,?,?,?,1,0)');
        foreach ($products as [$title, $price, $catSlug, $type, $spinner]) {
            $check->execute([$title]);
            if (!$check->fetchColumn()) {
                $insert->execute([$partnerId, $title, (int)round($price * 100), $categoryIds[$catSlug] ?? null, $type, $spinner]);
            }
        }

        $badges = [
            ['VIP_BRONZE', 'VIP Bronze Badge'],
            ['VIP_SILVER', 'VIP Silver Badge'],
        ];
        $badgeCheck = $pdo->prepare('SELECT id FROM perksin_badges WHERE code = ? LIMIT 1');
        $badgeInsert = $pdo->prepare('INSERT INTO perksin_badges(code, name, isDeleted) VALUES (?,?,0)');
        foreach ($badges as [$code, $name]) {
            $badgeCheck->execute([$code]);
            if (!$badgeCheck->fetchColumn()) {
                $badgeInsert->execute([$code, $name]);
            }
        }

        $bundles = [
            'Starter Saver Bundle' => ['Cinema -10%' => 1, 'Fuel 3%' => 1],
            'Family Weekend Bundle' => ['Cinema -20%' => 1, 'Fuel 5%' => 1],
        ];
        $bundleCheck = $pdo->prepare('SELECT id FROM perksin_product_bundles WHERE name = ? LIMIT 1');
        $bundleInsert = $pdo->prepare('INSERT INTO perksin_product_bundles(name, isDeleted) VALUES (?,0)');
        $offerIdByTitleStmt = $pdo->prepare('SELECT id FROM perksin_offers WHERE title = ? LIMIT 1');
        $bundleItemCheck = $pdo->prepare('SELECT id FROM perksin_product_bundle_items WHERE bundle_id = ? AND offer_id = ? AND isDeleted = 0 LIMIT 1');
        $bundleItemInsert = $pdo->prepare('INSERT INTO perksin_product_bundle_items(bundle_id, offer_id, quantity, isDeleted) VALUES (?,?,?,0)');
        foreach ($bundles as $bundleName => $bundleItems) {
            $bundleCheck->execute([$bundleName]);
            $bundleId = (int)($bundleCheck->fetchColumn() ?: 0);
            if ($bundleId <= 0) {
                $bundleInsert->execute([$bundleName]);
                $bundleId = (int)$pdo->lastInsertId();
            }
            foreach ($bundleItems as $offerTitle => $quantity) {
                $offerIdByTitleStmt->execute([$offerTitle]);
                $offerId = (int)($offerIdByTitleStmt->fetchColumn() ?: 0);
                if ($offerId <= 0) {
                    continue;
                }
                $bundleItemCheck->execute([$bundleId, $offerId]);
                if (!$bundleItemCheck->fetchColumn()) {
                    $bundleItemInsert->execute([$bundleId, $offerId, max(1, (int)$quantity)]);
                }
            }
        }
    }

    private function listExpandedCaseItems(int $caseId): array
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('
            SELECT ci.id, ci.case_id, ci.offer_id, ci.category_id, ci.bundle_id, ci.source_type, ci.title, ci.value_cents, ci.weight,
                   o.title AS offer_title, o.price_cents AS offer_price_cents,
                   c.name AS category_name,
                   b.name AS bundle_name
            FROM perksin_case_items ci
            LEFT JOIN perksin_offers o ON o.id = ci.offer_id
            LEFT JOIN perksin_categories c ON c.id = ci.category_id
            LEFT JOIN perksin_product_bundles b ON b.id = ci.bundle_id
            WHERE ci.case_id = ? AND ci.isDeleted = 0
            ORDER BY ci.id ASC
        ');
        $stmt->execute([$caseId]);
        $rows = $stmt->fetchAll() ?: [];
        $items = [];
        foreach ($rows as $row) {
            $sourceType = (string)($row['source_type'] ?? 'offer');
            $weight = max(1, (int)($row['weight'] ?? 1));
            if ($sourceType === 'category' && !empty($row['category_id'])) {
                $stOffers = $pdo->prepare('SELECT id, title, price_cents FROM perksin_offers WHERE category_id = ? AND isDeleted = 0 ORDER BY id ASC');
                $stOffers->execute([(int)$row['category_id']]);
                foreach ($stOffers->fetchAll() ?: [] as $offer) {
                    $items[] = [
                        'id' => (int)$row['id'],
                        'name' => (string)$offer['title'],
                        'value' => ((int)($offer['price_cents'] ?? 0)) / 100.0,
                        'weight' => $weight,
                        'offer_id' => (int)$offer['id'],
                        'source_type' => 'category',
                    ];
                }
                continue;
            }
            if ($sourceType === 'bundle' && !empty($row['bundle_id'])) {
                $stBundle = $pdo->prepare('
                    SELECT bi.offer_id, bi.quantity, o.title, o.price_cents
                    FROM perksin_product_bundle_items bi
                    INNER JOIN perksin_offers o ON o.id = bi.offer_id
                    WHERE bi.bundle_id = ? AND bi.isDeleted = 0 AND o.isDeleted = 0
                    ORDER BY bi.id ASC
                ');
                $stBundle->execute([(int)$row['bundle_id']]);
                $bundleItems = $stBundle->fetchAll() ?: [];
                $bundleTitle = (string)($row['bundle_name'] ?? $row['title'] ?? 'Bundle');
                $bundleValueCents = 0;
                foreach ($bundleItems as $bundleItem) {
                    $bundleValueCents += ((int)($bundleItem['price_cents'] ?? 0)) * max(1, (int)($bundleItem['quantity'] ?? 1));
                }
                $items[] = [
                    'id' => (int)$row['id'],
                    'name' => $bundleTitle,
                    'value' => $bundleValueCents / 100.0,
                    'weight' => $weight,
                    'offer_id' => null,
                    'source_type' => 'bundle',
                ];
                continue;
            }
            $valueCents = (int)($row['offer_price_cents'] ?? $row['value_cents'] ?? 0);
            $items[] = [
                'id' => (int)$row['id'],
                'name' => (string)($row['offer_title'] ?: $row['title']),
                'value' => $valueCents / 100.0,
                'weight' => $weight,
                'offer_id' => isset($row['offer_id']) ? (int)$row['offer_id'] : null,
                'source_type' => $sourceType,
            ];
        }
        return $items;
    }

    private function currentClientOwner(): array
    {
        if (!empty($_SESSION['user_id'])) {
            return ['user', (string)(int)$_SESSION['user_id']];
        }

        return ['session', session_id()];
    }

    private function isCurrentOwner(string $ownerType, string $ownerKey): bool
    {
        [$type, $key] = $this->currentClientOwner();
        return $ownerType === $type && $ownerKey === $key;
    }

    private function currentDisplayName(): string
    {
        if (!empty($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->pdo()->prepare('SELECT display_name, email FROM perksin_users WHERE id = ? LIMIT 1');
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch() ?: [];
                $display = trim((string)($row['display_name'] ?? ''));
                if ($display !== '') {
                    return $display;
                }
            } catch (\Throwable $e) {
            }
            return 'User #' . (int)$_SESSION['user_id'];
        }

        return 'Guest-' . substr(preg_replace('/[^a-z0-9]/i', '', session_id()), 0, 6);
    }

    private function requireAuthenticatedUserId(): int
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function getActiveInventoryItem(PDO $pdo, int $inventoryId, int $userId): ?array
    {
        $stmt = $pdo->prepare('
            SELECT ui.id, ui.user_id, ui.item_title, ui.item_value_cents, ui.code_id, ui.status, ui.created_at, ic.code AS coupon_code
            FROM perksin_user_inventory ui
            LEFT JOIN perksin_item_codes ic ON ic.id = ui.code_id
            WHERE ui.id = ? AND ui.user_id = ? AND ui.status = "active"
            LIMIT 1
        ');
        $stmt->execute([$inventoryId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function ensureWalletRow(PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT id FROM perksin_user_wallets WHERE user_id = ? AND currency = "USD" LIMIT 1');
        $stmt->execute([$userId]);
        $walletId = (int)($stmt->fetchColumn() ?: 0);
        if ($walletId > 0) {
            return $walletId;
        }
        $pdo->prepare('INSERT INTO perksin_user_wallets(user_id, currency, balance_cents, balance_milli) VALUES (?, "USD", 0, 0)')->execute([$userId]);
        return (int)$pdo->lastInsertId();
    }

    private function adjustWalletMilli(PDO $pdo, int $userId, int $amountMilli, string $type, string $referenceType, int $referenceId, string $description): void
    {
        $walletId = $this->ensureWalletRow($pdo, $userId);
        if ($amountMilli < 0) {
            $check = $pdo->prepare('SELECT balance_milli FROM perksin_user_wallets WHERE id = ? LIMIT 1');
            $check->execute([$walletId]);
            $balance = (int)($check->fetchColumn() ?: 0);
            if ($balance < abs($amountMilli)) {
                throw new \RuntimeException('insufficient_wallet');
            }
        }
        $amountCents = (int)round($amountMilli / 10);
        $pdo->prepare('INSERT INTO perksin_wallet_transactions(wallet_id,user_id,amount_cents,amount_milli,currency,type,reference_type,reference_id,description) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$walletId, $userId, $amountCents, $amountMilli, 'USD', $type, $referenceType, $referenceId, $description]);
        $pdo->prepare('UPDATE perksin_user_wallets SET balance_cents = balance_cents + ?, balance_milli = balance_milli + ? WHERE id = ?')
            ->execute([$amountCents, $amountMilli, $walletId]);
    }

    private function ensureGemRow(PDO $pdo, int $userId): void
    {
        $stmt = $pdo->prepare('SELECT id FROM perksin_user_gems WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn()) {
            return;
        }
        $pdo->prepare('INSERT INTO perksin_user_gems(user_id, balance) VALUES (?, 0)')->execute([$userId]);
    }

    private function adjustGems(PDO $pdo, int $userId, int $amount, string $type, string $referenceType, int $referenceId, string $description): void
    {
        $this->ensureGemRow($pdo, $userId);
        if ($amount < 0) {
            $stmt = $pdo->prepare('SELECT balance FROM perksin_user_gems WHERE user_id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $balance = (int)($stmt->fetchColumn() ?: 0);
            if ($balance < abs($amount)) {
                throw new \RuntimeException('insufficient_gems');
            }
        }
        $pdo->prepare('INSERT INTO perksin_gem_transactions(user_id, amount, type, reference_type, reference_id, description) VALUES (?,?,?,?,?,?)')
            ->execute([$userId, $amount, $type, $referenceType, $referenceId, $description]);
        $pdo->prepare('UPDATE perksin_user_gems SET balance = balance + ? WHERE user_id = ?')->execute([$amount, $userId]);
    }

    private function inventoryTradeExpiryReason(PDO $pdo, array $inventoryItem): ?string
    {
        $title = trim((string)($inventoryItem['item_title'] ?? ''));
        if ($title === '') {
            return 'invalid_inventory';
        }
        $stmt = $pdo->prepare('SELECT valid_until FROM perksin_offers WHERE title = ? AND isDeleted = 0 ORDER BY id DESC LIMIT 1');
        $stmt->execute([$title]);
        $validUntil = $stmt->fetchColumn();
        if (!$validUntil) {
            return null;
        }
        $ts = strtotime((string)$validUntil);
        if ($ts !== false && $ts < time()) {
            return 'expired_item';
        }
        return null;
    }

    private function moderateChatMessage(string $text): array
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        $masked = $normalized;
        $profanityHits = 0;
        $patterns = [
            '/\b(fuck|fucking|fucker|shit|bullshit|bitch|asshole|motherfucker|cunt|dick|pussy|bastard|wanker|slut|whore|retard)\b/iu',
            '/\b(kurva|bazd+meg|baszd+meg|geci|fasz|faszom|faszszopo|faszfej|picsa|segg|seggfej|szar|szopj|szopd|buzi|anyad|anyád|rohadt|rohadj|ribanc)\b/iu',
        ];

        foreach ($patterns as $pattern) {
            $masked = preg_replace_callback($pattern, function (array $matches) use (&$profanityHits): string {
                $word = (string)($matches[0] ?? '');
                $profanityHits++;
                return str_repeat('*', max(3, mb_strlen($word, 'UTF-8')));
            }, $masked) ?? $masked;
        }

        $masked = preg_replace_callback('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', function (array $matches): string {
            $word = (string)($matches[0] ?? '');
            return str_repeat('*', max(6, strlen($word)));
        }, $masked) ?? $masked;

        $masked = preg_replace_callback('/(?<!\w)(?:\+?\d[\d\s\-\(\)]{7,}\d)(?!\w)/u', function (array $matches): string {
            $word = (string)($matches[0] ?? '');
            return str_repeat('*', max(6, strlen(preg_replace('/\s+/', '', $word) ?? $word)));
        }, $masked) ?? $masked;

        return [
            'original' => $normalized,
            'masked' => trim($masked),
            'profanity_hits' => $profanityHits,
        ];
    }

    private function getChatModerationState(string $ownerType, string $ownerKey): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->pdo()->prepare('SELECT hit_count, banned_until FROM perksin_chat_moderation WHERE owner_type = ? AND owner_key = ? AND hit_date = ? LIMIT 1');
        $stmt->execute([$ownerType, $ownerKey, $today]);
        $row = $stmt->fetch() ?: ['hit_count' => 0, 'banned_until' => null];
        $bannedUntil = isset($row['banned_until']) ? (string)$row['banned_until'] : null;
        $isBanned = $bannedUntil !== null && $bannedUntil !== '' && strtotime($bannedUntil) > time();
        return [
            'date' => $today,
            'hit_count' => (int)($row['hit_count'] ?? 0),
            'banned_until' => $isBanned ? $bannedUntil : null,
            'is_banned' => $isBanned,
        ];
    }

    private function registerChatProfanity(string $ownerType, string $ownerKey, int $hits): array
    {
        $state = $this->getChatModerationState($ownerType, $ownerKey);
        if ($hits <= 0) {
            return $state;
        }

        $newHits = $state['hit_count'] + $hits;
        $bannedUntil = $state['banned_until'];
        if ($newHits > 10) {
            $bannedUntil = date('Y-m-d H:i:s', time() + 24 * 3600);
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('
                INSERT INTO perksin_chat_moderation (owner_type, owner_key, hit_date, hit_count, banned_until, modified_at)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(owner_type, owner_key, hit_date) DO UPDATE SET
                    hit_count = excluded.hit_count,
                    banned_until = excluded.banned_until,
                    modified_at = CURRENT_TIMESTAMP
            ');
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO perksin_chat_moderation (owner_type, owner_key, hit_date, hit_count, banned_until)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE hit_count = VALUES(hit_count), banned_until = VALUES(banned_until)
            ');
        }
        $stmt->execute([$ownerType, $ownerKey, $state['date'], $newHits, $bannedUntil]);

        return [
            'date' => $state['date'],
            'hit_count' => $newHits,
            'banned_until' => $bannedUntil,
            'is_banned' => $bannedUntil !== null && strtotime((string)$bannedUntil) > time(),
        ];
    }

    private function decodeStateValue(?string $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }

    private function getClientState(): void
    {
        [$ownerType, $ownerKey] = $this->currentClientOwner();
        $stmt = $this->db->pdo()->prepare('SELECT state_key, state_value FROM perksin_client_state WHERE owner_type = ? AND owner_key = ?');
        $stmt->execute([$ownerType, $ownerKey]);
        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $items[(string)$row['state_key']] = $this->decodeStateValue(isset($row['state_value']) ? (string)$row['state_value'] : null);
        }
        echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function saveClientState(): void
    {
        $d = $this->parseJson();
        $key = trim((string)($d['key'] ?? ''));
        if ($key === '') {
            http_response_code(400);
            echo json_encode(['error' => 'bad_key']);
            return;
        }

        [$ownerType, $ownerKey] = $this->currentClientOwner();
        $pdo = $this->db->pdo();
        $delete = !empty($d['delete']);

        if ($delete) {
            $stmt = $pdo->prepare('DELETE FROM perksin_client_state WHERE owner_type = ? AND owner_key = ? AND state_key = ?');
            $stmt->execute([$ownerType, $ownerKey, $key]);
            echo json_encode(['ok' => true, 'deleted' => true]);
            return;
        }

        $valueJson = json_encode($d['value'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('
                INSERT INTO perksin_client_state (owner_type, owner_key, state_key, state_value, modified_at)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(owner_type, owner_key, state_key) DO UPDATE SET
                    state_value = excluded.state_value,
                    modified_at = CURRENT_TIMESTAMP
            ');
            $stmt->execute([$ownerType, $ownerKey, $key, $valueJson]);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO perksin_client_state (owner_type, owner_key, state_key, state_value)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE state_value = VALUES(state_value), modified_at = CURRENT_TIMESTAMP
            ');
            $stmt->execute([$ownerType, $ownerKey, $key, $valueJson]);
        }

        echo json_encode(['ok' => true]);
    }

    private function saveTranslation(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $data = $this->parseJson();
        $key = trim((string)($data['key'] ?? ''));
        $locale = strtolower(trim((string)($data['locale'] ?? '')));
        $value = (string)($data['value'] ?? '');
        if ($key === '' || !in_array($locale, ['en','hu'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        // Guardrail: ignore noisy keys such as amounts, numbers-only or icon-only strings
        if ($this->isNoiseKey($key)) { echo json_encode(['ok'=>true, 'ignored'=>true]); return; }
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
        if ($value === '') {
            // delete override
            $stmt = $pdo->prepare('DELETE FROM perksin_translations_kv WHERE `key`=? AND locale=?');
            $stmt->execute([$key, $locale]);
            $pdo->commit();
            echo json_encode(['ok'=>true,'deleted'=>true,'key'=>$key,'locale'=>$locale]);
            return;
        }
        $this->upsertTranslationValue($pdo, $key, $locale, $value);
        $check = $pdo->prepare('SELECT value FROM perksin_translations_kv WHERE `key` = ? AND locale = ? LIMIT 1');
        $check->execute([$key, $locale]);
        $savedValue = $check->fetchColumn();
        if ((string)$savedValue !== $value) {
            throw new \RuntimeException('save_verification_failed');
        }
        $pdo->commit();
        echo json_encode(['ok'=>true,'key'=>$key,'locale'=>$locale,'value'=>(string)$savedValue]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'save_failed']);
        }
    }

    private function listTranslations(): void
    {
        $en = $this->i18n->all('en');
        $hu = $this->i18n->all('hu');
        // Filter out noise keys (amounts, numbers-only, icon-only etc.) from the payload
        $allKeys = array_unique(array_merge(array_keys($en), array_keys($hu)));
        foreach ($allKeys as $k) {
            if ($this->isNoiseKey($k)) { unset($en[$k], $hu[$k]); }
        }
        echo json_encode(['en'=>$en,'hu'=>$hu], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    private function syncTranslations(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $data = $this->parseJson();
        $sourceLocale = strtolower(trim((string)($data['sourceLocale'] ?? 'en')));
        $targetLocale = strtolower(trim((string)($data['targetLocale'] ?? 'hu')));
        if (!in_array($sourceLocale, ['en', 'hu'], true) || !in_array($targetLocale, ['en', 'hu'], true) || $sourceLocale === $targetLocale) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        $source = $this->i18n->all($sourceLocale);
        $target = $this->i18n->all($targetLocale);
        $pdo = $this->db->pdo();
        $updated = 0;
        foreach ($source as $key => $sourceValue) {
            $key = trim((string)$key);
            if ($key === '' || $this->isNoiseKey($key)) {
                continue;
            }
            $targetValue = trim((string)($target[$key] ?? ''));
            if ($targetValue !== '') {
                continue;
            }
            $this->upsertTranslationValue($pdo, $key, $targetLocale, (string)$sourceValue);
            $updated++;
        }

        echo json_encode(['ok' => true, 'updated' => $updated]);
    }

    private function upsertTranslationValue(PDO $pdo, string $key, string $locale, string $value): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('INSERT INTO perksin_translations_kv (`key`, locale, value) VALUES (?,?,?) ON CONFLICT(`key`, locale) DO UPDATE SET value=excluded.value');
            $stmt->execute([$key, $locale, $value]);
            return;
        }
        $stmt = $pdo->prepare('INSERT INTO perksin_translations_kv (`key`, locale, value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
        $stmt->execute([$key, $locale, $value]);
    }

    /**
     * Heuristic filter for noisy translation keys. Returns true if the key looks like a number/amount or symbol-only.
     */
    private function isNoiseKey(string $key): bool
    {
        $s = trim($key);
        if ($s === '') return true;
        // Must contain at least one letter
        if (!preg_match('/\p{L}/u', $s)) return true;
        // Price-like patterns: optional +/- then digits and optional currency
        if (preg_match('/^[\+\-]?\s*\d[\d\s\.,]*(?:\s*(USD|EUR|HUF|Ft|US\$|\$|€|£|¥))?$/iu', $s)) return true;
        // If letters are less than 20% of visible characters, treat as noise
        $letters = preg_match_all('/\p{L}/u', $s);
        $compact = preg_replace('/\s+/u', '', $s);
        $len = mb_strlen($compact ?: $s, 'UTF-8');
        if ($len > 0 && $letters / $len < 0.2) return true;
        // Pure symbol/icon lines
        if (preg_match('/^[★☆⭐️\p{S}\p{P}\s]+$/u', $s)) return true;
        return false;
    }

    private function cleanupTranslations(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $pdo = $this->db->pdo();
        $stmt = $pdo->query('SELECT DISTINCT `key` FROM perksin_translations_kv');
        $keys = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        $deleted = 0;
        foreach ($keys as $k) {
            if ($this->isNoiseKey((string)$k)) {
                $del = $pdo->prepare('DELETE FROM perksin_translations_kv WHERE `key` = ?');
                $del->execute([(string)$k]);
                $deleted += $del->rowCount();
            }
        }
        echo json_encode(['ok'=>true, 'deletedRows'=>$deleted]);
    }

    private function listSessions(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM perksin_user_sessions WHERE user_id = ? ORDER BY start_at DESC LIMIT 500');
        $stmt->execute([$uid]);
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }

    private function listHistory(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM perksin_case_open_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 1000');
        $stmt->execute([$uid]);
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }

    private function walletBalance(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $pdo = $this->db->pdo();
        // default currency USD
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, currency, balance_cents, balance_milli FROM perksin_user_wallets WHERE user_id = ? AND currency = "USD"');
            $stmt->execute([$uid]);
            $w = $stmt->fetch();
            if (!$w) {
                $pdo->prepare('INSERT INTO perksin_user_wallets(user_id, currency, balance_cents, balance_milli) VALUES (?, "USD", 0, 0)')->execute([$uid]);
                $wid = (int)$pdo->lastInsertId();
                $w = ['id'=>$wid, 'currency'=>'USD', 'balance_cents'=>0, 'balance_milli'=>0];
            } else {
                // heal legacy NULL milli
                if (!isset($w['balance_milli']) || $w['balance_milli'] === null) {
                    $wid = (int)$w['id'];
                    $pdo->prepare('UPDATE perksin_user_wallets SET balance_milli = 0 WHERE id = ? AND balance_milli IS NULL')->execute([$wid]);
                    $w['balance_milli'] = 0;
                }
            }
            $pdo->commit();
            echo json_encode(['wallet'=>$w]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500); echo json_encode(['error'=>'wallet_error']);
        }
    }

    private function walletTransactions(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
    $stmt = $this->db->pdo()->prepare('SELECT id, amount_cents, amount_milli, currency, type, reference_type, reference_id, description, created_at FROM perksin_wallet_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 200');
        $stmt->execute([$uid]);
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }

    private function gemTransactions(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $stmt = $this->db->pdo()->prepare('SELECT id, amount, type, description, created_at FROM perksin_gem_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 200');
        $stmt->execute([$uid]);
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }

    // Simple adjust (topup/withdraw) for demo; prod would use PSP webhook
    private function walletAdjust(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $data = $this->parseJson();
    $amountUsd = (float)($data['amountUSD'] ?? 0);
        $type = (string)($data['type'] ?? 'deposit'); // 'deposit'|'withdraw'
        if (!in_array($type, ['deposit','withdraw'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_type']); return; }
    $cents = (int)round($amountUsd * 100);
    $milli = (int)round($amountUsd * 1000);
    if ($milli === 0) { http_response_code(400); echo json_encode(['error'=>'bad_amount']); return; }
    if ($type === 'withdraw') { $cents = -$cents; $milli = -$milli; }
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            // lock wallet
            $st = $pdo->prepare('SELECT id, balance_cents, balance_milli FROM perksin_user_wallets WHERE user_id=? AND currency="USD"');
            $st->execute([$uid]);
            $w = $st->fetch();
            if (!$w) {
                $pdo->prepare('INSERT INTO perksin_user_wallets(user_id,currency,balance_cents,balance_milli) VALUES (?,?,0,0)')->execute([$uid, 'USD']);
                $wid = (int)$pdo->lastInsertId();
                $w = ['id'=>$wid, 'balance_cents'=>0, 'balance_milli'=>0];
            }
            $wid = (int)$w['id'];
            // write tx
            $pdo->prepare('INSERT INTO perksin_wallet_transactions(wallet_id,user_id,amount_cents,amount_milli,currency,type,description) VALUES (?,?,?,?,?, ?,?)')
                ->execute([$wid, $uid, $cents, $milli, 'USD', $type, 'Manual adjust']);
            // apply balance
            $pdo->prepare('UPDATE perksin_user_wallets SET balance_cents = balance_cents + :a, balance_milli = balance_milli + :m WHERE id=:w')
                ->execute([':a'=>$cents, ':m'=>$milli, ':w'=>$wid]);
            $b = (int)$pdo->query('SELECT balance_cents FROM perksin_user_wallets WHERE id='.$wid)->fetchColumn();
            $pdo->commit();
            // also return milli for clients that support micro display
            $bm = (int)$this->db->pdo()->query('SELECT balance_milli FROM perksin_user_wallets WHERE id='.$wid)->fetchColumn();
            echo json_encode(['ok'=>true, 'balance_cents'=>$b, 'balance_milli'=>$bm]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500); echo json_encode(['error'=>'wallet_adjust_failed']);
        }
    }

    private function recordCaseHistory(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
    $caseTitle = trim((string)($d['caseTitle'] ?? 'Case'));
        $wonName = trim((string)($d['name'] ?? $d['won_item_title'] ?? 'Item'));
        $wonValueUSD = (float)($d['value'] ?? $d['won_value_usd'] ?? 0);
        $status = ($d['status'] ?? 'claimed') === 'sold' ? 'sold' : 'claimed';
        $pricePaidUSD = (float)($d['priceUSD'] ?? 0);
        $pricePaidGems = (int)($d['priceGems'] ?? 0);
        $soldAmountUSD = (float)($d['soldAmountUSD'] ?? 0);
        $coupon = (string)($d['coupon'] ?? null);
    $caseItemId = (int)($d['caseItemId'] ?? 0);
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            // Ensure wallet
            $st = $pdo->prepare('SELECT id, balance_milli FROM perksin_user_wallets WHERE user_id=? AND currency="USD"');
            $st->execute([$uid]);
            $rowW = $st->fetch();
            $wid = (int)($rowW['id'] ?? 0);
            if (!$wid) {
                $pdo->prepare('INSERT INTO perksin_user_wallets(user_id,currency,balance_cents,balance_milli) VALUES (?,?,0,0)')->execute([$uid,'USD']);
                $wid = (int)$pdo->lastInsertId();
            } else if (!isset($rowW['balance_milli']) || $rowW['balance_milli'] === null) {
                // heal legacy NULL milli before arithmetic
                $pdo->prepare('UPDATE perksin_user_wallets SET balance_milli = 0 WHERE id = ? AND balance_milli IS NULL')->execute([$wid]);
            }
            $openTxId = null; $soldTxId = null;
            // Deduct case price if provided
            if ($pricePaidUSD > 0) {
                $c = (int)round($pricePaidUSD * 100);
                $m = (int)round($pricePaidUSD * 1000);
                $pdo->prepare('INSERT INTO perksin_wallet_transactions(wallet_id,user_id,amount_cents,amount_milli,currency,type,reference_type,description) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$wid, $uid, -$c, -$m, 'USD', 'purchase', 'case_open', $caseTitle]);
                $openTxId = (int)$pdo->lastInsertId();
                $pdo->prepare('UPDATE perksin_user_wallets SET balance_cents = balance_cents - :a, balance_milli = balance_milli - :m WHERE id=:w')
                    ->execute([':a'=>$c, ':m'=>$m, ':w'=>$wid]);
            }
            // Deduct gems if used for payment
            if ($pricePaidGems > 0) {
                // lock gems row
                $stG = $pdo->prepare('SELECT balance FROM perksin_user_gems WHERE user_id=?');
                $stG->execute([$uid]);
                $rowG = $stG->fetch();
                if (!$rowG) {
                    $pdo->prepare('INSERT INTO perksin_user_gems(user_id, balance) VALUES (?, 0)')->execute([$uid]);
                    $curBal = 0;
                } else {
                    $curBal = (int)$rowG['balance'];
                }
                if ($curBal < $pricePaidGems) { throw new \RuntimeException('insufficient_gems'); }
                $pdo->prepare('INSERT INTO perksin_gem_transactions(user_id, amount, type, description) VALUES (?,?,?,?)')
                    ->execute([$uid, -$pricePaidGems, 'spend', 'Case open: '.$caseTitle]);
                $pdo->prepare('UPDATE perksin_user_gems SET balance = balance - :a WHERE user_id=:u')->execute([':a'=>$pricePaidGems, ':u'=>$uid]);
            }
            if ($status === 'sold' && $soldAmountUSD > 0) {
                $c = (int)round($soldAmountUSD * 100);
                $m = (int)round($soldAmountUSD * 1000);
                $pdo->prepare('INSERT INTO perksin_wallet_transactions(wallet_id,user_id,amount_cents,amount_milli,currency,type,reference_type,description) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$wid, $uid, +$c, +$m, 'USD', 'sale', 'case_sale', $wonName]);
                $soldTxId = (int)$pdo->lastInsertId();
                $pdo->prepare('UPDATE perksin_user_wallets SET balance_cents = balance_cents + :a, balance_milli = balance_milli + :m WHERE id=:w')
                    ->execute([':a'=>$c, ':m'=>$m, ':w'=>$wid]);
            }
            // Optional stock/codes handling if caseItemId provided
            $codeId = null;
            if ($caseItemId > 0 && $status === 'claimed') {
                // Load item
                $stI = $pdo->prepare('SELECT id, stock_type FROM perksin_case_items WHERE id=?');
                $stI->execute([$caseItemId]);
                $rowI = $stI->fetch();
                if ($rowI) {
                    $stype = $rowI['stock_type'] ?? 'infinite';
                    if ($stype === 'finite') {
                        // Consume one unit if available
                        $upd = $pdo->prepare('UPDATE perksin_case_items SET stock_consumed = stock_consumed + 1 WHERE id=? AND (stock_qty IS NULL OR stock_qty - stock_consumed > 0)');
                        $upd->execute([$caseItemId]);
                    } elseif ($stype === 'codes') {
                        // Claim a fresh code
                        $stC = $pdo->prepare('SELECT id, code FROM perksin_item_codes WHERE case_item_id=? AND status="new" ORDER BY id ASC LIMIT 1');
                        $stC->execute([$caseItemId]);
                        $code = $stC->fetch();
                        if ($code) {
                            $codeId = (int)$code['id'];
                            $coupon = $coupon ?: (string)$code['code'];
                            $pdo->prepare('UPDATE perksin_item_codes SET status="claimed", claimed_at=NOW(), claimed_by=? WHERE id=?')->execute([$uid, $codeId]);
                        }
                    }
                }
            }
            // History row
            $pdo->prepare('INSERT INTO perksin_case_open_history (user_id, case_title, won_item_title, won_value_cents, coupon_code, status, currency, price_paid_cents, open_tx_id, sold_tx_id, sold_amount_cents, sold_amount_milli) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$uid, $caseTitle, $wonName, (int)round($wonValueUSD*100), $coupon ?: null, $status, 'USD', (int)round($pricePaidUSD*100), $openTxId, $soldTxId, (int)round($soldAmountUSD*100), (int)round($soldAmountUSD*1000)]);
            // Inventory on claimed
            if ($status === 'claimed') {
                $pdo->prepare('INSERT INTO perksin_user_inventory (user_id, item_title, item_value_cents, code_id, status) VALUES (?,?,?,?,"active")')
                    ->execute([$uid, $wonName, (int)round($wonValueUSD*100), $codeId]);
            }
            $pdo->commit();
            echo json_encode(['ok'=>true]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($e instanceof \RuntimeException && $e->getMessage()==='insufficient_gems') { http_response_code(400); echo json_encode(['error'=>'insufficient_gems']); return; }
            http_response_code(500); echo json_encode(['error'=>'record_failed']);
        }
    }

    private function gemsBalance(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $pdo = $this->db->pdo();
        $st = $pdo->prepare('SELECT balance FROM perksin_user_gems WHERE user_id=?');
        $st->execute([$uid]);
        $bal = $st->fetchColumn();
        if ($bal === false) $bal = 0;
        echo json_encode(['balance'=>(int)$bal]);
    }

    private function gemsAdjust(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $amount = (int)($d['amount'] ?? 0);
        $type = (string)($d['type'] ?? 'award'); // award|spend|adjust
        $refType = isset($d['reference_type']) ? (string)$d['reference_type'] : null;
        $refId = isset($d['reference_id']) ? (int)$d['reference_id'] : null;
        $desc = isset($d['description']) ? (string)$d['description'] : null;
        if (!in_array($type, ['award','spend','adjust'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_type']); return; }
        if ($amount === 0) { http_response_code(400); echo json_encode(['error'=>'bad_amount']); return; }
        if ($type === 'spend') $amount = -abs($amount);
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            // ensure row
            $st = $pdo->prepare('SELECT balance FROM perksin_user_gems WHERE user_id=?');
            $st->execute([$uid]);
            $row = $st->fetch();
            if (!$row) {
                $pdo->prepare('INSERT INTO perksin_user_gems(user_id, balance) VALUES (?, 0)')->execute([$uid]);
            }
            $pdo->prepare('INSERT INTO perksin_gem_transactions(user_id, amount, type, reference_type, reference_id, description) VALUES (?,?,?,?,?,?)')
                ->execute([$uid, $amount, $type, $refType, $refId, ($desc ?: 'API adjust')]);
            $pdo->prepare('UPDATE perksin_user_gems SET balance = balance + :a WHERE user_id=:u')->execute([':a'=>$amount, ':u'=>$uid]);
            $bal = (int)$pdo->prepare('SELECT balance FROM perksin_user_gems WHERE user_id=?')->execute([$uid]) ? (int)$pdo->query('SELECT balance FROM perksin_user_gems WHERE user_id='.$uid)->fetchColumn() : 0;
            $pdo->commit();
            echo json_encode(['ok'=>true,'balance'=>$bal]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500); echo json_encode(['error'=>'gems_adjust_failed']);
        }
    }

    private function listContentSections(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $pdo = $this->db->pdo();
        $page = trim((string)($_GET['pageKey'] ?? 'global'));
        $stmt = $pdo->prepare('SELECT id,page,section,locale,title,body,modified_at FROM perksin_content_sections WHERE page=? ORDER BY section, locale');
        $stmt->execute([$page]);
        echo json_encode(['items'=>$stmt->fetchAll()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    private function saveContentSection(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $page = trim((string)($d['page'] ?? 'global'));
        $section = trim((string)($d['section'] ?? 'header'));
        $locale = strtolower(trim((string)($d['locale'] ?? 'en')));
        $title = (string)($d['title'] ?? null);
        $body = (string)($d['body'] ?? null);
        if ($page === '' || $section === '' || !in_array($locale, ['en','hu'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('INSERT INTO perksin_content_sections(page,section,locale,title,body) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), body=VALUES(body)');
        $stmt->execute([$page, $section, $locale, $title, $body]);
        echo json_encode(['ok'=>true]);
    }

    private function listBuilderPages(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        echo json_encode(['items' => $this->pageBuilder->listPageConfigs()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function listBuilderModules(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        echo json_encode(['items' => array_values($this->pageBuilder->getModuleRegistry())], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function saveBuilderLayout(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        $data = $this->parseJson();
        $pageKey = trim((string)($data['page_key'] ?? ''));
        $layout = is_array($data['layout'] ?? null) ? $data['layout'] : [];
        if ($pageKey === '') { http_response_code(400); echo json_encode(['error' => 'bad_input']); return; }
        $this->pageBuilder->saveLayout($pageKey, $layout);
        echo json_encode(['ok' => true]);
    }

    private function saveBuilderAccess(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error' => 'forbidden']); return; }
        $data = $this->parseJson();
        $pageKey = trim((string)($data['page_key'] ?? ''));
        $access = is_array($data['access'] ?? null) ? $data['access'] : [];
        if ($pageKey === '') { http_response_code(400); echo json_encode(['error' => 'bad_input']); return; }
        $this->pageBuilder->savePageAccess($pageKey, $access);
        echo json_encode(['ok' => true]);
    }

    private function listInventory(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('
            SELECT ui.id, ui.item_title, ui.item_value_cents, ui.code_id, ui.status, ui.created_at, ic.code AS coupon_code
            FROM perksin_user_inventory ui
            LEFT JOIN perksin_item_codes ic ON ic.id = ui.code_id
            WHERE ui.user_id=? AND ui.status="active"
            ORDER BY ui.id DESC
        ');
        $stmt->execute([$uid]);
        $rows = $stmt->fetchAll();
        // Normalize values for client (value in USD)
        $items = array_map(function($r){
            return [
                'id' => (int)$r['id'],
                'item_title' => $r['item_title'],
                'item_value_cents' => (int)($r['item_value_cents'] ?? 0),
                'code_id' => isset($r['code_id']) ? (int)$r['code_id'] : null,
                'coupon_code' => (string)($r['coupon_code'] ?? ''),
                'status' => $r['status'] ?? 'active',
                'created_at' => $r['created_at'] ?? null,
            ];
        }, $rows ?: []);
        echo json_encode(['items'=>$items], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    // Get current daily spinner state (server-authoritative). If not present, create defaults.
    private function spinState(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('SELECT user_id, used_today, last_reset_date, lock_until, collectibles, bonus FROM perksin_user_spin_state WHERE user_id=?');
            $st->execute([$uid]);
            $row = $st->fetch();
            $today = (new \DateTime('today'))->format('Y-m-d');
            if (!$row) {
                $pdo->prepare('INSERT INTO perksin_user_spin_state (user_id, used_today, last_reset_date, lock_until, collectibles, bonus) VALUES (?,?,?,?,?,?)')
                    ->execute([$uid, 0, $today, null, 0, 0]);
                $row = ['user_id'=>$uid, 'used_today'=>0, 'last_reset_date'=>$today, 'lock_until'=>null, 'collectibles'=>0, 'bonus'=>0];
            }
            // Reset if date changed
            if (($row['last_reset_date'] ?? '') !== $today) {
                $pdo->prepare('UPDATE perksin_user_spin_state SET used_today=0, last_reset_date=?, lock_until=NULL WHERE user_id=?')
                    ->execute([$today, $uid]);
                $row['used_today'] = 0; $row['last_reset_date'] = $today; $row['lock_until'] = null;
            }
            $pdo->commit();
            echo json_encode(['state'=>[
                'used_today'=>(int)$row['used_today'],
                'daily_limit'=>10,
                'lock_until'=>$row['lock_until'],
                'collectibles'=>(int)$row['collectibles'],
                'bonus'=>(int)$row['bonus'],
            ]]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500); echo json_encode(['error'=>'spin_state_error']);
        }
    }

    // Adjust spinner counters atomically on the server.
    // Body: { action: 'consume'|'award_collectible'|'award_bonus'|'lock', amount?:int, lockHours?:int }
    private function spinAdjust(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $action = (string)($d['action'] ?? '');
        $amount = (int)($d['amount'] ?? 1);
        $lockHours = (int)($d['lockHours'] ?? 24);
        if (!in_array($action, ['consume','award_collectible','award_bonus','lock'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_action']); return; }
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('SELECT user_id, used_today, last_reset_date, lock_until, collectibles, bonus FROM perksin_user_spin_state WHERE user_id=?');
            $st->execute([$uid]);
            $row = $st->fetch();
            $today = (new \DateTime('today'))->format('Y-m-d');
            if (!$row) {
                $pdo->prepare('INSERT INTO perksin_user_spin_state (user_id, used_today, last_reset_date, lock_until, collectibles, bonus) VALUES (?,?,?,?,?,?)')
                    ->execute([$uid, 0, $today, null, 0, 0]);
                $row = ['user_id'=>$uid, 'used_today'=>0, 'last_reset_date'=>$today, 'lock_until'=>null, 'collectibles'=>0, 'bonus'=>0];
            }
            if (($row['last_reset_date'] ?? '') !== $today) {
                $row['used_today'] = 0; $row['last_reset_date'] = $today; $row['lock_until'] = null;
                $pdo->prepare('UPDATE perksin_user_spin_state SET used_today=0, last_reset_date=?, lock_until=NULL WHERE user_id=?')->execute([$today, $uid]);
            }
            $limit = 10;
            if ($action === 'consume') {
                // Prefer consuming bonus, otherwise normal quota and set lock if hit limit
                if ((int)$row['bonus'] > 0) {
                    $pdo->prepare('UPDATE perksin_user_spin_state SET bonus = bonus - 1 WHERE user_id=?')->execute([$uid]);
                    $row['bonus'] = (int)$row['bonus'] - 1;
                } else {
                    $newUsed = (int)$row['used_today'] + 1;
                    $pdo->prepare('UPDATE perksin_user_spin_state SET used_today = used_today + 1 WHERE user_id=?')->execute([$uid]);
                    $row['used_today'] = $newUsed;
                    if ($newUsed >= $limit) {
                        $until = (new \DateTime('+'.$lockHours.' hours'))->format('Y-m-d H:i:s');
                        $pdo->prepare('UPDATE perksin_user_spin_state SET lock_until = ? WHERE user_id=?')->execute([$until, $uid]);
                        $row['lock_until'] = $until;
                    }
                }
            } elseif ($action === 'award_collectible') {
                $newC = max(0, (int)$row['collectibles'] + max(1, $amount));
                $bonusInc = 0;
                while ($newC >= 10) { $newC -= 10; $bonusInc++; }
                $pdo->prepare('UPDATE perksin_user_spin_state SET collectibles=?, bonus=bonus+? WHERE user_id=?')->execute([$newC, $bonusInc, $uid]);
                $row['collectibles'] = $newC; $row['bonus'] = (int)$row['bonus'] + $bonusInc;
            } elseif ($action === 'award_bonus') {
                $inc = max(1, $amount);
                $pdo->prepare('UPDATE perksin_user_spin_state SET bonus = bonus + ? WHERE user_id=?')->execute([$inc, $uid]);
                $row['bonus'] = (int)$row['bonus'] + $inc;
            } elseif ($action === 'lock') {
                $until = (new \DateTime('+'.$lockHours.' hours'))->format('Y-m-d H:i:s');
                $pdo->prepare('UPDATE perksin_user_spin_state SET lock_until=? WHERE user_id=?')->execute([$until, $uid]);
                $row['lock_until'] = $until;
            }
            $pdo->commit();
            echo json_encode(['ok'=>true,'state'=>[
                'used_today'=>(int)$row['used_today'],
                'daily_limit'=>$limit,
                'lock_until'=>$row['lock_until'],
                'collectibles'=>(int)$row['collectibles'],
                'bonus'=>(int)$row['bonus'],
            ]]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500); echo json_encode(['error'=>'spin_adjust_failed']);
        }
    }

    private function listChatMessages(): void
    {
        [$ownerType, $ownerKey] = $this->currentClientOwner();
        $moderation = $this->getChatModerationState($ownerType, $ownerKey);
        $stmt = $this->db->pdo()->query('SELECT id, owner_type, owner_key, display_name, message, profanity_hits, created_at FROM perksin_chat_messages ORDER BY id DESC LIMIT 80');
        $items = array_reverse($stmt->fetchAll() ?: []);
        echo json_encode([
            'items' => array_map(function (array $row) use ($ownerType, $ownerKey): array {
                return [
                    'id' => (int)($row['id'] ?? 0),
                    'display_name' => (string)($row['display_name'] ?? 'Guest'),
                    'message' => (string)($row['message'] ?? ''),
                    'profanity_hits' => (int)($row['profanity_hits'] ?? 0),
                    'created_at' => $row['created_at'] ?? null,
                    'is_own' => (string)($row['owner_type'] ?? '') === $ownerType && (string)($row['owner_key'] ?? '') === $ownerKey,
                ];
            }, $items),
            'can_chat' => !$moderation['is_banned'],
            'banned_until' => $moderation['banned_until'],
            'daily_hits' => $moderation['hit_count'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function postChatMessage(): void
    {
        $data = $this->parseJson();
        $message = trim((string)($data['message'] ?? ''));
        if ($message === '') {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        [$ownerType, $ownerKey] = $this->currentClientOwner();
        $moderation = $this->getChatModerationState($ownerType, $ownerKey);
        if ($moderation['is_banned']) {
            http_response_code(403);
            echo json_encode(['error' => 'chat_banned', 'banned_until' => $moderation['banned_until']]);
            return;
        }

        $moderated = $this->moderateChatMessage($message);
        $masked = trim((string)($moderated['masked'] ?? ''));
        if ($masked === '') {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        $updatedModeration = $this->registerChatProfanity($ownerType, $ownerKey, (int)($moderated['profanity_hits'] ?? 0));
        if ($updatedModeration['is_banned']) {
            http_response_code(403);
            echo json_encode(['error' => 'chat_banned', 'banned_until' => $updatedModeration['banned_until']]);
            return;
        }

        $stmt = $this->db->pdo()->prepare('
            INSERT INTO perksin_chat_messages (owner_type, owner_key, display_name, message, original_message, profanity_hits)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $ownerType,
            $ownerKey,
            $this->currentDisplayName(),
            $masked,
            (string)($moderated['original'] ?? ''),
            (int)($moderated['profanity_hits'] ?? 0),
        ]);

        echo json_encode([
            'ok' => true,
            'message' => [
                'id' => (int)$this->db->pdo()->lastInsertId(),
                'display_name' => $this->currentDisplayName(),
                'message' => $masked,
                'created_at' => date('Y-m-d H:i:s'),
                'is_own' => true,
            ],
            'daily_hits' => $updatedModeration['hit_count'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function uploadCaseImage(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        $file = $_FILES['image'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'upload_failed']);
            return;
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $info = @getimagesize($tmpName);
        if (!$info) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_image']);
            return;
        }

        $mime = strtolower((string)($info['mime'] ?? ''));
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        $ext = $extMap[$mime] ?? null;
        if ($ext === null) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_image']);
            return;
        }

        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cases';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            http_response_code(500);
            echo json_encode(['error' => 'upload_dir_failed']);
            return;
        }

        $baseName = 'case-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $uploadDir . DIRECTORY_SEPARATOR . $baseName;
        if (!move_uploaded_file($tmpName, $target)) {
            http_response_code(500);
            echo json_encode(['error' => 'upload_failed']);
            return;
        }

        echo json_encode([
            'ok' => true,
            'path' => '/uploads/cases/' . $baseName,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function listMarketOffers(): void
    {
        $uid = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $stmt = $this->db->pdo()->query('SELECT id, owner_type, owner_key, owner_user_id, inventory_item_id, item_title, item_value_cents, requested_value_cents, currency, status, accepted_bid_id, created_at FROM perksin_market_offers ORDER BY created_at DESC LIMIT 500');
        $rows = $stmt->fetchAll() ?: [];
        $offerIds = array_values(array_filter(array_map(static fn(array $row): int => (int)($row['id'] ?? 0), $rows)));
        $bidsByOffer = [];
        if ($offerIds) {
            $placeholders = implode(',', array_fill(0, count($offerIds), '?'));
            $bst = $this->db->pdo()->prepare("SELECT id, market_offer_id, bidder_user_id, bid_type, inventory_item_id, bid_title, bid_value_cents, gem_amount, status, created_at FROM perksin_market_bids WHERE market_offer_id IN ($placeholders) ORDER BY created_at DESC");
            $bst->execute($offerIds);
            foreach ($bst->fetchAll() ?: [] as $bid) {
                $offerId = (int)($bid['market_offer_id'] ?? 0);
                $bidsByOffer[$offerId] ??= [];
                $bidsByOffer[$offerId][] = [
                    'id' => (int)$bid['id'],
                    'bidder_user_id' => (int)($bid['bidder_user_id'] ?? 0),
                    'bid_type' => (string)($bid['bid_type'] ?? 'inventory'),
                    'inventory_item_id' => isset($bid['inventory_item_id']) ? (int)$bid['inventory_item_id'] : null,
                    'bid_title' => (string)($bid['bid_title'] ?? ''),
                    'bid_value_cents' => (int)($bid['bid_value_cents'] ?? 0),
                    'gem_amount' => (int)($bid['gem_amount'] ?? 0),
                    'status' => (string)($bid['status'] ?? 'pending'),
                    'created_at' => $bid['created_at'] ?? null,
                ];
            }
        }
        $items = array_map(function (array $row) use ($uid, $bidsByOffer): array {
            $offerId = (int)$row['id'];
            $offerBids = $bidsByOffer[$offerId] ?? [];
            $ownerUserId = (int)($row['owner_user_id'] ?? 0);
            $isOwner = $this->isCurrentOwner((string)($row['owner_type'] ?? ''), (string)($row['owner_key'] ?? ''));
            return [
                'id' => $offerId,
                'item_title' => (string)($row['item_title'] ?? ''),
                'item_value_cents' => (int)($row['item_value_cents'] ?? 0),
                'requested_value_cents' => (int)($row['requested_value_cents'] ?? 0),
                'currency' => strtoupper((string)($row['currency'] ?? 'USD')),
                'status' => (string)($row['status'] ?? 'open'),
                'owner_user_id' => $ownerUserId,
                'inventory_item_id' => isset($row['inventory_item_id']) ? (int)$row['inventory_item_id'] : null,
                'accepted_bid_id' => isset($row['accepted_bid_id']) ? (int)$row['accepted_bid_id'] : null,
                'created_at' => $row['created_at'] ?? null,
                'can_close' => $isOwner,
                'can_bid' => $uid > 0 && !$isOwner && ($ownerUserId <= 0 || $ownerUserId !== $uid) && (string)($row['status'] ?? 'open') === 'open',
                'can_review_bids' => $uid > 0 && ($ownerUserId === $uid || $isOwner),
                'bid_count' => count($offerBids),
                'has_pending_bids' => count(array_filter($offerBids, static fn(array $bid): bool => (string)($bid['status'] ?? '') === 'pending')),
                'my_bid_status' => $uid > 0 ? (array_values(array_map(static fn(array $bid): string => (string)$bid['status'], array_filter($offerBids, function (array $bid) use ($uid): bool {
                    return (int)($bid['bidder_user_id'] ?? 0) === $uid;
                })))[0] ?? null) : null,
                'bids' => $uid > 0 && ($ownerUserId === $uid || $isOwner) ? $offerBids : [],
            ];
        }, $rows);
        echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function saveMarketOffer(): void
    {
        $uid = $this->requireAuthenticatedUserId();
        $d = $this->parseJson();
        $inventoryItemId = (int)($d['inventory_item_id'] ?? 0);
        $currency = strtoupper(trim((string)($d['currency'] ?? 'USD')));
        if (!in_array($currency, ['USD', 'EUR', 'HUF'], true)) {
            $currency = 'USD';
        }
        $requestedValueCents = (int)round(((float)($d['requested_value_usd'] ?? 0)) * 100);
        if ($inventoryItemId <= 0 || $requestedValueCents < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        $pdo = $this->db->pdo();
        $inventoryItem = $this->getActiveInventoryItem($pdo, $inventoryItemId, $uid);
        if (!$inventoryItem) {
            http_response_code(404);
            echo json_encode(['error' => 'inventory_not_found']);
            return;
        }
        $expiryReason = $this->inventoryTradeExpiryReason($pdo, $inventoryItem);
        if ($expiryReason) {
            http_response_code(400);
            echo json_encode(['error' => $expiryReason]);
            return;
        }

        [$ownerType, $ownerKey] = $this->currentClientOwner();
        $stmt = $pdo->prepare('
            INSERT INTO perksin_market_offers (owner_type, owner_key, owner_user_id, inventory_item_id, item_title, item_value_cents, requested_value_cents, currency, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, "open")
        ');
        $stmt->execute([$ownerType, $ownerKey, $uid, $inventoryItemId, (string)$inventoryItem['item_title'], (int)($inventoryItem['item_value_cents'] ?? 0), $requestedValueCents, $currency]);
        echo json_encode(['ok' => true, 'id' => (int)$this->db->pdo()->lastInsertId()]);
    }

    private function saveMarketBid(): void
    {
        $uid = $this->requireAuthenticatedUserId();
        $d = $this->parseJson();
        $offerId = (int)($d['offer_id'] ?? 0);
        $bidType = trim((string)($d['bid_type'] ?? 'inventory'));
        $inventoryItemId = (int)($d['inventory_item_id'] ?? 0);
        $cashValueCents = (int)round(((float)($d['cash_value_usd'] ?? 0)) * 100);
        $gemAmount = (int)($d['gem_amount'] ?? 0);
        if ($offerId <= 0 || !in_array($bidType, ['inventory', 'cash', 'gems'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }
        $pdo = $this->db->pdo();
        $offerStmt = $pdo->prepare('SELECT id, owner_user_id, inventory_item_id, status FROM perksin_market_offers WHERE id = ? LIMIT 1');
        $offerStmt->execute([$offerId]);
        $offer = $offerStmt->fetch();
        if (!$offer) {
            http_response_code(404);
            echo json_encode(['error' => 'not_found']);
            return;
        }
        if ((string)($offer['status'] ?? 'open') !== 'open') {
            http_response_code(400);
            echo json_encode(['error' => 'offer_closed']);
            return;
        }
        if ((int)($offer['owner_user_id'] ?? 0) === $uid) {
            http_response_code(400);
            echo json_encode(['error' => 'cannot_bid_own_offer']);
            return;
        }

        $bidTitle = '';
        $bidValueCents = 0;
        if ($bidType === 'inventory') {
            $inventoryItem = $this->getActiveInventoryItem($pdo, $inventoryItemId, $uid);
            if (!$inventoryItem) {
                http_response_code(404);
                echo json_encode(['error' => 'inventory_not_found']);
                return;
            }
            $expiryReason = $this->inventoryTradeExpiryReason($pdo, $inventoryItem);
            if ($expiryReason) {
                http_response_code(400);
                echo json_encode(['error' => $expiryReason]);
                return;
            }
            $bidTitle = (string)$inventoryItem['item_title'];
            $bidValueCents = (int)($inventoryItem['item_value_cents'] ?? 0);
        } elseif ($bidType === 'cash') {
            if ($cashValueCents <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'bad_input']);
                return;
            }
            $bidTitle = 'Cash offer';
            $bidValueCents = $cashValueCents;
        } else {
            if ($gemAmount <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'bad_input']);
                return;
            }
            $bidTitle = 'Gem offer';
            $bidValueCents = 0;
        }

        $existing = $pdo->prepare('SELECT id FROM perksin_market_bids WHERE market_offer_id = ? AND bidder_user_id = ? AND status = "pending" LIMIT 1');
        $existing->execute([$offerId, $uid]);
        if ($existing->fetchColumn()) {
            http_response_code(400);
            echo json_encode(['error' => 'existing_pending_bid']);
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO perksin_market_bids (market_offer_id, bidder_user_id, bid_type, inventory_item_id, bid_title, bid_value_cents, gem_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, "pending")');
        $stmt->execute([$offerId, $uid, $bidType, $bidType === 'inventory' ? $inventoryItemId : null, $bidTitle, $bidValueCents, $bidType === 'gems' ? $gemAmount : 0]);
        echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    }

    private function updateMarketOffer(): void
    {
        $uid = $this->requireAuthenticatedUserId();
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        $action = trim((string)($d['action'] ?? ''));
        $bidId = (int)($d['bid_id'] ?? 0);
        if ($id <= 0 || !in_array($action, ['close', 'accept_bid', 'reject_bid'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }

        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('SELECT owner_type, owner_key, owner_user_id, inventory_item_id, status FROM perksin_market_offers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $ownerUserId = (int)($row['owner_user_id'] ?? 0);
        $isOwner = $this->isCurrentOwner((string)($row['owner_type'] ?? ''), (string)($row['owner_key'] ?? ''))
            || ($ownerUserId > 0 && $ownerUserId === $uid);
        if (!$isOwner) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        if ($ownerUserId <= 0 && $isOwner) {
            $ownerUserId = $uid;
        }
        if (($row['status'] ?? 'open') !== 'open') {
            echo json_encode(['ok' => true, 'unchanged' => true]);
            return;
        }
        if ($action === 'close') {
            $upd = $pdo->prepare('UPDATE perksin_market_offers SET status = "cancelled", modified_at = CURRENT_TIMESTAMP WHERE id = ?');
            $upd->execute([$id]);
            $pdo->prepare('UPDATE perksin_market_bids SET status = "cancelled", modified_at = CURRENT_TIMESTAMP WHERE market_offer_id = ? AND status = "pending"')->execute([$id]);
            echo json_encode(['ok' => true]);
            return;
        }
        if ($bidId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'bad_input']);
            return;
        }
        $bidStmt = $pdo->prepare('SELECT id, bidder_user_id, bid_type, inventory_item_id, bid_title, bid_value_cents, gem_amount, status FROM perksin_market_bids WHERE id = ? AND market_offer_id = ? LIMIT 1');
        $bidStmt->execute([$bidId, $id]);
        $bid = $bidStmt->fetch();
        if (!$bid) {
            http_response_code(404);
            echo json_encode(['error' => 'bid_not_found']);
            return;
        }
        if ($action === 'reject_bid') {
            if ((string)($bid['status'] ?? 'pending') !== 'pending') {
                echo json_encode(['ok' => true, 'unchanged' => true]);
                return;
            }
            $pdo->prepare('UPDATE perksin_market_bids SET status = "rejected", modified_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$bidId]);
            echo json_encode(['ok' => true]);
            return;
        }

        if ((string)($bid['status'] ?? 'pending') !== 'pending') {
            echo json_encode(['ok' => true, 'unchanged' => true]);
            return;
        }
        $offerInventoryId = (int)($row['inventory_item_id'] ?? 0);
        $bidderUserId = (int)($bid['bidder_user_id'] ?? 0);
        if ($ownerUserId <= 0 || $bidderUserId <= 0) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $pdo->beginTransaction();
        try {
            $offerInventory = $this->getActiveInventoryItem($pdo, $offerInventoryId, $ownerUserId);
            if (!$offerInventory) {
                throw new \RuntimeException('inventory_not_found');
            }
            $offerExpiry = $this->inventoryTradeExpiryReason($pdo, $offerInventory);
            if ($offerExpiry) {
                throw new \RuntimeException($offerExpiry);
            }

            $bidType = (string)($bid['bid_type'] ?? 'inventory');
            if ($bidType === 'inventory') {
                $bidInventory = $this->getActiveInventoryItem($pdo, (int)($bid['inventory_item_id'] ?? 0), $bidderUserId);
                if (!$bidInventory) {
                    throw new \RuntimeException('inventory_not_found');
                }
                $bidExpiry = $this->inventoryTradeExpiryReason($pdo, $bidInventory);
                if ($bidExpiry) {
                    throw new \RuntimeException($bidExpiry);
                }
                $pdo->prepare('UPDATE perksin_user_inventory SET user_id = ? WHERE id = ?')->execute([$bidderUserId, $offerInventoryId]);
                $pdo->prepare('UPDATE perksin_user_inventory SET user_id = ? WHERE id = ?')->execute([$ownerUserId, (int)$bid['inventory_item_id']]);
            } elseif ($bidType === 'cash') {
                $amountMilli = (int)($bid['bid_value_cents'] ?? 0) * 10;
                $this->adjustWalletMilli($pdo, $bidderUserId, -$amountMilli, 'exchange_trade', 'market_offer', $id, 'Market trade payment');
                $this->adjustWalletMilli($pdo, $ownerUserId, $amountMilli, 'exchange_trade', 'market_offer', $id, 'Market trade payment received');
                $pdo->prepare('UPDATE perksin_user_inventory SET user_id = ? WHERE id = ?')->execute([$bidderUserId, $offerInventoryId]);
            } elseif ($bidType === 'gems') {
                $amount = (int)($bid['gem_amount'] ?? 0);
                $this->adjustGems($pdo, $bidderUserId, -$amount, 'exchange_trade', 'market_offer', $id, 'Market trade gems payment');
                $this->adjustGems($pdo, $ownerUserId, $amount, 'exchange_trade', 'market_offer', $id, 'Market trade gems received');
                $pdo->prepare('UPDATE perksin_user_inventory SET user_id = ? WHERE id = ?')->execute([$bidderUserId, $offerInventoryId]);
            }

            $pdo->prepare('UPDATE perksin_market_offers SET status = "accepted", accepted_bid_id = ?, modified_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$bidId, $id]);
            $pdo->prepare('UPDATE perksin_market_bids SET status = "accepted", modified_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$bidId]);
            $pdo->prepare('UPDATE perksin_market_bids SET status = "rejected", modified_at = CURRENT_TIMESTAMP WHERE market_offer_id = ? AND id <> ? AND status = "pending"')->execute([$id, $bidId]);
            $pdo->commit();
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = $e instanceof \RuntimeException ? $e->getMessage() : 'market_update_failed';
            http_response_code(400);
            echo json_encode(['error' => $message]);
        }
    }

    // Admin: list products (offers)
    private function listProducts(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        if ($isAdmin) {
            $stmt = $this->db->pdo()->query('SELECT o.id, o.title AS name, o.price_cents, o.category_id, o.product_type, o.use_home_spinner, o.description, o.valid_from, o.valid_until, c.name AS category_name, o.created_by FROM perksin_offers o LEFT JOIN perksin_categories c ON c.id = o.category_id WHERE o.isDeleted=0 ORDER BY o.id DESC LIMIT 500');
        } else {
            $st = $this->db->pdo()->prepare('SELECT o.id, o.title AS name, o.price_cents, o.category_id, o.product_type, o.use_home_spinner, o.description, o.valid_from, o.valid_until, c.name AS category_name, o.created_by FROM perksin_offers o LEFT JOIN perksin_categories c ON c.id = o.category_id WHERE o.isDeleted=0 AND (o.created_by = :u OR o.created_by IS NULL) ORDER BY o.id DESC LIMIT 500');
            $st->execute([':u'=>$uid]);
            $stmt = $st; // reuse variable for echo
        }
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }
    private function saveProduct(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $name = trim((string)($d['name'] ?? ''));
        $priceUSD = (float)($d['priceUSD'] ?? 0);
        $categoryId = isset($d['category_id']) && $d['category_id'] !== '' ? (int)$d['category_id'] : null;
        $productType = trim((string)($d['product_type'] ?? 'product'));
        $useHomeSpinner = !empty($d['use_home_spinner']) ? 1 : 0;
        $description = trim((string)($d['description'] ?? ''));
        $validFrom = trim((string)($d['valid_from'] ?? ''));
        $validUntil = trim((string)($d['valid_until'] ?? ''));
        $cents = (int)round($priceUSD*100);
        if ($name==='') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if (!in_array($productType, ['product','badge','bundle'], true)) $productType = 'product';
        if ($validFrom !== '' && strtotime($validFrom) === false) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if ($validUntil !== '' && strtotime($validUntil) === false) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if ($validFrom !== '' && $validUntil !== '' && strtotime($validFrom) > strtotime($validUntil)) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if ($id) {
            // Only owner or admin can update
            if (!$isAdmin) {
                $own = $pdo->prepare('SELECT created_by FROM perksin_offers WHERE id=?'); $own->execute([$id]); $cb = (int)$own->fetchColumn();
                if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
            }
            $st = $pdo->prepare('UPDATE perksin_offers SET title=?, price_cents=?, category_id=?, product_type=?, use_home_spinner=?, description=?, valid_from=?, valid_until=?, modified_by=? WHERE id=?');
            $st->execute([$name, $cents, $categoryId, $productType, $useHomeSpinner, ($description !== '' ? $description : null), ($validFrom !== '' ? $validFrom : null), ($validUntil !== '' ? $validUntil : null), $uid, $id]);
        } else {
            // minimal new product with a default partner 1 (or null if not exists)
            $partnerId = (int)($pdo->query('SELECT id FROM perksin_partners ORDER BY id ASC LIMIT 1')->fetchColumn() ?: 0);
            if ($partnerId===0) { $pdo->exec("INSERT INTO perksin_partners(name,category) VALUES ('Default','misc')"); $partnerId = (int)$pdo->lastInsertId(); }
            $st = $pdo->prepare('INSERT INTO perksin_offers(partner_id,title,price_cents,category_id,product_type,use_home_spinner,description,valid_from,valid_until,is_active,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$partnerId, $name, $cents, $categoryId, $productType, $useHomeSpinner, ($description !== '' ? $description : null), ($validFrom !== '' ? $validFrom : null), ($validUntil !== '' ? $validUntil : null), 1, $uid]);
        }
        echo json_encode(['ok'=>true]);
    }

    private function listEvents(): void
    {
        $stmt = $this->db->pdo()->query('SELECT id, date, title, description, href, start_at, end_at, color FROM perksin_events ORDER BY COALESCE(start_at, date) ASC, id ASC');
        echo json_encode(['items' => $stmt->fetchAll()]);
    }

    private function saveEvent(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $date = trim((string)($d['date'] ?? ''));
        $startAt = trim((string)($d['start_at'] ?? ''));
        $endAt = trim((string)($d['end_at'] ?? ''));
        $title = trim((string)($d['title'] ?? ''));
        $description = trim((string)($d['description'] ?? ''));
        $href = trim((string)($d['href'] ?? ''));
        $color = trim((string)($d['color'] ?? ''));
        if ($startAt === '' && $date !== '') $startAt = $date . ' 00:00:00';
        if ($endAt === '' && $date !== '') $endAt = $date . ' 23:59:59';
        if ($startAt === '' || $endAt === '' || $title === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if (strtotime($startAt) === false || strtotime($endAt) === false || strtotime($startAt) > strtotime($endAt)) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $date = substr($startAt, 0, 10);
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_events SET date=?, start_at=?, end_at=?, title=?, description=?, href=?, color=? WHERE id=?');
            $st->execute([$date, $startAt, $endAt, $title, ($description !== '' ? $description : null), ($href !== '' ? $href : null), ($color !== '' ? $color : null), $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_events(date, start_at, end_at, title, description, href, color) VALUES (?,?,?,?,?,?,?)');
            $st->execute([$date, $startAt, $endAt, $title, ($description !== '' ? $description : null), ($href !== '' ? $href : null), ($color !== '' ? $color : null)]);
        }
        echo json_encode(['ok' => true]);
    }

    private function deleteEvent(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('DELETE FROM perksin_events WHERE id=?');
        $st->execute([$id]);
        echo json_encode(['ok' => true]);
    }
    private function deleteProduct(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if (!$isAdmin) {
            $own = $pdo->prepare('SELECT created_by FROM perksin_offers WHERE id=?');
            $own->execute([$id]);
            $cb = (int)$own->fetchColumn();
            if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        }
        $st = $pdo->prepare('UPDATE perksin_offers SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }
    private function listProductCategories(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $stmt = $this->db->pdo()->query('SELECT id, slug, name, created_by FROM perksin_categories WHERE isDeleted=0 ORDER BY name ASC, id ASC');
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }
    private function saveProductCategory(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $name = trim((string)($d['name'] ?? ''));
        $slug = trim((string)($d['slug'] ?? ''));
        if ($name === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if ($slug === '') $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_categories SET name=?, slug=?, modified_by=?, isDeleted=0 WHERE id=?');
            $st->execute([$name, $slug, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_categories(slug, name, created_by, isDeleted) VALUES (?,?,?,0)');
            $st->execute([$slug, $name, $uid]);
            $id = (int)$pdo->lastInsertId();
        }
        echo json_encode(['ok'=>true, 'id'=>$id]);
    }
    private function deleteProductCategory(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('UPDATE perksin_categories SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }
    private function listProductBundles(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $stmt = $this->db->pdo()->query('SELECT id, name, created_by FROM perksin_product_bundles WHERE isDeleted=0 ORDER BY id DESC LIMIT 500');
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }
    private function saveProductBundle(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $name = trim((string)($d['name'] ?? ''));
        if ($name === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_product_bundles SET name=?, modified_by=?, isDeleted=0 WHERE id=?');
            $st->execute([$name, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_product_bundles(name, created_by, isDeleted) VALUES (?,?,0)');
            $st->execute([$name, $uid]);
            $id = (int)$pdo->lastInsertId();
        }
        echo json_encode(['ok'=>true, 'id'=>$id]);
    }
    private function deleteProductBundle(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('UPDATE perksin_product_bundles SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }

    private function listOffersForCategory(int $categoryId): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT id, title, price_cents FROM perksin_offers WHERE category_id = ? AND isDeleted = 0 ORDER BY id ASC');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll() ?: [];
    }

    private function listBundleResolvedItems(int $bundleId, array &$visited = []): array
    {
        if ($bundleId <= 0 || isset($visited[$bundleId])) return [];
        $visited[$bundleId] = true;
        $stmt = $this->db->pdo()->prepare('
            SELECT id, quantity, source_type, offer_id, source_category_id, source_case_id, source_bundle_id
            FROM perksin_product_bundle_items
            WHERE bundle_id = ? AND isDeleted = 0
            ORDER BY id ASC
        ');
        $stmt->execute([$bundleId]);
        $resolved = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $quantity = max(1, (int)($row['quantity'] ?? 1));
            $sourceType = (string)($row['source_type'] ?? 'offer');
            if ($sourceType === 'category' && !empty($row['source_category_id'])) {
                foreach ($this->listOffersForCategory((int)$row['source_category_id']) as $offer) {
                    $resolved[] = [
                        'type' => 'product',
                        'name' => (string)($offer['title'] ?? ''),
                        'quantity' => $quantity,
                        'value_cents' => ((int)($offer['price_cents'] ?? 0)) * $quantity,
                    ];
                }
                continue;
            }
            if ($sourceType === 'case' && !empty($row['source_case_id'])) {
                $stCase = $this->db->pdo()->prepare('SELECT title, base_price_cents FROM perksin_cases WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $stCase->execute([(int)$row['source_case_id']]);
                $case = $stCase->fetch();
                if ($case) {
                    $resolved[] = [
                        'type' => 'case',
                        'name' => (string)($case['title'] ?? 'Case'),
                        'quantity' => $quantity,
                        'value_cents' => ((int)($case['base_price_cents'] ?? 0)) * $quantity,
                    ];
                }
                continue;
            }
            if ($sourceType === 'bundle' && !empty($row['source_bundle_id'])) {
                $stBundle = $this->db->pdo()->prepare('SELECT name FROM perksin_product_bundles WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $stBundle->execute([(int)$row['source_bundle_id']]);
                $bundle = $stBundle->fetch();
                $bundleValue = $this->calculateBundleValueCents((int)$row['source_bundle_id'], $visited);
                $resolved[] = [
                    'type' => 'bundle',
                    'name' => (string)($bundle['name'] ?? 'Bundle'),
                    'quantity' => $quantity,
                    'value_cents' => $bundleValue * $quantity,
                ];
                continue;
            }
            if (!empty($row['offer_id'])) {
                $stOffer = $this->db->pdo()->prepare('SELECT title, price_cents FROM perksin_offers WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $stOffer->execute([(int)$row['offer_id']]);
                $offer = $stOffer->fetch();
                if ($offer) {
                    $resolved[] = [
                        'type' => 'product',
                        'name' => (string)($offer['title'] ?? ''),
                        'quantity' => $quantity,
                        'value_cents' => ((int)($offer['price_cents'] ?? 0)) * $quantity,
                    ];
                }
            }
        }
        unset($visited[$bundleId]);
        return $resolved;
    }

    private function calculateBundleValueCents(int $bundleId, array &$visited = []): int
    {
        $items = $this->listBundleResolvedItems($bundleId, $visited);
        $sum = 0;
        foreach ($items as $item) $sum += (int)($item['value_cents'] ?? 0);
        return $sum;
    }

    private function listProductBundleItems(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $bundleId = (int)($_GET['bundle_id'] ?? 0);
        if ($bundleId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $stmt = $this->db->pdo()->prepare('
            SELECT bi.id, bi.bundle_id, bi.offer_id, bi.quantity, bi.source_type, bi.source_category_id, bi.source_case_id, bi.source_bundle_id,
                   o.title AS offer_title, o.price_cents,
                   c.name AS category_name,
                   cs.title AS case_title,
                   pb.name AS source_bundle_name
            FROM perksin_product_bundle_items bi
            LEFT JOIN perksin_offers o ON o.id = bi.offer_id AND o.isDeleted = 0
            LEFT JOIN perksin_categories c ON c.id = bi.source_category_id
            LEFT JOIN perksin_cases cs ON cs.id = bi.source_case_id AND cs.isDeleted = 0
            LEFT JOIN perksin_product_bundles pb ON pb.id = bi.source_bundle_id AND pb.isDeleted = 0
            WHERE bi.bundle_id = ? AND bi.isDeleted=0
            ORDER BY bi.id ASC
        ');
        $stmt->execute([$bundleId]);
        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $sourceType = (string)($row['source_type'] ?? 'offer');
            $title = (string)($row['offer_title'] ?? '');
            $valueCents = (int)($row['price_cents'] ?? 0);
            if ($sourceType === 'category') {
                $title = (string)($row['category_name'] ?? 'Category');
                $offers = $this->listOffersForCategory((int)($row['source_category_id'] ?? 0));
                $valueCents = 0;
                foreach ($offers as $offer) $valueCents += (int)($offer['price_cents'] ?? 0);
            } elseif ($sourceType === 'case') {
                $title = (string)($row['case_title'] ?? 'Case');
                $stCase = $this->db->pdo()->prepare('SELECT base_price_cents FROM perksin_cases WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $stCase->execute([(int)($row['source_case_id'] ?? 0)]);
                $valueCents = (int)($stCase->fetchColumn() ?: 0);
            } elseif ($sourceType === 'bundle') {
                $title = (string)($row['source_bundle_name'] ?? 'Bundle');
                $visited = [];
                $valueCents = $this->calculateBundleValueCents((int)($row['source_bundle_id'] ?? 0), $visited);
            }
            $row['title'] = $title;
            $row['value_cents'] = $valueCents;
            $items[] = $row;
        }
        echo json_encode(['items'=>$items]);
    }
    private function saveProductBundleItem(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $bundleId = (int)($d['bundle_id'] ?? 0);
        $sourceType = trim((string)($d['source_type'] ?? 'offer'));
        $offerId = isset($d['offer_id']) && $d['offer_id'] !== '' ? (int)$d['offer_id'] : null;
        $sourceCategoryId = isset($d['source_category_id']) && $d['source_category_id'] !== '' ? (int)$d['source_category_id'] : null;
        $sourceCaseId = isset($d['source_case_id']) && $d['source_case_id'] !== '' ? (int)$d['source_case_id'] : null;
        $sourceBundleId = isset($d['source_bundle_id']) && $d['source_bundle_id'] !== '' ? (int)$d['source_bundle_id'] : null;
        $quantity = max(1, (int)($d['quantity'] ?? 1));
        if ($bundleId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if (!in_array($sourceType, ['offer', 'category', 'case', 'bundle'], true)) $sourceType = 'offer';
        $offerId = $sourceType === 'offer' ? $offerId : null;
        $sourceCategoryId = $sourceType === 'category' ? $sourceCategoryId : null;
        $sourceCaseId = $sourceType === 'case' ? $sourceCaseId : null;
        $sourceBundleId = $sourceType === 'bundle' ? $sourceBundleId : null;
        $sourceId = $offerId ?? $sourceCategoryId ?? $sourceCaseId ?? $sourceBundleId ?? 0;
        if ($sourceId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if ($sourceType === 'bundle' && $sourceBundleId === $bundleId) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_product_bundle_items SET source_type=?, offer_id=?, source_category_id=?, source_case_id=?, source_bundle_id=?, quantity=?, modified_by=?, isDeleted=0 WHERE id=?');
            $st->execute([$sourceType, $offerId, $sourceCategoryId, $sourceCaseId, $sourceBundleId, $quantity, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_product_bundle_items(bundle_id, source_type, offer_id, source_category_id, source_case_id, source_bundle_id, quantity, created_by, isDeleted) VALUES (?,?,?,?,?,?,?,?,0)');
            $st->execute([$bundleId, $sourceType, $offerId, $sourceCategoryId, $sourceCaseId, $sourceBundleId, $quantity, $uid]);
        }
        echo json_encode(['ok'=>true]);
    }
    private function deleteProductBundleItem(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $uid = (int)$_SESSION['user_id'];
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('UPDATE perksin_product_bundle_items SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }

    // Admin: list cases
    private function listCases(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        if ($isAdmin) {
            $stmt = $this->db->pdo()->query('SELECT id, slug, title, base_price_cents, tag, img, risk, is_event, required_level, is_community, is_featured, created_by FROM perksin_cases WHERE isDeleted=0 ORDER BY id DESC LIMIT 500');
        } else {
            $st = $this->db->pdo()->prepare('SELECT id, slug, title, base_price_cents, tag, img, risk, is_event, required_level, is_community, is_featured, created_by FROM perksin_cases WHERE isDeleted=0 AND created_by = :u ORDER BY id DESC LIMIT 500');
            $st->execute([':u'=>$uid]);
            $stmt = $st;
        }
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }
    private function listCaseMetaOptions(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $type = trim((string)($_GET['type'] ?? ''));
        if (!in_array($type, ['tag', 'risk'], true)) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $stmt = $this->db->pdo()->prepare('SELECT id, option_type, option_value, option_label, sort_order FROM perksin_case_meta_options WHERE option_type = ? AND isDeleted = 0 ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$type]);
        echo json_encode(['items' => $stmt->fetchAll()]);
    }
    private function saveCaseMetaOption(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $type = trim((string)($d['type'] ?? ''));
        $value = trim((string)($d['value'] ?? ''));
        $label = trim((string)($d['label'] ?? ''));
        $sortOrder = (int)($d['sort_order'] ?? 0);
        if (!in_array($type, ['tag', 'risk'], true) || $value === '' || $label === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $value = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '');
        $value = trim($value, '-');
        if ($value === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_case_meta_options SET option_value=?, option_label=?, sort_order=?, modified_by=?, isDeleted=0 WHERE id=? AND option_type=?');
            $st->execute([$value, $label, $sortOrder, $uid, $id, $type]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_case_meta_options(option_type, option_value, option_label, sort_order, created_by, isDeleted) VALUES (?,?,?,?,?,0)');
            $st->execute([$type, $value, $label, $sortOrder, $uid]);
        }
        echo json_encode(['ok' => true]);
    }
    private function deleteCaseMetaOption(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('UPDATE perksin_case_meta_options SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok' => true]);
    }
    private function saveCase(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $title = trim((string)($d['title'] ?? ''));
        $basePriceUSD = (float)($d['basePriceUSD'] ?? 0);
        $cents = (int)round($basePriceUSD*100);
        $slug = trim((string)($d['slug'] ?? ''));
        $tag = trim((string)($d['tag'] ?? 'starter'));
        $img = trim((string)($d['img'] ?? ''));
        if ($img !== '' && !preg_match('~^https?://~i', $img)) {
            $img = '/' . ltrim($img, '/');
        }
        $risk = trim((string)($d['risk'] ?? 'medium'));
        $requiredLevel = (int)($d['required_level'] ?? 1);
        $hasEventFlag = array_key_exists('is_event', $d);
        $isEvent = $hasEventFlag ? (!empty($d['is_event']) ? 1 : 0) : null;
        $isCommunity = array_key_exists('is_community', $d) ? (!empty($d['is_community']) ? 1 : 0) : 1;
        $isFeatured = !empty($d['is_featured']) ? 1 : 0;
        if ($title==='') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if ($slug === '') $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', $title)) ?: ('case-'.substr(sha1(uniqid('',true)),0,6));
        $tagOptions = $this->getCaseMetaOptionsMap('tag');
        $riskOptions = $this->getCaseMetaOptionsMap('risk');
        if (!$tagOptions) $tagOptions = ['starter' => 'Starter'];
        if (!$riskOptions) $riskOptions = ['medium' => 'Medium'];
        if (!array_key_exists($tag, $tagOptions)) $tag = (string)array_key_first($tagOptions);
        if (!array_key_exists($risk, $riskOptions)) $risk = (string)array_key_first($riskOptions);
        $requiredLevel = max(1, min(10, $requiredLevel));
        $pdo = $this->db->pdo();
        if ($id) {
            $existingStmt = $pdo->prepare('SELECT img, is_event FROM perksin_cases WHERE id=? LIMIT 1');
            $existingStmt->execute([$id]);
            $existing = $existingStmt->fetch() ?: null;
            if (!$isAdmin) { $own = $pdo->prepare('SELECT created_by FROM perksin_cases WHERE id=?'); $own->execute([$id]); $cb = (int)$own->fetchColumn(); if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; } }
            if ($img === '' && $existing) $img = (string)($existing['img'] ?? '');
            if ($isEvent === null && $existing) $isEvent = (int)($existing['is_event'] ?? 0);
            $st = $pdo->prepare('UPDATE perksin_cases SET slug=?, title=?, base_price_cents=?, tag=?, img=?, risk=?, is_event=?, required_level=?, is_community=?, is_featured=?, modified_by=? WHERE id=?');
            $st->execute([$slug, $title, $cents, $tag, ($img !== '' ? $img : null), $risk, (int)($isEvent ?? 0), $requiredLevel, $isCommunity, $isFeatured, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_cases(slug,title,base_price_cents,tag,img,risk,is_event,required_level,is_community,is_featured,is_active,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$slug, $title, $cents, $tag, ($img !== '' ? $img : null), $risk, (int)($isEvent ?? 0), $requiredLevel, $isCommunity, $isFeatured, 1, $uid]);
        }
        echo json_encode(['ok'=>true]);
    }
    private function deleteCase(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if (!$isAdmin) {
            $own = $pdo->prepare('SELECT created_by FROM perksin_cases WHERE id=?');
            $own->execute([$id]);
            $cb = (int)$own->fetchColumn();
            if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        }
        $st = $pdo->prepare('UPDATE perksin_cases SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }

    private function listCaseItems(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $caseId = (int)($_GET['case_id'] ?? 0);
        if ($caseId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $stmt = $this->db->pdo()->prepare('
            SELECT ci.id, ci.case_id, ci.offer_id, ci.category_id, ci.bundle_id, ci.source_type, ci.title, ci.value_cents, ci.weight, ci.stock_type, ci.stock_qty, ci.stock_reserved, ci.stock_consumed,
                   o.title AS offer_title, c.name AS category_name, b.name AS bundle_name
            FROM perksin_case_items ci
            LEFT JOIN perksin_offers o ON o.id = ci.offer_id
            LEFT JOIN perksin_categories c ON c.id = ci.category_id
            LEFT JOIN perksin_product_bundles b ON b.id = ci.bundle_id
            WHERE ci.case_id = ? AND ci.isDeleted = 0
            ORDER BY ci.id ASC
        ');
        $stmt->execute([$caseId]);
        echo json_encode(['items' => $stmt->fetchAll()]);
    }

    private function saveCaseItem(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $caseId = (int)($d['case_id'] ?? 0);
        $sourceType = trim((string)($d['source_type'] ?? 'offer'));
        $offerId = isset($d['offer_id']) && $d['offer_id'] !== '' ? (int)$d['offer_id'] : null;
        $categoryId = isset($d['category_id']) && $d['category_id'] !== '' ? (int)$d['category_id'] : null;
        $bundleId = isset($d['bundle_id']) && $d['bundle_id'] !== '' ? (int)$d['bundle_id'] : null;
        $title = trim((string)($d['title'] ?? ''));
        $valueUSD = (float)($d['valueUSD'] ?? 0);
        $weight = max(1, (int)($d['weight'] ?? 1));
        if ($caseId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        if (!in_array($sourceType, ['offer', 'category', 'bundle'], true)) $sourceType = 'offer';
        $offerId = $sourceType === 'offer' ? $offerId : null;
        $categoryId = $sourceType === 'category' ? $categoryId : null;
        $bundleId = $sourceType === 'bundle' ? $bundleId : null;
        if ($offerId) {
            $stOffer = $this->db->pdo()->prepare('SELECT title, price_cents FROM perksin_offers WHERE id = ? AND isDeleted = 0 LIMIT 1');
            $stOffer->execute([$offerId]);
            $offer = $stOffer->fetch();
            if ($offer) {
                if ($title === '') $title = (string)$offer['title'];
                if ($valueUSD <= 0) $valueUSD = ((int)($offer['price_cents'] ?? 0)) / 100;
            }
        }
        if ($categoryId) {
            $stCat = $this->db->pdo()->prepare('SELECT name FROM perksin_categories WHERE id = ? AND isDeleted = 0 LIMIT 1');
            $stCat->execute([$categoryId]);
            $cat = $stCat->fetch();
            if ($cat) {
                $title = (string)($cat['name'] ?? 'Category');
                $stVal = $this->db->pdo()->prepare('SELECT AVG(price_cents) AS avg_cents FROM perksin_offers WHERE category_id = ? AND isDeleted = 0');
                $stVal->execute([$categoryId]);
                $avg = $stVal->fetchColumn();
                if ($valueUSD <= 0) $valueUSD = ((int)$avg) / 100;
            }
        }
        if ($bundleId) {
            $stBundle = $this->db->pdo()->prepare('SELECT name FROM perksin_product_bundles WHERE id = ? AND isDeleted = 0 LIMIT 1');
            $stBundle->execute([$bundleId]);
            $bundle = $stBundle->fetch();
            if ($bundle) {
                $title = (string)($bundle['name'] ?? 'Bundle');
                $visited = [];
                $sum = $this->calculateBundleValueCents($bundleId, $visited);
                if ($valueUSD <= 0) $valueUSD = ((int)$sum) / 100;
            }
        }
        if ($title === '') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $valueCents = (int)round($valueUSD * 100);
        $pdo = $this->db->pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE perksin_case_items SET source_type=?, offer_id=?, category_id=?, bundle_id=?, title=?, value_cents=?, weight=?, modified_by=? WHERE id=?');
            $st->execute([$sourceType, $offerId, $categoryId, $bundleId, $title, $valueCents, $weight, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_case_items(case_id, source_type, offer_id, category_id, bundle_id, title, value_cents, weight, created_by) VALUES (?,?,?,?,?,?,?,?,?)');
            $st->execute([$caseId, $sourceType, $offerId, $categoryId, $bundleId, $title, $valueCents, $weight, $uid]);
        }
        echo json_encode(['ok' => true]);
    }

    private function deleteCaseItem(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $st = $this->db->pdo()->prepare('UPDATE perksin_case_items SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok' => true]);
    }

    // Admin: list badges
    private function listBadges(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        if ($isAdmin) {
            $stmt = $this->db->pdo()->query('SELECT id, code, name, created_by FROM perksin_badges WHERE isDeleted=0 ORDER BY id DESC LIMIT 500');
        } else {
            $st = $this->db->pdo()->prepare('SELECT id, code, name, created_by FROM perksin_badges WHERE isDeleted=0 AND created_by=:u ORDER BY id DESC LIMIT 500');
            $st->execute([':u'=>$uid]);
            $stmt = $st;
        }
        echo json_encode(['items'=>$stmt->fetchAll()]);
    }
    private function saveBadge(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = isset($d['id']) ? (int)$d['id'] : null;
        $code = trim((string)($d['code'] ?? ''));
        $name = trim((string)($d['name'] ?? ''));
        if ($code==='' || $name==='') { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if ($id) {
            if (!$isAdmin) { $own = $pdo->prepare('SELECT created_by FROM perksin_badges WHERE id=?'); $own->execute([$id]); $cb = (int)$own->fetchColumn(); if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; } }
            $st = $pdo->prepare('UPDATE perksin_badges SET code=?, name=?, modified_by=? WHERE id=?');
            $st->execute([$code, $name, $uid, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO perksin_badges(code,name,created_by) VALUES (?,?,?)');
            $st->execute([$code, $name, $uid]);
        }
        echo json_encode(['ok'=>true]);
    }
    private function deleteBadge(): void
    {
        if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); return; }
        $uid = (int)$_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $isCompany = !empty($_SESSION['is_company']);
        if (!$isAdmin && !$isCompany) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        $d = $this->parseJson();
        $id = (int)($d['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_input']); return; }
        $pdo = $this->db->pdo();
        if (!$isAdmin) {
            $own = $pdo->prepare('SELECT created_by FROM perksin_badges WHERE id=?');
            $own->execute([$id]);
            $cb = (int)$own->fetchColumn();
            if ($cb !== $uid) { http_response_code(403); echo json_encode(['error'=>'forbidden']); return; }
        }
        $st = $pdo->prepare('UPDATE perksin_badges SET isDeleted=1, modified_by=? WHERE id=?');
        $st->execute([$uid, $id]);
        echo json_encode(['ok'=>true]);
    }
}
