# MasterWell01 - Aplicación de Gestión de Pozos

## Descripción
Aplicación web para la gestión y consulta de información de pozos petroleros utilizando bases de datos Oracle.

## Requisitos del Sistema

### Servidor Web
- Apache 2.4+ con mod_rewrite habilitado
- PHP 7.4+ con las siguientes extensiones:
  - PDO
  - PDO_OCI (Oracle)
  - Session
  - JSON

### Base de Datos
- Oracle Database 11g o superior
- Acceso a las instancias configuradas en `config.php`

## Instalación

1. **Clonar o descargar** el proyecto en el directorio web del servidor
2. **Configurar permisos** de escritura en el directorio `logs/` si existe
3. **Verificar configuración** de la base de datos en `config.php`
4. **Acceder** a la aplicación via navegador

## Configuración

### Base de Datos
Editar `config.php` para configurar las instancias de base de datos:

```php
$db_instances = [
    'Espacio de Entrenamiento' => 'oci:dbname=162.122.168.244:1521/FINDTEST',
    'PDVSA - Exploración y Producción' => 'oci:dbname=162.122.168.244:1521/FINDPDV',
    'FINDREG8' => 'oci:dbname=167.134.183.240:1521/FINDREG8',
    'CVP - Empresas Mixtas' => 'oci:dbname=162.122.168.244:1521/CVP',
];
```

### Estructura de Archivos
```
masterwell01/
├── index.php              # Punto de entrada principal
├── config.php             # Configuración de BD
├── db.php                 # Conexión a BD
├── controllers/           # Controladores MVC
├── models/               # Modelos de datos
├── views/                # Vistas
├── partials/             # Componentes reutilizables
├── includes/             # Clases auxiliares
├── js/                   # JavaScript
├── images/               # Imágenes
└── style.css             # Estilos CSS
```

## Uso

1. **Acceder** a la aplicación: `http://servidor/masterwell01/`
2. **Iniciar sesión** con credenciales de Oracle
3. **Navegar** por las diferentes secciones:
   - Dashboard: Estadísticas generales
   - Well: Gestión de pozos
   - Explorer: Explorador de base de datos
   - Schema: Esquema WELL_HDR
   - SQL Plus: Consola SQL

## Solución de Problemas

### Error 500 - Internal Server Error
1. Verificar que mod_rewrite esté habilitado
2. Revisar logs de error de Apache
3. Verificar permisos de archivos

### Error de Conexión a Base de Datos
1. Verificar configuración en `config.php`
2. Comprobar conectividad de red
3. Verificar credenciales de usuario

### Página en Blanco
1. Habilitar display_errors en PHP
2. Verificar sintaxis de archivos PHP
3. Revisar logs de error

### Archivos No Encontrados
1. Verificar rutas en `.htaccess`
2. Comprobar estructura de directorios
3. Verificar permisos de archivos

## Archivos de Prueba

- `test.php`: Verificación básica del sistema
- `http://servidor/masterwell01/test.php`

## Logs

Los logs de error se guardan en:
- Apache: `/var/log/apache2/error.log` (Linux) o `logs/` (Windows)
- PHP: Configurado en `php.ini`

## Soporte

Para reportar problemas o solicitar soporte:
1. Revisar logs de error
2. Verificar configuración
3. Probar con `test.php`
4. Documentar pasos para reproducir el error

## Versión
1.0.0 - Versión inicial







