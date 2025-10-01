<?php require_once 'partials/header.php'; ?>

<h1>Consola SQL*Plus</h1>

<p>Escribe tu consulta SQL en el siguiente cuadro de texto. La consulta se ejecutará a través de SQL*Plus con las credenciales de tu sesión actual.</p>
<p><strong>Nota:</strong> Por favor, ten cuidado. Las consultas de modificación de datos (UPDATE, DELETE, INSERT) se ejecutarán y confirmarán.</p>

<form action="<?php echo BASE_PATH; ?>/sqlplus" method="post">
    <div class="form-group">
        <label for="sql_query">Consulta SQL:</label>
        <textarea id="sql_query" name="sql_query" rows="10" style="width: 100%; font-family: monospace;"><?php echo htmlspecialchars($sql_query); ?></textarea>
    </div>
    <button type="submit">Ejecutar Consulta</button>
</form>

<?php if (isset($query_result)): ?>
    <h2>Resultado de la Consulta:</h2>
    <div class="sql-result-container">
        <?php echo $query_result; // La salida ya está formateada como HTML ?>
    </div>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
