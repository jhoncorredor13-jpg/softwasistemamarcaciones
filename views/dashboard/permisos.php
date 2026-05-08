<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}

require_once __DIR__ . '/../../controllers/PermisoController.php';

$controller = new PermisoController();
$permisos = $controller->indexAdmin();

$admin = $_SESSION['usuario'];
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Permisos</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0; padding: 0; font-family: 'Roboto', Arial, sans-serif;
            background-color: #E4E4E4; display: flex; flex-direction: column; height: 100vh;
        }
        .topbar {
            display: flex; align-items: center; background-color: #E4E4E4;
            height: 60px; justify-content: space-between; padding-right: 30px;
        }
        .topbar-left { display: flex; align-items: center; height: 100%; }
        .logo-area {
            width: 220px; background-color: #fff; height: 100%;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-area img { max-height: 45px; }
        .topbar-title { margin-left: 20px; font-size: 16px; color: #2e7d32; font-weight: 700; }
        .user-profile { display: flex; align-items: center; font-size: 14px; color: #000; }
        .user-profile .icon {
            background-color: #fff; border: 1px solid #ccc; width: 25px; height: 25px;
            display: flex; justify-content: center; align-items: center; margin-right: 15px; color: #555;
        }
        .main-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar {
            width: 220px; background-color: #52A65A; display: flex;
            flex-direction: column; padding-top: 20px; overflow-y: auto;
        }
        .sidebar-item {
            display: flex; align-items: center; padding: 18px 20px; color: #fff;
            text-decoration: none; font-size: 14px; gap: 15px;
        }
        .sidebar-item:hover, .sidebar-item.active { background-color: #1b5e20; color: #fff; text-decoration: none; }
        .icon-wrapper {
            width: 35px; height: 35px; display: flex; justify-content: center;
            align-items: center; border-radius: 4px;
        }
        .icon-home { background-color: #fff; color: #000; font-size: 22px; }
        .icon-purple { background-color: #9C27B0; color: #fff; font-size: 20px; }
        .content { flex: 1; padding: 30px 40px; overflow-y: auto; background: #dfeee2; }
        .page-title { font-size: 22px; font-weight: 400; color: #000; margin-bottom: 8px; }
        .divider { border-bottom: 1px solid #333; margin-bottom: 30px; width: 100%; }
        .card {
            background-color: #fff; border-radius: 35px; padding: 25px 40px;
            margin-bottom: 30px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #777;
        }
        .card-header h2 { font-size: 20px; font-weight: 500; color: #111; margin: 0; }
        .search-bar { margin-bottom: 20px; }
        .search-bar input {
            width: 300px; padding: 9px 14px; border: 1px solid #777;
            border-radius: 8px; font-family: 'Roboto', sans-serif; font-size: 13px; outline: none;
        }
        .search-bar input:focus { border-color: #FFC107; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #FFC107; color: #000; }
        thead th { padding: 11px 14px; text-align: left; font-weight: 500; border: 1px solid #333; }
        tbody tr { border-bottom: 1px solid #ddd; }
        tbody tr:hover { background: #f9f9f9; }
        tbody td { padding: 11px 14px; color: #111; border: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .badge-pendiente { background-color: #FFC107; color: #000; }
        .badge-aprobado { background-color: #4CAF50; color: #fff; }
        .badge-rechazado { background-color: #F44336; color: #fff; }
        .btn-action {
            border: none; padding: 6px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; color: #fff;
        }
        .btn-approve { background: #4CAF50; }
        .btn-approve:hover { background: #388E3C; }
        .btn-reject { background: #F44336; }
        .btn-reject:hover { background: #D32F2F; }
        .empty { text-align: center; padding: 40px; color: #999; font-size: 14px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola" onerror="this.style.display='none'">
            </div>
            <div class="topbar-title">Permisos y Ausencias</div>
        </div>
        <div class="user-profile">
            <div class="icon"><i class="fa fa-user"></i></div>
            <?= htmlspecialchars($admin['nombres'] ?? 'Admin') ?>
        </div>
    </div>
    <div class="main-container">
        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item">
                <div class="icon-wrapper icon-home"><i class="fa fa-home"></i></div>
                <span>Inicio</span>
            </a>
            <a href="permisos.php" class="sidebar-item active">
                <div class="icon-wrapper icon-purple"><i class="fa fa-calendar-times"></i></div>
                <span>Permisos</span>
            </a>
            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item" style="margin-top: auto;">
                <div class="icon-wrapper" style="background:#fff; color:#E74C3C;"><i class="fa fa-sign-out-alt"></i></div>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
        <main class="content">
            <h1 class="page-title">Módulo de Permisos</h1>
            <div class="divider"></div>

            <div class="card">
                <div class="card-header">
                    <h2>Solicitudes de Permisos y Vacaciones</h2>
                </div>
                <div class="search-bar">
                    <input type="text" id="buscar" placeholder="🔍 Buscar por trabajador..." oninput="filtrar()">
                </div>

                <?php if (empty($permisos)): ?>
                    <div class="empty">
                        <i class="fa fa-folder-open fa-3x" style="margin-bottom:12px;display:block"></i>
                        No hay permisos registrados.
                    </div>
                <?php else: ?>
                    <table id="tabla">
                        <thead>
                            <tr>
                                <th>Rango de Fechas</th>
                                <th>Trabajador</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permisos as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></strong> <br>
                                        <span style="color:#777; font-size:11px;">hasta</span> <br>
                                        <strong><?= date('d/m/Y', strtotime($p['fecha_fin'])) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></strong> <br>
                                        <small style="color: #666;">Doc: <?= htmlspecialchars($p['documento']) ?></small>
                                    </td>
                                    <td style="max-width: 250px; color: #555;"><?= htmlspecialchars($p['motivo']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($p['estado']) ?>">
                                            <?= htmlspecialchars($p['estado']) ?>
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <?php if ($p['estado'] === 'pendiente'): ?>
                                            <form action="../../controllers/PermisoController.php?accion=cambiarEstado" method="POST" style="display:inline;" onsubmit="return confirmar(event, 'aprobado')">
                                                <input type="hidden" name="id_permiso" value="<?= $p['id_permiso'] ?>">
                                                <input type="hidden" name="estado" value="aprobado">
                                                <button type="submit" class="btn-action btn-approve" title="Aceptar Permiso" style="background:#4CAF50; border:none; color:#fff; padding:8px 12px; border-radius:8px; cursor:pointer; margin-right:5px;">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="../../controllers/PermisoController.php?accion=cambiarEstado" method="POST" style="display:inline;" onsubmit="return confirmar(event, 'rechazado')">
                                                <input type="hidden" name="id_permiso" value="<?= $p['id_permiso'] ?>">
                                                <input type="hidden" name="estado" value="rechazado">
                                                <button type="submit" class="btn-action btn-reject" title="Rechazar Permiso" style="background:#F44336; border:none; color:#fff; padding:8px 12px; border-radius:8px; cursor:pointer;">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:#999; font-style:italic; font-size:12px;">Procesado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function filtrar() {
            const q = document.getElementById('buscar').value.toLowerCase();
            document.querySelectorAll('#tabla tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        function confirmar(e, estado) {
            e.preventDefault();
            const form = e.target;
            const accionText = estado === 'aprobado' ? 'aprobar' : 'rechazar';
            const color = estado === 'aprobado' ? '#4CAF50' : '#F44336';

            Swal.fire({
                icon: 'question',
                title: `¿Desea ${accionText} este permiso?`,
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Sí, ' + accionText,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>
    <?php if ($alert && is_array($alert)): ?>
        <script>
            Swal.fire({
                icon: "<?= htmlspecialchars($alert['icon'] ?? 'info') ?>",
                title: "<?= htmlspecialchars($alert['title'] ?? 'Mensaje') ?>",
                text: "<?= htmlspecialchars($alert['text'] ?? '') ?>",
                confirmButtonColor: "#9C27B0"
            });
        </script>
    <?php endif; ?>
</body>
</html>
