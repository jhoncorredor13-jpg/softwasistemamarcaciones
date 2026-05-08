<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';

class UsuarioController
{
    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuario/registre.php");
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $rol = trim($_POST['rol'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $id_cargo = trim($_POST['id_cargo'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? date('Y-m-d'));

        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password) || empty($rol)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Campos incompletos',
                'text' => 'Debe completar todos los campos obligatorios'
            ];
            header("Location: ../views/usuario/registre.php");
            exit;
        }

        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Contraseñas diferentes',
                'text' => 'Las contraseñas no coinciden'
            ];
            header("Location: ../views/usuario/registre.php");
            exit;
        }

        try {
            $db = (new Database())->conectar();
            $usuarioModel = new Usuario($db);

            if ($usuarioModel->existeCorreo($email)) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Correo existente',
                    'text' => 'Este correo ya está registrado'
                ];
                header("Location: ../views/usuario/registre.php");
                exit;
            }

            $datos = [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'rol' => $rol,
                'telefono' => $telefono,
                'id_cargo' => $id_cargo ?: null,
                'documento' => $documento,
                'fecha_ingreso' => $fecha_ingreso
            ];

            $resultado = $usuarioModel->registrar($datos);

            $_SESSION['alert'] = [
                'icon' => $resultado === true ? 'success' : 'error',
                'title' => $resultado === true ? 'Registro exitoso' : 'Error al registrar',
                'text' => $resultado === true ? 'El usuario fue creado correctamente' : $resultado
            ];

            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error del sistema',
                'text' => $e->getMessage()
            ];
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php#gestion-usuarios");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol = trim($_POST['rol'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $id_cargo = trim($_POST['id_cargo'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

        if (!$id_usuario || empty($nombres) || empty($apellidos) || empty($email) || empty($rol)) {
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'Campos incompletos',
                'text' => 'Debe completar los campos obligatorios'
            ];
            header("Location: ../views/usuario/editar_usuario.php?id=" . $id_usuario);
            exit;
        }

        try {
            $db = (new Database())->conectar();
            $usuarioModel = new Usuario($db);

            $datos = [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'email' => $email,
                'rol' => $rol,
                'telefono' => $telefono,
                'id_cargo' => $id_cargo ?: null,
                'documento' => $documento,
                'fecha_ingreso' => $fecha_ingreso
            ];

            if (!empty($password)) {
                $datos['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $resultado = $usuarioModel->actualizar($id_usuario, $datos);

            $_SESSION['alert'] = [
                'icon' => $resultado === true ? 'success' : 'error',
                'title' => $resultado === true ? 'Actualización exitosa' : 'Error al actualizar',
                'text' => $resultado === true ? 'La información fue actualizada correctamente' : $resultado
            ];

            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error del sistema',
                'text' => $e->getMessage()
            ];
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }
    }

    public function desactivar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php#gestion-usuarios");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;

        try {
            $db = (new Database())->conectar();
            $usuarioModel = new Usuario($db);
            $resultado = $usuarioModel->desactivar((int)$id_usuario);

            $_SESSION['alert'] = [
                'icon' => $resultado ? 'success' : 'error',
                'title' => $resultado ? 'Usuario desactivado' : 'Error',
                'text' => $resultado ? 'El usuario fue desactivado correctamente' : 'No se pudo desactivar el usuario'
            ];

            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }
    }

    public function activar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;

        try {
            $db = (new Database())->conectar();
            $usuarioModel = new Usuario($db);
            $resultado = $usuarioModel->activar((int)$id_usuario);

            $_SESSION['alert'] = [
                'icon' => $resultado ? 'success' : 'error',
                'title' => $resultado ? 'Usuario activado' : 'Error',
                'text' => $resultado ? 'El usuario fue activado correctamente' : 'No se pudo activar el usuario'
            ];

            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php#gestion-usuarios");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;

        try {
            $db = (new Database())->conectar();
            $usuarioModel = new Usuario($db);
            $resultado = $usuarioModel->borrar((int)$id_usuario);

            $_SESSION['alert'] = [
                'icon' => $resultado ? 'success' : 'error',
                'title' => $resultado ? 'Usuario eliminado' : 'Error',
                'text' => $resultado ? 'El usuario fue eliminado permanentemente' : 'No se pudo eliminar el usuario'
            ];

            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()];
            header("Location: ../views/dashboard/lista_usuario.php");
            exit;
        }
    }
}

$controller = new UsuarioController();
$accion = $_GET['accion'] ?? 'registrar';

switch ($accion) {
    case 'registrar':
        $controller->registrar();
        break;
    case 'actualizar':
        $controller->actualizar();
        break;
    case 'desactivar':
        $controller->desactivar();
        break;
    case 'activar':
        $controller->activar();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        $controller->registrar();
        break;
}
