# Guia de Diagnostico de Fallos — Lagricola
Sistema de Marcaciones Agropecuarias

Este documento describe que puede romperse en cada modulo, por que se rompe (logica exacta del codigo) y como repararlo.

---

## INDICE RAPIDO

| Si se daño... | Ir a seccion |
|---|---|
| No puedo iniciar sesion | Seccion 1 |
| No aparecen usuarios en la lista | Seccion 2 |
| No registra entrada o salida | Seccion 3 |
| No se asignan turnos | Seccion 4 |
| No se guardan los permisos | Seccion 5 |
| El reporte sale vacio | Seccion 6 |
| No aparecen notificaciones | Seccion 7 |
| El kiosko no encuentra al trabajador | Seccion 8 |
| Pantalla en blanco en cualquier vista | Seccion 9 |

---

## SECCION 1 — SE DAÑO: AUTENTICACION (Login)

### Archivo responsable
controllers/AuthController.php
models/usuario.php

### Como funciona (logica normal)
1. El formulario login.php envia POST a AuthController.php con email y password
2. El controlador valida que los campos no esten vacios
3. Valida el formato del email con filter_var()
4. Llama a Usuario->obtenerPorEmail($email) que ejecuta:
   SELECT id_usuario, nombres, apellidos, email, password_hash, rol, activo
   FROM usuario WHERE email = :email AND activo = 1 LIMIT 1
5. Si encuentra el usuario, verifica la contrasena con password_verify($password, $usuario[password_hash])
6. Si todo es correcto, guarda la sesion y redirige segun el rol

### Fallo 1 — Pagina en blanco o redirige al login sin mensaje
CAUSA: El campo activo del usuario en la base de datos esta en 0.
La query tiene AND activo = 1, entonces no devuelve el usuario aunque la contrasena sea correcta.
SOLUCION: Ejecutar en MySQL:
UPDATE usuario SET activo = 1 WHERE email = 'correo@ejemplo.com';

### Fallo 2 — Error: Contrasena incorrecta aunque sea la correcta
CAUSA: El campo password_hash en la tabla usuario fue editado directamente en phpMyAdmin
y se guardo el texto plano en lugar del hash bcrypt.
password_verify() compara contra un hash bcrypt que empieza con $2y$10$...
Si el campo tiene texto plano, la verificacion siempre falla.
SOLUCION: Usar la recuperacion de contrasena desde el login, o ejecutar en MySQL:
UPDATE usuario SET password_hash = '$2y$10$HASH_GENERADO' WHERE email = 'correo@ejemplo.com';
Para generar el hash correcto usar PHP: echo password_hash('nueva_contrasena', PASSWORD_DEFAULT);

### Fallo 3 — Error: Rol no valido, no redirige al dashboard
CAUSA: El campo rol en la tabla usuario tiene un valor diferente a administrador o trabajador.
El switch en AuthController solo maneja esos dos casos exactos (en minuscula).
Si el valor es Administrador (con mayuscula) o admin, cae en el default y muestra error.
SOLUCION: Verificar el valor exacto en la BD:
SELECT rol FROM usuario WHERE email = 'correo@ejemplo.com';
Corregir si es necesario:
UPDATE usuario SET rol = 'administrador' WHERE email = 'correo@ejemplo.com';

### Fallo 4 — Error de conexion al intentar login
CAUSA: La clase Database en config/database.php tiene el puerto incorrecto.
El puerto configurado es 3320 (Laragon personalizado). Si MySQL corre en el puerto 3306 estandar, la conexion falla.
SOLUCION: Abrir config/database.php y cambiar:
private $port = "3320";  →  private $port = "3306";
O verificar el puerto real en el panel de Laragon.

### Fallo 5 — Recuperacion de contrasena no funciona
CAUSA: El metodo recuperar() busca el usuario con obtenerPorEmail() que tiene AND activo = 1.
Si el usuario esta desactivado, no lo encuentra y muestra Correo no encontrado aunque exista.
SOLUCION: Activar el usuario primero:
UPDATE usuario SET activo = 1 WHERE email = 'correo@ejemplo.com';

