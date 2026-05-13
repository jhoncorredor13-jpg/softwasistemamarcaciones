<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}

require_once __DIR__ . '/../../controllers/PermisoController.php';
require_once __DIR__ . '/../../models/usuario.php';

$controller = new PermisoController();
$permisos = $controller->indexAdmin();

// Para el select de trabajadores
require_once __DIR__ . '/../../config/database.php';
$db = (new Database())->conectar();
$usuarioModel = new Usuario($db);
$trabajadoresList = $usuarioModel->listarTrabajadores();

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
            background-color: #fff; border-radius: 12px; padding: 25px 30px;
            margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .search-container {
            margin-bottom: 25px; position: relative;
        }
        .search-input {
            width: 100%; padding: 15px 45px; border: 2px solid #52A65A;
            border-radius: 12px; font-size: 16px; outline: none;
            box-shadow: 0 4px 6px rgba(82, 166, 90, 0.1);
        }
        .search-icon {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #52A65A; font-size: 18px;
        }
        .permiso-card {
            background: #fff; border: 1px solid #eee; border-radius: 15px;
            padding: 20px; margin-bottom: 15px; display: flex;
            align-items: center; justify-content: space-between;
            transition: all 0.2s;
        }
        .permiso-card:hover { border-color: #52A65A; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .permiso-info { display: flex; align-items: center; gap: 20px; }
        .worker-avatar {
            width: 50px; height: 50px; background: #dfeee2; color: #2e7d32;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 20px; font-weight: bold;
        }
        .permiso-details h4 { margin: 0 0 5px 0; font-size: 16px; color: #333; }
        .permiso-details p { margin: 0; font-size: 13px; color: #666; }
        .permiso-type {
            font-size: 11px; font-weight: bold; text-transform: uppercase;
            padding: 3px 8px; border-radius: 4px; margin-bottom: 5px; display: inline-block;
        }
        .type-vacaciones { background: #e3f2fd; color: #1976d2; }
        .type-permiso { background: #f3e5f5; color: #7b1fa2; }
        .permiso-actions { display: flex; gap: 10px; }
        .btn-approve-lg { background: #4CAF50; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-reject-lg { background: #F44336; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-approve-lg:hover { background: #388E3C; }
        .btn-reject-lg:hover { background: #D32F2F; }
        .date-badge { background: #f5f5f5; padding: 5px 10px; border-radius: 6px; font-size: 12px; color: #333; display: inline-block; margin-top: 5px; }
        .empty { text-align: center; padding: 60px; color: #999; }

        /* Formulario de Asignación */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px; border: 1.5px solid #ddd; border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: #FFC107; }
        .btn-save-yellow {
            background-color: #FFC107; color: #000; padding: 12px 25px;
            border-radius: 10px; border: none; cursor: pointer; font-weight: 700;
            display: flex; align-items: center; gap: 10px; transition: background 0.2s;
        }
        .btn-save-yellow:hover { background-color: #e6af06; }
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
            <h1 class="page-title">Módulo de Permisos y Vacaciones</h1>
            <div class="divider"></div>

            <!-- Formulario de Asignación Directa (Admin) -->
            <div class="card" style="border-radius: 15px; border-top: 5px solid #FFC107;">
                <h2 style="font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-calendar-plus" style="color: #FFC107;"></i> Asignar Permiso o Vacaciones a Trabajador
                </h2>
                <form action="../../controllers/PermisoController.php?accion=asignar_admin" method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Seleccionar Trabajador</label>
                            <select name="id_trabajador" class="form-control" required>
                                <option value="">-- Seleccione un trabajador --</option>
                                <?php foreach ($trabajadoresList as $t): ?>
                                    <option value="<?= $t['id_trabajador'] ?>"><?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Motivo de Permiso o Vacaciones</label>
                            <input type="text" name="motivo" class="form-control" placeholder="Escriba el motivo (ej: Vacaciones, Cita Médica...)" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Desde Fecha</label>
                            <input type="datetime-local" name="fecha_inicio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Hasta Fecha</label>
                            <input type="datetime-local" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-save-yellow">
                        <i class="fa fa-save"></i> Generar y Asignar Permiso
                    </button>
                </form>
            </div>

            <div class="card">
                <div class="search-container">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="buscar" class="search-input" placeholder="Buscar por trabajador o motivo..." oninput="filtrar()">
                </div>

                <div id="permisos-lista">
                    <?php if (empty($permisos)): ?>
                        <div class="empty">
                            <i class="fa fa-folder-open fa-3x" style="margin-bottom:15px;display:block"></i>
                            No hay solicitudes de permisos o vacaciones pendientes.
                        </div>
                    <?php else: ?>
                        <?php foreach ($permisos as $p): 
                            $isVacation = stripos($p['motivo'], 'vacacion') !== false;
                            $typeLabel = $isVacation ? 'Vacaciones' : 'Permiso';
                            $typeClass = $isVacation ? 'type-vacaciones' : 'type-permiso';
                        ?>
                            <div class="permiso-card" data-search="<?= strtolower($p['nombres'] . ' ' . $p['apellidos'] . ' ' . $p['motivo']) ?>">
                                <div class="permiso-info">
                                    <div class="worker-avatar">
                                        <?= strtoupper(substr($p['nombres'], 0, 1) . substr($p['apellidos'], 0, 1)) ?>
                                    </div>
                                    <div class="permiso-details">
                                        <span class="permiso-type <?= $typeClass ?>"><?= $typeLabel ?></span>
                                        <h4><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></h4>
                                        <p><strong>Motivo:</strong> <?= htmlspecialchars($p['motivo']) ?></p>
                                        <div class="date-badge">
                                            <i class="fa fa-calendar-alt"></i> 
                                            <?= date('d/m/Y H:i', strtotime($p['fecha_inicio'])) ?> 
                                            <i class="fa fa-arrow-right" style="margin: 0 5px;"></i>
                                            <?= date('d/m/Y H:i', strtotime($p['fecha_fin'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="permiso-actions">
                                    <?php if ($p['estado'] === 'pendiente'): ?>
                                        <button type="button" class="btn-approve-lg" onclick="gestionarPermiso(<?= $p['id_permiso'] ?>, 'aprobado', '<?= $typeLabel ?>', '<?= $p['fecha_fin'] ?>')">
                                            <i class="fa fa-check-circle"></i> Aceptar <?= $typeLabel ?>
                                        </button>
                                        <button type="button" class="btn-reject-lg" onclick="gestionarPermiso(<?= $p['id_permiso'] ?>, 'rechazado', '<?= $typeLabel ?>')">
                                            <i class="fa fa-times-circle"></i> No Aceptar
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-<?= strtolower($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function filtrar() {
            const q = document.getElementById('buscar').value.toLowerCase();
            document.querySelectorAll('.permiso-card').forEach(card => {
                const text = card.getAttribute('data-search');
                card.style.display = text.includes(q) ? 'flex' : 'none';
            });
        }

        async function gestionarPermiso(id, estado, tipo, fechaFinActual = '') {
            const isAprobar = estado === 'aprobado';
            const actionText = isAprobar ? 'Aprobar' : 'Rechazar';
            const color = isAprobar ? '#4CAF50' : '#F44336';
            
            let htmlContent = `¿Está seguro de que desea ${actionText.toLowerCase()} esta solicitud de <strong>${tipo}</strong>?`;
            
            if (isAprobar) {
                if (tipo === 'Vacaciones') {
                    htmlContent += `
                        <div style="margin-top:20px; text-align:left;">
                            <label style="display:block; font-size:14px; margin-bottom:5px;"><strong>Aceptar vacaciones hasta el día:</strong></label>
                            <input type="date" id="swal-fecha-fin" class="swal2-input" value="${fechaFinActual.split(' ')[0]}" style="width:100%; margin:0;">
                        </div>`;
                } else {
                    htmlContent += `
                        <div style="margin-top:20px; text-align:left;">
                            <label style="display:block; font-size:14px; margin-bottom:5px;"><strong>Confirmar fecha y hora exacta:</strong></label>
                            <input type="datetime-local" id="swal-fecha-fin" class="swal2-input" value="${fechaFinActual.replace(' ', 'T')}" style="width:100%; margin:0;">
                        </div>`;
                }
            }

            const { isConfirmed, value: fechaFin } = await Swal.fire({
                title: `${actionText} Solicitud`,
                html: htmlContent,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#aaa',
                confirmButtonText: `Sí, ${actionText}`,
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    if (isAprobar) {
                        return document.getElementById('swal-fecha-fin').value;
                    }
                }
            });

            if (isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../controllers/PermisoController.php?accion=cambiarEstado';
                
                const fields = {
                    'id_permiso': id,
                    'estado': estado,
                    'fecha_fin': fechaFin || ''
                };

                for (const name in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = fields[name];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            }
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
