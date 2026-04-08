<?php
namespace App\Services;

use PDO;

class PageBuilderService
{
    private ?Database $db;

    public function __construct(?Database $db)
    {
        $this->db = $db;
        $this->ensureSchema();
    }

    public function getModuleRegistry(): array
    {
        return [
            'home.hero' => [
                'key' => 'home.hero',
                'page' => 'home',
                'name' => 'Hero Banner',
                'description' => 'Main hero with CTA buttons and Swiper banner.',
            ],
            'home.live-drop' => [
                'key' => 'home.live-drop',
                'page' => 'home',
                'name' => 'Live Drop',
                'description' => 'Ticker section with live drop toggle.',
            ],
            'home.spinner-chat' => [
                'key' => 'home.spinner-chat',
                'page' => 'home',
                'name' => 'Spinner + Chat',
                'description' => 'Spinner viewport and live chat block.',
            ],
            'home.support-box' => [
                'key' => 'home.support-box',
                'page' => 'home',
                'name' => 'Support Box',
                'description' => 'Donation/support information card with CTA button.',
            ],
            'home.cases-grid' => [
                'key' => 'home.cases-grid',
                'page' => 'home',
                'name' => 'Cases Grid',
                'description' => 'Filterable cases overview grid.',
            ],
            'home.community-carousel' => [
                'key' => 'home.community-carousel',
                'page' => 'home',
                'name' => 'Community Carousel',
                'description' => 'Community case carousel block.',
            ],
            'home.featured-carousel' => [
                'key' => 'home.featured-carousel',
                'page' => 'home',
                'name' => 'Featured Carousel',
                'description' => 'Featured case carousel block.',
            ],
            'home.bundle-rewards' => [
                'key' => 'home.bundle-rewards',
                'page' => 'home',
                'name' => 'Bundle Rewards',
                'description' => 'Bundle rewards showcase.',
            ],
            'home.event-cases' => [
                'key' => 'home.event-cases',
                'page' => 'home',
                'name' => 'Event Cases',
                'description' => 'Event countdown and event case cards.',
            ],
            'events.header' => [
                'key' => 'events.header',
                'page' => 'events',
                'name' => 'Events Header',
                'description' => 'Page title and back action.',
            ],
            'events.calendar' => [
                'key' => 'events.calendar',
                'page' => 'events',
                'name' => 'Events Calendar',
                'description' => 'Calendar view of scheduled events.',
            ],
            'missions.header' => [
                'key' => 'missions.header',
                'page' => 'missions',
                'name' => 'Missions Header',
                'description' => 'Page title and back action.',
            ],
            'missions.grid' => [
                'key' => 'missions.grid',
                'page' => 'missions',
                'name' => 'Mission Cards',
                'description' => 'Mission listing cards.',
            ],
            'exchange.header' => [
                'key' => 'exchange.header',
                'page' => 'exchange',
                'name' => 'Exchange Header',
                'description' => 'Page title and back action.',
            ],
            'exchange.market' => [
                'key' => 'exchange.market',
                'page' => 'exchange',
                'name' => 'Exchange Market',
                'description' => 'Marketplace offer creation and listing.',
            ],
            'battles.header' => [
                'key' => 'battles.header',
                'page' => 'battles',
                'name' => 'Battles Header',
                'description' => 'Page title and back action.',
            ],
            'battles.hero' => [
                'key' => 'battles.hero',
                'page' => 'battles',
                'name' => 'Battles Hero',
                'description' => 'Intro card for the battles page.',
            ],
            'battles.rooms' => [
                'key' => 'battles.rooms',
                'page' => 'battles',
                'name' => 'Battle Rooms',
                'description' => 'List of current or planned battle rooms.',
            ],
        ];
    }

    public function getDefaultLayout(string $pageKey): array
    {
        if ($pageKey === 'home') {
            return [
                ['module' => 'home.hero'],
                ['module' => 'home.live-drop'],
                ['module' => 'home.spinner-chat'],
                ['module' => 'home.support-box'],
                ['module' => 'home.cases-grid'],
                ['module' => 'home.community-carousel'],
                ['module' => 'home.featured-carousel'],
                ['module' => 'home.bundle-rewards'],
                ['module' => 'home.event-cases'],
            ];
        }
        if ($pageKey === 'events') {
            return [
                ['module' => 'events.header'],
                ['module' => 'events.calendar'],
            ];
        }
        if ($pageKey === 'missions') {
            return [
                ['module' => 'missions.header'],
                ['module' => 'missions.grid'],
            ];
        }
        if ($pageKey === 'exchange') {
            return [
                ['module' => 'exchange.header'],
                ['module' => 'exchange.market'],
            ];
        }
        if ($pageKey === 'battles') {
            return [
                ['module' => 'battles.header'],
                ['module' => 'battles.hero'],
                ['module' => 'battles.rooms'],
            ];
        }

        return [];
    }

