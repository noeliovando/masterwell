<?php
require_once 'config/database.php';

echo "<h2>Prueba de Consulta con Alias - Oracle 7</h2>";

try {
    $pdo = get_db_connection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Conexión exitosa a Oracle 7</p>";
    
    // UWI de prueba (puedes cambiar este valor)
    $test_uwi = '123456789012345';
    
    echo "<h3>Probando consulta con alias y estructura Oracle 7:</h3>";
    
    // Consulta con alias pero compatible con Oracle 7
    $sql = "SELECT
        WH.UWI, WH.WELL_NAME, WH.SHORT_NAME, WH.PLOT_NAME,
        WH.INITIAL_CLASS, WH.CLASS, WH.CURRENT_CLASS,
        WH.ORSTATUS, WH.CRSTATUS, WH.COUNTRY, 
        WH.GEOLOGIC_PROVINCE, WH.PROV_ST, WH.COUNTY,
        WH.FIELD, WH.BLOCK_ID, WH.LOCATION_TABLE,
        WH.SPUD_DATE, WH.FIN_DRILL, WH.RIGREL, WH.COMP_DATE,
        WH.ONINJECT, WH.ONPROD, WH.DISCOVER_WELL, WH.DEVIATION_FLAG,
        WH.PLOT_SYMBOL, WH.GOVT_ASSIGNED_NO, WH.WELL_HDR_TYPE,
        WH.WELL_NUMBER, WH.PARENT_UWI, WH.TIE_IN_UWI, WH.PRIMARY_SOURCE,
        WH.CONTRACTOR, WH.RIG_NO, WH.RIG_NAME, WH.HOLE_DIRECTION,
        WH.OPERATOR, WH.DISTRICT, WH.AGENT, WH.LEASE_NO,
        WH.LEASE_NAME, WH.LICENSEE, WH.DRILLERS_TD, WH.TVD,
        WH.LOG_TD, WH.LOG_TVD, WH.PLUGBACK_TD, WH.WHIPSTOCK_DEPTH,
        WH.WATER_DEPTH, WH.ELEVATION_REF, WH.ELEVATION,
        WH.GROUND_ELEVATION, WH.FORM_AT_TD, WH.NODE_ID,
        NS.LATITUDE, NS.LONGITUDE, NS.NODE_X, NS.NODE_Y, NS.DATUM,
        WR.REMARKS, WR.REMARKS_TYPE,
        WA.WELL_ALIAS,
        GP.DESCRIPTION AS GEOLOGIC_PROVINCE_DESC,
        FH.FIELD_NAME,
        WCC.DESCRIPTION AS CLASS_DESC,
        WSC.DESCRIPTION AS STATUS_DESC,
        BA_OP.NAME AS OPERATOR_NAME,
        BA_AG.NAME AS AGENT_NAME,
        L.LEASE_NAME AS LEASE_DESC
    FROM PDVSA.WELL_HDR WH,
         PDVSA.NODES_SECOND NS(+),
         PDVSA.WELL_REMARKS WR(+),
         PDVSA.WELL_ALIAS WA(+),
         CODES.GEOLOGIC_PROVINCE GP(+),
         PDVSA.FIELD_HDR FH(+),
         CODES.WELL_CLASS_CODES WCC(+),
         CODES.WELL_STATUS_CODES WSC(+),
         PDVSA.BUSINESS_ASSOC BA_OP(+),
         PDVSA.BUSINESS_ASSOC BA_AG(+),
         PDVSA.LEASE L(+)
    WHERE WH.UWI = :uwi
      AND NS.NODE_ID(+) = WH.NODE_ID
      AND WR.UWI(+) = WH.UWI 
      AND WR.REMARKS_TYPE(+) = 'INICIO_PERF'
      AND WA.UWI(+) = WH.UWI
      AND GP.GEOL_PROV_ID(+) = WH.GEOLOGIC_PROVINCE
      AND FH.FIELD_CODE(+) = WH.FIELD
      AND WCC.CLASS_CODE(+) = WH.CLASS
      AND WCC.SOURCE(+) = 'CT_CONVERT_UTM'
      AND WSC.STATUS_CODE(+) = WH.ORSTATUS
      AND WSC.SOURCE(+) = 'CT_CONVERT_UTM'
      AND BA_OP.BUSINESS_ASSOC_ID(+) = WH.OPERATOR
      AND BA_AG.BUSINESS_ASSOC_ID(+) = WH.AGENT
      AND L.LEASE_ID(+) = WH.LEASE_NO";
    
    echo "<p><strong>SQL a ejecutar:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars($sql);
    echo "</pre>";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uwi' => $test_uwi]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Consulta ejecutada exitosamente</p>";
        echo "<h4>Resultados:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        
        foreach ($result as $key => $value) {
            if ($value !== null) {
                echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
            }
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
        echo "<p>Esto puede ser normal si el UWI no existe en la base de datos.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error en la consulta:</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    
    // Mostrar información adicional para debugging
    if (strpos($e->getMessage(), 'ORA-00904') !== false) {
        echo "<p style='color: red;'><strong>Error ORA-00904:</strong> Nombre de columna inválido. Esto puede indicar:</p>";
        echo "<ul>";
        echo "<li>Una columna no existe en la tabla</li>";
        echo "<li>Problema con alias de tabla en Oracle 7</li>";
        echo "<li>Problema con la sintaxis de outer join (+)</li>";
        echo "</ul>";
    }
    
    if (strpos($e->getMessage(), 'ORA-00933') !== false) {
        echo "<p style='color: red;'><strong>Error ORA-00933:</strong> Comando SQL no terminado correctamente. Esto puede indicar:</p>";
        echo "<ul>";
        echo "<li>Problema con la sintaxis de Oracle 7</li>";
        echo "<li>Uso de funciones no soportadas</li>";
        echo "<li>Problema con la estructura de la consulta</li>";
        echo "</ul>";
    }
}

echo "<hr>";
echo "<h3>Prueba de consulta simple con alias:</h3>";

try {
    // Prueba simple con alias
    $simple_sql = "SELECT WH.UWI, WH.WELL_NAME, WH.SHORT_NAME 
                   FROM PDVSA.WELL_HDR WH 
                   WHERE WH.UWI = :uwi";
    
    echo "<p><strong>SQL simple:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo htmlspecialchars($simple_sql);
    echo "</pre>";
    
    $stmt = $pdo->prepare($simple_sql);
    $stmt->execute([':uwi' => $test_uwi]);
    $simple_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($simple_result) {
        echo "<p style='color: green;'>✓ Consulta simple con alias ejecutada exitosamente</p>";
        echo "<p><strong>UWI:</strong> " . htmlspecialchars($simple_result['UWI']) . "</p>";
        echo "<p><strong>WELL_NAME:</strong> " . htmlspecialchars($simple_result['WELL_NAME']) . "</p>";
        echo "<p><strong>SHORT_NAME:</strong> " . htmlspecialchars($simple_result['SHORT_NAME']) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No se encontraron datos para el UWI: $test_uwi</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error en consulta simple con alias:</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Nota:</strong> Si las consultas fallan, podemos probar con una estructura más simple o identificar exactamente qué parte causa el problema.</p>";
?> 