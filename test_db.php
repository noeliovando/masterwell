<?php
// test_db.php - Archivo de prueba para verificar la conectividad de la base de datos
// Compatible con Oracle 7
require_once 'db.php';

echo "<h1>Prueba de Conectividad de Base de Datos - Oracle 7</h1>";

try {
    $pdo = get_db_connection();
    if ($pdo) {
        echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos</p>";
        
        // Probar consulta simple
        $test_sql = "SELECT COUNT(*) as total FROM PDVSA.WELL_HDR";
        $stmt = $pdo->prepare($test_sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "<p>Total de pozos en la base de datos: " . $result['total'] . "</p>";
        
        // Probar búsqueda de un pozo específico
        $test_uwi = "123456789012345"; // UWI de ejemplo
        $search_sql = "SELECT UWI, WELL_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
        $stmt = $pdo->prepare($search_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $well = $stmt->fetch();
        
        if ($well) {
            echo "<p>✓ Pozo encontrado: " . $well['UWI'] . " - " . $well['WELL_NAME'] . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontró el pozo de prueba. Esto es normal si el UWI no existe.</p>";
        }
        
        // Probar la consulta optimizada (compatible con Oracle 7)
        echo "<h2>Probando consulta optimizada (Oracle 7)...</h2>";
        require_once 'models/Well.php';
        
        $test_result = Well::searchWellsOptimized("123");
        if (is_array($test_result) && !isset($test_result['error'])) {
            echo "<p style='color: green;'>✓ Búsqueda optimizada funciona correctamente</p>";
            echo "<p>Resultados encontrados: " . count($test_result) . "</p>";
            
            // Mostrar algunos resultados de ejemplo
            if (count($test_result) > 0) {
                echo "<h3>Primeros 3 resultados:</h3>";
                echo "<ul>";
                for ($i = 0; $i < min(3, count($test_result)); $i++) {
                    $well = $test_result[$i];
                    echo "<li>" . $well['UWI'] . " - " . $well['WELL_NAME'] . "</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error en búsqueda optimizada: " . ($test_result['error'] ?? 'Error desconocido') . "</p>";
        }
        
        // Probar detalles de un pozo (si existe)
        echo "<h2>Probando obtención de detalles del pozo...</h2>";
        if (count($test_result) > 0) {
            $first_well_uwi = $test_result[0]['UWI'];
            $details_result = Well::getWellDetailsByUWI($first_well_uwi);
            
            if (is_array($details_result) && !isset($details_result['error'])) {
                echo "<p style='color: green;'>✓ Obtención de detalles funciona correctamente</p>";
                echo "<p>UWI probado: " . $first_well_uwi . "</p>";
                echo "<p>Campos obtenidos: " . count($details_result) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Error en obtención de detalles: " . ($details_result['error'] ?? 'Error desconocido') . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ No hay pozos para probar detalles</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ No se pudo establecer conexión con la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Detalles del error:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?> 