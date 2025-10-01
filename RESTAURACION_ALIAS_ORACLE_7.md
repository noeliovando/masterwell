# Restauración de Alias y Estructura Compatible con Oracle 7

## Resumen de Cambios

Se restauraron los alias de tabla en la consulta `getWellDetailsOptimized` y se implementó una estructura compatible con Oracle 7 para resolver el error `ORA-00904`.

## Problema Identificado

El usuario indicó que los alias de tabla funcionaban en versiones anteriores, por lo que el problema no era los alias en sí, sino la estructura de la consulta para Oracle 7.

## Solución Implementada

### 1. Restauración de Alias de Tabla

Se restauraron todos los alias de tabla (`WH.`, `NS.`, `WR.`, etc.) en la consulta principal:

```sql
SELECT
    WH.UWI, WH.WELL_NAME, WH.SHORT_NAME, WH.PLOT_NAME,
    WH.INITIAL_CLASS, WH.CLASS, WH.CURRENT_CLASS,
    -- ... más columnas con alias
    NS.LATITUDE, NS.LONGITUDE, NS.NODE_X, NS.NODE_Y, NS.DATUM,
    WR.REMARKS, WR.REMARKS_TYPE,
    WA.WELL_ALIAS,
    GP.DESCRIPTION AS GEOLOGIC_PROVINCE_DESC,
    -- ... más columnas de otras tablas
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
```

### 2. Estructura Compatible con Oracle 7

- **Outer Joins**: Se usa la sintaxis `(+)` en lugar de `LEFT JOIN`
- **Lista de Tablas**: Se estructura como lista separada por comas en lugar de `JOIN`
- **Condiciones de Join**: Se colocan en la cláusula `WHERE` con la sintaxis `(+)`

### 3. Condiciones de Outer Join

```sql
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
  AND L.LEASE_ID(+) = WH.LEASE_NO
```

### 4. Eliminación de Consultas Separadas

Se eliminaron todas las consultas separadas que obtenían datos adicionales, ya que ahora la consulta principal incluye todos los datos necesarios a través de outer joins.

### 5. Descripción del Distrito

Se mantiene una consulta separada para la descripción del distrito para evitar problemas con `SUBSTR` en outer joins:

```sql
SELECT DESCRIPTION 
FROM CODES.R_ELEMENT 
WHERE ELEMENT_ID = :district_id 
  AND ELEMENT_TYPE = 'DISTRICT'
```

## Archivos Modificados

### `models/Well.php`
- **Método**: `getWellDetailsOptimized()`
- **Cambios**:
  - Restauración de alias de tabla
  - Implementación de estructura Oracle 7
  - Eliminación de consultas separadas
  - Simplificación del código

### `test_alias_query.php` (Nuevo)
- **Propósito**: Probar la consulta con alias y estructura Oracle 7
- **Funcionalidades**:
  - Prueba de consulta completa con alias
  - Prueba de consulta simple con alias
  - Diagnóstico de errores específicos de Oracle 7

## Beneficios Esperados

1. **Compatibilidad Total**: Estructura 100% compatible con Oracle 7
2. **Rendimiento Mejorado**: Una sola consulta en lugar de múltiples
3. **Mantenimiento de Alias**: Se mantienen los alias que funcionaban anteriormente
4. **Código Más Limpio**: Eliminación de consultas redundantes

## Pruebas Recomendadas

1. **Acceder a `test_alias_query.php`** desde el navegador para verificar la consulta
2. **Probar la búsqueda de pozos** en la aplicación principal
3. **Seleccionar un pozo** para verificar que se cargan los detalles sin errores
4. **Verificar en los logs** si hay errores `ORA-00904` o `ORA-00933`

## Posibles Problemas

Si aún persisten errores, podrían deberse a:

1. **Nombres de columnas incorrectos** en alguna tabla
2. **Problemas de permisos** en las tablas
3. **Estructura de datos** diferente a la esperada
4. **Configuración específica** de Oracle 7

## Próximos Pasos

1. Probar la consulta con `test_alias_query.php`
2. Verificar el funcionamiento en la aplicación principal
3. Si hay errores, analizar los logs para identificar el problema específico
4. Ajustar la consulta según sea necesario

## Comandos SQL Útiles para Oracle 7

```sql
-- Verificar estructura de tabla
DESC PDVSA.WELL_HDR;

-- Verificar permisos
SELECT * FROM USER_TAB_PRIVS WHERE TABLE_NAME = 'WELL_HDR';

-- Verificar índices
SELECT INDEX_NAME, COLUMN_NAME FROM USER_IND_COLUMNS 
WHERE TABLE_NAME = 'WELL_HDR' ORDER BY INDEX_NAME, COLUMN_POSITION;
``` 