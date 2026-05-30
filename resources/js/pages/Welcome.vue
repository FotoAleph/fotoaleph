<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';


// Declaramos la variable reactiva para el botón "Ver más"
const showFullDinamycode = ref(false);

const heroHighlights = [
    'Sitios web personalizados con foco comercial',
    'Automatización de procesos y operaciones',
    'Sistemas escalables con Laravel y frontend moderno',
];

const dinamycodeHighlights = [
    'Facturación electrónica y reportes fiscales (FEV)',
    'Optimización de ventas, registros y flujos internos',
    'Arquitectura mantenible, escalable y orientada al dato',
];

const githubHighlights = [
    'Experimentos, prototipos y proyectos personales',
    'Ideas en evolución que muestran proceso y criterio técnico',
    'Un espacio honesto para ver cómo construyo y aprendo',
];

const laravelHighlights = [
    'Permite avanzar rápido sin sacrificar estructura.',
    'Escala bien desde productos pequeños hasta plataformas complejas.',
    'Tiene un ecosistema maduro y una comunidad que acelera el desarrollo.',
];


withDefaults(
    defineProps<{
        canRegister: boolean;
        socialNetworks: Array<{ name: string; url: string; icon: string }>;
    }>(),
    {
        canRegister: true,
    },
);

</script>

