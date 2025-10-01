<?php
require_once 'partials/header.php';
?>

<h1>Esquema de la Tabla PDVSA.WELL_HDR</h1>

<?php if (isset($schema['error'])): ?>
    <p class="error"><?php echo htmlspecialchars($schema['error']); ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nombre de la Columna</th>
                <th>Tipo de Dato</th>
                <th>Descripción Conceptual</th>
                <th>Referencias (FK)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schema as $column): ?>
                <tr>
                    <td><?php echo htmlspecialchars($column['column_name']); ?></td>
                    <td><?php echo htmlspecialchars($column['data_type']); ?></td>
                    <td><?php echo htmlspecialchars($fieldDescriptions[strtoupper($column['column_name'])] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($column['references']): ?>
                            <?php echo htmlspecialchars(strtoupper($column['references']['references_owner']) . '.' . strtoupper($column['references']['references_table']) . '(' . strtoupper($column['references']['references_column']) . ')'); ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require_once 'partials/footer.php';
?>
