<?php

function get_db_connection() {
    // Verificar si las credenciales están en la sesión
    if (!isset($_SESSION['db_credentials'])) {
        // Si no hay credenciales, no se puede conectar.
        // El header.php ya debería haber redirigido al login.
        return null;
    }

    $credentials = $_SESSION['db_credentials'];
    $username = $credentials['user'];
    $password = $credentials['pass'];
    $db_instance = $credentials['db_instance'];

require_once 'config.php';
global $db_instances;

    if (!array_key_exists($db_instance, $db_instances)) {
        return null;
    }

    $dsn = $db_instances[$db_instance];
    $options = DB_OPTIONS;

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En un contexto AJAX, no redirigir ni destruir la sesión directamente aquí.
        // Simplemente registrar el error y devolver null.
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return null;
    }
}

// Función para obtener el esquema principal basado en la instancia actual
function get_main_schema() {
    if (!isset($_SESSION['db_credentials'])) {
        return 'PDVSA'; // Esquema por defecto
    }
    
    $db_instance = $_SESSION['db_credentials']['db_instance'];
    
    require_once 'config.php';
    global $db_schemas;
    
    if (array_key_exists($db_instance, $db_schemas)) {
        return $db_schemas[$db_instance]['main_schema'];
    }
    
    return 'PDVSA'; // Esquema por defecto
}

// Función para obtener el esquema de códigos basado en la instancia actual
function get_codes_schema() {
    if (!isset($_SESSION['db_credentials'])) {
        return 'CODES'; // Esquema por defecto
    }
    
    $db_instance = $_SESSION['db_credentials']['db_instance'];
    
    require_once 'config.php';
    global $db_schemas;
    
    if (array_key_exists($db_instance, $db_schemas)) {
        return $db_schemas[$db_instance]['codes_schema'];
    }
    
    return 'CODES'; // Esquema por defecto
}

// Función helper para construir nombres de tabla dinámicamente
function get_table_name($table_name, $schema_type = 'main') {
    if ($schema_type === 'main') {
        $schema = get_main_schema();
    } else {
        $schema = get_codes_schema();
    }
    
    return $schema . '.' . $table_name;
}

// Función para verificar si una tabla existe en la instancia actual
function table_exists($table_name) {
    if (!isset($_SESSION['db_credentials'])) {
        return true; // Por defecto asumimos que existe
    }
    
    $db_instance = $_SESSION['db_credentials']['db_instance'];
    
    require_once 'config.php';
    global $db_schemas;
    
    if (array_key_exists($db_instance, $db_schemas) && 
        isset($db_schemas[$db_instance]['available_tables'])) {
        return in_array($table_name, $db_schemas[$db_instance]['available_tables']);
    }
    
    return true; // Por defecto asumimos que existe si no está configurado
}
?>
