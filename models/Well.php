<?php
// models/Well.php
require_once __DIR__ . '/../db.php';

class Well {
    private static $allowedUpdateColumns = [
        'WELL_NAME', 'SHORT_NAME', 'PLOT_NAME', 'GOVT_ASSIGNED_NO',
        'INITIAL_CLASS', 'CLASS', 'CURRENT_CLASS', 'ORSTATUS', 'CRSTATUS',
        'COUNTRY', 'GEOLOGIC_PROVINCE', 'PROV_ST', 'COUNTY', 'FIELD', 'BLOCK_ID', 'LOCATION_TABLE',
        'SPUD_DATE', 'FIN_DRILL', 'RIGREL', 'COMP_DATE', 'ONINJECT', 'ONPROD',
        'DISCOVER_WELL', 'DEVIATION_FLAG', 'PLOT_SYMBOL',
        'WELL_HDR_TYPE', 'WELL_NUMBER', 'PARENT_UWI', 'TIE_IN_UWI',
        'PRIMARY_SOURCE', 'CONTRACTOR', 'RIG_NO', 'RIG_NAME', 'HOLE_DIRECTION',
        'OPERATOR', 'DISTRICT', 'AGENT', 'LEASE_NO', 'LEASE_NAME', 'LICENSEE',
        'DRILLERS_TD', 'TVD', 'LOG_TD', 'LOG_TVD', 'PLUGBACK_TD', 'WHIPSTOCK_DEPTH', 'WATER_DEPTH',
        'ELEVATION_REF', 'ELEVATION', 'GROUND_ELEVATION', 'FORM_AT_TD',
        // Nuevos campos que se podrian actualizar si se obtienen por separado
        'LATITUDE', 'LONGITUDE', 'NODE_X', 'NODE_Y', 'DATUM', 'REMARKS', 'REMARKS_TYPE'
    ];

    private static $dateColumns = [
        'SPUD_DATE', 'FIN_DRILL', 'RIGREL', 'COMP_DATE', 'ONINJECT', 'ONPROD'
    ];

    private static $numericColumns = [
        'DRILLERS_TD', 'TVD', 'LOG_TD', 'LOG_TVD', 'PLUGBACK_TD', 'WHIPSTOCK_DEPTH', 'WATER_DEPTH',
        'ELEVATION', 'GROUND_ELEVATION', 'LATITUDE', 'LONGITUDE', 'NODE_X', 'NODE_Y'
    ];

    // Caché simple para evitar consultas repetidas
    private static $cache = [];
    private static $cache_limit = 100; // Límite de elementos en caché

    public static function searchWells($searchTerm) {
        // Usar el método optimizado por defecto
        return self::searchWellsOptimized($searchTerm);
    }

