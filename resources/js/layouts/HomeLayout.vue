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
   
    <!-- Contenedor Principal -->
    <div
        class="min-h-screen relative bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans selection:bg-green-500 selection:text-white pb-12 overflow-hidden">

        <!-- Cabecera de Navegación Mejorada (Efecto Cristal) -->
        <header
            class="fixed top-0 left-0 w-full flex items-center justify-between px-6 py-4 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-900/80 border-b border-gray-200/50 dark:border-gray-800/50 transition-all">

            <!-- Marca / Logo Izquierda -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10">
                    <img src="dina.svg" alt="DC">
                </div>
                <span class="font-extrabold text-xl text-cyan-400 tracking-tight hidden sm:block">Dinamy<span
                        class="text-pink-500">Code</span></span>
            </div>

            <!-- Navegación Interna (Secciones) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600 dark:text-gray-300">
                <a href="#inicio" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Inicio</a>
                <a href="#proyectos"
                    class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Proyectos</a>
                <a href="#experiencia"
                    class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Experiencia</a>
                <a href="#contacto"
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

    <component>
        <slot />
    </component>

    </div>
 </template>
