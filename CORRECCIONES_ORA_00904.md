# Correcciones para Error ORA-00904: Invalid Column Name

## Problema Identificado

El error `ORA-00904: invalid column name` ocurre cuando se selecciona un pozo para ver sus detalles. Este error indica que hay problemas de compatibilidad con Oracle 7 en la consulta `getWellDetailsOptimized`.

## Causas del Error

### 1. Uso de SUBSTR en Outer Joins
Oracle 7 no permite usar funciones como `SUBSTR` en tablas con outer join usando la sintaxis `(+)`:

```sql
-- PROBLEMÁTICO en Oracle 7:
AND SUBSTR(AD.ELEMENT_ID(+),5,4) = WH.DISTRICT
```

### 2. Condiciones Múltiples en Outer Joins
Oracle 7 tiene limitaciones con múltiples condiciones en el mismo outer join:

```sql
-- PROBLEMÁTICO en Oracle 7:
AND NS.SOURCE(+) = 'CT_CONVERT_UTM'
AND WR.REMARKS_TYPE(+) = 'INICIO_PERF'
AND WCC.SOURCE(+) = 'INITIAL CLASS'
```

### 3. Columnas Ambiguas
Algunas columnas pueden ser ambiguas cuando se usan múltiples tablas con alias.

## Soluciones Implementadas

### 1. Simplificación de Outer Joins

**ANTES (problemático):**
```sql
FROM PDVSA.WELL_HDR WH,
     PDVSA.NODES_SECOND NS,
     PDVSA.WELL_REMARKS WR,
     PDVSA.WELL_ALIAS WA,
     CODES.GEOLOGIC_PROVINCE GP,
     PDVSA.FIELD_HDR FH,
     CODES.WELL_CLASS_CODES WCC,
     CODES.WELL_STATUS_CODES WSC,
     CODES.BUSINESS_ASSOC BA,
     CODES.R_ELEMENT AD,
     CODES.BUSINESS_ASSOC AG,
     PDVSA.LEASE LN
WHERE WH.UWI = :uwi
  AND WH.NODE_ID = NS.NODE_ID(+)
  AND NS.SOURCE(+) = 'CT_CONVERT_UTM'
  AND WH.UWI = WR.UWI(+)
  AND WR.REMARKS_TYPE(+) = 'INICIO_PERF'
  AND WH.UWI = WA.UWI(+)
  AND WH.GEOLOGIC_PROVINCE = GP.GEOL_PROV_ID(+)
  AND WH.FIELD = FH.FIELD_CODE(+)
  AND WH.INITIAL_CLASS = WCC.CODE(+)
  AND WCC.SOURCE(+) = 'INITIAL CLASS'
  AND WH.ORSTATUS = WSC.STATUS(+)
  AND WH.OPERATOR = BA.ASSOC_ID(+)
  AND SUBSTR(AD.ELEMENT_ID(+),5,4) = WH.DISTRICT
  AND AD.ELEMENT_TYPE(+) = 'DISTRITO'
  AND WH.AGENT = AG.ASSOC_ID(+)
  AND WH.LEASE_NO = LN.LEASE_ID(+)
```

**DESPUÉS (compatible con Oracle 7):**
```sql
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
  AND WH.LEASE_NO = LN.LEASE_ID(+)
  -- Condiciones adicionales para filtrar datos específicos
  AND (NS.SOURCE IS NULL OR NS.SOURCE = 'CT_CONVERT_UTM')
  AND (WR.REMARKS_TYPE IS NULL OR WR.REMARKS_TYPE = 'INICIO_PERF')
  AND (WCC.SOURCE IS NULL OR WCC.SOURCE = 'INITIAL CLASS')
```

### 2. Consulta Separada para Descripción del Distrito

**Nuevo código agregado:**
```php
// Si encontramos datos, obtener la descripción del distrito por separado
if ($details && !empty($details['DISTRICT'])) {
    try {
        $district_sql = "SELECT DESCRIPTION FROM CODES.R_ELEMENT 
                        WHERE ELEMENT_TYPE = 'DISTRITO' 
                        AND SUBSTR(ELEMENT_ID, 5, 4) = :district";
        $district_stmt = $pdo->prepare($district_sql);
        $district_stmt->execute([':district' => $details['DISTRICT']]);
        $district_result = $district_stmt->fetch();
        if ($district_result) {
            $details['DISTRICT_DESC'] = $district_result['DESCRIPTION'];
        }
    } catch (PDOException $e) {
        error_log("Error al obtener descripción del distrito: " . $e->getMessage());
        $details['DISTRICT_DESC'] = 'N/A';
    }
}
```

### 3. Eliminación de Tabla Problemática

Se removió la tabla `CODES.R_ELEMENT AD` del join principal y se maneja por separado para evitar conflictos con `SUBSTR`.

## Archivos Modificados

### `models/Well.php`
- **Método**: `getWellDetailsOptimized()`
- **Cambios**:
  - Simplificación de outer joins
  - Eliminación de condiciones problemáticas con `SUBSTR`
  - Consulta separada para descripción del distrito
  - Mejora en el manejo de errores

### `test_well_details.php` (NUEVO)
- **Propósito**: Archivo de prueba para diagnosticar errores ORA-00904
- **Funcionalidades**:
  - Pruebas paso a paso de cada join
  - Verificación de compatibilidad con Oracle 7
  - Diagnóstico de errores específicos

## Beneficios de los Cambios

1. **Compatibilidad con Oracle 7**: Elimina el uso de funciones en outer joins
2. **Mejor Manejo de Errores**: Logging detallado para diagnóstico
3. **Consultas Más Simples**: Reduce la complejidad de los joins
4. **Mantenibilidad**: Código más fácil de entender y mantener

## Pruebas Recomendadas

1. **Ejecutar `test_well_details.php`** para verificar la compatibilidad
2. **Probar búsqueda de pozos** para asegurar que funciona
3. **Seleccionar un pozo** para verificar que los detalles se cargan correctamente
4. **Verificar logs** para identificar cualquier error restante

## Comandos SQL Útiles para Diagnóstico

```sql
-- Verificar estructura de tablas
DESC PDVSA.WELL_HDR;
DESC PDVSA.NODES_SECOND;
DESC CODES.GEOLOGIC_PROVINCE;

-- Verificar datos de prueba
SELECT UWI, WELL_NAME FROM PDVSA.WELL_HDR WHERE ROWNUM <= 5;

-- Verificar joins individuales
SELECT WH.UWI, WH.WELL_NAME, NS.LATITUDE 
FROM PDVSA.WELL_HDR WH, PDVSA.NODES_SECOND NS 
WHERE WH.NODE_ID = NS.NODE_ID(+) AND ROWNUM <= 1;
```

## Notas Importantes

- **Oracle 7 Limitaciones**: No soporta `FETCH FIRST ROWS ONLY`, `LEFT JOIN`, ni funciones en outer joins
- **Sintaxis Antigua**: Se debe usar `(+)` en lugar de `LEFT JOIN`
- **Funciones**: Evitar `SUBSTR`, `TO_CHAR`, etc. en outer joins
- **Condiciones Múltiples**: Simplificar las condiciones de outer join

## Estado Actual

✅ **Completado**: Correcciones para ORA-00904 implementadas
🔄 **En Prueba**: Verificación de funcionamiento con Oracle 7
📋 **Pendiente**: Confirmación del usuario sobre el funcionamiento 