<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar los parámetros de la cookie de sesión antes de session_start()
// Esto asegura que la cookie de sesión sea válida para todo el BASE_PATH
$base_path_cookie = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$cookie_path = '/' . $base_path_cookie . '/';
session_set_cookie_params([
    'lifetime' => 0, // La cookie expira cuando se cierra el navegador
    'path' => $cookie_path,
    'domain' => '', // Deja vacío para el dominio actual
    'secure' => false, // Cambiar a true si usas HTTPS
    'httponly' => true,
    'samesite' => 'Lax' // 'Lax' o 'Strict' para protección CSRF
]);

session_start();

$base_path = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_PATH', '/' . $base_path); // Define BASE_PATH globally

require_once 'includes/Auth.php'; // Explicitly include Auth class
require_once 'db.php'; // Still needed for get_db_connection in models
require_once 'config.php'; // Ensure config is available globally

// Autoload de clases para evitar require_once manuales para cada clase
spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists(__DIR__ . '/controllers/' . $file)) {
        require_once __DIR__ . '/controllers/' . $file;
    } elseif (file_exists(__DIR__ . '/models/' . $file)) {
        require_once __DIR__ . '/models/' . $file;
    } elseif (file_exists(__DIR__ . '/includes/' . $file)) {
        require_once __DIR__ . '/includes/' . $file;
    }
});

// Simple Router
$request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if ($base_path && strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}
$request_uri = trim($request_uri, '/');

// Define routes
$routes = [
    '' => ['controller' => 'DashboardController', 'action' => 'index'],
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    'login' => ['controller' => 'AuthController', 'action' => 'login'],
    'auth' => ['controller' => 'AuthController', 'action' => 'authenticate'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'well' => ['controller' => 'WellController', 'action' => 'index'],
    'well/update' => ['controller' => 'WellController', 'action' => 'update'],
    'well/updateField' => ['controller' => 'WellController', 'action' => 'updateField'], // Nueva ruta para actualización de campo individual
    'well/search' => ['controller' => 'WellController', 'action' => 'search'], // Nueva ruta para búsqueda AJAX optimizada
    'well/testUpdatePermission' => ['controller' => 'WellController', 'action' => 'testUpdatePermissionView'], // Nueva ruta para la vista de prueba de permisos
    'sqlplus' => ['controller' => 'SqlPlusController', 'action' => 'index'], // Nueva ruta para la consola SQL
    'tables/details' => ['controller' => 'TableController', 'action' => 'details'],
    'explorer' => ['controller' => 'ExplorerController', 'action' => 'index'],
    'schema' => ['controller' => 'SchemaController', 'action' => 'index'],
];

// Debugging: Log the request URI and routes array AFTER definition
error_log("DEBUG: Request URI (after trim): '" . $request_uri . "'");
error_log("DEBUG: Routes array (after definition): " . print_r($routes, true));


$controllerName = 'DashboardController';
$actionName = 'index';
$uwi = null; // Reiniciar uwi para evitar conflictos

// Add dynamic route for well details
if (preg_match('/^well\/details\/(.+)$/', $request_uri, $matches)) {
    $controllerName = 'WellController';
    $actionName = 'details';
    $uwi = $matches[1];
} elseif (array_key_exists($request_uri, $routes)) {
    $controllerName = $routes[$request_uri]['controller'];
    $actionName = $routes[$request_uri]['action'];
} else {
    // Handle 404 or redirect to dashboard if not logged in
    if (!Auth::check()) {
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }
    header('Location: ' . BASE_PATH . '/dashboard');
    exit;
}

// Check authentication for protected routes
// Excluir 'well/updateField' de las rutas protegidas aquí, la autenticación se manejará dentro del método updateField.
$protected_routes = ['dashboard', 'well', 'well/details', 'tables/details', 'explorer'];
if (in_array($request_uri, $protected_routes) && !Auth::check()) {
    header('Location: ' . BASE_PATH . '/login');
    exit;
}


$controllerFile = 'controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    if (method_exists($controller, $actionName)) {
        if (isset($uwi)) {
            $controller->$actionName($uwi);
        } else {
            $controller->$actionName();
        }
    } else {
        // Action not found
        header('Location: ' . BASE_PATH . '/dashboard'); // Redirect to dashboard or 404
        exit;
    }
} else {
    // Controller not found
    header('Location: ' . BASE_PATH . '/dashboard'); // Redirect to dashboard or 404
    exit;
}
?>
