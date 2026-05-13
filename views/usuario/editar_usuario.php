<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/usuario.php';

$id_usuario = $_GET['id'] ?? null;
if (!$id_usuario) {
    header("Location: ../dashboard/admin.php");
    exit;
}

$db = (new Database())->conectar();
$model = new Usuario($db);
$user_data = $model->obtenerPorId($id_usuario);

if (!$user_data) {
    header("Location: ../dashboard/admin.php");
    exit;
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

try {
  $stmt = $db->prepare("SELECT id_cargo, nombre FROM cargo ORDER BY nombre ASC");
  $stmt->execute();
  $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $cargos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Usuario — Lagricola</title>

  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --g: #2e7d32;
      --gd: #1b5e20;
      --gl: #deeade;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Nunito', sans-serif;
      background: #d6d6d6;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .box {
      background: #fff;
      border-radius: 20px;
      padding: 36px 32px;
      width: 100%;
      max-width: 520px;
      box-shadow: 0 10px 36px rgba(0, 0, 0, .12);
    }

    .logo-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 22px;
    }

    .logo-row img {
      height: 44px;
    }

    h1 {
      font-size: 22px;
      font-weight: 900;
      color: var(--gd);
    }

    h2 {
      font-size: 14px;
      font-weight: 600;
      color: #666;
      margin-bottom: 18px;
    }

    .grid2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .field {
      margin-bottom: 14px;
    }

    .field label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--g);
      margin-bottom: 5px;
    }

    .field input,
    .field select {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid #cde0cd;
      border-radius: 9px;
      font-family: 'Nunito', sans-serif;
      font-size: 13px;
      color: #333;
      outline: none;
      transition: border .2s;
    }

    .field input:focus,
    .field select:focus {
      border-color: var(--g);
      box-shadow: 0 0 0 3px rgba(46, 125, 50, .1);
    }

    .btn {
      width: 100%;
      padding: 13px;
      background: var(--g);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Nunito', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: background .2s;
      margin-top: 6px;
    }

    .btn:hover {
      background: var(--gd);
    }

    .back {
      display: block;
      text-align: center;
      margin-top: 12px;
      font-size: 13px;
      color: #555;
      text-decoration: none;
    }

    .back:hover {
      color: var(--g);
    }

    #campo-trabajador {
      display: <?= $user_data['rol'] === 'trabajador' ? 'block' : 'none' ?>;
    }

    @media (max-width: 600px) {
      .grid2 {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <div class="box">
    <div class="logo-row">
      <img src="../../img/logo.png" alt="Lagricola" onerror="this.style.display='none'" />
      <div>
        <h1>Editar Usuario</h1>
        <h2>Actualizar información de <?= htmlspecialchars($user_data['nombres']) ?></h2>
      </div>
    </div>

    <form action="../../controllers/UsuarioController.php?accion=actualizar" method="POST">
      <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($user_data['id_usuario']) ?>" />

      <div class="grid2">
        <div class="field">
          <label>Nombres *</label>
          <input type="text" name="nombres" value="<?= htmlspecialchars($user_data['nombres']) ?>" required />
        </div>

        <div class="field">
          <label>Apellidos *</label>
          <input type="text" name="apellidos" value="<?= htmlspecialchars($user_data['apellidos']) ?>" required />
        </div>
      </div>

      <div class="field">
        <label>Correo electrónico *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required />
      </div>

      <div class="field">
        <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
        <input type="password" name="password" placeholder="Mín. 6 caracteres" />
      </div>

      <div class="grid2">
        <div class="field">
          <label>Rol *</label>
          <select name="rol" id="rol" onchange="toggleTrabajador()" required>
            <option value="trabajador" selected>Trabajador</option>
          </select>
        </div>

        <div class="field">
          <label>Cargo *</label>
          <select name="id_cargo" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($cargos as $c): ?>
              <?php if (strtolower($c['nombre']) !== 'administrador'): ?>
                <option value="<?= htmlspecialchars($c['id_cargo']) ?>" <?= $user_data['id_cargo'] == $c['id_cargo'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nombre']) ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="field">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($user_data['telefono'] ?? '') ?>" />
      </div>

      <div id="campo-trabajador">
        <div class="grid2">
          <div class="field">
            <label>Documento</label>
            <input type="text" name="documento" value="<?= htmlspecialchars($user_data['documento'] ?? '') ?>" />
          </div>

          <div class="field">
            <label>Fecha de ingreso</label>
            <input type="date" name="fecha_ingreso" value="<?= htmlspecialchars($user_data['fecha_ingreso'] ?? date('Y-m-d')) ?>" />
          </div>
        </div>
      </div>

      <button type="submit" class="btn">
        <i class="fa fa-save"></i> Guardar Cambios
      </button>
    </form>

    <a href="../dashboard/admin.php" class="back">
      <i class="fa fa-arrow-left"></i> Cancelar y Volver
    </a>
  </div>

  <script>
    function toggleTrabajador() {
      const rol = document.getElementById('rol').value;
      const campoTrabajador = document.getElementById('campo-trabajador');
      campoTrabajador.style.display = rol === 'trabajador' ? 'block' : 'none';
    }
  </script>

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
