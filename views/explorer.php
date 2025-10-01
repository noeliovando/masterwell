<h1>Explorador de Base de Datos Oracle</h1>

<div class='view-switcher'>
  <b>Ver:</b> 
  <a href='<?php echo BASE_PATH; ?>/explorer?view=user'>Mis Tablas</a>
  <a href='<?php echo BASE_PATH; ?>/explorer?view=all'>Todas las Tablas Accesibles</a>
  <a href='<?php echo BASE_PATH; ?>/explorer?view=all_columns'>Todos los Campos (PDVSA/CODES)</a>
</div>

<?php if ($error): ?>
    <h1 style='color:red;'>Error de Base de Datos</h1>
    <pre>Error: <?php echo htmlspecialchars($error); ?></pre>
<?php else: ?>

    <?php if ($view_mode === 'all'): ?>
        <h2>Todas las Tablas Accesibles (Propietarios: PDVSA, CODES)</h2>

        <form method='get' action='<?php echo BASE_PATH; ?>/explorer' style='margin-bottom: 1em;'>
          <input type='hidden' name='view' value='all'>
          <label for='owner'>Filtrar por Propietario (dejar vacío para PDVSA/CODES):</label>
          <input type='text' id='owner' name='owner' value='<?php echo htmlspecialchars($owner_input); ?>'>
          <button type='submit'>Filtrar</button>
        </form>

        <?php if (count($tables) > 0): ?>
            <table>
                <tr><th>Propietario</th><th>Nombre Tabla</th></tr>
                <?php foreach ($tables as $table): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($table['OWNER']); ?></td>
                        <td><?php echo htmlspecialchars($table['TABLE_NAME']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No se encontraron tablas.</p>
        <?php endif; ?>

    <?php elseif ($view_mode === 'all_columns'): ?>
        <h2>Todos los Campos de Tablas (Propietarios: PDVSA, CODES)</h2>
        <?php if (count($columns_list) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Propietario</th>
                            <th>Nombre Tabla</th>
                            <th>Nombre Columna</th>
                            <th>Tipo de Dato</th>
                            <th>Longitud</th>
                            <th>Admite Nulos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($columns_list as $col): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($col['OWNER']); ?></td>
                                <td><?php echo htmlspecialchars($col['TABLE_NAME']); ?></td>
                                <td><?php echo htmlspecialchars($col['COLUMN_NAME']); ?></td>
                                <td><?php echo htmlspecialchars($col['DATA_TYPE']); ?></td>
                                <td><?php echo htmlspecialchars($col['DATA_LENGTH']); ?></td>
                                <td><?php echo ($col['NULLABLE'] === 'Y' ? 'Sí' : 'No'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron campos.</p>
        <?php endif; ?>

    <?php else: // view_mode === 'user' ?>
        <h2>Mis Tablas (Usuario: <?php echo htmlspecialchars($_SESSION['db_credentials']['user'] ?? 'N/A'); ?>)</h2>
        <?php if (count($tables) > 0): ?>
            <ul>
                <?php foreach ($tables as $table): ?>
                    <li><?php echo htmlspecialchars($table); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No se encontraron tablas.</p>
        <?php endif; ?>
    <?php endif; ?>

    <hr>
    <h2>Ver Detalles de una Tabla</h2>
    <form method='get' action='<?php echo BASE_PATH; ?>/explorer'>
      <input type='hidden' name='view' value='<?php echo htmlspecialchars($view_mode); ?>'>
      <label for='tablename'>Nombre de la Tabla:</label>
      <input type='text' id='tablename' name='tablename' size='40' required placeholder='<?php echo ($view_mode === 'all' ? 'PROPIETARIO.TABLA' : 'MI_TABLA'); ?>' value='<?php echo htmlspecialchars($table_input); ?>'>
      <button type='submit'>Mostrar Detalles</button>
    </form>

    <?php if (isset($_GET['tablename']) && !empty($_GET['tablename'])): ?>
        <h3>Detalles de la tabla: <?php echo htmlspecialchars($owner . '.' . $table_name); ?></h3>

        <?php if (count($columns) > 0): ?>
            <h4>Estructura de Columnas (Campos)</h4>
            <table>
                <tr><th>Nombre Columna</th><th>Tipo de Dato</th><th>Longitud</th><th>Admite Nulos</th></tr>
                <?php foreach ($columns as $col): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($col['COLUMN_NAME']); ?></td>
                        <td><?php echo htmlspecialchars($col['DATA_TYPE']); ?></td>
                        <td><?php htmlspecialchars($col['DATA_LENGTH']); ?></td>
                        <td><?php echo ($col['NULLABLE'] === 'Y' ? 'Sí' : 'No'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h4>Vista Previa de Datos (primeras 20 filas)</h4>
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
                <p>La tabla está vacía.</p>
            <?php endif; ?>

        <?php else: ?>
            <p style='color:red;'>No se encontraron detalles o la tabla '<?php echo htmlspecialchars($input_table); ?>' no existe o no es accesible.</p>
        <?php endif; ?>
    <?php endif; ?>

<?php endif; ?>
