<?php
/**
 * Simple test to identify the problematic column causing ORA-00904
 */

require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple - Identificar Columna Problemática</h1>";

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
            
            // Test 1: Basic columns only
            echo "<h3>Test 1: Columnas básicas de WELL_HDR</h3>";
            try {
                $sql1 = "SELECT UWI, WELL_NAME, SHORT_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->execute([':uwi' => $test_uwi]);
                $result1 = $stmt1->fetch();
                echo "<p style='color: green;'>✓ Test 1 exitoso</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Test 1 falló: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Test 2: Add more columns one by one
            echo "<h3>Test 2: Agregando columnas una por una</h3>";
            
            $columns_to_test = [
                'PLOT_NAME',
                'INITIAL_CLASS', 
                'CLASS',
                'CURRENT_CLASS',
                'ORSTATUS',
                'CRSTATUS',
                'COUNTRY',
                'GEOLOGIC_PROVINCE',
                'PROV_ST',
                'COUNTY',
                'FIELD',
                'BLOCK_ID',
                'LOCATION_TABLE',
                'SPUD_DATE',
                'FIN_DRILL',
                'RIGREL',
                'COMP_DATE',
                'ONINJECT',
                'ONPROD',
                'DISCOVER_WELL',
                'DEVIATION_FLAG',
                'PLOT_SYMBOL',
                'GOVT_ASSIGNED_NO',
                'WELL_HDR_TYPE',
                'WELL_NUMBER',
                'PARENT_UWI',
                'TIE_IN_UWI',
                'PRIMARY_SOURCE',
                'CONTRACTOR',
                'RIG_NO',
                'RIG_NAME',
                'HOLE_DIRECTION',
                'OPERATOR',
                'DISTRICT',
                'AGENT',
                'LEASE_NO',
                'LEASE_NAME',
                'LICENSEE',
                'DRILLERS_TD',
                'TVD',
                'LOG_TD',
                'LOG_TVD',
                'PLUGBACK_TD',
                'WHIPSTOCK_DEPTH',
                'WATER_DEPTH',
                'ELEVATION_REF',
                'ELEVATION',
                'GROUND_ELEVATION',
                'FORM_AT_TD'
            ];
            
            $working_columns = ['UWI', 'WELL_NAME', 'SHORT_NAME'];
            
            foreach ($columns_to_test as $column) {
                try {
                    $test_columns = array_merge($working_columns, [$column]);
                    $sql_test = "SELECT " . implode(', ', $test_columns) . " FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
                    $stmt_test = $pdo->prepare($sql_test);
                    $stmt_test->execute([':uwi' => $test_uwi]);
                    $result_test = $stmt_test->fetch();
                    
                    if ($result_test) {
                        $working_columns[] = $column;
                        echo "<p style='color: green;'>✓ Columna " . $column . " funciona</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠ Columna " . $column . " sin datos</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>✗ Columna " . $column . " falló: " . htmlspecialchars($e->getMessage()) . "</p>";
                    break;
                }
            }
            
            echo "<h3>Columnas que funcionan:</h3>";
            echo "<p>" . implode(', ', $working_columns) . "</p>";
            
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