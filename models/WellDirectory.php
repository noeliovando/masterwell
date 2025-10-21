<?php
// models/WellDirectory.php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

class WellDirectory
{
    /* ==============================
     *  Config / cache opcional
     * ============================== */
    private static array $cache = [];
    private static int   $cache_limit = 100;

    private static array $dateColumns = ['SPUD_DATE','FIN_DRILL','RIGREL','COMP_DATE','ONINJECT','ONPROD'];

    private static array $allowedUpdateColumns = [
        'WELL_NAME','SHORT_NAME','PLOT_NAME','GOVT_ASSIGNED_NO',
        'INITIAL_CLASS','CLASS','CURRENT_CLASS','ORSTATUS','CRSTATUS',
        'COUNTRY','GEOLOGIC_PROVINCE','PROV_ST','COUNTY','FIELD','BLOCK_ID','LOCATION_TABLE',
        'SPUD_DATE','FIN_DRILL','RIGREL','COMP_DATE','ONINJECT','ONPROD',
        'DISCOVER_WELL','DEVIATION_FLAG','PLOT_SYMBOL',
        'WELL_HDR_TYPE','WELL_NUMBER','PARENT_UWI','TIE_IN_UWI',
        'PRIMARY_SOURCE','CONTRACTOR','RIG_NO','RIG_NAME','HOLE_DIRECTION',
        'OPERATOR','DISTRICT','AGENT','LEASE_NO','LEASE_NAME','LICENSEE',
        'DRILLERS_TD','TVD','LOG_TD','LOG_TVD','PLUGBACK_TD','WHIPSTOCK_DEPTH','WATER_DEPTH',
        'ELEVATION_REF','ELEVATION','GROUND_ELEVATION','FORM_AT_TD',
        'LATITUDE','LONGITUDE','NODE_X','NODE_Y','DATUM','REMARKS','REMARKS_TYPE'
    ];

    private static array $numericColumns = [
        'DRILLERS_TD','TVD','LOG_TD','LOG_TVD','PLUGBACK_TD','WHIPSTOCK_DEPTH','WATER_DEPTH',
        'ELEVATION','GROUND_ELEVATION','LATITUDE','LONGITUDE','NODE_X','NODE_Y'
    ];

    /** Columnas base para el listado */
    private array $selectColumns = [
        'UWI',
        'WELL_NAME',
        'GOVT_ASSIGNED_NO',
        'OPERATOR',
        'FIELD',
        'DISTRICT',
        'LOCATION_TABLE',
        'AGENT',        // Unidad de Explotación (código)
        'SPUD_DATE'
    ];

    /* =========================================
     * Helpers privados (reutilizables en estáticos)
     * ========================================= */

    /** Expresión para igualar ignorando espacios, tabs y NBSP */
    private static function stripExpr(string $placeholder = '%s'): string
    {
        // REPLACE(... ' ', ''), REPLACE(... CHR(9) tab, ''), REPLACE(... CHR(160) NBSP, '')
        return "REPLACE(REPLACE(REPLACE({$placeholder}, ' ', ''), CHR(9), ''), CHR(160), '')";
    }

    /** Verifica existencia de tabla en el schema dado */
    private static function tableExists(\PDO $pdo, string $table, ?string $schema = null): bool
    {
        $table  = strtoupper(trim($table));
        $schema = $schema ? strtoupper(trim($schema)) : null;

        try {
            if ($schema) {
                $sql = "SELECT 1 FROM ALL_TABLES WHERE OWNER = :own AND TABLE_NAME = :tab";
                $st  = $pdo->prepare($sql);
                $st->execute([':own' => $schema, ':tab' => $table]);
            } else {
                $sql = "SELECT 1 FROM USER_TABLES WHERE TABLE_NAME = :tab";
                $st  = $pdo->prepare($sql);
                $st->execute([':tab' => $table]);
            }
            return (bool)$st->fetchColumn();
        } catch (\PDOException $e) {
            error_log("tableExists error: " . $e->getMessage());
            return false;
        }
    }

    /* =====================
     *  LISTADO / BÚSQUEDA
     * ===================== */

