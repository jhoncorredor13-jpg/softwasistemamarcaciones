<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

class NotificacionController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
    }

    public function getNotificaciones()
    {
        $notificaciones = [];
        $hoy = date('Y-m-d');
        $horaActual = date('H:i:s');

        // 1. Permisos pendientes
        $queryPermisos = "
            SELECT p.id_permiso, p.fecha_inicio, p.fecha_fin, u.nombres, u.apellidos, p.created_at
            FROM permiso p
            JOIN trabajador t ON p.id_trabajador = t.id_trabajador
            JOIN usuario u ON t.id_usuario = u.id_usuario
            WHERE p.estado = 'pendiente'
            ORDER BY p.created_at DESC
        ";
        $stmt = $this->db->prepare($queryPermisos);
        $stmt->execute();
        $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($permisos as $p) {
            $notificaciones[] = [
                'tipo' => 'permiso',
                'icono' => 'fa-calendar-alt',
                'color' => '#FF9800', // Naranja
                'titulo' => 'Solicitud de permiso pendiente',
                'mensaje' => $p['nombres'] . ' ' . $p['apellidos'] . ' solicitó un permiso del ' . date('d/m', strtotime($p['fecha_inicio'])) . ' al ' . date('d/m', strtotime($p['fecha_fin'])),
                'fecha' => $p['created_at'],
                'accion' => 'permisos.php'
            ];
        }

        // 2. Faltas / Llegadas tarde de HOY
        $queryTurnosHoy = "
            SELECT t.id_turno, t.horaEntrada, u.nombres, u.apellidos, re.hora_entrada as marco_entrada
            FROM turno t
            JOIN trabajador tr ON t.id_trabajador = tr.id_trabajador
            JOIN usuario u ON tr.id_usuario = u.id_usuario
            LEFT JOIN registro_entrada re ON t.id_turno = re.id_turno AND re.fecha_registro = :hoy
            WHERE t.fecha = :hoy
        ";
        $stmt2 = $this->db->prepare($queryTurnosHoy);
        $stmt2->execute([':hoy' => $hoy]);
        $turnosHoy = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($turnosHoy as $turno) {
            if ($turno['marco_entrada'] === null) {
                // No ha marcado
                if ($horaActual > $turno['horaEntrada']) {
                    // Ya pasó la hora de entrada y no ha marcado
                    $notificaciones[] = [
                        'tipo' => 'alerta',
                        'icono' => 'fa-exclamation-triangle',
                        'color' => '#E74C3C', // Rojo
                        'titulo' => 'Posible inasistencia o atraso',
                        'mensaje' => $turno['nombres'] . ' ' . $turno['apellidos'] . ' no ha registrado su entrada (Turno: ' . date('H:i', strtotime($turno['horaEntrada'])) . ')',
                        'fecha' => $hoy . ' ' . $turno['horaEntrada'],
                        'accion' => 'marcaciones.php'
                    ];
                }
            } else {
                // Sí marcó, ver si fue tarde (tolerancia de 5 min)
                $entradaEsperada = strtotime($turno['horaEntrada']);
                $entradaReal = strtotime($turno['marco_entrada']);
                
                if (($entradaReal - $entradaEsperada) > 300) { // más de 5 minutos (300 seg)
                    $notificaciones[] = [
                        'tipo' => 'info',
                        'icono' => 'fa-clock',
                        'color' => '#F1C40F', // Amarillo
                        'titulo' => 'Llegada tarde registrada',
                        'mensaje' => $turno['nombres'] . ' ' . $turno['apellidos'] . ' llegó a las ' . date('H:i', strtotime($turno['marco_entrada'])) . ' (Turno: ' . date('H:i', strtotime($turno['horaEntrada'])) . ')',
                        'fecha' => $hoy . ' ' . $turno['marco_entrada'],
                        'accion' => 'marcaciones.php'
                    ];
                }
            }
        }

        // Ordenar notificaciones por fecha más reciente
        usort($notificaciones, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return $notificaciones;
    }
}
?>
