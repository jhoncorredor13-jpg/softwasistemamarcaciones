<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/turno.php';

class TurnoController
{
    private $db;
    private $turno;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->turno = new Turno($this->db);
    }

    public function indexAdmin()
    {
        $stmt = $this->turno->getTurnosFuturos();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function indexAdminAgrupados()
    {
        $turnos = $this->indexAdmin();
        $agrupados = [];

        if (empty($turnos)) return [];

        $currentGroup = null;

        foreach ($turnos as $t) {
            $id_trabajador = $t['id_trabajador'];
            $fechaStr = $t['fecha'];
            $horaEntrada = $t['horaEntrada'];
            $horaSalida = $t['horaSalida'];

            $dateObj = new DateTime($fechaStr);

            if ($currentGroup === null) {
                // Iniciar primer grupo
                $currentGroup = [
                    'id_trabajador' => $id_trabajador,
                    'nombres' => $t['nombres'],
                    'apellidos' => $t['apellidos'],
                    'documento' => $t['documento'],
                    'horaEntrada' => $horaEntrada,
                    'horaSalida' => $horaSalida,
                    'fecha_inicio' => $fechaStr,
                    'fecha_fin' => $fechaStr,
                    'ids_turnos' => [$t['id_turno']],
                    'last_date_obj' => clone $dateObj
                ];
            } else {
                // Verificar si pertenece al mismo grupo (mismo trabajador, mismas horas, día consecutivo)
                $diff = $currentGroup['last_date_obj']->diff($dateObj)->days;

                if ($currentGroup['id_trabajador'] == $id_trabajador && 
                    $currentGroup['horaEntrada'] == $horaEntrada && 
                    $currentGroup['horaSalida'] == $horaSalida && 
                    $diff == 1) {
                    
                    // Es consecutivo, actualizar fin
                    $currentGroup['fecha_fin'] = $fechaStr;
                    $currentGroup['ids_turnos'][] = $t['id_turno'];
                    $currentGroup['last_date_obj'] = clone $dateObj;
                } else {
                    // No es consecutivo o es otro trabajador, guardar grupo anterior e iniciar nuevo
                    $agrupados[] = $currentGroup;
                    $currentGroup = [
                        'id_trabajador' => $id_trabajador,
                        'nombres' => $t['nombres'],
                        'apellidos' => $t['apellidos'],
                        'documento' => $t['documento'],
                        'horaEntrada' => $horaEntrada,
                        'horaSalida' => $horaSalida,
                        'fecha_inicio' => $fechaStr,
                        'fecha_fin' => $fechaStr,
                        'ids_turnos' => [$t['id_turno']],
                        'last_date_obj' => clone $dateObj
                    ];
                }
            }
        }
        
        if ($currentGroup !== null) {
            $agrupados[] = $currentGroup;
        }

        return $agrupados;
    }

    public function getAllTrabajadores()
    {
        $query = "SELECT t.id_trabajador, t.documento, u.nombres, u.apellidos 
                  FROM trabajador t 
                  JOIN usuario u ON t.id_usuario = u.id_usuario 
                  WHERE t.estado = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarMasivo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $tipo_turno = $_POST['tipo_turno'] ?? '';

            if (empty($id_trabajador) || empty($fecha_inicio) || empty($fecha_fin) || empty($tipo_turno)) {
                $_SESSION['alert'] = [
                    'icon' => 'warning',
                    'title' => 'Datos incompletos',
                    'text' => 'Por favor complete todos los campos para asignar el turno.'
                ];
                header("Location: ../views/dashboard/turnos.php");
                exit;
            }

            if ($fecha_inicio > $fecha_fin) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Fechas inválidas',
                    'text' => 'La fecha de inicio no puede ser mayor a la fecha de fin.'
                ];
                header("Location: ../views/dashboard/turnos.php");
                exit;
            }

            // Determinar horas según el tipo de turno
            $horaEntrada = '00:00:00';
            $horaSalida = '00:00:00';

            switch ($tipo_turno) {
                case 'manana':
                    $horaEntrada = '06:00:00';
                    $horaSalida = '14:00:00';
                    break;
                case 'tarde':
                    $horaEntrada = '14:00:00';
                    $horaSalida = '22:00:00';
                    break;
                case 'noche':
                    $horaEntrada = '22:00:00';
                    $horaSalida = '06:00:00'; // Termina al día siguiente (en el sistema se puede manejar cruzando media noche o requerirá lógica adicional para las validaciones de asistencia)
                    break;
                case 'oficina':
                    $horaEntrada = '08:00:00';
                    $horaSalida = '17:00:00';
                    break;
            }

            // Iterar sobre los días
            $begin = new DateTime($fecha_inicio);
            $end = new DateTime($fecha_fin);
            $end->modify('+1 day'); 
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            $exitos = 0;
            foreach ($period as $dt) {
                $fechaStr = $dt->format("Y-m-d");
                if ($this->turno->asignarTurnoDia($id_trabajador, $fechaStr, $horaEntrada, $horaSalida)) {
                    $exitos++;
                }
            }

            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Turnos asignados',
                'text' => "Se han asignado/actualizado $exitos días de turno correctamente."
            ];
            header("Location: ../views/dashboard/turnos.php");
            exit;
        }
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids_turnos = $_POST['ids_turnos'] ?? '';
            
            if (!empty($ids_turnos)) {
                $idsArray = explode(',', $ids_turnos);
                $exitos = 0;
                foreach ($idsArray as $id) {
                    if ($this->turno->eliminarTurno(trim($id))) {
                        $exitos++;
                    }
                }

                if ($exitos > 0) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Turnos eliminados',
                        'text' => "Se han eliminado $exitos turnos correctamente."
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'No se pudo eliminar el turno.'
                    ];
                }
            }
            header("Location: ../views/dashboard/turnos.php");
            exit;
        }
    }
}

// Enrutador
if (isset($_GET['accion'])) {
    $controller = new TurnoController();
    $accion = $_GET['accion'];

    if ($accion === 'asignar') {
        $controller->asignarMasivo();
    } elseif ($accion === 'eliminar') {
        $controller->eliminar();
    }
}
?>