---

## SECCION 2 — SE DAÑO: GESTION DE USUARIOS

### Archivos responsables
controllers/UsuarioController.php
models/usuario.php
views/dashboard/lista_usuario.php

### Como funciona (logica normal)
1. lista_usuario.php llama a Usuario->listarTrabajadores() que ejecuta un SELECT con JOIN entre usuario, trabajador y cargo
2. Solo muestra usuarios con rol = trabajador
3. El formulario de agregar envia POST a UsuarioController.php?accion=registrar
4. El controlador valida campos, verifica que el email no exista, hashea la contrasena
5. Llama a Usuario->registrar($datos) que abre una TRANSACCION:
   - INSERT en tabla usuario
   - Si rol = trabajador, INSERT en tabla trabajador
   - Si algo falla, hace ROLLBACK

### Fallo 1 — La lista de usuarios aparece vacia
CAUSA A: La query de listarTrabajadores() filtra WHERE u.rol = trabajador.
Si todos los usuarios tienen rol = administrador, la lista sale vacia.
CAUSA B: El JOIN con la tabla trabajador usa LEFT JOIN, pero si se borro un registro de trabajador
sin borrar el usuario, el usuario aparece con campos nulos pero si aparece en la lista.
SOLUCION: Verificar en BD:
SELECT u.email, u.rol FROM usuario u;

### Fallo 2 — Al registrar usuario sale Error al registrar con mensaje de BD
CAUSA MAS COMUN: El email ya existe. La tabla usuario tiene UNIQUE KEY en el campo email.
Si se intenta insertar un email duplicado, MySQL lanza una excepcion que el metodo registrar() captura
y retorna el mensaje de error como string.
SOLUCION: Usar un email diferente o verificar:
SELECT email FROM usuario WHERE email = 'correo@ejemplo.com';

### Fallo 3 — Se registra el usuario pero no aparece en la lista
CAUSA: El usuario se creo con rol = administrador en lugar de trabajador.
La query listarTrabajadores() solo muestra WHERE u.rol = trabajador.
SOLUCION: Cambiar el rol en BD:
UPDATE usuario SET rol = 'trabajador' WHERE email = 'correo@ejemplo.com';

### Fallo 4 — Al editar usuario, los cambios no se guardan
CAUSA: El metodo actualizar() en el modelo usa una TRANSACCION.
Si el email nuevo ya existe en otro usuario, el UPDATE falla por la restriccion UNIQUE KEY
y hace ROLLBACK, deshaciendo todos los cambios.
SOLUCION: Usar un email que no este registrado en otro usuario.

### Fallo 5 — No se puede eliminar un usuario
CAUSA: La tabla trabajador tiene FOREIGN KEY hacia usuario con ON DELETE CASCADE.
Pero la tabla turno tiene FOREIGN KEY hacia trabajador con ON DELETE CASCADE.
Si el trabajador tiene registros en registro_entrada o registro_salida, la eliminacion puede fallar
si las FK no estan configuradas correctamente en la BD.
SOLUCION: Verificar las FK en la BD o desactivar al usuario en lugar de eliminarlo.

---

## SECCION 3 — SE DAÑO: MARCACIONES (Entrada y Salida)

### Archivo responsable
controllers/MarcacionController.php
views/dashboard/trabajador.php

### Como funciona (logica normal)
1. El panel trabajador.php muestra los botones de entrada/salida
2. Al hacer clic, envia POST a MarcacionController.php con id_trabajador y accion (entrada o salida)
3. El controlador busca el turno del dia: SELECT * FROM turno WHERE id_trabajador = ? AND fecha = hoy
4. Si no hay turno, crea uno automaticamente con INSERT INTO turno (id_trabajador, fecha)
5. Para ENTRADA: verifica que no exista ya en registro_entrada, luego inserta
6. Para SALIDA: busca el registro_entrada, verifica que no exista ya en registro_salida, luego inserta

