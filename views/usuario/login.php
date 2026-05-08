<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio Sesión - Lagrícola</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Segoe UI', sans-serif;
            background: #d9d9d9;
            /* FONDO GRIS */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }


        /* CONTENEDOR BLANCO que envuelve formulario + imagen */
        .wrapper {
            background: #ffffff;
            border-radius: 24px;
            padding: 36px 32px;
            display: flex;
            align-items: center;
            gap: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }


        /* ===== IZQUIERDA ===== */
        .card {
            background: #deeade;
            /* VERDE CLARO */
            border-radius: 20px;
            padding: 32px 28px;
            width: 290px;
        }


        .admin-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }


        .admin-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #b8d4b8;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .admin-icon svg {
            fill: #2e7d32;
            width: 24px;
            height: 24px;
        }


        .admin-label span {
            font-size: 14px;
            font-weight: 600;
            color: #2e7d32;
        }


        h1 {
            font-size: 28px;
            font-weight: 900;
            color: #1b5e20;
            margin-bottom: 20px;
        }


        .input-group {
            position: relative;
            margin-bottom: 11px;
        }


        .input-group svg {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            fill: #7a9e7a;
            width: 17px;
            height: 17px;
        }


        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 42px;
            border: 1.5px solid #b8d4b8;
            border-radius: 10px;
            background: #ffffff;
            font-size: 13px;
            color: #333;
            outline: none;
            transition: border 0.2s, box-shadow 0.2s;
        }


        .input-group input:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }


        .input-group input::placeholder {
            color: #aaa;
        }


        .forgot {
            text-align: right;
            font-size: 12px;
            color: #555;
            margin-top: 4px;
            margin-bottom: 12px;
            cursor: pointer;
        }


        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #333;
            margin-bottom: 18px;
        }


        .remember input[type="checkbox"] {
            accent-color: #2e7d32;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }


        .btn-login {
            width: 100%;
            padding: 13px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }


        .btn-login:hover {
            background: #1b5e20;
            transform: translateY(-1px);
        }


        .access-note {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 10px;
        }


        /* ===== DERECHA: IMAGEN ===== */
        .logo-box {
            width: 270px;
            height: 270px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }


        .imagen img {
            border-radius: 20px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
            border: 3px solid #b8d4b8;
        }




        @media (max-width: 680px) {
            .wrapper {
                flex-direction: column;
                padding: 24px;
            }


            .logo-box {
                width: 100%;
                height: 200px;
            }


            .card {
                width: 100%;
            }
        }
    </style>
</head>


<body>
    <div class="wrapper">


        <!-- IZQUIERDA -->
        <div class="card">
            <div class="admin-label">
                <div class="admin-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                    </svg>
                </div>
                <span>Administrador</span>
            </div>


            <h1>INICIO SESIÓN</h1>

            <form action="../../controllers/AuthController.php" method="POST">
                <div class="input-group">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z" />
                    </svg>
                    <input type="email" name="email" placeholder="Correo electrónico" required />
                </div>

                <div class="input-group">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.8-2.2-5-5-5S7 3.2 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.7 1.4-3.1 3.1-3.1 1.7 0 3.1 1.4 3.1 3.1v2z" />
                    </svg>
                    <input type="password" name="password" placeholder="Contraseña" required />
                </div>

                <p class="forgot" onclick="showRecovery()">¿Olvidaste tu contraseña?</p>

                <label class="remember">
                    <input type="checkbox" name="remember" checked />
                    Recordar contraseña
                </label>

                <button type="submit" class="btn-login">
                    Iniciar Sesión
                </button>
            </form>
            <p class="access-note">Acceso exclusivo para administrador</p>
        </div>


        <!-- DERECHA: IMAGEN -->
        <div class="imagen">
            <img src="../../img/inicio.png" alt="Imagen" width="300">
        </div>
    </div>

    <!-- SweetAlert2 for displaying alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function showRecovery() {
            const { value: email } = await Swal.fire({
                title: 'Recuperar contraseña',
                text: 'Se generará una contraseña temporal para su cuenta.',
                input: 'email',
                inputLabel: 'Ingrese su correo electrónico',
                inputPlaceholder: 'ejemplo@correo.com',
                showCancelButton: true,
                confirmButtonText: 'Recuperar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2e7d32'
            });

            if (email) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../controllers/AuthController.php?accion=recuperar';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'email_recovery';
                input.value = email;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['icon'] ?>',
                title: '<?= $_SESSION['alert']['title'] ?>',
                html: '<?= $_SESSION['alert']['text'] ?>',
                confirmButtonColor: '#2e7d32'
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</body>

</html>