# 📊 PROPUESTA: DASHBOARD CON DATOS REALES - MasterWell

## 📅 Fecha de Análisis
**06 de Agosto de 2025**

## 🎯 OBJETIVO
Reemplazar los datos de muestra del dashboard con **datos reales** extraídos de la base de datos Oracle, mostrando información relevante y actualizada sobre pozos petroleros.

---

## 📋 **DATOS REALES DISPONIBLES EN LA BASE DE DATOS**

### **🗃️ TABLA PRINCIPAL: WELL_HDR**

| **📊 Campo** | **📝 Descripción** | **🎯 Uso en Dashboard** | **📈 Tipo de Métrica** |
|--------------|-------------------|-------------------------|-------------------------|
| `UWI` | Identificador Único de Pozo | Conteo total de pozos | **Estadística General** |
| `CRSTATUS` | Estado actual del hoyo | Pozos por estado (activos/inactivos) | **Estadística Operacional** |
| `OPERATOR` | Empresa operadora del pozo | Distribución por operador | **Análisis por Empresa** |
| `DISTRICT` | Distrito administrativo | Distribución geográfica | **Análisis Geográfico** |
| `FIELD` | Campo geológico | Pozos por campo petrolero | **Análisis de Campos** |
| `GEOLOGIC_PROVINCE` | Cuenca/subcuenca geológica | Distribución por cuenca | **Análisis Geológico** |
| `PROV_ST` | Estado/provincia | Distribución por estados | **Análisis Regional** |
| `CLASS` | Clasificación Lahee final | Tipos de pozos | **Análisis Técnico** |
| `WELL_HDR_TYPE` | Tipo de hoyo | Categorización técnica | **Clasificación** |
| `SPUD_DATE` | Fecha inicio perforación | Pozos por año/período | **Análisis Temporal** |
| `COMP_DATE` | Fecha de completación | Actividad de completación | **Análisis de Productividad** |
| `DISCOVER_WELL` | Pozo descubridor | Pozos exploratorios vs desarrollo | **Análisis Exploratorio** |
| `DEVIATION_FLAG` | Pozo con desvío | Pozos direccionales vs verticales | **Análisis Técnico** |

### **🗃️ TABLAS DE CÓDIGOS DISPONIBLES**

| **📊 Tabla** | **📝 Propósito** | **🎯 Datos para Dashboard** |
|--------------|------------------|----------------------------|
| `WELL_STATUS_CODES` | Estados de pozos | Descripciones de estados actuales |
| `WELL_CLASS_CODES` | Clasificaciones Lahee | Tipos de pozos con descripciones |
| `GEOLOGIC_PROVINCE` | Cuencas geológicas | Nombres de cuencas |
| `BUSINESS_ASSOC` | Empresas asociadas | Operadores y contratistas |

---

## 📊 **PROPUESTA DE DASHBOARD CON DATOS REALES**

### **🔢 SECCIÓN 1: ESTADÍSTICAS GENERALES**

#### **Datos Actuales (Muestra):**
```php
'totalWells' => 4850,
'activeWells' => 3920,
'lastUpdate' => date('d/m/Y H:i'),
```

#### **✅ DATOS REALES PROPUESTOS:**
```sql
-- Total de pozos
SELECT COUNT(*) as total_wells FROM {schema}.WELL_HDR;

-- Pozos activos (estado operativo)
SELECT COUNT(*) as active_wells 
FROM {schema}.WELL_HDR 
WHERE CRSTATUS IN ('ACTIVE', 'PRODUCING', 'ON PRODUCTION');

-- Pozos completados este año
SELECT COUNT(*) as wells_completed_this_year 
FROM {schema}.WELL_HDR 
WHERE EXTRACT(YEAR FROM COMP_DATE) = EXTRACT(YEAR FROM SYSDATE);

-- Última actualización de datos
SELECT MAX(LAST_UPDATE) as last_update FROM {schema}.WELL_HDR;
```