### Fallo 1 — El boton de entrada no aparece o aparece deshabilitado
CAUSA: La vista trabajador.php consulta el turno de hoy con un JOIN que incluye registro_salida.
Si la columna registro_salida.registro_entrada (FK) tiene un nombre diferente al esperado,
el JOIN falla silenciosamente y $turnoHoy queda null, mostrando el boton de entrada.
Pero si $trabajador es null (el usuario no tiene registro en la tabla trabajador), los botones
no aparecen porque el formulario usa $trabajador[id_trabajador] que seria vacio.
SOLUCION: Verificar que el usuario tenga registro en la tabla trabajador:
SELECT * FROM trabajador WHERE id_usuario = ID_DEL_USUARIO;

### Fallo 2 — Registra entrada pero al recargar sigue mostrando el boton de entrada
CAUSA: La query en trabajador.php para obtener el turno de hoy usa un JOIN con registro_salida
que referencia la columna rs.registro_entrada. Si esa columna se renombro a id_registro_entrada
en la BD, el JOIN falla y $turnoHoy[hora_entrada] siempre es null.
SOLUCION: Verificar el nombre exacto de la columna FK en registro_salida:
DESCRIBE registro_salida;
La columna debe llamarse id_registro_entrada para que el JOIN funcione.

### Fallo 3 — Error en el sistema al marcar (sin mensaje especifico)
CAUSA: El bloque catch del controlador captura cualquier excepcion pero solo muestra
Error en el sistema sin el detalle. Esto ocurre cuando:
- La tabla turno no existe o tiene columnas renombradas
- La tabla registro_entrada no existe
- El id_trabajador enviado en el POST no existe en la BD
SOLUCION: Activar errores en PHP temporalmente para ver el error real.
Agregar al inicio de MarcacionController.php:
error_reporting(E_ALL); ini_set('display_errors', 1);

### Fallo 4 — El trabajador puede marcar entrada dos veces
CAUSA: La validacion verifica con SELECT * FROM registro_entrada WHERE id_turno = ?
Si el id_turno es diferente cada vez (porque se creo un turno nuevo en lugar de reusar el existente),
la validacion no detecta la entrada anterior.
Esto pasa si la logica de buscar el turno del dia falla y siempre crea uno nuevo.
SOLUCION: Verificar que no haya turnos duplicados para el mismo trabajador y fecha:
SELECT * FROM turno WHERE id_trabajador = X AND fecha = HOY;

---

## SECCION 4 — SE DAÑO: TURNOS ROTATIVOS

### Archivos responsables
controllers/TurnoController.php
models/turno.php
views/dashboard/turnos.php

### Como funciona (logica normal)
1. El formulario envia POST a TurnoController.php?accion=asignar con id_trabajador, tipo_turno, fecha_inicio, fecha_fin
2. El controlador determina horaEntrada y horaSalida segun el tipo_turno con un switch
3. Crea un DatePeriod que itera dia a dia entre fecha_inicio y fecha_fin
4. Para cada dia llama a Turno->asignarTurnoDia() que:
   - DELETE FROM turno WHERE id_trabajador = ? AND fecha = ?  (borra el turno existente si hay)
   - INSERT INTO turno (id_trabajador, fecha, horaEntrada, horaSalida)
5. La vista muestra los turnos agrupados por consecutividad

### Fallo 1 — Se asignan 0 turnos aunque el formulario se envia correctamente
CAUSA A: El tipo_turno enviado no coincide con ninguno de los case del switch.
Los valores validos son exactamente: manana, tarde, noche, oficina (sin tildes, en minuscula).
Si el select del formulario tiene un value diferente (ej: mañana con tilde), el switch no lo reconoce
y horaEntrada y horaSalida quedan en 00:00:00.
CAUSA B: fecha_inicio > fecha_fin. El controlador valida esto y redirige con error.
SOLUCION: Verificar los values del select en turnos.php:
<option value="manana"> debe ser exactamente manana sin tilde.

### Fallo 2 — Los turnos no aparecen en la lista despues de asignar
CAUSA: La lista usa getTurnosFuturos() que filtra WHERE t.fecha >= CURDATE().
Si se asignaron turnos con fechas pasadas (fecha_inicio anterior a hoy), no aparecen en la lista.
SOLUCION: Asignar turnos con fechas desde hoy en adelante.
Para ver todos los turnos incluyendo pasados, modificar la query en turno.php:
WHERE t.fecha >= CURDATE()  →  quitar el filtro de fecha

