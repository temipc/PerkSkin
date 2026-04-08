<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\PageBuilderService;

class MissionsController
{
    private LocalizationService $i18n;
    private ?PageBuilderService $pageBuilder;

    public function __construct(LocalizationService $i18n, ?PageBuilderService $pageBuilder = null)
    {
        $this->i18n = $i18n;
        $this->pageBuilder = $pageBuilder;
    }

    public function index(): void
    {
        $title = $this->i18n->t('nav.missions');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        $pageLayout = $this->pageBuilder ? $this->pageBuilder->getLayout('missions') : [];
        include __DIR__ . '/../Views/missions.php';
    }
}
