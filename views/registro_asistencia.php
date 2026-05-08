<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Marcaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #fafafa;
            color: #333;
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background-color: #dcedc8; /* Verde claro similar a la imagen */
            padding: 15px 30px;
            display: flex;
            align-items: center;
            font-size: 22px;
            font-weight: 500;
            color: #2e7d32;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .topbar i {
            margin-right: 15px;
            background: #fff;
            padding: 8px;
            border-radius: 50%;
            font-size: 18px;
            color: #333;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Main Container */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Top Widgets (Date & Time) */
        .time-widgets {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }

        .widget {
            flex: 1;
            background-color: #ededed;
            border-radius: 20px;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            border: 1px solid #d5d5d5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            max-width: 400px;
        }

        .widget i {
            font-size: 32px;
            margin-right: 20px;
            color: #444;
            background: #fff;
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #ccc;
        }

        .widget-text {
            font-size: 16px;
            color: #666;
            display: flex;
            flex-direction: column;
        }

        .widget-text .value {
            font-size: 20px;
            color: #111;
            font-weight: 700;
            margin-top: 5px;
        }

        /* Main Cards Area */
        .cards-area {
            display: flex;
            gap: 30px;
        }

        /* Card styles */
        .card {
            background-color: #e5e5e5;
            border-radius: 25px;
            padding: 40px;
            border: 1px solid #d5d5d5;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        /* Left Card: Status */
        .status-card {
            flex: 0 0 32%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .status-icon-box {
            background: #fff;
            border-radius: 30px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            min-height: 280px;
        }

        .status-icon-box i.fa-user-clock {
            font-size: 130px;
            color: #222;
        }

        .status-text {
            font-size: 16px;
            color: #444;
            margin-bottom: 15px;
        }

        .status-value {
            font-size: 22px;
            font-weight: 800;
            color: #111;
            text-transform: uppercase;
        }

        /* Right Card: Data */
        .data-card {
            flex: 1;
        }

        .data-header {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
            font-size: 18px;
            font-weight: 500;
            color: #222;
            text-transform: uppercase;
        }

        .data-header-icon {
            font-size: 45px;
            margin-right: 20px;
            color: #607d8b; /* Azul grisáceo */
            background: #fff;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .data-field {
            background: #fff;
            border-radius: 30px;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .data-field i {
            font-size: 20px;
            color: #333;
            width: 35px;
            text-align: center;
            margin-right: 15px;
        }

        .data-field-label {
            font-size: 16px;
            color: #333;
            flex: 1;
        }

        .data-field-value {
            font-size: 16px;
            font-weight: 500;
            color: #555;
        }
        
        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }

        /* Permission Note Styles */
        .permission-note {
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 15px;
            display: none; /* Hidden by default */
            font-weight: 700;
            text-align: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .note-aprobado {
            background-color: #C8E6C9;
            color: #2E7D32;
            border: 2px solid #2E7D32;
        }
        .note-rechazado {
            background-color: #FFCDD2;
            color: #C62828;
            border: 2px solid #C62828;
        }

    </style>
</head>
<body>

    <div class="topbar">
        <i class="fa fa-clock"></i>
        REGISTRO DE MARCACIONES
    </div>

    <div class="container">
        
        <div class="time-widgets">
            <div class="widget">
                <i class="fa fa-calendar-alt"></i>
                <div class="widget-text">
                    Fecha actual
                    <span class="value" id="current-date">--/--/----</span>
                </div>
            </div>
            <div class="widget">
                <i class="fa fa-clock"></i>
                <div class="widget-text">
                    Hora actual
                    <span class="value" id="current-time">--:--:--</span>
                </div>
            </div>
        </div>

        <div class="cards-area">
            
            <!-- Left Card -->
            <div class="card status-card">
                <div class="status-icon-box">
                    <i class="fa fa-user-clock pulse"></i>
                </div>
                <div class="status-text">Estado actual</div>
                <div class="status-value" id="status-display">EN ESPERA O EXITOSA</div>
                
                <!-- Permission Note Placeholder -->
                <div id="permission-note" class="permission-note"></div>
            </div>

            <!-- Right Card -->
            <div class="card data-card">
                <div class="data-header">
                    <div class="data-header-icon">
                        <i class="fa fa-clipboard-user"></i>
                    </div>
                    DATOS MARCACIÓN
                </div>

                <div class="data-field">
                    <i class="fa fa-id-card"></i>
                    <span class="data-field-label">Identificación</span>
                    <span class="data-field-value" id="val-identificacion"></span>
                </div>

                <div class="data-field">
                    <i class="fa fa-user"></i>
                    <span class="data-field-label">Nombre</span>
                    <span class="data-field-value" id="val-nombre"></span>
                </div>

                <div class="data-field">
                    <i class="fa fa-fingerprint"></i>
                    <span class="data-field-label">Control de Acceso</span>
                    <span class="data-field-value" id="val-acceso" style="color:#555; font-style:italic;">
                        <input type="text" id="simular-huella" placeholder="Ingrese documento..." style="border:none; border-bottom:1px solid #ccc; outline:none; font-style:italic; width:150px;">
                    </span>
                </div>

                <div class="data-field">
                    <i class="fa fa-briefcase"></i>
                    <span class="data-field-label">Cargo</span>
                    <span class="data-field-value" id="val-cargo"></span>
                </div>

                <div class="data-field">
                    <i class="fa fa-clock"></i>
                    <span class="data-field-label">Horario</span>
                    <span class="data-field-value" id="val-horario"></span>
                </div>

            </div>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            
            // Format Date for Colombia
            const optionsDate = { 
                timeZone: 'America/Bogota', 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dateString = now.toLocaleDateString('es-CO', optionsDate);
            // Capitalize first letter
            document.getElementById('current-date').textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);

            // Format Time for Colombia
            const optionsTime = {
                timeZone: 'America/Bogota',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true // Opcional: mostrar AM/PM si lo prefieres, pero mantengo el estándar
            };
            const timeString = now.toLocaleTimeString('es-CO', optionsTime);
            document.getElementById('current-time').textContent = timeString;
        }

        // Update immediately and then every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // --- SIMULACIÓN DE HUELLA Y GESTIÓN DE PERMISOS ---
        const inputHuella = document.getElementById('simular-huella');
        const permissionNote = document.getElementById('permission-note');
        
        inputHuella.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const documento = this.value;
                if (!documento) return;

                // Limpiar campos previos
                permissionNote.style.display = 'none';
                permissionNote.className = 'permission-note';
                document.getElementById('status-display').textContent = 'BUSCANDO...';

                // Llamada AJAX para obtener datos del trabajador y su permiso
                fetch(`../controllers/MarcacionController.php?accion=identificar&documento=${documento}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Rellenar datos
                            document.getElementById('val-identificacion').textContent = data.trabajador.documento;
                            document.getElementById('val-nombre').textContent = data.trabajador.nombres + ' ' + data.trabajador.apellidos;
                            document.getElementById('val-cargo').textContent = data.trabajador.cargo;
                            document.getElementById('val-horario').textContent = data.trabajador.horario || 'No asignado';
                            document.getElementById('status-display').textContent = 'EXITOSA';
                            document.getElementById('status-display').style.color = '#2E7D32';

                            // Mostrar NOTA de permiso si existe
                            if (data.permiso) {
                                permissionNote.textContent = `NOTA: permiso ${data.permiso.estado}`;
                                permissionNote.classList.add(`note-${data.permiso.estado}`);
                                permissionNote.style.display = 'block';
                            }
                            
                            // Limpiar input después de 3 segundos
                            setTimeout(() => {
                                // this.value = '';
                                // Opcional: resetear UI
                            }, 3000);
                        } else {
                            document.getElementById('status-display').textContent = 'NO ENCONTRADO';
                            document.getElementById('status-display').style.color = '#C62828';
                            // Limpiar campos
                            document.getElementById('val-identificacion').textContent = '';
                            document.getElementById('val-nombre').textContent = '';
                            document.getElementById('val-cargo').textContent = '';
                            document.getElementById('val-horario').textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('status-display').textContent = 'ERROR';
                    });
            }
        });
    </script>
</body>
</html>
