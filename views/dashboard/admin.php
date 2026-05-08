<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/usuario.php';

$total_usuarios = 0;
$total_trabajadores = 0;
$marcaciones_hoy = 0;

try {
    $db = (new Database())->conectar();
    $model = new Usuario($db);

    $total_usuarios = $model->contarUsuarios();
    $total_trabajadores = $model->contarTrabajadores();

    $hoy = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM registro_entrada re
        INNER JOIN turno t ON re.id_turno = t.id_turno
        WHERE t.fecha = ?
    ");
    $stmt->execute([$hoy]);
    $marcaciones_hoy = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_usuarios = 0;
    $total_trabajadores = 0;
    $marcaciones_hoy = 0;
}

$admin = $_SESSION['usuario'];
$nombre_admin = $admin['nombres'] ?? $admin['nombre'] ?? 'Administrador';

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
            background: #dfeee2;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .topbar {
            height: 48px;
            background: #dfe8df;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px 0 20px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .logo-area {
            width: 58px;
            height: 48px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-area img {
            width: 48px;
            height: 34px;
            object-fit: contain;
        }

        .topbar-title {
            font-size: 14px;
            color: #2e7d32;
            font-weight: bold;
            margin-left: 12px;
        }

        .curva {
            width: 120px;
            height: 4px;
            background: #2e7d32;
            border-radius: 50px;
            margin-left: 70px;
            margin-top: 0;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #111;
        }

        .user-icon {
            width: 22px;
            height: 22px;
            background: #ffffff;
            border: 1px solid #c7c7c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

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

        .sidebar-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .icon-wrapper {
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 4px;
        }

        .icon-home {
            background-color: #fff;
            color: #000;
            font-size: 22px;
        }

        .icon-user-green {
            background-color: #4CAF50;
            color: #fff;
            font-size: 22px;
        }

        .icon-clock {
            background-color: #FF9800;
            color: #fff;
            font-size: 18px;
        }

        .icon-permissions { background-color: #9C27B0; color: #fff; font-size: 18px; }
        .icon-reports { background-color: #E91E63; color: #fff; font-size: 18px; }
        .icon-notifications { background-color: #00BCD4; color: #fff; font-size: 18px; }
        .icon-shifts { background-color: #FFC107; color: #fff; font-size: 18px; }
        .icon-fingerprint { background-color: #607D8B; color: #fff; font-size: 18px; }

        .icon-logout {
            background-color: #fff;
            color: #E74C3C;
            font-size: 18px;
        }

        .content {
            flex: 1;
            padding: 30px 40px;
            overflow-y: auto;
            background: transparent;
        }

        .page-title {
            font-size: 22px;
            font-weight: 400;
            color: #000;
            margin-bottom: 8px;
        }

        .divider {
            border-bottom: 1px solid #333;
            margin-bottom: 30px;
            width: 100%;
        }

        .card {
            background-color: #fff;
            border-radius: 40px;
            padding: 25px 40px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .card-title {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 12px;
            color: #111;
        }

        .card-divider {
            border-bottom: 1px solid #777;
            margin-bottom: 30px;
            width: 100%;
        }

        .stats-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .stat-box {
            background-color: #F6F6F6;
            padding: 25px;
            width: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 4px;
        }

        .stat-box span {
            font-size: 13px;
            color: #000;
            text-align: center;
        }

        .stat-box i {
            font-size: 40px;
            color: #4A6473;
        }

        .stat-box .stat-num {
            font-size: 26px;
            font-weight: 700;
            color: #52A65A;
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola">
            </div>
            <div class="topbar-title">Panel Administrador</div>
        </div>

        <div class="topbar-right">
            <div class="user-box">
                <div class="user-icon">
                    <i class="fa fa-user"></i>
                </div>
                <span>Administrador</span>
            </div>
        </div>
    </div>

    <div class="curva"></div>

    <div class="main-container">

        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item">
                <div class="icon-wrapper icon-home"><i class="fa fa-home"></i></div>
                Panel de Control
            </a>

            <a href="lista_usuario.php" class="sidebar-item">
                <div class="icon-wrapper icon-user-green"><i class="fa fa-user-plus"></i></div>
                Gestión de Usuarios
            </a>

            <a href="marcaciones.php" class="sidebar-item">
                <div class="icon-wrapper icon-clock"><i class="fa fa-clock"></i></div>
                Marcaciones
            </a>

            <div class="divider" style="margin: 10px 20px; border-bottom: 1px solid rgba(255,255,255,0.2);"></div>

            <a href="permisos.php" class="sidebar-item">
                <div class="icon-wrapper icon-permissions"><i class="fa fa-calendar-times"></i></div>
                Permisos / Ausencias
            </a>
            
            <a href="reportes.php" class="sidebar-item">
                <div class="icon-wrapper icon-reports"><i class="fa fa-file-pdf"></i></div>
                Reportes PDF o Excel
            </a>

            <a href="notificaciones.php" class="sidebar-item">
                <div class="icon-wrapper icon-notifications"><i class="fa fa-bell"></i></div>
                Notificaciones Auto.
            </a>

            <a href="turnos.php" class="sidebar-item">
                <div class="icon-wrapper icon-shifts"><i class="fa fa-sync-alt"></i></div>
                Turnos Rotativos
            </a>

            <a href="../registro_asistencia.php" class="sidebar-item" target="_blank" title="Abrir Kiosko de Marcaciones">
                <div class="icon-wrapper icon-fingerprint"><i class="fa fa-fingerprint"></i></div>
                Huella Biométrica (Kiosko)
            </a>

            <div class="divider" style="margin: 10px 20px; border-bottom: 1px solid rgba(255,255,255,0.2);"></div>

            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item">
                <div class="icon-wrapper icon-logout"><i class="fa fa-sign-out-alt"></i></div>
                Cerrar Sesión
            </a>
        </nav>

        <main class="content">
            <h1 class="page-title">Bienvenido Administrador</h1>
            <div class="divider"></div>

            <div class="card">
                <h2 class="card-title">Estadísticas del sistema</h2>
                <div class="card-divider"></div>

                <div class="stats-container">
                    <div class="stat-box">
                        <span>Usuarios Registrados</span>
                        <i class="fa fa-user"></i>
                        <span class="stat-num"><?= htmlspecialchars($total_usuarios) ?></span>
                    </div>

                    <div class="stat-box">
                        <span>Trabajadores Activos</span>
                        <i class="fa fa-hard-hat"></i>
                        <span class="stat-num"><?= htmlspecialchars($total_trabajadores) ?></span>
                    </div>

                    <div class="stat-box">
                        <span>Marcaciones Hoy</span>
                        <i class="fa fa-clock"></i>
                        <span class="stat-num"><?= htmlspecialchars($marcaciones_hoy) ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($alert && is_array($alert)): ?>
        <script>
            Swal.fire({
                icon: "<?= htmlspecialchars($alert['icon'] ?? 'info') ?>",
                title: "<?= htmlspecialchars($alert['title'] ?? 'Mensaje') ?>",
                text: "<?= htmlspecialchars($alert['text'] ?? '') ?>",
                confirmButtonColor: "#2e7d32"
            });
        </script>
    <?php endif; ?>

</body>

</html>