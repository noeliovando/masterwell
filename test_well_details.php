<?php
/**
 * Test file for well details query - Oracle 7 compatibility
 * This file tests the getWellDetailsOptimized method to identify ORA-00904 errors
 */

require_once 'config/database.php';
require_once 'models/Well.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Consulta de Detalles de Pozo - Oracle 7</h1>";

// Test database connection
echo "<h2>1. Probando conexión a la base de datos...</h2>";
try {
    $pdo = get_db_connection();
    if ($pdo) {
        echo "<p style='color: green;'>✓ Conexión exitosa</p>";
        
        // Test basic well query
        echo "<h2>2. Probando consulta básica de pozo...</h2>";
        $test_uwi = "12345678901234567890"; // Use a test UWI
        $sql = "SELECT UWI, WELL_NAME FROM PDVSA.WELL_HDR WHERE ROWNUM <= 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $test_well = $stmt->fetch();
        
        if ($test_well) {
            $test_uwi = $test_well['UWI'];
            echo "<p>✓ Pozo de prueba encontrado: " . htmlspecialchars($test_uwi) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No se encontraron pozos para probar</p>";
        }
        
        // Test the optimized query step by step
        echo "<h2>3. Probando consulta optimizada paso a paso...</h2>";
        
        // Test 1: Basic well header query
        echo "<h3>3.1 Consulta básica de WELL_HDR</h3>";
        try {
            $sql1 = "SELECT UWI, WELL_NAME, SHORT_NAME FROM PDVSA.WELL_HDR WHERE UWI = :uwi";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':uwi' => $test_uwi]);
            $result1 = $stmt1->fetch();
            if ($result1) {
                echo "<p style='color: green;'>✓ Consulta básica exitosa</p>";
            } else {
                echo "<p style='color: orange;'>⚠ No se encontró el pozo con UWI: " . htmlspecialchars($test_uwi) . "</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en consulta básica: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 2: Join with NODES_SECOND
        echo "<h3>3.2 Join con NODES_SECOND</h3>";
        try {
            $sql2 = "SELECT WH.UWI, WH.WELL_NAME, NS.LATITUDE, NS.LONGITUDE 
                     FROM PDVSA.WELL_HDR WH, PDVSA.NODES_SECOND NS 
                     WHERE WH.UWI = :uwi AND WH.NODE_ID = NS.NODE_ID(+)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([':uwi' => $test_uwi]);
            $result2 = $stmt2->fetch();
            if ($result2) {
                echo "<p style='color: green;'>✓ Join con NODES_SECOND exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con NODES_SECOND sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con NODES_SECOND: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 3: Join with WELL_REMARKS
        echo "<h3>3.3 Join con WELL_REMARKS</h3>";
        try {
            $sql3 = "SELECT WH.UWI, WH.WELL_NAME, WR.REMARKS 
                     FROM PDVSA.WELL_HDR WH, PDVSA.WELL_REMARKS WR 
                     WHERE WH.UWI = :uwi AND WH.UWI = WR.UWI(+)";
            $stmt3 = $pdo->prepare($sql3);
            $stmt3->execute([':uwi' => $test_uwi]);
            $result3 = $stmt3->fetch();
            if ($result3) {
                echo "<p style='color: green;'>✓ Join con WELL_REMARKS exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con WELL_REMARKS sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con WELL_REMARKS: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 4: Join with GEOLOGIC_PROVINCE
        echo "<h3>3.4 Join con GEOLOGIC_PROVINCE</h3>";
        try {
            $sql4 = "SELECT WH.UWI, WH.WELL_NAME, GP.DESCRIPTION 
                     FROM PDVSA.WELL_HDR WH, CODES.GEOLOGIC_PROVINCE GP 
                     WHERE WH.UWI = :uwi AND WH.GEOLOGIC_PROVINCE = GP.GEOL_PROV_ID(+)";
            $stmt4 = $pdo->prepare($sql4);
            $stmt4->execute([':uwi' => $test_uwi]);
            $result4 = $stmt4->fetch();
            if ($result4) {
                echo "<p style='color: green;'>✓ Join con GEOLOGIC_PROVINCE exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con GEOLOGIC_PROVINCE sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con GEOLOGIC_PROVINCE: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 5: Join with FIELD_HDR
        echo "<h3>3.5 Join con FIELD_HDR</h3>";
        try {
            $sql5 = "SELECT WH.UWI, WH.WELL_NAME, FH.FIELD_NAME 
                     FROM PDVSA.WELL_HDR WH, PDVSA.FIELD_HDR FH 
                     WHERE WH.UWI = :uwi AND WH.FIELD = FH.FIELD_CODE(+)";
            $stmt5 = $pdo->prepare($sql5);
            $stmt5->execute([':uwi' => $test_uwi]);
            $result5 = $stmt5->fetch();
            if ($result5) {
                echo "<p style='color: green;'>✓ Join con FIELD_HDR exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con FIELD_HDR sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con FIELD_HDR: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 6: Join with WELL_CLASS_CODES
        echo "<h3>3.6 Join con WELL_CLASS_CODES</h3>";
        try {
            $sql6 = "SELECT WH.UWI, WH.WELL_NAME, WCC.REMARKS 
                     FROM PDVSA.WELL_HDR WH, CODES.WELL_CLASS_CODES WCC 
                     WHERE WH.UWI = :uwi AND WH.INITIAL_CLASS = WCC.CODE(+)";
            $stmt6 = $pdo->prepare($sql6);
            $stmt6->execute([':uwi' => $test_uwi]);
            $result6 = $stmt6->fetch();
            if ($result6) {
                echo "<p style='color: green;'>✓ Join con WELL_CLASS_CODES exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con WELL_CLASS_CODES sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con WELL_CLASS_CODES: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 7: Join with WELL_STATUS_CODES
        echo "<h3>3.7 Join con WELL_STATUS_CODES</h3>";
        try {
            $sql7 = "SELECT WH.UWI, WH.WELL_NAME, WSC.REMARKS 
                     FROM PDVSA.WELL_HDR WH, CODES.WELL_STATUS_CODES WSC 
                     WHERE WH.UWI = :uwi AND WH.ORSTATUS = WSC.STATUS(+)";
            $stmt7 = $pdo->prepare($sql7);
            $stmt7->execute([':uwi' => $test_uwi]);
            $result7 = $stmt7->fetch();
            if ($result7) {
                echo "<p style='color: green;'>✓ Join con WELL_STATUS_CODES exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con WELL_STATUS_CODES sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con WELL_STATUS_CODES: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 8: Join with BUSINESS_ASSOC (Operator)
        echo "<h3>3.8 Join con BUSINESS_ASSOC (Operator)</h3>";
        try {
            $sql8 = "SELECT WH.UWI, WH.WELL_NAME, BA.ASSOC_NAME 
                     FROM PDVSA.WELL_HDR WH, CODES.BUSINESS_ASSOC BA 
                     WHERE WH.UWI = :uwi AND WH.OPERATOR = BA.ASSOC_ID(+)";
            $stmt8 = $pdo->prepare($sql8);
            $stmt8->execute([':uwi' => $test_uwi]);
            $result8 = $stmt8->fetch();
            if ($result8) {
                echo "<p style='color: green;'>✓ Join con BUSINESS_ASSOC (Operator) exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con BUSINESS_ASSOC (Operator) sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con BUSINESS_ASSOC (Operator): " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 9: Join with BUSINESS_ASSOC (Agent)
        echo "<h3>3.9 Join con BUSINESS_ASSOC (Agent)</h3>";
        try {
            $sql9 = "SELECT WH.UWI, WH.WELL_NAME, AG.ASSOC_NAME 
                     FROM PDVSA.WELL_HDR WH, CODES.BUSINESS_ASSOC AG 
                     WHERE WH.UWI = :uwi AND WH.AGENT = AG.ASSOC_ID(+)";
            $stmt9 = $pdo->prepare($sql9);
            $stmt9->execute([':uwi' => $test_uwi]);
            $result9 = $stmt9->fetch();
            if ($result9) {
                echo "<p style='color: green;'>✓ Join con BUSINESS_ASSOC (Agent) exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con BUSINESS_ASSOC (Agent) sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con BUSINESS_ASSOC (Agent): " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 10: Join with LEASE
        echo "<h3>3.10 Join con LEASE</h3>";
        try {
            $sql10 = "SELECT WH.UWI, WH.WELL_NAME, LN.LEASE_NAME 
                      FROM PDVSA.WELL_HDR WH, PDVSA.LEASE LN 
                      WHERE WH.UWI = :uwi AND WH.LEASE_NO = LN.LEASE_ID(+)";
            $stmt10 = $pdo->prepare($sql10);
            $stmt10->execute([':uwi' => $test_uwi]);
            $result10 = $stmt10->fetch();
            if ($result10) {
                echo "<p style='color: green;'>✓ Join con LEASE exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con LEASE sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con LEASE: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 11: Join with WELL_ALIAS
        echo "<h3>3.11 Join con WELL_ALIAS</h3>";
        try {
            $sql11 = "SELECT WH.UWI, WH.WELL_NAME, WA.WELL_ALIAS 
                      FROM PDVSA.WELL_HDR WH, PDVSA.WELL_ALIAS WA 
                      WHERE WH.UWI = :uwi AND WH.UWI = WA.UWI(+)";
            $stmt11 = $pdo->prepare($sql11);
            $stmt11->execute([':uwi' => $test_uwi]);
            $result11 = $stmt11->fetch();
            if ($result11) {
                echo "<p style='color: green;'>✓ Join con WELL_ALIAS exitoso</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Join con WELL_ALIAS sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en join con WELL_ALIAS: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 12: Full optimized query with minimal columns
        echo "<h3>3.12 Consulta optimizada con columnas mínimas</h3>";
        try {
            $sql12 = "SELECT
                WH.UWI, WH.WELL_NAME, WH.SHORT_NAME,
                NS.LATITUDE, NS.LONGITUDE,
                WR.REMARKS,
                WA.WELL_ALIAS,
                GP.DESCRIPTION AS GEOLOGIC_PROVINCE_DESC,
                FH.FIELD_NAME,
                WCC.REMARKS AS INITIAL_CLASS_DESC,
                WSC.REMARKS AS ORSTATUS_DESC,
                BA.ASSOC_NAME AS OPERATOR_NAME,
                AG.ASSOC_NAME AS AGENT_NAME,
                LN.LEASE_NAME AS LEASE_NAME_DESC
            FROM PDVSA.WELL_HDR WH,
                 PDVSA.NODES_SECOND NS,
                 PDVSA.WELL_REMARKS WR,
                 PDVSA.WELL_ALIAS WA,
                 CODES.GEOLOGIC_PROVINCE GP,
                 PDVSA.FIELD_HDR FH,
                 CODES.WELL_CLASS_CODES WCC,
                 CODES.WELL_STATUS_CODES WSC,
                 CODES.BUSINESS_ASSOC BA,
                 CODES.BUSINESS_ASSOC AG,
                 PDVSA.LEASE LN
            WHERE WH.UWI = :uwi
              AND WH.NODE_ID = NS.NODE_ID(+)
              AND WH.UWI = WR.UWI(+)
              AND WH.UWI = WA.UWI(+)
              AND WH.GEOLOGIC_PROVINCE = GP.GEOL_PROV_ID(+)
              AND WH.FIELD = FH.FIELD_CODE(+)
              AND WH.INITIAL_CLASS = WCC.CODE(+)
              AND WH.ORSTATUS = WSC.STATUS(+)
              AND WH.OPERATOR = BA.ASSOC_ID(+)
              AND WH.AGENT = AG.ASSOC_ID(+)
              AND WH.LEASE_NO = LN.LEASE_ID(+)";
            $stmt12 = $pdo->prepare($sql12);
            $stmt12->execute([':uwi' => $test_uwi]);
            $result12 = $stmt12->fetch();
            if ($result12) {
                echo "<p style='color: green;'>✓ Consulta optimizada con columnas mínimas exitosa</p>";
                echo "<p>Columnas obtenidas: " . implode(', ', array_keys($result12)) . "</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Consulta optimizada con columnas mínimas sin resultados</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error en consulta optimizada con columnas mínimas: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 13: Full optimized query
        echo "<h3>3.13 Consulta optimizada completa</h3>";
        try {
            $details = Well::getWellDetailsOptimized($test_uwi);
            if (isset($details['error'])) {
                echo "<p style='color: red;'>✗ Error en consulta optimizada: " . htmlspecialchars($details['error']) . "</p>";
            } else {
                echo "<p style='color: green;'>✓ Consulta optimizada exitosa</p>";
                echo "<p>Datos obtenidos: " . count($details) . " campos</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Excepción en consulta optimizada: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ No se pudo establecer conexión con la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>4. Información del sistema</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
echo "<p>Oracle Client Version: " . (function_exists('oci_server_version') ? oci_server_version() : 'No disponible') . "</p>";
?> 