    public function getLayout(string $pageKey): array
    {
        $fallback = $this->getDefaultLayout($pageKey);
        if (!$this->db) {
            return $fallback;
        }

        $stmt = $this->db->pdo()->prepare('SELECT layout_json FROM perksin_page_layouts WHERE page_key = ? LIMIT 1');
        $stmt->execute([$pageKey]);
        $raw = $stmt->fetchColumn();
        if (!is_string($raw) || trim($raw) === '') {
            return $fallback;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $fallback;
        }

        return array_values(array_filter($decoded, static function ($item): bool {
            return is_array($item) && !empty($item['module']);
        }));
    }

    public function saveLayout(string $pageKey, array $layout): void
    {
        if (!$this->db) {
            return;
        }

        $normalized = array_values(array_map(static function (array $item): array {
            return [
                'module' => (string)($item['module'] ?? ''),
                'settings' => is_array($item['settings'] ?? null) ? $item['settings'] : new \stdClass(),
            ];
        }, array_filter($layout, static function ($item): bool {
            return is_array($item) && !empty($item['module']);
        })));

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $payload = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('INSERT INTO perksin_page_layouts(page_key, layout_json, modified_at) VALUES (?,?,CURRENT_TIMESTAMP) ON CONFLICT(page_key) DO UPDATE SET layout_json=excluded.layout_json, modified_at=CURRENT_TIMESTAMP');
            $stmt->execute([$pageKey, $payload]);
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO perksin_page_layouts(page_key, layout_json) VALUES (?,?) ON DUPLICATE KEY UPDATE layout_json=VALUES(layout_json)');
        $stmt->execute([$pageKey, $payload]);
    }

    public function getPageAccessMap(): array
    {
        $items = $this->getDefaultPageAccessItems();
        if (!$this->db) {
            return $items;
        }

        $rows = $this->db->pdo()->query('SELECT page_key, enabled, show_in_nav, guest_enabled, user_enabled, admin_enabled, nav_label, nav_href, sort_order FROM perksin_page_access')->fetchAll() ?: [];
        foreach ($rows as $row) {
            $key = (string)($row['page_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $base = $items[$key] ?? [
                'page_key' => $key,
                'enabled' => true,
                'show_in_nav' => false,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => ucfirst($key),
                'nav_href' => '/index.php?page=' . rawurlencode($key),
                'sort_order' => 999,
            ];
            $items[$key] = [
                'page_key' => $key,
                'enabled' => !empty($row['enabled']),
                'show_in_nav' => !empty($row['show_in_nav']),
                'guest_enabled' => !empty($row['guest_enabled']),
                'user_enabled' => !empty($row['user_enabled']),
                'admin_enabled' => !empty($row['admin_enabled']),
                'nav_label' => (string)($row['nav_label'] ?? $base['nav_label']),
                'nav_href' => (string)($row['nav_href'] ?? $base['nav_href']),
                'sort_order' => (int)($row['sort_order'] ?? $base['sort_order']),
            ];
        }

        uasort($items, static fn(array $a, array $b): int => ((int)$a['sort_order']) <=> ((int)$b['sort_order']));
        return $items;
    }

    public function savePageAccess(string $pageKey, array $config): void
    {
        if (!$this->db) {
            return;
        }

        $defaults = $this->getDefaultPageAccessItems()[$pageKey] ?? [
            'nav_label' => ucfirst($pageKey),
            'nav_href' => '/index.php?page=' . rawurlencode($pageKey),
            'sort_order' => 999,
        ];
        $payload = [
            'enabled' => !empty($config['enabled']) ? 1 : 0,
            'show_in_nav' => !empty($config['show_in_nav']) ? 1 : 0,
            'guest_enabled' => !empty($config['guest_enabled']) ? 1 : 0,
            'user_enabled' => !empty($config['user_enabled']) ? 1 : 0,
            'admin_enabled' => !empty($config['admin_enabled']) ? 1 : 0,
            'nav_label' => trim((string)($config['nav_label'] ?? $defaults['nav_label'])),
            'nav_href' => trim((string)($config['nav_href'] ?? $defaults['nav_href'])),
            'sort_order' => (int)($config['sort_order'] ?? $defaults['sort_order']),
        ];

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('INSERT INTO perksin_page_access(page_key, enabled, show_in_nav, guest_enabled, user_enabled, admin_enabled, nav_label, nav_href, sort_order, modified_at) VALUES (?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(page_key) DO UPDATE SET enabled=excluded.enabled, show_in_nav=excluded.show_in_nav, guest_enabled=excluded.guest_enabled, user_enabled=excluded.user_enabled, admin_enabled=excluded.admin_enabled, nav_label=excluded.nav_label, nav_href=excluded.nav_href, sort_order=excluded.sort_order, modified_at=CURRENT_TIMESTAMP');
            $stmt->execute([$pageKey, $payload['enabled'], $payload['show_in_nav'], $payload['guest_enabled'], $payload['user_enabled'], $payload['admin_enabled'], $payload['nav_label'], $payload['nav_href'], $payload['sort_order']]);
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO perksin_page_access(page_key, enabled, show_in_nav, guest_enabled, user_enabled, admin_enabled, nav_label, nav_href, sort_order) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled), show_in_nav=VALUES(show_in_nav), guest_enabled=VALUES(guest_enabled), user_enabled=VALUES(user_enabled), admin_enabled=VALUES(admin_enabled), nav_label=VALUES(nav_label), nav_href=VALUES(nav_href), sort_order=VALUES(sort_order)');
        $stmt->execute([$pageKey, $payload['enabled'], $payload['show_in_nav'], $payload['guest_enabled'], $payload['user_enabled'], $payload['admin_enabled'], $payload['nav_label'], $payload['nav_href'], $payload['sort_order']]);
    }

