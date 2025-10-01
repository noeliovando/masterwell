<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - App de Pozos</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-container-login">
            <img src="<?php echo BASE_PATH; ?>/images/MW_Logo_combinado.png" alt="Logo de la Empresa" class="company-logo-combinado">
        </div>
        <div class="login-title-container">
            <h1>Gestión y Administración de Datos de Pozos</h1>
        </div>
        <form action="<?php echo BASE_PATH; ?>/auth" method="post">
            <div class="form-group">
                <label for="db_instance">Seleccione la instancia de la base de datos</label>
                <select class="login-editable-field" name="db_instance" id="db_instance" required>
                    <?php foreach ($db_instances as $name => $dsn): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user">Usuario de la BD:</label>
                <input class="editable-field" type="text" id="user" name="user" required>
            </div>
            <div class="form-group">
                <label for="pass">Contraseña de la BD:</label>
                <input class="editable-field" type="password" id="pass" name="pass" required>
            </div>
            <?php if (isset($error) && $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <button type="submit">Conectar</button>
        </form>
    </div>
</body>
</html>
