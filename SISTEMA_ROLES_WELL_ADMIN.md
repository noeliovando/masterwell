# Sistema de Roles WELL_ADMIN - MasterWell

## 📅 Fecha de Implementación
**06 de Agosto de 2025**

## 🎯 Objetivo
Implementar un sistema de control de acceso basado en roles para restringir el acceso a la aplicación MasterWell únicamente a usuarios que posean el rol `WELL_ADMIN`.

## 🔒 Requerimiento de Seguridad
**RESTRICCIÓN**: Solo usuarios con rol `WELL_ADMIN` pueden acceder a la aplicación.

## 📋 Implementación Técnica

### **Consulta SQL Utilizada**
```sql
SELECT granted_role 
FROM user_role_privs 
WHERE username = UPPER(:username) 
AND granted_role = 'WELL_ADMIN'
```

### **Flujo de Autenticación**
1. **Conexión**: Usuario ingresa credenciales y selecciona instancia
2. **Validación de Credenciales**: Se verifica conectividad a la base de datos
3. **Verificación de Rol**: Se consulta `user_role_privs` para rol `WELL_ADMIN`
4. **Decisión de Acceso**: Solo se permite acceso si tiene el rol requerido
5. **Creación de Sesión**: Solo si todas las validaciones son exitosas

## 📂 Archivos Modificados

### **1. includes/Auth.php**

#### **Función login() - Modificada**
```php
// Verificar que el usuario tenga el rol WELL_ADMIN
if (!self::hasWellAdminRole($pdo, $username)) {
    $_SESSION['login_error'] = 'Acceso denegado: El usuario no tiene permisos de WELL_ADMIN para acceder a esta aplicación.';
    return false;
}
```

#### **Nueva Función hasWellAdminRole()**
```php
private static function hasWellAdminRole($pdo, $username) {
    try {
        $sql = "SELECT granted_role 
                FROM user_role_privs 
                WHERE username = UPPER(:username) 
                AND granted_role = 'WELL_ADMIN'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch();
        
        if ($result) {
            error_log("Usuario {$username} tiene rol WELL_ADMIN - Acceso permitido");
            return true;
        } else {
            error_log("Usuario {$username} NO tiene rol WELL_ADMIN - Acceso denegado");
            return false;
        }
        
    } catch (PDOException $e) {
        error_log("Error al verificar rol WELL_ADMIN para usuario {$username}: " . $e->getMessage());
        return false; // Fail-safe: denegar acceso en caso de error
    }
}
```

### **2. config.php**
- ✅ Agregado comentario sobre sistema de roles
- ✅ Documentada la consulta SQL utilizada
- ✅ Fecha de implementación

### **3. test_well_admin_role.php**
- ✅ Archivo de prueba para validar el sistema
- ✅ Interfaz web para probar usuarios
- ✅ Documentación técnica integrada

## 🛡️ Características de Seguridad

### **1. Principio de Menor Privilegio**
- Solo usuarios con rol específico `WELL_ADMIN` pueden acceder
- Sin el rol, no hay acceso independientemente de credenciales válidas

### **2. Fail-Safe Security**
- En caso de error al verificar roles → **Acceso denegado**
- En caso de error en consulta SQL → **Acceso denegado**
- Principio: "En caso de duda, denegar acceso"

### **3. Logging y Auditoría**
- Se registra cada intento de verificación de rol
- Logs incluyen resultado exitoso o fallido
- Información para auditorías de seguridad

### **4. Validación Robusta**
- Verificación antes de crear sesión de usuario
- No se almacenan credenciales sin verificar rol
- Proceso atómico: todo o nada

## 📊 Tipos de Respuesta del Sistema

### **✅ Acceso Permitido**
- **Condición**: Usuario tiene rol `WELL_ADMIN`
- **Acción**: Se crea sesión y redirige a dashboard
- **Log**: "Usuario {username} tiene rol WELL_ADMIN - Acceso permitido"

### **❌ Acceso Denegado por Rol**
- **Condición**: Usuario no tiene rol `WELL_ADMIN`
- **Mensaje**: "Acceso denegado: El usuario no tiene permisos de WELL_ADMIN para acceder a esta aplicación."
- **Log**: "Usuario {username} NO tiene rol WELL_ADMIN - Acceso denegado"

### **❌ Error de Conexión**
- **Condición**: Credenciales incorrectas o problema de BD
- **Mensaje**: "Error de conexión: [detalles del error]"
- **Acción**: No se verifica rol si no hay conexión

### **❌ Error de Consulta de Rol**
- **Condición**: Error al ejecutar consulta de verificación
- **Acción**: Acceso denegado por seguridad (fail-safe)
- **Log**: "Error al verificar rol WELL_ADMIN para usuario {username}: [error]"

## 🧪 Pruebas del Sistema

### **Archivo de Prueba: test_well_admin_role.php**

#### **Funcionalidades de Prueba:**
- ✅ Interfaz web para probar usuarios
- ✅ Selección de instancia de BD
- ✅ Resultados visuales de aprobación/denegación
- ✅ Información técnica del sistema
- ✅ Guías de uso y configuración

