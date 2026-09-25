<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

// Apache serves real assets before rewriting. This gives PHP's optional local server the same behavior.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicPath = realpath(__DIR__);
    $requestedFile = realpath(__DIR__ . $requestPath);

    if ($publicPath !== false
        && $requestedFile !== false
        && str_starts_with($requestedFile, $publicPath . DIRECTORY_SEPARATOR)
        && is_file($requestedFile)
    ) {
        return false;
    }
}

$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$baseUrl = in_array($scriptDirectory, ['/', '.'], true) ? '' : rtrim($scriptDirectory, '/');
define('BASE_URL', $baseUrl);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$applicationTimezone = getenv('APP_TIMEZONE') ?: 'Africa/Khartoum';
if (!in_array($applicationTimezone, timezone_identifiers_list(), true)) {
    $applicationTimezone = 'UTC';
}
date_default_timezone_set($applicationTimezone);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => BASE_URL === '' ? '/' : BASE_URL,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function autoloadClass(string $className): void
{
    if (preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $className) !== 1) {
        return;
    }

    foreach (['config', 'core', 'controllers', 'models'] as $directory) {
        $candidateFiles = [
            ROOT_PATH . '/' . $directory . '/' . $className . '.php',
            ROOT_PATH . '/' . $directory . '/' . lcfirst($className) . '.php',
        ];

        foreach ($candidateFiles as $file) {
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
}

function renderErrorPage(int $statusCode, string $viewName, string $pageTitle): void
{
    http_response_code($statusCode);
    require ROOT_PATH . '/views/layout/header.php';
    require ROOT_PATH . '/views/errors/' . $viewName . '.php';
    require ROOT_PATH . '/views/layout/footer.php';
}

spl_autoload_register('autoloadClass');
require_once ROOT_PATH . '/core/helpers.php';

$router = new Router(BASE_URL);

$router->get('/', [PhotoController::class, 'home']);
$router->get('/about', [PhotoController::class, 'about']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/photos', [PhotoController::class, 'index']);
$router->get('/photos/create', [PhotoController::class, 'create']);
$router->post('/photo/store', [PhotoController::class, 'store']);
$router->get('/photo/{id}', [PhotoController::class, 'show']);
$router->post('/photo/{id}/delete', [PhotoController::class, 'delete']);
$router->post('/photo/{id}/comments', [CommentController::class, 'store']);
$router->setNotFoundHandler('renderNotFoundPage');

function renderNotFoundPage(): void
{
    renderErrorPage(404, '404', 'Page not found');
}

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $exception) {
    error_log((string) $exception);
    renderErrorPage(500, '500', 'Application error');
}
