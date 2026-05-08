<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lagricola - Sistema de Marcaciones</title>

    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .slide {
            display: none;
        }

        .slide.active {
            display: block;
            animation: fadeIn .8s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- NAV -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center h-16">
            <img src="../img/logo.png" class="h-12">

            <div class="space-x-6 hidden md:flex">
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#funciones">Funciones</a>
                <a href="../views/usuario/login.php"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Ingresar
                </a>
            </div>
        </div>
    </nav>

    <!-- SLIDER -->
   <section id="inicio" class="relative h-[500px] text-white">
       <div class="slide active h-full relative">
           <img src="../img/marcacion.png"
                class="absolute w-full h-full object-cover brightness-100">
           <div class="flex flex-col justify-center items-center h-full text-center">
               <h1 class="text-5xl font-bold mb-4">
                    Sistema de Marcaciones
                </h1>
                <p class="text-lg max-w-xl">
                    Control eficiente de asistencia y turnos para el sector agropecuario.
                </p>
            </div>
        </div>

        <button onclick="changeSlide(-1)" class="absolute left-5 top-1/2 bg-black/40 p-3 rounded-full">
            <i class="fas fa-chevron-left"></i>
        </button>

        <button onclick="changeSlide(1)" class="absolute right-5 top-1/2 bg-black/40 p-3 rounded-full">
            <i class="fas fa-chevron-right"></i>
        </button>
    </section>

    <!-- NOSOTROS -->
    <section id="nosotros" class="py-20 max-w-6xl mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-10">

            <div class="bg-white p-8 shadow rounded-xl border-t-4 border-green-600">
                <h2 class="text-2xl font-bold mb-3">Misión</h2>
                <p>
                    Facilitar el control de asistencia de trabajadores mediante
                    herramientas tecnológicas modernas, mejorando la eficiencia
                    operativa en el sector agrícola.
                </p>
            </div>

            <div class="bg-white p-8 shadow rounded-xl border-t-4 border-green-400">
                <h2 class="text-2xl font-bold mb-3">Visión</h2>
                <p>
                    Ser el sistema líder en gestión de marcaciones agropecuarias,
                    garantizando precisión, seguridad y confiabilidad.
                </p>
            </div>

        </div>
    </section>

    <!-- FUNCIONES -->
    <section id="funciones" class="bg-gray-100 py-20">
        <div class="max-w-6xl mx-auto px-6 text-center">

            <h2 class="text-3xl font-bold mb-10">Funciones del Sistema</h2>

            <div class="grid md:grid-cols-3 gap-8">

                <div class="bg-white p-6 rounded-xl shadow">
                    <i class="fas fa-user-check text-3xl text-green-600 mb-4"></i>
                    <h3 class="font-bold mb-2">Control de Asistencia</h3>
                    <p>Registro de entrada y salida en tiempo real.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <i class="fas fa-users text-3xl text-green-600 mb-4"></i>
                    <h3 class="font-bold mb-2">Gestión de Usuarios</h3>
                    <p>Administración de trabajadores y roles.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <i class="fas fa-clock text-3xl text-green-600 mb-4"></i>
                    <h3 class="font-bold mb-2">Marcaciones</h3>
                    <p>Historial completo de turnos y registros.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-300 py-10 text-center">
        <h3 class="text-lg font-bold text-white mb-2">Lagricola</h3>
        <p>Sistema de Marcaciones Agropecuarias</p>
        <p class="text-xs mt-4">&copy; 2026 Todos los derechos reservados</p>
    </footer>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');

        function changeSlide(direction) {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(() => changeSlide(1), 5000);
    </script>

</body>

</html>