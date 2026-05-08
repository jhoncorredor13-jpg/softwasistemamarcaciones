<?php

class Usuario
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function existeCorreo($email)
    {
        $sql = "SELECT id_usuario FROM usuario WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorEmail($email)
    {
        $sql = "SELECT id_usuario, nombres, apellidos, email, password_hash, rol, activo
                FROM usuario
                WHERE email = :email AND activo = 1
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT u.*, t.id_trabajador, t.documento, t.telefono, t.id_cargo, t.fecha_ingreso
                FROM usuario u
                LEFT JOIN trabajador t ON u.id_usuario = t.id_usuario
                WHERE u.id_usuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function contarUsuarios()
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function contarTrabajadores()
    {
        $sql = "SELECT COUNT(*)
                FROM trabajador t
                INNER JOIN usuario u ON t.id_usuario = u.id_usuario
                WHERE u.rol = 'trabajador' AND u.activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function listarTrabajadores()
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.email,
                    u.rol,
                    u.activo,
                    t.id_trabajador,
                    t.documento,
                    t.telefono,
                    t.fecha_ingreso,
                    c.nombre AS cargo
                FROM usuario u
                LEFT JOIN trabajador t ON u.id_usuario = t.id_usuario
                LEFT JOIN cargo c ON t.id_cargo = c.id_cargo
                WHERE u.rol = 'trabajador'
                ORDER BY u.nombres ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrar($datos)
    {
        try {
            $this->conn->beginTransaction();

            $sqlUsuario = "INSERT INTO usuario
                (nombres, apellidos, email, password_hash, rol, activo)
                VALUES
                (:nombres, :apellidos, :email, :password_hash, :rol, 1)";

            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bindParam(":nombres", $datos['nombres']);
            $stmtUsuario->bindParam(":apellidos", $datos['apellidos']);
            $stmtUsuario->bindParam(":email", $datos['email']);
            $stmtUsuario->bindParam(":password_hash", $datos['password_hash']);
            $stmtUsuario->bindParam(":rol", $datos['rol']);
            $stmtUsuario->execute();

            $id_usuario = $this->conn->lastInsertId();

            if ($datos['rol'] === 'trabajador') {
                $sqlTrabajador = "INSERT INTO trabajador
                    (id_usuario, documento, telefono, id_cargo, fecha_ingreso, estado)
                    VALUES
                    (:id_usuario, :documento, :telefono, :id_cargo, :fecha_ingreso, 1)";

                $stmtTrabajador = $this->conn->prepare($sqlTrabajador);
                $stmtTrabajador->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
                $stmtTrabajador->bindParam(":documento", $datos['documento']);
                $stmtTrabajador->bindParam(":telefono", $datos['telefono']);
                $stmtTrabajador->bindParam(":id_cargo", $datos['id_cargo']);
                $stmtTrabajador->bindParam(":fecha_ingreso", $datos['fecha_ingreso']);
                $stmtTrabajador->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Error al registrar: " . $e->getMessage();
        }
    }

    public function desactivar($id_usuario)
    {
        $sql = "UPDATE usuario 
                SET activo = 0 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function activar($id_usuario)
    {
        $sql = "UPDATE usuario 
                SET activo = 1 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function actualizarPassword($email, $password_hash)
    {
        $sql = "UPDATE usuario 
                SET password_hash = :password_hash 
                WHERE email = :email";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":email", $email);

        return $stmt->execute();
    }
    public function borrar($id_usuario)
    {
        $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizar($id_usuario, $datos)
    {
        try {
            $this->conn->beginTransaction();

            $sqlUsuario = "UPDATE usuario 
                           SET nombres = :nombres, apellidos = :apellidos, email = :email, rol = :rol 
                           WHERE id_usuario = :id_usuario";
            
            if (!empty($datos['password_hash'])) {
                $sqlUsuario = "UPDATE usuario 
                               SET nombres = :nombres, apellidos = :apellidos, email = :email, rol = :rol, password_hash = :password_hash 
                               WHERE id_usuario = :id_usuario";
            }

            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bindParam(":nombres", $datos['nombres']);
            $stmtUsuario->bindParam(":apellidos", $datos['apellidos']);
            $stmtUsuario->bindParam(":email", $datos['email']);
            $stmtUsuario->bindParam(":rol", $datos['rol']);
            $stmtUsuario->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            if (!empty($datos['password_hash'])) {
                $stmtUsuario->bindParam(":password_hash", $datos['password_hash']);
            }
            $stmtUsuario->execute();

            if ($datos['rol'] === 'trabajador') {
                $sqlCheck = "SELECT id_trabajador FROM trabajador WHERE id_usuario = :id_usuario";
                $stmtCheck = $this->conn->prepare($sqlCheck);
                $stmtCheck->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
                $stmtCheck->execute();
                
                if ($stmtCheck->rowCount() > 0) {
                    $sqlTrabajador = "UPDATE trabajador 
                                     SET documento = :documento, telefono = :telefono, id_cargo = :id_cargo, fecha_ingreso = :fecha_ingreso 
                                     WHERE id_usuario = :id_usuario";
                } else {
                    $sqlTrabajador = "INSERT INTO trabajador (id_usuario, documento, telefono, id_cargo, fecha_ingreso, estado) 
                                     VALUES (:id_usuario, :documento, :telefono, :id_cargo, :fecha_ingreso, 1)";
                }

                $stmtTrabajador = $this->conn->prepare($sqlTrabajador);
                $stmtTrabajador->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
                $stmtTrabajador->bindParam(":documento", $datos['documento']);
                $stmtTrabajador->bindParam(":telefono", $datos['telefono']);
                $stmtTrabajador->bindParam(":id_cargo", $datos['id_cargo']);
                $stmtTrabajador->bindParam(":fecha_ingreso", $datos['fecha_ingreso']);
                $stmtTrabajador->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return $e->getMessage();
        }
    }
}
