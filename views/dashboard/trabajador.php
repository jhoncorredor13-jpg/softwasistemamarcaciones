<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'trabajador') {
    header("Location: ../usuario/login.php");
    exit;
}
require_once __DIR__ . '/../../config/database.php';
$db      = (new Database())->conectar();
$usuario = $_SESSION['usuario'];
$hoy     = date('Y-m-d');


// Buscar id_trabajador
$stmt = $db->prepare("SELECT id_trabajador, id_cargo FROM trabajador WHERE id_usuario = ? AND estado = 1 LIMIT 1");
$stmt->execute([$usuario['id_usuario']]);
$trabajador = $stmt->fetch();


// Turno de hoy
$turnoHoy = null;
if ($trabajador) {
    $stmt2 = $db->prepare("SELECT t.*, re.hora_entrada, re.id_registro_entrada, rs.hora_salida
        FROM turno t
        LEFT JOIN registro_entrada re ON re.id_turno = t.id_turno
        LEFT JOIN registro_salida  rs ON rs.registro_entrada = re.id_registro_entrada
        WHERE t.id_trabajador = ? AND t.fecha = ?
        LIMIT 1");
    $stmt2->execute([$trabajador['id_trabajador'], $hoy]);
    $turnoHoy = $stmt2->fetch();
}

// Obtener permisos
require_once __DIR__ . '/../../controllers/PermisoController.php';
$misPermisos = [];
if ($trabajador) {
    $permisoController = new PermisoController();
    $misPermisos = $permisoController->indexTrabajador($trabajador['id_trabajador']);
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .icon-logout { background-color: #fff; color: #E74C3C; font-size: 22px; border-radius: 4px; }

        .content { flex: 1; padding: 30px 40px; overflow-y: auto; }
        .page-title { font-size: 22px; font-weight: 400; color: #000; margin-bottom: 8px; }
        .divider { border-bottom: 1px solid #333; margin-bottom: 30px; width: 100%; }

        .card { background-color: #fff; border-radius: 40px; padding: 25px 40px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .card h2 { font-size: 20px; font-weight: 400; color: #111; margin-bottom: 12px; }
        .card-divider { border-bottom: 1px solid #777; margin-bottom: 30px; width: 100%; }
        
        .info-hoy { font-size: 14px; color: #555; margin-bottom: 20px; }
        .info-hoy strong { color: #52A65A; }
        
        .marcacion-box { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 25px; }
        .marc-card { flex: 1; min-width: 200px; background: #F6F6F6; border-radius: 10px; padding: 25px; text-align: center; }
        .marc-card h3 { font-size: 16px; font-weight: 500; color: #333; margin-bottom: 10px; }
        .marc-card .hora { font-size: 28px; font-weight: 700; color: #52A65A; }
        .marc-card .sin-marc { font-size: 14px; color: #999; }
        
        .btn-marcar {
            display: block; width: 100%; padding: 15px;
            border: none; border-radius: 10px; font-size: 15px;
            font-weight: 500; cursor: pointer; margin-top: 12px;
            transition: opacity .2s; border: 1px solid #333;
        }
        .btn-entrada { background: #65B969; color: #000; }
        .btn-entrada:hover { opacity: .85; }
        .btn-salida { background: #F4D03F; color: #000; }
        .btn-salida:hover { opacity: .85; }
        .btn-marcar:disabled { opacity: .5; cursor: not-allowed; background: #E2E2E2; color: #000; border-color: #999; }
        
        /* Permisos Styles */
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; font-size: 14px; margin-bottom: 5px; color: #333; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: 'Roboto', sans-serif; font-size: 14px; }
        .btn-submit { background: #9C27B0; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: opacity 0.2s; }
        .btn-submit:hover { opacity: 0.9; }
        
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 15px; }
        thead tr { background: #9C27B0; color: #fff; }
        thead th { padding: 11px 14px; text-align: left; font-weight: 500; border: 1px solid #333; }
        tbody tr { border-bottom: 1px solid #ddd; }
        tbody td { padding: 11px 14px; color: #111; border: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .badge-pendiente { background-color: #FFC107; color: #000; }
        .badge-aprobado { background-color: #4CAF50; color: #fff; }
        .badge-rechazado { background-color: #F44336; color: #fff; }
        .flex-row { display: flex; gap: 15px; }
        .flex-col { flex: 1; }
    </style>
</head>
<body>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola" onerror="this.style.display='none'">
            </div>
            <div class="topbar-title">Panel Trabajador</div>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="icon"><i class="fa fa-user"></i></div>
                <?= htmlspecialchars($usuario['nombres']) ?>
            </div>
        </div>
    </div>

    <div class="main-container">
        <nav class="sidebar">
            <a href="trabajador.php" class="sidebar-item">
                <div class="icon-wrapper icon-home"><i class="fa fa-home"></i></div>
                Mi Panel
            </a>
            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item">
                <div class="icon-wrapper icon-logout"><i class="fa fa-sign-out-alt"></i></div>
                Cerrar Sesión
            </a>
        </nav>

        <main class="content">
            <h1 class="page-title">Bienvenido, <?= htmlspecialchars($usuario['nombres']) ?> 👋</h1>
            <div class="divider"></div>

            <div class="card">
                <h2><i class="fa fa-clock"></i> Mis Marcaciones de Hoy</h2>
                <div class="card-divider"></div>
                
                <p class="info-hoy">Fecha: <strong><?= date('d/m/Y') ?></strong> &nbsp;|&nbsp; Hora actual: <strong id="hora-actual">--:--:--</strong></p>

                <div class="marcacion-box">
                    <div class="marc-card">
                        <h3><i class="fa fa-sign-in-alt"></i> Entrada</h3>
                        <?php if ($turnoHoy && $turnoHoy['hora_entrada']): ?>
                            <div class="hora"><?= $turnoHoy['hora_entrada'] ?></div>
                        <?php else: ?>
                            <div class="sin-marc">Sin registrar</div>
                        <?php endif; ?>
                    </div>
                    <div class="marc-card">
                        <h3><i class="fa fa-sign-out-alt"></i> Salida</h3>
                        <?php if ($turnoHoy && $turnoHoy['hora_salida']): ?>
                            <div class="hora"><?= $turnoHoy['hora_salida'] ?></div>
                        <?php else: ?>
                            <div class="sin-marc">Sin registrar</div>
                        <?php endif; ?>
                    </div>
                </div>

                <form action="../../controllers/MarcacionController.php" method="POST" style="margin-top:16px">
                    <input type="hidden" name="id_trabajador" value="<?= $trabajador['id_trabajador'] ?? '' ?>"/>
                    <?php if (!$turnoHoy || !$turnoHoy['hora_entrada']): ?>
                        <button type="submit" name="accion" value="entrada" class="btn-marcar btn-entrada">
                            <i class="fa fa-sign-in-alt"></i> Registrar Entrada
                        </button>
                    <?php elseif (!$turnoHoy['hora_salida']): ?>
                        <button type="submit" name="accion" value="salida" class="btn-marcar btn-salida">
                            <i class="fa fa-sign-out-alt"></i> Registrar Salida
                        </button>
                    <?php else: ?>
                        <button class="btn-marcar" disabled>
                            <i class="fa fa-check"></i> Marcaciones completas del día
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card" style="border-radius: 20px;">
                <h2 style="color: #9C27B0;"><i class="fa fa-calendar-times"></i> Solicitar Permiso o Vacaciones</h2>
                <div class="card-divider"></div>
                <form action="../../controllers/PermisoController.php?accion=solicitar" method="POST">
                    <input type="hidden" name="id_trabajador" value="<?= $trabajador['id_trabajador'] ?? '' ?>">
                    <div class="flex-row">
                        <div class="form-group flex-col">
                            <label>Desde la fecha</label>
                            <input type="date" name="fecha_inicio" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group flex-col">
                            <label>Hasta la fecha</label>
                            <input type="date" name="fecha_fin" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Motivo de la solicitud</label>
                        <textarea name="motivo" class="form-control" rows="3" required placeholder="Explique brevemente el motivo de su ausencia..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa fa-paper-plane"></i> Enviar Solicitud</button>
                </form>
            </div>

            <div class="card" style="border-radius: 20px;">
                <h2 style="color: #333;"><i class="fa fa-history"></i> Historial de Permisos</h2>
                <div class="card-divider"></div>
                <?php if (empty($misPermisos)): ?>
                    <p style="color: #777; font-size: 14px;">No ha solicitado permisos recientemente.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha Solicitud</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($misPermisos as $p): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_fin'])) ?></td>
                                    <td style="max-width: 200px;"><?= htmlspecialchars($p['motivo']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($p['estado']) ?>">
                                            <?= htmlspecialchars($p['estado']) ?>
                                        </span>
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
setInterval(() => {
    const d = new Date();
    document.getElementById('hora-actual').textContent = d.toLocaleTimeString('es-CO');
}, 1000);
</script>

<?php if ($alert && is_array($alert)): ?>
    <script>
        Swal.fire({
            icon: "<?= htmlspecialchars($alert['icon'] ?? 'info') ?>",
            title: "<?= htmlspecialchars($alert['title'] ?? 'Mensaje') ?>",
            text: "<?= htmlspecialchars($alert['text'] ?? '') ?>",
            confirmButtonColor: "#52A65A"
        });
    </script>
<?php endif; ?>

</body>
</html>
