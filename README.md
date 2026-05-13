# MK Software - Sistema de Gestión de Marcaciones y Asistencia

## Descripción
MK Software es una solución integral diseñada para la automatización y control del registro de asistencia laboral. El sistema permite gestionar de manera eficiente los turnos rotativos, permisos y marcaciones de los trabajadores, utilizando tecnologías web modernas y soporte para identificación biométrica.

---

## Objetivos del Proyecto

### Objetivo General
Optimizar el control de asistencia y la gestión administrativa de los recursos humanos mediante una plataforma digital centralizada y automatizada.

### Objetivos Específicos
*   **Automatización de Marcaciones**: Implementar un sistema de registro mediante biometría (huella dactilar) para garantizar la veracidad de la asistencia.
*   **Gestión de Turnos**: Facilitar la asignación y visualización de turnos rotativos para el personal operativo y administrativo.
*   **Control de Permisos**: Digitalizar el flujo de solicitud y aprobación de permisos, permitiendo una comunicación fluida entre trabajador y administrador.
*   **Generación de Reportes**: Proporcionar herramientas analíticas para la toma de decisiones basadas en el historial de puntualidad y ausentismo.

---

## Alcance del Software

El sistema MK Software abarca tres áreas fundamentales:

### 1. Panel Administrativo
*   **Gestión de Usuarios**: Creación, edición y control de acceso de administradores y trabajadores.
*   **Módulo de Turnos**: Programación masiva de horarios rotativos (Mañana, Tarde, Noche, Oficina).
*   **Módulo de Permisos**: Visualización de solicitudes en tiempo real con capacidad de aprobación o rechazo inmediato.
*   **Dashboard de Estadísticas**: Visualización de métricas clave sobre el estado de la planta.

### 2. Panel del Trabajador
*   **Consulta de Turnos**: Los empleados pueden ver sus horarios asignados para la semana o el mes.
*   **Solicitud de Permisos**: Interfaz amigable para enviar peticiones de permisos con motivos y fechas específicas.
*   **Estado de Solicitudes**: Seguimiento en tiempo real sobre la aprobación de sus permisos.

### 3. Kiosko de Registro (Terminal Biométrica)
*   **Identificación Biométrica**: Registro de entrada y salida mediante huella dactilar.
*   **Notificaciones Instantáneas**: Visualización de "Notas" sobre permisos aceptados o rechazados al momento de la marcación.
*   **Feedback Visual**: Pantalla optimizada para kioscos que muestra datos del trabajador y confirmación de la marcación.

---

## Estructura del Proyecto
```text
proyecto-clases/
├── config/             # Configuración de base de datos y constantes
├── controllers/        # Lógica de negocio (MVC)
├── models/             # Interacción con la base de datos (Entidades)
├── views/              # Interfaz de usuario (HTML/PHP)
│   ├── dashboard/      # Vistas específicas del administrador
│   ├── layouts/        # Componentes reutilizables de la interfaz
│   └── usuario/        # Vistas de perfil y autenticación
├── public/             # Punto de entrada y archivos públicos
├── sql/                # Scripts de creación y dump de la base de datos
├── img/                # Recursos gráficos y logos
└── Documentacion/      # Manuales y guías del sistema
```

---

## Tecnologías Utilizadas
*   **Backend**: PHP 8.x (Arquitectura MVC).
*   **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6+).
*   **Base de Datos**: MySQL / MariaDB.
*   **Librerías**: SweetAlert2 (Notificaciones), FontAwesome 6 (Iconografía).
*   **Servidor Recomendado**: Laragon / XAMPP sobre Windows.