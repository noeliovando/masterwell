<?php
// includes/Auth.php

class Auth {
    public static function login($username, $password, $db_instance) {
        // Incluir la configuración de la base de datos 
        require_once __DIR__ . '/../config.php';
        global $db_instances;

        if (!array_key_exists($db_instance, $db_instances)) {
            $_SESSION['login_error'] = 'Instancia de base de datos no válida.';
            return false;
        }

        $dsn = $db_instances[$db_instance];
        $options = DB_OPTIONS;

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Verificar que el usuario tenga el rol WELL_ADMIN
            if (!self::hasWellAdminRole($pdo, $username)) {
                $_SESSION['login_error'] = 'Acceso denegado: El usuario no tiene permisos de WELL_ADMIN para acceder a esta aplicación.';
                return false;
            }
            
            // Si la conexión es exitosa y tiene el rol correcto, guardar las credenciales en la sesión
            $_SESSION['db_credentials'] = [
                'user' => $username,
                'pass' => $password,
                'db_instance' => $db_instance
            ];
            
            // Limpiar cualquier error de sesión anterior
            unset($_SESSION['login_error']);
            return true;

        } catch (PDOException $e) {
            $_SESSION['login_error'] = 'Error de conexión: ' . $e->getMessage();
            return false;
        }
    }

    public static function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check() {
        return isset($_SESSION['db_credentials']);
    }

    /**
     * Verifica si el usuario tiene el rol WELL_ADMIN
     * @param PDO $pdo Conexión a la base de datos
     * @param string $username Nombre del usuario
     * @return bool True si tiene el rol, False si no
     */
    private static function hasWellAdminRole($pdo, $username) {
        try {
            $sql = "SELECT granted_role 
                    FROM user_role_privs 
                    WHERE username = UPPER(:username) 
                    AND granted_role = 'WELL_ADMIN'";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);
            $result = $stmt->fetch();
            
            // Log para debugging (opcional)
            if ($result) {
                error_log("Usuario {$username} tiene rol WELL_ADMIN - Acceso permitido");
                return true;
            } else {
                error_log("Usuario {$username} NO tiene rol WELL_ADMIN - Acceso denegado");
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Error al verificar rol WELL_ADMIN para usuario {$username}: " . $e->getMessage());
            // En caso de error en la consulta, denegar acceso por seguridad
            return false;
        }
    }
}
?>