### Fallo 3 — Los turnos aparecen como filas separadas en lugar de agrupados
CAUSA: El metodo indexAdminAgrupados() agrupa turnos consecutivos del mismo trabajador
con el mismo horario. Si hay un dia sin turno en el medio del rango, rompe la consecutividad
y aparecen como grupos separados. Esto es comportamiento esperado, no un error.
CAUSA DE ERROR REAL: Si horaEntrada o horaSalida son null en la BD, la comparacion
$currentGroup[horaEntrada] == $horaEntrada falla y nunca agrupa.
SOLUCION: Verificar que los turnos tengan horaEntrada y horaSalida no nulos:
SELECT * FROM turno WHERE horaEntrada IS NULL;

### Fallo 4 — No se puede eliminar un turno
CAUSA: La tabla registro_entrada tiene FK hacia turno con ON DELETE CASCADE.
Si el turno tiene registros de entrada asociados, al eliminar el turno se eliminan en cascada
los registros de entrada y salida. Si la FK no tiene CASCADE configurado, la eliminacion falla.
SOLUCION: Verificar la configuracion de FK:
SHOW CREATE TABLE registro_entrada;
La FK debe tener ON DELETE CASCADE.

---

## SECCION 5 — SE DAÑO: PERMISOS Y AUSENCIAS

### Archivos responsables
controllers/PermisoController.php
models/permiso.php
views/dashboard/permisos.php
views/dashboard/trabajador.php

### Como funciona (logica normal)
1. El trabajador llena el formulario en trabajador.php y envia POST a PermisoController.php?accion=solicitar
2. El controlador asigna los valores a las propiedades del objeto Permiso y llama a crearPermiso()
3. crearPermiso() ejecuta INSERT INTO permiso (id_trabajador, fecha_inicio, fecha_fin, motivo, estado)
   con estado = pendiente
4. El admin ve la lista en permisos.php que llama a getAllPermisos()
5. Al aprobar/rechazar, envia POST a PermisoController.php?accion=cambiarEstado
6. actualizarEstado() ejecuta UPDATE permiso SET estado = :estado WHERE id_permiso = :id_permiso

### Fallo 1 — El formulario de solicitud no guarda el permiso
CAUSA MAS COMUN: El campo id_trabajador en el formulario esta vacio.
En trabajador.php, el input hidden tiene value="<?= $trabajador[id_trabajador] ?? '' ?>".
Si $trabajador es null (el usuario no tiene registro en la tabla trabajador), el id queda vacio.
El controlador valida empty($id_trabajador) y redirige con error Campos incompletos.
SOLUCION: Verificar que el usuario tenga registro en trabajador:
SELECT * FROM trabajador WHERE id_usuario = ID_DEL_USUARIO;

### Fallo 2 — Los permisos no aparecen en la lista del administrador
CAUSA: getAllPermisos() usa INNER JOIN con trabajador y usuario.
Si un permiso tiene un id_trabajador que no existe en la tabla trabajador (registro huerfano),
el JOIN no lo devuelve y el permiso no aparece.
SOLUCION: Verificar integridad:
SELECT p.id_permiso FROM permiso p LEFT JOIN trabajador t ON p.id_trabajador = t.id_trabajador WHERE t.id_trabajador IS NULL;

### Fallo 3 — Los botones de aprobar/rechazar no aparecen
CAUSA: Los botones solo se muestran cuando $p[estado] === pendiente.
Si el permiso ya fue procesado (aprobado o rechazado), los botones se ocultan y aparece Procesado.
Esto es comportamiento esperado.
CAUSA DE ERROR REAL: Si el campo estado en la BD tiene un valor diferente a pendiente
(ej: Pendiente con mayuscula), la comparacion falla y no muestra los botones.
SOLUCION: Verificar el valor exacto:
SELECT estado FROM permiso WHERE id_permiso = X;
Corregir si es necesario:
UPDATE permiso SET estado = 'pendiente' WHERE id_permiso = X;

