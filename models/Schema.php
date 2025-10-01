<?php
// models/Schema.php
require_once __DIR__ . '/../db.php';

class Schema {
    public static function getTableSchema($tableName, $filterColumns = []) {
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $parts = explode('.', $tableName);
                $owner = strtoupper($parts[0]);
                $actualTableName = strtoupper($parts[1]);

                // 1. Get basic column info
                $sqlColumns = "SELECT column_name, data_type FROM all_tab_columns WHERE owner = :owner AND table_name = :table_name";
                $stmtColumns = $pdo->prepare($sqlColumns);
                $stmtColumns->execute([':owner' => $owner, ':table_name' => $actualTableName]);
                $rawSchema = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);

                // 2. Get foreign key info using older Oracle join syntax
                $foreignKeys = [];
                $sqlFk = "
                    SELECT
                        acc.column_name AS referencing_column,
                        ac.r_owner AS referenced_owner,
                        r_ac.table_name AS referenced_table,
                        r_acc.column_name AS referenced_column
                    FROM
                        all_cons_columns acc,
                        all_constraints ac,
                        all_constraints r_ac,
                        all_cons_columns r_acc
                    WHERE
                        acc.owner = ac.owner
                        AND acc.constraint_name = ac.constraint_name
                        AND ac.r_owner = r_ac.owner
                        AND ac.r_constraint_name = r_ac.constraint_name
                        AND r_ac.owner = r_acc.owner
                        AND r_ac.constraint_name = r_acc.constraint_name
                        AND r_acc.position = acc.position
                        AND ac.constraint_type = 'R'
                        AND acc.owner = :owner
                        AND acc.table_name = :table_name
                ";
                $sqlFk = str_replace(["\r", "\n"], ' ', $sqlFk); // Clean up newlines

                error_log("FK Query (Oracle 8 syntax): " . $sqlFk);
                error_log("FK Params: Owner=" . $owner . ", Table=" . $actualTableName);

                $stmtFk = $pdo->prepare($sqlFk);
                $stmtFk->execute([':owner' => $owner, ':table_name' => $actualTableName]);
                $rawForeignKeys = $stmtFk->fetchAll(PDO::FETCH_ASSOC);
                error_log("FK Raw Results: " . print_r($rawForeignKeys, true));

                foreach ($rawForeignKeys as $fk) {
                    $referencingColumn = strtoupper($fk['REFERENCING_COLUMN']);
                    $foreignKeys[$referencingColumn] = [
                        'references_owner' => strtolower($fk['REFERENCED_OWNER']),
                        'references_table' => strtolower($fk['REFERENCED_TABLE']),
                        'references_column' => strtolower($fk['REFERENCED_COLUMN'])
                    ];
                }

                // 3. Merge and filter
                $filteredSchema = [];
                $columnNamesFilter = array_map('strtoupper', $filterColumns);

                foreach ($rawSchema as $row) {
                    $columnName = strtoupper($row['COLUMN_NAME']);
                    if (empty($filterColumns) || in_array($columnName, $columnNamesFilter)) {
                        $columnInfo = [
                            'column_name' => strtolower($row['COLUMN_NAME']),
                            'data_type' => strtolower($row['DATA_TYPE']),
                            'references' => null // Default to null
                        ];

                        if (isset($foreignKeys[$columnName])) {
                            $columnInfo['references'] = $foreignKeys[$columnName];
                        }
                        $filteredSchema[] = $columnInfo;
                    }
                }
                return $filteredSchema;
            } catch (PDOException $e) {
                error_log("Error al obtener el esquema de la tabla: " . $e->getMessage());
                return ['error' => "Error al obtener el esquema de la tabla: " . $e->getMessage()];
            } finally {
                $pdo = null;
            }
        } else {
            return ['error' => "No se pudo establecer conexión con la base de datos."];
        }
    }

    public static function getFieldDescriptions() {
        return require __DIR__ . '/../docs/well_hdr_field_descriptions.php';
    }
}
?>
