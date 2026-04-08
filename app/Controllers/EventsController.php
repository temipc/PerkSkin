<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\Database;
use App\Services\PageBuilderService;

class EventsController
{
    private LocalizationService $i18n;
    private ?Database $db;
    private ?PageBuilderService $pageBuilder;

    public function __construct(LocalizationService $i18n, ?Database $db = null, ?PageBuilderService $pageBuilder = null)
    {
        $this->i18n = $i18n;
        $this->db = $db;
        $this->pageBuilder = $pageBuilder;
    }

    public function index(): void
    {
        $title = $this->i18n->t('nav.events');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        $events = [];
        if ($this->db) {
            try {
                $stmt = $this->db->pdo()->query('SELECT id, date, title, description, href, start_at, end_at, color FROM perksin_events ORDER BY COALESCE(start_at, date) ASC, id ASC');
                $events = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {}
        }
        $pageLayout = $this->pageBuilder ? $this->pageBuilder->getLayout('events') : [];
        include __DIR__ . '/../Views/events.php';
    }
}