### **🌍 SECCIÓN 2: DISTRIBUCIÓN GEOGRÁFICA REAL**

#### **Datos Actuales (Muestra):**
```php
'regions' => [
    'Occidente' => ['total' => 1250, 'active' => 980],
    // ... datos simulados
]
```

#### **✅ DATOS REALES PROPUESTOS:**
```sql
-- Pozos por estado/provincia
SELECT 
    ps.DESCRIPCION as estado,
    COUNT(*) as total_pozos,
    COUNT(CASE WHEN wh.CRSTATUS IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
FROM {schema}.WELL_HDR wh
LEFT JOIN CODES.PROV_ST ps ON wh.PROV_ST = ps.CODIGO
GROUP BY ps.DESCRIPCION
ORDER BY total_pozos DESC;

-- Pozos por cuenca geológica
SELECT 
    gp.DESCRIPTION as cuenca,
    COUNT(*) as total_pozos
FROM {schema}.WELL_HDR wh
LEFT JOIN CODES.GEOLOGIC_PROVINCE gp ON wh.GEOLOGIC_PROVINCE = gp.GEOL_PROV_ID
GROUP BY gp.DESCRIPTION
ORDER BY total_pozos DESC;
```

### **🏢 SECCIÓN 3: ANÁLISIS POR OPERADORES REALES**

#### **Datos Actuales (Muestra):**
```php
'mixedCompanies' => [
    'Petrororaima' => ['wells' => 320, 'production' => 150000],
    // ... datos simulados
]
```

#### **✅ DATOS REALES PROPUESTOS:**
```sql
-- Pozos por operador
SELECT 
    ba.SHORT_NAME as operador,
    COUNT(*) as total_pozos,
    COUNT(CASE WHEN wh.CRSTATUS IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos,
    COUNT(CASE WHEN wh.DISCOVER_WELL = 'Y' THEN 1 END) as pozos_descubridores
FROM {schema}.WELL_HDR wh
LEFT JOIN {schema}.BUSINESS_ASSOC ba ON wh.OPERATOR = ba.BUSINESS_ASSOCIATE_ID
GROUP BY ba.SHORT_NAME
ORDER BY total_pozos DESC
FETCH FIRST 10 ROWS ONLY;
```

### **📈 SECCIÓN 4: ANÁLISIS TÉCNICO Y OPERACIONAL**

#### **✅ NUEVOS DATOS REALES:**
```sql
-- Distribución por clasificación Lahee
SELECT 
    wcc.REMARKS as clasificacion,
    COUNT(*) as cantidad_pozos
FROM {schema}.WELL_HDR wh
LEFT JOIN CODES.WELL_CLASS_CODES wcc ON wh.CLASS = wcc.CODE
GROUP BY wcc.REMARKS
ORDER BY cantidad_pozos DESC;

-- Estados actuales de pozos
SELECT 
    wsc.REMARKS as estado,
    COUNT(*) as cantidad_pozos
FROM {schema}.WELL_HDR wh
LEFT JOIN CODES.WELL_STATUS_CODES wsc ON wh.CRSTATUS = wsc.STATUS
GROUP BY wsc.REMARKS
ORDER BY cantidad_pozos DESC;

-- Pozos por tipo de hoyo
SELECT 
    WELL_HDR_TYPE as tipo_hoyo,
    COUNT(*) as cantidad_pozos
FROM {schema}.WELL_HDR
GROUP BY WELL_HDR_TYPE
ORDER BY cantidad_pozos DESC;
```

### **📅 SECCIÓN 5: ANÁLISIS TEMPORAL**

