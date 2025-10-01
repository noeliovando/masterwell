<?php
// controllers/AuthController.php

class AuthController {
    public function login() {
        require_once __DIR__ . '/../config.php';
        $db_instances = $GLOBALS['db_instances'];
        $error = $_SESSION['login_error'] ?? null;
        if ($error) {
            unset($_SESSION['login_error']);
        }
        // The view will be at views/login.php
        require_once __DIR__ . '/../views/login.php';
    }

    public function authenticate() {
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../includes/Auth.php';

        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $db_instance = $_POST['db_instance'] ?? '';

        if (empty($user) || empty($pass) || empty($db_instance)) {
            $_SESSION['login_error'] = 'Por favor, complete todos los campos.';
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }

        if (!array_key_exists($db_instance, $GLOBALS['db_instances'])) {
            $_SESSION['login_error'] = 'Instancia de base de datos no válida.';
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }

        if (Auth::login($user, $pass, $db_instance)) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        } else {
            // Auth::login() sets the error message in the session
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    public function logout() {
        require_once __DIR__ . '/../includes/Auth.php';
        Auth::logout();
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }
}
?>
