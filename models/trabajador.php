<?php

class Trabajador
{
    private $conn;
    private $table_name = "trabajador";

    public $id_trabajador;
    public $id_usuario;
    public $id_cargo;
    public $documento;
    public $telefono;
    public $fecha_ingreso;
    public $estado;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerPorIdUsuario($id_usuario)
    {
        $query = "SELECT t.*, u.nombres, u.apellidos, u.email, c.nombre as cargo_nombre 
                  FROM " . $this->table_name . " t
                  JOIN usuario u ON t.id_usuario = u.id_usuario
                  LEFT JOIN cargo c ON t.id_cargo = c.id_cargo
                  WHERE t.id_usuario = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorDocumento($documento)
    {
        $query = "SELECT t.*, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " t
                  JOIN usuario u ON t.id_usuario = u.id_usuario
                  WHERE t.documento = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $documento);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarTodos()
    {
        $query = "SELECT t.*, u.nombres, u.apellidos, c.nombre as cargo_nombre 
                  FROM " . $this->table_name . " t
                  JOIN usuario u ON t.id_usuario = u.id_usuario
                  LEFT JOIN cargo c ON t.id_cargo = c.id_cargo
                  ORDER BY u.nombres ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
