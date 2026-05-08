<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}

require_once __DIR__ . '/../../controllers/TurnoController.php';

$controller = new TurnoController();
$turnos_agrupados = $controller->indexAdminAgrupados();
$trabajadores = $controller->getAllTrabajadores();

$admin = $_SESSION['usuario'];
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos Rotativos</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { margin: 0; padding: 0; font-family: 'Roboto', Arial, sans-serif; background-color: #E4E4E4; display: flex; flex-direction: column; height: 100vh; }
        .topbar { display: flex; align-items: center; background-color: #E4E4E4; height: 60px; justify-content: space-between; padding-right: 30px; }
        .topbar-left { display: flex; align-items: center; height: 100%; }
        .logo-area { width: 220px; background-color: #fff; height: 100%; display: flex; align-items: center; justify-content: center; }
        .logo-area img { max-height: 45px; }
        .topbar-title { margin-left: 20px; font-size: 16px; color: #2e7d32; font-weight: 700; }
        .user-profile { display: flex; align-items: center; font-size: 14px; color: #000; }
        .user-profile .icon { background-color: #fff; border: 1px solid #ccc; width: 25px; height: 25px; display: flex; justify-content: center; align-items: center; margin-right: 15px; color: #555; }
        .main-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { width: 220px; background-color: #52A65A; display: flex; flex-direction: column; padding-top: 20px; overflow-y: auto; }
        .sidebar-item { display: flex; align-items: center; padding: 18px 20px; color: #fff; text-decoration: none; font-size: 14px; gap: 15px; }
        .sidebar-item:hover, .sidebar-item.active { background-color: #1b5e20; color: #fff; text-decoration: none; }
        .icon-wrapper { width: 35px; height: 35px; display: flex; justify-content: center; align-items: center; border-radius: 4px; }
        .icon-home { background-color: #fff; color: #000; font-size: 22px; }
        .icon-yellow { background-color: #FFC107; color: #000; font-size: 20px; }
        .content { flex: 1; padding: 30px 40px; overflow-y: auto; background: #dfeee2; }
        .page-title { font-size: 22px; font-weight: 400; color: #000; margin-bottom: 8px; }
        .divider { border-bottom: 1px solid #333; margin-bottom: 30px; width: 100%; }
        
        .card { background-color: #fff; border-radius: 30px; padding: 30px 40px; margin-bottom: 30px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05); }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .card-header h2 { font-size: 20px; font-weight: 500; color: #111; margin: 0; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 200px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 6px; font-size: 14px; color: #444; font-weight: 500; }
        .form-control { padding: 10px 14px; border: 1px solid #ccc; border-radius: 8px; font-family: 'Roboto', sans-serif; font-size: 14px; outline: none; }
        .form-control:focus { border-color: #FFC107; }
        .btn-submit { background: #FFC107; color: #000; border: none; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: opacity 0.2s; align-self: flex-start; margin-top: 10px; }
        .btn-submit:hover { opacity: 0.85; }

        .search-bar { margin-bottom: 20px; }
        .search-bar input { width: 300px; padding: 9px 14px; border: 1px solid #777; border-radius: 8px; font-family: 'Roboto', sans-serif; font-size: 13px; outline: none; }
        .search-bar input:focus { border-color: #FFC107; }
        
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #FFC107; color: #000; }
        thead th { padding: 11px 14px; text-align: left; font-weight: 500; border: 1px solid #ccc; }
        tbody tr { border-bottom: 1px solid #ddd; }
        tbody tr:hover { background: #fffcf0; }
        tbody td { padding: 11px 14px; color: #111; border: 1px solid #ddd; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 11px; text-transform: uppercase; background: #e0e0e0; color: #333; }
        
        .btn-del { background: #fff; color: #E74C3C; border: 1px solid #ccc; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; }
        .btn-del:hover { background: #ffecec; border-color: #E74C3C; }
        .empty { text-align: center; padding: 40px; color: #999; font-size: 14px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola" onerror="this.style.display='none'">
            </div>
            <div class="topbar-title">Turnos Rotativos</div>
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
            <a href="turnos.php" class="sidebar-item active">
                <div class="icon-wrapper icon-yellow"><i class="fa fa-sync-alt"></i></div>
                <span>Turnos Rotativos</span>
            </a>
            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item" style="margin-top: auto;">
                <div class="icon-wrapper" style="background:#fff; color:#E74C3C;"><i class="fa fa-sign-out-alt"></i></div>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
        <main class="content">
            <h1 class="page-title">Módulo de Turnos Rotativos</h1>
            <div class="divider"></div>

            <!-- Formulario de Asignación Masiva -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fa fa-calendar-plus" style="color: #FFC107; margin-right: 8px;"></i> Asignar Turnos a Trabajador</h2>
                </div>
                <form action="../../controllers/TurnoController.php?accion=asignar" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Seleccionar Trabajador</label>
                            <select name="id_trabajador" class="form-control" required>
                                <option value="">-- Seleccione un trabajador --</option>
                                <?php foreach ($trabajadores as $t): ?>
                                    <option value="<?= $t['id_trabajador'] ?>"><?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?> (Doc: <?= $t['documento'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Turno</label>
                            <select name="tipo_turno" class="form-control" required>
                                <option value="">-- Seleccione un turno --</option>
                                <option value="manana">Turno Mañana (06:00 a 14:00)</option>
                                <option value="tarde">Turno Tarde (14:00 a 22:00)</option>
                                <option value="noche">Turno Noche (22:00 a 06:00)</option>
                                <option value="oficina">Turno Oficina (08:00 a 17:00)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Desde Fecha</label>
                            <input type="date" name="fecha_inicio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Hasta Fecha</label>
                            <input type="date" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa fa-save"></i> Generar y Asignar Turnos</button>
                </form>
            </div>

            <!-- Lista de Turnos Programados -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fa fa-list" style="color: #FFC107; margin-right: 8px;"></i> Turnos Programados (Futuros)</h2>
                </div>
                <div class="search-bar">
                    <input type="text" id="buscar" placeholder="🔍 Buscar por trabajador..." oninput="filtrar()">
                </div>

                <?php if (empty($turnos_agrupados)): ?>
                    <div class="empty">
                        <i class="fa fa-calendar-times fa-3x" style="margin-bottom:12px;display:block;color:#ccc;"></i>
                        No hay turnos programados en el sistema a partir de hoy.
                    </div>
                <?php else: ?>
                    <table id="tabla">
                        <thead>
                            <tr>
                                <th>Rango de Fechas</th>
                                <th>Trabajador</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turnos_agrupados as $t): ?>
                                <tr>
                                    <td>
                                        <?php if ($t['fecha_inicio'] === $t['fecha_fin']): ?>
                                            <strong><?= date('d/m/Y', strtotime($t['fecha_inicio'])) ?></strong>
                                        <?php else: ?>
                                            <strong><?= date('d/m/Y', strtotime($t['fecha_inicio'])) ?></strong> <br>
                                            <span style="color:#777; font-size:11px;">hasta</span> <br>
                                            <strong><?= date('d/m/Y', strtotime($t['fecha_fin'])) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?> <br><small>Doc: <?= htmlspecialchars($t['documento']) ?></small></td>
                                    <td><span class="badge"><?= date('H:i', strtotime($t['horaEntrada'])) ?></span></td>
                                    <td><span class="badge"><?= date('H:i', strtotime($t['horaSalida'])) ?></span></td>
                                    <td>
                                        <form action="../../controllers/TurnoController.php?accion=eliminar" method="POST" style="margin:0;" onsubmit="return confirmarEliminar(event)">
                                            <input type="hidden" name="ids_turnos" value="<?= implode(',', $t['ids_turnos']) ?>">
                                            <button type="submit" class="btn-del" title="Eliminar Turnos"><i class="fa fa-trash"></i></button>
                                        </form>
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

        function confirmarEliminar(e) {
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar este turno?',
                text: 'El trabajador ya no tendrá este horario asignado para esa fecha.',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Sí, eliminar',
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
                confirmButtonColor: "#FFC107"
            });
        </script>
    <?php endif; ?>
</body>
</html>
