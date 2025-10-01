<?php
require_once 'config/database.php';

echo "<h2>Prueba de Consulta Simple con Alias - Oracle 7</h2>";

try {
    $pdo = get_db_connection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Conexión exitosa a Oracle 7</p>";
    
    // UWI de prueba (puedes cambiar este valor)
    $test_uwi = '123456789012345';
    
    echo "<h3>Prueba 1: Consulta básica sin alias</h3>";
    
    try {
        $basic_sql = "SELECT UWI, WELL_NAME, SHORT_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
        $stmt = $pdo->prepare($basic_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Consulta básica sin alias ejecutada exitosamente</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en consulta básica: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 2: Consulta con alias simple</h3>";
    
    try {
        $alias_sql = "SELECT WH.UWI, WH.WELL_NAME, WH.SHORT_NAME FROM PDVSA.WELL_HDR WH WHERE WH.UWI = :uwi";
        $stmt = $pdo->prepare($alias_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Consulta con alias simple ejecutada exitosamente</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en consulta con alias: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 3: Consulta con un outer join</h3>";
    
    try {
        $join_sql = "SELECT WH.UWI, WH.WELL_NAME, WH.SHORT_NAME, NS.LATITUDE, NS.LONGITUDE 
                     FROM PDVSA.WELL_HDR WH, PDVSA.NODES_SECOND NS(+)
                     WHERE WH.UWI = :uwi AND NS.NODE_ID(+) = WH.NODE_ID";
        $stmt = $pdo->prepare($join_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Consulta con un outer join ejecutada exitosamente</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en consulta con outer join: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 4: Consulta con dos outer joins</h3>";
    
    try {
        $join2_sql = "SELECT WH.UWI, WH.WELL_NAME, WH.SHORT_NAME, NS.LATITUDE, NS.LONGITUDE, WR.REMARKS
                      FROM PDVSA.WELL_HDR WH, PDVSA.NODES_SECOND NS(+), PDVSA.WELL_REMARKS WR(+)
                      WHERE WH.UWI = :uwi 
                        AND NS.NODE_ID(+) = WH.NODE_ID
                        AND WR.UWI(+) = WH.UWI";
        $stmt = $pdo->prepare($join2_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Consulta con dos outer joins ejecutada exitosamente</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en consulta con dos outer joins: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h3>Prueba 5: Consulta con condición adicional en outer join</h3>";
    
    try {
        $join3_sql = "SELECT WH.UWI, WH.WELL_NAME, WH.SHORT_NAME, NS.LATITUDE, NS.LONGITUDE, WR.REMARKS
                      FROM PDVSA.WELL_HDR WH, PDVSA.NODES_SECOND NS(+), PDVSA.WELL_REMARKS WR(+)
                      WHERE WH.UWI = :uwi 
                        AND NS.NODE_ID(+) = WH.NODE_ID
                        AND WR.UWI(+) = WH.UWI 
                        AND WR.REMARKS_TYPE(+) = 'INICIO_PERF'";
        $stmt = $pdo->prepare($join3_sql);
        $stmt->execute([':uwi' => $test_uwi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Consulta con condición adicional ejecutada exitosamente</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en consulta con condición adicional: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error general: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Nota:</strong> Estas pruebas nos ayudarán a identificar exactamente en qué punto la consulta falla.</p>";
?> 