    /**
     * Búsqueda optimizada de pozos con mejor rendimiento
     * @param string $searchTerm Término de búsqueda
     * @return array Resultados de búsqueda
     */
    public static function searchWellsOptimized($searchTerm) {
        $results = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                // Obtener el esquema dinámicamente según la instancia
                $main_schema = get_main_schema();
                
                // Consulta optimizada compatible con Oracle 8i
                // Oracle 8i no soporta FETCH FIRST ROWS ONLY, usamos ROWNUM
                $sql = "SELECT UWI, WELL_NAME, SHORT_NAME, PLOT_NAME 
                        FROM {$main_schema}.WELL_HDR 
                        WHERE (UWI LIKE :search_term_prefix 
                           OR UWI LIKE :search_term_contains)
                           AND ROWNUM <= 50
                        ORDER BY UWI";
                
                $stmt = $pdo->prepare($sql);
                $searchTermUpper = strtoupper(trim($searchTerm));
                $stmt->execute([
                    ':search_term_prefix' => $searchTermUpper . '%',
                    ':search_term_contains' => '%' . $searchTermUpper . '%'
                ]);
                $results = $stmt->fetchAll();
                
                // Log de rendimiento para monitoreo
                error_log("Búsqueda optimizada ejecutada para: " . $searchTerm . " - Resultados: " . count($results));
                
            } catch (PDOException $e) {
                error_log("Error en la consulta optimizada de pozos: " . $e->getMessage());
                return ['error' => "Error en la consulta: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }
        return $results;
    }

    public static function getOptionsFromTable($tableName, $codeColumn, $descriptionColumn, $whereClause = null) {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $parts = explode('.', $tableName);
                $owner = strtoupper($parts[0]);
                $actualTableName = strtoupper($parts[1]);

                $sql = "SELECT " . strtoupper($codeColumn) . " AS CODE, " . strtoupper($descriptionColumn) . " AS DESCRIPTION FROM " . $owner . "." . $actualTableName;
                
                if ($whereClause) {
                    $sql .= " WHERE " . $whereClause;
                }
                
                $sql .= " ORDER BY " . strtoupper($descriptionColumn);

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de la tabla {$tableName}: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getWellClassCodes($source = null) {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $codes_schema = get_codes_schema();
                $sql = "SELECT CODE, REMARKS FROM {$codes_schema}.WELL_CLASS_CODES";
                $params = [];
                
                if ($source) {
                    $sql .= " WHERE SOURCE = :source";
                    $params[':source'] = strtoupper($source);
                }
                
                $sql .= " ORDER BY REMARKS";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['REMARKS'],
                        'display_text' => $row['CODE'] . ' - ' . $row['REMARKS']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de clasificación de pozos: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getAdminDistricts() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT SUBSTR(ELEMENT_ID,5,4) AS CODE, DESCRIPTION FROM CODES.R_ELEMENT WHERE ELEMENT_TYPE ='DISTRITO' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de distrito: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getAdminDistrictDescriptionByCode($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.R_ELEMENT WHERE ELEMENT_TYPE ='DISTRITO' AND SUBSTR(ELEMENT_ID,5,4) = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de distrito: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getBusinessAssoc() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT ASSOC_ID, ASSOC_NAME FROM CODES.BUSINESS_ASSOC ORDER BY ASSOC_NAME";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['ASSOC_ID'],
                        'description' => $row['ASSOC_NAME'],
                        'display_text' => $row['ASSOC_ID'] . ' - ' . $row['ASSOC_NAME']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de business assoc: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getOperatorOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT ASSOC_ID, ASSOC_NAME FROM CODES.BUSINESS_ASSOC WHERE TYPE IN ('COMPANIAS','EMPRESA_MIXTA','EMPRESAS_MIXTAS','ASOCIACION') ORDER BY ASSOC_NAME";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['ASSOC_ID'],
                        'description' => $row['ASSOC_NAME'],
                        'display_text' => $row['ASSOC_ID'] . ' - ' . $row['ASSOC_NAME']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de operadora: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getAgentOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT ASSOC_ID, ASSOC_NAME FROM CODES.BUSINESS_ASSOC WHERE TYPE = 'UNIDAD_EXPLOTACION' ORDER BY ASSOC_NAME";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['ASSOC_ID'],
                        'description' => $row['ASSOC_NAME'],
                        'display_text' => $row['ASSOC_ID'] . ' - ' . $row['ASSOC_NAME']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de agente: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getBusinessAssocNameById($id) {
        $pdo = get_db_connection();
        if ($pdo && !empty($id)) {
            try {
                $sql = "SELECT ASSOC_NAME FROM CODES.BUSINESS_ASSOC WHERE ASSOC_ID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['ASSOC_NAME'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener nombre de business assoc: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getWellStatusCodes() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $codes_schema = get_codes_schema();
                $sql = "SELECT STATUS, REMARKS FROM {$codes_schema}.WELL_STATUS_CODES WHERE REMARKS IS NOT NULL ORDER BY REMARKS";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['STATUS'],
                        'description' => $row['REMARKS'],
                        'display_text' => $row['STATUS'] . ' - ' . $row['REMARKS']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de estado de pozos: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getWellStatusDescriptionByCode($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT REMARKS FROM CODES.WELL_STATUS_CODES WHERE STATUS = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['REMARKS'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de well status code: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getYesNoOptions() {
        $codes_schema = get_codes_schema();
        return self::getOptionsFromTable("{$codes_schema}.YES_NO", 'YES_NO', 'YES_NO');
    }

    public static function getFieldHeaders() {
        $main_schema = get_main_schema();
        return self::getOptionsFromTable("{$main_schema}.FIELD_HDR", 'FIELD_CODE', 'FIELD_NAME');
    }

    public static function getFieldNameByCode($fieldCode) {
        $pdo = get_db_connection();
        if ($pdo && !empty($fieldCode)) {
            try {
                $main_schema = get_main_schema();
                $sql = "SELECT FIELD_NAME FROM {$main_schema}.FIELD_HDR WHERE FIELD_CODE = :field_code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':field_code' => $fieldCode]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['FIELD_NAME'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener nombre del campo: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getLeaseOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $main_schema = get_main_schema();
                $sql = "SELECT LEASE_ID, LEASE_NAME FROM {$main_schema}.LEASE ORDER BY LEASE_NAME";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['LEASE_ID'],
                        'description' => $row['LEASE_NAME'],
                        'display_text' => $row['LEASE_ID'] . ' - ' . $row['LEASE_NAME']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de licencia: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getLeaseNameById($id) {
        $pdo = get_db_connection();
        if ($pdo && !empty($id)) {
            try {
                $main_schema = get_main_schema();
                $sql = "SELECT LEASE_NAME FROM {$main_schema}.LEASE WHERE LEASE_ID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['LEASE_NAME'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener nombre de lease: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getGeologicProvinces() {
        $codes_schema = get_codes_schema();
        return self::getOptionsFromTable("{$codes_schema}.GEOLOGIC_PROVINCE", 'GEOL_PROV_ID', 'DESCRIPTION');
    }

    public static function getRSourceOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $codes_schema = get_codes_schema();
                $sql = "SELECT TYPE, DESCRIPTION FROM {$codes_schema}.R_SOURCE WHERE SOURCE_TYPE = 'PRIMARY_SOURCE' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['TYPE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['TYPE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de empresa origen: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getPlotSymbolOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $codes_schema = get_codes_schema();
                $sql = "SELECT CODE, DESCRIPTION FROM {$codes_schema}.MISC_CODES WHERE CODE_TYPE IN ('SIMBOLO_POZO','SIM_ISOPACO') ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de símbolo de mapa: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getDeviationFlagOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT CODE, DESCRIPTION FROM CODES.MISC_CODES WHERE CODE_TYPE = 'DESVIO' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de desvío: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getDiscoverWellOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT CODE, DESCRIPTION FROM CODES.MISC_CODES WHERE CODE_TYPE = 'POZO_DESCUBRIDOR' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de pozo descubridor: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getRigOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT CODE, DESCRIPTION FROM CODES.MISC_CODES WHERE CODE_TYPE = 'RIG' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de taladro: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getHoleDirectionOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT CODE, DESCRIPTION FROM CODES.MISC_CODES WHERE CODE_TYPE = 'HOLE_DIR' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de dirección del hoyo: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getBlockOptions() {
        $main_schema = get_main_schema();
        return self::getOptionsFromTable("{$main_schema}.BLOCK", 'BLOCK_ID', 'BLOCK_ID');
    }

    public static function getWellTypeOptions() {
        $codes_schema = get_codes_schema();
        return self::getOptionsFromTable("{$codes_schema}.WELL_TYPE", 'WELL_TYPE', 'DESCRIPTION');
    }

    public static function getWellHdrTypeOptions() {
        return [
            [
                'code' => 'WELL_WELLBORE',
                'description' => 'WELL_WELLBORE',
                'display_text' => 'WELL_WELLBORE'
            ],
            [
                'code' => 'WELLBORE',
                'description' => 'WELLBORE', 
                'display_text' => 'WELLBORE'
            ]
        ];
    }

    public static function getWellTypeDescriptionByCode($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.R_WELL_TYPE WHERE TYPE = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de well type: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getWellElevRefOptions() {
        // Asumiendo que el nombre de la tabla es CODES.WELL_ELEV_REF_CODES basado en el patrón del usuario
        return self::getOptionsFromTable('CODES.WELL_ELEV_REF_CODES', 'CODE', 'DESCRIPTION');
    }

    public static function getWellElevRefDescriptionByCode($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.WELL_ELEV_REF_CODES WHERE CODE = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de well elev ref code: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getCountryOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT CODE, DESCRIPTION FROM CODES.MISC_CODES WHERE CODE_TYPE = 'COUNTRY' ORDER BY DESCRIPTION";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODE'],
                        'description' => $row['DESCRIPTION'],
                        'display_text' => $row['CODE'] . ' - ' . $row['DESCRIPTION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de PAIS: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getMiscCodeDescription($code, $code_type) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code) && !empty($code_type)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.MISC_CODES WHERE CODE = :code AND CODE_TYPE = :code_type";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code, ':code_type' => $code_type]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de misc code: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }
    
    public static function getCountyOptions() {
        return self::getOptionsFromTable('CODES.GEOPOL_AREA', 'COUNTY', 'DESCRIPTION');
    }

    public static function getCountyDescriptionByCode($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.GEOPOL_AREA WHERE COUNTY = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de county: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getProvStOptions() {
        $options = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $sql = "SELECT DISTINCT PROV_ST_CODE CODIGO, PROV_ST DESCRIPCION FROM CODES.GEOPOL_AREA ORDER BY PROV_ST";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rawOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rawOptions as $row) {
                    $options[] = [
                        'code' => $row['CODIGO'],
                        'description' => $row['DESCRIPCION'],
                        'display_text' => $row['CODIGO'] . ' - ' . $row['DESCRIPCION']
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error al obtener opciones de ESTADO/PROVINCIA: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        }
        return $options;
    }

    public static function getGeologicProvinceDescription($code) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code)) {
            try {
                $sql = "SELECT DESCRIPTION FROM CODES.GEOLOGIC_PROVINCE WHERE GEOL_PROV_ID = :code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['DESCRIPTION'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de provincia geologica: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getWellClassDescriptionByCode($code, $source) {
        $pdo = get_db_connection();
        if ($pdo && !empty($code) && !empty($source)) {
            try {
                $sql = "SELECT REMARKS FROM CODES.WELL_CLASS_CODES WHERE CODE = :code AND SOURCE = :source";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':code' => $code, ':source' => $source]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['REMARKS'] ?? null;
            } catch (PDOException $e) {
                error_log("Error al obtener descripcion de well class code: " . $e->getMessage());
                return null;
            } finally {
                $pdo = null;
            }
        }
        return null;
    }

    public static function getGeographicAreaByFieldCode($fieldCode) {
        if (empty($fieldCode) || $fieldCode === 'N/A') {
            return 'S/I';
        }
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $areaCode = substr($fieldCode, 0, 3);
                $sql = "SELECT DISTINCT CODE || ' - ' || DESCRIPTION AS GEOGRAPHIC_AREA
                        FROM CODES.COUNTRY_MISC_CODES
                        WHERE CODE = :area_code";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':area_code' => $areaCode]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['GEOGRAPHIC_AREA'] ?? 'S/I';
            } catch (PDOException $e) {
                error_log("Error al obtener area geografica: " . $e->getMessage());
                return 'S/I';
            } finally {
                $pdo = null;
            }
        }
        return 'S/I';
    }

    public static function getNodeDetailsByUWI($uwi) {
        $details = null;
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                $main_schema = get_main_schema();
                $sql = "SELECT
                    NS.LATITUDE, NS.LONGITUDE, NS.NODE_X, NS.NODE_Y, NS.DATUM
                FROM 
                    {$main_schema}.WELL_HDR WH, {$main_schema}.NODES_SECOND NS
                WHERE WH.NODE_ID = NS.NODE_ID (+)
                AND REPLACE(WH.UWI, ' ', '') = REPLACE(:uwi, ' ', '')
                AND NS.SOURCE (+) = 'CT_CONVERT_UTM'";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $details = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($details) {
                    foreach ($details as $key => $value) {
                        if ($value === null) {
                            $details[$key] = 'N/A';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Error al obtener detalles del nodo: " . $e->getMessage());
                return ['error' => "Error al obtener detalles del nodo: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }
        return $details;
    }

    public static function getWellRemarksByUWI($uwi) {
        $details = null;
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                $main_schema = get_main_schema();
                $sql = "SELECT REMARKS, REMARKS_TYPE
                        FROM {$main_schema}.WELL_REMARKS
                        WHERE REPLACE(UWI, ' ', '') = REPLACE(:uwi, ' ', '') AND REMARKS_TYPE = 'INICIO_PERF'";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $details = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($details) {
                    foreach ($details as $key => $value) {
                        if ($value === null) {
                            $details[$key] = 'N/A';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Error al obtener comentarios del pozo: " . $e->getMessage());
                return ['error' => "Error al obtener comentarios del pozo: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }
        return $details;
    }

    public static function getWellAliasByUWI($uwi) {
        $details = null;
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                $main_schema = get_main_schema();
                $sql = "SELECT WELL_ALIAS
                        FROM {$main_schema}.WELL_ALIAS
                        WHERE REPLACE(UWI, ' ', '') = REPLACE(:uwi, ' ', '')";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $details = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($details) {
                    foreach ($details as $key => $value) {
                        if ($value === null) {
                            $details[$key] = 'N/A';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Error al obtener alias del pozo: " . $e->getMessage());
                return ['error' => "Error al obtener alias del pozo: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }
        return $details;
    }

    public static function getCoordinatesByUWI($uwi) {
        $details = [
            'LONGITUD' => 'N/A',
            'LATITUD' => 'N/A',
            'CANOA_DATUM' => 'N/A',
            'CANOA_ESTE' => 'N/A',
            'CANOA_NORTE' => 'N/A',
            'CANOA_ORIGEN' => 'N/A',
            'REGVEN_DATUM' => 'N/A',
            'REGVEN_ESTE' => 'N/A',
            'REGVEN_NORTE' => 'N/A',
            'REGVEN_ORIGEN' => 'N/A',
        ];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                $sql = "SELECT
                           GEO_PDV.NODE_X AS LONGITUD,
                           GEO_PDV.NODE_Y AS LATITUD,
                           UTM_PDV.DATUM AS CANOA_DATUM,
                           UTM_PDV.NODE_X AS CANOA_ESTE,
                           UTM_PDV.NODE_Y AS CANOA_NORTE,
                           (SELECT CODE || ' - ' || NVL(ABBREVIATION, 'ACTUAL') FROM CODES.MISC_CODES WHERE CODE_TYPE = 'LOC_QUAL' AND CODE = UTM_PDV.LOC_QUAL) AS CANOA_ORIGEN,
                           UTM_REG.DATUM AS REGVEN_DATUM,
                           UTM_REG.NODE_X AS REGVEN_ESTE,
                           UTM_REG.NODE_Y AS REGVEN_NORTE,
                           (SELECT CODE || ' - ' || NVL(ABBREVIATION, 'ACTUAL') FROM CODES.MISC_CODES WHERE CODE_TYPE = 'LOC_QUAL' AND CODE = UTM_REG.LOC_QUAL) AS REGVEN_ORIGEN
                        FROM
                           PDVSA.WELL_HDR PZO_PDV,
                           PDVSA.NODES GEO_PDV,
                           PDVSA.NODES_SECOND UTM_PDV,
                           PDVSA.WELL_HDR@FINDREG.WORLD PZO_REG,
                           PDVSA.NODES_SECOND@FINDREG.WORLD UTM_REG
                        WHERE
                           REPLACE(PZO_PDV.UWI, ' ', '') = REPLACE(:uwi, ' ', '')
                           AND PZO_REG.UWI(+) = PZO_PDV.UWI
                           AND UTM_PDV.NODE_ID(+) = PZO_PDV.NODE_ID
                           AND GEO_PDV.NODE_ID(+) = PZO_PDV.NODE_ID
                           AND UTM_REG.NODE_ID(+) = PZO_REG.NODE_ID
                           AND UTM_PDV.SOURCE(+) = 'CT_CONVERT_UTM'
                           AND UTM_REG.SOURCE(+) = 'CT_CONVERT_UTM'";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    foreach ($result as $key => $value) {
                        if ($value !== null) {
                            $details[strtoupper($key)] = $value;
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Error al obtener coordenadas: " . $e->getMessage());
                // Devuelve los valores por defecto en caso de error
                return $details;
            } finally {
                $pdo = null;
            }
        }
        return $details;
    }

    /**
     * Obtener detalles del pozo con caché para mejorar rendimiento
     * @param string $uwi UWI del pozo
     * @return array Detalles del pozo
     */
    public static function getWellDetailsByUWI($uwi) {
        $uwi = trim($uwi);
        $cache_key = 'well_details_' . $uwi;
        
        // Verificar caché primero
        if (isset(self::$cache[$cache_key])) {
            error_log("Detalles del pozo obtenidos desde caché: " . $uwi);
            return self::$cache[$cache_key];
        }
        
        // Si no está en caché, usar el método optimizado
        $details = self::getWellDetailsOptimized($uwi);
        
        // Guardar en caché si no hay error
        if (!isset($details['error'])) {
            self::addToCache($cache_key, $details);
        }
        
        return $details;
    }

    /**
     * Obtener detalles del pozo con consulta consolidada optimizada
     * @param string $uwi UWI del pozo
     * @return array Detalles del pozo
     */
    public static function getWellDetailsOptimized($uwi) {
        $details = null;
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                $start_time = microtime(true);
                $sql = ""; // Inicializar variable sql para logging de errores

                // Obtener el esquema dinámicamente según la instancia
                $main_schema = get_main_schema();
                
                // Primero probar una consulta simple para verificar la conexión
                $test_sql = "SELECT UWI, WELL_NAME FROM {$main_schema}.WELL_HDR WHERE UWI = :uwi";
                $test_stmt = $pdo->prepare($test_sql);
                $test_stmt->execute([':uwi' => $uwi]);
                $test_result = $test_stmt->fetch();
                
                if (!$test_result) {
                    error_log("No se encontró el pozo con UWI: " . $uwi);
                    return ['error' => "Pozo no encontrado"];
                }

                // Consulta simplificada compatible con Oracle 8i
                // Primero obtenemos los datos básicos del pozo
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
                WHERE WH.UWI = :uwi";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $details = $stmt->fetch();

                if (!$details) {
                    error_log("No se encontraron datos para el pozo con UWI: " . $uwi);
                    return ['error' => "No se encontraron datos del pozo"];
                }

                // Obtener datos adicionales por separado (más compatible con Oracle 7)
                
                // 1. Coordenadas
                if (!empty($details['NODE_ID'])) {
                    try {
                        $coord_sql = "SELECT LATITUDE, LONGITUDE, NODE_X, NODE_Y, DATUM 
                                     FROM {$main_schema}.NODES_SECOND 
                                     WHERE NODE_ID = :node_id";
                        $coord_stmt = $pdo->prepare($coord_sql);
                        $coord_stmt->execute([':node_id' => $details['NODE_ID']]);
                        $coord_result = $coord_stmt->fetch();
                        if ($coord_result) {
                            $details['LATITUDE'] = $coord_result['LATITUDE'];
                            $details['LONGITUDE'] = $coord_result['LONGITUDE'];
                            $details['NODE_X'] = $coord_result['NODE_X'];
                            $details['NODE_Y'] = $coord_result['NODE_Y'];
                            $details['DATUM'] = $coord_result['DATUM'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener coordenadas: " . $e->getMessage());
                    }
                }
                
                // 2. Comentarios (solo si la tabla existe en esta instancia)
                if (table_exists('WELL_REMARKS')) {
                    try {
                        $remarks_sql = "SELECT REMARKS, REMARKS_TYPE 
                                       FROM {$main_schema}.WELL_REMARKS 
                                       WHERE UWI = :uwi AND REMARKS_TYPE = 'INICIO_PERF'";
                        $remarks_stmt = $pdo->prepare($remarks_sql);
                        $remarks_stmt->execute([':uwi' => $uwi]);
                        $remarks_result = $remarks_stmt->fetch();
                        if ($remarks_result) {
                            $details['REMARKS'] = $remarks_result['REMARKS'];
                            $details['REMARKS_TYPE'] = $remarks_result['REMARKS_TYPE'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener comentarios: " . $e->getMessage());
                    }
                } else {
                    // Tabla no existe en esta instancia, usar valores por defecto
                    $details['REMARKS'] = 'N/A';
                    $details['REMARKS_TYPE'] = 'N/A';
                }
                
                // 3. Alias del pozo (solo si la tabla existe en esta instancia)
                if (table_exists('WELL_ALIAS')) {
                    try {
                        $alias_sql = "SELECT WELL_ALIAS FROM {$main_schema}.WELL_ALIAS WHERE UWI = :uwi";
                        $alias_stmt = $pdo->prepare($alias_sql);
                        $alias_stmt->execute([':uwi' => $uwi]);
                        $alias_result = $alias_stmt->fetch();
                        if ($alias_result) {
                            $details['WELL_ALIAS'] = $alias_result['WELL_ALIAS'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener alias: " . $e->getMessage());
                    }
                } else {
                    // Tabla no existe en esta instancia, usar valores por defecto
                    $details['WELL_ALIAS'] = 'N/A';
                }
                
                // 4. Descripción de provincia geológica
                if (!empty($details['GEOLOGIC_PROVINCE'])) {
                    try {
                        $codes_schema = get_codes_schema();
                        $prov_sql = "SELECT DESCRIPTION 
                                    FROM {$codes_schema}.GEOLOGIC_PROVINCE 
                                    WHERE GEOL_PROV_ID = :prov_id";
                        $prov_stmt = $pdo->prepare($prov_sql);
                        $prov_stmt->execute([':prov_id' => $details['GEOLOGIC_PROVINCE']]);
                        $prov_result = $prov_stmt->fetch();
                        if ($prov_result) {
                            $details['GEOLOGIC_PROVINCE_DESC'] = $prov_result['DESCRIPTION'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener descripción de provincia: " . $e->getMessage());
                    }
                }
                
                // 5. Nombre del campo (solo si la tabla existe en esta instancia)
                if (!empty($details['FIELD']) && table_exists('FIELD_HDR')) {
                    try {
                        $field_sql = "SELECT FIELD_NAME FROM {$main_schema}.FIELD_HDR WHERE FIELD_CODE = :field_code";
                        $field_stmt = $pdo->prepare($field_sql);
                        $field_stmt->execute([':field_code' => $details['FIELD']]);
                        $field_result = $field_stmt->fetch();
                        if ($field_result) {
                            $details['FIELD_NAME'] = $field_result['FIELD_NAME'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener nombre del campo: " . $e->getMessage());
                    }
                } else if (!empty($details['FIELD'])) {
                    // Tabla no existe, usar el código como nombre
                    $details['FIELD_NAME'] = $details['FIELD'];
                }
                
                // 6. Descripción de clase
                if (!empty($details['CLASS'])) {
                    try {
                        $class_sql = "SELECT DESCRIPTION 
                                     FROM CODES.WELL_CLASS_CODES 
                                     WHERE CLASS_CODE = :class_code AND SOURCE = 'CT_CONVERT_UTM'";
                        $class_stmt = $pdo->prepare($class_sql);
                        $class_stmt->execute([':class_code' => $details['CLASS']]);
                        $class_result = $class_stmt->fetch();
                        if ($class_result) {
                            $details['CLASS_DESC'] = $class_result['DESCRIPTION'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener descripción de clase: " . $e->getMessage());
                    }
                }
                
                // 7. Descripción de estado
                if (!empty($details['ORSTATUS'])) {
                    try {
                        $status_sql = "SELECT DESCRIPTION 
                                      FROM CODES.WELL_STATUS_CODES 
                                      WHERE STATUS_CODE = :status_code AND SOURCE = 'CT_CONVERT_UTM'";
                        $status_stmt = $pdo->prepare($status_sql);
                        $status_stmt->execute([':status_code' => $details['ORSTATUS']]);
                        $status_result = $status_stmt->fetch();
                        if ($status_result) {
                            $details['STATUS_DESC'] = $status_result['DESCRIPTION'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener descripción de estado: " . $e->getMessage());
                    }
                }
                
                // 8. Nombre del operador (solo si la tabla existe en esta instancia)
                if (!empty($details['OPERATOR']) && table_exists('BUSINESS_ASSOC')) {
                    try {
                        $op_sql = "SELECT NAME FROM {$main_schema}.BUSINESS_ASSOC WHERE BUSINESS_ASSOC_ID = :op_id";
                        $op_stmt = $pdo->prepare($op_sql);
                        $op_stmt->execute([':op_id' => $details['OPERATOR']]);
                        $op_result = $op_stmt->fetch();
                        if ($op_result) {
                            $details['OPERATOR_NAME'] = $op_result['NAME'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener nombre del operador: " . $e->getMessage());
                    }
                } else if (!empty($details['OPERATOR'])) {
                    // Tabla no existe, usar el código como nombre
                    $details['OPERATOR_NAME'] = $details['OPERATOR'];
                }
                
                // 9. Nombre del agente (solo si la tabla existe en esta instancia)
                if (!empty($details['AGENT']) && table_exists('BUSINESS_ASSOC')) {
                    try {
                        $agent_sql = "SELECT NAME FROM {$main_schema}.BUSINESS_ASSOC WHERE BUSINESS_ASSOC_ID = :agent_id";
                        $agent_stmt = $pdo->prepare($agent_sql);
                        $agent_stmt->execute([':agent_id' => $details['AGENT']]);
                        $agent_result = $agent_stmt->fetch();
                        if ($agent_result) {
                            $details['AGENT_NAME'] = $agent_result['NAME'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener nombre del agente: " . $e->getMessage());
                    }
                } else if (!empty($details['AGENT'])) {
                    // Tabla no existe, usar el código como nombre
                    $details['AGENT_NAME'] = $details['AGENT'];
                }
                
                // 10. Nombre del arrendamiento (solo si la tabla existe en esta instancia)
                if (!empty($details['LEASE_NO']) && table_exists('LEASE')) {
                    try {
                        $lease_sql = "SELECT LEASE_NAME FROM {$main_schema}.LEASE WHERE LEASE_ID = :lease_id";
                        $lease_stmt = $pdo->prepare($lease_sql);
                        $lease_stmt->execute([':lease_id' => $details['LEASE_NO']]);
                        $lease_result = $lease_stmt->fetch();
                        if ($lease_result) {
                            $details['LEASE_DESC'] = $lease_result['LEASE_NAME'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener nombre del arrendamiento: " . $e->getMessage());
                    }
                } else if (!empty($details['LEASE_NO'])) {
                    // Tabla no existe, usar el código como descripción
                    $details['LEASE_DESC'] = $details['LEASE_NO'];
                }
                
                // 11. Descripción del distrito
                if (!empty($details['DISTRICT'])) {
                    try {
                        $district_sql = "SELECT DESCRIPTION 
                                        FROM {$codes_schema}.R_ELEMENT 
                                        WHERE ELEMENT_ID = :district_id 
                                          AND ELEMENT_TYPE = 'DISTRICT'";
                        $district_stmt = $pdo->prepare($district_sql);
                        $district_stmt->execute([':district_id' => $details['DISTRICT']]);
                        $district_result = $district_stmt->fetch();
                        if ($district_result) {
                            $details['DISTRICT_DESC'] = $district_result['DESCRIPTION'];
                        }
                    } catch (PDOException $e) {
                        error_log("Error al obtener descripción del distrito: " . $e->getMessage());
                    }
                }

                // Convertir NULL a 'N/A' y otros formatos
                if ($details) {
                    foreach ($details as $key => $value) {
                        if ($value === null) {
                            $details[$key] = 'N/A';
                        } elseif (is_numeric($value)) {
                            $details[$key] = (float)$value;
                        }
                    }
                    
                    // Preparar campos de visualización
                    $details = self::prepareDisplayFields($details);
                }

                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;
                error_log("Consulta optimizada ejecutada para UWI: " . $uwi . " - Tiempo: " . number_format($execution_time, 4) . "s");

            } catch (PDOException $e) {
                error_log("Error en la consulta optimizada de detalles del pozo: " . $e->getMessage());
                error_log("SQL que causó el error: " . $sql);
                return ['error' => "Error en la consulta de detalles: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos."];
        }
        return $details;
    }

    /**
     * Agregar elemento al caché con límite de tamaño
     * @param string $key Clave del caché
     * @param mixed $value Valor a almacenar
     */
    private static function addToCache($key, $value) {
        // Si el caché está lleno, eliminar el elemento más antiguo
        if (count(self::$cache) >= self::$cache_limit) {
            $oldest_key = array_key_first(self::$cache);
            unset(self::$cache[$oldest_key]);
        }
        
        self::$cache[$key] = $value;
    }

    /**
     * Preparar campos de visualización para la interfaz
     * @param array $details Detalles del pozo
     * @return array Detalles con campos de visualización
     */
    private static function prepareDisplayFields($details) {
        // Preparar descripciones para campos de códigos
        if (!empty($details['GEOLOGIC_PROVINCE']) && $details['GEOLOGIC_PROVINCE'] !== 'N/A') {
            $details['GEOLOGIC_PROVINCE_DISPLAY'] = $details['GEOLOGIC_PROVINCE'] . ' - ' . ($details['GEOLOGIC_PROVINCE_DESC'] ?? 'N/A');
        } else {
            $details['GEOLOGIC_PROVINCE_DISPLAY'] = 'N/A';
        }

        if (!empty($details['FIELD']) && $details['FIELD'] !== 'N/A') {
            $details['FIELD_DISPLAY'] = $details['FIELD'] . ' - ' . ($details['FIELD_NAME'] ?? 'N/A');
        } else {
            $details['FIELD_DISPLAY'] = 'N/A';
        }

        if (!empty($details['INITIAL_CLASS']) && $details['INITIAL_CLASS'] !== 'N/A') {
            $details['INITIAL_CLASS_DISPLAY'] = $details['INITIAL_CLASS'] . ' - ' . ($details['INITIAL_CLASS_DESC'] ?? 'N/A');
        } else {
            $details['INITIAL_CLASS_DISPLAY'] = 'N/A';
        }

        if (!empty($details['ORSTATUS']) && $details['ORSTATUS'] !== 'N/A') {
            $details['ORSTATUS_DISPLAY'] = $details['ORSTATUS'] . ' - ' . ($details['ORSTATUS_DESC'] ?? 'N/A');
        } else {
            $details['ORSTATUS_DISPLAY'] = 'N/A';
        }

        if (!empty($details['OPERATOR']) && $details['OPERATOR'] !== 'N/A') {
            $details['OPERATOR_DISPLAY'] = $details['OPERATOR'] . ' - ' . ($details['OPERATOR_NAME'] ?? 'N/A');
        } else {
            $details['OPERATOR_DISPLAY'] = 'N/A';
        }

        if (!empty($details['DISTRICT']) && $details['DISTRICT'] !== 'N/A') {
            $details['DISTRICT_DISPLAY'] = $details['DISTRICT'] . ' - ' . ($details['DISTRICT_DESC'] ?? 'N/A');
        } else {
            $details['DISTRICT_DISPLAY'] = 'N/A';
        }

        if (!empty($details['AGENT']) && $details['AGENT'] !== 'N/A') {
            $details['AGENT_DISPLAY'] = $details['AGENT'] . ' - ' . ($details['AGENT_NAME'] ?? 'N/A');
        } else {
            $details['AGENT_DISPLAY'] = 'N/A';
        }

        if (!empty($details['LEASE_NO']) && $details['LEASE_NO'] !== 'N/A') {
            $details['LEASE_NO_DISPLAY'] = $details['LEASE_NO'] . ' - ' . ($details['LEASE_NAME_DESC'] ?? 'N/A');
        } else {
            $details['LEASE_NO_DISPLAY'] = 'N/A';
        }

        return $details;
    }

    public static function testUpdatePermission() {
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $pdo->beginTransaction();

                $main_schema = get_main_schema();
                $sql = "UPDATE {$main_schema}.WELL_HDR
                        SET GEOLOGIC_PROVINCE = :GEOLOGIC_PROVINCE
                        WHERE REPLACE(UWI, ' ', '') = :uwi";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':GEOLOGIC_PROVINCE', '2');
                $stmt->bindValue(':uwi', '007WHTOM00011');
                $stmt->execute();
                
                $rowCount = $stmt->rowCount();
                $pdo->commit();

                return ['success' => true, 'message' => 'Consulta de prueba ejecutada y confirmada. Filas afectadas: ' . $rowCount];
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Error en la consulta de prueba de actualizacion: " . $e->getMessage());
                return ['success' => false, 'message' => "Error en la consulta de prueba: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexion con la base de datos para la prueba."];
        }
    }

    public static function updateWellDetails($uwi, $wellDetails) {
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $main_schema = get_main_schema();
                $sql = "UPDATE {$main_schema}.WELL_HDR SET ";
                $updates = [];
                $bindValues = [':uwi' => str_replace(' ', '', $uwi)];

                $columnMap = [
                    'PZP_CENTINELA' => 'GOVT_ASSIGNED_NO'
                ];

                $dateColumns = self::$dateColumns;

                foreach ($wellDetails as $key => $value) {
                    $dbColumn = strtoupper($key);

                    if (isset($columnMap[$dbColumn])) {
                        $dbColumn = $columnMap[$dbColumn];
                    }

                    if ($dbColumn !== 'UWI' && in_array($dbColumn, self::$allowedUpdateColumns)) {
                        if (in_array($dbColumn, $dateColumns)) {
                            if (empty($value) || $value === 'N/A') {
                                $updates[] = $dbColumn . " = NULL";
                            } else {
                                $updates[] = $dbColumn . " = TO_DATE(:" . $dbColumn . ", 'DD/MM/YYYY')";
                                $bindValues[":" . $dbColumn] = $value;
                            }
                        } elseif (in_array($dbColumn, self::$numericColumns)) {
                            if (empty($value) || $value === 'N/A') {
                                $updates[] = $dbColumn . " = NULL";
                            } else {
                                $updates[] = $dbColumn . " = :" . $dbColumn;
                                $bindValues[":" . $dbColumn] = (float)$value;
                            }
                        } else {
                            $updates[] = $dbColumn . " = :" . $dbColumn;
                            $bindValues[":" . $dbColumn] = $value;
                        }
                    }
                }

                if (empty($updates)) {
                    return true;
                }

                $pdo->beginTransaction();

                $sql .= implode(', ', $updates);
                $sql .= " WHERE REPLACE(UWI, ' ', '') = :uwi";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($bindValues);
                
                $pdo->commit();

                return true;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Error al actualizar los detalles del pozo: " . $e->getMessage());
                return false;
            } finally {
                $pdo = null;
            }
        } else {
            return false;
        }
    }

    public static function getRelatedWells($uwi) {
        $relatedWells = [];
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $uwi = trim($uwi);
                
                // Obtener el esquema dinámicamente según la instancia
                $main_schema = get_main_schema();
                
                $sql = "SELECT wh.uwi,
                            wh.well_number as SECUENCIA,
                            wh.well_hdr_type as TIPO_DE_HOYO,
                            wh.parent_uwi as HOYO_ORIGINAL,
                            wh.tie_in_uwi as HOYO_ANTERIOR
                        FROM {$main_schema}.well_hdr wh,
                            (SELECT nvl(parent_uwi, uwi) as uwi
                            FROM {$main_schema}.well_hdr
                            WHERE uwi = :uwi) pr
                        WHERE wh.uwi = pr.UWI
                        OR wh.parent_uwi = pr.UWI
                        ORDER BY wh.well_number";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':uwi' => $uwi]);
                $relatedWells = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Reemplazar valores NULL por 'N/A'
                foreach ($relatedWells as &$well) {
                    foreach ($well as $key => $value) {
                        if ($value === null) {
                            $well[$key] = 'N/A';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Error al obtener pozos relacionados: " . $e->getMessage());
                return ['error' => "Error al obtener pozos relacionados: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        return $relatedWells;
    }

    // Para el dashboard
    public static function getWellsYearStats() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getWellsYearStats");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener el esquema dinámicamente según la instancia
            $main_schema = get_main_schema();
            
            // Pozos creados por año (asegurar el alias correcto)
            $sqlCreated = "SELECT 
                            EXTRACT(YEAR FROM insert_date) AS year, 
                            COUNT(*) AS count 
                        FROM {$main_schema}.well_hdr 
                        GROUP BY EXTRACT(YEAR FROM insert_date)
                        ORDER BY year";
            
            // Pozos actualizados por año
            $sqlUpdated = "SELECT 
                            EXTRACT(YEAR FROM last_update) AS year, 
                            COUNT(*) AS count 
                        FROM {$main_schema}.well_hdr 
                        GROUP BY EXTRACT(YEAR FROM last_update)
                        ORDER BY year";
            
            $stmtCreated = $pdo->query($sqlCreated);
            $stmtUpdated = $pdo->query($sqlUpdated);
            
            $stats['created'] = $stmtCreated->fetchAll(PDO::FETCH_ASSOC);
            $stats['updated'] = $stmtUpdated->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting well stats by year: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    // ===== FUNCIONES PARA DASHBOARD CON DATOS REALES =====
    
    /**
     * Obtiene estadísticas generales reales del dashboard
     */
    public static function getDashboardGeneralStats() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getDashboardGeneralStats");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener el esquema dinámicamente según la instancia
            $main_schema = get_main_schema();
            
            // Total de pozos
            $sqlTotal = "SELECT COUNT(*) as total_wells FROM {$main_schema}.WELL_HDR";
            $stmtTotal = $pdo->query($sqlTotal);
            $totalResult = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $stats['totalWells'] = $totalResult['TOTAL_WELLS'] ?? 0;
            
            // Pozos activos
            $sqlActive = "SELECT COUNT(*) as active_wells 
                         FROM {$main_schema}.WELL_HDR 
                         WHERE UPPER(CRSTATUS) IN ('ACTIVE', 'PRODUCING')";
            $stmtActive = $pdo->query($sqlActive);
            $activeResult = $stmtActive->fetch(PDO::FETCH_ASSOC);
            $stats['activeWells'] = $activeResult['ACTIVE_WELLS'] ?? 0;
            
            // Pozos completados este año (2025)
            $sqlCompleted = "SELECT COUNT(*) as completed_this_year 
                           FROM {$main_schema}.WELL_HDR 
                           WHERE EXTRACT(YEAR FROM COMP_DATE) = 2025";
            $stmtCompleted = $pdo->query($sqlCompleted);
            $completedResult = $stmtCompleted->fetch(PDO::FETCH_ASSOC);
            $stats['completedThisYear'] = $completedResult['COMPLETED_THIS_YEAR'] ?? 0;
            
            // Última actualización
            $sqlLastUpdate = "SELECT MAX(LAST_UPDATE) as last_update FROM {$main_schema}.WELL_HDR";
            $stmtLastUpdate = $pdo->query($sqlLastUpdate);
            $lastUpdateResult = $stmtLastUpdate->fetch(PDO::FETCH_ASSOC);
            $stats['lastUpdate'] = $lastUpdateResult['LAST_UPDATE'] ?? date('d/m/Y H:i');
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting dashboard general stats: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    /**
     * Obtiene distribución de pozos por estados/provincias
     */
    public static function getWellsByState() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getWellsByState");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener esquemas dinámicamente según la instancia
            $main_schema = get_main_schema();
            $codes_schema = get_codes_schema();
            
            $sql = "SELECT 
                        COALESCE(ps.DESCRIPCION, wh.PROV_ST, 'No Especificado') as estado,
                        COUNT(*) as total_pozos,
                        COUNT(CASE WHEN UPPER(wh.CRSTATUS) IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
                    FROM {$main_schema}.WELL_HDR wh
                    LEFT JOIN {$codes_schema}.PROV_ST ps ON wh.PROV_ST = ps.CODIGO
                    GROUP BY COALESCE(ps.DESCRIPCION, wh.PROV_ST, 'No Especificado')
                    ORDER BY total_pozos DESC";
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting wells by state: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    /**
     * Obtiene distribución de pozos por cuencas geológicas
     */
    public static function getWellsByGeologicProvince() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getWellsByGeologicProvince");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener esquemas dinámicamente según la instancia
            $main_schema = get_main_schema();
            $codes_schema = get_codes_schema();
            
            $sql = "SELECT 
                        COALESCE(gp.DESCRIPTION, wh.GEOLOGIC_PROVINCE, 'No Especificado') as cuenca,
                        COUNT(*) as total_pozos,
                        COUNT(CASE WHEN UPPER(wh.CRSTATUS) IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
                    FROM {$main_schema}.WELL_HDR wh
                    LEFT JOIN {$codes_schema}.GEOLOGIC_PROVINCE gp ON wh.GEOLOGIC_PROVINCE = gp.GEOL_PROV_ID
                    GROUP BY COALESCE(gp.DESCRIPTION, wh.GEOLOGIC_PROVINCE, 'No Especificado')
                    ORDER BY total_pozos DESC";
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting wells by geologic province: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    /**
     * Obtiene distribución de pozos por distritos
     */
    public static function getWellsByDistrict() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getWellsByDistrict");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener el esquema dinámicamente según la instancia
            $main_schema = get_main_schema();
            
            $sql = "SELECT 
                        COALESCE(DISTRICT, 'No Especificado') as distrito,
                        COUNT(*) as total_pozos,
                        COUNT(CASE WHEN UPPER(CRSTATUS) IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
                    FROM {$main_schema}.WELL_HDR
                    GROUP BY COALESCE(DISTRICT, 'No Especificado')
                    ORDER BY total_pozos DESC";
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting wells by district: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }

    /**
     * Obtiene distribución de pozos por campos petroleros
     */
    public static function getWellsByField() {
        $pdo = get_db_connection();
        $stats = [];
        
        if (!$pdo) {
            error_log("Error: No se pudo establecer conexión con la base de datos en getWellsByField");
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
        
        try {
            // Obtener el esquema dinámicamente según la instancia
            $main_schema = get_main_schema();
            
            // Verificar si la tabla FIELD_HDR existe para esta instancia
            if (table_exists('FIELD_HDR')) {
                $sql = "SELECT 
                            COALESCE(fh.FIELD_NAME, wh.FIELD, 'No Especificado') as campo,
                            COUNT(*) as total_pozos,
                            COUNT(CASE WHEN UPPER(wh.CRSTATUS) IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
                        FROM {$main_schema}.WELL_HDR wh
                        LEFT JOIN {$main_schema}.FIELD_HDR fh ON wh.FIELD = fh.FIELD_ID
                        GROUP BY COALESCE(fh.FIELD_NAME, wh.FIELD, 'No Especificado')
                        ORDER BY total_pozos DESC";
            } else {
                // Si no existe FIELD_HDR, usar solo el campo FIELD de WELL_HDR
                $sql = "SELECT 
                            COALESCE(FIELD, 'No Especificado') as campo,
                            COUNT(*) as total_pozos,
                            COUNT(CASE WHEN UPPER(CRSTATUS) IN ('ACTIVE', 'PRODUCING') THEN 1 END) as pozos_activos
                        FROM {$main_schema}.WELL_HDR
                        GROUP BY COALESCE(FIELD, 'No Especificado')
                        ORDER BY total_pozos DESC";
            }
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting wells by field: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            $pdo = null;
        }
    }
}
?>
