<?php
namespace App\Services;

use PDO; use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(array $cfg)
    {
        // Support both SQLite and MySQL
        if (isset($cfg['driver']) && $cfg['driver'] === 'sqlite') {
            // SQLite configuration - normalize path
            $dbPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cfg['database']);
            $dbPath = realpath(dirname($dbPath)) . DIRECTORY_SEPARATOR . basename($dbPath);
            $dsn = 'sqlite:' . $dbPath;
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, null, null, $opts);
            $this->pdo->exec("PRAGMA foreign_keys = ON");
        } else {
            // MySQL configuration
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $cfg['host'], $cfg['db'], $cfg['charset'] ?? 'utf8mb4');
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $opts);
        }
    }

    public function pdo(): PDO { return $this->pdo; }
}