    /**
     * Búsqueda con filtros + paginación (ROWNUM) – método de instancia
     */
    public function search(array $filters, int $page = 1, int $perPage = 25, string $sort = 'UWI', string $dir = 'ASC'): array
    {
        $pdo = get_db_connection();
        if (!$pdo) {
            return ['rows' => [], 'total' => 0, 'page' => 1, 'perPage' => $perPage, 'pages' => 1, 'sort' => $sort, 'dir' => $dir];
        }

        $main_schema  = get_main_schema();

        // ====== VALIDACIÓN DE ORDEN ======
        $sort = strtoupper(trim($sort ?: 'UWI'));
        $dir  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        // Lista blanca de campos ordenables (incluye alias FIELD_NAME)
        $allowedSort = [
            'UWI','WELL_NAME','GOVT_ASSIGNED_NO','OPERATOR',
            'FIELD','FIELD_NAME','DISTRICT','LOCATION_TABLE','AGENT','SPUD_DATE'
        ];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'UWI';
        }

        // Mapa a columnas reales (o alias en SELECT)
        $sortMap = [
            'UWI'              => 'WH.UWI',
            'WELL_NAME'        => 'WH.WELL_NAME',
            'GOVT_ASSIGNED_NO' => 'WH.GOVT_ASSIGNED_NO',
            'OPERATOR'         => 'WH.OPERATOR',
            'FIELD'            => 'WH.FIELD',
            'FIELD_NAME'       => 'FIELD_NAME',      // alias calculado en el SELECT
            'DISTRICT'         => 'WH.DISTRICT',
            'LOCATION_TABLE'   => 'WH.LOCATION_TABLE',
            'AGENT'            => 'WH.AGENT',
            'SPUD_DATE'        => 'WH.SPUD_DATE',
        ];
        $orderCol = $sortMap[$sort] ?? 'WH.UWI';

        // ====== WHERE dinámico ======
        $where  = [];
        $params = [];

        if (!empty($filters['uwi'])) {
            $where[] = 'UPPER(WH.UWI) LIKE :uwi';
            $params[':uwi'] = '%' . strtoupper(trim($filters['uwi'])) . '%';
        }
        if (!empty($filters['location'])) {
            $where[] = 'UPPER(WH.LOCATION_TABLE) LIKE :location';
            $params[':location'] = '%' . strtoupper(trim($filters['location'])) . '%';
        }
        if (!empty($filters['exploitation_unit'])) {
            $where[] = 'UPPER(WH.AGENT) LIKE :agent';
            $params[':agent'] = '%' . strtoupper(trim($filters['exploitation_unit'])) . '%';
        }
        if (!empty($filters['operator'])) {
            $where[] = 'UPPER(WH.OPERATOR) LIKE :operator';
            $params[':operator'] = '%' . strtoupper(trim($filters['operator'])) . '%';
        }
        if (!empty($filters['field'])) {
            $where[] = 'UPPER(WH.FIELD) LIKE :field';
            $params[':field'] = '%' . strtoupper(trim($filters['field'])) . '%';
        }
        if (!empty($filters['district'])) {
            $where[] = 'UPPER(WH.DISTRICT) LIKE :district';
            $params[':district'] = '%' . strtoupper(trim($filters['district'])) . '%';
        }
        if (!empty($filters['gov'])) {
            $where[] = 'UPPER(WH.GOVT_ASSIGNED_NO) LIKE :gov';
            $params[':gov'] = '%' . strtoupper(trim($filters['gov'])) . '%';
        }
        if (!empty($filters['spud_from'])) {
            $where[] = "WH.SPUD_DATE >= TO_DATE(:spud_from,'YYYY-MM-DD')";
            $params[':spud_from'] = $filters['spud_from'];
        }
        if (!empty($filters['spud_to'])) {
            $where[] = "WH.SPUD_DATE < TO_DATE(:spud_to,'YYYY-MM-DD') + 1";
            $params[':spud_to'] = $filters['spud_to'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Columnas base + alias FIELD_NAME con subconsulta rápida
        $cols = "
            WH.UWI, WH.WELL_NAME, WH.GOVT_ASSIGNED_NO, WH.OPERATOR, WH.FIELD,
            WH.DISTRICT, WH.LOCATION_TABLE, WH.AGENT, WH.SPUD_DATE,
            (SELECT FH.FIELD_NAME
            FROM {$main_schema}.FIELD_HDR FH
            WHERE FH.FIELD_CODE = WH.FIELD
                AND ROWNUM = 1) AS FIELD_NAME
        ";

        // Conteo total
        $countSql = "SELECT COUNT(*) AS C FROM {$main_schema}.WELL_HDR WH {$whereSql}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
        $stmtCount->execute();
        $total = (int)($stmtCount->fetch(\PDO::FETCH_ASSOC)['C'] ?? 0);

        // Paginación (ROWNUM)
        $offset = max(0, ($page - 1) * $perPage);
        $maxRow = $offset + $perPage;

        $sql = "
            SELECT * FROM (
                SELECT t.*, ROWNUM AS rn
                FROM (
                    SELECT {$cols}
                    FROM {$main_schema}.WELL_HDR WH
                    {$whereSql}
                    ORDER BY {$orderCol} {$dir}
                ) t
                WHERE ROWNUM <= :max_row
            )
            WHERE rn > :min_row
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':max_row', $maxRow, \PDO::PARAM_INT);
        $stmt->bindValue(':min_row', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => $perPage ? (int)ceil($total / $perPage) : 1,
            'sort'    => $sort,
            'dir'     => $dir
        ];
    }