    public function listPageConfigs(): array
    {
        $access = $this->getPageAccessMap();
        $items = [];
        foreach ($access as $pageKey => $cfg) {
            $items[] = [
                'page_key' => $pageKey,
                'access' => $cfg,
                'layout' => $this->getLayout($pageKey),
            ];
        }
        return $items;
    }

    public function isPageEnabled(string $pageKey): bool
    {
        $access = $this->getPageAccessMap();
        if (!isset($access[$pageKey])) {
            return true;
        }

        $cfg = $access[$pageKey];
        if (empty($cfg['enabled'])) {
            return false;
        }

        $role = $this->resolveViewerRole();
        if ($role === 'admin') {
            return !empty($cfg['admin_enabled']);
        }
        if ($role === 'user') {
            return !empty($cfg['user_enabled']);
        }
        return !empty($cfg['guest_enabled']);
    }

    public function getNavigationItems(callable $translate): array
    {
        $items = [];
        foreach ($this->getPageAccessMap() as $cfg) {
            if (empty($cfg['show_in_nav']) || !$this->isPageEnabled((string)$cfg['page_key'])) {
                continue;
            }
            $items[] = [
                'page_key' => (string)$cfg['page_key'],
                'label' => $this->translateLabel((string)$cfg['nav_label'], $translate),
                'href' => (string)$cfg['nav_href'],
            ];
        }
        return $items;
    }

    private function translateLabel(string $value, callable $translate): string
    {
        if (str_starts_with($value, 't:')) {
            return (string)$translate(substr($value, 2));
        }
        return $value;
    }

    private function resolveViewerRole(): string
    {
        if (!empty($_SESSION['is_admin'])) {
            return 'admin';
        }
        if (!empty($_SESSION['user_id'])) {
            return 'user';
        }
        return 'guest';
    }

    private function getDefaultPageAccessItems(): array
    {
        return [
            'home' => [
                'page_key' => 'home',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.cases',
                'nav_href' => '/index.php?page=home#cases',
                'sort_order' => 10,
            ],
            'events' => [
                'page_key' => 'events',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.events',
                'nav_href' => '/index.php?page=events',
                'sort_order' => 20,
            ],
            'missions' => [
                'page_key' => 'missions',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.missions',
                'nav_href' => '/index.php?page=missions',
                'sort_order' => 30,
            ],
            'exchange' => [
                'page_key' => 'exchange',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.exchange',
                'nav_href' => '/index.php?page=exchange',
                'sort_order' => 40,
            ],
            'dashboard' => [
                'page_key' => 'dashboard',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => false,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.dashboard',
                'nav_href' => '/index.php?page=dashboard',
                'sort_order' => 50,
            ],
            'battles' => [
                'page_key' => 'battles',
                'enabled' => true,
                'show_in_nav' => true,
                'guest_enabled' => true,
                'user_enabled' => true,
                'admin_enabled' => true,
                'nav_label' => 't:nav.battles',
                'nav_href' => '/index.php?page=battles',
                'sort_order' => 25,
            ],
        ];
    }

    private function ensureSchema(): void
    {
        if (!$this->db) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_page_layouts (
                page_key TEXT PRIMARY KEY,
                layout_json TEXT NULL,
                settings_json TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                modified_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
            $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_page_access (
                page_key TEXT PRIMARY KEY,
                enabled INTEGER NOT NULL DEFAULT 1,
                show_in_nav INTEGER NOT NULL DEFAULT 1,
                guest_enabled INTEGER NOT NULL DEFAULT 1,
                user_enabled INTEGER NOT NULL DEFAULT 1,
                admin_enabled INTEGER NOT NULL DEFAULT 1,
                nav_label TEXT NULL,
                nav_href TEXT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                modified_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
            return;
        }

        $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_page_layouts (
            page_key VARCHAR(64) PRIMARY KEY,
            layout_json LONGTEXT NULL,
            settings_json LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $pdo->exec('CREATE TABLE IF NOT EXISTS perksin_page_access (
            page_key VARCHAR(64) PRIMARY KEY,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            show_in_nav TINYINT(1) NOT NULL DEFAULT 1,
            guest_enabled TINYINT(1) NOT NULL DEFAULT 1,
            user_enabled TINYINT(1) NOT NULL DEFAULT 1,
            admin_enabled TINYINT(1) NOT NULL DEFAULT 1,
            nav_label VARCHAR(191) NULL,
            nav_href VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
}
