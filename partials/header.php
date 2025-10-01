<?php
// Definir BASE_PATH si no está definido
if (!defined('BASE_PATH')) {
    $base_path = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    define('BASE_PATH', '/' . $base_path);
}

require_once __DIR__ . '/../includes/Auth.php'; // Ensure Auth class is available

// Proteger la página: si el usuario no ha iniciado sesión, redirigirlo a login.php
if (!Auth::check()) {
    header('Location: ' . BASE_PATH . '/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>App de Pozos</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav>
        <div class="logo-container">
            <img src="<?php echo BASE_PATH; ?>/images/company-logo.png" alt="Logo de la Empresa" class="company-logo">
        </div>
        <ul>
            <li><a href="<?php echo BASE_PATH; ?>/dashboard">Inicio</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/well">Pozo</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/tables/details">Detalles de Tablas</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/explorer">Explorador de DB</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/schema">Esquema WELL_HDR</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/sqlplus">Consola SQL</a></li>
            <li><a href="<?php echo BASE_PATH; ?>/logout">Cerrar Sesión</a></li>
            <?php if (isset($_SESSION['db_credentials']['db_instance'])): ?>
                <li style="float:right; color: white; padding: 14px 16px;">
                    Instancia: <?php echo htmlspecialchars($_SESSION['db_credentials']['db_instance']); ?>
                </li>
            <?php endif; ?>
        </ul>
        <div class="logo-container">
            <img src="<?php echo BASE_PATH; ?>/images/logo-mw-fondoblanco.png" alt="Logo de la Empresa" class="company-logo-mw">
        </div>
    </nav>
    <div class="main-container">
