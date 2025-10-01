<?php
// controllers/WellController.php
require_once __DIR__ . '/../models/Well.php';
require_once __DIR__ . '/../includes/Auth.php'; // Asegurarse de que la clase Auth esté disponible

class WellController {
    public function index() {
        $search_term = $_GET['search_well'] ?? '';
        $selected_uwi = $_GET['uwi'] ?? null;
        $results = [];
        $well_details = null;
        $error = null;
        $related_wells = []; // Inicializar variable

        if (!empty($search_term)) {
            $well_data = Well::searchWells($search_term);
            if (isset($well_data['error'])) {
                $error = $well_data['error'];
            } else {
                $results = $well_data;
            }
        }

        if ($selected_uwi) {
            $uwi_decoded = urldecode($selected_uwi);
            $well_data = Well::getWellDetailsByUWI($uwi_decoded);
            if (isset($well_data['error'])) {
                $error = $well_data['error'];
            } else {
                $well_details = $well_data;

                // Obtener y combinar detalles adicionales
                $node_details = Well::getNodeDetailsByUWI($uwi_decoded);
                if ($node_details && !isset($node_details['error'])) {
                    $well_details = array_merge($well_details, $node_details);
                } else if (isset($node_details['error'])) {
                    error_log("Error al obtener detalles del nodo en WellController: " . $node_details['error']);
                }

                $remarks_details = Well::getWellRemarksByUWI($uwi_decoded);
                if ($remarks_details && !isset($remarks_details['error'])) {
                    $well_details = array_merge($well_details, $remarks_details);
                } else if (isset($remarks_details['error'])) {
                    error_log("Error al obtener comentarios del pozo en WellController: " . $remarks_details['error']);
                }

                $alias_details = Well::getWellAliasByUWI($uwi_decoded);
                if ($alias_details && !isset($alias_details['error'])) {
                    $well_details = array_merge($well_details, $alias_details);
                } else if (isset($alias_details['error'])) {
                    error_log("Error al obtener alias del pozo en WellController: " . $alias_details['error']);
                }

                // Obtener las nuevas coordenadas
                $coordinates = Well::getCoordinatesByUWI($uwi_decoded);
                if ($coordinates && !isset($coordinates['error'])) {
                    $well_details = array_merge($well_details, $coordinates);
                } else if (isset($coordinates['error'])) {
                    error_log("Error al obtener coordenadas en WellController: " . $coordinates['error']);
                }

                // Obtener la descripción de GEOLOGIC_PROVINCE y concatenarla
                if (!empty($well_details['GEOLOGIC_PROVINCE']) && $well_details['GEOLOGIC_PROVINCE'] !== 'N/A') {
                    $province_code = $well_details['GEOLOGIC_PROVINCE'];
                    $province_desc = Well::getGeologicProvinceDescription($province_code);
                    if ($province_desc) {
                        $well_details['GEOLOGIC_PROVINCE'] = $province_code . ' - ' . $province_desc;
                    }
                }

                // Obtener el nombre del campo geológico y concatenarlo
                if (!empty($well_details['FIELD']) && $well_details['FIELD'] !== 'N/A') {
                    $field_code = $well_details['FIELD'];
                    $field_name = Well::getFieldNameByCode($field_code);
                    if ($field_name) {
                        $well_details['FIELD_DISPLAY'] = $field_code . ' - ' . $field_name;
                    } else {
                        $well_details['FIELD_DISPLAY'] = $field_code; // Mostrar solo el código si no se encuentra el nombre
                    }
                    // Obtener el área geográfica basada en el código de campo
                    $well_details['GEOGRAPHIC_AREA'] = Well::getGeographicAreaByFieldCode($field_code);
                } else {
                    $well_details['FIELD_DISPLAY'] = 'N/A';
                    $well_details['GEOGRAPHIC_AREA'] = 'S/I';
                }

                // Preparar el texto de visualización para INITIAL_CLASS
                if (!empty($well_details['INITIAL_CLASS']) && $well_details['INITIAL_CLASS'] !== 'N/A') {
                    $initial_class_code = $well_details['INITIAL_CLASS'];
                    $initial_class_desc = Well::getWellClassDescriptionByCode($initial_class_code, 'INITIAL CLASS');
                    if ($initial_class_desc) {
                        $well_details['INITIAL_CLASS_DISPLAY'] = $initial_class_code . ' - ' . $initial_class_desc;
                    } else {
                        $well_details['INITIAL_CLASS_DISPLAY'] = $initial_class_code;
                    }
                } else {
                    $well_details['INITIAL_CLASS_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para CLASS
                if (!empty($well_details['CLASS']) && $well_details['CLASS'] !== 'N/A') {
                    $class_code = $well_details['CLASS'];
                    $class_desc = Well::getWellClassDescriptionByCode($class_code, 'CLASS');
                    if ($class_desc) {
                        $well_details['CLASS_DISPLAY'] = $class_code . ' - ' . $class_desc;
                    } else {
                        $well_details['CLASS_DISPLAY'] = $class_code;
                    }
                } else {
                    $well_details['CLASS_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para ORSTATUS
                if (!empty($well_details['ORSTATUS']) && $well_details['ORSTATUS'] !== 'N/A') {
                    $orstatus_code = $well_details['ORSTATUS'];
                    $orstatus_desc = Well::getWellStatusDescriptionByCode($orstatus_code);
                    if ($orstatus_desc) {
                        $well_details['ORSTATUS_DISPLAY'] = $orstatus_code . ' - ' . $orstatus_desc;
                    } else {
                        $well_details['ORSTATUS_DISPLAY'] = $orstatus_code;
                    }
                } else {
                    $well_details['ORSTATUS_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para CRSTATUS
                if (!empty($well_details['CRSTATUS']) && $well_details['CRSTATUS'] !== 'N/A') {
                    $crstatus_code = $well_details['CRSTATUS'];
                    $crstatus_desc = Well::getWellStatusDescriptionByCode($crstatus_code);
                    if ($crstatus_desc) {
                        $well_details['CRSTATUS_DISPLAY'] = $crstatus_code . ' - ' . $crstatus_desc;
                    } else {
                        $well_details['CRSTATUS_DISPLAY'] = $crstatus_code;
                    }
                } else {
                    $well_details['CRSTATUS_DISPLAY'] = 'N/A';
                }
                
                // Preparar el texto de visualización para WELL_HDR_TYPE
                if (!empty($well_details['WELL_HDR_TYPE']) && $well_details['WELL_HDR_TYPE'] !== 'N/A') {
                    $well_type_code = $well_details['WELL_HDR_TYPE'];
                    // Buscar la descripción en las opciones disponibles
                    $well_hdr_type_options = Well::getWellHdrTypeOptions();
                    $well_type_desc = null;
                    foreach ($well_hdr_type_options as $option) {
                        if ($option['code'] === $well_type_code) {
                            $well_type_desc = $option['description'];
                            break;
                        }
                    }
                    if ($well_type_desc && $well_type_code !== $well_type_desc) {
                        // Solo mostrar "CODE - DESCRIPTION" si son diferentes
                        $well_details['WELL_HDR_TYPE_DISPLAY'] = $well_type_code . ' - ' . $well_type_desc;
                    } else {
                        // Si son iguales, solo mostrar el código
                        $well_details['WELL_HDR_TYPE_DISPLAY'] = $well_type_code;
                    }
                } else {
                    $well_details['WELL_HDR_TYPE_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para OPERATOR
                if (!empty($well_details['OPERATOR']) && $well_details['OPERATOR'] !== 'N/A') {
                    $operator_code = $well_details['OPERATOR'];
                    $operator_desc = Well::getBusinessAssocNameById($operator_code);
                    if ($operator_desc) {
                        $well_details['OPERATOR_DISPLAY'] = $operator_code . ' - ' . $operator_desc;
                    } else {
                        $well_details['OPERATOR_DISPLAY'] = $operator_code;
                    }
                } else {
                    $well_details['OPERATOR_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para DISTRICT
                if (!empty($well_details['DISTRICT']) && $well_details['DISTRICT'] !== 'N/A') {
                    $district_code = $well_details['DISTRICT'];
                    $district_desc = Well::getAdminDistrictDescriptionByCode($district_code);
                    if ($district_desc) {
                        $well_details['DISTRICT_DISPLAY'] = $district_code . ' - ' . $district_desc;
                    } else {
                        $well_details['DISTRICT_DISPLAY'] = $district_code;
                    }
                } else {
                    $well_details['DISTRICT_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para AGENT
                if (!empty($well_details['AGENT']) && $well_details['AGENT'] !== 'N/A') {
                    $agent_code = $well_details['AGENT'];
                    $agent_desc = Well::getBusinessAssocNameById($agent_code);
                    if ($agent_desc) {
                        $well_details['AGENT_DISPLAY'] = $agent_code . ' - ' . $agent_desc;
                    } else {
                        $well_details['AGENT_DISPLAY'] = $agent_code;
                    }
                } else {
                    $well_details['AGENT_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para LEASE_NO
                if (!empty($well_details['LEASE_NO']) && $well_details['LEASE_NO'] !== 'N/A') {
                    $lease_code = $well_details['LEASE_NO'];
                    $lease_desc = Well::getLeaseNameById($lease_code);
                    if ($lease_desc) {
                        $well_details['LEASE_NO_DISPLAY'] = $lease_code . ' - ' . $lease_desc;
                    } else {
                        $well_details['LEASE_NO_DISPLAY'] = $lease_code;
                    }
                } else {
                    $well_details['LEASE_NO_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para ELEVATION_REF
                if (!empty($well_details['ELEVATION_REF']) && $well_details['ELEVATION_REF'] !== 'N/A') {
                    $elev_ref_code = $well_details['ELEVATION_REF'];
                    $elev_ref_desc = Well::getWellElevRefDescriptionByCode($elev_ref_code);
                    if ($elev_ref_desc) {
                        $well_details['ELEVATION_REF_DISPLAY'] = $elev_ref_code . ' - ' . $elev_ref_desc;
                    } else {
                        $well_details['ELEVATION_REF_DISPLAY'] = $elev_ref_code;
                    }
                } else {
                    $well_details['ELEVATION_REF_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para COUNTRY
                if (!empty($well_details['COUNTRY']) && $well_details['COUNTRY'] !== 'N/A') {
                    $country_code = $well_details['COUNTRY'];
                    $country_desc = Well::getMiscCodeDescription($country_code, 'COUNTRY');
                    if ($country_desc) {
                        $well_details['COUNTRY_DISPLAY'] = $country_code . ' - ' . $country_desc;
                    } else {
                        $well_details['COUNTRY_DISPLAY'] = $country_code;
                    }
                } else {
                    $well_details['COUNTRY_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para COUNTY
                if (!empty($well_details['COUNTY']) && $well_details['COUNTY'] !== 'N/A') {
                    $county_code = $well_details['COUNTY'];
                    $county_desc = Well::getCountyDescriptionByCode($county_code);
                    if ($county_desc) {
                        $well_details['COUNTY_DISPLAY'] = $county_code . ' - ' . $county_desc;
                    } else {
                        $well_details['COUNTY_DISPLAY'] = $county_code;
                    }
                } else {
                    $well_details['COUNTY_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para PLOT_SYMBOL
                if (!empty($well_details['PLOT_SYMBOL']) && $well_details['PLOT_SYMBOL'] !== 'N/A') {
                    $plot_symbol_code = $well_details['PLOT_SYMBOL'];
                    $plot_symbol_desc = Well::getMiscCodeDescription($plot_symbol_code, 'SIMBOLO_POZO');
                    if (!$plot_symbol_desc) {
                        $plot_symbol_desc = Well::getMiscCodeDescription($plot_symbol_code, 'SIM_ISOPACO');
                    }
                    if ($plot_symbol_desc) {
                        $well_details['PLOT_SYMBOL_DISPLAY'] = $plot_symbol_code . ' - ' . $plot_symbol_desc;
                    } else {
                        $well_details['PLOT_SYMBOL_DISPLAY'] = $plot_symbol_code;
                    }
                } else {
                    $well_details['PLOT_SYMBOL_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para PRIMARY_SOURCE
                if (!empty($well_details['PRIMARY_SOURCE']) && $well_details['PRIMARY_SOURCE'] !== 'N/A') {
                    $primary_source_code = $well_details['PRIMARY_SOURCE'];
                    // Asumiendo que la descripción está en la misma tabla R_SOURCE
                    $primary_source_desc = Well::getMiscCodeDescription($primary_source_code, 'PRIMARY_SOURCE');
                    if ($primary_source_desc) {
                        $well_details['PRIMARY_SOURCE_DISPLAY'] = $primary_source_code . ' - ' . $primary_source_desc;
                    } else {
                        $well_details['PRIMARY_SOURCE_DISPLAY'] = $primary_source_code;
                    }
                } else {
                    $well_details['PRIMARY_SOURCE_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para CONTRACTOR
                if (!empty($well_details['CONTRACTOR']) && $well_details['CONTRACTOR'] !== 'N/A') {
                    $contractor_code = $well_details['CONTRACTOR'];
                    $contractor_desc = Well::getBusinessAssocNameById($contractor_code);
                    if ($contractor_desc) {
                        $well_details['CONTRACTOR_DISPLAY'] = $contractor_code . ' - ' . $contractor_desc;
                    } else {
                        $well_details['CONTRACTOR_DISPLAY'] = $contractor_code;
                    }
                } else {
                    $well_details['CONTRACTOR_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para RIG_NO
                if (!empty($well_details['RIG_NO']) && $well_details['RIG_NO'] !== 'N/A') {
                    $rig_no_code = $well_details['RIG_NO'];
                    $rig_no_desc = Well::getMiscCodeDescription($rig_no_code, 'RIG');
                    if ($rig_no_desc) {
                        $well_details['RIG_NO_DISPLAY'] = $rig_no_code . ' - ' . $rig_no_desc;
                    } else {
                        $well_details['RIG_NO_DISPLAY'] = $rig_no_code;
                    }
                } else {
                    $well_details['RIG_NO_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para HOLE_DIRECTION
                if (!empty($well_details['HOLE_DIRECTION']) && $well_details['HOLE_DIRECTION'] !== 'N/A') {
                    $hole_direction_code = $well_details['HOLE_DIRECTION'];
                    $hole_direction_desc = Well::getMiscCodeDescription($hole_direction_code, 'HOLE_DIR');
                    if ($hole_direction_desc) {
                        $well_details['HOLE_DIRECTION_DISPLAY'] = $hole_direction_code . ' - ' . $hole_direction_desc;
                    } else {
                        $well_details['HOLE_DIRECTION_DISPLAY'] = $hole_direction_code;
                    }
                } else {
                    $well_details['HOLE_DIRECTION_DISPLAY'] = 'N/A';
                }

                // Fetch options for dropdowns
                $options = [
                    'INITIAL_CLASS' => Well::getWellClassCodes('INITIAL CLASS'),
                    'CLASS' => Well::getWellClassCodes('CLASS'),
                    'CURRENT_CLASS' => Well::getWellClassCodes(),
                    'ORSTATUS' => Well::getWellStatusCodes(),
                    'CRSTATUS' => Well::getWellStatusCodes(),
                    'DEVIATION_FLAG' => Well::getYesNoOptions(),
                    'DISTRICT' => Well::getAdminDistricts(),
                    'OPERATOR' => Well::getOperatorOptions(),
                    'LICENSEE' => Well::getBusinessAssoc(),
                    'AGENT' => Well::getAgentOptions(),
                    'FIELD' => Well::getFieldHeaders(), // Ahora devuelve CODE y NAME
                    'BLOCK_ID' => Well::getBlockOptions(),
                    'ELEVATION_REF' => Well::getWellElevRefOptions(),
                    'COUNTRY' => Well::getCountryOptions(),
                    'COUNTY' => Well::getCountyOptions(),
                    'PLOT_SYMBOL' => Well::getPlotSymbolOptions(),
                    'DEVIATION_FLAG' => Well::getDeviationFlagOptions(),
                    'DISCOVER_WELL' => Well::getDiscoverWellOptions(),
                    'PRIMARY_SOURCE' => Well::getRSourceOptions(),
                    'RIG_NO' => Well::getRigOptions(),
                    'HOLE_DIRECTION' => Well::getHoleDirectionOptions(),
                    'LEASE_NO' => Well::getLeaseOptions(),
                    'GEOLOGIC_PROVINCE' => Well::getGeologicProvinces(),
                    'CONTRACTOR' => Well::getBusinessAssoc(),
                    'PROV_ST' => Well::getProvStOptions(),
                    'WELL_HDR_TYPE' => Well::getWellHdrTypeOptions(),
                ];

                // Obtener la descripción completa de PROV_ST para la visualización en la tarjeta
                if (!empty($well_details['PROV_ST']) && $well_details['PROV_ST'] !== 'N/A') {
                    $prov_st_code = $well_details['PROV_ST'];
                    $prov_st_options = Well::getProvStOptions();
                    foreach ($prov_st_options as $option) {
                        if ($option['code'] === $prov_st_code) {
                            $well_details['PROV_ST_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    // Si no se encontró en las opciones, mostrar solo el código
                    if (!isset($well_details['PROV_ST_DISPLAY'])) {
                        $well_details['PROV_ST_DISPLAY'] = $prov_st_code;
                    }
                } else {
                    $well_details['PROV_ST_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para DISCOVER_WELL
                if (!empty($well_details['DISCOVER_WELL']) && $well_details['DISCOVER_WELL'] !== 'N/A') {
                    $discover_well_code = $well_details['DISCOVER_WELL'];
                    $discover_well_options = Well::getDiscoverWellOptions();
                    foreach ($discover_well_options as $option) {
                        if ($option['code'] === $discover_well_code) {
                            $well_details['DISCOVER_WELL_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['DISCOVER_WELL_DISPLAY'])) {
                        $well_details['DISCOVER_WELL_DISPLAY'] = $discover_well_code;
                    }
                } else {
                    $well_details['DISCOVER_WELL_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para DEVIATION_FLAG
                if (!empty($well_details['DEVIATION_FLAG']) && $well_details['DEVIATION_FLAG'] !== 'N/A') {
                    $deviation_flag_code = $well_details['DEVIATION_FLAG'];
                    $deviation_flag_options = Well::getDeviationFlagOptions();
                    foreach ($deviation_flag_options as $option) {
                        if ($option['code'] === $deviation_flag_code) {
                            $well_details['DEVIATION_FLAG_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['DEVIATION_FLAG_DISPLAY'])) {
                        $well_details['DEVIATION_FLAG_DISPLAY'] = $deviation_flag_code;
                    }
                } else {
                    $well_details['DEVIATION_FLAG_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para PLOT_SYMBOL
                if (!empty($well_details['PLOT_SYMBOL']) && $well_details['PLOT_SYMBOL'] !== 'N/A') {
                    $plot_symbol_code = $well_details['PLOT_SYMBOL'];
                    $plot_symbol_options = Well::getPlotSymbolOptions();
                    foreach ($plot_symbol_options as $option) {
                        if ($option['code'] === $plot_symbol_code) {
                            $well_details['PLOT_SYMBOL_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['PLOT_SYMBOL_DISPLAY'])) {
                        $well_details['PLOT_SYMBOL_DISPLAY'] = $plot_symbol_code;
                    }
                } else {
                    $well_details['PLOT_SYMBOL_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para OPERATOR
                if (!empty($well_details['OPERATOR']) && $well_details['OPERATOR'] !== 'N/A') {
                    $operator_code = $well_details['OPERATOR'];
                    $operator_options = Well::getOperatorOptions();
                    foreach ($operator_options as $option) {
                        if ($option['code'] === $operator_code) {
                            $well_details['OPERATOR_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['OPERATOR_DISPLAY'])) {
                        $well_details['OPERATOR_DISPLAY'] = $operator_code;
                    }
                } else {
                    $well_details['OPERATOR_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para DISTRICT
                if (!empty($well_details['DISTRICT']) && $well_details['DISTRICT'] !== 'N/A') {
                    $district_code = $well_details['DISTRICT'];
                    $district_options = Well::getAdminDistricts();
                    foreach ($district_options as $option) {
                        if ($option['code'] === $district_code) {
                            $well_details['DISTRICT_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['DISTRICT_DISPLAY'])) {
                        $well_details['DISTRICT_DISPLAY'] = $district_code;
                    }
                } else {
                    $well_details['DISTRICT_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para AGENT
                if (!empty($well_details['AGENT']) && $well_details['AGENT'] !== 'N/A') {
                    $agent_code = $well_details['AGENT'];
                    $agent_options = Well::getAgentOptions();
                    foreach ($agent_options as $option) {
                        if ($option['code'] === $agent_code) {
                            $well_details['AGENT_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['AGENT_DISPLAY'])) {
                        $well_details['AGENT_DISPLAY'] = $agent_code;
                    }
                } else {
                    $well_details['AGENT_DISPLAY'] = 'N/A';
                }

                // Preparar el texto de visualización para LEASE_NO
                if (!empty($well_details['LEASE_NO']) && $well_details['LEASE_NO'] !== 'N/A') {
                    $lease_no_code = $well_details['LEASE_NO'];
                    $lease_no_options = Well::getLeaseOptions();
                    foreach ($lease_no_options as $option) {
                        if ($option['code'] === $lease_no_code) {
                            $well_details['LEASE_NO_DISPLAY'] = $option['display_text'];
                            break;
                        }
                    }
                    if (!isset($well_details['LEASE_NO_DISPLAY'])) {
                        $well_details['LEASE_NO_DISPLAY'] = $lease_no_code;
                    }
                } else {
                    $well_details['LEASE_NO_DISPLAY'] = 'N/A';
                }
            }

            // Obtener pozos relacionados
            $related_wells = Well::getRelatedWells($uwi_decoded);
            if (isset($related_wells['error'])) {
                error_log("Error al obtener pozos relacionados: " . $related_wells['error']);
            }
        }
        
        require_once __DIR__ . '/../views/well.php';
    }

    public function update() {
        // This method will now handle full form submissions, if any remain, or can be repurposed.
        // For individual field updates, use updateField.
        $uwi = $_POST['uwi'] ?? null;
        if ($uwi) {
            $wellDetails = $_POST;
            unset($wellDetails['uwi']); // Remove UWI from the update array as it's the key

            $success = Well::updateWellDetails($uwi, $wellDetails);

            if ($success) {
                header("Location: " . BASE_PATH . "/well?search_well=" . urlencode($uwi) . "&uwi=" . urlencode($uwi) . "&message=success");
                exit;
            } else {
                header("Location: " . BASE_PATH . "/well?search_well=" . urlencode($uwi) . "&uwi=" . urlencode($uwi) . "&message=error");
                exit;
            }
        } else {
            header("Location: " . BASE_PATH . "/dashboard?message=uwi_missing");
            exit;
        }
    }

    public function updateField() {
        // Desactivar la visualización de errores para evitar que corrompan la salida JSON
        ini_set('display_errors', 'Off');
        error_reporting(E_ALL); // Asegurarse de que todos los errores sean reportados, pero no mostrados

        // Limpiar cualquier búfer de salida accidental antes de enviar la respuesta JSON
        ob_clean(); 
        
        // Configurar el manejo de errores para convertir errores a excepciones
        set_error_handler(function($errno, $errstr, $errfile, $errline ) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Error desconocido.'];

        try {
            $uwi = $_POST['uwi'] ?? null;
            $field = $_POST['field'] ?? null;
            $value = $_POST['value'] ?? null;

            if (!$uwi || !$field) {
                $response['message'] = 'UWI o campo no proporcionado.';
                echo json_encode($response);
                restore_error_handler(); // Restaurar el manejador de errores
                exit;
            }

            $wellDetails = [$field => $value];
            $success = Well::updateWellDetails($uwi, $wellDetails);

            if ($success) {
                $response['success'] = true;
                $response['message'] = 'Campo actualizado correctamente.';
            } else {
                $response['message'] = 'Error al actualizar el campo en la base de datos.';
            }

        } catch (Exception $e) {
            $response['message'] = 'Excepción en el servidor: ' . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine();
            error_log("Excepción en WellController::updateField: " . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
        } finally {
            restore_error_handler(); // Asegurarse de restaurar el manejador de errores
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Endpoint AJAX para búsqueda asíncrona de pozos
     * Optimizado para búsqueda rápida con autocompletado
     */
    public function search() {
        // Verificar autenticación
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        // Configurar headers para JSON
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            $term = $_GET['term'] ?? '';
            
            // Validar entrada
            if (empty($term) || strlen($term) < 2) {
                echo json_encode([]);
                return;
            }

            // Limpiar término de búsqueda
            $term = trim($term);
            
            // Ejecutar búsqueda optimizada
            $results = Well::searchWellsOptimized($term);
            
            // Formatear resultados para autocompletado
            $formatted_results = [];
            if (!isset($results['error'])) {
                foreach ($results as $well) {
                    $formatted_results[] = [
                        'value' => $well['UWI'],
                        'label' => $well['UWI'] . ' - ' . ($well['WELL_NAME'] ?? 'Sin nombre'),
                        'uwi' => $well['UWI'],
                        'well_name' => $well['WELL_NAME'] ?? '',
                        'short_name' => $well['SHORT_NAME'] ?? '',
                        'plot_name' => $well['PLOT_NAME'] ?? ''
                    ];
                }
            }

            echo json_encode($formatted_results);

        } catch (Exception $e) {
            error_log("Error en búsqueda AJAX: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function testUpdatePermissionView() {
        // Verificar autenticación para la vista de prueba
        if (!Auth::check()) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        $test_result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_test'])) {
            $test_result = Well::testUpdatePermission();
        }
        require_once __DIR__ . '/../views/test_update_permission.php';
    }
    
}
?>
