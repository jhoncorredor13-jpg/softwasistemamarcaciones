<?php

class Database
{
    private $host = "127.0.0.1";
    private $port = "3320"; // Tu puerto personalizado
    private $db_name = "sistema_marcaciones"; // ← CORREGIDO
    private $username = "root";
    private $password = "";

    public $conn;

    public function conectar()
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Configuración importante para producción y debugging
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}