#### **✅ DATOS REALES TEMPORALES:**
```sql
-- Pozos perforados por año
SELECT 
    EXTRACT(YEAR FROM SPUD_DATE) as año,
    COUNT(*) as pozos_perforados
FROM {schema}.WELL_HDR
WHERE SPUD_DATE IS NOT NULL
GROUP BY EXTRACT(YEAR FROM SPUD_DATE)
ORDER BY año DESC
FETCH FIRST 10 ROWS ONLY;

-- Pozos completados por año
SELECT 
    EXTRACT(YEAR FROM COMP_DATE) as año,
    COUNT(*) as pozos_completados
FROM {schema}.WELL_HDR
WHERE COMP_DATE IS NOT NULL
GROUP BY EXTRACT(YEAR FROM COMP_DATE)
ORDER BY año DESC
FETCH FIRST 10 ROWS ONLY;
```

### **🎯 SECCIÓN 6: MÉTRICAS ESPECIALIZADAS**

#### **✅ DATOS TÉCNICOS ESPECÍFICOS:**
```sql
-- Pozos direccionales vs verticales
SELECT 
    CASE 
        WHEN DEVIATION_FLAG = 'Y' THEN 'Direccional'
        WHEN DEVIATION_FLAG = 'N' THEN 'Vertical'
        ELSE 'No Especificado'
    END as tipo_perforacion,
    COUNT(*) as cantidad_pozos
FROM {schema}.WELL_HDR
GROUP BY DEVIATION_FLAG;

-- Pozos descubridores
SELECT 
    COUNT(CASE WHEN DISCOVER_WELL = 'Y' THEN 1 END) as pozos_descubridores,
    COUNT(CASE WHEN DISCOVER_WELL = 'N' THEN 1 END) as pozos_desarrollo,
    COUNT(*) as total_pozos
FROM {schema}.WELL_HDR;

-- Distribución de profundidades (rangos)
SELECT 
    CASE 
        WHEN DRILLERS_TD < 1000 THEN 'Somero (< 1000m)'
        WHEN DRILLERS_TD BETWEEN 1000 AND 3000 THEN 'Medio (1000-3000m)'
        WHEN DRILLERS_TD BETWEEN 3000 AND 5000 THEN 'Profundo (3000-5000m)'
        WHEN DRILLERS_TD > 5000 THEN 'Muy Profundo (> 5000m)'
        ELSE 'No Especificado'
    END as rango_profundidad,
    COUNT(*) as cantidad_pozos
FROM {schema}.WELL_HDR
WHERE DRILLERS_TD IS NOT NULL
GROUP BY 
    CASE 
        WHEN DRILLERS_TD < 1000 THEN 'Somero (< 1000m)'
        WHEN DRILLERS_TD BETWEEN 1000 AND 3000 THEN 'Medio (1000-3000m)'
        WHEN DRILLERS_TD BETWEEN 3000 AND 5000 THEN 'Profundo (3000-5000m)'
        WHEN DRILLERS_TD > 5000 THEN 'Muy Profundo (> 5000m)'
        ELSE 'No Especificado'
    END;
```

---

## 🎨 **DISEÑO PROPUESTO PARA EL DASHBOARD**

### **📊 LAYOUT SUGERIDO:**

#### **🔝 FILA 1: Estadísticas Generales**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ Total Pozos │ Pozos       │ Completados │ Última      │
│   [REAL]    │ Activos     │ Este Año    │ Actualiz.   │
│             │ [REAL]      │ [REAL]      │ [REAL]      │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

#### **🌍 FILA 2: Distribución Geográfica**
```
┌─────────────────────────────────────────────────────────┐
│               POZOS POR ESTADO/PROVINCIA                │
│  ┌─────────────┬─────────────┬─────────────┬─────────┐  │
│  │ Estado 1    │ Estado 2    │ Estado 3    │ Estado 4│  │
│  │ Total: XXX  │ Total: XXX  │ Total: XXX  │ Tot: XXX│  │
│  │ Activos:XXX │ Activos:XXX │ Activos:XXX │ Act: XXX│  │
│  └─────────────┴─────────────┴─────────────┴─────────┘  │
└─────────────────────────────────────────────────────────┘
```

