<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\PageBuilderService;

class BattlesController
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
        $title = $this->i18n->t('nav.battles');
        $locale = $this->i18n->getLocale();
        $t = fn(string $k, array $r = []) => $this->i18n->t($k, $r);
        $pageLayout = $this->pageBuilder ? $this->pageBuilder->getLayout('battles') : [];
        $battleRooms = [
            ['name' => 'Rookie Rush', 'status' => 'Planned', 'players' => '2v2', 'entry' => '$2.99'],
            ['name' => 'Neon Clash', 'status' => 'Coming soon', 'players' => '4 players', 'entry' => '$7.49'],
            ['name' => 'Vault Royale', 'status' => 'Prototype', 'players' => '8 players', 'entry' => '$12.99'],
        ];
        include __DIR__ . '/../Views/battles.php';
    }
}
