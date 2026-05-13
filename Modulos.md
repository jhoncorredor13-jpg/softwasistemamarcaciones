# Lagricola — Sistema de Marcaciones Agropecuarias

> Sistema web de control de asistencia, turnos rotativos y gestión de permisos para el sector agropecuario.

---

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Tecnologías](#2-tecnologías)
3. [Instalación Rápida](#3-instalación-rápida)
4. [Estructura del Proyecto](#4-estructura-del-proyecto)
5. [Módulo 1 — Autenticación](#5-módulo-1--autenticación)
6. [Módulo 2 — Panel Administrador](#6-módulo-2--panel-administrador)
7. [Módulo 3 — Gestión de Usuarios](#7-módulo-3--gestión-de-usuarios)
8. [Módulo 4 — Marcaciones](#8-módulo-4--marcaciones)
9. [Módulo 5 — Turnos Rotativos](#9-módulo-5--turnos-rotativos)
10. [Módulo 6 — Permisos y Ausencias](#10-módulo-6--permisos-y-ausencias)
11. [Módulo 7 — Reportes](#11-módulo-7--reportes)
12. [Módulo 8 — Notificaciones](#12-módulo-8--notificaciones)
13. [Módulo 9 — Kiosko Biométrico](#13-módulo-9--kiosko-biométrico)
14. [Módulo 10 — Panel Trabajador](#14-módulo-10--panel-trabajador)
15. [Base de Datos](#15-base-de-datos)
16. [Roles y Accesos](#16-roles-y-accesos)

---

## 1. Descripción General

**Lagricola** es una plataforma web diseñada para automatizar el control de asistencia del personal en empresas del sector agropecuario. Centraliza el registro de entradas y salidas, la programación de turnos rotativos, la gestión de permisos y la generación de reportes de asistencia.

**Misión:** Facilitar el control de asistencia mediante herramientas tecnológicas modernas, mejorando la eficiencia operativa en el sector agrícola.

**Visión:** Ser el sistema líder en gestión de marcaciones agropecuarias, garantizando precisión, seguridad y confiabilidad.

---

## 2. Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.x — Patrón MVC manual |
| Base de datos | MySQL 8.0 con PDO |
| Frontend | HTML5, CSS3, JavaScript (ES6) |
| UI Framework | Tailwind CSS (landing), CSS propio (dashboard) |
| Iconos | Font Awesome 6 |
| Alertas | SweetAlert2 |
| Servidor local | Laragon (Apache + MySQL) |

---

## 3. Instalación Rápida

**Requisitos:** PHP 8.0+, MySQL 8.0, Apache, extensiones `pdo` y `pdo_mysql`.

```bash
# 1. Colocar el proyecto en el directorio web
C:\laragon\www\proyecto-clases\

# 2. Crear la base de datos en MySQL
CREATE DATABASE sistema_marcaciones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_marcaciones;
-- Ejecutar: sql/sistema_marcaciones_sql

# 3. Acceder al sistema
http://localhost/proyecto-clases/public/index.php
```

**Configurar conexión** en `config/database.php`:

```php
private $host     = "127.0.0.1";
private $port     = "3320";   // Verificar en panel de Laragon
private $db_name  = "sistema_marcaciones";
private $username = "root";
private $password = "";
```

**Credenciales por defecto:**
- Email: `jhoncorredor13@gmail.com`
- Rol: `administrador`

---

## 4. Estructura del Proyecto

```
proyecto-clases/
├── config/
│   └── database.php              # Conexión PDO centralizada
├── controllers/
│   ├── AuthController.php        # Login, logout, recuperación
│   ├── UsuarioController.php     # CRUD de usuarios
│   ├── MarcacionController.php   # Entrada, salida, kiosko AJAX
│   ├── TurnoController.php       # Asignación y eliminación de turnos
│   ├── PermisoController.php     # Solicitud y aprobación de permisos
│   ├── ReporteController.php     # Generación de reportes
│   ├── NotificacionController.php # Alertas automáticas
│   └── AdminUsuarioController.php # (legacy)
├── models/
│   ├── usuario.php               # Modelo de usuarios y trabajadores
│   ├── turno.php                 # Modelo de turnos
│   └── permiso.php               # Modelo de permisos
├── views/
│   ├── dashboard/
│   │   ├── admin.php             # Panel principal administrador
│   │   ├── lista_usuario.php     # Gestión de usuarios
│   │   ├── marcaciones.php       # Historial de marcaciones
│   │   ├── turnos.php            # Gestión de turnos
│   │   ├── permisos.php          # Gestión de permisos
│   │   ├── reportes.php          # Reportes de asistencia
│   │   ├── notificaciones.php    # Centro de alertas
│   │   ├── trabajador.php        # Panel del trabajador
│   │   └── biometria.php         # Vista biométrica
│   ├── usuario/
│   │   ├── login.php             # Inicio de sesión
│   │   ├── registre.php          # Registro de usuario
│   │   └── editar_usuario.php    # Edición de usuario
│   └── registro_asistencia.php   # Kiosko de marcación
├── public/
│   └── index.php                 # Landing page
├── sql/
│   └── sistema_marcaciones_sql   # Script SQL completo
└── img/                          # Recursos gráficos
```

---

## 5. Módulo 1 — Autenticación

**Archivos:** `controllers/AuthController.php` · `views/usuario/login.php`

### ¿Qué hace?

Controla el acceso al sistema. Valida las credenciales del usuario, crea la sesión PHP y redirige al panel correspondiente según el rol.

### Funcionalidades

| Acción | Descripción |
|---|---|
| Iniciar sesión | Valida email y contraseña con `password_verify()` |
| Cerrar sesión | Destruye la sesión con `session_destroy()` |
| Recuperar contraseña | Genera contraseña temporal `[inicial]123456` y la guarda hasheada |

### Flujo de login

```
Usuario ingresa email + contraseña
        │
        ├─ Validar campos vacíos
        ├─ Validar formato de email (filter_var)
        ├─ Buscar usuario activo en BD
        ├─ Verificar hash con password_verify()
        ├─ Regenerar ID de sesión (session_regenerate_id)
        └─ Redirigir según rol
              ├─ administrador → /views/dashboard/admin.php
              └─ trabajador    → /views/dashboard/trabajador.php
```

### Recuperación de contraseña

Desde el login, el botón "¿Olvidaste tu contraseña?" abre un modal (SweetAlert2) donde el usuario ingresa su email. El sistema genera una contraseña temporal con el formato:

```
[primera_letra_del_nombre_en_minúscula] + 123456
```

Ejemplo: nombre `Admin` → contraseña temporal `a123456`.

> La contraseña temporal se guarda hasheada en la base de datos. Se recomienda cambiarla al ingresar.

### Seguridad aplicada

- Contraseñas almacenadas con `password_hash()` (bcrypt)
- Regeneración de ID de sesión al autenticar
- Validación de email con `filter_var()`
- Verificación de estado `activo = 1` antes de permitir acceso

---

## 6. Módulo 2 — Panel Administrador

**Archivos:** `views/dashboard/admin.php`

### ¿Qué hace?

Es la pantalla principal del administrador. Muestra un resumen estadístico del sistema y da acceso a todos los módulos desde la barra lateral.

### Estadísticas mostradas

| Métrica | Fuente |
|---|---|
| Usuarios registrados | `COUNT(*) FROM usuario WHERE activo = 1` |
| Trabajadores activos | `COUNT(*) FROM trabajador JOIN usuario WHERE rol = 'trabajador' AND activo = 1` |
| Marcaciones de hoy | `COUNT(*) FROM registro_entrada JOIN turno WHERE fecha = hoy` |

### Navegación disponible (sidebar)

- Panel de Control
- Gestión de Usuarios
- Marcaciones
- Permisos / Ausencias
- Reportes PDF o Excel
- Notificaciones Automáticas
- Turnos Rotativos
- Huella Biométrica (Kiosko)
- Cerrar Sesión

### Control de acceso

La vista verifica que la sesión tenga rol `administrador`. Si no, redirige al login:

```php
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    header("Location: ../usuario/login.php");
    exit;
}
```

---

## 7. Módulo 3 — Gestión de Usuarios

**Archivos:** `controllers/UsuarioController.php` · `views/dashboard/lista_usuario.php` · `models/usuario.php`

### ¿Qué hace?

Permite al administrador crear, editar, activar, desactivar y eliminar usuarios del sistema. Al registrar un trabajador, crea simultáneamente el registro en `usuario` y en `trabajador` dentro de una transacción.

### Funcionalidades

| Acción | Endpoint | Descripción |
|---|---|---|
| Listar usuarios | `lista_usuario.php` | Tabla con búsqueda y paginación en cliente |
| Registrar usuario | `UsuarioController.php?accion=registrar` | Crea usuario + trabajador en transacción |
| Editar usuario | `UsuarioController.php?accion=actualizar` | Actualiza datos personales y laborales |
| Desactivar | `UsuarioController.php?accion=desactivar` | Marca `activo = 0`, bloquea el acceso |
| Activar | `UsuarioController.php?accion=activar` | Marca `activo = 1`, restaura el acceso |
| Eliminar | `UsuarioController.php?accion=eliminar` | Borra permanentemente el registro |

### Formulario de registro (campos)

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombres | Sí | |
| Apellidos | Sí | |
| Correo electrónico | Sí | Debe ser único en la BD |
| Contraseña | Sí | Mínimo 6 caracteres |
| Confirmar contraseña | Sí | Debe coincidir |
| Rol | Sí | `administrador` o `trabajador` |
| Cargo | Sí | Selección del catálogo de cargos |
| Teléfono | No | |

### Validaciones aplicadas

- Campos obligatorios no vacíos
- Contraseñas coincidentes
- Email único (verificado con `existeCorreo()`)
- Contraseña hasheada con `password_hash()` antes de guardar

### Tabla de usuarios — controles de interfaz

- **Búsqueda en tiempo real** por nombre, email o rol (filtro JavaScript)
- **Paginación cliente** con selector de 5 / 10 / 25 / 50 registros
- **Confirmación SweetAlert2** antes de desactivar, activar o eliminar
- **Modal de edición** precargado con los datos actuales del usuario

---

## 8. Módulo 4 — Marcaciones

**Archivos:** `controllers/MarcacionController.php` · `views/dashboard/marcaciones.php`

### ¿Qué hace?

Registra la asistencia diaria de los trabajadores. Gestiona los registros de entrada y salida vinculados a los turnos. También expone un endpoint AJAX para el kiosko biométrico.

### Funcionalidades

| Acción | Tipo | Descripción |
|---|---|---|
| Registrar entrada | POST `accion=entrada` | Crea turno del día si no existe, luego registra entrada |
| Registrar salida | POST `accion=salida` | Requiere entrada previa; registra la salida |
| Identificar trabajador | GET `?accion=identificar&documento=X` | Endpoint AJAX para el kiosko |
| Ver historial | `marcaciones.php` | Tabla con los últimos 100 registros |

### Flujo de registro de entrada

```
POST accion=entrada, id_trabajador=X
        │
        ├─ Buscar turno del día (fecha = hoy, id_trabajador = X)
        ├─ Si no existe → INSERT turno automático
        ├─ Verificar si ya existe registro_entrada para ese turno
        │       ├─ Sí → alerta "Ya registraste tu entrada hoy"
        │       └─ No → INSERT registro_entrada (hora actual)
        └─ Redirigir a dashboard trabajador
```

### Flujo de registro de salida

```
POST accion=salida, id_trabajador=X
        │
        ├─ Buscar turno del día
        ├─ Buscar registro_entrada del turno
        │       └─ No existe → alerta "Primero debes registrar entrada"
        ├─ Verificar si ya existe registro_salida
        │       ├─ Sí → alerta "Ya registraste tu salida"
        │       └─ No → INSERT registro_salida (hora actual)
        └─ Redirigir a dashboard trabajador
```

### Vista de historial (admin)

Muestra los últimos 100 registros con:
- Nombre y apellido del trabajador
- Cargo
- Área del turno
- Fecha
- Hora de entrada (badge verde)
- Hora de salida (badge amarillo)
- Reporte del turno (truncado a 40 caracteres)

---

## 9. Módulo 5 — Turnos Rotativos

**Archivos:** `controllers/TurnoController.php` · `views/dashboard/turnos.php` · `models/turno.php`

### ¿Qué hace?

Permite al administrador asignar turnos de trabajo a los trabajadores por rangos de fechas. Soporta cuatro tipos de turno predefinidos y agrupa los turnos consecutivos para una visualización más limpia.

### Tipos de turno

| Tipo | Hora Entrada | Hora Salida |
|---|---|---|
| Mañana | 06:00 | 14:00 |
| Tarde | 14:00 | 22:00 |
| Noche | 22:00 | 06:00 |
| Oficina | 08:00 | 17:00 |

### Funcionalidades

| Acción | Endpoint | Descripción |
|---|---|---|
| Asignar turnos | `TurnoController.php?accion=asignar` | Itera día a día en el rango y crea/reemplaza turnos |
| Eliminar turno | `TurnoController.php?accion=eliminar` | Elimina uno o varios turnos por ID |
| Ver turnos futuros | `turnos.php` | Lista agrupada de turnos desde hoy |

### Flujo de asignación masiva

```
POST: id_trabajador, tipo_turno, fecha_inicio, fecha_fin
        │
        ├─ Validar campos completos
        ├─ Validar que fecha_inicio <= fecha_fin
        ├─ Determinar horaEntrada y horaSalida según tipo_turno
        └─ Iterar cada día del rango (DatePeriod)
              └─ Para cada día:
                    ├─ DELETE turno existente (si hay)
                    └─ INSERT nuevo turno
```

### Agrupación de turnos consecutivos

El método `indexAdminAgrupados()` agrupa los turnos en rangos cuando:
- Son del mismo trabajador
- Tienen el mismo horario (entrada y salida)
- Las fechas son consecutivas (diferencia de 1 día)

Esto evita mostrar una fila por cada día y presenta rangos como `01/06/2026 hasta 15/06/2026`.

### Búsqueda en la tabla

Filtro en tiempo real por nombre o documento del trabajador (JavaScript).

---

## 10. Módulo 6 — Permisos y Ausencias

**Archivos:** `controllers/PermisoController.php` · `views/dashboard/permisos.php` · `models/permiso.php`

### ¿Qué hace?

Gestiona el flujo completo de solicitudes de permiso entre trabajadores y el administrador. El trabajador solicita desde su panel y el administrador aprueba o rechaza desde el suyo.

### Estados del permiso

```
pendiente  →  aprobado
           →  rechazado
```

### Funcionalidades

| Acción | Quién | Endpoint | Descripción |
|---|---|---|---|
| Solicitar permiso | Trabajador | `PermisoController.php?accion=solicitar` | Crea permiso con estado `pendiente` |
| Aprobar permiso | Administrador | `PermisoController.php?accion=cambiarEstado` | Cambia estado a `aprobado` |
| Rechazar permiso | Administrador | `PermisoController.php?accion=cambiarEstado` | Cambia estado a `rechazado` |
| Ver todos los permisos | Administrador | `permisos.php` | Lista con filtro de búsqueda |
| Ver mis permisos | Trabajador | `trabajador.php` | Historial personal con badges de estado |

### Formulario de solicitud (campos)

| Campo | Descripción |
|---|---|
| Fecha inicio | Fecha desde la que aplica el permiso (mínimo hoy) |
| Fecha fin | Fecha hasta la que aplica (mínimo hoy) |
| Motivo | Descripción del motivo de la ausencia |

### Validaciones

- Todos los campos son obligatorios
- `fecha_inicio` no puede ser posterior a `fecha_fin`
- El permiso queda en estado `pendiente` hasta que el administrador actúe

### Vista del administrador

- Tabla con búsqueda en tiempo real por nombre del trabajador
- Botones de aprobar (verde) y rechazar (rojo) solo visibles en permisos `pendiente`
- Confirmación SweetAlert2 antes de cambiar el estado
- Permisos ya procesados muestran el texto "Procesado" en lugar de botones

### Impacto en el kiosko

Cuando un trabajador marca en el kiosko, el sistema consulta su último permiso aprobado o rechazado y lo muestra como una nota en pantalla.

---

## 11. Módulo 7 — Reportes

**Archivos:** `controllers/ReporteController.php` · `views/dashboard/reportes.php`

### ¿Qué hace?

Genera reportes de asistencia filtrables por rango de fechas y trabajador. Calcula automáticamente el estado de asistencia de cada registro y permite exportar a PDF (impresión) o CSV (Excel).

### Filtros disponibles

| Filtro | Descripción |
|---|---|
| Fecha inicio | Primer día del rango (por defecto: primer día del mes actual) |
| Fecha fin | Último día del rango (por defecto: último día del mes actual) |
| Trabajador | Opcional — filtra por un trabajador específico |

### Columnas del reporte

| Columna | Descripción |
|---|---|
| Fecha | Fecha del turno |
| Trabajador | Nombre completo |
| Turno asignado | Horario programado (entrada — salida) |
| Hora entrada | Hora real de marcación de entrada |
| Hora salida | Hora real de marcación de salida |
| Estado | Estado calculado automáticamente |

### Lógica de estado automático

| Condición | Estado | Color |
|---|---|---|
| Tiene permiso aprobado en esa fecha | `Permiso` | Azul |
| Tiene registro de entrada | `Asistió` | Verde |
| Fecha pasada sin marcación | `Faltó` | Rojo |
| Fecha futura o actual sin marcación | `Pendiente` | Gris |

### Exportación

| Formato | Método |
|---|---|
| PDF | `window.print()` con estilos `@media print` que ocultan filtros y sidebar |
| Excel / CSV | Función JavaScript que convierte la tabla HTML a CSV con codificación UTF-8 (BOM incluido para compatibilidad con Excel) |

---

## 12. Módulo 8 — Notificaciones

**Archivos:** `controllers/NotificacionController.php` · `views/dashboard/notificaciones.php`

### ¿Qué hace?

Genera alertas automáticas en tiempo real para el administrador. Detecta permisos pendientes, posibles inasistencias y llegadas tarde del día actual.

### Tipos de notificación

| Tipo | Color | Icono | Condición |
|---|---|---|---|
| Permiso pendiente | Naranja `#FF9800` | `fa-calendar-alt` | Permiso con estado `pendiente` |
| Posible inasistencia | Rojo `#E74C3C` | `fa-exclamation-triangle` | Turno de hoy sin entrada y ya pasó la hora |
| Llegada tarde | Amarillo `#F1C40F` | `fa-clock` | Entrada registrada con más de 5 minutos de retraso |

### Lógica de detección

**Permisos pendientes:**
```sql
SELECT ... FROM permiso WHERE estado = 'pendiente' ORDER BY created_at DESC
```

**Inasistencias del día:**
```
Para cada turno de hoy:
  Si no hay registro_entrada Y hora_actual > horaEntrada del turno
  → Notificación de posible inasistencia
```

**Llegadas tarde:**
```
Para cada turno de hoy con registro_entrada:
  Si (hora_entrada_real - hora_entrada_turno) > 300 segundos (5 minutos)
  → Notificación de llegada tarde
```

### Interfaz

- Las notificaciones se muestran como tarjetas con borde de color izquierdo
- Cada tarjeta incluye: icono, título, mensaje descriptivo, fecha/hora y botón "Ver Detalle"
- Si no hay notificaciones, muestra un estado vacío con mensaje "¡Todo está al día!"
- Las notificaciones se ordenan por fecha más reciente

---

## 13. Módulo 9 — Kiosko Biométrico

**Archivos:** `views/registro_asistencia.php` · `controllers/MarcacionController.php` (endpoint AJAX)

### ¿Qué hace?

Es una pantalla de pantalla completa diseñada para funcionar como terminal de marcación. Simula la lectura de huella dactilar mediante el ingreso del número de documento. Muestra los datos del trabajador en tiempo real sin recargar la página.

### Funcionalidades

| Funcionalidad | Descripción |
|---|---|
| Reloj en tiempo real | Fecha y hora actualizadas cada segundo (zona horaria `America/Bogota`) |
| Identificación por documento | El operador ingresa el número de documento y presiona Enter |
| Consulta AJAX | Llama a `MarcacionController.php?accion=identificar&documento=X` |
| Visualización de datos | Muestra: identificación, nombre, cargo, horario asignado |
| Estado de permiso | Si el trabajador tiene permiso aprobado o rechazado, lo muestra como nota |

### Endpoint AJAX — respuesta JSON

```json
{
  "success": true,
  "trabajador": {
    "id_trabajador": 1,
    "documento": "12345678",
    "nombres": "Juan",
    "apellidos": "Pérez",
    "cargo": "Operario"
  },
  "permiso": {
    "estado": "aprobado"
  }
}
```

### Estados visuales

| Estado | Texto mostrado | Color |
|---|---|---|
| Esperando | `EN ESPERA O EXITOSA` | Gris |
| Buscando | `BUSCANDO...` | Gris |
| Encontrado | `EXITOSA` | Verde `#2E7D32` |
| No encontrado | `NO ENCONTRADO` | Rojo `#C62828` |
| Error | `ERROR` | Rojo |

### Nota de permiso

Si el trabajador tiene un permiso activo, aparece una nota debajo del estado:

- Permiso **aprobado** → fondo verde, texto `NOTA: permiso aprobado`
- Permiso **rechazado** → fondo rojo, texto `NOTA: permiso rechazado`

---

## 14. Módulo 10 — Panel Trabajador

**Archivos:** `views/dashboard/trabajador.php`

### ¿Qué hace?

Es el panel personal del trabajador. Desde aquí puede registrar su entrada y salida del día, solicitar permisos y consultar el historial de sus solicitudes.

### Secciones del panel

#### Mis Marcaciones de Hoy

Muestra el estado de asistencia del día actual:

| Elemento | Descripción |
|---|---|
| Reloj en tiempo real | Hora actual actualizada cada segundo |
| Tarjeta Entrada | Hora registrada o "Sin registrar" |
| Tarjeta Salida | Hora registrada o "Sin registrar" |
| Botón de acción | Cambia dinámicamente según el estado del día |

**Lógica del botón de acción:**

```
Si no hay entrada registrada hoy:
    → Mostrar botón "Registrar Entrada" (verde)
Si hay entrada pero no salida:
    → Mostrar botón "Registrar Salida" (amarillo)
Si hay entrada y salida:
    → Mostrar botón deshabilitado "Marcaciones completas del día"
```

#### Solicitar Permiso o Vacaciones

Formulario para enviar solicitudes de permiso al administrador:

| Campo | Descripción |
|---|---|
| Desde la fecha | Fecha de inicio del permiso (mínimo hoy) |
| Hasta la fecha | Fecha de fin del permiso (mínimo hoy) |
| Motivo | Descripción de la solicitud |

#### Historial de Permisos

Tabla con todas las solicitudes del trabajador:

| Columna | Descripción |
|---|---|
| Fecha Solicitud | Cuándo se envió la solicitud |
| Desde | Fecha de inicio del permiso |
| Hasta | Fecha de fin del permiso |
| Motivo | Descripción ingresada |
| Estado | Badge de color: `pendiente` (amarillo), `aprobado` (verde), `rechazado` (rojo) |

### Control de acceso

```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'trabajador') {
    header("Location: ../usuario/login.php");
    exit;
}
```

---

## 15. Base de Datos

**Nombre:** `sistema_marcaciones` · **Motor:** InnoDB · **Charset:** utf8mb4_unicode_ci

### Tablas

| Tabla | Descripción |
|---|---|
| `usuario` | Credenciales y rol de todos los usuarios del sistema |
| `trabajador` | Datos laborales vinculados a un usuario |
| `cargo` | Catálogo de cargos (Administrador, Trabajador Agrícola, Operario, Supervisor) |
| `turno` | Turnos asignados por fecha con horario de entrada y salida |
| `registro_entrada` | Hora real de entrada del trabajador en su turno |
| `registro_salida` | Hora real de salida, vinculada a una entrada |
| `permiso` | Solicitudes de permiso con estado de aprobación |

### Relaciones principales

```
usuario (1) ──── (1) trabajador (1) ──── (N) turno
                                    └─── (N) permiso
cargo   (1) ──── (N) trabajador
turno   (1) ──── (1) registro_entrada (1) ──── (1) registro_salida
```

### Script SQL

El archivo `sql/sistema_marcaciones_sql` contiene el dump completo con estructura y datos iniciales (cargos y usuario administrador).

---

## 16. Roles y Accesos

| Funcionalidad | Administrador | Trabajador |
|---|---|---|
| Panel de estadísticas | ✅ | ❌ |
| Gestión de usuarios (CRUD) | ✅ | ❌ |
| Ver historial de marcaciones | ✅ (todos) | ❌ |
| Registrar entrada/salida | ❌ | ✅ (propias) |
| Asignar turnos | ✅ | ❌ |
| Aprobar/rechazar permisos | ✅ | ❌ |
| Solicitar permisos | ❌ | ✅ |
| Ver historial de permisos | ✅ (todos) | ✅ (propios) |
| Generar reportes | ✅ | ❌ |
| Ver notificaciones | ✅ | ❌ |
| Acceso al kiosko | ✅ | ❌ |

---

*Documentación generada el 13 de mayo de 2026*
