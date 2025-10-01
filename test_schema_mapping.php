<?php
// test_schema_mapping.php
// Archivo de prueba para validar el mapeo dinámico de esquemas

session_start();
require_once 'db.php';
require_once 'models/Well.php';

echo "<h1>🧪 Prueba de Mapeo Dinámico de Esquemas</h1>";

// Simular conexión con diferentes instancias
$test_instances = [
    'PDVSA - Exploración y Producción',
    'CVP - Empresas Mixtas',
    'Entrenamiento'
];

foreach ($test_instances as $instance) {
    echo "<h2>📊 Probando instancia: {$instance}</h2>";
    
    // Simular sesión con esta instancia
    $_SESSION['db_credentials'] = [
        'db_instance' => $instance,
        'user' => 'test_user',
        'pass' => 'test_pass'
    ];
    
    try {
        // Probar funciones de esquema
        $main_schema = get_main_schema();
        $codes_schema = get_codes_schema();
        
        echo "<p><strong>✅ Esquema principal:</strong> {$main_schema}</p>";
        echo "<p><strong>✅ Esquema de códigos:</strong> {$codes_schema}</p>";
        
        // Probar función helper
        $well_table = get_table_name('WELL_HDR', 'main');
        $province_table = get_table_name('GEOLOGIC_PROVINCE', 'codes');
        
        echo "<p><strong>📋 Tabla de pozos:</strong> {$well_table}</p>";
        echo "<p><strong>📋 Tabla de provincias:</strong> {$province_table}</p>";
        
        // Mostrar consulta de ejemplo
        echo "<p><strong>🔍 Consulta de búsqueda optimizada:</strong></p>";
        echo "<pre>";
        echo "SELECT UWI, WELL_NAME, SHORT_NAME, PLOT_NAME\n";
        echo "FROM {$main_schema}.WELL_HDR\n";
        echo "WHERE (UWI LIKE :search_term_prefix\n";
        echo "   OR UWI LIKE :search_term_contains)\n";
        echo "   AND ROWNUM <= 50\n";
        echo "ORDER BY UWI";
        echo "</pre>";
        
        echo "<hr>";
        
    } catch (Exception $e) {
        echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<hr>";
    }
}

echo "<h2>📝 Resumen del Mapeo de Esquemas</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Instancia</th><th>Esquema Principal</th><th>Esquema de Códigos</th><th>Tabla Principal</th></tr>";

require_once 'config.php';
global $db_schemas;

foreach ($db_schemas as $instance => $schemas) {
    echo "<tr>";
    echo "<td>{$instance}</td>";
    echo "<td>{$schemas['main_schema']}</td>";
    echo "<td>{$schemas['codes_schema']}</td>";
    echo "<td>{$schemas['main_schema']}.WELL_HDR</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>🎯 Beneficios de la Implementación</h2>";
echo "<ul>";
echo "<li>✅ <strong>Compatibilidad automática:</strong> La aplicación detecta automáticamente qué esquema usar</li>";
echo "<li>✅ <strong>Sin cambios manuales:</strong> No requiere modificaciones en código al cambiar instancia</li>";
echo "<li>✅ <strong>Escalabilidad:</strong> Fácil agregar nuevas instancias con diferentes esquemas</li>";
echo "<li>✅ <strong>Mantenimiento:</strong> Configuración centralizada en config.php</li>";
echo "<li>✅ <strong>Robustez:</strong> Valores por defecto en caso de instancias no configuradas</li>";
echo "</ul>";

echo "<h2>🚀 Funciones Implementadas</h2>";
echo "<ul>";
echo "<li><code>get_main_schema()</code> - Obtiene el esquema principal (PDVSA o FINDCVP)</li>";
echo "<li><code>get_codes_schema()</code> - Obtiene el esquema de códigos (CODES)</li>";
echo "<li><code>get_table_name(\$table, \$schema_type)</code> - Construye nombres de tabla completos</li>";
echo "</ul>";

echo "<h2>📋 Próximos Pasos</h2>";
echo "<ol>";
echo "<li>Probar conexión real con instancia CVP</li>";
echo "<li>Validar que las consultas funcionan correctamente</li>";
echo "<li>Verificar que todos los módulos usan el esquema dinámico</li>";
echo "<li>Monitorear logs para asegurar funcionamiento correcto</li>";
echo "</ol>";

// Limpiar sesión de prueba
unset($_SESSION['db_credentials']);
?>