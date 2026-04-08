<?php
namespace App\Services;

class LocalizationService
{
    private array $translations = [];
    private string $defaultLocale = 'en';
    private string $currentLocale;
    private ?Database $db = null;

    public function __construct(string $basePath, string $defaultLocale = 'en', ?Database $db = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->currentLocale = $this->detectLocale();
        $this->db = $db;
        $this->loadTranslations($basePath);
    }

    private function detectLocale(): string
    {
        // Priority: query ?lang=xx > cookie > default
        $lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? $this->defaultLocale);
        $lang = strtolower(preg_replace('/[^a-z]/i', '', $lang));
        if (!in_array($lang, ['en', 'hu'])) {
            $lang = $this->defaultLocale;
        }
        // persist in cookie for 30 days
        setcookie('lang', $lang, time() + 60 * 60 * 24 * 30, '/');
        return $lang;
    }

    private function loadTranslations(string $basePath): void
    {
        $locales = ['en', 'hu'];
        foreach ($locales as $locale) {
            $this->translations[$locale] = $this->loadLocaleTranslations($basePath, $locale);
        }
    }

    private function loadLocaleTranslations(string $basePath, string $locale): array
    {
        $translations = [];
        $file = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale . '.php';
        if (is_file($file)) {
            $fileArr = include $file;
            if (is_array($fileArr)) {
                $translations = $fileArr;
            }
        }
        if ($this->db) {
            try {
                $stmt = $this->db->pdo()->prepare('SELECT `key`, `value` FROM perksin_translations_kv WHERE locale = ?');
                $stmt->execute([$locale]);
                while ($row = $stmt->fetch()) {
                    $translations[$row['key']] = $row['value'];
                }
            } catch (\Throwable $e) { /* ignore db errors, keep file defaults */ }
        }
        return $translations;
    }

    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    public function t(string $key, array $replacements = []): string
    {
        $value = $this->translations[$this->currentLocale][$key]
            ?? $this->translations[$this->defaultLocale][$key]
            ?? $key;

        foreach ($replacements as $k => $v) {
            $value = str_replace('{' . $k . '}', (string)$v, $value);
        }
        return $value;
    }

    public function all(string $locale): array
    {
        $locale = strtolower(trim($locale));
        if (!in_array($locale, ['en', 'hu'], true)) {
            return [];
        }
        return $this->loadLocaleTranslations(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Translations', $locale);
    }
}
