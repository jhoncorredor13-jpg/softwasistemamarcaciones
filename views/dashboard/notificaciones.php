<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}

require_once __DIR__ . '/../../controllers/NotificacionController.php';
$controller = new NotificacionController();
$notificaciones = $controller->getNotificaciones();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro de Notificaciones - Panel Administrador</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background: #dfeee2; display: flex; flex-direction: column; height: 100vh; }
        .topbar { height: 48px; background: #dfe8df; display: flex; align-items: center; justify-content: space-between; padding: 0 28px 0 20px; }
        .main-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { width: 220px; background-color: #52A65A; display: flex; flex-direction: column; padding-top: 20px; overflow-y: auto; }
        .sidebar-item { display: flex; align-items: center; padding: 18px 20px; color: #fff; text-decoration: none; font-size: 14px; gap: 15px; }
        .sidebar-item:hover { background-color: rgba(0, 0, 0, 0.05); }
        .sidebar-item.active { background-color: rgba(0, 0, 0, 0.1); border-left: 4px solid #fff; }
        
        .content { flex: 1; padding: 30px 40px; overflow-y: auto; background: transparent; }
        .page-title { font-size: 22px; font-weight: 400; color: #000; margin-bottom: 8px; }
        .divider { border-bottom: 1px solid #333; margin-bottom: 30px; width: 100%; }
        
        /* Notifications Feed */
        .feed-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .notif-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-left: 5px solid transparent;
            transition: transform 0.2s;
        }

        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .notif-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: #fff;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .notif-content {
            flex: 1;
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .notif-title {
            font-size: 16px;
            font-weight: 600;
            color: #222;
        }

        .notif-time {
            font-size: 12px;
            color: #888;
        }

        .notif-message {
            font-size: 14px;
            color: #555;
            line-height: 1.4;
        }

        .notif-action {
            margin-left: 20px;
        }

        .btn-action {
            background: #f0f0f0;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-action:hover {
            background: #e0e0e0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 12px;
            color: #777;
        }
        .empty-state i {
            font-size: 60px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            margin: 0;
            color: #444;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div style="display:flex; align-items:center;">
            <div style="background:#fff; padding:2px 5px; border-radius:4px; margin-right:10px;">
                <img src="../../img/logo.png" alt="Logo" height="24">
            </div>
            <div style="font-weight: bold; color: #2e7d32;">Panel Administrador</div>
        </div>
        <div style="font-size: 13px;"><i class="fa fa-user"></i> Administrador</div>
    </div>

    <div class="main-container">
        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item"><i class="fa fa-home"></i> Inicio</a>
            <a href="lista_usuario.php" class="sidebar-item"><i class="fa fa-users"></i> Gestión Usuarios</a>
            <a href="turnos.php" class="sidebar-item"><i class="fa fa-sync-alt"></i> Turnos Rotativos</a>
            <a href="permisos.php" class="sidebar-item"><i class="fa fa-calendar-times"></i> Permisos</a>
            <a href="marcaciones.php" class="sidebar-item"><i class="fa fa-clock"></i> Marcaciones</a>
            <a href="reportes.php" class="sidebar-item"><i class="fa fa-file-pdf"></i> Reportes</a>
            <a href="notificaciones.php" class="sidebar-item active"><i class="fa fa-bell"></i> Notificaciones</a>
            <div style="border-bottom: 1px solid rgba(255,255,255,0.2); margin: 10px 20px;"></div>
            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item" style="color:#ffcdd2"><i class="fa fa-sign-out-alt"></i> Salir</a>
        </nav>

        <main class="content">
            <h1 class="page-title">Centro de Alertas y Notificaciones</h1>
            <div class="divider"></div>

            <div class="feed-container">
                <?php if (empty($notificaciones)): ?>
                    <div class="empty-state">
                        <i class="fa fa-check-circle"></i>
                        <h3>¡Todo está al día!</h3>
                        <p>No tienes notificaciones ni alertas pendientes por revisar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notificaciones as $n): ?>
                        <div class="notif-card" style="border-left-color: <?= $n['color'] ?>;">
                            <div class="notif-icon" style="background-color: <?= $n['color'] ?>;">
                                <i class="fa <?= $n['icono'] ?>"></i>
                            </div>
                            
                            <div class="notif-content">
                                <div class="notif-header">
                                    <div class="notif-title"><?= htmlspecialchars($n['titulo']) ?></div>
                                    <div class="notif-time">
                                        <i class="fa fa-clock" style="font-size:10px; margin-right:3px;"></i>
                                        <?= date('d/m/Y h:i A', strtotime($n['fecha'])) ?>
                                    </div>
                                </div>
                                <div class="notif-message">
                                    <?= htmlspecialchars($n['mensaje']) ?>
                                </div>
                            </div>

                            <div class="notif-action">
                                <a href="<?= $n['accion'] ?>" class="btn-action">Ver Detalle</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
