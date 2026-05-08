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

$db = (new Database())->conectar();
$model = new Usuario($db);

$trabajadores = $model->listarTrabajadores();

try {
    $stmt = $db->prepare("SELECT id_cargo, nombre FROM cargo ORDER BY nombre ASC");
    $stmt->execute();
    $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $cargos = [];
}

$admin = $_SESSION['usuario'];
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>

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

        .sidebar-item:hover, .sidebar-item.active {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .icon-wrapper {
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 4px;
        }

        .icon-home { background-color: #fff; color: #000; font-size: 22px; }
        .icon-user-green { background-color: #4CAF50; color: #fff; font-size: 22px; }
        .icon-clock { background-color: #FF9800; color: #fff; font-size: 18px; }
        .icon-logout { background-color: #fff; color: #E74C3C; font-size: 18px; }

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

        .user-table-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .user-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-table-title {
            font-size: 24px;
            color: #333;
            font-weight: 400;
        }
        .user-table-title strong { font-weight: 700; }

        .btn-blue {
            background-color: #2F64E3;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn-blue:hover { background-color: #224bba; }

        .table-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 13px;
            color: #555;
        }

        .table-controls select, .table-controls input {
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            color: #444;
        }

        .styled-table thead tr {
            background-color: #F4F6F8;
            color: #555;
            font-weight: 600;
            text-align: left;
        }

        .styled-table th, .styled-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .badge-active {
            background-color: #E6F4EA;
            color: #1E8E3E;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #1E8E3E;
        }

        .badge-inactive {
            background-color: #FCE8E6;
            color: #D93025;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #D93025;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn-sm {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #ccc;
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-link-sm { border-color: #2F64E3; color: #2F64E3; }
        .btn-edit-sm { border-color: #2F64E3; color: #2F64E3; }
        .btn-activate-sm { border-color: #2e7d32; color: #2e7d32; }
        .btn-deactivate-sm { border-color: #E53935; color: #E53935; }
        .btn-delete-sm { border-color: #E53935; color: #E53935; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modal-header img {
            height: 40px;
        }

        .modal-header .header-info h3 {
            margin: 0;
            font-size: 20px;
            color: #1b5e20;
            font-weight: 700;
        }

        .modal-header .header-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .close-modal { font-size: 24px; color: #999; cursor: pointer; background: none; border: none; margin-left: auto; }
        .modal-body { padding: 30px; }
        .modal-footer { padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 12px; }
        .btn-cancel { background-color: #f8f8f8; color: #666; border: 1px solid #ddd; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; }

        .btn-save-green {
            background-color: #2e7d32;
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-save-green:hover { background-color: #1b5e20; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #2e7d32; margin-bottom: 8px; }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 12px; 
            border: 1.5px solid #cde0cd; 
            border-radius: 9px; 
            font-size: 14px; 
            outline: none; 
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo-area">
                <img src="../../img/logo.png" alt="Lagricola">
            </div>
            <div class="topbar-title">Gestión de Usuarios</div>
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

    <div class="curva" style="width: 120px; height: 4px; background: #2e7d32; border-radius: 50px; margin-left: 70px;"></div>

    <div class="main-container">

        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item">
                <div class="icon-wrapper icon-home"><i class="fa fa-home"></i></div>
                Panel de Control
            </a>

            <a href="lista_usuario.php" class="sidebar-item active">
                <div class="icon-wrapper icon-user-green"><i class="fa fa-user-plus"></i></div>
                Gestión de Usuarios
            </a>

            <a href="marcaciones.php" class="sidebar-item">
                <div class="icon-wrapper icon-clock"><i class="fa fa-clock"></i></div>
                Marcaciones
            </a>

            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item">
                <div class="icon-wrapper icon-logout"><i class="fa fa-sign-out-alt"></i></div>
                Cerrar Sesión
            </a>
        </nav>

        <main class="content">
            <h1 class="page-title">Gestión de Usuarios</h1>
            <div class="divider"></div>

            <div class="user-table-card">
                <div class="user-table-header">
                    <div class="user-table-title">Gestión de <strong>Usuarios</strong></div>
                    <button type="button" class="btn-blue" onclick="openModal('modalAgregar')">Agregar Usuario</button>
                </div>

                <div class="table-controls">
                    <div>
                        Mostrar 
                        <select id="cant_registros" onchange="refrescarTabla()">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select> 
                        registros
                    </div>
                    <div>
                        Buscar: <input type="text" id="buscar_usuario" onkeyup="filtrarTabla()">
                    </div>
                </div>

                <table class="styled-table" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajadores as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?></td>
                                <td><?= htmlspecialchars($t['email']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($t['rol'])) ?></td>
                                <td>
                                    <?php if($t['activo']): ?>
                                        <span class="badge-active">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn-sm btn-edit-sm" title="Editar" onclick='openEditModal(<?= json_encode($t) ?>)'><i class="fa fa-pencil-alt"></i></button>
                                        
                                        <?php if ($t['activo']): ?>
                                            <form id="form-desactivar-<?= $t['id_usuario'] ?>" action="../../controllers/UsuarioController.php?accion=desactivar" method="POST" style="margin:0;">
                                                <input type="hidden" name="id_usuario" value="<?= $t['id_usuario'] ?>">
                                                <button type="button" class="action-btn-sm btn-deactivate-sm" title="Desactivar" onclick="confirmarDesactivar(<?= $t['id_usuario'] ?>)"><i class="fa fa-ban"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form id="form-activar-<?= $t['id_usuario'] ?>" action="../../controllers/UsuarioController.php?accion=activar" method="POST" style="margin:0;">
                                                <input type="hidden" name="id_usuario" value="<?= $t['id_usuario'] ?>">
                                                <button type="button" class="action-btn-sm btn-activate-sm" title="Activar" onclick="confirmarActivar(<?= $t['id_usuario'] ?>)"><i class="fa fa-check"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <form id="form-eliminar-<?= $t['id_usuario'] ?>" action="../../controllers/UsuarioController.php?accion=eliminar" method="POST" style="margin:0;">
                                            <input type="hidden" name="id_usuario" value="<?= $t['id_usuario'] ?>">
                                            <button type="button" class="action-btn-sm btn-delete-sm" onclick="confirmarEliminar(<?= $t['id_usuario'] ?>)"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modals (Copied from previous admin.php implementation) -->
    <div id="modalAgregar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <img src="../../img/logo.png" alt="Logo" onerror="this.style.display='none'">
                <div class="header-info">
                    <h3>Registrar Usuario</h3>
                    <p>Sistema de Marcaciones — Lagricola</p>
                </div>
                <button class="close-modal" onclick="closeModal('modalAgregar')">&times;</button>
            </div>
            <form action="../../controllers/UsuarioController.php?accion=registrar" method="POST">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group"><label>Nombres *</label><input type="text" name="nombres" placeholder="Nombres" required></div>
                        <div class="form-group"><label>Apellidos *</label><input type="text" name="apellidos" placeholder="Apellidos" required></div>
                    </div>
                    <div class="form-group"><label>Correo Electrónico *</label><input type="email" name="email" placeholder="correo@ejemplo.com" required></div>
                    <div class="form-grid">
                        <div class="form-group"><label>Contraseña *</label><input type="password" name="password" placeholder="Mín. 6 caracteres" required></div>
                        <div class="form-group"><label>Confirmar Contraseña *</label><input type="password" name="confirmar_password" placeholder="Repetir contraseña" required></div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Rol *</label>
                            <select name="rol" required>
                                <option value="">-- Seleccione --</option>
                                <option value="administrador">Administrador</option>
                                <option value="trabajador">Trabajador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cargo *</label>
                            <select name="id_cargo" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($cargos as $c): ?>
                                    <option value="<?= $c['id_cargo'] ?>"><?= $c['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" placeholder="Número de teléfono"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalAgregar')">Cancelar</button>
                    <button type="submit" class="btn-save-green"><i class="fa fa-user-plus"></i> Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <img src="../../img/logo.png" alt="Logo" onerror="this.style.display='none'">
                <div class="header-info">
                    <h3>Editar Usuario</h3>
                    <p>Actualizar información del personal</p>
                </div>
                <button class="close-modal" onclick="closeModal('modalEditar')">&times;</button>
            </div>
            <form action="../../controllers/UsuarioController.php?accion=actualizar" method="POST">
                <input type="hidden" name="id_usuario" id="edit_id">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group"><label>Nombres *</label><input type="text" name="nombres" id="edit_nombres" required></div>
                        <div class="form-group"><label>Apellidos *</label><input type="text" name="apellidos" id="edit_apellidos" required></div>
                    </div>
                    <div class="form-group"><label>Correo Electrónico *</label><input type="email" name="email" id="edit_email" required></div>
                    <div class="form-group"><label>Nueva Contraseña (vacío para no cambiar)</label><input type="password" name="password"></div>
                    <div class="form-grid">
                        <div class="form-group"><label>Rol *</label><select name="rol" id="edit_rol" required><option value="administrador">Administrador</option><option value="trabajador">Trabajador</option></select></div>
                        <div class="form-group"><label>Cargo *</label><select name="id_cargo" id="edit_cargo" required><?php foreach ($cargos as $c): ?><option value="<?= $c['id_cargo'] ?>"><?= $c['nombre'] ?></option><?php endforeach; ?></select></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-save-green"><i class="fa fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        function openEditModal(user) {
            document.getElementById('edit_id').value = user.id_usuario;
            document.getElementById('edit_nombres').value = user.nombres;
            document.getElementById('edit_apellidos').value = user.apellidos;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_rol').value = user.rol;
            document.getElementById('edit_cargo').value = user.id_cargo;
            openModal('modalEditar');
        }

        function refrescarTabla() {
            const q = document.getElementById("buscar_usuario").value.toLowerCase();
            const limite = parseInt(document.getElementById("cant_registros").value);
            const filas = document.querySelectorAll("#tablaUsuarios tbody tr");
            let visibles = 0;

            filas.forEach(tr => {
                const texto = tr.textContent.toLowerCase();
                const cumpleFiltro = texto.includes(q);
                
                if (cumpleFiltro && visibles < limite) {
                    tr.style.display = "";
                    visibles++;
                } else {
                    tr.style.display = "none";
                }
            });
        }

        function filtrarTabla() {
            refrescarTabla();
        }

        function confirmarDesactivar(id) {
            Swal.fire({ title: '¿Desactivar?', text: "El usuario no podrá entrar al sistema.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#E53935', confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar' }).then((r) => { if (r.isConfirmed) document.getElementById('form-desactivar-' + id).submit(); });
        }
        function confirmarActivar(id) {
            Swal.fire({ title: '¿Activar?', text: "El usuario recuperará el acceso al sistema.", icon: 'question', showCancelButton: true, confirmButtonColor: '#2e7d32', confirmButtonText: 'Sí, activar', cancelButtonText: 'Cancelar' }).then((r) => { if (r.isConfirmed) document.getElementById('form-activar-' + id).submit(); });
        }
        function confirmarEliminar(id) {
            Swal.fire({ title: '¿Eliminar?', text: "Se borrará permanentemente.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar' }).then((r) => { if (r.isConfirmed) document.getElementById('form-eliminar-' + id).submit(); });
        }
        window.onclick = function(e) { if (e.target.className === 'modal') e.target.style.display = 'none'; }
        
        // Ejecutar al cargar para aplicar el límite inicial
        document.addEventListener('DOMContentLoaded', refrescarTabla);
    </script>

    <?php if ($alert && is_array($alert)): ?>
        <script>
            Swal.fire({ icon: "<?= $alert['icon'] ?>", title: "<?= $alert['title'] ?>", text: "<?= $alert['text'] ?>", confirmButtonColor: "#2e7d32" });
        </script>
    <?php endif; ?>

</body>
</html>