<template>

    <Head title="Carlos Ramírez | Desarrollador de Software y Soluciones Web">

        <meta name="description"
            content="Especialista en desarrollo de software, automatización de procesos y soluciones digitales. Carlos Ramírez crea aplicaciones web a medida con Laravel, Vue y React." />
        <meta property="og:title" content="Desarrollador de Software y Soluciones WEB" />
        <meta property="og:descrition"
            content="soluciones de software enfocadas en automatizar procesos empresariales y modernizar operaciones de negocio">
        <meta property="og:image" content="https://fotoaleph.dinamycode.com/carlos_foto.jpg">
        <meta property="og:image:type" content="image/jpg" />
        <meta property="og:image:width" content="640" />
        <meta property="og:image:height" content="1280" />
        <meta property="og:url" content="https://fotoaleph.dinamycode.com" />
        <meta property="og:type" content="website" />

    </Head>

    <!-- Contenedor Principal -->
    <div
        class="min-h-screen relative bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans selection:bg-green-500 selection:text-white pb-12 overflow-hidden">

        <!-- Cabecera de Navegación Mejorada (Efecto Cristal) -->
        <header
            class="fixed top-0 left-0 w-full flex items-center justify-between px-6 py-4 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-900/80 border-b border-gray-200/50 dark:border-gray-800/50 transition-all">

            <!-- Marca / Logo Izquierda -->
            <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" class="w-12 h-12" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2" viewBox="0 0 47 41">
                        <path id="Mesa-de-trabajo1" d="M0 0h46.327v40.564H0z" style="fill:none"/>
                        <path id="dinamy" d="M21.939 6.63v22.156h.687q1.433 0 2.06.578.628.578.628 1.512 0 .914-.618 1.491t-2.07.578h-4.709v-1.014q-1.374.832-2.874 1.258a11.3 11.3 0 0 1-3.11.426q-4.532 0-7.299-2.709-2.766-2.708-2.766-6.746 0-4.22 2.904-7.203t7.083-2.983q1.57 0 3.08.497 1.51.496 2.982 1.491v-5.174h-.687q-1.452 0-2.07-.578t-.618-1.491q0-.933.618-1.512.618-.578 2.07-.578zm-4.022 17.652q0-2.577-1.746-4.362t-4.277-1.785q-2.51 0-4.258 1.785-1.746 1.785-1.746 4.301 0 2.292 1.57 3.764 1.569 1.471 4.434 1.471 2.845 0 4.434-1.471t1.589-3.703" style="fill:#00aeef;fill-rule:nonzero"/>
                        <path id="Code" d="M40.772 16.455q.698-.663 1.433-.664.83 0 1.367.597t.537 1.981v2.503q0 1.403-.537 1.991t-1.404.588q-.792 0-1.338-.455-.396-.342-.622-1.394-.226-1.053-1.093-1.564-1.527-.91-3.902-.91-2.733 0-4.383 1.612-1.65 1.611-1.65 4.077 0 2.276 1.584 3.593t5.26 1.318q2.413 0 3.94-.493.905-.303 1.716-1.033t1.47-.73q.791 0 1.367.588.575.588.575 1.384 0 1.29-1.753 2.446-2.601 1.725-7.616 1.725-4.505 0-7.013-1.877-3.393-2.522-3.393-6.902 0-4.152 2.752-6.873t7.183-2.721q1.602 0 2.979.303a10.3 10.3 0 0 1 2.545.91Z" style="fill:#ec008c;fill-rule:nonzero"/>
                    </svg>                   
                <p class="tracking-tight font-extrabold text-xl hidden sm:flex">
                    <span class="text-cyan-500">dinamy</span>
                    <span class="text-pink-500">Code</span>
                </p>
            </div>

            <!-- Navegación Interna (Secciones) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600 dark:text-gray-300">
                <a href="https://dinamycode.com" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Inicio</a>
                <a href="https://dinamycode.com/productos/"
                    class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Productos</a>
                <a href="https://dinamycode.com/servicios"
                    class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Servcios</a>
                <a href="https://dinamycode.com/contacto"
                    class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Contacto</a>
            </nav>

            <!-- Acciones Derecha (Auth) -->
            <div class="flex items-center gap-4">

                <Link v-if="$page.props.auth?.user" :href="dashboard()"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors hover:bg-gray-200 dark:hover:bg-gray-800 border border-transparent">
                    {{ $page.props.auth?.user.name }}
                </Link>
                <template v-else>
                    <Link :href="login()"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors hover:bg-gray-200 dark:hover:bg-gray-800 border border-transparent">
                        Iniciar sesión
                    </Link>
                    <Link v-if="canRegister" :href="register()"
                        class="hidden sm:inline-block px-4 py-1.5 rounded-md text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors shadow-sm">
                        Registrarse
                    </Link>
                </template>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main
            class="max-w-7xl mx-auto p-4 md:p-8 flex flex-col items-center justify-center relative z-10">



            <!-- SECCIÓN DE EXPERIENCIA (Estilo Mini-Cards) -->
            <article id="experiencia" class="w-full mt-16 relative z-10 scroll-mt-32">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">Experiencia</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        Puedo contarte lo que hago, pero también puedes contrastarlo con personas que ya han trabajado
                        conmigo
                        en proyectos anteriores.
                    </p>
                </div>

                <!-- Grid de Mini Cards (1 col móvil, 2 cols tablet, 4 cols desktop) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 2xl:grid-cols-6 gap-1 md:gap-6 w-full">

                    <!-- Mini Card 1: Vidrios y Estructuras JyM -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Vidrios y Estructuras JyM
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Diseño y desarrollo del sitio web
                        </p>

                        <a href="https://vidriosyestructurasjym.com.co/" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-16 h-16 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300"
                                src="https://vidriosyestructurasjym.com.co/IMG/Logo.png"
                                alt="Vidrios y Estructuras JyM">
                        </a>

                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Jairo Pulido</p>
                            <a href="tel:+573124491072"
                                class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 312 4491072
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 2: Casa Angel Eventos -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Casa Angel Eventos</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Diseño y desarrollo del sitio web
                        </p>

                        <a href="https://www.eventoscasaangel.com/" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-24 h-auto object-contain rounded drop-shadow-sm group-hover:scale-110 transition-transform duration-300"
                                src="https://www.eventoscasaangel.com/images/casaangel.svg" alt="Casa Angel Eventos">
                        </a>

                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Ruben Salgado</p>
                            <a href="tel:+573142502033"
                                class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 314 2502033
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 3: Savatechnology -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Savatechnology</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Publicidad y campañas en redes</p>

                        <a href="https://www.facebook.com/savatechnology/" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10 text-blue-600 dark:text-blue-500">
                            <svg class="w-24 h-16 fill-current group-hover:scale-110 transition-transform duration-300 drop-shadow-sm"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73.62 94.68">
                                <path
                                    d="M0,81.42,0,79H.15a5.43,5.43,0,0,0,.59,1.45,1.85,1.85,0,0,0,1.59,1A1.51,1.51,0,0,0,3.44,81a1.58,1.58,0,0,0,.44-1.14,1.9,1.9,0,0,0-.1-.61,1.64,1.64,0,0,0-.26-.53,2,2,0,0,0-.47-.44,9,9,0,0,0-.94-.53c-.37-.19-.65-.34-.85-.47a2.78,2.78,0,0,1-.47-.39,1.89,1.89,0,0,1-.5-1.34,2.31,2.31,0,0,1,.19-.93A2.13,2.13,0,0,1,1,73.85a2.08,2.08,0,0,1,1.46-.57,2.1,2.1,0,0,1,.51,0,4.85,4.85,0,0,1,.62.22l.4.15.19,0q.19,0,.27-.3h.14l0,2.32H4.52a8,8,0,0,0-.27-.85A2.72,2.72,0,0,0,4,74.36a2.09,2.09,0,0,0-.63-.68,1.49,1.49,0,0,0-.79-.22,1.26,1.26,0,0,0-1,.45,1.27,1.27,0,0,0-.25.42,1.46,1.46,0,0,0-.09.49c0,.65.42,1.18,1.25,1.57l.82.4a3.67,3.67,0,0,1,1.37,1,2.24,2.24,0,0,1,.48,1.42,2.55,2.55,0,0,1-.21,1,2.25,2.25,0,0,1-.92,1,2.64,2.64,0,0,1-1.42.37,3.59,3.59,0,0,1-1.45-.3l-.43-.18-.16,0a.38.38,0,0,0-.39.28Z">
                                </path>
                                <path
                                    d="M14,81.44H10.18v-.13a1.6,1.6,0,0,0,.72-.18.7.7,0,0,0,.37-.63,1.9,1.9,0,0,0-.17-.64l-.1-.24-.56-1.38H7.88l-.31.81-.14.36a2.81,2.81,0,0,0-.27,1,.91.91,0,0,0,.1.4.85.85,0,0,0,.26.3,1.14,1.14,0,0,0,.58.19v.13H5.49v-.13A1.14,1.14,0,0,0,6,81.16a1.66,1.66,0,0,0,.46-.43,3.77,3.77,0,0,0,.36-.62c.12-.25.28-.63.47-1.13l2.14-5.5h.33l2.54,6.13a8.81,8.81,0,0,0,.45,1,1.59,1.59,0,0,0,.37.45.88.88,0,0,0,.32.16,3.1,3.1,0,0,0,.54.1ZM10.35,78,9.13,75,8,78Z">
                                </path>
                                <path
                                    d="M13.69,73.48h3.85v.13a2.12,2.12,0,0,0-.63.14.75.75,0,0,0-.29.27.8.8,0,0,0-.11.4,1.12,1.12,0,0,0,0,.27c0,.08.08.24.18.51l1.72,4.69,1.68-4.12a4,4,0,0,0,.35-1.26,1.42,1.42,0,0,0-.07-.42.7.7,0,0,0-.19-.29.55.55,0,0,0-.26-.14,1.15,1.15,0,0,0-.36,0v-.13h2.6v.13a1.53,1.53,0,0,0-.56.16,1.62,1.62,0,0,0-.45.39,3.57,3.57,0,0,0-.31.51c-.1.22-.25.55-.44,1L18,81.44H17.8l-2.33-6A5.68,5.68,0,0,0,14.74,74a1.13,1.13,0,0,0-.42-.29,1.91,1.91,0,0,0-.63-.09Z">
                                </path>
                                <path
                                    d="M30.4,81.44H26.56v-.13a1.7,1.7,0,0,0,.73-.18.71.71,0,0,0,.36-.63,1.84,1.84,0,0,0-.16-.64l-.1-.24-.56-1.38H24.26l-.3.81-.15.36a3,3,0,0,0-.26,1,.9.9,0,0,0,.09.4,1,1,0,0,0,.26.3,1.18,1.18,0,0,0,.59.19v.13H21.87v-.13a1.21,1.21,0,0,0,.55-.15,1.79,1.79,0,0,0,.45-.43,3.17,3.17,0,0,0,.37-.62c.11-.25.27-.63.47-1.13l2.13-5.5h.33l2.54,6.13a7,7,0,0,0,.46,1,1.42,1.42,0,0,0,.36.45.92.92,0,0,0,.33.16,3.1,3.1,0,0,0,.54.1ZM26.73,78l-1.22-3-1.16,3Z">
                                </path>
                                <path
                                    d="M6.75,64.75V43.56H7.87A43.22,43.22,0,0,0,12.8,56q5.43,8.46,12.68,8.46a11.24,11.24,0,0,0,7.67-2.85q3.52-3.14,3.52-8.91a13.72,13.72,0,0,0-2.53-8.23A16.94,16.94,0,0,0,30.58,41a78.7,78.7,0,0,0-7.22-4.42q-8.86-5-11.82-8.38A16.24,16.24,0,0,1,7.31,17.14a17,17,0,0,1,4.24-11.6A16.35,16.35,0,0,1,24.46,0a16.66,16.66,0,0,1,6.71,1.35l2.4,1.12a5.44,5.44,0,0,0,2,.45c.9,0,1.74-.75,2.55-2.25H39.5l.77,17H38.79a37.4,37.4,0,0,0-1.65-5.76,16.89,16.89,0,0,0-2.5-4.19,16.53,16.53,0,0,0-5.07-4.45,11.54,11.54,0,0,0-5.7-1.61,8.78,8.78,0,0,0-6.72,3,10.62,10.62,0,0,0-2.78,7.45,10,10,0,0,0,2.5,6.62,22.85,22.85,0,0,0,7.71,5.43L32,27.77a30.89,30.89,0,0,1,5.14,3.06c.76,2.22,6.91,19.25,7.66,21.1a17.34,17.34,0,0,1-4.28,8.17,18.3,18.3,0,0,1-14.12,6A25.17,25.17,0,0,1,17,64.15c-2.07-.85-3.32-1.34-3.74-1.46a5.66,5.66,0,0,0-1.62-.19,4.07,4.07,0,0,0-3.75,2.25Z"
                                    style="fill:currentColor"></path>
                            </svg>
                        </a>

                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Andres Rojas</p>
                            <a href="tel:+573173671746"
                                class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 317 3671746
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 4: Lab Biotek -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Laboratorio clínico Biotek
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Sistema de registro de pacientes y
                            bitácora</p>

                        <a href="https://maps.app.goo.gl/btdcnCEd7i2tpbat8" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-20 h-20 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300 rounded-full bg-white p-1 border border-gray-100"
                                src="https://biotek.dinamycode.com/Biotek.png" alt="Laboratorio Biotek">
                        </a>

                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Claudia Buitrago</p>
                            <a href="tel:+573207001403"
                                class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 320 7001403
                            </a>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">JAD colombian</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">WEB site</p>

                        <a href="https://jadcolombia.com/" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-20 h-20 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300 rounded-full bg-white p-1 border border-gray-100"
                                src="https://jadcolombia.com/img/icon-trasparente.png" alt="JAD">
                        </a>

                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg z-10">INNOVAR </h3>
                        <h2 class="font-bold text-gray-900 dark:text-white text-md mb-1 z-10"> SOLUCIONES FINANCIERAS
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">WEB site</p>

                        <a href="https://innovarsoluciones.com.co" target="_blank" rel="noopener noreferrer"
                            class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-20 h-20 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300 rounded-full bg-white p-1 border border-gray-100"
                                src="https://innovarsoluciones.com.co/img/innovar.png" alt="Innovar">
                        </a>

                    </div>



                </div>
            </article>



            <!-- SECCIÓN HERO Y NUBE DE ETIQUETAS -->
            <section id="inicio"
                class="w-full p-2 mt-4 relative flex flex-col lg:flex-row items-center justify-between min-h-[50vh] lg:min-h-0 scroll-mt-32">

                <!-- Forma Gráfica (Fondo en móvil, Izquierda en Desktop) -->
                <div
                    class="absolute inset-0 lg:relative lg:inset-auto lg:w-2/5 flex items-center justify-center opacity-15  dark:opacity-100 lg:opacity-60 pointer-events-none z-0 lg:order-2">
                            <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100%" height="100%" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2" viewBox="0 0 47 41">
            <path id="Mesa-de-trabajo1" d="M0 0h46.327v40.564H0z" style="fill:none"/>
            <path id="dinamy" d="M21.939 6.63v22.156h.687q1.433 0 2.06.578.628.578.628 1.512 0 .914-.618 1.491t-2.07.578h-4.709v-1.014q-1.374.832-2.874 1.258a11.3 11.3 0 0 1-3.11.426q-4.532 0-7.299-2.709-2.766-2.708-2.766-6.746 0-4.22 2.904-7.203t7.083-2.983q1.57 0 3.08.497 1.51.496 2.982 1.491v-5.174h-.687q-1.452 0-2.07-.578t-.618-1.491q0-.933.618-1.512.618-.578 2.07-.578zm-4.022 17.652q0-2.577-1.746-4.362t-4.277-1.785q-2.51 0-4.258 1.785-1.746 1.785-1.746 4.301 0 2.292 1.57 3.764 1.569 1.471 4.434 1.471 2.845 0 4.434-1.471t1.589-3.703" style="fill:#00aeef;fill-rule:nonzero"/>
            <path id="Code" d="M40.772 16.455q.698-.663 1.433-.664.83 0 1.367.597t.537 1.981v2.503q0 1.403-.537 1.991t-1.404.588q-.792 0-1.338-.455-.396-.342-.622-1.394-.226-1.053-1.093-1.564-1.527-.91-3.902-.91-2.733 0-4.383 1.612-1.65 1.611-1.65 4.077 0 2.276 1.584 3.593t5.26 1.318q2.413 0 3.94-.493.905-.303 1.716-1.033t1.47-.73q.791 0 1.367.588.575.588.575 1.384 0 1.29-1.753 2.446-2.601 1.725-7.616 1.725-4.505 0-7.013-1.877-3.393-2.522-3.393-6.902 0-4.152 2.752-6.873t7.183-2.721q1.602 0 2.979.303a10.3 10.3 0 0 1 2.545.91Z" style="fill:#ec008c;fill-rule:nonzero"/>
        </svg>
                </div>

                <!-- Texto (Izquierda en Desktop, Centrado en Móvil) -->
                <div class="w-full lg:w-3/5 lg:pr-12 text-center lg:text-left relative z-10 lg:order-1 pt-12 lg:pt-0">
                    <h1
                        class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-6">
                        Soluciones Digitales <br class="hidden md:block lg:hidden xl:block" />
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-700 dark:from-red-400 dark:to-red-500">Orientadas
                            a Resultados</span>
                    </h1>

                    <div class="space-y-5 mb-10 relative z-10">
                        <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 leading-relaxed max-w-3xl">
                            Desarrollo software y soluciones digitales con un objetivo claro: que la tecnología se
                            traduzca en
                            resultados medibles para el negocio.
                        </p>
                        <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl">
                            Diseño sitios web, aplicaciones a medida y automatizaciones que mejoran la presencia
                            digital,
                            ordenan la operación y ayudan a convertir visitas en oportunidades reales.
                        </p>

                    </div>

                    <!-- Nube de Palabras / Etiquetas Interactiva -->
                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 md:gap-4 relative z-10">
                        <span
                            class="px-5 py-2 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-red-200 dark:border-red-800/50">Laravel</span>
                        <span
                            class="px-5 py-2 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-blue-200 dark:border-blue-800/50">React</span>
                        <span
                            class="px-5 py-2 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-emerald-200 dark:border-emerald-800/50">Vue.js</span>
                        <span
                            class="px-5 py-2 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-orange-200 dark:border-orange-800/50">Svelte</span>
                        <span
                            class="px-5 py-2 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-purple-200 dark:border-purple-800/50">Automatización</span>
                        <span
                            class="px-5 py-2 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-yellow-200 dark:border-yellow-800/50">SEO</span>
                        <span
                            class="px-5 py-2 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-indigo-200 dark:border-indigo-800/50">Sistemas
                            Empresariales</span>
                        <span
                            class="px-5 py-2 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-teal-200 dark:border-teal-800/50">Escalabilidad</span>
                        <span
                            class="px-5 py-2 rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-gray-300 dark:border-gray-700">Código
                            Limpio</span>
                    </div>
                </div>
            </section>


            <!-- SECCIÓN DE CONTACTO (Call to Action) -->
            <section id="contacto" class="w-full mt-32 mb-16 relative z-10 scroll-mt-32">
                <div
                    class="bg-white dark:bg-gray-800 rounded-[2rem] p-10 md:p-16 text-center shadow-xl border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <!-- Elementos decorativos de fondo -->
                    <div
                        class="absolute top-0 right-0 p-32 bg-green-500/10 dark:bg-green-500/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 p-32 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none">
                    </div>

                    <div class="relative z-10 max-w-2xl mx-auto">
                        <span
                            class="inline-block px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-xs font-bold rounded-full tracking-wider uppercase mb-4 border border-green-200 dark:border-green-800/50">
                            Contacto
                        </span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-6">¿Listo para
                            dar el
                            siguiente paso?</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-10">
                            Ya sea que tengas una idea en mente, necesites optimizar un proceso en tu empresa, o
                            simplemente
                            quieras saludar. ¡Me encantaría escucharte!
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                            <!-- Botón Correo -->
                            <a href="mailto:info@dinamycode.com"
                                class="w-full sm:w-auto px-8 py-3.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl shadow-md hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                info@dinamycode.com
                            </a>
                            <!-- Botón WhatsApp -->
                            <a href="https://wa.me/573014819820" target="_blank" rel="noopener noreferrer"
                                class="w-full sm:w-auto px-8 py-3.5 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                                </svg>
                                301 481 9820
                            </a>
                        </div>
                    </div>
                </div>
            </section>


        </main>

        <!-- SPREAD / Fondo Vectorizado Inferior Original -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-0 pointer-events-none">
            <!-- Capa Decorativa Onda de Fondo -->
            <svg class="relative block w-[calc(100%+1.3px)] h-[120px] md:h-[220px]" data-name="Layer 1"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path
                    d="M0,0V46.29c47.79,22.2,103.59,32.15,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                    class="fill-gray-200 dark:fill-gray-800 opacity-50"></path>
                <path
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                    class="fill-gray-300 dark:fill-gray-950 opacity-40"></path>
            </svg>
        </div>

        <!-- Footer simple sobre el spread vectorizado -->
        <footer
            class="absolute bottom-0 w-full pb-6 flex items-center justify-center text-sm text-gray-600 dark:text-gray-400 z-10">
            <div class="flex items-center gap-3">

                <!-- Redes Sociales Rotativas -->
                <div class="flex items-center gap-3 pr-3 border-r border-gray-300 dark:border-gray-700">
                    <div v-for="network in socialNetworks" :key="network.name">
                        <a :href="network.url" target="_blank" rel="noopener noreferrer"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors duration-300"
                            v-html="network.icon">
                        </a>
                    </div>
                </div>

                <!-- Copyright Responsivo -->
                <p>
                    &copy; {{ new Date().getFullYear() }}
                    <span class="hidden sm:inline">Carlos Ramírez. Todos los derechos reservados.</span>
                    <span class="sm:hidden">Carlos R.</span>
                </p>

            </div>
        </footer>

    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
