<?php
// controllers/WellController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/Well.php';
require_once __DIR__ . '/../includes/Auth.php';

class WellController
{
    /* =========================================================
     * LISTADO / BÚSQUEDA (HTML por defecto, JSON opcional)
     * Rutas: GET /well  | GET /wells
     * Params: search_well, page, per_page, format=json
     * Vista: views/well.php (legacy) o JSON si se pide
     * =======================================================*/
    public function index(): void
    {
        $this->requireAuth();

        $search_term = trim((string)($_GET['search_well'] ?? ''));
        $selected_uwi = $_GET['uwi'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 20)));
        $asJson = $this->wantsJson();

        $results = [];
        $total = 0;
        $error = null;

        if ($search_term !== '') {
            $searchData = Well::searchWellsOptimized($search_term, $page, $perPage);
            if (isset($searchData['error'])) {
                $error = $searchData['error'];
            } else {
                // Permite que el modelo retorne ['data'=>[], 'total'=>N] o un array plano
                if (isset($searchData['data'])) {
                    $results = $searchData['data'];
                    $total   = (int)($searchData['total'] ?? count($results));
                } else {
                    $results = $searchData;
                    $total   = count($results);
                }
            }
        }

        $well_details = null;
        $options = [];
        $related_wells = [];

        if ($selected_uwi) {
            $loaded = $this->loadWellShowData(urldecode((string)$selected_uwi));
            $well_details  = $loaded['well_details'];
            $related_wells = $loaded['related_wells'];
            $options       = $loaded['options'];
            $error         = $error ?: $loaded['error'];
        }

        if ($asJson) {
            $this->json([
                'search' => $search_term,
                'page'   => $page,
                'per_page' => $perPage,
                'total'  => $total,
                'results'=> $results,
                'well'   => $well_details,
                'related_wells' => $related_wells,
                'error'  => $error,
            ]);
        }

        // Vista legacy unificada
        $this->render('well', compact('search_term','results','error','well_details','options','related_wells'));
    }

    /* =========================================================
     * VISTA CREAR (formulario)
     * Ruta: GET /wells/create
     * Vista: views/well_create.php (créala con tus campos)
     * =======================================================*/
    public function create(): void
    {
        $this->requireAuth();

        $options = $this->loadOptions();
        $csrf = $this->csrf();
        $this->render('well_create', compact('options','csrf'));
    }

    /* =========================================================
     * CREAR (INSERT)
     * Ruta: POST /wells
     * Cuerpo: application/x-www-form-urlencoded o JSON
     * Redirige a /wells/{uwi} o retorna JSON
     * =======================================================*/
    public function store(): void
    {
        $this->requireAuth();

        $input = $this->input();
        $asJson = $this->wantsJson();

        $uwi = trim((string)($input['UWI'] ?? $input['uwi'] ?? ''));
        if ($uwi === '') {
            $this->fail("El UWI es requerido para crear el pozo.", $asJson, BASE_PATH . '/wells/create');
        }

        try {
            if (!method_exists('Well', 'create')) {
                $this->fail("Función Well::create() no implementada.", $asJson);
            }

            // Sanitiza entrada: borra llaves peligrosas y fuerza allowed
            $data = $this->filterAllowed($input);
            $data['UWI'] = $uwi;

            $ok = Well::create($data);
            if (!$ok) {
                $this->fail("No se pudo crear el pozo (revisa restricciones/duplicados).", $asJson);
            }

            $this->flash('success', "Pozo {$uwi} creado correctamente.");

            if ($asJson) {
                $this->json(['success' => true, 'uwi' => $uwi], 201);
            }
            header('Location: ' . BASE_PATH . '/wells/' . rawurlencode($uwi));
            exit;
        } catch (Throwable $e) {
            error_log("STORE Well error: " . $e->getMessage());
            $this->fail("Error en servidor al crear el pozo.", $asJson);
        }
    }

    /* =========================================================
     * MOSTRAR DETALLE
     * Ruta: GET /wells/{uwi} | GET /well/details/{uwi}
     * Vista: views/well.php (la misma que usas)
     * =======================================================*/
    public function show(string $uwi): void
    {
        $this->requireAuth();
        $asJson = $this->wantsJson();

        $loaded = $this->loadWellShowData($uwi);
        if ($loaded['error']) {
            $this->fail($loaded['error'], $asJson, BASE_PATH . '/well');
        }

        if ($asJson) {
            $this->json([
                'well' => $loaded['well_details'],
                'related_wells' => $loaded['related_wells'],
                'options' => $loaded['options'],
            ]);
        }

        // Reusar vista principal (legacy)
        $search_term   = $uwi;
        $results       = [];
        $error         = null;
        $well_details  = $loaded['well_details'];
        $options       = $loaded['options'];
        $related_wells = $loaded['related_wells'];

        $this->render('well', compact('search_term','results','error','well_details','options','related_wells'));
    }

    /* =========================================================
     * ACTUALIZAR
     * Rutas: PATCH /wells/{uwi}  |  POST /well/update (legacy)
     * Body: JSON o x-www-form-urlencoded
     * =======================================================*/
    public function update($uwiOrNone = null): void
    {
        $this->requireAuth();

        $asJson = $this->wantsJson();
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Compatibilidad con legacy: POST /well/update con campo hidden 'uwi'
        if ($method === 'POST' && $uwiOrNone === null) {
            $uwi = trim((string)($_POST['uwi'] ?? ''));
            if ($uwi === '') {
                $this->fail('Falta el UWI en la solicitud.', false, BASE_PATH . '/dashboard?message=uwi_missing');
            }
            $payload = $_POST;
            unset($payload['uwi'], $payload['csrf_token'], $payload['_method']);
        } else {
            // PATCH /wells/{uwi} con JSON o x-www-form-urlencoded
            $uwi = (string)$uwiOrNone;
            $payload = $this->input();
        }

        if ($uwi === '') {
            $this->fail('UWI inválido.', $asJson);
        }

        try {
            $data = $this->filterAllowed($payload);

            $ok = Well::updateWellDetails($uwi, $data);
            if (!$ok) {
                $this->fail('No se pudo actualizar el pozo. Revise los datos.', $asJson);
            }

            $this->flash('success', "Pozo {$uwi} actualizado correctamente.");

            if ($asJson) {
                $this->json(['success' => true, 'uwi' => $uwi]);
            }

            // Redirección legacy a la vista del mismo pozo
            header("Location: " . BASE_PATH . "/well?search_well=" . urlencode($uwi) . "&uwi=" . urlencode($uwi) . "&message=success");
            exit;
        } catch (Throwable $e) {
            error_log("UPDATE Well error: " . $e->getMessage());
            $this->fail('Error en el servidor al actualizar.', $asJson);
        }
    }

    /* =========================================================
     * ELIMINAR
     * Ruta: DELETE /wells/{uwi}
     * Form: _method=DELETE + csrf_token || Fetch con header X-CSRF-Token
     * =======================================================*/
    public function destroy(string $uwi): void
    {
        $this->requireAuth();
        $asJson = $this->wantsJson();

        try {
            if (!method_exists('Well', 'deleteByUwi')) {
                $this->fail("Función Well::deleteByUwi() no implementada.", $asJson);
            }

            $ok = Well::deleteByUwi($uwi);
            if (!$ok) {
                $this->fail("No se pudo eliminar el pozo {$uwi}.", $asJson);
            }

            $this->flash('success', "Pozo {$uwi} eliminado.");
            if ($asJson) {
                $this->json(['success' => true, 'uwi' => $uwi]);
            }
            header('Location: ' . BASE_PATH . '/wells');
            exit;
        } catch (Throwable $e) {
            error_log("DESTROY Well error: " . $e->getMessage());
            $this->fail('Error en el servidor al eliminar el pozo.', $asJson);
        }
    }

    /* =========================================================
     * UPDATE FIELD (AJAX inline) - Legacy mejorado + CSRF interno
     * Ruta: POST /well/updateField
     * -------------------------------------------------------*/
    public function updateField(): void
    {
        // No uses render aquí, retornamos JSON siempre
        header('Content-Type: application/json; charset=UTF-8');

        // Auth
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        // CSRF local (esta ruta está excluida del CSRF global)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'CSRF inválido']);
            return;
        }

        // Oculta notices en salida JSON
        ini_set('display_errors', '0');
        error_reporting(E_ALL);
        if (function_exists('ob_get_length')) { while (ob_get_level()) { ob_end_clean(); } }

        try {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

            $uwi   = trim((string)($_POST['uwi']   ?? ''));
            $field = trim((string)($_POST['field'] ?? ''));
            $value = $_POST['value'] ?? null;

            if ($uwi === '' || $field === '') {
                echo json_encode(['success' => false, 'message' => 'UWI o campo no proporcionado.']);
                return;
            }

            // Filtra a columnas permitidas
            $payload = $this->filterAllowed([$field => $value]);
            if (empty($payload)) {
                echo json_encode(['success' => false, 'message' => 'Campo no permitido.']);
                return;
            }

            $ok = Well::updateWellDetails($uwi, $payload);
            if (!$ok) {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar en BD.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Campo actualizado.']);
        } catch (Throwable $e) {
            error_log("Excepción en updateField: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        } finally {
            restore_error_handler();
        }
    }

    /* =========================================================
     * AUTOCOMPLETE / BUSCADOR RÁPIDO (JSON)
     * Rutas: GET /well/search | GET /api/wells/search
     * Query: term, limit
     * =======================================================*/
    public function search(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            $term  = trim((string)($_GET['term'] ?? ''));
            $limit = min(50, max(5, (int)($_GET['limit'] ?? 20)));

            if ($term === '' || mb_strlen($term) < 2) {
                echo json_encode([]);
                return;
            }

            // Puedes extender el modelo para que reciba limit/paginación
            $results = Well::searchWellsOptimized($term, 1, $limit);

            $rows = $results['data'] ?? $results;
            $out = [];
            if (!isset($results['error'])) {
                foreach ($rows as $well) {
                    $out[] = [
                        'value'      => $well['UWI'],
                        'label'      => $well['UWI'] . ' - ' . ($well['WELL_NAME'] ?? 'Sin nombre'),
                        'uwi'        => $well['UWI'],
                        'well_name'  => $well['WELL_NAME']  ?? '',
                        'short_name' => $well['SHORT_NAME'] ?? '',
                        'plot_name'  => $well['PLOT_NAME']  ?? '',
                    ];
                }
            }
            echo json_encode($out);
        } catch (Throwable $e) {
            error_log("Error búsqueda AJAX: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    /* =========================================================
     * Vista de prueba de permisos (legacy)
     * =======================================================*/
    public function testUpdatePermissionView(): void
    {
        $this->requireAuth();

        $test_result = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['run_test'])) {
            $test_result = Well::testUpdatePermission();
        }
        $this->render('test_update_permission', compact('test_result'));
    }

    /* =========================================================
     * --------- HELPERS PRIVADOS (carga, render, etc.) --------
     * =======================================================*/

    private function loadWellShowData(string $uwi): array
    {
        $uwi = trim($uwi);
        $error = null;
        $well_details = null;
        $related_wells = [];
        $options = [];

        if ($uwi === '') {
            return ['error' => 'UWI vacío.', 'well_details' => null, 'related_wells' => [], 'options' => []];
        }

        // Detalle base
        $data = Well::getWellDetailsByUWI($uwi);
        if (isset($data['error'])) {
            $error = $data['error'];
        } else {
            $well_details = $data;

            // Enriquecimiento adicional (con logs silenciosos si no existen tablas)
            $node = Well::getNodeDetailsByUWI($uwi);
            if ($node && !isset($node['error'])) $well_details = array_merge($well_details, $node);

            $remarks = Well::getWellRemarksByUWI($uwi);
            if ($remarks && !isset($remarks['error'])) $well_details = array_merge($well_details, $remarks);

            $alias = Well::getWellAliasByUWI($uwi);
            if ($alias && !isset($alias['error'])) $well_details = array_merge($well_details, $alias);

            $coords = Well::getCoordinatesByUWI($uwi);
            if ($coords && !isset($coords['error'])) $well_details = array_merge($well_details, $coords);

            // Displays y derivados (si faltan, el modelo también prepara)
            $well_details = $this->prepareDisplays($well_details);
        }

        // Relacionados
        $related_wells = Well::getRelatedWells($uwi);
        if (isset($related_wells['error'])) {
            error_log("Error pozos relacionados: " . $related_wells['error']);
            $related_wells = [];
        }

        // Opciones para selects
        $options = $this->loadOptions();

        return compact('error','well_details','related_wells','options');
    }

    private function loadOptions(): array
    {
        return [
            'INITIAL_CLASS'   => Well::getWellClassCodes('INITIAL CLASS'),
            'CLASS'           => Well::getWellClassCodes('CLASS'),
            'CURRENT_CLASS'   => Well::getWellClassCodes(),
            'ORSTATUS'        => Well::getWellStatusCodes(),
            'CRSTATUS'        => Well::getWellStatusCodes(),
            'DEVIATION_FLAG'  => Well::getDeviationFlagOptions(),
            'DISTRICT'        => Well::getAdminDistricts(),
            'OPERATOR'        => Well::getOperatorOptions(),
            'LICENSEE'        => Well::getBusinessAssoc(),
            'AGENT'           => Well::getAgentOptions(),
            'FIELD'           => Well::getFieldHeaders(),
            'BLOCK_ID'        => Well::getBlockOptions(),
            'ELEVATION_REF'   => Well::getWellElevRefOptions(),
            'COUNTRY'         => Well::getCountryOptions(),
            'COUNTY'          => Well::getCountyOptions(),
            'PLOT_SYMBOL'     => Well::getPlotSymbolOptions(),
            'DISCOVER_WELL'   => Well::getDiscoverWellOptions(),
            'PRIMARY_SOURCE'  => Well::getRSourceOptions(),
            'RIG_NO'          => Well::getRigOptions(),
            'HOLE_DIRECTION'  => Well::getHoleDirectionOptions(),
            'LEASE_NO'        => Well::getLeaseOptions(),
            'GEOLOGIC_PROVINCE'=> Well::getGeologicProvinces(),
            'CONTRACTOR'      => Well::getBusinessAssoc(),
            'PROV_ST'         => Well::getProvStOptions(),
            'WELL_HDR_TYPE'   => Well::getWellHdrTypeOptions(),
        ];
    }

    private function prepareDisplays(array $wd): array
    {
        // Si el modelo ya rellenó *_DISPLAY perfecto; si no, completa aquí con descripciones
        // País
        if (!empty($wd['COUNTRY']) && empty($wd['COUNTRY_DISPLAY'])) {
            $desc = Well::getMiscCodeDescription($wd['COUNTRY'], 'COUNTRY');
            $wd['COUNTRY_DISPLAY'] = $desc ? "{$wd['COUNTRY']} - {$desc}" : $wd['COUNTRY'];
        }
        // Provincia geológica
        if (!empty($wd['GEOLOGIC_PROVINCE']) && empty($wd['GEOLOGIC_PROVINCE_DISPLAY'])) {
            $desc = Well::getGeologicProvinceDescription($wd['GEOLOGIC_PROVINCE']);
            $wd['GEOLOGIC_PROVINCE_DISPLAY'] = $desc ? "{$wd['GEOLOGIC_PROVINCE']} - {$desc}" : $wd['GEOLOGIC_PROVINCE'];
        }
        // County
        if (!empty($wd['COUNTY']) && empty($wd['COUNTY_DISPLAY'])) {
            $desc = Well::getCountyDescriptionByCode($wd['COUNTY']);
            $wd['COUNTY_DISPLAY'] = $desc ? "{$wd['COUNTY']} - {$desc}" : $wd['COUNTY'];
        }
        // Field
        if (!empty($wd['FIELD']) && empty($wd['FIELD_DISPLAY'])) {
            $name = Well::getFieldNameByCode($wd['FIELD']);
            $wd['FIELD_DISPLAY'] = $name ? "{$wd['FIELD']} - {$name}" : $wd['FIELD'];
            $wd['GEOGRAPHIC_AREA'] = Well::getGeographicAreaByFieldCode($wd['FIELD']);
        }
        return $wd;
    }

    /* ------------ Utilidades web ------------ */

    private function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    private function render(string $view, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/../views/' . $view . '.php';
    }

    private function json($payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }

    private function wantsJson(): bool
    {
        if (isset($_GET['format']) && strtolower((string)$_GET['format']) === 'json') return true;
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return stripos($accept, 'application/json') !== false;
    }

    private function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw ?? '', true);
            return is_array($data) ? $data : [];
        }
        // x-www-form-urlencoded / multipart
        $data = $_POST;
        unset($data['_method'], $data['csrf_token']);
        return $data;
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'][$type] = $msg;
    }

    private function csrf(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Filtra la carga útil únicamente a columnas permitidas por el modelo.
     * Evita sobre-escritura de claves no deseadas (p.ej. UWI).
     */
    private function filterAllowed(array $payload): array
    {
        // Si el modelo expone su lista de columnas permitidas, úsala:
        $refAllowed = (new ReflectionClass('Well'))->getProperty('allowedUpdateColumns');
        $refAllowed->setAccessible(true);
        $allowed = $refAllowed->getValue();

        $out = [];
        foreach ($payload as $k => $v) {
            $col = strtoupper((string)$k);
            if ($col === 'UWI') continue; // clave primaria fuera del set
            if (in_array($col, $allowed, true)) {
                $out[$col] = is_string($v) ? trim($v) : $v;
            }
        }
        return $out;
    }

    private function fail(string $message, bool $asJson, string $redirect = null): void
    {
        if ($asJson) {
            $this->json(['success' => false, 'error' => $message], 400);
            return;
        }
        if ($redirect) {
            $this->flash('error', $message);
            header('Location: ' . $redirect);
            exit;
        }
        http_response_code(400);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit;
    }
}
