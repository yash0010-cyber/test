<?php
/**
 * Application Entry Point
 * 
 * Main routing and controller dispatcher
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/../.env');
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
}

// Include configuration and setup
require_once __DIR__ . '/../config/constants.php';

// Start session
session_start();

// Include classes
require_once CONFIG_PATH . '/Database.php';
require_once APP_PATH . '/utils/Validator.php';
require_once APP_PATH . '/utils/Helper.php';
require_once APP_PATH . '/middleware/AuthMiddleware.php';

// Try to load Composer autoload if available
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Initialize database
$db = new Database();
$db->connect();

// Initialize auth middleware
$auth = new AuthMiddleware($db);
$auth->handle();

// Parse request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/index.php', '', $requestUri);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simple routing logic
$routes = [
    // Public routes
    'GET' => [
        '/' => 'pages/home',
        '/login' => 'auth/login',
        '/register' => 'auth/register',
        '/forgot-password' => 'auth/forgot-password',
        '/verify-email' => 'auth/verify-email',
        '/reset-password' => 'auth/reset-password',
        '/properties' => 'properties/list',
    ],
    'POST' => [
        '/login' => 'auth/login',
        '/register' => 'auth/register',
        '/forgot-password' => 'auth/forgot-password',
        '/reset-password' => 'auth/reset-password',
        '/logout' => 'auth/logout',
    ]
];

// Protected routes
if ($auth->isAuthenticated()) {
    $routes['GET']['/dashboard'] = 'dashboard/index';
    $routes['GET']['/profile'] = 'profile/index';
    $routes['POST']['/profile'] = 'profile/update';
    $routes['POST']['/logout'] = 'auth/logout';

    // Owner routes
    if ($auth->hasRole(ROLE_OWNER)) {
        $routes['GET']['/owner/properties'] = 'owner/properties';
        $routes['GET']['/owner/properties/create'] = 'owner/property-create';
        $routes['POST']['/owner/properties'] = 'owner/property-store';
        $routes['GET']['/owner/properties/:id/edit'] = 'owner/property-edit';
        $routes['POST']['/owner/properties/:id'] = 'owner/property-update';
        $routes['POST']['/owner/properties/:id/delete'] = 'owner/property-delete';
        $routes['GET']['/owner/applications'] = 'owner/applications';
    }

    // Tenant routes
    if ($auth->hasRole(ROLE_TENANT)) {
        $routes['GET']['/tenant/favorites'] = 'tenant/favorites';
        $routes['POST']['/tenant/favorites/:id'] = 'tenant/add-favorite';
        $routes['POST']['/tenant/apply'] = 'tenant/apply-property';
        $routes['GET']['/tenant/applications'] = 'tenant/applications';
        $routes['POST']['/tenant/rate'] = 'tenant/rate-property';
    }

    // Admin routes
    if ($auth->hasRole(ROLE_ADMIN)) {
        $routes['GET']['/admin/dashboard'] = 'admin/dashboard';
        $routes['GET']['/admin/users'] = 'admin/users';
        $routes['GET']['/admin/properties'] = 'admin/properties';
        $routes['GET']['/admin/ratings'] = 'admin/ratings';
        $routes['POST']['/admin/users/:id/status'] = 'admin/update-user-status';
    }
}

// Error routes
$routes['GET']['/error/unauthorized'] = 'error/unauthorized';
$routes['GET']['/error/not-found'] = 'error/not-found';
$routes['GET']['/error/server-error'] = 'error/server-error';

// Route matching
$route = $requestUri;
$controllerPath = null;

if (isset($routes[$requestMethod][$route])) {
    $controllerPath = $routes[$requestMethod][$route];
} else {
    // Try to find dynamic routes
    foreach ($routes[$requestMethod] ?? [] as $pattern => $controller) {
        if (strpos($pattern, ':') !== false) {
            $regex = preg_replace('/:[a-z_]+/', '([0-9]+)', $pattern);
            $regex = str_replace('/', '\/', $regex);
            
            if (preg_match('/^' . $regex . '$/', $route, $matches)) {
                $controllerPath = $controller;
                array_shift($matches); // Remove full match
                $_GET['params'] = $matches;
                break;
            }
        }
    }
}

// Load controller or show 404
if ($controllerPath) {
    $controllerFile = VIEWS_PATH . '/' . $controllerPath . '.php';
    
    if (file_exists($controllerFile)) {
        include $controllerFile;
    } else {
        http_response_code(500);
        include VIEWS_PATH . '/error/server-error.php';
    }
} else {
    http_response_code(404);
    include VIEWS_PATH . '/error/not-found.php';
}
