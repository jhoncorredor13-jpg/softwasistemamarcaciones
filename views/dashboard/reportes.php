<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}

require_once __DIR__ . '/../../controllers/ReporteController.php';
$controller = new ReporteController();
$trabajadores = $controller->getAllTrabajadores();

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes por defecto
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t'); // Último día del mes por defecto
$id_trabajador = $_GET['id_trabajador'] ?? '';

$reporte = $controller->generarReporte($fecha_inicio, $fecha_fin, $id_trabajador);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Panel Administrador</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        .card { background-color: #fff; border-radius: 20px; padding: 25px 40px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02); }
        .card-title { font-size: 18px; font-weight: 500; margin-bottom: 15px; color: #111; display:flex; justify-content: space-between; align-items: center;}
        
        /* Filters */
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background: #f9f9f9; padding: 20px; border-radius: 10px; border: 1px solid #eee; margin-bottom: 20px;}
        .form-group { display: flex; flex-direction: column; gap: 5px; min-width: 200px;}
        .form-group label { font-size: 13px; font-weight: 600; color: #444; }
        .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; }
        .form-group input:focus, .form-group select:focus { border-color: #2e7d32; }
        .btn-submit { background: #2e7d32; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; height: 40px; display: flex; align-items: center; gap: 8px; }
        .btn-submit:hover { background: #1b5e20; }

        /* Export buttons */
        .btn-export-pdf { background: #E91E63; color: #fff; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .btn-export-excel { background: #4CAF50; color: #fff; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin-left: 10px;}
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #FFC107; color: #333; font-weight: 600; }
        tbody tr:hover { background-color: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; background: #e0e0e0; color: #333; }
        
        .estado-asistio { background: #c8e6c9; color: #2e7d32; }
        .estado-falto { background: #ffcdd2; color: #c62828; }
        .estado-permiso { background: #bbdefb; color: #1565c0; }
        .estado-pendiente { background: #f5f5f5; color: #757575; }

        .empty { text-align: center; padding: 40px; color: #777; font-size: 15px; }

        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            .content, .content * { visibility: visible; }
            .content { position: absolute; left: 0; top: 0; width: 100%; padding: 0; }
            .filter-form, .btn-export-pdf, .btn-export-excel { display: none !important; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .page-title { display: block; text-align: center; }
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
            <a href="reportes.php" class="sidebar-item active"><i class="fa fa-file-pdf"></i> Reportes</a>
            <div style="border-bottom: 1px solid rgba(255,255,255,0.2); margin: 10px 20px;"></div>
            <a href="../../controllers/AuthController.php?accion=logout" class="sidebar-item" style="color:#ffcdd2"><i class="fa fa-sign-out-alt"></i> Salir</a>
        </nav>

        <main class="content" id="reporte-area">
            <h1 class="page-title">Módulo de Reportes e Historiales</h1>
            <div class="divider"></div>

            <div class="card">
                <form method="GET" action="reportes.php" class="filter-form">
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Trabajador (Opcional)</label>
                        <select name="id_trabajador">
                            <option value="">-- Todos los trabajadores --</option>
                            <?php foreach ($trabajadores as $t): ?>
                                <option value="<?= $t['id_trabajador'] ?>" <?= $id_trabajador == $t['id_trabajador'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombres'] . ' ' . $t['apellidos']) ?> (<?= htmlspecialchars($t['documento']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa fa-search"></i> Generar</button>
                </form>

                <div class="card-title">
                    <span>Resultados del Reporte</span>
                    <div>
                        <button onclick="window.print()" class="btn-export-pdf"><i class="fa fa-file-pdf"></i> Imprimir / PDF</button>
                        <button onclick="exportTableToCSV('reporte_asistencia.csv')" class="btn-export-excel"><i class="fa fa-file-excel"></i> Exportar Excel</button>
                    </div>
                </div>

                <?php if (empty($reporte)): ?>
                    <div class="empty">
                        <i class="fa fa-folder-open fa-3x" style="margin-bottom:12px;display:block;color:#ccc;"></i>
                        No se encontraron registros para los filtros seleccionados.
                    </div>
                <?php else: ?>
                    <table id="tabla-reporte">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Trabajador</th>
                                <th>Turno Asignado</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte as $r): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($r['nombres'] . ' ' . $r['apellidos']) ?></td>
                                    <td><?= date('H:i', strtotime($r['turno_entrada'])) ?> - <?= date('H:i', strtotime($r['turno_salida'])) ?></td>
                                    <td><?= $r['marca_entrada'] ? date('H:i', strtotime($r['marca_entrada'])) : '--:--' ?></td>
                                    <td><?= $r['marca_salida'] ? date('H:i', strtotime($r['marca_salida'])) : '--:--' ?></td>
                                    <td>
                                        <?php
                                            $clase = 'estado-pendiente';
                                            if ($r['estado_final'] === 'Asistió') $clase = 'estado-asistio';
                                            if ($r['estado_final'] === 'Faltó') $clase = 'estado-falto';
                                            if ($r['estado_final'] === 'Permiso') $clase = 'estado-permiso';
                                        ?>
                                        <span class="badge <?= $clase ?>"><?= $r['estado_final'] ?></span>
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
        function downloadCSV(csv, filename) {
            var csvFile;
            var downloadLink;
            // CSV file
            csvFile = new Blob(["\uFEFF"+csv], {type: "text/csv;charset=utf-8;"});
            // Download link
            downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }

        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table tr");
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (var j = 0; j < cols.length; j++) {
                    // Limpiar el texto
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ");
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(","));
            }
            downloadCSV(csv.join("\n"), filename);
        }
    </script>
</body>
</html>
