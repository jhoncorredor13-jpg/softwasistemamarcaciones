<?php

class Permiso
{
    private $conn;
    private $table_name = "permiso";

    public $id_permiso;
    public $id_trabajador;
    public $fecha_inicio;
    public $fecha_fin;
    public $motivo;
    public $estado;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllPermisos()
    {
        $query = "SELECT p.*, t.documento, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " p
                  JOIN trabajador t ON p.id_trabajador = t.id_trabajador
                  JOIN usuario u ON t.id_usuario = u.id_usuario
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getPermisosByTrabajador($id_trabajador)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_trabajador = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_trabajador);
        $stmt->execute();
        return $stmt;
    }

    public function crearPermiso()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_trabajador, fecha_inicio, fecha_fin, motivo, estado) 
                  VALUES (:id_trabajador, :fecha_inicio, :fecha_fin, :motivo, :estado)";

        $stmt = $this->conn->prepare($query);

        $this->id_trabajador = htmlspecialchars(strip_tags($this->id_trabajador));
        $this->fecha_inicio = htmlspecialchars(strip_tags($this->fecha_inicio));
        $this->fecha_fin = htmlspecialchars(strip_tags($this->fecha_fin));
        $this->motivo = htmlspecialchars(strip_tags($this->motivo));
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(":id_trabajador", $this->id_trabajador);
        $stmt->bindParam(":fecha_inicio", $this->fecha_inicio);
        $stmt->bindParam(":fecha_fin", $this->fecha_fin);
        $stmt->bindParam(":motivo", $this->motivo);
        $stmt->bindParam(":estado", $this->estado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function actualizarEstado($fecha_fin = null)
    {
        $sqlFecha = "";
        if (!empty($fecha_fin)) {
            $sqlFecha = ", fecha_fin = :fecha_fin";
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado {$sqlFecha} 
                  WHERE id_permiso = :id_permiso";

        $stmt = $this->conn->prepare($query);

        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id_permiso = htmlspecialchars(strip_tags($this->id_permiso));

        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id_permiso", $this->id_permiso);
        if (!empty($fecha_fin)) {
            $stmt->bindParam(":fecha_fin", $fecha_fin);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
