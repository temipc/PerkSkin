<?php
namespace App\Services;

use App\Services\Database;

class SeedTranslations
{
    public static function ensureCore(Database $db, array $keys): void
    {
        $pdo = $db->pdo();
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $sql = $driver === 'sqlite'
            ? 'INSERT INTO perksin_translations_kv (`key`, locale, value) VALUES (:k,:l,:v) ON CONFLICT(`key`, locale) DO UPDATE SET value=excluded.value'
            : 'INSERT INTO perksin_translations_kv (`key`, locale, value) VALUES (:k,:l,:v) ON DUPLICATE KEY UPDATE value=VALUES(value)';
        $stmt = $pdo->prepare($sql);
        foreach ($keys as $key => $pair) {
            if (!is_array($pair)) continue;
            if (isset($pair['en'])) { $stmt->execute([':k'=>$key, ':l'=>'en', ':v'=>$pair['en']]); }
            if (isset($pair['hu'])) { $stmt->execute([':k'=>$key, ':l'=>'hu', ':v'=>$pair['hu']]); }
        }
    }
}
