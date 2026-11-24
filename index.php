<?php
// Show errors only in local/dev — disable on production
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'], true)
        || str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.test')
        || str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.local');

if ($isLocal) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

define('APP_ROOT', dirname(__DIR__));

// Base URL of the app (e.g. /resqnow), derived from the front controller's location.
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$publicDir  = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseUrl    = rtrim(str_replace('\\', '/', dirname($publicDir)), '/');
define('BASE_URL', $baseUrl === '' ? '' : $baseUrl);

require APP_ROOT . '/app/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));

    // Map namespace segments to actual filesystem directories.
    // On Windows (case-insensitive) this doesn't matter.
    // On Linux (case-sensitive) the directories are lowercase,
    // so we convert each namespace directory segment to lowercase,
    // while keeping the final class filename in its original casing.
    $parts     = explode('\\', $relative);
    $className = array_pop($parts);           // e.g. "Database", "Router"
    $dirs      = array_map('strtolower', $parts); // e.g. ["core"], ["controllers","api"]
    $file      = $baseDir . (empty($dirs) ? '' : implode('/', $dirs) . '/') . $className . '.php';

    if (file_exists($file)) {
        require $file;
        return;
    }

    // Fallback: try with original casing (in case some directory is mixed-case on the host)
    $fileFallback = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($fileFallback)) {
        require $fileFallback;
    }
});

$config = require APP_ROOT . '/app/config.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

session_name($config['session']['name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

$db = App\Core\Database::getInstance($config['db']);

$router = new App\Core\Router($db);

require APP_ROOT . '/app/routes.php';

$url = '/' . ltrim($_GET['url'] ?? '', '/');
$router->dispatch($url, $_SERVER['REQUEST_METHOD']);
