<?php
/**
 * Simple browser test for the current query
 * Access this via web browser to test the query
 */

require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Query - Oracle 7</title></head><body>";
echo "<h1>Test de Consulta Actual - Oracle 7</h1>";

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
            echo "<p><strong>Pozo de prueba:</strong> " . htmlspecialchars($test_uwi) . "</p>";
            
            // Test 1: Minimal query
            echo "<h3>Test 1: Consulta mínima</h3>";
            try {
                $sql1 = "SELECT UWI, WELL_NAME, SHORT_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->execute([':uwi' => $test_uwi]);
                $result1 = $stmt1->fetch();
                
                if ($result1) {
                    echo "<p style='color: green;'>✓ Consulta mínima exitosa</p>";
                    echo "<p>UWI: " . htmlspecialchars($result1['UWI']) . "</p>";
                    echo "<p>WELL_NAME: " . htmlspecialchars($result1['WELL_NAME']) . "</p>";
                    echo "<p>SHORT_NAME: " . htmlspecialchars($result1['SHORT_NAME']) . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Consulta mínima sin resultados</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Error en consulta mínima: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Test 2: Current query (without alias)
            echo "<h3>Test 2: Consulta actual (sin alias)</h3>";
            try {
                $sql2 = "SELECT
                    UWI, WELL_NAME, SHORT_NAME, PLOT_NAME,
                    INITIAL_CLASS, CLASS, CURRENT_CLASS,
                    ORSTATUS, CRSTATUS, COUNTRY, 
                    GEOLOGIC_PROVINCE, PROV_ST, COUNTY,
                    FIELD, BLOCK_ID, LOCATION_TABLE,
                    SPUD_DATE, FIN_DRILL, RIGREL, COMP_DATE,
                    ONINJECT, ONPROD, DISCOVER_WELL, DEVIATION_FLAG,
                    PLOT_SYMBOL, GOVT_ASSIGNED_NO, WELL_HDR_TYPE,
                    WELL_NUMBER, PARENT_UWI, TIE_IN_UWI, PRIMARY_SOURCE,
                    CONTRACTOR, RIG_NO, RIG_NAME, HOLE_DIRECTION,
                    OPERATOR, DISTRICT, AGENT, LEASE_NO,
                    LEASE_NAME, LICENSEE, DRILLERS_TD, TVD,
                    LOG_TD, LOG_TVD, PLUGBACK_TD, WHIPSTOCK_DEPTH,
                    WATER_DEPTH, ELEVATION_REF, ELEVATION,
                    GROUND_ELEVATION, FORM_AT_TD, NODE_ID
                FROM PDVSA.WELL_HDR
                WHERE UWI = :uwi";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([':uwi' => $test_uwi]);
                $result2 = $stmt2->fetch();
                
                if ($result2) {
                    echo "<p style='color: green;'>✓ Consulta actual exitosa</p>";
                    echo "<p>Columnas obtenidas: " . count($result2) . "</p>";
                    echo "<p>Primeras 5 columnas: " . implode(', ', array_slice(array_keys($result2), 0, 5)) . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Consulta actual sin resultados</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Error en consulta actual: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Test 3: Test the actual method
            echo "<h3>Test 3: Método getWellDetailsOptimized</h3>";
            try {
                require_once 'models/Well.php';
                $details = Well::getWellDetailsOptimized($test_uwi);
                
                if (isset($details['error'])) {
                    echo "<p style='color: red;'>✗ Error en método: " . htmlspecialchars($details['error']) . "</p>";
                } else {
                    echo "<p style='color: green;'>✓ Método exitoso</p>";
                    echo "<p>Datos obtenidos: " . count($details) . " campos</p>";
                    echo "<p>Primeros 5 campos: " . implode(', ', array_slice(array_keys($details), 0, 5)) . "</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Excepción en método: " . htmlspecialchars($e->getMessage()) . "</p>";
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

echo "<h2>Información del sistema</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";

echo "</body></html>";
?> 