### Fallo 4 — Al aprobar un permiso, el estado no cambia
CAUSA: actualizarEstado() en el modelo usa htmlspecialchars(strip_tags()) sobre $this->estado.
Si el valor de estado llega como aprobado, htmlspecialchars no lo modifica.
Pero si el campo id_permiso llega vacio o como 0, el WHERE no encuentra el registro y el UPDATE
afecta 0 filas, retornando true (PDO execute() retorna true aunque no afecte filas).
SOLUCION: Verificar que el input hidden id_permiso en el formulario tenga el valor correcto:
<input type="hidden" name="id_permiso" value="<?= $p[id_permiso] ?>">

---

## SECCION 6 — SE DAÑO: REPORTES

### Archivos responsables
controllers/ReporteController.php
views/dashboard/reportes.php

### Como funciona (logica normal)
1. reportes.php carga con fecha_inicio = primer dia del mes y fecha_fin = ultimo dia del mes por defecto
2. Llama a ReporteController->generarReporte($fecha_inicio, $fecha_fin, $id_trabajador)
3. La query hace JOIN entre turno, trabajador, usuario y LEFT JOIN con registro_entrada, registro_salida y permiso
4. Despues de obtener los resultados, un foreach calcula el estado_final de cada fila:
   - Si permiso_estado = aprobado → Permiso
   - Si marca_entrada != null → Asistio
   - Si fecha < hoy → Falto
   - Si no → Pendiente
5. La vista muestra la tabla con los resultados

### Fallo 1 — El reporte sale completamente vacio
CAUSA A: No hay registros en la tabla turno para el rango de fechas seleccionado.
La query usa INNER JOIN con turno, entonces si no hay turnos asignados, no hay filas.
CAUSA B: Las fechas fecha_inicio y fecha_fin llegaron en formato incorrecto (ej: DD/MM/YYYY en lugar de YYYY-MM-DD).
El BETWEEN de MySQL necesita formato YYYY-MM-DD.
SOLUCION: Verificar que existan turnos en el rango:
SELECT COUNT(*) FROM turno WHERE fecha BETWEEN '2026-05-01' AND '2026-05-31';

### Fallo 2 — Todos los registros aparecen como Falto aunque el trabajador si marco
CAUSA: El LEFT JOIN con registro_entrada usa ON t.id_turno = re.id_turno.
Si el turno que aparece en la tabla turno es diferente al turno con el que se registro la entrada
(porque se creo un turno automatico al marcar y hay dos turnos para el mismo dia),
el JOIN no conecta la entrada con el turno del reporte y marca_entrada queda null.
SOLUCION: Verificar si hay turnos duplicados para el mismo trabajador y fecha:
SELECT id_trabajador, fecha, COUNT(*) FROM turno GROUP BY id_trabajador, fecha HAVING COUNT(*) > 1;

### Fallo 3 — El estado aparece como Permiso aunque el permiso fue rechazado
CAUSA: La query del reporte filtra el permiso con AND p.estado = aprobado en el LEFT JOIN.
Solo une permisos aprobados. Si el estado es rechazado, p.estado queda null y no afecta el estado_final.
Esto es comportamiento correcto. Si aparece Permiso con un permiso rechazado, significa que
hay otro permiso aprobado para ese trabajador en esas fechas.
SOLUCION: Verificar en BD:
SELECT * FROM permiso WHERE id_trabajador = X AND estado = 'aprobado';

### Fallo 4 — La exportacion a Excel (CSV) genera caracteres raros
CAUSA: El archivo CSV se genera con BOM UTF-8 (\uFEFF) para compatibilidad con Excel.
Si Excel no reconoce el BOM, los caracteres con tilde o enie aparecen mal.
SOLUCION: Abrir el CSV con Excel usando Datos → Desde texto/CSV y seleccionar codificacion UTF-8.

### Fallo 5 — El boton Imprimir/PDF no imprime la tabla
CAUSA: Los estilos @media print en reportes.php ocultan todo excepto .content.
Si la tabla esta dentro de un elemento con clase diferente, no aparece en la impresion.
SOLUCION: Verificar que la tabla este dentro del div con id="reporte-area" o clase content.

