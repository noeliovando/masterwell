<?php
require_once 'config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Mínimo - WELL_HDR</h1>";

try {
    $pdo = get_db_connection();
    if ($pdo) {
        echo "<p style='color: green;'>✓ Conexión exitosa</p>";
        
        // Get a test UWI
        $sql = "SELECT UWI FROM PDVSA.WELL_HDR WHERE ROWNUM <= 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $test_well = $stmt->fetch();
        
        if ($test_well) {
            $test_uwi = $test_well['UWI'];
            echo "<p>Pozo de prueba: " . htmlspecialchars($test_uwi) . "</p>";
            
            // Test minimal query
            echo "<h3>Test: Consulta mínima</h3>";
            try {
                $sql_minimal = "SELECT UWI, WELL_NAME, SHORT_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
                $stmt_minimal = $pdo->prepare($sql_minimal);
                $stmt_minimal->execute([':uwi' => $test_uwi]);
                $result_minimal = $stmt_minimal->fetch();
                
                if ($result_minimal) {
                    echo "<p style='color: green;'>✓ Consulta mínima exitosa</p>";
                    echo "<p>UWI: " . htmlspecialchars($result_minimal['UWI']) . "</p>";
                    echo "<p>WELL_NAME: " . htmlspecialchars($result_minimal['WELL_NAME']) . "</p>";
                    echo "<p>SHORT_NAME: " . htmlspecialchars($result_minimal['SHORT_NAME']) . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Consulta mínima sin resultados</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Error en consulta mínima: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Test with more columns
            echo "<h3>Test: Consulta con más columnas</h3>";
            try {
                $sql_extended = "SELECT UWI, WELL_NAME, SHORT_NAME, PLOT_NAME, INITIAL_CLASS, CLASS, CURRENT_CLASS, ORSTATUS, CRSTATUS, COUNTRY, GEOLOGIC_PROVINCE, PROV_ST, COUNTY, FIELD, BLOCK_ID, LOCATION_TABLE, SPUD_DATE, FIN_DRILL, RIGREL, COMP_DATE, ONINJECT, ONPROD, DISCOVER_WELL, DEVIATION_FLAG, PLOT_SYMBOL, GOVT_ASSIGNED_NO, WELL_HDR_TYPE, WELL_NUMBER, PARENT_UWI, TIE_IN_UWI, PRIMARY_SOURCE, CONTRACTOR, RIG_NO, RIG_NAME, HOLE_DIRECTION, OPERATOR, DISTRICT, AGENT, LEASE_NO, LEASE_NAME, LICENSEE, DRILLERS_TD, TVD, LOG_TD, LOG_TVD, PLUGBACK_TD, WHIPSTOCK_DEPTH, WATER_DEPTH, ELEVATION_REF, ELEVATION, GROUND_ELEVATION, FORM_AT_TD, NODE_ID FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
                $stmt_extended = $pdo->prepare($sql_extended);
                $stmt_extended->execute([':uwi' => $test_uwi]);
                $result_extended = $stmt_extended->fetch();
                
                if ($result_extended) {
                    echo "<p style='color: green;'>✓ Consulta extendida exitosa</p>";
                    echo "<p>Columnas obtenidas: " . count($result_extended) . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Consulta extendida sin resultados</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Error en consulta extendida: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ No se encontraron pozos para probar</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ No se pudo establecer conexión con la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 