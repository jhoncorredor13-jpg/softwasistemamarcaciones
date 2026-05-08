<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';

class AuthController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuario/login.php");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Campos incompletos',
                'text' => 'Debe ingresar correo y contraseña'
            ];
            header("Location: ../views/usuario/login.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Correo inválido',
                'text' => 'Ingrese un correo electrónico válido'
            ];
            header("Location: ../views/usuario/login.php");
            exit;
        }

        try {
            $database = new Database();
            $db = $database->conectar();

            $usuarioModel = new Usuario($db);
            $usuario = $usuarioModel->obtenerPorEmail($email);

            if (!$usuario) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Usuario no encontrado',
                    'text' => 'El correo no está registrado o está inactivo'
                ];
                header("Location: ../views/usuario/login.php");
                exit;
            }

            if (!password_verify($password, $usuario['password_hash'])) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Contraseña incorrecta',
                    'text' => 'Verifique sus credenciales'
                ];
                header("Location: ../views/usuario/login.php");
                exit;
            }

            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'nombres'    => $usuario['nombres'],
                'apellidos'  => $usuario['apellidos'],
                'email'      => $usuario['email'],
                'rol'        => $usuario['rol']
            ];

            switch (strtolower($usuario['rol'])) {
                case 'administrador':
                    header("Location: ../views/dashboard/admin.php");
                    exit;

                case 'trabajador':
                    header("Location: ../views/dashboard/trabajador.php");
                    exit;

                default:
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Rol no válido',
                        'text' => 'No se pudo determinar el acceso del usuario'
                    ];
                    header("Location: ../views/usuario/login.php");
                    exit;
            }
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error del sistema',
                'text' => 'Ocurrió un problema al iniciar sesión: ' . $e->getMessage()
            ];
            header("Location: ../views/usuario/login.php");
            exit;
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: ../views/usuario/login.php");
        exit;
    }
    public function recuperar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuario/login.php");
            exit;
        }

        $email = trim($_POST['email_recovery'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Correo inválido',
                'text' => 'Por favor ingrese un correo válido para recuperar su contraseña.'
            ];
            header("Location: ../views/usuario/login.php");
            exit;
        }

        try {
            $database = new Database();
            $db = $database->conectar();
            $usuarioModel = new Usuario($db);

            $usuario = $usuarioModel->obtenerPorEmail($email);

            if ($usuario) {
                // Generar contraseña temporal fácil: inicial del nombre + 123456
                $inicial = strtolower(substr($usuario['nombres'], 0, 1));
                $tempPassword = $inicial . '123456';
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

                if ($usuarioModel->actualizarPassword($email, $hashedPassword)) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Contraseña recuperada',
                        'text' => 'Su nueva contraseña temporal es: <b>' . $tempPassword . '</b> <br><br>Cópiela e inicie sesión.'
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error del sistema',
                        'text' => 'No se pudo generar la nueva contraseña.'
                    ];
                }
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Correo no encontrado',
                    'text' => 'El correo ingresado no pertenece a ningún usuario registrado.'
                ];
            }
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error del sistema',
                'text' => 'Ocurrió un problema: ' . $e->getMessage()
            ];
        }

        header("Location: ../views/usuario/login.php");
        exit;
    }
}

$controller = new AuthController();
$accion = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} elseif ($accion === 'recuperar') {
    $controller->recuperar();
} else {
    $controller->login();
}

