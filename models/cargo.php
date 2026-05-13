<?php

class Cargo
{
    private $conn;
    private $table_name = "cargo";

    public $id_cargo;
    public $nombre;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarCargos()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cargo = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
