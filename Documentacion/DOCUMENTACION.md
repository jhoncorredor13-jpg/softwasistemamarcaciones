# Sistema de Marcacione la agricola 

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Tecnologías Utilizadas](#2-tecnologías-utilizadas)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Arquitectura](#4-arquitectura)
5. [Base de Datos](#5-base-de-datos)
6. [Módulos del Sistema](#6-módulos-del-sistema)
7. [Controladores](#7-controladores)
8. [Modelos](#8-modelos)
9. [Vistas](#9-vistas)
10. [Roles y Permisos](#10-roles-y-permisos)
11. [Flujos Principales](#11-flujos-principales)
12. [Configuración e Instalación](#12-configuración-e-instalación)

---

## 1. Descripción General

**Lagricola** es un sistema web de control de asistencia y gestión de turnos orientado al sector agropecuario. Permite registrar la entrada y salida de trabajadores, administrar turnos rotativos, gestionar permisos/ausencias y generar reportes de asistencia.

**Misión:** Facilitar el control de asistencia de trabajadores mediante herramientas tecnológicas modernas, mejorando la eficiencia operativa en el sector agrícola.

**Visión:** Ser el sistema líder en gestión de marcaciones agropecuarias, garantizando precisión, seguridad y confiabilidad.

---

## 2. Tecnologías Utilizadas

| Tecnología | Uso |
|---|---|
| PHP 8.x | Backend / lógica del servidor |
| MySQL 8.0 | Base de datos relacional |
| PDO | Capa de acceso a datos (consultas parametrizadas) |
| HTML5 / CSS3 | Estructura y estilos de las vistas |
| Tailwind CSS (CDN) | Framework CSS utilitario (página de inicio) |
| Font Awesome 6 | Iconografía |
| SweetAlert2 | Alertas y modales interactivos |
| JavaScript (Vanilla) | Interactividad del cliente, llamadas AJAX |
| Laragon | Entorno de desarrollo local (Apache + MySQL) |

---

## 3. Estructura del Proyecto

```
proyecto-clases/
├── config/
│   └── database.php              # Clase de conexión PDO a MySQL
├── controllers/
│   ├── AuthController.php        # Autenticación (login, logout, recuperación)
│   ├── AdminUsuarioController.php # Gestión de usuarios (admin)
│   ├── MarcacionController.php   # Registro de entrada/salida y kiosko
│   ├── NotificacionController.php # Notificaciones automáticas
│   ├── PermisoController.php     # Solicitud y aprobación de permisos
│   ├── ReporteController.php     # Generación de reportes
│   ├── TurnoController.php       # Asignación y gestión de turnos
│   └── UsuarioController.php     # CRUD de usuarios
├── models/
│   ├── cargo.php                 # Modelo de cargos
│   ├── marcacion.php             # Modelo de marcaciones
│   ├── permiso.php               # Modelo de permisos
│   ├── trabajador.php            # Modelo de trabajadores
│   ├── turno.php                 # Modelo de turnos
│   └── usuario.php               # Modelo de usuarios
├── views/
│   ├── dashboard/
│   │   ├── admin.php             # Panel principal del administrador
│   │   ├── biometria.php         # Vista de biometría
│   │   ├── lista_usuario.php     # Listado y gestión de usuarios
│   │   ├── marcaciones.php       # Vista de marcaciones (admin)
│   │   ├── notificaciones.php    # Vista de notificaciones
│   │   ├── permisos.php          # Gestión de permisos (admin)
│   │   ├── reportes.php          # Generación de reportes
│   │   ├── trabajador.php        # Panel del trabajador
│   │   └── turnos.php            # Gestión de turnos
│   ├── usuario/
│   │   ├── login.php             # Formulario de inicio de sesión
│   │   ├── registre.php          # Registro de nuevos usuarios
│   │   └── editar_usuario.php    # Edición de datos de usuario
│   └── registro_asistencia.php   # Kiosko de marcación biométrica
├── public/
│   └── index.php                 # Página de inicio (landing page)
├── sql/
│   └── sistema_marcaciones_sql   # Script SQL de la base de datos
├── img/
│   ├── logo.png
│   ├── inicio.png
│   ├── marcacion.png
│   └── carrusel1.jpg
└── Documentacion/
    └── DOCUMENTACION.md          # Este archivo
```

---

## 4. Arquitectura

El sistema sigue el patrón **MVC (Modelo-Vista-Controlador)** de forma manual, sin framework:

```
Navegador
    │
    ▼
Vista (views/)              ← HTML + PHP embebido, interfaz de usuario
    │
    ▼
Controlador (controllers/)  ← Lógica de negocio, validaciones, redirecciones
    │
    ▼
Modelo (models/)            ← Consultas SQL, acceso a datos vía PDO
    │
    ▼
Base de Datos (MySQL)       ← sistema_marcaciones
```

La conexión a la base de datos se centraliza en `config/database.php` mediante la clase `Database`, que retorna una instancia PDO configurada con manejo de errores por excepción.

---

## 5. Base de Datos

**Nombre:** `sistema_marcaciones`
**Motor:** InnoDB
**Charset:** utf8mb4_unicode_ci

### Relaciones entre Tablas

```
cargo
  └── id_cargo (PK)
  └── nombre

usuario
  └── id_usuario (PK)
  └── nombres, apellidos, email (UNIQUE)
  └── password_hash
  └── rol ENUM('administrador','trabajador')
  └── activo, created_at

trabajador
  └── id_trabajador (PK)
  └── id_usuario  (FK → usuario)
  └── id_cargo    (FK → cargo)
  └── documento, telefono, fecha_ingreso, estado

turno
  └── id_turno (PK)
  └── id_trabajador (FK → trabajador)
  └── fecha, horaEntrada, horaSalida, reporteTurno

registro_entrada
  └── id_registro_entrada (PK)
  └── id_turno (FK → turno)
  └── hora_entrada, fecha_registro

registro_salida
  └── id_registro_salida (PK)
  └── id_registro_entrada (FK → registro_entrada)
  └── hora_salida, fecha_registro

permiso
  └── id_permiso (PK)
  └── id_trabajador (FK → trabajador)
  └── fecha_inicio, fecha_fin, motivo
  └── estado ENUM('pendiente','aprobado','rechazado')
  └── created_at
```

### Descripción de Tablas

| Tabla | Descripción |
|---|---|
| `usuario` | Almacena todos los usuarios del sistema con su rol y credenciales |
| `trabajador` | Datos laborales del trabajador, vinculado a un usuario |
| `cargo` | Catálogo de cargos (Administrador, Trabajador Agrícola, Operario, Supervisor) |
| `turno` | Turnos asignados a cada trabajador por fecha con horario de entrada y salida |
| `registro_entrada` | Registro de la hora real de entrada del trabajador en su turno |
| `registro_salida` | Registro de la hora real de salida, vinculada a una entrada |
| `permiso` | Solicitudes de permiso/ausencia con estado de aprobación |

### Datos Iniciales

**Cargos disponibles:**
- Administrador
- Trabajador Agrícola
- Operario
- Supervisor

**Usuario administrador por defecto:**
- Email: `jhoncorredor13@gmail.com`
- Rol: `administrador`
- Contraseña: hasheada con `password_hash` de PHP (bcrypt)

---

## 6. Módulos del Sistema

### 6.1 Autenticación

Gestiona el acceso al sistema mediante email y contraseña.

**Funcionalidades:**
- Inicio de sesión con validación de credenciales
- Cierre de sesión con destrucción de sesión PHP
- Recuperación de contraseña: genera contraseña temporal con formato `[inicial_nombre]123456`
- Redirección automática según el rol del usuario

### 6.2 Gestión de Usuarios

Permite al administrador administrar los usuarios del sistema.

**Funcionalidades:**
- Listar todos los trabajadores con su cargo
- Registrar nuevo usuario (crea registros en `usuario` y `trabajador` en una transacción atómica)
- Editar datos personales y laborales
- Activar / desactivar cuenta
- Eliminar usuario

### 6.3 Marcaciones

Módulo central del sistema. Registra la asistencia diaria de los trabajadores.

**Funcionalidades:**
- Registrar entrada (crea turno del día automáticamente si no existe)
- Registrar salida (requiere entrada previa registrada)
- Validación de doble marcación (no permite registrar dos veces el mismo tipo)
- Kiosko de marcación por número de documento (simulación biométrica vía AJAX)

### 6.4 Turnos Rotativos

Permite al administrador asignar turnos a los trabajadores por rangos de fechas.

**Tipos de turno disponibles:**

| Tipo | Hora Entrada | Hora Salida |
|---|---|---|
| Mañana | 06:00 | 14:00 |
| Tarde | 14:00 | 22:00 |
| Noche | 22:00 | 06:00 |
| Oficina | 08:00 | 17:00 |

**Funcionalidades:**
- Asignación masiva por rango de fechas (itera día a día)
- Si ya existe un turno para esa fecha, se reemplaza automáticamente
- Agrupación de turnos consecutivos para visualización simplificada
- Eliminación de turnos individuales o en grupo

### 6.5 Permisos y Ausencias

Gestión del flujo de solicitudes de permiso entre trabajadores y administrador.

**Flujo:**
1. El trabajador solicita un permiso indicando fechas y motivo
2. El permiso queda en estado `pendiente`
3. El administrador aprueba o rechaza la solicitud
4. El estado del permiso se refleja en el kiosko de marcación

**Estados posibles:** `pendiente` → `aprobado` / `rechazado`

### 6.6 Reportes

Genera reportes de asistencia filtrables por rango de fechas y trabajador.

**Información incluida:**
- Nombre y documento del trabajador
- Fecha del turno
- Horario asignado (entrada/salida del turno)
- Hora real de marcación (entrada/salida)
- Estado final calculado

**Lógica del estado final:**

| Condición | Estado |
|---|---|
| Tiene permiso aprobado en esa fecha | `Permiso` |
| Tiene registro de entrada | `Asistió` |
| Fecha pasada sin marcación | `Faltó` |
| Fecha futura o actual sin marcación | `Pendiente` |

### 6.7 Kiosko Biométrico

Vista de pantalla completa para registro de asistencia en tiempo real. Simula la lectura de huella dactilar mediante ingreso de número de documento.

**Funcionalidades:**
- Muestra fecha y hora en tiempo real (zona horaria Colombia — `America/Bogota`)
- Búsqueda de trabajador por documento vía AJAX (sin recargar la página)
- Muestra datos del trabajador: nombre, cargo, horario asignado
- Indica si el trabajador tiene un permiso activo (aprobado o rechazado)

---

## 7. Controladores

### `AuthController.php`

| Método | Acción | Descripción |
|---|---|---|
| `login()` | POST (default) | Valida credenciales y crea sesión |
| `logout()` | GET `?accion=logout` | Destruye la sesión y redirige al login |
| `recuperar()` | POST `?accion=recuperar` | Genera y guarda contraseña temporal |

### `MarcacionController.php`

| Acción | Método HTTP | Descripción |
|---|---|---|
| `accion=entrada` | POST | Registra la entrada del trabajador |
| `accion=salida` | POST | Registra la salida del trabajador |
| `?accion=identificar&documento=X` | GET / AJAX | Retorna JSON con datos del trabajador para el kiosko |

### `TurnoController.php`

| Método | Acción GET | Descripción |
|---|---|---|
| `asignarMasivo()` | `?accion=asignar` | Asigna turnos por rango de fechas |
| `eliminar()` | `?accion=eliminar` | Elimina uno o varios turnos |
| `indexAdmin()` | — | Retorna turnos futuros |
| `indexAdminAgrupados()` | — | Retorna turnos agrupados por consecutividad |
| `getAllTrabajadores()` | — | Lista trabajadores activos |

### `PermisoController.php`

| Método | Acción GET | Descripción |
|---|---|---|
| `solicitarPermiso()` | `?accion=solicitar` | Crea una solicitud de permiso |
| `cambiarEstado()` | `?accion=cambiarEstado` | Aprueba o rechaza un permiso |
| `indexAdmin()` | — | Lista todos los permisos |
| `indexTrabajador($id)` | — | Lista permisos de un trabajador |

### `ReporteController.php`

| Método | Descripción |
|---|---|
| `generarReporte($inicio, $fin, $id_trabajador)` | Consulta y procesa datos de asistencia |
| `getAllTrabajadores()` | Lista trabajadores activos para el filtro |

---

## 8. Modelos

### `Usuario`

| Método | Descripción |
|---|---|
| `existeCorreo($email)` | Verifica si un email ya está registrado |
| `obtenerPorEmail($email)` | Busca usuario activo por email |
| `obtenerPorId($id)` | Obtiene usuario con datos de trabajador (JOIN) |
| `contarUsuarios()` | Cuenta usuarios activos |
| `contarTrabajadores()` | Cuenta trabajadores activos con rol trabajador |
| `listarTrabajadores()` | Lista todos los trabajadores con cargo |
| `registrar($datos)` | Crea usuario y trabajador en transacción |
| `actualizar($id, $datos)` | Actualiza usuario y trabajador en transacción |
| `desactivar($id)` | Marca usuario como inactivo (`activo=0`) |
| `activar($id)` | Marca usuario como activo (`activo=1`) |
| `actualizarPassword($email, $hash)` | Actualiza contraseña hasheada |
| `borrar($id)` | Elimina usuario de la base de datos |

### `Turno`

| Método | Descripción |
|---|---|
| `getTurnosFuturos()` | Obtiene turnos desde hoy en adelante con datos del trabajador |
| `asignarTurnoDia($id, $fecha, $entrada, $salida)` | Crea o reemplaza turno para un día específico |
| `eliminarTurno($id_turno)` | Elimina un turno por ID |

### `Permiso`

| Método | Descripción |
|---|---|
| `getAllPermisos()` | Lista todos los permisos con datos del trabajador |
| `getPermisosByTrabajador($id)` | Lista permisos de un trabajador específico |
| `crearPermiso()` | Inserta nueva solicitud de permiso |
| `actualizarEstado()` | Cambia el estado de un permiso |

### `Cargo`

| Método | Descripción |
|---|---|
| `listarCargos()` | Obtiene todos los cargos registrados ordenados alfabéticamente |
| `obtenerPorId($id)` | Busca la información de un cargo específico por su ID |

### `Trabajador`

| Método | Descripción |
|---|---|
| `obtenerPorIdUsuario($id_usuario)` | Obtiene el perfil completo del trabajador (incluyendo nombres y cargo) usando el ID de usuario |
| `obtenerPorDocumento($documento)` | Busca a un trabajador por su número de identificación/documento |
| `listarTodos()` | Lista a todos los trabajadores con sus respectivos nombres y cargos |

### `Marcacion`

| Método | Descripción |
|---|---|
| `registrarEntrada($id_turno)` | Registra la hora de entrada real (`CURTIME()`) vinculada a un turno específico |
| `registrarSalida($id_id_entrada)` | Registra la hora de salida real vinculada a una entrada previa |
| `obtenerEntradaPendiente($id_turno)` | Verifica si existe una entrada que aún no tenga salida registrada para un turno |
| `listarHistorial($limite)` | Recupera el historial detallado de asistencias, cruzando datos de turnos, usuarios y registros de tiempo |

---

## 9. Vistas

### Panel Administrador (`views/dashboard/`)

| Vista | Descripción |
|---|---|
| `admin.php` | Dashboard con estadísticas: usuarios registrados, trabajadores activos, marcaciones del día |
| `lista_usuario.php` | Tabla de trabajadores con opciones de editar, activar/desactivar y eliminar |
| `marcaciones.php` | Historial de marcaciones de todos los trabajadores |
| `turnos.php` | Formulario de asignación de turnos y listado de turnos futuros agrupados |
| `permisos.php` | Lista de solicitudes de permiso con botones de aprobar/rechazar |
| `reportes.php` | Filtros de fecha y trabajador para generar reporte de asistencia |
| `notificaciones.php` | Vista de notificaciones automáticas del sistema |
| `biometria.php` | Vista relacionada con el módulo biométrico |

### Panel Trabajador

| Vista | Descripción |
|---|---|
| `views/dashboard/trabajador.php` | Dashboard del trabajador: botones de entrada/salida, historial personal, solicitud de permisos |

### Autenticación (`views/usuario/`)

| Vista | Descripción |
|---|---|
| `login.php` | Formulario de inicio de sesión con recuperación de contraseña vía SweetAlert2 |
| `registre.php` | Formulario de registro de nuevo usuario |
| `editar_usuario.php` | Formulario de edición de datos de usuario |

### Kiosko

| Vista | Descripción |
|---|---|
| `views/registro_asistencia.php` | Pantalla de kiosko para marcación por documento con reloj en tiempo real |

---

## 10. Roles y Permisos

El sistema maneja dos roles definidos en la columna `rol` de la tabla `usuario`:

### Administrador

- Accede al panel `/views/dashboard/admin.php`
- Gestión completa de usuarios (crear, editar, activar, desactivar, eliminar)
- Visualización de todas las marcaciones
- Asignación y eliminación de turnos
- Aprobación o rechazo de permisos
- Generación de reportes de asistencia
- Acceso al kiosko biométrico

### Trabajador

- Accede al panel `/views/dashboard/trabajador.php`
- Registro de entrada y salida propios
- Visualización de su historial de marcaciones
- Solicitud de permisos/ausencias
- Visualización del estado de sus permisos

---

## 11. Flujos Principales

### Flujo de Inicio de Sesión

```
Usuario ingresa email + contraseña
        │
        ▼
AuthController::login()
        ├─ Validar campos vacíos
        ├─ Validar formato de email
        ├─ Buscar usuario en BD (activo = 1)
        ├─ Verificar password con password_verify()
        ├─ Regenerar ID de sesión (session_regenerate_id)
        └─ Redirigir según rol
              ├─ administrador → /views/dashboard/admin.php
              └─ trabajador    → /views/dashboard/trabajador.php
```

### Flujo de Marcación de Asistencia

```
Trabajador hace clic en "Registrar Entrada"
        │
        ▼
MarcacionController (POST accion=entrada)
        ├─ Buscar turno del día para el trabajador
        ├─ Si no existe turno → INSERT turno automático
        ├─ Verificar si ya existe registro_entrada para ese turno
        │       ├─ Sí → alerta "Ya registrado"
        │       └─ No → INSERT en registro_entrada
        └─ Redirigir a dashboard trabajador
```

### Flujo de Permiso

```
Trabajador solicita permiso (fechas + motivo)
        │
        ▼
PermisoController::solicitarPermiso()
        └─ INSERT permiso con estado = 'pendiente'

Administrador revisa permisos pendientes
        │
        ▼
PermisoController::cambiarEstado()
        └─ UPDATE permiso SET estado = 'aprobado' | 'rechazado'
```

### Flujo del Kiosko Biométrico

```
Operador ingresa número de documento + Enter
        │
        ▼
AJAX → MarcacionController?accion=identificar&documento=X
        ├─ Buscar trabajador por documento
        ├─ Buscar último permiso (aprobado/rechazado)
        └─ Retornar JSON con datos del trabajador y estado de permiso
                │
                ▼
        Mostrar en pantalla: nombre, cargo, horario, estado de permiso
```

---

## 12. Configuración e Instalación

### Requisitos

- PHP 8.0 o superior
- MySQL 8.0
- Servidor web Apache (Laragon recomendado para desarrollo)
- Extensiones PHP: `pdo`, `pdo_mysql`

### Pasos de Instalación

**1. Colocar el proyecto en el directorio web**

```
C:\laragon\www\proyecto-clases\
```

**2. Crear la base de datos**

Desde phpMyAdmin o consola MySQL:

```sql
CREATE DATABASE sistema_marcaciones
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sistema_marcaciones;
-- Ejecutar el contenido del archivo: sql/sistema_marcaciones_sql
```

**3. Configurar la conexión**

Editar `config/database.php`:

```php
private $host     = "127.0.0.1";
private $port     = "3320";           // Verificar el puerto en el panel de Laragon
private $db_name  = "sistema_marcaciones";
private $username = "root";
private $password = "";
```

**4. Acceder al sistema**

- Página de inicio: `http://localhost/proyecto-clases/public/index.php`
- Login directo: `http://localhost/proyecto-clases/views/usuario/login.php`

**Credenciales del administrador por defecto:**
- Email: `jhoncorredor13@gmail.com`
- Contraseña: definida en el script SQL (hash bcrypt)

### Recuperación de Contraseña

Usar la opción "¿Olvidaste tu contraseña?" en el login. El sistema genera una contraseña temporal con el formato:

```
[primera_letra_del_nombre_en_minúscula] + 123456
```

Ejemplo: nombre `Admin` → contraseña temporal `a123456`.

---

*Documentación generada el 11 de mayo de 2026*
