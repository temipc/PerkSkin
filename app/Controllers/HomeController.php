<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\Database;
use App\Services\PageBuilderService;

class HomeController
{
    private LocalizationService $i18n;
    private ?Database $db;
    private ?PageBuilderService $pageBuilder;
    private ?array $bundleItemColumns = null;

    public function __construct(LocalizationService $i18n, ?Database $db = null, ?PageBuilderService $pageBuilder = null)
    {
        $this->i18n = $i18n;
        $this->db = $db;
        $this->pageBuilder = $pageBuilder;
    }

    private function normalizeImagePath(?string $path, string $fallback = '/assets/images/case-1.svg'): string
    {
        $value = trim((string)$path);
        if ($value === '') return $fallback;
        if (preg_match('~^https?://~i', $value)) return $value;
        return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
    }

    private function loadCaseItems(\PDO $pdo, int $caseId): array
    {
        $stmt = $pdo->prepare('SELECT id, offer_id, category_id, bundle_id, source_type, title, value_cents, weight FROM perksin_case_items WHERE case_id = ? AND isDeleted=0 ORDER BY id ASC');
        $stmt->execute([$caseId]);
        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $it) {
            $sourceType = (string)($it['source_type'] ?? 'offer');
            $weight = (int)($it['weight'] ?? 1);
            if ($sourceType === 'category' && !empty($it['category_id'])) {
                $stOffers = $pdo->prepare('SELECT id, title, price_cents FROM perksin_offers WHERE category_id = ? AND isDeleted = 0 ORDER BY id ASC');
                $stOffers->execute([(int)$it['category_id']]);
                foreach ($stOffers->fetchAll() ?: [] as $offer) {
                    $items[] = ['id'=>(int)$it['id'], 'name'=>$offer['title'], 'value'=>((int)$offer['price_cents'])/100.0, 'weight'=>$weight, 'offer_id'=>(int)$offer['id']];
                }
                continue;
            }
            if ($sourceType === 'bundle' && !empty($it['bundle_id'])) {
                $stBundle = $pdo->prepare('SELECT b.name FROM perksin_product_bundles b WHERE b.id = ? AND b.isDeleted = 0 LIMIT 1');
                $stBundle->execute([(int)$it['bundle_id']]);
                $bundle = $stBundle->fetch() ?: ['name' => $it['title']];
                $visited = [];
                $items[] = ['id'=>(int)$it['id'], 'name'=>(string)($bundle['name'] ?? $it['title']), 'value'=>$this->bundleTotalValue($pdo, (int)$it['bundle_id'], $visited), 'weight'=>$weight, 'offer_id'=>null];
                continue;
            }
            $items[] = ['id'=>(int)$it['id'], 'name'=>$it['title'], 'value'=>((int)$it['value_cents'])/100.0, 'weight'=>$weight, 'offer_id'=>isset($it['offer_id'])?(int)$it['offer_id']:null];
        }
        return $items;
    }

    private function loadBundleContents(\PDO $pdo, int $bundleId, array &$visited = []): array
    {
        if ($bundleId <= 0) return [];
        $columns = $this->bundleItemColumns($pdo);
        $hasExtendedSources = in_array('source_type', $columns, true);
        $sql = $hasExtendedSources
            ? '
                SELECT bi.id, bi.quantity, bi.source_type, bi.offer_id, bi.source_category_id, bi.source_case_id, bi.source_bundle_id,
                       o.id AS offer_id, o.title AS offer_title, o.price_cents,
                       oc.name AS offer_category_name,
                       c.name AS category_name,
                       cs.title AS case_title,
                       pb.name AS source_bundle_name
                FROM perksin_product_bundle_items bi
                LEFT JOIN perksin_offers o ON o.id = bi.offer_id AND o.isDeleted = 0
                LEFT JOIN perksin_categories oc ON oc.id = o.category_id
                LEFT JOIN perksin_categories c ON c.id = bi.source_category_id
                LEFT JOIN perksin_cases cs ON cs.id = bi.source_case_id AND cs.isDeleted = 0
                LEFT JOIN perksin_product_bundles pb ON pb.id = bi.source_bundle_id AND pb.isDeleted = 0
                WHERE bi.bundle_id = ? AND bi.isDeleted = 0
                ORDER BY bi.id ASC
            '
            : '
                SELECT bi.id, bi.quantity, o.id AS offer_id, o.title AS offer_title, o.price_cents, c.name AS category_name
                FROM perksin_product_bundle_items bi
                INNER JOIN perksin_offers o ON o.id = bi.offer_id AND o.isDeleted = 0
                LEFT JOIN perksin_categories c ON c.id = o.category_id
                WHERE bi.bundle_id = ? AND bi.isDeleted = 0
                ORDER BY bi.id ASC
            ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bundleId]);
        $items = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $sourceType = (string)($row['source_type'] ?? 'offer');
            if ($sourceType === 'category') {
                $items[] = [
                    'type' => 'category',
                    'name' => (string)($row['category_name'] ?? ''),
                    'quantity' => max(1, (int)($row['quantity'] ?? 1)),
                    'value' => $this->bundleCategoryValue($pdo, (int)($row['source_category_id'] ?? 0)),
                    'category' => (string)($row['category_name'] ?? ''),
                ];
                continue;
            }
            if ($sourceType === 'case') {
                $items[] = [
                    'type' => 'case',
                    'name' => (string)($row['case_title'] ?? ''),
                    'quantity' => max(1, (int)($row['quantity'] ?? 1)),
                    'value' => $this->bundleCaseValue($pdo, (int)($row['source_case_id'] ?? 0)),
                    'category' => '',
                ];
                continue;
            }
            if ($sourceType === 'bundle') {
                $items[] = [
                    'type' => 'bundle',
                    'name' => (string)($row['source_bundle_name'] ?? ''),
                    'quantity' => max(1, (int)($row['quantity'] ?? 1)),
                    'value' => $this->bundleTotalValue($pdo, (int)($row['source_bundle_id'] ?? 0), $visited),
                    'category' => '',
                ];
                continue;
            }
            $items[] = [
                'type' => 'product',
                'name' => (string)($row['offer_title'] ?? ''),
                'quantity' => max(1, (int)($row['quantity'] ?? 1)),
                'value' => ((int)($row['price_cents'] ?? 0)) / 100.0,
                'category' => (string)($row['offer_category_name'] ?? $row['category_name'] ?? ''),
                'offer_id' => (int)($row['offer_id'] ?? 0),
            ];
        }
        return $items;
    }

    private function bundleItemColumns(\PDO $pdo): array
    {
        if ($this->bundleItemColumns !== null) return $this->bundleItemColumns;
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $rows = $pdo->query('PRAGMA table_info(perksin_product_bundle_items)')->fetchAll();
            return $this->bundleItemColumns = array_map(static fn(array $row) => (string)($row['name'] ?? ''), $rows ?: []);
        }
        $rows = $pdo->query('SHOW COLUMNS FROM perksin_product_bundle_items')->fetchAll();
        return $this->bundleItemColumns = array_map(static fn(array $row) => (string)($row['Field'] ?? ''), $rows ?: []);
    }

    private function bundleCategoryValue(\PDO $pdo, int $categoryId): float
    {
        if ($categoryId <= 0) return 0.0;
        $stmt = $pdo->prepare('SELECT SUM(price_cents) FROM perksin_offers WHERE category_id = ? AND isDeleted = 0');
        $stmt->execute([$categoryId]);
        return ((int)($stmt->fetchColumn() ?: 0)) / 100.0;
    }

    private function bundleCaseValue(\PDO $pdo, int $caseId): float
    {
        if ($caseId <= 0) return 0.0;
        $stmt = $pdo->prepare('SELECT base_price_cents FROM perksin_cases WHERE id = ? AND isDeleted = 0 LIMIT 1');
        $stmt->execute([$caseId]);
        return ((int)($stmt->fetchColumn() ?: 0)) / 100.0;
    }

    private function bundleTotalValue(\PDO $pdo, int $bundleId, array &$visited = []): float
    {
        if ($bundleId <= 0 || isset($visited[$bundleId])) return 0.0;
        $visited[$bundleId] = true;
        $sum = 0.0;
        foreach ($this->loadBundleContents($pdo, $bundleId, $visited) as $item) {
            $sum += ((float)($item['value'] ?? 0)) * max(1, (int)($item['quantity'] ?? 1));
        }
        unset($visited[$bundleId]);
        return $sum;
    }

    private function resolveEventAccess(array $events): array
    {
        $now = time();
        $active = null;
        $upcoming = null;
        foreach ($events as $event) {
            $start = strtotime(str_replace('T', ' ', (string)($event['start_at'] ?? $event['date'] ?? '')));
            $end = strtotime(str_replace('T', ' ', (string)($event['end_at'] ?? $event['start_at'] ?? $event['date'] ?? '')));
            if ($start === false || $end === false) {
                continue;
            }
            if ($start <= $now && $end >= $now) {
                if ($active === null || $start < ($active['start_ts'] ?? PHP_INT_MAX)) {
                    $active = $event + ['start_ts' => $start, 'end_ts' => $end];
                }
                continue;
            }
            if ($start > $now && ($upcoming === null || $start < ($upcoming['start_ts'] ?? PHP_INT_MAX))) {
                $upcoming = $event + ['start_ts' => $start, 'end_ts' => $end];
            }
        }
        $selected = $active ?? $upcoming;
        return [
            'is_active' => $active !== null,
            'event_id' => (int)($selected['id'] ?? 0),
            'event_title' => (string)($selected['title'] ?? ''),
            'start_at' => (string)($selected['start_at'] ?? ''),
            'end_at' => (string)($selected['end_at'] ?? ''),
        ];
    }

    public function index(): void
    {
        $title = $this->i18n->t('app.title');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        $cases = [];
        $communityCases = [];
        $featuredCases = [];
        $eventCases = [];
        $homeEvents = [];
        $spinnerProducts = [];
        $homeBundles = [];
        if ($this->db) {
            $pdo = $this->db->pdo();
            $rows = $pdo->query("SELECT id, slug, title, base_price_cents, tag, img, risk, is_event, required_level, is_community, is_featured FROM perksin_cases WHERE is_active=1 AND isDeleted=0 ORDER BY id ASC")->fetchAll();
            foreach ($rows as $r) {
                $list = $this->loadCaseItems($pdo, (int)$r['id']);
                $row = [
                    'id' => (int)$r['id'],
                    'slug' => (string)($r['slug'] ?? ''),
                    'title' => $r['title'],
                    'price' => ((int)$r['base_price_cents'])/100.0,
                    'tag' => $r['tag'],
                    'img' => $this->normalizeImagePath($r['img'] ?? null),
                    'risk'=> $r['risk'] ?: 'medium',
                    'required_level' => max(1, (int)($r['required_level'] ?? 1)),
                    'is_community' => !empty($r['is_community']),
                    'is_featured' => !empty($r['is_featured']),
                    'is_event' => !empty($r['is_event']),
                    'items' => $list,
                ];
                $cases[] = $row;
                if ((int)$r['is_event'] === 1) { $eventCases[] = $row; }
                if (!empty($r['is_community'])) { $communityCases[] = $row; }
                if (!empty($r['is_featured'])) { $featuredCases[] = $row; }
            }
            try {
                $sp = $pdo->query("SELECT o.id, o.title, o.price_cents, o.product_type, c.name AS category_name FROM perksin_offers o LEFT JOIN perksin_categories c ON c.id = o.category_id WHERE o.isDeleted=0 AND o.use_home_spinner=1 ORDER BY o.id ASC")->fetchAll();
                foreach ($sp ?: [] as $row) {
                    $spinnerProducts[] = [
                        'id' => (int)$row['id'],
                        'title' => (string)$row['title'],
                        'value' => ((int)($row['price_cents'] ?? 0)) / 100.0,
                        'product_type' => (string)($row['product_type'] ?? 'product'),
                        'category_name' => (string)($row['category_name'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {}
            try {
                $eventRows = $pdo->query("SELECT id, date, title, description, href, start_at, end_at, color FROM perksin_events ORDER BY COALESCE(start_at, date) ASC, id ASC")->fetchAll();
                foreach ($eventRows ?: [] as $eventRow) {
                    $homeEvents[] = [
                        'id' => (int)($eventRow['id'] ?? 0),
                        'date' => (string)($eventRow['date'] ?? ''),
                        'title' => (string)($eventRow['title'] ?? ''),
                        'description' => (string)($eventRow['description'] ?? ''),
                        'href' => (string)($eventRow['href'] ?? ''),
                        'start_at' => (string)($eventRow['start_at'] ?? ''),
                        'end_at' => (string)($eventRow['end_at'] ?? ''),
                        'color' => (string)($eventRow['color'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {}
            $eventAccess = $this->resolveEventAccess($homeEvents);
            foreach ($eventCases as &$eventCase) {
                $eventCase['event_access'] = $eventAccess;
            }
            unset($eventCase);
            try {
                $bundles = $pdo->query("SELECT b.id, b.name
                    FROM perksin_product_bundles b
                    WHERE b.isDeleted = 0
                    ORDER BY b.id DESC")->fetchAll();
                foreach ($bundles ?: [] as $row) {
                    $visited = [];
                    $contents = $this->loadBundleContents($pdo, (int)$row['id'], $visited);
                    $value = 0.0;
                    $itemCount = 0;
                    foreach ($contents as $content) {
                        $qty = max(1, (int)($content['quantity'] ?? 1));
                        $itemCount += $qty;
                        $value += ((float)($content['value'] ?? 0)) * $qty;
                    }
                    $homeBundles[] = [
                        'id' => (int)$row['id'],
                        'name' => (string)$row['name'],
                        'item_count' => $itemCount,
                        'value' => $value,
                        'contents' => $contents,
                    ];
                }
            } catch (\Throwable $e) {}
        }
        if (empty($cases) && empty($eventCases) && empty($communityCases) && empty($featuredCases)) {
            $data = require __DIR__ . '/../Config/cases.php';
            $cases = $data['cases'] ?? [];
            $communityCases = $data['cases'] ?? [];
            $featuredCases = array_slice($data['cases'] ?? [], 0, 5);
            $eventCases = $data['eventCases'] ?? [];
        }
        $homeLayout = $this->pageBuilder ? $this->pageBuilder->getLayout('home') : [];
        include __DIR__ . '/../Views/home.php';
    }
}