---

## SECCION 7 — SE DAÑO: NOTIFICACIONES

### Archivo responsable
controllers/NotificacionController.php
views/dashboard/notificaciones.php

### Como funciona (logica normal)
1. notificaciones.php instancia NotificacionController y llama a getNotificaciones()
2. El metodo ejecuta dos queries:
   QUERY 1: Busca permisos con estado = pendiente
   QUERY 2: Busca todos los turnos de hoy con LEFT JOIN a registro_entrada
3. Para cada turno de hoy:
   - Si marco_entrada es null Y hora_actual > horaEntrada del turno → alerta de inasistencia
   - Si marco_entrada no es null Y (hora_real - hora_turno) > 300 segundos → alerta de llegada tarde
4. Ordena todas las notificaciones por fecha descendente con usort()

### Fallo 1 — No aparecen notificaciones aunque hay permisos pendientes
CAUSA: La query de permisos usa JOIN con trabajador y usuario.
Si un permiso tiene id_trabajador que no existe en trabajador, el JOIN no lo devuelve.
SOLUCION: Verificar:
SELECT p.id_permiso, t.id_trabajador FROM permiso p LEFT JOIN trabajador t ON p.id_trabajador = t.id_trabajador WHERE p.estado = 'pendiente';

### Fallo 2 — No aparecen alertas de inasistencia aunque el trabajador no marco
CAUSA A: El turno del trabajador no tiene horaEntrada asignada (es null).
La comparacion $horaActual > $turno[horaEntrada] con null siempre es false.
CAUSA B: La zona horaria del servidor PHP es diferente a la hora real.
date('H:i:s') usa la zona horaria configurada en php.ini.
Si el servidor esta en UTC y Colombia es UTC-5, la hora del servidor puede ser 5 horas adelantada.
SOLUCION A: Asignar horaEntrada a los turnos.
SOLUCION B: Agregar al inicio del controlador: date_default_timezone_set('America/Bogota');

### Fallo 3 — Aparece error al cargar notificaciones.php
CAUSA: Si la clase NotificacionController no puede conectarse a la BD,
el constructor lanza una excepcion que no esta capturada en la vista.
La vista no tiene try/catch alrededor de $controller->getNotificaciones().
SOLUCION: Verificar la conexion a la BD y el archivo config/database.php.

---

## SECCION 8 — SE DAÑO: KIOSKO BIOMETRICO

### Archivos responsables
controllers/MarcacionController.php (endpoint AJAX)
views/registro_asistencia.php

### Como funciona (logica normal)
1. El operador escribe el numero de documento y presiona Enter
2. JavaScript captura el evento keypress y hace fetch() a:
   MarcacionController.php?accion=identificar&documento=NUMERO
3. El controlador busca: SELECT t.id_trabajador, t.documento, u.nombres, u.apellidos, c.nombre as cargo
   FROM trabajador t JOIN usuario u ON t.id_usuario = u.id_usuario LEFT JOIN cargo c ON t.id_cargo = c.id_cargo
   WHERE t.documento = ? LIMIT 1
4. Si encuentra al trabajador, busca su ultimo permiso aprobado o rechazado
5. Retorna JSON con success:true, datos del trabajador y estado del permiso
6. JavaScript muestra los datos en pantalla

### Fallo 1 — El kiosko muestra NO ENCONTRADO aunque el trabajador existe
CAUSA A: El documento buscado tiene espacios al inicio o al final.
La query busca WHERE t.documento = ? de forma exacta. Un espacio extra hace que no coincida.
CAUSA B: El documento en la BD tiene un formato diferente (ej: con guiones o puntos).
SOLUCION: Verificar el documento exacto en BD:
SELECT documento FROM trabajador WHERE documento LIKE '%NUMERO%';

### Fallo 2 — El kiosko no responde al presionar Enter
CAUSA: El evento esta en keypress sobre el input con id simular-huella.
Si el input fue renombrado o su id fue cambiado en el HTML, el addEventListener no lo encuentra.
SOLUCION: Verificar en registro_asistencia.php que el input tenga exactamente:
id="simular-huella"

