<?php require_once 'partials/header.php'; ?>

<h1>Prueba de Permisos de Actualización en PDVSA.WELL_HDR</h1>

<p>Esta página permite probar si el usuario actual tiene permisos para ejecutar una consulta UPDATE en la tabla PDVSA.WELL_HDR.</p>
<p>La consulta de prueba intentará actualizar el campo <code>GEOLOGIC_PROVINCE</code> a <code>'01'</code> para el pozo con <code>UWI = '007WHTOM0001 1'</code>.</p>

<form action="<?php echo BASE_PATH; ?>/well/testUpdatePermission" method="post">
    <button type="submit" name="run_test">Ejecutar Prueba de Actualización</button>
</form>

<?php if (isset($test_result)): ?>
    <h2>Resultado de la Prueba:</h2>
    <?php if ($test_result['success']): ?>
        <p style="color: green;"><strong>Éxito:</strong> <?php echo htmlspecialchars($test_result['message']); ?></p>
    <?php else: ?>
        <p style="color: red;"><strong>Error:</strong> <?php echo htmlspecialchars($test_result['message']); ?></p>
        <?php if (strpos($test_result['message'], 'ORA-01031') !== false): ?>
            <p style="color: orange;">El error ORA-01031 indica que el usuario de la base de datos no tiene los privilegios necesarios para realizar la operación UPDATE en la tabla PDVSA.WELL_HDR.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
