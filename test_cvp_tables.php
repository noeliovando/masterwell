<?php
// test_cvp_tables.php
// Archivo de prueba para verificar qué tablas existen en CVP

session_start();
require_once 'db.php';

echo "<h1>🔍 Verificación de Tablas en CVP</h1>";

// Simular conexión con CVP
$_SESSION['db_credentials'] = [
    'db_instance' => 'CVP - Empresas Mixtas',
    'user' => 'test_user', // Cambiar por usuario real
    'pass' => 'test_pass'  // Cambiar por contraseña real
];

$tables_to_check = [
    'WELL_HDR',
    'NODES_SECOND', 
    'WELL_REMARKS',
    'WELL_ALIAS',
    'FIELD_HDR',
    'BUSINESS_ASSOC',
    'LEASE'
];

echo "<h2>📋 Verificando existencia de tablas en FINDCVP:</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Tabla</th><th>Esquema Completo</th><th>Estado</th><th>Acción</th></tr>";

$pdo = get_db_connection();
if ($pdo) {
    foreach ($tables_to_check as $table) {
        $full_table = "FINDCVP." . $table;
        $exists = false;
        
        try {
            // Intentar hacer una consulta simple para verificar si la tabla existe
            $sql = "SELECT COUNT(*) FROM {$full_table} WHERE ROWNUM <= 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $exists = true;
            $status = "✅ Existe";
            $action = "Consultas habilitadas";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'ORA-00942') !== false) {
                $status = "❌ No existe";
                $action = "Usar valores por defecto";
            } else {
                $status = "⚠️ Error: " . $e->getMessage();
                $action = "Verificar manualmente";
            }
        }
        
        echo "<tr>";
        echo "<td>{$table}</td>";
        echo "<td>{$full_table}</td>";
        echo "<td>{$status}</td>";
        echo "<td>{$action}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>❌ No se pudo conectar a la base de datos</td></tr>";
}

echo "</table>";

echo "<h2>🔧 Configuración Recomendada para CVP:</h2>";
echo "<p>Basándose en los resultados anteriores, actualiza la configuración en <code>config.php</code>:</p>";
echo "<pre>";
echo "'CVP - Empresas Mixtas' => [\n";
echo "    'main_schema' => 'FINDCVP',\n";
echo "    'codes_schema' => 'CODES',\n";
echo "    'available_tables' => [";

// Esta será la lista que debemos actualizar basándose en los resultados
$existing_tables = [];
if ($pdo) {
    foreach ($tables_to_check as $table) {
        $full_table = "FINDCVP." . $table;
        try {
            $sql = "SELECT COUNT(*) FROM {$full_table} WHERE ROWNUM <= 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $existing_tables[] = "'{$table}'";
        } catch (PDOException $e) {
            // Tabla no existe, no la incluimos
        }
    }
}

echo implode(', ', $existing_tables);
echo "]\n";
echo "],\n";
echo "</pre>";

echo "<h2>🧪 Prueba de Consulta de Pozo:</h2>";
if ($pdo) {
    try {
        $sql = "SELECT UWI, WELL_NAME, SHORT_NAME FROM FINDCVP.WELL_HDR WHERE ROWNUM <= 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        if ($results) {
            echo "<p><strong>✅ Consulta exitosa. Pozos encontrados:</strong></p>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>UWI</th><th>Nombre del Pozo</th><th>Nombre Corto</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['UWI']) . "</td>";
                echo "<td>" . htmlspecialchars($row['WELL_NAME'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['SHORT_NAME'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ No se encontraron pozos en la tabla.</p>";
        }
    } catch (PDOException $e) {
        echo "<p><strong>❌ Error en consulta de pozos:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<h2>📝 Próximos Pasos:</h2>";
echo "<ol>";
echo "<li>Ejecutar este script con credenciales reales de CVP</li>";
echo "<li>Actualizar la configuración en <code>config.php</code> con las tablas que existen</li>";
echo "<li>Probar los detalles del pozo nuevamente</li>";
echo "<li>Verificar que no hay más errores ORA-00942</li>";
echo "</ol>";

// Limpiar sesión de prueba
unset($_SESSION['db_credentials']);
?>