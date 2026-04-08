<?php
namespace App\Services;

use PDO;

class TwoFactorService
{
    private const ISSUER = 'PerkSpin';
    private const SECRET_LENGTH = 32;
    private const TIME_STEP = 30;
    private const DIGITS = 6;

    private Database $db;
    private bool $schemaEnsured = false;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function ensureSchema(): void
    {
        if ($this->schemaEnsured) {
            return;
        }

        $pdo = $this->db->pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $columns = $this->listColumns($pdo, 'perksin_users', $driver);

        $required = [
            'two_factor_enabled' => $driver === 'sqlite'
                ? 'ALTER TABLE perksin_users ADD COLUMN two_factor_enabled INTEGER NOT NULL DEFAULT 0'
                : 'ALTER TABLE perksin_users ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0',
            'two_factor_secret' => 'ALTER TABLE perksin_users ADD COLUMN two_factor_secret VARCHAR(64) NULL',
            'two_factor_pending_secret' => 'ALTER TABLE perksin_users ADD COLUMN two_factor_pending_secret VARCHAR(64) NULL',
            'two_factor_confirmed_at' => 'ALTER TABLE perksin_users ADD COLUMN two_factor_confirmed_at DATETIME NULL',
        ];

        foreach ($required as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                $pdo->exec($sql);
            }
        }

        $this->schemaEnsured = true;
    }

    public function generateSecret(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bytes = random_bytes(self::SECRET_LENGTH);
        $secret = '';
        for ($i = 0; $i < self::SECRET_LENGTH; $i++) {
            $secret .= $alphabet[ord($bytes[$i]) % 32];
        }
        return $secret;
    }

    public function buildOtpAuthUri(string $email, ?string $secret = null): string
    {
        $secret = $secret ?: $this->generateSecret();
        $label = rawurlencode(self::ISSUER . ':' . $email);
        $issuer = rawurlencode(self::ISSUER);
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=" . self::DIGITS . '&period=' . self::TIME_STEP;
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D+/', '', $code);
        if ($secret === '' || strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = (int) floor(time() / self::TIME_STEP);
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->generateTotpCode($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    private function generateTotpCode(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0f;
        $chunk = substr($hash, $offset, 4);
        $value = unpack('N', $chunk)[1] & 0x7fffffff;
        $mod = 10 ** self::DIGITS;
        return str_pad((string) ($value % $mod), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
        $bits = '';

        $length = strlen($secret);
        for ($i = 0; $i < $length; $i++) {
            $index = strpos($alphabet, $secret[$i]);
            if ($index === false) {
                continue;
            }
            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $bitLength = strlen($bits);
        for ($i = 0; $i + 8 <= $bitLength; $i += 8) {
            $bytes .= chr(bindec(substr($bits, $i, 8)));
        }

        return $bytes;
    }

    private function listColumns(PDO $pdo, string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = $pdo->query("PRAGMA table_info({$table})")->fetchAll();
            return array_map(static fn(array $row) => (string) ($row['name'] ?? ''), $rows ?: []);
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM {$table}");
        $rows = $stmt->fetchAll();
        return array_map(static fn(array $row) => (string) ($row['Field'] ?? ''), $rows ?: []);
    }
}
