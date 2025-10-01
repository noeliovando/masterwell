<?php
// Configurar BASE_PATH
$base_path = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_PATH', '/' . $base_path);

require_once 'includes/Auth.php';
Auth::logout();

// Redirigir a la página de inicio de sesión
header('Location: ' . BASE_PATH . '/login');
exit;
?>