    /* =====================
     *  DETALLES – ESTÁTICOS
     * ===================== */

    public static function getWellDetailsByUWI(string $uwi): array
    {
        $uwi = trim($uwi);
        $key = 'well_details_' . str_replace(' ', '', $uwi);

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $details = self::getWellDetailsOptimized($uwi);
        if (!isset($details['error'])) {
            if (count(self::$cache) >= self::$cache_limit) {
                array_shift(self::$cache); // LRU simple
            }
            self::$cache[$key] = $details;
        }
        return $details;
    }

    public static function getWellDetailsOptimized(string $uwi): array
    {
        $pdo = get_db_connection();
        if (!$pdo) {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }

        $uwi = trim($uwi);
        $details = [];
        $sql = '';
        try {
            $start_time  = microtime(true);
            $main_schema = get_main_schema();
            $codes_schema = get_codes_schema();

            // Verificación básica (ignorar espacios)
            $sql = "SELECT UWI, WELL_NAME
                    FROM {$main_schema}.WELL_HDR
                    WHERE " . self::stripExpr('UWI') . " = " . self::stripExpr(':uwi');
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            if (!$st->fetch(\PDO::FETCH_ASSOC)) {
                return ['error' => "Pozo no encontrado"];
            }

            // Datos base
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
                        WH.GROUND_ELEVATION, WH.FORM_AT_TD, WH.NODE_ID
                    FROM {$main_schema}.WELL_HDR WH
                    WHERE " . self::stripExpr('WH.UWI') . " = " . self::stripExpr(':uwi');
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $details = $st->fetch(\PDO::FETCH_ASSOC);

            if (!$details) {
                return ['error' => "No se encontraron datos del pozo"];
            }

            // Coordenadas desde NODES_SECOND (si hay NODE_ID)
            if (!empty($details['NODE_ID']) && $details['NODE_ID'] !== 'N/A') {
                try {
                    $sql = "SELECT LATITUDE, LONGITUDE, NODE_X, NODE_Y, DATUM
                            FROM {$main_schema}.NODES_SECOND
                            WHERE NODE_ID = :nid";
                    $st = $pdo->prepare($sql);
                    $st->execute([':nid' => $details['NODE_ID']]);
                    $coord = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($coord) {
                        $details['LATITUDE']  = $coord['LATITUDE'];
                        $details['LONGITUDE'] = $coord['LONGITUDE'];
                        $details['NODE_X']    = $coord['NODE_X'];
                        $details['NODE_Y']    = $coord['NODE_Y'];
                        $details['DATUM']     = $coord['DATUM'];
                    }
                } catch (\PDOException $e) {
                    error_log("coords error: " . $e->getMessage());
                }
            }

            // WELL_REMARKS si existe
            if (self::tableExists($pdo, 'WELL_REMARKS', $main_schema)) {
                try {
                    $sql = "SELECT REMARKS, REMARKS_TYPE
                            FROM {$main_schema}.WELL_REMARKS
                            WHERE " . self::stripExpr('UWI') . " = " . self::stripExpr(':uwi') . "
                              AND REMARKS_TYPE = 'INICIO_PERF'";
                    $st = $pdo->prepare($sql);
                    $st->execute([':uwi' => $uwi]);
                    $rm = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($rm) {
                        $details['REMARKS']      = $rm['REMARKS'];
                        $details['REMARKS_TYPE'] = $rm['REMARKS_TYPE'];
                    }
                } catch (\PDOException $e) {
                    error_log("remarks error: " . $e->getMessage());
                }
            } else {
                $details['REMARKS'] = $details['REMARKS_TYPE'] = 'N/A';
            }

            // WELL_ALIAS si existe
            if (self::tableExists($pdo, 'WELL_ALIAS', $main_schema)) {
                try {
                    $sql = "SELECT WELL_ALIAS
                            FROM {$main_schema}.WELL_ALIAS
                            WHERE " . self::stripExpr('UWI') . " = " . self::stripExpr(':uwi');
                    $st = $pdo->prepare($sql);
                    $st->execute([':uwi' => $uwi]);
                    $al = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($al) $details['WELL_ALIAS'] = $al['WELL_ALIAS'];
                } catch (\PDOException $e) {
                    error_log("alias error: " . $e->getMessage());
                }
            } else {
                $details['WELL_ALIAS'] = 'N/A';
            }

            // Descripciones de catálogos
            if (!empty($details['GEOLOGIC_PROVINCE'])) {
                try {
                    $sql = "SELECT DESCRIPTION FROM {$codes_schema}.GEOLOGIC_PROVINCE WHERE GEOL_PROV_ID = :id";
                    $st = $pdo->prepare($sql);
                    $st->execute([':id' => $details['GEOLOGIC_PROVINCE']]);
                    $row = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($row) $details['GEOLOGIC_PROVINCE_DESC'] = $row['DESCRIPTION'];
                } catch (\PDOException $e) { error_log("geo prov desc: ".$e->getMessage()); }
            }

            if (!empty($details['FIELD'])) {
                if (self::tableExists($pdo, 'FIELD_HDR', $main_schema)) {
                    try {
                        $sql = "SELECT FIELD_NAME FROM {$main_schema}.FIELD_HDR WHERE FIELD_CODE = :c";
                        $st = $pdo->prepare($sql);
                        $st->execute([':c' => $details['FIELD']]);
                        $row = $st->fetch(\PDO::FETCH_ASSOC);
                        if ($row) $details['FIELD_NAME'] = $row['FIELD_NAME'];
                    } catch (\PDOException $e) { error_log("field name: ".$e->getMessage()); }
                } else {
                    $details['FIELD_NAME'] = $details['FIELD'];
                }
            }

            if (!empty($details['CLASS'])) {
                try {
                    $sql = "SELECT DESCRIPTION
                            FROM {$codes_schema}.WELL_CLASS_CODES
                            WHERE CLASS_CODE = :cc AND SOURCE = 'CT_CONVERT_UTM'";
                    $st = $pdo->prepare($sql);
                    $st->execute([':cc' => $details['CLASS']]);
                    $row = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($row) $details['CLASS_DESC'] = $row['DESCRIPTION'];
                } catch (\PDOException $e) { error_log("class desc: ".$e->getMessage()); }
            }

            if (!empty($details['ORSTATUS'])) {
                try {
                    $sql = "SELECT DESCRIPTION
                            FROM {$codes_schema}.WELL_STATUS_CODES
                            WHERE STATUS_CODE = :sc AND SOURCE = 'CT_CONVERT_UTM'";
                    $st = $pdo->prepare($sql);
                    $st->execute([':sc' => $details['ORSTATUS']]);
                    $row = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($row) $details['STATUS_DESC'] = $row['DESCRIPTION'];
                } catch (\PDOException $e) { error_log("status desc: ".$e->getMessage()); }
            }

            if (!empty($details['LEASE_NO'])) {
                if (self::tableExists($pdo, 'LEASE', $main_schema)) {
                    try {
                        $sql = "SELECT LEASE_NAME FROM {$main_schema}.LEASE WHERE LEASE_ID = :id";
                        $st = $pdo->prepare($sql);
                        $st->execute([':id' => $details['LEASE_NO']]);
                        $row = $st->fetch(\PDO::FETCH_ASSOC);
                        if ($row) $details['LEASE_DESC'] = $row['LEASE_NAME'];
                    } catch (\PDOException $e) { error_log("lease name: ".$e->getMessage()); }
                } else {
                    $details['LEASE_DESC'] = $details['LEASE_NO'];
                }
            }

            if (!empty($details['DISTRICT'])) {
                try {
                    $sql = "SELECT DESCRIPTION
                            FROM {$codes_schema}.R_ELEMENT
                            WHERE ELEMENT_TYPE = 'DISTRICT' AND ELEMENT_ID = :id";
                    $st = $pdo->prepare($sql);
                    $st->execute([':id' => $details['DISTRICT']]);
                    $row = $st->fetch(\PDO::FETCH_ASSOC);
                    if ($row) $details['DISTRICT_DESC'] = $row['DESCRIPTION'];
                } catch (\PDOException $e) { error_log("district desc: ".$e->getMessage()); }
            }

            // Normalizar NULLs
            foreach ($details as $k => $v) {
                if ($v === null) $details[$k] = 'N/A';
            }

            // Campos DISPLAY
            $details = self::prepareDisplayFields($details);

            $execution_time = microtime(true) - $start_time;
            error_log("getWellDetailsOptimized UWI={$uwi} en " . number_format($execution_time,4) . "s");

            return $details;

        } catch (\PDOException $e) {
            error_log("getWellDetailsOptimized error: " . $e->getMessage() . " SQL: " . $sql);
            return ['error' => "Error en la consulta de detalles: " . $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    private static function prepareDisplayFields(array $details): array
    {
        $details['GEOLOGIC_PROVINCE_DISPLAY'] =
            (!empty($details['GEOLOGIC_PROVINCE']) && $details['GEOLOGIC_PROVINCE'] !== 'N/A')
            ? ($details['GEOLOGIC_PROVINCE'] . ' - ' . ($details['GEOLOGIC_PROVINCE_DESC'] ?? 'N/A'))
            : 'N/A';

        $details['FIELD_DISPLAY'] =
            (!empty($details['FIELD']) && $details['FIELD'] !== 'N/A')
            ? ($details['FIELD'] . ' - ' . ($details['FIELD_NAME'] ?? 'N/A'))
            : 'N/A';

        $details['INITIAL_CLASS_DISPLAY'] =
            (!empty($details['INITIAL_CLASS']) && $details['INITIAL_CLASS'] !== 'N/A')
            ? ($details['INITIAL_CLASS'] . ' - ' . ($details['INITIAL_CLASS_DESC'] ?? 'N/A'))
            : 'N/A';

        $details['ORSTATUS_DISPLAY'] =
            (!empty($details['ORSTATUS']) && $details['ORSTATUS'] !== 'N/A')
            ? ($details['ORSTATUS'] . ' - ' . ($details['STATUS_DESC'] ?? 'N/A'))
            : 'N/A';

        $details['OPERATOR_DISPLAY'] =
            (!empty($details['OPERATOR']) && $details['OPERATOR'] !== 'N/A')
            ? ($details['OPERATOR'] . ' - ' . ($details['OPERATOR_NAME'] ?? 'N/A'))
            : 'N/A';

        $details['DISTRICT_DISPLAY'] =
            (!empty($details['DISTRICT']) && $details['DISTRICT'] !== 'N/A')
            ? ($details['DISTRICT'] . ' - ' . ($details['DISTRICT_DESC'] ?? 'N/A'))
            : 'N/A';

        $details['AGENT_DISPLAY'] =
            (!empty($details['AGENT']) && $details['AGENT'] !== 'N/A')
            ? ($details['AGENT'] . ' - ' . ($details['AGENT_NAME'] ?? 'N/A'))
            : 'N/A';

        $details['LEASE_NO_DISPLAY'] =
            (!empty($details['LEASE_NO']) && $details['LEASE_NO'] !== 'N/A')
            ? ($details['LEASE_NO'] . ' - ' . ($details['LEASE_DESC'] ?? 'N/A'))
            : 'N/A';

        return $details;
    }

    public static function getCoordinatesByUWI(string $uwi): array
    {
        $pdo = get_db_connection();
        if (!$pdo) {
            return [
                'LONGITUD' => 'N/A','LATITUD'=>'N/A','CANOA_DATUM'=>'N/A',
                'CANOA_ESTE'=>'N/A','CANOA_NORTE'=>'N/A','CANOA_ORIGEN'=>'N/A',
                'REGVEN_DATUM'=>'N/A','REGVEN_ESTE'=>'N/A','REGVEN_NORTE'=>'N/A','REGVEN_ORIGEN'=>'N/A'
            ];
        }

        $main_schema = get_main_schema();
        $uwi = trim($uwi);
        try {
            // Simple: tomar de NODES_SECOND de esta instancia
            $sql = "SELECT ns.LATITUDE, ns.LONGITUDE, ns.NODE_X, ns.NODE_Y, ns.DATUM
                    FROM {$main_schema}.WELL_HDR wh, {$main_schema}.NODES_SECOND ns
                    WHERE " . self::stripExpr('wh.UWI') . " = " . self::stripExpr(':uwi') . "
                      AND ns.NODE_ID(+) = wh.NODE_ID
                      AND ns.SOURCE(+) = 'CT_CONVERT_UTM'";
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $r = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

            // Normalizar y mapear a claves esperadas por la vista
            $out = [
                'LONGITUD' => $r['LONGITUDE'] ?? 'N/A',
                'LATITUD'  => $r['LATITUDE']  ?? 'N/A',
                'CANOA_DATUM' => $r['DATUM'] ?? 'N/A',
                'CANOA_ESTE'  => $r['NODE_X'] ?? 'N/A',
                'CANOA_NORTE' => $r['NODE_Y'] ?? 'N/A',
                'CANOA_ORIGEN'=> 'N/A',
                'REGVEN_DATUM'=> 'N/A',
                'REGVEN_ESTE' => 'N/A',
                'REGVEN_NORTE'=> 'N/A',
                'REGVEN_ORIGEN'=> 'N/A',
            ];
            foreach ($out as $k => $v) if ($v === null) $out[$k] = 'N/A';
            return $out;

        } catch (\PDOException $e) {
            error_log("getCoordinatesByUWI error: " . $e->getMessage());
            return [
                'LONGITUD' => 'N/A','LATITUD'=>'N/A','CANOA_DATUM'=>'N/A',
                'CANOA_ESTE'=>'N/A','CANOA_NORTE'=>'N/A','CANOA_ORIGEN'=>'N/A',
                'REGVEN_DATUM'=>'N/A','REGVEN_ESTE'=>'N/A','REGVEN_NORTE'=>'N/A','REGVEN_ORIGEN'=>'N/A'
            ];
        } finally { $pdo = null; }
    }

    public static function getWellAliasByUWI(string $uwi): ?array
    {
        $pdo = get_db_connection();
        if (!$pdo) return null;

        try {
            $uwi = trim($uwi);
            $main_schema = get_main_schema();
            $sql = "SELECT WELL_ALIAS
                    FROM {$main_schema}.WELL_ALIAS
                    WHERE " . self::stripExpr('UWI') . " = " . self::stripExpr(':uwi');
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                foreach ($row as $k => $v) if ($v === null) $row[$k] = 'N/A';
                return $row;
            }
            return null;
        } catch (\PDOException $e) {
            error_log("getWellAliasByUWI error: " . $e->getMessage());
            return null;
        } finally { $pdo = null; }
    }

    public static function getWellRemarksByUWI(string $uwi): ?array
    {
        $pdo = get_db_connection();
        if (!$pdo) return null;

        try {
            $uwi = trim($uwi);
            $main_schema = get_main_schema();
            $sql = "SELECT REMARKS, REMARKS_TYPE
                    FROM {$main_schema}.WELL_REMARKS
                    WHERE " . self::stripExpr('UWI') . " = " . self::stripExpr(':uwi') . "
                      AND REMARKS_TYPE = 'INICIO_PERF'";
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                foreach ($row as $k => $v) if ($v === null) $row[$k] = 'N/A';
                return $row;
            }
            return null;
        } catch (\PDOException $e) {
            error_log("getWellRemarksByUWI error: " . $e->getMessage());
            return null;
        } finally { $pdo = null; }
    }

    public static function getRelatedWells(string $uwi): array
    {
        $pdo = get_db_connection();
        if (!$pdo) return [];

        try {
            $uwi = trim($uwi);
            $main_schema = get_main_schema();
            $sql = "SELECT wh.uwi,
                           wh.well_number   AS SECUENCIA,
                           wh.well_hdr_type AS TIPO_DE_HOYO,
                           wh.parent_uwi    AS HOYO_ORIGINAL,
                           wh.tie_in_uwi    AS HOYO_ANTERIOR
                    FROM {$main_schema}.well_hdr wh,
                         (SELECT NVL(parent_uwi, uwi) AS uwi
                          FROM {$main_schema}.well_hdr
                          WHERE " . self::stripExpr('uwi') . " = " . self::stripExpr(':uwi') . "
                         ) pr
                    WHERE " . self::stripExpr('wh.uwi') . " = " . self::stripExpr('pr.uwi') . "
                       OR " . self::stripExpr('wh.parent_uwi') . " = " . self::stripExpr('pr.uwi') . "
                    ORDER BY wh.well_number";
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$r) {
                foreach ($r as $k => $v) if ($v === null) $r[$k] = 'N/A';
            }
            return $rows;

        } catch (\PDOException $e) {
            error_log("getRelatedWells error: " . $e->getMessage());
            return [];
        } finally { $pdo = null; }
    }

