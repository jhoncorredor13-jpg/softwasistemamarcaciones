<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header("Location: ../usuarios/login.php"); exit;
}
require_once __DIR__ . '/../../config/database.php';
$db    = (new Database())->conectar();
$admin = $_SESSION['usuario'];

// Obtener todas las marcaciones (turnos con entradas y salidas)
$sql = "SELECT t.id_turno, t.area, t.fecha, t.horaEntrada, t.horaSalida, t.reporteTurno,
               u.nombres, u.apellidos, c.nombre AS cargo,
               re.hora_entrada, rs.hora_salida
        FROM turno t
        INNER JOIN trabajador tr2 ON t.id_trabajador = tr2.id_trabajador
        INNER JOIN usuario u      ON tr2.id_usuario  = u.id_usuario
        LEFT  JOIN cargo c        ON tr2.id_cargo    = c.id_cargo
        LEFT  JOIN registro_entrada re ON re.id_turno = t.id_turno
        LEFT  JOIN registro_salida  rs ON rs.registro_entrada = re.id_registro_entrada
        ORDER BY t.fecha DESC, re.hora_entrada DESC
        LIMIT 100";
try {
    $stmt      = $db->prepare($sql);
    $stmt->execute();
    $marcaciones = $stmt->fetchAll();
} catch(Exception $e) {
    $marcaciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Marcaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #E4E4E4;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .topbar {
            display: flex;
            align-items: center;
            background-color: #E4E4E4;
            height: 60px;
            justify-content: space-between;
            padding-right: 30px;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .logo-area {
            width: 220px;
            background-color: #fff;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 2px 0 5px rgba(0,0,0,0.03); 
        }
        
        .logo-area img { max-height: 45px; }
        .topbar-title { margin-left: 20px; font-size: 16px; color: #000; }
        .topbar-right { display: flex; align-items: center; }

        .user-profile {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #000;
        }

        .user-profile .icon {
            background-color: #fff;
            border: 1px solid #ccc;
            width: 25px;
            height: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: #555;
        }

        .main-container { display: flex; flex: 1; overflow: hidden; }

        .sidebar {
            width: 220px;
            background-color: #52A65A;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            overflow-y: auto;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 18px 20px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            gap: 15px;
        }

        .sidebar-item:hover { background-color: rgba(0,0,0,0.05); }
        .icon-wrapper { width: 35px; height: 35px; display: flex; justify-content: center; align-items: center; }

        .icon-home { background-color: #fff; color: #000; font-size: 22px; border-radius: 4px; }
        .icon-user-green { background-color: #4CAF50; color: #fff; font-size: 22px; }
        .icon-user-check { background-color: #fff; color: #2C3E50; font-size: 18px; border-radius: 4px;}
        .icon-user-times { background-color: #fff; color: #2C3E50; font-size: 18px; border-radius: 4px;}
        .icon-list { background-color: #3F51B5; color: #fff; font-size: 18px; border-radius: 4px;}

        .content { flex: 1; padding: 30px 40px; overflow-y: auto; }
        .page-title { font-size: 22px; font-weight: 400; color: #000; margin-bottom: 8px; }
        .divider { border-bottom: 1px solid #333; margin-bottom: 30px; width: 100%; }

        .card { background-color: #fff; border-radius: 40px; padding: 25px 40px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #777; }
        .card-header h2 { font-size: 20px; font-weight: 400; color: #111; margin: 0; }
        
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #52A65A; color: #fff; }
        thead th { padding: 11px 14px; text-align: left; font-weight: 500; border: 1px solid #333; }
        tbody tr { border-bottom: 1px solid #ddd; transition: background .1s; }
        tbody tr:hover { background: #f9f9f9; }
        tbody td { padding: 11px 14px; color: #111; border: 1px solid #ddd; }
        
        .empty { text-align: center; padding: 40px; color: #999; font-size: 14px; }
        .badge-entrada { background: #d4edda; color: #1b5e20; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-salida { background: #fff3cd; color: #856404; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola" onerror="this.style.display='none'">
            </div>
            <div class="topbar-title">Marcaciones</div>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="icon"><i class="fa fa-user"></i></div>
                <?= htmlspecialchars($admin['nombres']) ?>
            </div>
        </div>
    </div>

    <div class="main-container">
        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item">
                <div class="icon-wrapper icon-home"><i class="fa fa-home"></i></div>
                Panel de Control
            </a>
            <a href="admin.php#gestion-usuarios" class="sidebar-item">
                <div class="icon-wrapper icon-user-green"><i class="fa fa-user-plus"></i></div>
                Gestión de Usuarios
            </a>

        </nav>

        <main class="content">
            <h1 class="page-title">Registro de Marcaciones</h1>
            <div class="divider"></div>

            <div class="card">
                <div class="card-header">
                    <h2>Historial de turnos (últimos 100)</h2>
                    <span style="font-size:12px;color:#999">Actualizado: <?= date('d/m/Y H:i') ?></span>
                </div>
                
                <?php if (empty($marcaciones)): ?>
                    <div class="empty"><i class="fa fa-clock fa-3x" style="display:block;margin-bottom:12px"></i>No hay marcaciones registradas aún.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th><th>Trabajador</th><th>Cargo</th><th>Área</th>
                                <th>Fecha</th><th>Hora Entrada</th><th>Hora Salida</th><th>Reporte</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcaciones as $i => $m): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($m['nombres'].' '.$m['apellidos']) ?></td>
                                <td><?= htmlspecialchars($m['cargo'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($m['area'] ?? '—') ?></td>
                                <td><?= $m['fecha'] ? date('d/m/Y', strtotime($m['fecha'])) : '—' ?></td>
                                <td><?= $m['hora_entrada'] ? '<span class="badge-entrada">'.$m['hora_entrada'].'</span>' : '—' ?></td>
                                <td><?= $m['hora_salida']  ? '<span class="badge-salida">'.$m['hora_salida'].'</span>'   : '—' ?></td>
                                <td><?= htmlspecialchars(substr($m['reporteTurno'] ?? '',0,40)) ?><?= strlen($m['reporteTurno']??'')>40?'...':'' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>