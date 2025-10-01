<?php
// test_well_admin_role.php
// Archivo de prueba para verificar el sistema de roles WELL_ADMIN

session_start();
require_once 'includes/Auth.php';
require_once 'config.php';

echo "<h1>🔒 Prueba del Sistema de Roles WELL_ADMIN</h1>";

echo "<h2>📋 Información del Sistema</h2>";
echo "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Consulta SQL utilizada:</strong></p>";
echo "<pre>";
echo "SELECT granted_role\n";
echo "FROM user_role_privs\n"; 
echo "WHERE username = UPPER(:username)\n";
echo "AND granted_role = 'WELL_ADMIN'";
echo "</pre>";

echo "<h2>🧪 Prueba de Validación de Roles</h2>";

// Formulario de prueba
if ($_POST) {
    $test_user = $_POST['test_user'] ?? '';
    $test_pass = $_POST['test_pass'] ?? '';
    $test_instance = $_POST['test_instance'] ?? '';
    
    if ($test_user && $test_pass && $test_instance) {
        echo "<h3>🔍 Probando usuario: <code>{$test_user}</code></h3>";
        
        // Intentar autenticación
        $result = Auth::login($test_user, $test_pass, $test_instance);
        
        if ($result) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; color: #155724;'>";
            echo "✅ <strong>ACCESO PERMITIDO</strong><br>";
            echo "El usuario <code>{$test_user}</code> tiene el rol WELL_ADMIN y puede acceder a la aplicación.";
            echo "</div>";
            
            // Limpiar sesión después de la prueba
            Auth::logout();
        } else {
            $error = $_SESSION['login_error'] ?? 'Error desconocido';
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; color: #721c24;'>";
            echo "❌ <strong>ACCESO DENEGADO</strong><br>";
            echo "Razón: {$error}";
            echo "</div>";
            unset($_SESSION['login_error']);
        }
    }
}

echo "<form method='POST' style='margin-top: 20px;'>";
echo "<h3>Probar Acceso de Usuario:</h3>";
echo "<table style='border-collapse: collapse;'>";
echo "<tr>";
echo "<td style='padding: 5px;'><label for='test_user'>Usuario:</label></td>";
echo "<td style='padding: 5px;'><input type='text' id='test_user' name='test_user' required style='width: 200px; padding: 5px;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 5px;'><label for='test_pass'>Contraseña:</label></td>";
echo "<td style='padding: 5px;'><input type='password' id='test_pass' name='test_pass' required style='width: 200px; padding: 5px;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 5px;'><label for='test_instance'>Instancia:</label></td>";
echo "<td style='padding: 5px;'>";
echo "<select id='test_instance' name='test_instance' required style='width: 212px; padding: 5px;'>";
echo "<option value=''>Seleccionar...</option>";
foreach ($db_instances as $name => $dsn) {
    echo "<option value='{$name}'>{$name}</option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='2' style='padding: 10px; text-align: center;'>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;'>Probar Acceso</button>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";

echo "<h2>📚 Información Técnica</h2>";
echo "<h3>🔧 Cómo Funciona el Sistema:</h3>";
echo "<ol>";
echo "<li><strong>Conexión:</strong> El usuario ingresa credenciales y selecciona instancia</li>";
echo "<li><strong>Validación de Conexión:</strong> Se verifica que las credenciales sean válidas</li>";
echo "<li><strong>Verificación de Rol:</strong> Se consulta la tabla <code>user_role_privs</code></li>";
echo "<li><strong>Decisión:</strong> Solo se permite acceso si tiene rol <code>WELL_ADMIN</code></li>";
echo "</ol>";

echo "<h3>📊 Tipos de Respuesta:</h3>";
echo "<ul>";
echo "<li><strong>✅ Acceso Permitido:</strong> Usuario tiene rol WELL_ADMIN</li>";
echo "<li><strong>❌ Acceso Denegado:</strong> Usuario no tiene rol WELL_ADMIN</li>";
echo "<li><strong>❌ Error de Conexión:</strong> Credenciales incorrectas o problema de BD</li>";
echo "<li><strong>❌ Error de Consulta:</strong> Problema al verificar roles</li>";
echo "</ul>";

echo "<h3>🛡️ Seguridad Implementada:</h3>";
echo "<ul>";
echo "<li><strong>Principio de Menor Privilegio:</strong> Solo usuarios WELL_ADMIN</li>";
echo "<li><strong>Fail-Safe:</strong> En caso de error, se deniega acceso</li>";
echo "<li><strong>Logging:</strong> Se registran intentos de acceso en logs</li>";
echo "<li><strong>Validación Robusta:</strong> Verificación antes de crear sesión</li>";
echo "</ul>";

echo "<h2>📝 Archivos Modificados</h2>";
echo "<ul>";
echo "<li><code>includes/Auth.php</code> - Agregada validación de rol WELL_ADMIN</li>";
echo "<li><code>test_well_admin_role.php</code> - Archivo de prueba del sistema</li>";
echo "</ul>";

echo "<h2>⚠️ Notas Importantes</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; color: #856404;'>";
echo "<p><strong>⚠️ Importante:</strong> Este sistema restringe el acceso SOLAMENTE a usuarios con rol WELL_ADMIN.</p>";
echo "<p><strong>📋 Para usar la aplicación:</strong> El administrador de BD debe asignar el rol WELL_ADMIN a los usuarios autorizados.</p>";
echo "<p><strong>🔒 Seguridad:</strong> Usuarios sin este rol no podrán acceder a la aplicación, incluso con credenciales válidas.</p>";
echo "</div>";

echo "<h2>🚀 Próximos Pasos</h2>";
echo "<ol>";
echo "<li>Probar con un usuario que tenga rol WELL_ADMIN</li>";
echo "<li>Probar con un usuario que NO tenga rol WELL_ADMIN</li>";
echo "<li>Verificar que los logs muestren las validaciones correctamente</li>";
echo "<li>Confirmar que la aplicación principal funciona correctamente</li>";
echo "</ol>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #007bff; }
    h2 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
    h3 { color: #dc3545; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #007bff; }
    code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; color: #e83e8c; }
    table { border: 1px solid #ddd; }
    td { border: 1px solid #ddd; }
</style>