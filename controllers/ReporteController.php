<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

class ReporteController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
    }

    public function getAllTrabajadores()
    {
        $query = "SELECT t.id_trabajador, u.nombres, u.apellidos, t.documento 
                  FROM trabajador t 
                  JOIN usuario u ON t.id_usuario = u.id_usuario 
                  WHERE t.estado = 1 
                  ORDER BY u.nombres";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generarReporte($fecha_inicio, $fecha_fin, $id_trabajador = '')
    {
        $params = [
            ':inicio' => $fecha_inicio,
            ':fin' => $fecha_fin
        ];

        $workerFilter = "";
        if (!empty($id_trabajador)) {
            $workerFilter = " AND t.id_trabajador = :id_trabajador ";
            $params[':id_trabajador'] = $id_trabajador;
        }

        $query = "
            SELECT 
                u.nombres, 
                u.apellidos, 
                tr.documento,
                t.fecha, 
                t.horaEntrada AS turno_entrada, 
                t.horaSalida AS turno_salida,
                re.hora_entrada AS marca_entrada,
                rs.hora_salida AS marca_salida,
                p.estado AS permiso_estado
            FROM turno t
            INNER JOIN trabajador tr ON t.id_trabajador = tr.id_trabajador
            INNER JOIN usuario u ON tr.id_usuario = u.id_usuario
            LEFT JOIN registro_entrada re ON t.id_turno = re.id_turno
            LEFT JOIN registro_salida rs ON re.id_registro_entrada = rs.id_registro_entrada
            LEFT JOIN permiso p ON tr.id_trabajador = p.id_trabajador AND t.fecha BETWEEN p.fecha_inicio AND p.fecha_fin AND p.estado = 'aprobado'
            WHERE t.fecha BETWEEN :inicio AND :fin
            $workerFilter
            ORDER BY t.fecha ASC, u.nombres ASC
        ";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesar estados
        foreach ($resultados as &$fila) {
            if ($fila['permiso_estado'] === 'aprobado') {
                $fila['estado_final'] = 'Permiso';
            } elseif ($fila['marca_entrada'] !== null) {
                $fila['estado_final'] = 'Asistió';
            } elseif ($fila['fecha'] < date('Y-m-d')) {
                $fila['estado_final'] = 'Faltó';
            } else {
                $fila['estado_final'] = 'Pendiente';
            }
        }

        return $resultados;
    }
}
?>
