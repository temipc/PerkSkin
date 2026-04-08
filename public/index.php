<?php
// Simple front controller for the demo MVP
declare(strict_types=1);

// Start PHP session for auth
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

spl_autoload_register(function ($class) {
	$prefix = 'App\\';
	$baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
	if (str_starts_with($class, $prefix)) {
		$relative = substr($class, strlen($prefix));
		$relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
		$path = $baseDir . 'app' . DIRECTORY_SEPARATOR . $relativePath;
		if (is_file($path)) require $path;
	}
});

use App\Services\LocalizationService;
use App\Services\Database;
use App\Services\PageBuilderService;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CaseController;
use App\Controllers\DashboardController;
use App\Controllers\ExchangeController;
use App\Controllers\EventsController;
use App\Controllers\MissionsController;
use App\Controllers\BattlesController;
use App\Controllers\ApiController;

// DB connection (optional if DB not yet provisioned)
$dbCfgPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'db.php';
$db = null;
if (is_file($dbCfgPath)) {
	$cfg = require $dbCfgPath;
	try { 
		$db = new Database($cfg); 
	} catch (\Throwable $e) { 
		// Silently fail but could log: error_log($e->getMessage());
		$db = null; 
	}
}

$i18n = new LocalizationService(
	dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Translations',
	'en',
	$db
);

// Expose translation helper globally so views using ___() can fallback to server-side translations
$GLOBALS['t'] = function(string $k, array $r = []) use ($i18n) { return $i18n->t($k, $r); };
$GLOBALS['locale'] = $i18n->getLocale();
// Compute base path so app can run from a subdirectory (e.g. /demo/perkspin)
$basePath = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($basePath === '/') $basePath = '';
$GLOBALS['basePath'] = $basePath;
// Asset helper: returns a path prefixed with basePath when appropriate
$GLOBALS['asset'] = function(string $p) use ($basePath) {
	if (!is_string($p)) return $p;
	// If path already absolute (starts with /) prefix with basePath
	if (strlen($p)>0 && $p[0] === '/') return ($basePath ?: '') . $p;
	return ($basePath ?: '') . '/' . ltrim($p, '/');
};

$pageBuilder = new PageBuilderService($db);
$GLOBALS['pageBuilder'] = $pageBuilder;
$GLOBALS['navigationItems'] = $pageBuilder->getNavigationItems($GLOBALS['t']);

// simple router
$page = $_GET['page'] ?? 'home';
$pageAccessGuarded = ['home', 'events', 'missions', 'exchange', 'dashboard', 'battles'];
if (in_array($page, $pageAccessGuarded, true) && !$pageBuilder->isPageEnabled($page)) {
	http_response_code(404);
	echo 'Page not available.';
	exit;
}
switch ($page) {
	case 'login':
		$controller = new AuthController($i18n, $db);
		$controller->login();
		break;
	case 'register':
		$controller = new AuthController($i18n, $db);
		$controller->register();
		break;
	case 'logout':
		$controller = new AuthController($i18n, $db);
		$controller->logout();
		break;
	case 'case':
		$controller = new CaseController($i18n, $db);
		$controller->show($_GET['slug'] ?? '');
		break;
	case 'dashboard':
		$controller = new DashboardController($i18n, $db, $pageBuilder);
		$controller->index();
		break;
	case 'exchange':
		$controller = new ExchangeController($i18n, $pageBuilder);
		$controller->index();
		break;
	case 'events':
		$controller = new EventsController($i18n, $db, $pageBuilder);
		$controller->index();
		break;
	case 'missions':
		$controller = new MissionsController($i18n, $pageBuilder);
		$controller->index();
		break;
	case 'battles':
		$controller = new BattlesController($i18n, $pageBuilder);
		$controller->index();
		break;
	case 'api':
			$controller = new ApiController($i18n, $db);
			$controller->handle();
			break;
	case 'home':
	default:
		$controller = new HomeController($i18n, $db, $pageBuilder);
		$controller->index();
}
