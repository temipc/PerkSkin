<?php
namespace App\Controllers;

use App\Services\LocalizationService;
use App\Services\Database;
use App\Services\TwoFactorService;

class AuthController
{
    private LocalizationService $i18n;
    private ?Database $db;
    private ?TwoFactorService $twoFactor;

    public function __construct(LocalizationService $i18n, ?Database $db)
    {
        $this->i18n = $i18n;
        $this->db = $db;
        $this->twoFactor = $db ? new TwoFactorService($db) : null;
        $this->twoFactor?->ensureSchema();
    }

    private function redirect(string $url): void { header('Location: ' . $url); exit; }
    private function isPost(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
    private function post(string $k, string $def=''): string { return trim((string)($_POST[$k] ?? $def)); }
    
    private function parseJson(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function completeLogin(array $user): void
    {
        unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_email']);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = (int)($user['is_admin'] ?? 0);
        $this->createSessionRecord((int)$user['id']);
    }

    private function requiresTwoFactor(array $user): bool
    {
        return $this->twoFactorAllowed($user) && !empty($user['two_factor_enabled']) && !empty($user['two_factor_secret']);
    }

    private function twoFactorAllowed(array $user): bool
    {
        return strtolower((string)($user['email'] ?? '')) !== 'admin@admin.com';
    }

    private function beginTwoFactorChallenge(array $user): void
    {
        $_SESSION['pending_2fa_user_id'] = (int)$user['id'];
        $_SESSION['pending_2fa_email'] = (string)($user['email'] ?? '');
    }

    private function clearTwoFactorChallenge(): void
    {
        unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_email']);
    }

    private function pendingTwoFactorUser(): ?array
    {
        if (!$this->db) {
            return null;
        }

        $userId = (int)($_SESSION['pending_2fa_user_id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        $stmt = $this->db->pdo()->prepare('SELECT * FROM perksin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !$this->requiresTwoFactor($user)) {
            $this->clearTwoFactorChallenge();
            return null;
        }

        return $user;
    }

    private function verifyTwoFactorChallenge(string $code): bool
    {
        $user = $this->pendingTwoFactorUser();
        if (!$user || !$this->twoFactor) {
            return false;
        }

        if (!$this->twoFactor->verifyCode((string)($user['two_factor_secret'] ?? ''), $code)) {
            return false;
        }

        $this->completeLogin($user);
        return true;
    }

    private function createSessionRecord(?int $userId): void
    {
        if (!$this->db) return;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $this->db->pdo()->prepare('INSERT INTO perksin_user_sessions (user_id, start_at, ip, active) VALUES (?,?,?,1)');
        $stmt->execute([$userId, date('Y-m-d H:i:s'), $ip]);
        $_SESSION['session_row_id'] = (int)$this->db->pdo()->lastInsertId();
    }

    private function closeSessionRecord(): void
    {
        if (!$this->db) return;
        $rowId = (int)($_SESSION['session_row_id'] ?? 0);
        if ($rowId > 0) {
            $stmt = $this->db->pdo()->prepare('UPDATE perksin_user_sessions SET end_at=?, active=0 WHERE id=?');
            $stmt->execute([date('Y-m-d H:i:s'), $rowId]);
        }
        unset($_SESSION['session_row_id']);
    }

    // These handlers are intended to be used as API endpoints via public/index.php?page=api&action=...
    public function handleLoginJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->db || !$this->isPost()) { http_response_code(400); echo json_encode(['error'=>'bad_request']); return; }
        // Accept both JSON and form-urlencoded payloads
        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (strpos($contentType, 'application/json') !== false) {
            $data = $this->parseJson();
        } else {
            // PHP auto-populates $_POST for application/x-www-form-urlencoded
            $data = $_POST;
        }
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        if (!$email || !$password) { http_response_code(400); echo json_encode(['error'=>'required_fields']); return; }
        $stmt = $this->db->pdo()->prepare('SELECT * FROM perksin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'] ?? '')) {
            if ($this->requiresTwoFactor($user)) {
                $this->beginTwoFactorChallenge($user);
                echo json_encode(['ok'=>true, 'requires_2fa'=>true]);
                return;
            }

            $this->completeLogin($user);
            echo json_encode(['ok'=>true, 'requires_2fa'=>false]);
        } else {
            http_response_code(401); echo json_encode(['error'=>'invalid_credentials']);
        }
    }

    public function handleVerifyTwoFactorJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->db || !$this->isPost()) { http_response_code(400); echo json_encode(['error'=>'bad_request']); return; }

        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        $data = strpos($contentType, 'application/json') !== false ? $this->parseJson() : $_POST;
        $code = trim((string)($data['code'] ?? ''));

        if ($code === '') {
            http_response_code(400);
            echo json_encode(['error'=>'required_code']);
            return;
        }

        if (!$this->pendingTwoFactorUser()) {
            http_response_code(401);
            echo json_encode(['error'=>'two_factor_not_pending']);
            return;
        }

        if (!$this->verifyTwoFactorChallenge($code)) {
            http_response_code(401);
            echo json_encode(['error'=>'invalid_two_factor_code']);
            return;
        }

        echo json_encode(['ok'=>true]);
    }

    public function handleRegisterJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->db || !$this->isPost()) { http_response_code(400); echo json_encode(['error'=>'bad_request']); return; }
        // Accept both JSON and form-urlencoded payloads
        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (strpos($contentType, 'application/json') !== false) {
            $data = $this->parseJson();
        } else {
            $data = $_POST;
        }
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $display = trim((string)($data['display_name'] ?? 'Player'));
        if (!$email || !$password) { http_response_code(400); echo json_encode(['error'=>'required_fields']); return; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['error'=>'invalid_email']); return; }
        if (strlen($password) < 6) { http_response_code(400); echo json_encode(['error'=>'weak_password']); return; }
        $exists = $this->db->pdo()->prepare('SELECT id FROM perksin_users WHERE email=?');
        $exists->execute([$email]);
        if ($exists->fetchColumn()) { http_response_code(409); echo json_encode(['error'=>'email_exists']); return; }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->pdo()->prepare('INSERT INTO perksin_users (email, password_hash, display_name, locale, is_admin) VALUES (?,?,?,?,0)');
        $stmt->execute([$email, $hash, $display, $this->i18n->getLocale()]);
        $_SESSION['user_id'] = (int)$this->db->pdo()->lastInsertId();
        $_SESSION['user_email'] = $email;
        $_SESSION['is_admin'] = 0;
        $this->createSessionRecord((int)$_SESSION['user_id']);
        echo json_encode(['ok'=>true]);
    }

    public function logout(): void
    {
        $this->closeSessionRecord();
        session_unset();
        session_destroy();
        $this->redirect('/index.php?page=home&logged_out=1');
    }

    // Show login page or process login POST
    public function login(): void
    {
        $error = null;
        $locale = $this->i18n->getLocale();
        $t = fn(string $key) => $this->i18n->t($key);
        $twoFactorPending = $this->pendingTwoFactorUser() !== null;
        $twoFactorEmail = (string)($_SESSION['pending_2fa_email'] ?? '');

        // Handle POST request
        if ($this->isPost()) {
            if (!$this->db) {
                $error = 'Database connection required';
            } else {
                if ($twoFactorPending || $this->post('two_factor_step') === '1') {
                    $code = $this->post('otp_code');
                    if ($code === '') {
                        $error = $t('auth.twoFactorCodeRequired');
                    } elseif ($this->verifyTwoFactorChallenge($code)) {
                        $this->redirect('/index.php?page=home');
                    } else {
                        $error = $t('auth.invalidTwoFactorCode');
                    }
                    $twoFactorPending = $this->pendingTwoFactorUser() !== null;
                    $twoFactorEmail = (string)($_SESSION['pending_2fa_email'] ?? '');
                } else {
                    $email = strtolower($this->post('email'));
                    $password = $this->post('password');

                    if (empty($email) || empty($password)) {
                        $error = $t('auth.requiredFields');
                    } else {
                        $stmt = $this->db->pdo()->prepare('SELECT * FROM perksin_users WHERE email = ? LIMIT 1');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();

                        if ($user && password_verify($password, $user['password_hash'] ?? '')) {
                            if ($this->requiresTwoFactor($user)) {
                                $this->beginTwoFactorChallenge($user);
                                $twoFactorPending = true;
                                $twoFactorEmail = (string)$user['email'];
                            } else {
                                $this->completeLogin($user);
                                $this->redirect('/index.php?page=home');
                            }
                        } else {
                            $error = $t('auth.invalidCredentials');
                        }
                    }
                }
            }
        }

        // Show login page
        include dirname(__DIR__) . '/Views/auth/login.php';
    }

    // Show register page or process registration POST
    public function register(): void
    {
        $error = null;
        $locale = $this->i18n->getLocale();
        $t = fn(string $key) => $this->i18n->t($key);

        // Handle POST request
        if ($this->isPost()) {
            if (!$this->db) {
                $error = 'Database connection required';
            } else {
                $email = strtolower($this->post('email'));
                $password = $this->post('password');
                $display = $this->post('display_name') ?: 'Player';

                if (empty($email) || empty($password)) {
                    $error = $t('auth.requiredFields');
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = $t('auth.invalidEmail');
                } elseif (strlen($password) < 6) {
                    $error = $t('auth.weakPassword');
                } else {
                    $exists = $this->db->pdo()->prepare('SELECT id FROM perksin_users WHERE email = ?');
                    $exists->execute([$email]);
                    if ($exists->fetchColumn()) {
                        $error = $t('auth.emailExists');
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $this->db->pdo()->prepare('INSERT INTO perksin_users (email, password_hash, display_name, locale, is_admin) VALUES (?, ?, ?, ?, 0)');
                        $stmt->execute([$email, $hash, $display, $locale]);
                        $_SESSION['user_id'] = (int)$this->db->pdo()->lastInsertId();
                        $_SESSION['user_email'] = $email;
                        $_SESSION['is_admin'] = 0;
                        $this->createSessionRecord((int)$_SESSION['user_id']);
                        $this->redirect('/index.php?page=home');
                    }
                }
            }
        }

        // Show login page (same template as login, will show register form)
        include dirname(__DIR__) . '/Views/auth/login.php';
    }
}
