<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/permiso.php';

class PermisoController
{
    private $db;
    private $permiso;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->permiso = new Permiso($this->db);
    }

    public function indexAdmin()
    {
        $stmt = $this->permiso->getAllPermisos();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function indexTrabajador($id_trabajador)
    {
        $stmt = $this->permiso->getPermisosByTrabajador($id_trabajador);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function solicitarPermiso()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $motivo = $_POST['motivo'] ?? '';

            if (empty($id_trabajador) || empty($fecha_inicio) || empty($fecha_fin) || empty($motivo)) {
                $_SESSION['alert'] = [
                    'icon' => 'warning',
                    'title' => 'Campos incompletos',
                    'text' => 'Por favor, complete todos los campos de la solicitud.'
                ];
                header("Location: ../views/dashboard/trabajador.php");
                exit;
            }

            if ($fecha_inicio > $fecha_fin) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Fechas inválidas',
                    'text' => 'La fecha de inicio no puede ser posterior a la fecha de fin.'
                ];
                header("Location: ../views/dashboard/trabajador.php");
                exit;
            }

            $this->permiso->id_trabajador = $id_trabajador;
            $this->permiso->fecha_inicio = $fecha_inicio;
            $this->permiso->fecha_fin = $fecha_fin;
            $this->permiso->motivo = $motivo;
            $this->permiso->estado = 'pendiente';

            if ($this->permiso->crearPermiso()) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Solicitud enviada',
                    'text' => 'Su solicitud de permiso ha sido enviada al administrador.'
                ];
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Hubo un problema al enviar la solicitud.'
                ];
            }
            header("Location: ../views/dashboard/trabajador.php");
            exit;
        }
    }

    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_permiso = $_POST['id_permiso'] ?? '';
            $estado = $_POST['estado'] ?? '';

            if (!empty($id_permiso) && in_array($estado, ['aprobado', 'rechazado'])) {
                $this->permiso->id_permiso = $id_permiso;
                $this->permiso->estado = $estado;

                if ($this->permiso->actualizarEstado()) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Estado actualizado',
                        'text' => 'El permiso ha sido ' . $estado . '.'
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'No se pudo actualizar el estado del permiso.'
                    ];
                }
            }
            header("Location: ../views/dashboard/permisos.php");
            exit;
        }
    }
}

// Routing simple
if (isset($_GET['accion'])) {
    $controller = new PermisoController();
    $accion = $_GET['accion'];

    if ($accion === 'solicitar') {
        $controller->solicitarPermiso();
    } elseif ($accion === 'cambiarEstado') {
        $controller->cambiarEstado();
    }
}
?>
