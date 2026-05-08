<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? null;
    $id_trabajador = $_POST['id_trabajador'] ?? null;

    if (!$id_trabajador) {
        header("Location: ../views/dashboard/trabajador.php");
        exit;
    }

    $hoy = date('Y-m-d');
    $hora = date('H:i:s');

    try {

        // 🔍 Buscar turno de hoy
        $stmt = $db->prepare("SELECT * FROM turno WHERE id_trabajador = ? AND fecha = ? LIMIT 1");
        $stmt->execute([$id_trabajador, $hoy]);
        $turno = $stmt->fetch();

        // 🟢 SI NO HAY TURNO → CREAR UNO
        if (!$turno) {
            $stmt = $db->prepare("
                INSERT INTO turno (id_trabajador, fecha)
                VALUES (?, ?)
            ");
            $stmt->execute([$id_trabajador, $hoy]);

            $id_turno = $db->lastInsertId();
        } else {
            $id_turno = $turno['id_turno'];
        }

        // ============================
        // 🔹 REGISTRAR ENTRADA
        // ============================
        if ($accion === 'entrada') {

            // Validar si ya existe entrada
            $stmt = $db->prepare("SELECT * FROM registro_entrada WHERE id_turno = ?");
            $stmt->execute([$id_turno]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['alert'] = [
                    'icon' => 'warning',
                    'title' => 'Ya registrado',
                    'text' => 'Ya registraste tu entrada hoy'
                ];
            } else {
                $stmt = $db->prepare("
                    INSERT INTO registro_entrada (id_turno, hora_entrada, fecha_registro)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$id_turno, $hora, $hoy]);

                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Entrada registrada',
                    'text' => 'Se registró correctamente'
                ];
            }
        }

        // ============================
        // 🔹 REGISTRAR SALIDA
        // ============================
        if ($accion === 'salida') {

            // Buscar entrada
            $stmt = $db->prepare("SELECT * FROM registro_entrada WHERE id_turno = ?");
            $stmt->execute([$id_turno]);
            $entrada = $stmt->fetch();

            if (!$entrada) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Primero debes registrar entrada'
                ];
            } else {

                // Verificar si ya tiene salida
                $stmt = $db->prepare("SELECT * FROM registro_salida WHERE id_registro_entrada = ?");
                $stmt->execute([$entrada['id_registro_entrada']]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['alert'] = [
                        'icon' => 'warning',
                        'title' => 'Ya registrada',
                        'text' => 'Ya registraste tu salida'
                    ];
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO registro_salida (id_registro_entrada, hora_salida, fecha_registro)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$entrada['id_registro_entrada'], $hora, $hoy]);

                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Salida registrada',
                        'text' => 'Se registró correctamente'
                    ];
                }
            }
        }
    } catch (Exception $e) {

        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Error',
            'text' => 'Error en el sistema'
        ];
    }

    header("Location: ../views/dashboard/trabajador.php");
    exit;
}

// ============================
// 🔹 ENDPOINT PARA EL KIOSKO (AJAX)
// ============================
if (isset($_GET['accion']) && $_GET['accion'] === 'identificar') {
    $documento = $_GET['documento'] ?? '';
    
    if (empty($documento)) {
        echo json_encode(['success' => false, 'message' => 'Documento requerido']);
        exit;
    }

    try {
        // 1. Buscar trabajador
        $query = "SELECT t.id_trabajador, t.documento, u.nombres, u.apellidos, c.nombre as cargo
                  FROM trabajador t
                  JOIN usuario u ON t.id_usuario = u.id_usuario
                  LEFT JOIN cargo c ON t.id_cargo = c.id_cargo
                  WHERE t.documento = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$documento]);
        $trabajador = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($trabajador) {
            // 2. Buscar último permiso (aprobado o rechazado)
            $queryP = "SELECT estado FROM permiso 
                       WHERE id_trabajador = ? 
                       AND estado IN ('aprobado', 'rechazado')
                       ORDER BY created_at DESC LIMIT 1";
            $stmtP = $db->prepare($queryP);
            $stmtP->execute([$trabajador['id_trabajador']]);
            $permiso = $stmtP->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'trabajador' => $trabajador,
                'permiso' => $permiso ?: null
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trabajador no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
