<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\Database;
use App\Services\PageBuilderService;

class DashboardController
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
        if (empty($_SESSION['user_id'])) {
            header('Location: /index.php?page=login');
            exit;
        }
        $title = $this->i18n->t('app.title');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        // Embed base translations so Languages tab has data immediately
        $baseTranslations = [
            'en' => $this->i18n->all('en'),
            'hu' => $this->i18n->all('hu'),
        ];
        $builderPages = $this->pageBuilder ? $this->pageBuilder->listPageConfigs() : [];
        $builderModules = $this->pageBuilder ? array_values($this->pageBuilder->getModuleRegistry()) : [];
        include __DIR__ . '/../Views/dashboard.php';
    }
}
