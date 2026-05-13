<?php

class Marcacion
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Registra la entrada de un trabajador para un turno específico
     */
    public function registrarEntrada($id_turno)
    {
        $query = "INSERT INTO registro_entrada (id_turno, hora_entrada, fecha_registro) 
                  VALUES (:id_turno, CURTIME(), CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_turno", $id_turno);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Registra la salida vinculada a una entrada específica
     */
    public function registrarSalida($id_registro_entrada)
    {
        $query = "INSERT INTO registro_salida (id_registro_entrada, hora_salida, fecha_registro) 
                  VALUES (:id_registro_entrada, CURTIME(), CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_registro_entrada", $id_registro_entrada);
        
        return $stmt->execute();
    }

    /**
     * Obtiene la última marcación de entrada pendiente (sin salida) para un turno
     */
    public function obtenerEntradaPendiente($id_turno)
    {
        $query = "SELECT re.* 
                  FROM registro_entrada re
                  LEFT JOIN registro_salida rs ON re.id_registro_entrada = rs.id_registro_entrada
                  WHERE re.id_turno = :id_turno AND rs.id_registro_salida IS NULL
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_turno", $id_turno);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lista el historial de marcaciones
     */
    public function listarHistorial($limite = 100)
    {
        $query = "SELECT t.id_turno, t.fecha, u.nombres, u.apellidos, c.nombre AS cargo,
                         re.hora_entrada, rs.hora_salida, re.id_registro_entrada
                  FROM turno t
                  INNER JOIN trabajador tr ON t.id_trabajador = tr.id_trabajador
                  INNER JOIN usuario u ON tr.id_usuario = u.id_usuario
                  LEFT JOIN cargo c ON tr.id_cargo = c.id_cargo
                  LEFT JOIN registro_entrada re ON re.id_turno = t.id_turno
                  LEFT JOIN registro_salida rs ON rs.id_registro_entrada = re.id_registro_entrada
                  ORDER BY t.fecha DESC, re.hora_entrada DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
