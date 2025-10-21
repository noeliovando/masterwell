<?php
// controllers/WellDirectoryController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/WellDirectory.php';
require_once __DIR__ . '/../includes/Auth.php'; // si ya usas Auth
require_once __DIR__ . '/../models/Well.php';

class WellDirectoryController
{
    public function index()
    {
        // Filtros desde GET
        $filters = [
            'uwi'                => $_GET['uwi'] ?? '',
            'location'           => $_GET['location'] ?? '',
            'exploitation_unit'  => $_GET['exploitation_unit'] ?? '',
            'operator'           => $_GET['operator'] ?? '',
            'field'              => $_GET['field'] ?? '',
            'district'           => $_GET['district'] ?? '',
            'gov'                => $_GET['gov'] ?? '',
            'spud_from'          => $_GET['spud_from'] ?? '',
            'spud_to'            => $_GET['spud_to'] ?? '',
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $sort = $_GET['sort'] ?? 'UWI';
        $dir  = $_GET['dir']  ?? 'ASC';

        // 🔧 Instanciar el modelo (NO llamar estático)
        $model = new WellDirectory();
        $result = $model->search($filters, $page, $perPage, $sort, $dir);

        // Variables para la vista
        $rows        = $result['rows'];
        $total       = (int)$result['total'];
        $currentPage = (int)$result['page'];
        $totalPages  = (int)$result['pages'];
        $perPage     = (int)$result['perPage'];
        $sort        = $result['sort'];
        $dir         = $result['dir'];

        require __DIR__ . '/../views/well_directory/index.php';
    }

    public function details($uwi)
    {
        $uwi = urldecode($uwi);

        // Modelo
        $model = new WellDirectory();

        // Un solo “paquete” para la vista
        $bundle = $model->getDetailsBundle($uwi);

        if (isset($bundle['error'])) {
            $this->renderError($bundle['error']);
            return;
        }

        $details = $bundle['details'];
        $coords  = $bundle['coords'];
        $alias   = $bundle['alias'];
        $remarks = $bundle['remarks'];
        $related = $bundle['related'];
        $uwi     = $details['UWI'] ?? $uwi;

        require __DIR__ . '/../views/well_directory/details.php';
    }

    private function render($viewPath, $data = [])
    {
        extract($data);
        require $viewPath;
    }

    private function renderError($message)
    {
        $data = ['message' => $message];
        // puedes crear una vista de error genérica si prefieres
        echo "<link rel='stylesheet' href='".BASE_PATH."/styles/well_directory_details.css'>";
        echo "<div style='padding:16px'><h2>Error</h2><p>".htmlspecialchars($message,ENT_QUOTES,'UTF-8')."</p></div>";
    }

    public function edit(string $uwi)
    {
        $uwi = urldecode($uwi);
        $model = new WellDirectory();

        // Datos mínimos para edición (rápidos)
        $details = $model->getDetailsForEdit($uwi);
        if (isset($details['error'])) {
            return $this->renderError($details['error']);
        }

        require __DIR__ . '/../views/well_directory/edit.php';
    }

    public function update()
    {
        // Acepta JSON (fetch) o application/x-www-form-urlencoded
        $isJson = (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        $payload = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;

        $uwi = trim($payload['uwi'] ?? '');
        if ($uwi === '') {
            return $this->json(['ok' => false, 'message' => 'UWI requerido.'], 400);
        }

        $model = new WellDirectory();
        $result = $model->updateWellPartial($uwi, $payload);

        if (!empty($result['ok'])) {
            return $this->json(['ok' => true, 'message' => 'Cambios guardados.', 'redirect' => BASE_PATH . '/wells-directory/details/' . urlencode($uwi)]);
        } else {
            $msg = $result['message'] ?? 'No se pudo actualizar.';
            return $this->json(['ok' => false, 'message' => $msg], 422);
        }
    }

    // Helpers para responder JSON
    private function json(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        return;
    }
}