#### **Casos de Prueba:**
1. **Usuario con WELL_ADMIN**: Debe permitir acceso
2. **Usuario sin WELL_ADMIN**: Debe denegar acceso
3. **Usuario inexistente**: Debe denegar acceso
4. **Credenciales incorrectas**: Debe denegar acceso

### **URL de Prueba:**
```
http://localhost/test_well_admin_role.php
```

## 🔧 Configuración Administrativa

### **Para Otorgar Acceso a un Usuario:**
```sql
-- El DBA debe ejecutar este comando para dar acceso:
GRANT WELL_ADMIN TO nombre_usuario;
```

### **Para Verificar Roles de un Usuario:**
```sql
-- Verificar roles de un usuario específico:
SELECT granted_role 
FROM user_role_privs 
WHERE username = UPPER('nombre_usuario');
```

### **Para Revocar Acceso:**
```sql
-- El DBA puede revocar acceso ejecutando:
REVOKE WELL_ADMIN FROM nombre_usuario;
```

## 📈 Beneficios Implementados

### **1. Seguridad Mejorada**
- ✅ Control de acceso granular por roles
- ✅ Prevención de acceso no autorizado
- ✅ Auditoría completa de intentos de acceso

### **2. Administración Simplificada**
- ✅ Control centralizado via roles de BD
- ✅ No requiere mantenimiento de listas de usuarios en código
- ✅ Administración estándar de Oracle

### **3. Compliance y Auditoría**
- ✅ Logs detallados de accesos
- ✅ Trazabilidad de usuarios autorizados
- ✅ Principios de seguridad corporativa

### **4. Mantenibilidad**
- ✅ Configuración via base de datos
- ✅ Sin hardcoding de usuarios
- ✅ Escalable para múltiples usuarios

## ⚠️ Consideraciones Importantes

### **Para Administradores:**
1. **Asignación de Rol**: Usuarios deben tener rol `WELL_ADMIN` asignado por DBA
2. **Testing**: Usar `test_well_admin_role.php` para verificar configuración
3. **Logs**: Revisar logs para monitorear intentos de acceso
4. **Backup**: Mantener lista de usuarios autorizados para respaldo

### **Para Usuarios:**
1. **Requerimiento**: Deben solicitar rol `WELL_ADMIN` al administrador
2. **Mensaje de Error**: Si ven mensaje de acceso denegado, contactar administrador
3. **Credenciales**: Deben seguir teniendo credenciales válidas de BD

### **Para Desarrolladores:**
1. **No Modificar**: No cambiar la lógica de verificación de roles
2. **Testing**: Siempre probar con usuarios que tengan y no tengan el rol
3. **Logs**: Monitorear logs durante desarrollo
4. **Fail-Safe**: Mantener principio de denegar acceso en caso de error

## 🔮 Extensiones Futuras Posibles

### **Roles Adicionales:**
- [ ] Implementar `WELL_VIEWER` para acceso de solo lectura
- [ ] Implementar `WELL_EDITOR` para edición limitada
- [ ] Sistema de roles jerárquicos

### **Funcionalidades Avanzadas:**
- [ ] Timeout de sesión basado en rol
- [ ] Restricciones por instancia de BD
- [ ] Auditoría avanzada de acciones por rol

## 📞 Soporte y Troubleshooting

### **Problemas Comunes:**

#### **"Acceso denegado: El usuario no tiene permisos de WELL_ADMIN"**
- **Causa**: Usuario no tiene rol asignado
- **Solución**: DBA debe ejecutar `GRANT WELL_ADMIN TO usuario;`

#### **"Error al verificar rol WELL_ADMIN"**
- **Causa**: Problema en consulta a `user_role_privs`
- **Solución**: Verificar permisos de consulta y existencia de vista

#### **Usuario con rol no puede acceder**
- **Causa**: Problema en consulta o configuración
- **Solución**: Usar `test_well_admin_role.php` para diagnosticar

### **Verificación Manual:**
```sql
-- Verificar que la vista existe:
SELECT * FROM user_role_privs WHERE ROWNUM <= 1;

-- Verificar roles específicos:
SELECT username, granted_role 
FROM user_role_privs 
WHERE granted_role = 'WELL_ADMIN';
```

## ✅ Estado Final

**SISTEMA DE ROLES IMPLEMENTADO EXITOSAMENTE**

- ✅ **Control de acceso**: Solo usuarios WELL_ADMIN
- ✅ **Seguridad robusta**: Fail-safe implementado
- ✅ **Logging completo**: Auditoría de accesos
- ✅ **Pruebas disponibles**: Sistema de testing funcional
- ✅ **Documentación completa**: Guías técnicas y de usuario

## 👥 Desarrollado por
- **Sistema de Optimización MasterWell**
- **Fecha:** 06 de Agosto de 2025
- **Versión:** 1.0
- **Estado:** ✅ Implementado y Funcionando

---

*Este documento describe la implementación completa del sistema de roles WELL_ADMIN en MasterWell, proporcionando control de acceso granular y seguridad robusta.*