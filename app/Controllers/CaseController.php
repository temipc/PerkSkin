<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\Database;

class CaseController
{
    private LocalizationService $i18n;
    private ?Database $db;
    private ?array $bundleItemColumns = null;

    public function __construct(LocalizationService $i18n, ?Database $db = null)
    {
        $this->i18n = $i18n;
        $this->db = $db;
    }

    private function normalizeImagePath(?string $path, string $fallback = '/assets/images/case-1.svg'): string
    {
        $value = trim((string)$path);
        if ($value === '') return $fallback;
        if (preg_match('~^https?://~i', $value)) return $value;
        return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
    }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('~[^a-z0-9]+~', '-', $s);
        return trim($s, '-');
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

    private function bundleTotalValue(\PDO $pdo, int $bundleId, array &$visited = []): float
    {
        if ($bundleId <= 0 || isset($visited[$bundleId])) return 0.0;
        $visited[$bundleId] = true;
        $sum = 0.0;
        $columns = $this->bundleItemColumns($pdo);
        $hasExtendedSources = in_array('source_type', $columns, true);
        $sql = $hasExtendedSources
            ? 'SELECT quantity, source_type, offer_id, source_category_id, source_case_id, source_bundle_id FROM perksin_product_bundle_items WHERE bundle_id = ? AND isDeleted = 0 ORDER BY id ASC'
            : 'SELECT quantity, offer_id FROM perksin_product_bundle_items WHERE bundle_id = ? AND isDeleted = 0 ORDER BY id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bundleId]);
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $qty = max(1, (int)($row['quantity'] ?? 1));
            $type = (string)($row['source_type'] ?? 'offer');
            if ($type === 'category' && !empty($row['source_category_id'])) {
                $st = $pdo->prepare('SELECT SUM(price_cents) FROM perksin_offers WHERE category_id = ? AND isDeleted = 0');
                $st->execute([(int)$row['source_category_id']]);
                $sum += (((int)($st->fetchColumn() ?: 0)) / 100.0) * $qty;
                continue;
            }
            if ($type === 'case' && !empty($row['source_case_id'])) {
                $st = $pdo->prepare('SELECT base_price_cents FROM perksin_cases WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $st->execute([(int)$row['source_case_id']]);
                $sum += (((int)($st->fetchColumn() ?: 0)) / 100.0) * $qty;
                continue;
            }
            if ($type === 'bundle' && !empty($row['source_bundle_id'])) {
                $sum += $this->bundleTotalValue($pdo, (int)$row['source_bundle_id'], $visited) * $qty;
                continue;
            }
            if (!empty($row['offer_id'])) {
                $st = $pdo->prepare('SELECT price_cents FROM perksin_offers WHERE id = ? AND isDeleted = 0 LIMIT 1');
                $st->execute([(int)$row['offer_id']]);
                $sum += (((int)($st->fetchColumn() ?: 0)) / 100.0) * $qty;
            }
        }
        unset($visited[$bundleId]);
        return $sum;
    }

    private function resolveEventAccess(\PDO $pdo): array
    {
        try {
            $rows = $pdo->query('SELECT id, title, start_at, end_at, date FROM perksin_events ORDER BY COALESCE(start_at, date) ASC, id ASC')->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return ['is_active' => false, 'event_id' => 0, 'event_title' => '', 'start_at' => '', 'end_at' => ''];
        }
        $now = time();
        $active = null;
        $upcoming = null;
        foreach ($rows as $event) {
            $start = strtotime(str_replace('T', ' ', (string)($event['start_at'] ?? $event['date'] ?? '')));
            $end = strtotime(str_replace('T', ' ', (string)($event['end_at'] ?? $event['start_at'] ?? $event['date'] ?? '')));
            if ($start === false || $end === false) continue;
            if ($start <= $now && $end >= $now) {
                if ($active === null || $start < ($active['start_ts'] ?? PHP_INT_MAX)) $active = $event + ['start_ts' => $start, 'end_ts' => $end];
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

    public function show(string $slug): void
    {
        $title = $this->i18n->t('app.title');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        $found = null;
        if ($this->db) {
            $pdo = $this->db->pdo();
            // try find by slug column if present; if not, fallback to title slug
            $stmt = $pdo->prepare('SELECT id, slug, title, base_price_cents, tag, img, risk, required_level, is_event FROM perksin_cases WHERE slug = ? AND isDeleted=0 LIMIT 1');
            $stmt->execute([$slug]);
            $row = $stmt->fetch();
            if (!$row) {
                // fallback try by title-derived slug
                $stmtAll = $pdo->query('SELECT id, title, base_price_cents, tag, img, risk, required_level, is_event FROM perksin_cases WHERE isDeleted=0');
                foreach ($stmtAll->fetchAll() as $r) {
                    if ($this->slugify($r['title']) === $slug) { $row = $r; break; }
                }
            }
            if ($row) {
                $list = $this->loadCaseItems($pdo, (int)$row['id']);
                $eventAccess = !empty($row['is_event']) ? $this->resolveEventAccess($pdo) : ['is_active' => true, 'event_id' => 0, 'event_title' => '', 'start_at' => '', 'end_at' => ''];
                $found = [
                    'id'=>(int)$row['id'],
                    'slug'=>(string)($row['slug'] ?? ''),
                    'title'=>$row['title'],
                    'price'=>((int)$row['base_price_cents'])/100.0,
                    'tag'=>$row['tag'],
                    'img'=>$this->normalizeImagePath($row['img'] ?? null),
                    'risk'=>$row['risk'] ?: 'medium',
                    'required_level'=>max(1, (int)($row['required_level'] ?? 1)),
                    'is_event'=>!empty($row['is_event']),
                    'event_access'=>$eventAccess,
                    'items'=>$list,
                ];
            }
        }
        if (!$found) {
            $data = require __DIR__ . '/../Config/cases.php';
            $all = array_merge($data['cases'] ?? [], $data['eventCases'] ?? []);
            foreach ($all as $c) {
                if ($this->slugify($c['title']) === $slug) { $found = $c; break; }
            }
        }
        if (!$found) {
            http_response_code(404);
            echo 'Case not found';
            return;
        }
        $case = $found;
        include __DIR__ . '/../Views/case.php';
    }
}