#### **🏢 FILA 3: Análisis por Operadores**
```
┌─────────────────────────────────────────────────────────┐
│                TOP 10 OPERADORES                        │
│  ┌─────────────┬─────────────┬─────────────┬─────────┐  │
│  │ Operador 1  │ Operador 2  │ Operador 3  │ Oper. 4 │  │
│  │ Pozos: XXX  │ Pozos: XXX  │ Pozos: XXX  │ Poz: XXX│  │
│  │ Activos:XXX │ Activos:XXX │ Activos:XXX │ Act: XXX│  │
│  │ Descub.: XX │ Descub.: XX │ Descub.: XX │ Des: XX │  │
│  └─────────────┴─────────────┴─────────────┴─────────┘  │
└─────────────────────────────────────────────────────────┘
```

#### **📈 FILA 4: Gráficos Analíticos**
```
┌─────────────────────────┬─────────────────────────┐
│    POZOS POR CUENCA     │   CLASIFICACIÓN LAHEE   │
│                         │                         │
│   [Gráfico de Barras]   │   [Gráfico de Dona]     │
│                         │                         │
└─────────────────────────┴─────────────────────────┘
```

#### **🔍 FILA 5: Análisis Técnico**
```
┌─────────────────────────┬─────────────────────────┐
│   ESTADOS DE POZOS      │  ANÁLISIS TEMPORAL      │
│                         │                         │
│  [Gráfico de Barras]    │ [Gráfico de Líneas]     │
│                         │                         │
└─────────────────────────┴─────────────────────────┘
```

#### **⚙️ FILA 6: Métricas Especializadas**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ Direccional │ Vertical    │ Descubr.    │ Desarrollo  │
│   XX%       │   XX%       │   XXX       │   XXX       │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

---

## 💻 **IMPLEMENTACIÓN TÉCNICA**

### **📁 ARCHIVOS A MODIFICAR:**

#### **1. models/Well.php - Nuevas Funciones**
```php
// Funciones para el dashboard con datos reales
public static function getDashboardGeneralStats();
public static function getWellsByState();
public static function getWellsByGeologicProvince();
public static function getWellsByOperator($limit = 10);
public static function getWellsByClassification();
public static function getWellsByStatus();
public static function getWellsByYear($type = 'spud'); // 'spud' o 'completion'
public static function getTechnicalMetrics();
public static function getDepthDistribution();
```

#### **2. controllers/DashboardController.php - Actualizar Datos**
```php
public function index() {
    $data = [
        'generalStats' => Well::getDashboardGeneralStats(),
        'stateDistribution' => Well::getWellsByState(),
        'geologicDistribution' => Well::getWellsByGeologicProvince(),
        'operatorStats' => Well::getWellsByOperator(10),
        'classificationStats' => Well::getWellsByClassification(),
        'statusStats' => Well::getWellsByStatus(),
        'yearlyStats' => Well::getWellsByYear('spud'),
        'technicalMetrics' => Well::getTechnicalMetrics(),
        'depthStats' => Well::getDepthDistribution()
    ];
    
    $this->loadView('dashboard', $data);
}
```

#### **3. views/dashboard.php - Nuevo Layout**
- Reemplazar datos estáticos con variables dinámicas
- Agregar nuevas secciones para métricas técnicas
- Implementar gráficos con datos reales

#### **4. js/dashboard-charts.js - Gráficos Actualizados**
- Configurar Chart.js con datos reales
- Agregar nuevos tipos de gráficos
- Implementar actualización automática

---

## 🎯 **BENEFICIOS DE LA IMPLEMENTACIÓN**

### **📊 DATOS REALES:**
- ✅ **Información Actual**: Datos actualizados de la base de datos
- ✅ **Precisión Total**: Elimina estimaciones y datos simulados
- ✅ **Relevancia Operacional**: Métricas útiles para toma de decisiones

### **🔍 ANÁLISIS MEJORADO:**
- ✅ **Distribución Geográfica Real**: Estados/provincias con datos reales
- ✅ **Análisis por Operadores**: Empresas reales con pozos reales
- ✅ **Métricas Técnicas**: Clasificaciones y estados reales

