<h1>Detalles de Tablas</h1>

<form method='get' action='<?php echo BASE_PATH; ?>/tables/details'>
  <label for='owner'>Propietario:</label>
  <input type='text' id='owner' name='owner' value='<?php echo htmlspecialchars($owner_input); ?>' required>
  <label for='tablename'>Nombre de Tabla:</label>
  <input type='text' id='tablename' name='tablename' value='<?php echo htmlspecialchars($table_input); ?>' required>
  <button type='submit'>Inspeccionar Tabla</button>
</form>

<?php if ($error): ?>
    <p style='color:red;'>Error: <?php echo htmlspecialchars($error); ?></p>
<?php elseif (!empty($owner_input) && !empty($table_input)): ?>
    <hr>
    <h2>Detalles de la tabla: <?php echo htmlspecialchars($owner . '.' . $table_name); ?></h2>

    <?php if (count($columns) > 0): ?>
        <h3>Estructura de Columnas (Campos)</h3>
        <table>
            <tr><th>Nombre Columna</th><th>Tipo de Dato</th><th>Longitud</th><th>Admite Nulos</th></tr>
            <?php foreach ($columns as $col): ?>
                <tr>
                    <td><?php echo htmlspecialchars($col['COLUMN_NAME']); ?></td>
                    <td><?php echo htmlspecialchars($col['DATA_TYPE']); ?></td>
                    <td><?php echo htmlspecialchars($col['DATA_LENGTH']); ?></td>
                    <td><?php echo ($col['NULLABLE'] === 'Y' ? 'Sí' : 'No'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Vista Previa de Datos (primeras 50 filas)</h3>
        <?php if (count($data) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <tr>
                        <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?php echo htmlspecialchars($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?php echo htmlspecialchars($cell ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <p>La tabla está vacía o no se pudieron obtener datos.</p>
        <?php endif; ?>

    <?php else: ?>
        <p style='color:red;'>No se encontraron detalles. Verifica que el propietario y el nombre de la tabla sean correctos y que tengas permisos para verla.</p>
    <?php endif; ?>
<?php endif; ?>