    // Devuelve mapa FIELD_CODE => FIELD_NAME para un conjunto de códigos
    private static function fetchFieldNames(\PDO $pdo, array $codes): array {
        $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));
        if (empty($codes)) return [];

        $main = get_main_schema();

        // Oracle no soporta arrays bind; armamos placeholders  :c0,:c1,...
        $ph = [];
        $bind = [];
        foreach ($codes as $i => $code) {
            $k = ":c{$i}";
            $ph[] = $k;
            $bind[$k] = $code;
        }

        $sql = "SELECT FIELD_CODE, FIELD_NAME
                FROM {$main}.FIELD_HDR
                WHERE FIELD_CODE IN (" . implode(',', $ph) . ")";

        $st = $pdo->prepare($sql);
        foreach ($bind as $k => $v) $st->bindValue($k, $v);
        $st->execute();

        $map = [];
        while ($r = $st->fetch(\PDO::FETCH_ASSOC)) {
            $map[trim($r['FIELD_CODE'])] = $r['FIELD_NAME'];
        }
        return $map;
    }

    // Paquete listo para el view de tabs
    public function getDetailsBundle(string $uwi): array
    {
        $uwi = trim($uwi);
        $details = self::getWellDetailsByUWI($uwi);
        if (isset($details['error'])) {
            return ['error' => $details['error']];
        }
        // Área geográfica basada en FIELD (si aplica)
        if (!empty($details['FIELD']) && $details['FIELD'] !== 'N/A') {
            $details['GEOGRAPHIC_AREA'] = self::getGeographicAreaByFieldCode($details['FIELD']);
        } else {
            $details['GEOGRAPHIC_AREA'] = 'S/I';
        }

        // Bundle de extras
        $coords  = self::getCoordinatesByUWI($uwi);         // ya devuelve claves CANOA_*/REGVEN_*
        $alias   = self::getWellAliasByUWI($uwi) ?? [];
        $remarks = self::getWellRemarksByUWI($uwi) ?? [];
        $related = self::getRelatedWells($uwi) ?? [];

        return compact('details','coords','alias','remarks','related');
    }

    // Igual al de Well.php pero local, rápido y con fallback
    public static function getGeographicAreaByFieldCode($fieldCode): string
    {
        if (empty($fieldCode) || $fieldCode === 'N/A') {
            return 'S/I';
        }
        $pdo = get_db_connection();
        if (!$pdo) return 'S/I';

        try {
            $areaCode = substr($fieldCode, 0, 3); // prefijo típico de área
            $sql = "SELECT DISTINCT CODE || ' - ' || NVL(DESCRIPTION, 'S/I') AS GEOGRAPHIC_AREA
                    FROM CODES.COUNTRY_MISC_CODES
                    WHERE CODE = :area_code";
            $st = $pdo->prepare($sql);
            $st->execute([':area_code' => $areaCode]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return $r['GEOGRAPHIC_AREA'] ?? 'S/I';
        } catch (\PDOException $e) {
            error_log("getGeographicAreaByFieldCode error: ".$e->getMessage());
            return 'S/I';
        } finally {
            $pdo = null;
        }
    }

    public function getDetailsForEdit(string $uwi): array
    {
        $pdo = get_db_connection();
        if (!$pdo) return ['error' => 'Sin conexión a BD.'];
        $main = get_main_schema();

        try {
            $sql = "
            SELECT
                UWI, WELL_NAME, SHORT_NAME, PLOT_NAME, GOVT_ASSIGNED_NO,
                FIELD, LOCATION_TABLE, OPERATOR, DISTRICT, AGENT,
                SPUD_DATE, FIN_DRILL, RIGREL, COMP_DATE, ONINJECT, ONPROD,
                DRILLERS_TD, TVD, LOG_TD, LOG_TVD, PLUGBACK_TD, WHIPSTOCK_DEPTH, WATER_DEPTH,
                ELEVATION_REF, ELEVATION, GROUND_ELEVATION, FORM_AT_TD
            FROM {$main}.WELL_HDR
            WHERE REPLACE(UWI,' ','') = REPLACE(:uwi,' ','')
            ";
            $st = $pdo->prepare($sql);
            $st->execute([':uwi' => $uwi]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['error' => 'Pozo no encontrado.'];

            // Normaliza NULLs
            foreach ($row as $k => $v) {
                if ($v === null) $row[$k] = '';
            }
            return $row;
        } catch (PDOException $e) {
            error_log("getDetailsForEdit: " . $e->getMessage());
            return ['error' => 'Error consultando datos.'];
        }
    }

    public function updateWellPartial(string $uwi, array $data): array
    {
        $pdo = get_db_connection();
        if (!$pdo) return ['ok' => false, 'message' => 'Sin conexión a BD.'];
        $main = get_main_schema();

        try {
            $updates = [];
            $bind = [':uwi' => str_replace(' ', '', $uwi)];

            // Mapa simple (por si llega PZP_CENTINELA desde UI)
            $columnMap = ['PZP_CENTINELA' => 'GOVT_ASSIGNED_NO'];

            foreach ($data as $key => $val) {
                $col = strtoupper($key);
                if ($col === 'UWI') continue;
                if (isset($columnMap[$col])) $col = $columnMap[$col];

                if (!in_array($col, self::$allowedUpdateColumns, true)) continue;

                // Fechas
                if (in_array($col, self::$dateColumns, true)) {
                    if ($val === '' || strtoupper($val) === 'N/A') {
                        $updates[] = "{$col} = NULL";
                    } else {
                        // Acepta YYYY-MM-DD (del input type=date) o DD/MM/YYYY
                        if (preg_match('~^\d{4}-\d{2}-\d{2}$~', $val)) {
                            $updates[] = "{$col} = TO_DATE(:{$col}, 'YYYY-MM-DD')";
                        } else {
                            $updates[] = "{$col} = TO_DATE(:{$col}, 'DD/MM/YYYY')";
                        }
                        $bind[":{$col}"] = $val;
                    }
                    continue;
                }

                // Numéricos
                if (in_array($col, self::$numericColumns, true)) {
                    if ($val === '' || strtoupper($val) === 'N/A') {
                        $updates[] = "{$col} = NULL";
                    } else {
                        $updates[] = "{$col} = :{$col}";
                        $bind[":{$col}"] = (float)$val;
                    }
                    continue;
                }

                // Texto
                $updates[] = "{$col} = :{$col}";
                $bind[":{$col}"] = $val;
            }

            if (!$updates) return ['ok' => true]; // Nada que actualizar

            $sql = "UPDATE {$main}.WELL_HDR SET " . implode(', ', $updates) . " WHERE REPLACE(UWI,' ','') = :uwi";
            $pdo->beginTransaction();
            $st = $pdo->prepare($sql);
            $st->execute($bind);
            $pdo->commit();

            return ['ok' => true];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("updateWellPartial: " . $e->getMessage());
            return ['ok' => false, 'message' => 'Error al guardar cambios.'];
        }
    }
}

