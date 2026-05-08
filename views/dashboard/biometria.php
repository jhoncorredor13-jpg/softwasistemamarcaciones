<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Huella Biométrica</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background: #dfeee2; display: flex; flex-direction: column; height: 100vh; }
        .topbar { height: 48px; background: #dfe8df; display: flex; align-items: center; justify-content: space-between; padding: 0 28px 0 20px; }
        .main-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { width: 220px; background-color: #52A65A; display: flex; flex-direction: column; padding-top: 20px; overflow-y: auto; }
        .sidebar-item { display: flex; align-items: center; padding: 18px 20px; color: #fff; text-decoration: none; font-size: 14px; gap: 15px; }
        .sidebar-item:hover { background-color: rgba(0, 0, 0, 0.05); }
        .content { flex: 1; padding: 30px 40px; overflow-y: auto; background: transparent; }
        .card { background-color: #fff; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); margin-top: 40px; }
        .card i { font-size: 60px; color: #607D8B; margin-bottom: 20px; }
        .card h2 { font-size: 24px; color: #333; }
        .card p { font-size: 16px; color: #666; }
    </style>
</head>
<body>
    <div class="topbar">
        <div style="font-weight: bold; color: #2e7d32;">Panel Administrador</div>
        <a href="admin.php" style="color: #333; text-decoration: none;"><i class="fa fa-arrow-left"></i> Volver al Inicio</a>
    </div>
    <div class="main-container">
        <nav class="sidebar">
            <a href="admin.php" class="sidebar-item"><i class="fa fa-home"></i> Inicio</a>
        </nav>
        <main class="content">
            <div class="card">
                <i class="fa fa-fingerprint"></i>
                <h2>Módulo de Huella Biométrica</h2>
                <p>Esta sección está actualmente en construcción. Aquí se realizará la conexión y enrolamiento del sensor biométrico para cada trabajador.</p>
            </div>
        </main>
    </div>
</body>
</html>