### Fallo 3 — El kiosko muestra ERROR en lugar de los datos
CAUSA: La llamada fetch() fallo. Esto ocurre cuando:
- La URL del fetch es incorrecta (ruta relativa mal calculada)
- El servidor retorno HTML en lugar de JSON (error PHP en el controlador)
- El controlador no tiene el header Content-Type: application/json
SOLUCION: Abrir las herramientas de desarrollador del navegador (F12),
ir a la pestana Network y ver la respuesta del request al controlador.

### Fallo 4 — El horario del trabajador aparece como No asignado
CAUSA: El kiosko muestra data.trabajador.horario en el campo Horario.
Pero la query del endpoint identificar no incluye el horario del turno.
La query solo trae: id_trabajador, documento, nombres, apellidos, cargo.
No hace JOIN con la tabla turno para obtener horaEntrada y horaSalida.
Por eso siempre muestra No asignado.
SOLUCION: Agregar el JOIN con turno en la query del endpoint identificar en MarcacionController.php.

---

## SECCION 9 — PANTALLA EN BLANCO EN CUALQUIER VISTA

### Causas mas comunes

### Fallo 1 — Pantalla en blanco al abrir cualquier vista del dashboard
CAUSA MAS COMUN: La sesion no esta iniciada o expiro.
Todas las vistas del dashboard verifican $_SESSION[usuario] al inicio.
Si la sesion no existe, redirigen a login.php. Si hay un error antes de la redireccion,
puede aparecer pantalla en blanco.
SOLUCION: Iniciar sesion nuevamente desde login.php.

### Fallo 2 — Pantalla en blanco con error de require_once
CAUSA: Un archivo PHP usa require_once con una ruta relativa incorrecta.
Por ejemplo: require_once __DIR__ . '/../config/database.php'
Si el archivo fue movido de carpeta, __DIR__ cambia y la ruta ya no es valida.
PHP muestra pantalla en blanco si display_errors esta desactivado.
SOLUCION: Activar errores en php.ini o agregar al inicio del archivo:
error_reporting(E_ALL); ini_set('display_errors', 1);

### Fallo 3 — Pantalla en blanco solo en vistas que usan un controlador
CAUSA: La vista incluye el controlador con require_once y el controlador tiene
el bloque de enrutamiento al final que ejecuta metodos automaticamente.
Si el metodo lanza una excepcion no capturada, la pagina queda en blanco.
SOLUCION: Envolver el require_once del controlador en un try/catch en la vista.

### Fallo 4 — Error: Cannot redeclare class
CAUSA: Una clase PHP (ej: Database, Usuario, Permiso) se declara dos veces
porque dos archivos la incluyen con require en lugar de require_once.
SOLUCION: Cambiar todos los require por require_once en los archivos afectados.

---

## SECCION 10 — TABLA DE ERRORES RAPIDOS

| Error visible | Causa probable | Archivo a revisar |
|---|---|---|
| Contrasena incorrecta (siendo correcta) | password_hash editado manualmente en BD | tabla usuario, campo password_hash |
| Lista de usuarios vacia | Todos tienen rol=administrador | tabla usuario, campo rol |
| Boton entrada deshabilitado | Usuario sin registro en tabla trabajador | tabla trabajador |
| Reporte vacio | Sin turnos en el rango de fechas | tabla turno |
| Notificaciones no aparecen | Zona horaria incorrecta en PHP | php.ini o date_default_timezone_set |
| Kiosko no encuentra trabajador | Documento con espacios o formato diferente | tabla trabajador, campo documento |
| Permiso no se guarda | id_trabajador vacio en el formulario | views/dashboard/trabajador.php |
| Turnos no se agrupan | horaEntrada null en la BD | tabla turno, campos horaEntrada/horaSalida |
| Error de conexion | Puerto de MySQL incorrecto | config/database.php, campo port |
| Cannot redeclare class | require en lugar de require_once | cualquier archivo que incluya modelos |

---

*Documento generado el 13 de mayo de 2026*
