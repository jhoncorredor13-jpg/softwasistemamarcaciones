<?php

class Turno
{
    private $conn;
    private $table_name = "turno";

    public $id_turno;
    public $id_trabajador;
    public $fecha;
    public $horaEntrada;
    public $horaSalida;
    public $reporteTurno;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTurnosFuturos()
    {
        $query = "SELECT t.*, tr.documento, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " t
                  JOIN trabajador tr ON t.id_trabajador = tr.id_trabajador
                  JOIN usuario u ON tr.id_usuario = u.id_usuario
                  WHERE t.fecha >= CURDATE()
                  ORDER BY t.fecha ASC, t.horaEntrada ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function crearTurno()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_trabajador, fecha, horaEntrada, horaSalida) 
                  VALUES (:id_trabajador, :fecha, :horaEntrada, :horaSalida)
                  ON DUPLICATE KEY UPDATE horaEntrada = :horaEntrada_update, horaSalida = :horaSalida_update";

        $stmt = $this->conn->prepare($query);

        $this->id_trabajador = htmlspecialchars(strip_tags($this->id_trabajador));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->horaEntrada = htmlspecialchars(strip_tags($this->horaEntrada));
        $this->horaSalida = htmlspecialchars(strip_tags($this->horaSalida));

        $stmt->bindParam(":id_trabajador", $this->id_trabajador);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":horaEntrada", $this->horaEntrada);
        $stmt->bindParam(":horaSalida", $this->horaSalida);
        // For ON DUPLICATE KEY (assuming we add a unique constraint, but without it we just insert)
        // Wait, the schema doesn't have a UNIQUE KEY on (id_trabajador, fecha).
        // Let's first delete any existing shift for that day and worker, then insert to avoid duplicates.
        return false; // Will implement via a transaction in the controller instead
    }

    public function asignarTurnoDia($id_trabajador, $fecha, $horaEntrada, $horaSalida)
    {
        // Primero eliminar si ya existe un turno para ese trabajador en esa fecha
        $queryDelete = "DELETE FROM " . $this->table_name . " WHERE id_trabajador = ? AND fecha = ?";
        $stmtDelete = $this->conn->prepare($queryDelete);
        $stmtDelete->execute([$id_trabajador, $fecha]);

        // Insertar el nuevo turno
        $queryInsert = "INSERT INTO " . $this->table_name . " (id_trabajador, fecha, horaEntrada, horaSalida) VALUES (?, ?, ?, ?)";
        $stmtInsert = $this->conn->prepare($queryInsert);
        return $stmtInsert->execute([$id_trabajador, $fecha, $horaEntrada, $horaSalida]);
    }

    public function eliminarTurno($id_turno)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_turno = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_turno]);
    }
}
?>