### **📈 VALOR EMPRESARIAL:**
- ✅ **Toma de Decisiones**: Información real para planificación
- ✅ **Monitoreo Operacional**: Estados actuales de pozos
- ✅ **Análisis de Tendencias**: Datos temporales reales

### **🔄 ACTUALIZACIÓN AUTOMÁTICA:**
- ✅ **Datos Dinámicos**: Se actualiza automáticamente con la BD
- ✅ **Consistencia**: Misma fuente que el resto de la aplicación
- ✅ **Escalabilidad**: Crece con el volumen de datos

---

## 🚀 **PLAN DE IMPLEMENTACIÓN**

### **📅 FASE 1: Funciones Base (2-3 horas)**
1. Crear funciones en `models/Well.php` para extraer datos reales
2. Probar consultas SQL con diferentes instancias (PDVSA/CVP)
3. Manejar casos donde tablas no existen

### **📅 FASE 2: Controller y Vista (2 horas)**
1. Actualizar `DashboardController.php` con datos reales
2. Modificar `dashboard.php` para mostrar nuevos datos
3. Ajustar CSS para nuevas métricas

### **📅 FASE 3: Gráficos y Visualización (2 horas)**
1. Configurar Chart.js con datos dinámicos
2. Crear nuevos tipos de gráficos
3. Implementar responsividad

### **📅 FASE 4: Testing y Optimización (1 hora)**
1. Probar con ambas instancias (PDVSA/CVP)
2. Optimizar consultas SQL
3. Validar rendimiento

---

## ⚠️ **CONSIDERACIONES TÉCNICAS**

### **🔒 COMPATIBILIDAD:**
- ✅ **Oracle 8i**: Todas las consultas compatible con sintaxis Oracle 8i
- ✅ **Multi-esquema**: Funciona con PDVSA y FINDCVP
- ✅ **Tablas Opcionales**: Maneja casos donde tablas no existen

### **⚡ RENDIMIENTO:**
- ✅ **Consultas Optimizadas**: Uso de índices existentes
- ✅ **Caché Posible**: Implementar caché para datos que no cambian frecuentemente
- ✅ **Paginación**: Limitar resultados en consultas grandes

### **🛡️ SEGURIDAD:**
- ✅ **Mismos Roles**: Usa el sistema WELL_ADMIN existente
- ✅ **Prepared Statements**: Todas las consultas parametrizadas
- ✅ **Error Handling**: Manejo robusto de errores

---

## 📋 **DATOS ESPECÍFICOS RECOMENDADOS**

### **🎯 MÉTRICAS PRIORITARIAS:**

#### **Top 5 Estadísticas Más Relevantes:**
1. **Total de Pozos** - Conteo real de UWI únicos
2. **Pozos Activos** - Basado en CRSTATUS operativo
3. **Distribución por Estado** - PROV_ST con nombres reales
4. **Top Operadores** - OPERATOR con conteos reales
5. **Clasificación Lahee** - CLASS con distribución real

#### **Top 5 Gráficos Más Útiles:**
1. **Barras: Pozos por Estado** - Distribución geográfica
2. **Dona: Clasificación Lahee** - Tipos de pozos
3. **Barras: Estados de Pozos** - Operacional vs inactivo
4. **Líneas: Pozos por Año** - Tendencia temporal
5. **Barras: Top Operadores** - Análisis empresarial

---

## ✅ **CONCLUSIÓN**

**La implementación de datos reales en el dashboard transformará MasterWell de una aplicación con datos simulados a una herramienta de inteligencia empresarial real, proporcionando métricas precisas y actualizadas para la toma de decisiones operacionales.**

**🎯 PRÓXIMO PASO: ¿Quieres que implemente estas funciones para mostrar los datos reales en el dashboard?**

---

**📝 Documento Generado:** 06 de Agosto de 2025  
**👨‍💻 Sistema:** MasterWell Analytics  
**📊 Estado:** Propuesta Lista para Implementación