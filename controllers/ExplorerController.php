<?php
// controllers/ExplorerController.php
require_once __DIR__ . '/../db.php'; // Para get_db_connection()

class ExplorerController {
    public function index() {
        require_once __DIR__ . '/../partials/header.php';

        $view_mode = $_GET['view'] ?? 'user'; // 'user', 'all', o 'all_columns'
        $owner_input = $_GET['owner'] ?? '';
        $table_input = $_GET['tablename'] ?? '';

        $tables = [];
        $columns_list = []; // Para la nueva vista de todos los campos
        $columns = [];
        $data = [];
        $error = null;

        $pdo = get_db_connection();
        if ($pdo) {
            try {
                // --- 1. OBTENER Y MOSTRAR LISTA DE TABLAS / CAMPOS ---
                if ($view_mode === 'all') {
                    // Filtrar por PDVSA y CODES por defecto
                    $query_all_tables = "SELECT owner, table_name FROM all_tables WHERE owner IN ('PDVSA', 'CODES')";
                    $params = [];
                    if (!empty($owner_input)) {
                        // Si el usuario especifica un filtro, se aplica
                        $query_all_tables = "SELECT owner, table_name FROM all_tables WHERE owner = :owner";
                        $params[':owner'] = strtoupper($owner_input);
                    }
                    $query_all_tables .= " ORDER BY owner, table_name";
                    
                    $stmt_tables = $pdo->prepare($query_all_tables);
                    $stmt_tables->execute($params);
                    $tables = $stmt_tables->fetchAll();

                } elseif ($view_mode === 'all_columns') {
                    // Nueva vista: todos los campos de tablas de PDVSA y CODES
                    $query_all_columns = "SELECT ATC.owner, ATC.table_name, ATC.column_name, ATC.data_type, ATC.data_length, ATC.nullable
                                          FROM all_tab_columns ATC
                                          WHERE ATC.owner IN ('PDVSA', 'CODES')
                                          ORDER BY ATC.owner, ATC.table_name, ATC.column_id";
                    $stmt_all_columns = $pdo->prepare($query_all_columns);
                    $stmt_all_columns->execute();
                    $columns_list = $stmt_all_columns->fetchAll();

                } else { // view_mode === 'user'
                    // Obtener el nombre de usuario de la sesión para user_tables
                    $username = $_SESSION['db_credentials']['user'] ?? '';
                    if (empty($username)) {
                        $error = "No se pudo obtener el nombre de usuario de la sesión.";
                    } else {
                        $stmt_tables = $pdo->query("SELECT table_name FROM user_tables ORDER BY table_name");
                        $tables = $stmt_tables->fetchAll(PDO::FETCH_COLUMN);
                    }
                }

                // --- 3. MOSTRAR DETALLES SI SE ESPECIFICÓ UNA TABLA (EXISTENTE) ---
                if (isset($_GET['tablename']) && !empty($_GET['tablename'])) {
                    $input_table = trim($_GET['tablename']);
                    $owner = $_SESSION['db_credentials']['user'] ?? ''; // Default a usuario actual
                    $table_name = $input_table;
                    $dictionary_view = 'user_tab_columns';

                    // Si estamos en vista 'all' o 'all_columns' y el usuario usa el formato PROPIETARIO.TABLA
                    if (($view_mode === 'all' || $view_mode === 'all_columns') && strpos($input_table, '.') !== false) {
                        list($owner, $table_name) = explode('.', $input_table, 2);
                        $dictionary_view = 'all_tab_columns';
                    }
                    
                    $owner = strtoupper($owner);
                    $table_name = strtoupper($table_name);

                    $query_columns = "SELECT column_name, data_type, data_length, nullable 
                                      FROM " . $dictionary_view . " 
                                      WHERE owner = :owner AND table_name = :tablename 
                                      ORDER BY column_id";
                    
                    $stmt_columns = $pdo->prepare($query_columns);
                    $stmt_columns->execute([':owner' => $owner, ':tablename' => $table_name]);
                    $columns = $stmt_columns->fetchAll();

                    if (count($columns) > 0) {
                        // --- 4. MOSTRAR VISTA PREVIA DE LOS DATOS ---
                        $stmt_data = $pdo->query("SELECT * FROM " . $owner . "." . $table_name . " WHERE ROWNUM <= 20");
                        $data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $error = "No se encontraron detalles o la tabla '" . htmlspecialchars($input_table) . "' no existe o no es accesible.";
                    }
                }

            } catch (PDOException $e) {
                $error = "Error de Base de Datos: " . $e->getMessage();
                error_log("Error en ExplorerController::index: " . $e->getMessage());
            } finally {
                $pdo = null;
            }
        } else {
            $error = "No se pudo establecer conexión con la base de datos.";
        }

        // Cargar la vista
        require_once __DIR__ . '/../views/explorer.php';
        require_once __DIR__ . '/../partials/footer.php';
    }
}
?>
