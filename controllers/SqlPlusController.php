<?php
// controllers/SqlPlusController.php
require_once __DIR__ . '/../includes/Auth.php';

class SqlPlusController {
    public function index() {
        if (!Auth::check()) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        
        $query_result = null;
        $sql_query = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_query'])) {
            $sql_query = $_POST['sql_query'];
            $query_result = $this->execute($sql_query);
        }

        require_once __DIR__ . '/../views/sqlplus.php';
    }

    private function execute($sql_query) {
        require_once __DIR__ . '/../db.php';
        $pdo = get_db_connection();

        if (!$pdo) {
            return '<p style="color: red;">Error: No se pudo establecer la conexión con la base de datos.</p>';
        }

        try {
            // Limpiar la consulta de punto y coma y espacios al final
            $clean_sql_query = rtrim(trim($sql_query), ';');

            // Determinar el tipo de consulta
            $query_type = strtoupper(strtok($clean_sql_query, " \n\t\r"));

            $stmt = $pdo->prepare($clean_sql_query);
            $stmt->execute();

            if ($query_type === 'SELECT') {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($results) > 0) {
                    $output = '<table class="results-table">';
                    // Encabezados
                    $output .= '<thead><tr>';
                    foreach (array_keys($results[0]) as $header) {
                        $output .= '<th>' . htmlspecialchars($header) . '</th>';
                    }
                    $output .= '</tr></thead>';
                    // Filas
                    $output .= '<tbody>';
                    foreach ($results as $row) {
                        $output .= '<tr>';
                        foreach ($row as $cell) {
                            $output .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $output .= '</tr>';
                    }
                    $output .= '</tbody></table>';
                    return $output;
                } else {
                    return '<p>La consulta no devolvió resultados.</p>';
                }
            } else {
                $rowCount = $stmt->rowCount();
                return '<p style="color: green;">Consulta ejecutada correctamente. Filas afectadas: ' . $rowCount . '</p>';
            }
        } catch (PDOException $e) {
            return '<p style="color: red;"><strong>Error de SQL:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        } finally {
            $pdo = null;
        }
    }
}
?>
