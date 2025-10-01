<?php
// controllers/TableController.php
require_once __DIR__ . '/../db.php'; // Para get_db_connection()

class TableController {
    public function details() {
        require_once __DIR__ . '/../partials/header.php';

        $owner_input = $_GET['owner'] ?? '';
        $table_input = $_GET['tablename'] ?? '';
        $columns = [];
        $data = [];
        $error = null;

        if (!empty($owner_input) && !empty($table_input)) {
            $pdo = get_db_connection();
            if ($pdo) {
                try {
                    $owner = strtoupper(trim($owner_input));
                    $table_name = strtoupper(trim($table_input));

                    // Consulta para obtener las columnas
                    $query_columns = "SELECT column_name, data_type, data_length, nullable 
                                      FROM all_tab_columns 
                                      WHERE owner = :owner AND table_name = :tablename 
                                      ORDER BY column_id";
                    
                    $stmt_columns = $pdo->prepare($query_columns);
                    $stmt_columns->execute([':owner' => $owner, ':tablename' => $table_name]);
                    $columns = $stmt_columns->fetchAll();

                    if (count($columns) > 0) {
                        // Consulta para obtener vista previa de los datos
                        // Se usa PDO::query para SELECT * ya que el nombre de la tabla es dinámico
                        // y ya se ha validado que existe en all_tab_columns.
                        // Limitar a 50 filas para evitar sobrecarga.
                        $stmt_data = $pdo->query("SELECT * FROM " . $owner . "." . $table_name . " WHERE ROWNUM <= 50");
                        $data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $error = "No se encontraron detalles. Verifica que el propietario y el nombre de la tabla sean correctos y que tengas permisos para verla.";
                    }

                } catch (PDOException $e) {
                    $error = "Error de Base de Datos: " . $e->getMessage();
                    error_log("Error en TableController::details: " . $e->getMessage());
                } finally {
                    $pdo = null;
                }
            } else {
                $error = "No se pudo establecer conexión con la base de datos.";
            }
        }

        // Cargar la vista
        require_once __DIR__ . '/../views/table_details.php';
        require_once __DIR__ . '/../partials/footer.php';
    }
}
?>
