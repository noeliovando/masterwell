<?php
require_once 'config/database.php';
require_once 'models/Well.php';

echo "<h2>Prueba Completa de la Aplicación - Oracle 7</h2>";

try {
    $pdo = get_db_connection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Conexión exitosa a Oracle 7</p>";
    
    // UWI de prueba (puedes cambiar este valor)
    $test_uwi = '123456789012345';
    
    echo "<h3>Prueba 1: Búsqueda de pozos optimizada</h3>";
    
    try {
        $search_results = Well::searchWellsOptimized('123');
        
        if (is_array($search_results) && !empty($search_results)) {
            echo "<p style='color: green;'>✓ Búsqueda optimizada ejecutada exitosamente</p>";
            echo "<p>Resultados encontrados: " . count($search_results) . "</p>";
            
            // Mostrar los primeros 3 resultados
            echo "<ul>";
            for ($i = 0; $i < min(3, count($search_results)); $i++) {
                $well = $search_results[$i];
                echo "<li><strong>UWI:</strong> " . htmlspecialchars($well['UWI']) . 
                     " | <strong>Nombre:</strong> " . htmlspecialchars($well['WELL_NAME']) . 
                     " | <strong>Corto:</strong> " . htmlspecialchars($well['SHORT_NAME']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron resultados de búsqueda</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error en búsqueda optimizada: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 2: Obtención de detalles del pozo optimizada</h3>";
    
    try {
        $well_details = Well::getWellDetailsOptimized($test_uwi);
        
        if (is_array($well_details)) {
            if (isset($well_details['error'])) {
                echo "<p style='color: orange;'>⚠ " . htmlspecialchars($well_details['error']) . "</p>";
                echo "<p>Esto puede ser normal si el UWI de prueba no existe en la base de datos.</p>";
            } else {
                echo "<p style='color: green;'>✓ Obtención de detalles optimizada ejecutada exitosamente</p>";
                echo "<p><strong>Datos obtenidos:</strong></p>";
                echo "<ul>";
                echo "<li><strong>UWI:</strong> " . htmlspecialchars($well_details['UWI'] ?? 'N/A') . "</li>";
                echo "<li><strong>Nombre:</strong> " . htmlspecialchars($well_details['WELL_NAME'] ?? 'N/A') . "</li>";
                echo "<li><strong>Nombre Corto:</strong> " . htmlspecialchars($well_details['SHORT_NAME'] ?? 'N/A') . "</li>";
                echo "<li><strong>Campo:</strong> " . htmlspecialchars($well_details['FIELD_NAME'] ?? 'N/A') . "</li>";
                echo "<li><strong>Operador:</strong> " . htmlspecialchars($well_details['OPERATOR_NAME'] ?? 'N/A') . "</li>";
                echo "<li><strong>Estado:</strong> " . htmlspecialchars($well_details['STATUS_DESC'] ?? 'N/A') . "</li>";
                echo "<li><strong>Latitud:</strong> " . htmlspecialchars($well_details['LATITUDE'] ?? 'N/A') . "</li>";
                echo "<li><strong>Longitud:</strong> " . htmlspecialchars($well_details['LONGITUDE'] ?? 'N/A') . "</li>";
                echo "<li><strong>Comentarios:</strong> " . htmlspecialchars($well_details['REMARKS'] ?? 'N/A') . "</li>";
                echo "<li><strong>Alias:</strong> " . htmlspecialchars($well_details['WELL_ALIAS'] ?? 'N/A') . "</li>";
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error: No se obtuvieron datos válidos</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error en obtención de detalles: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 3: Verificación de caché</h3>";
    
    try {
        // Probar el caché llamando dos veces al mismo método
        $start_time = microtime(true);
        $details1 = Well::getWellDetailsByUWI($test_uwi);
        $time1 = microtime(true) - $start_time;
        
        $start_time = microtime(true);
        $details2 = Well::getWellDetailsByUWI($test_uwi);
        $time2 = microtime(true) - $start_time;
        
        echo "<p><strong>Primera llamada:</strong> " . number_format($time1, 4) . "s</p>";
        echo "<p><strong>Segunda llamada (caché):</strong> " . number_format($time2, 4) . "s</p>";
        
        if ($time2 < $time1) {
            echo "<p style='color: green;'>✓ El caché está funcionando (segunda llamada más rápida)</p>";
        } else {
            echo "<p style='color: orange;'>⚠ El caché podría no estar funcionando como esperado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error en prueba de caché: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error general: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>Resumen de la Prueba</h3>";
echo "<p><strong>Estado:</strong> La aplicación está lista para ser probada completamente.</p>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ol>";
echo "<li>Acceder a la aplicación principal</li>";
echo "<li>Probar la búsqueda de pozos</li>";
echo "<li>Seleccionar un pozo para ver sus detalles</li>";
echo "<li>Verificar que no hay errores ORA-00933 o ORA-00904</li>";
echo "</ol>";

echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la Aplicación Principal</a></p>";
?> 