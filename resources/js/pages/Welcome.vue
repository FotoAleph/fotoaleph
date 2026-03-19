<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { rand } from '@vueuse/core';

// Declaramos la variable reactiva para el botón "Ver más"
const showFullDinamycode = ref(false);

// Batería completa de redes sociales con sus paths SVG (ViewBox 24x24)
const socialNetworks = [
    { name: 'TikTok', url: 'https://www.tiktok.com/@carlos.alberto.ra295', icon: 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.22-.76 4.41-2.06 6.13-1.61 2.14-4.14 3.55-6.84 3.86-2.58.3-5.27-.12-7.5-1.57-2.12-1.38-3.64-3.51-4.25-5.96-.58-2.32-.42-4.82.46-7.06 1.03-2.6 3.14-4.7 5.75-5.71 2.05-.79 4.34-1 6.51-.54v4.06c-1.2-.18-2.45-.16-3.62.18-1.41.41-2.69 1.34-3.46 2.59-.76 1.25-1 2.76-.71 4.21.28 1.45 1.13 2.74 2.36 3.55 1.24.81 2.8 1.07 4.26.75 1.63-.35 3.06-1.42 3.86-2.88.58-1.07.88-2.3.89-3.54.02-7.81.01-15.62.01-23.43z' },
    { name: 'Facebook', url: 'https://www.facebook.com/FotoAleph', icon: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z' },
    { name: 'Google Maps', url: 'https://maps.app.goo.gl/iWiMn7bWFLi24Rxy5', icon: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z' },
    { name: 'LinkedIn', url: 'https://www.linkedin.com/in/carlos-alberto-ramirez/', icon: 'M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z' },
    { name: 'GitHub', url: 'https://github.com/FotoAleph', icon: 'M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z' },
    { name: 'YouTube', url: 'https://www.youtube.com/@FotoAleph', icon: 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z' },
    { name: 'Instagram', url: 'https://www.instagram.com/rlcirilo/', icon: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z' },
    { name: 'Discord', url: 'https://discord.com/fotoaleph', icon: 'M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z' },
    { name: 'X / Twitter', url: 'https://x.com/rlcirilo', icon: 'M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z' },
    { name: 'Reddit', url: 'https://www.reddit.com/user/Known-Gate-2912/', icon: 'M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .883.175 1.195.465 1.2-.843 2.835-1.4 4.632-1.484l.882-4.136a.326.326 0 0 1 .371-.256l3.14.661zm-7.668 5.485c-.947 0-1.714.767-1.714 1.714 0 .947.767 1.714 1.714 1.714.947 0 1.714-.767 1.714-1.714 0-.947-.767-1.714-1.714-1.714zm5.314 0c-.947 0-1.714.767-1.714 1.714 0 .947.767 1.714 1.714 1.714.947 0 1.714-.767 1.714-1.714 0-.947-.767-1.714-1.714-1.714zm-2.657 4.145c-1.577 0-2.857.85-2.857 1.9 0 1.05 1.28 1.9 2.857 1.9 1.577 0 2.857-.85 2.857-1.9 0-1.05-1.28-1.9-2.857-1.9z' },
    { name: 'Figma', url: 'https://www.figma.com/@carlosramirez9', icon: 'M8 2h4.5a4 4 0 0 1 0 8H8V2zm0 8h4.5a4 4 0 0 1 0 8H8v-8zm0 8v2a4 4 0 0 0 4 4 4 4 0 0 0 0-8H8v2zm-4-8a4 4 0 0 1 4-4v8a4 4 0 0 1-4-4zm0-8a4 4 0 0 1 4-4v8a4 4 0 0 1-4-4z' },
    { name: 'Canva', url: 'https://www.canva.com/brand/kAGSEiMCzKA', icon: 'M12 22.5A10.5 10.5 0 1 0 1.5 12 10.5 10.5 0 0 0 12 22.5Zm0-2A8.5 8.5 0 1 1 20.5 12 8.5 8.5 0 0 1 12 20.5Zm3-11.5c-2.8 0-4.5 2.2-4.5 5h1.5c0-1.8 1.1-3.5 3-3.5v-1.5Zm-4.5 5c0 1.8-1.1 3.5-3 3.5v1.5c2.8 0 4.5-2.2 4.5-5H10.5Zm-3-3.5c1.9 0 3 1.7 3 3.5h1.5c0-2.8-1.7-5-4.5-5v1.5Z' },
    { name: 'Chess.com', url: 'https://www.chess.com/member/rlcirilo88', icon: 'M12 2.5a3 3 0 0 0-3 3c0 1.34.88 2.47 2.08 2.87-1.29 1.1-3.08 3.93-3.08 7.63v.5h8v-.5c0-3.7-1.79-6.53-3.08-7.63 1.2-.4 2.08-1.53 2.08-2.87a3 3 0 0 0-3-3zm-5 14v1.5a1.5 1.5 0 0 0 1.5 1.5h7a1.5 1.5 0 0 0 1.5-1.5V16.5h-10z' }
];

const activeSocials = ref<{name: string, url: string, icon: string}[]>([]);

onMounted(() => {

    // Algoritmo matemático simple para asignar pares consistentes sin repetirse el mismo día
   console.log(socialNetworks.length)
    const index1 = Math.floor(Math.random() * 7);
    const index3= Math.floor(Math.random() * 7) + 6
    const index2 = index3  == index1 ? 12 : index3 ;
    activeSocials.value = [socialNetworks[index1], socialNetworks[index2]];
});

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Carlos Ramirez | Desarrollador de Software y Soluciones Web">
        <meta name="description" content="Especialista en desarrollo de software, automatización de procesos y soluciones digitales. Carlos Ramirez crea aplicaciones web a medida con Laravel, Vue y React." />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Contenedor Principal -->
    <div class="min-h-screen relative bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans selection:bg-green-500 selection:text-white pb-12 overflow-hidden">
        
        <!-- Cabecera de Navegación Mejorada (Efecto Cristal) -->
        <header class="fixed top-0 left-0 w-full flex items-center justify-between px-6 py-4 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-900/80 border-b border-gray-200/50 dark:border-gray-800/50 transition-all">
            
            <!-- Marca / Logo Izquierda -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center text-white font-bold text-xl shadow-sm">
                   CR
                </div>
                <span class="font-extrabold text-xl tracking-tight hidden sm:block">Carlos<span class="text-red-600 dark:text-green-500">Ramirez</span></span>
            </div>

            <!-- Navegación Interna (Secciones) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600 dark:text-gray-300">
                <a href="#inicio" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Inicio</a>
                <a href="#proyectos" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Proyectos</a>
                <a href="#experiencia" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Experiencia</a>
                <a href="#contacto" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Contacto</a>
            </nav>

            <!-- Acciones Derecha (Auth) -->
            <div class="flex items-center gap-4">

                <Link v-if="$page.props.auth?.user" :href="dashboard()"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors hover:bg-gray-200 dark:hover:bg-gray-800 border border-transparent">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors hover:bg-gray-200 dark:hover:bg-gray-800 border border-transparent">
                        Entrar
                    </Link>
                    <Link v-if="canRegister" :href="register()"
                        class="hidden sm:inline-block px-4 py-1.5 rounded-md text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors shadow-sm">
                        Registrarse
                    </Link>
                </template>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-12 flex flex-col items-center justify-center relative z-10">
            
            <!-- SECCIÓN HERO Y NUBE DE ETIQUETAS -->
            <section id="inicio" class="w-full mb-24 relative flex flex-col lg:flex-row items-center justify-between min-h-[50vh] lg:min-h-0 scroll-mt-32">
                
                <!-- Forma Gráfica (Fondo en móvil, Izquierda en Desktop) -->
                <div class="absolute inset-0 lg:relative lg:inset-auto lg:w-2/5 flex items-center justify-center opacity-15 dark:opacity-10 lg:opacity-100 pointer-events-none z-0 lg:order-2">
                    <svg class="w-[150%] max-w-none lg:w-full lg:max-w-md transform translate-x-10 lg:translate-x-0" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <path fill="url(#gradient-blob)" d="M54.8,-60.1C70.2,-46.8,81,-26.7,82.4,-5.9C83.8,14.9,75.8,36.4,61.4,52.3C47,68.2,26.2,78.5,4.7,74.7C-16.9,70.9,-37.8,53,-52.1,35.6C-66.4,18.2,-74.1,1.3,-71.3,-14.2C-68.5,-29.7,-55.2,-43.8,-40.1,-57.1C-25,-70.4,-8.1,-82.9,6.5,-90.6C21.1,-98.3,42.2,-101.2,54.8,-60.1Z" transform="translate(100 100) scale(1.1)" />
                        <defs>
                            <linearGradient id="gradient-blob" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#86efac" /> <!-- green-300 -->
                                <stop offset="100%" stop-color="#059669" /> <!-- emerald-600 -->
                            </linearGradient>
                        </defs>
                        <!-- Capa superpuesta para más profundidad -->
                        <path fill="currentColor" class="text-blue-500/20 dark:text-blue-400/20" d="M38.1,-46.3C52.4,-35.1,69.2,-25.9,73.1,-12.9C77,0.1,68,16.8,56.1,30.3C44.2,43.8,29.4,54.1,12.7,59.3C-4,64.5,-22.6,64.6,-37.3,56.6C-52,48.6,-62.8,32.5,-66.9,15.1C-71,-2.3,-68.4,-21,-58.5,-35.2C-48.6,-49.4,-31.4,-59.1,-16.1,-63.4C-0.8,-67.7,12.6,-66.6,23.8,-57.5Z" transform="translate(100 100) scale(1.2) rotate(45)" />
                    </svg>
                </div>

                <!-- Texto (Izquierda en Desktop, Centrado en Móvil) -->
                <div class="w-full lg:w-3/5 lg:pr-12 text-center lg:text-left relative z-10 lg:order-1 pt-12 lg:pt-0">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-6">
                        Soluciones Digitales <br class="hidden md:block lg:hidden xl:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-emerald-700 dark:from-green-400 dark:to-emerald-500">Orientadas a Resultados</span>
                    </h1>
                    
                    <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 leading-relaxed mb-10 text-justify lg:text-left relative z-10">
                        Especialista en desarrollo de software y soluciones digitales orientadas a resultados. Diseño y desarrollo sitios web personalizados y aplicaciones a medida que convierten visitantes en clientes, optimizando la presencia digital y la eficiencia operativa de las organizaciones. Experiencia en automatización de procesos, desarrollo de sistemas empresariales y crecimiento digital mediante estrategias de SEO y visibilidad online. Trabajo con tecnologías modernas y frameworks robustos como Laravel, aprovechando su ecosistema para crear soluciones escalables, seguras y de código limpio, integrables con interfaces modernas en React, Vue o Svelte. Mi enfoque combina tecnología, diseño y estrategia digital para impulsar productividad, control operativo y expansión digital de los negocios.
                    </p>

                    <!-- Nube de Palabras / Etiquetas Interactiva -->
                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 md:gap-4 relative z-10">
                        <span class="px-5 py-2 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-red-200 dark:border-red-800/50">Laravel</span>
                        <span class="px-5 py-2 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-blue-200 dark:border-blue-800/50">React</span>
                        <span class="px-5 py-2 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-emerald-200 dark:border-emerald-800/50">Vue.js</span>
                        <span class="px-5 py-2 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-orange-200 dark:border-orange-800/50">Svelte</span>
                        <span class="px-5 py-2 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-purple-200 dark:border-purple-800/50">Automatización</span>
                        <span class="px-5 py-2 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-yellow-200 dark:border-yellow-800/50">SEO</span>
                        <span class="px-5 py-2 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-indigo-200 dark:border-indigo-800/50">Sistemas Empresariales</span>
                        <span class="px-5 py-2 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-teal-200 dark:border-teal-800/50">Escalabilidad</span>
                        <span class="px-5 py-2 rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-semibold text-sm hover:scale-110 hover:-translate-y-1 transition-all cursor-default shadow-sm border border-gray-300 dark:border-gray-700">Código Limpio</span>
                    </div>
                </div>
            </section>
            
            <!-- Grid Asimétrico (Estilo Bento Box) -->
            <div id="proyectos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 w-full auto-rows-min scroll-mt-32">
                
                <!-- Tarjeta 1: Dinamycode (Destacada, ocupa 2 columnas y 2 filas) -->
                <div class="md:col-span-2 lg:col-span-2 lg:row-span-2 bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col transition-all hover:-translate-y-1 hover:shadow-xl duration-500 group relative">
                    <!-- Efecto de resplandor sutil -->
                    <div class="absolute -top-32 -right-32 w-96 h-96 bg-green-400/10 dark:bg-green-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="p-8 md:p-10 flex flex-col justify-around h-full">
                        <div class="flex-1 flex flex-col justify-center relative z-10">
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-full w-fit mb-6 tracking-wide uppercase border border-green-200 dark:border-green-800/50">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Proyecto Principal
                            </span>
                            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-6 tracking-tight">Dinamycode</h2>
                            
                            <div class="text-gray-600 dark:text-gray-300 text-sm md:text-base leading-relaxed text-justify mb-8">
                                <p>
                                    En Dinamycode desarrollo soluciones de software enfocadas en la automatización de procesos empresariales y la modernización de operaciones digitales. Este proyecto nace de la combinación entre mi interés por la ingeniería de software y la oportunidad de colaborar con profesionales que admiro, en un entorno orientado a la construcción de tecnología útil para el contexto empresarial colombiano.
                                </p>
                                
                                <div v-show="showFullDinamycode" class="mt-4 space-y-4">
                                    <p>
                                        Actualmente trabajamos en el diseño y desarrollo de una plataforma que busca simplificar y automatizar procesos relacionados con la facturación electrónica y los reportes fiscales (FEV), al tiempo que optimiza los flujos de ventas, registros operativos y gestión de información dentro de las organizaciones. La solución se construye sobre arquitecturas web modernas, aprovechando frameworks robustos y prácticas de desarrollo que priorizan la escalabilidad, mantenibilidad y claridad del código.
                                    </p>
                                    <p>
                                        El objetivo es crear herramientas que permitan a las empresas reducir fricción operativa, mejorar el control de sus procesos y acelerar su transformación digital, integrando tecnología web actual, automatización y experiencias de usuario orientadas a la eficiencia y la toma de decisiones basada en datos.
                                    </p>
                                </div>
                                
                                <button @click="showFullDinamycode = !showFullDinamycode" class="mt-3 text-green-600 dark:text-green-400 font-semibold hover:text-green-800 dark:hover:text-green-300 focus:outline-none transition-colors inline-flex items-center gap-1">
                                    {{ showFullDinamycode ? 'Ver menos' : 'Ver más...' }}
                                    <svg :class="{'rotate-180': showFullDinamycode}" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex-1 flex items-center justify-center relative z-10 lg:pl-6 mt-auto mb-auto">
                            <a href="https://dinamycode.com" target="_blank" rel="noopener noreferrer" class="block w-full overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-600 shadow-lg hover:scale-[1.03] transition-transform duration-500 ring-4 ring-white dark:ring-gray-800">
                                <img class="w-full object-cover aspect-auto lg:h-full lg:object-cover" 
                                    src="https://dinamycode.com/img/dinamycode_title.png" 
                                    loading="lazy" 
                                    alt="Dinamycode Logo">
                            </a>
                        </div>
                        <div class="hidden lg:flex items-center gap-3 mt-6">
                            <a href="#" aria-label="Facebook de DinamyCode" class="w-9 h-9 rounded-full border border-gray-200 dark:border-white/15 flex items-center justify-center hover:border-gray-400 dark:hover:border-white/50 transition-colors">
                                <img src="/svg/Facebook.svg" alt="" class="w-4 h-4 opacity-55" aria-hidden="true">
                            </a>
                            <a href="#" aria-label="Instagram de DinamyCode" class="w-9 h-9 rounded-full border border-gray-200 dark:border-white/15 flex items-center justify-center hover:border-gray-400 dark:hover:border-white/50 transition-colors">
                                <img src="/svg/Instagram.svg" alt="" class="w-4 h-4 opacity-55" aria-hidden="true">
                            </a>
                            <a href="https://www.linkedin.com/company/dinamycode/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn de DinamyCode" class="w-9 h-9 rounded-full border border-gray-200 dark:border-white/15 flex items-center justify-center hover:border-gray-400 dark:hover:border-white/50 transition-colors">
                                <img src="/svg/Linkedin.svg" alt="" class="w-4 h-4 opacity-55" aria-hidden="true">
                            </a>
                            <a href="https://wa.me/573132635848" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp de DinamyCode" class="w-9 h-9 rounded-full border border-gray-200 dark:border-white/15 flex items-center justify-center hover:border-gray-400 dark:hover:border-white/50 transition-colors">
                                <img src="/svg/WhatsApp.svg" alt="" class="w-4 h-4 opacity-55" aria-hidden="true">
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Github (Bloque oscuro, arriba derecha) -->
                <div class="md:col-span-1 lg:col-span-1 bg-[#0d1117] rounded-[2rem] shadow-lg overflow-hidden flex flex-col transition-all hover:-translate-y-1 hover:shadow-2xl duration-500 relative group border border-gray-800">
                    <div class="absolute top-0 right-0 p-32 bg-blue-500/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none transition-all group-hover:bg-blue-500/10"></div>

                    <div class="p-8 relative z-10 flex-grow flex flex-col">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-3 bg-white/5 rounded-xl backdrop-blur-md border border-white/10">
                                <!-- Icono de GitHub -->
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-white tracking-wide">GitHub</h2>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed text-justify">
                            Supongo que todo debe tener un inicio. Échale un vistazo a mi repositorio de GitHub. Verás muchos proyectos (algunos sin terminar) que he realizado en mi tiempo libre. Como todos sabemos, en el mundo de la programación es común que algunos proyectos se queden a medio camino. Aunque la recomendación es no dejarlos incompletos, a veces surgen nuevas ideas o prioridades que nos desvían. Sin embargo, nunca se sabe, tal vez algún día me dé el tiempo para retomar y terminar esos proyectos.
                        </p>
                    </div>
                </div>

                <!-- Tarjeta 3: Framework (Bloque de acento, abajo derecha) -->
                <div class="md:col-span-1 lg:col-span-1 bg-gradient-to-br from-red-600 to-red-800 dark:from-red-700 dark:to-red-950 rounded-[2rem] shadow-lg overflow-hidden flex flex-col transition-all hover:-translate-y-1 hover:shadow-2xl duration-500 relative text-white">
                    <!-- Patrón de puntos decorativo -->
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>

                    <div class="p-8 relative z-10 flex-grow flex flex-col justify-center">
                        <div class="mb-5">
                            <span class="inline-block px-3 py-1 bg-white/10 text-white border border-white/20 text-xs font-bold rounded-full tracking-wider uppercase backdrop-blur-sm">Framework Favorito</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-wide mb-4">Laravel</h2>
                        <p class="text-red-100 text-sm leading-relaxed text-justify">
                            Todos tenemos un framework favorito (aunque no deberíamos). El mío, debo confesar, es Laravel. Me parece simple y dinámico, con la capacidad de abarcar una gran escala de proyectos, desde los más pequeños hasta los más complejos. Su sintaxis elegante, su amplia documentación y la gran comunidad que lo respalda hacen que el desarrollo sea mucho más ágil y placentero. Aunque sé que la elección del framework debe basarse en las necesidades del proyecto, Laravel siempre ha sido una opción confiable para mí. Seguro que en tu equipo también tienen su framework favorito, ¿verdad? Apuesto a que podría convertirse en el mío también.
                        </p>
                    </div>
                </div>

            </div>

            <!-- SECCIÓN DE EXPERIENCIA (Estilo Mini-Cards) -->
            <article id="experiencia" class="w-full mt-32 relative z-10 scroll-mt-32">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">Experiencia</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        Soy muy bueno en esto, pero seguro quieres la opinión de alguien más. Puedes hablar con uno de los usuarios de mis proyectos anteriores. Si desconfías, puedes hablar con todos 😉.
                    </p>
                </div>

                <!-- Grid de Mini Cards (1 col móvil, 2 cols tablet, 4 cols desktop) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
                    
                    <!-- Mini Card 1: Vidrios y Estructuras JyM -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Vidrios y Estructuras JyM</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Diseño y desarrollo del sitio WEB</p>
                        
                        <a href="https://vidriosyestructurasjym.com.co/" target="_blank" rel="noopener noreferrer" class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-16 h-16 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300" src="https://vidriosyestructurasjym.com.co/IMG/Logo.png" alt="Vidrios y Estructuras JyM">
                        </a>
                        
                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Jairo Pulido</p>
                            <a href="tel:+573124491072" class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 312 4491072
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 2: Casa Angel Eventos -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Casa Angel Eventos</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Diseño y desarrollo del sitio WEB</p>
                        
                        <a href="https://www.eventoscasaangel.com/" target="_blank" rel="noopener noreferrer" class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-24 h-auto object-contain rounded drop-shadow-sm group-hover:scale-110 transition-transform duration-300" src="https://www.eventoscasaangel.com/IMGes/logo.jpg" alt="Casa Angel Eventos">
                        </a>
                        
                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Ruben Salgado</p>
                            <a href="tel:+573142502033" class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 314 2502033
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 3: Savatechnology -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Savatechnology</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Publicidad y campañas en redes</p>
                        
                        <a href="https://www.facebook.com/savatechnology/" target="_blank" rel="noopener noreferrer" class="flex-grow flex items-center justify-center mb-6 z-10 text-blue-600 dark:text-blue-500">
                            <svg class="w-16 h-16 fill-current group-hover:scale-110 transition-transform duration-300 drop-shadow-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73.62 94.68">
                                <path d="M0,81.42,0,79H.15a5.43,5.43,0,0,0,.59,1.45,1.85,1.85,0,0,0,1.59,1A1.51,1.51,0,0,0,3.44,81a1.58,1.58,0,0,0,.44-1.14,1.9,1.9,0,0,0-.1-.61,1.64,1.64,0,0,0-.26-.53,2,2,0,0,0-.47-.44,9,9,0,0,0-.94-.53c-.37-.19-.65-.34-.85-.47a2.78,2.78,0,0,1-.47-.39,1.89,1.89,0,0,1-.5-1.34,2.31,2.31,0,0,1,.19-.93A2.13,2.13,0,0,1,1,73.85a2.08,2.08,0,0,1,1.46-.57,2.1,2.1,0,0,1,.51,0,4.85,4.85,0,0,1,.62.22l.4.15.19,0q.19,0,.27-.3h.14l0,2.32H4.52a8,8,0,0,0-.27-.85A2.72,2.72,0,0,0,4,74.36a2.09,2.09,0,0,0-.63-.68,1.49,1.49,0,0,0-.79-.22,1.26,1.26,0,0,0-1,.45,1.27,1.27,0,0,0-.25.42,1.46,1.46,0,0,0-.09.49c0,.65.42,1.18,1.25,1.57l.82.4a3.67,3.67,0,0,1,1.37,1,2.24,2.24,0,0,1,.48,1.42,2.55,2.55,0,0,1-.21,1,2.25,2.25,0,0,1-.92,1,2.64,2.64,0,0,1-1.42.37,3.59,3.59,0,0,1-1.45-.3l-.43-.18-.16,0a.38.38,0,0,0-.39.28Z"></path>
                                <path d="M14,81.44H10.18v-.13a1.6,1.6,0,0,0,.72-.18.7.7,0,0,0,.37-.63,1.9,1.9,0,0,0-.17-.64l-.1-.24-.56-1.38H7.88l-.31.81-.14.36a2.81,2.81,0,0,0-.27,1,.91.91,0,0,0,.1.4.85.85,0,0,0,.26.3,1.14,1.14,0,0,0,.58.19v.13H5.49v-.13A1.14,1.14,0,0,0,6,81.16a1.66,1.66,0,0,0,.46-.43,3.77,3.77,0,0,0,.36-.62c.12-.25.28-.63.47-1.13l2.14-5.5h.33l2.54,6.13a8.81,8.81,0,0,0,.45,1,1.59,1.59,0,0,0,.37.45.88.88,0,0,0,.32.16,3.1,3.1,0,0,0,.54.1ZM10.35,78,9.13,75,8,78Z"></path>
                                <path d="M13.69,73.48h3.85v.13a2.12,2.12,0,0,0-.63.14.75.75,0,0,0-.29.27.8.8,0,0,0-.11.4,1.12,1.12,0,0,0,0,.27c0,.08.08.24.18.51l1.72,4.69,1.68-4.12a4,4,0,0,0,.35-1.26,1.42,1.42,0,0,0-.07-.42.7.7,0,0,0-.19-.29.55.55,0,0,0-.26-.14,1.15,1.15,0,0,0-.36,0v-.13h2.6v.13a1.53,1.53,0,0,0-.56.16,1.62,1.62,0,0,0-.45.39,3.57,3.57,0,0,0-.31.51c-.1.22-.25.55-.44,1L18,81.44H17.8l-2.33-6A5.68,5.68,0,0,0,14.74,74a1.13,1.13,0,0,0-.42-.29,1.91,1.91,0,0,0-.63-.09Z"></path>
                                <path d="M30.4,81.44H26.56v-.13a1.7,1.7,0,0,0,.73-.18.71.71,0,0,0,.36-.63,1.84,1.84,0,0,0-.16-.64l-.1-.24-.56-1.38H24.26l-.3.81-.15.36a3,3,0,0,0-.26,1,.9.9,0,0,0,.09.4,1,1,0,0,0,.26.3,1.18,1.18,0,0,0,.59.19v.13H21.87v-.13a1.21,1.21,0,0,0,.55-.15,1.79,1.79,0,0,0,.45-.43,3.17,3.17,0,0,0,.37-.62c.11-.25.27-.63.47-1.13l2.13-5.5h.33l2.54,6.13a7,7,0,0,0,.46,1,1.42,1.42,0,0,0,.36.45.92.92,0,0,0,.33.16,3.1,3.1,0,0,0,.54.1ZM26.73,78l-1.22-3-1.16,3Z"></path>
                                <path d="M6.75,64.75V43.56H7.87A43.22,43.22,0,0,0,12.8,56q5.43,8.46,12.68,8.46a11.24,11.24,0,0,0,7.67-2.85q3.52-3.14,3.52-8.91a13.72,13.72,0,0,0-2.53-8.23A16.94,16.94,0,0,0,30.58,41a78.7,78.7,0,0,0-7.22-4.42q-8.86-5-11.82-8.38A16.24,16.24,0,0,1,7.31,17.14a17,17,0,0,1,4.24-11.6A16.35,16.35,0,0,1,24.46,0a16.66,16.66,0,0,1,6.71,1.35l2.4,1.12a5.44,5.44,0,0,0,2,.45c.9,0,1.74-.75,2.55-2.25H39.5l.77,17H38.79a37.4,37.4,0,0,0-1.65-5.76,16.89,16.89,0,0,0-2.5-4.19,16.53,16.53,0,0,0-5.07-4.45,11.54,11.54,0,0,0-5.7-1.61,8.78,8.78,0,0,0-6.72,3,10.62,10.62,0,0,0-2.78,7.45,10,10,0,0,0,2.5,6.62,22.85,22.85,0,0,0,7.71,5.43L32,27.77a30.89,30.89,0,0,1,5.14,3.06c.76,2.22,6.91,19.25,7.66,21.1a17.34,17.34,0,0,1-4.28,8.17,18.3,18.3,0,0,1-14.12,6A25.17,25.17,0,0,1,17,64.15c-2.07-.85-3.32-1.34-3.74-1.46a5.66,5.66,0,0,0-1.62-.19,4.07,4.07,0,0,0-3.75,2.25Z" style="fill:currentColor"></path>
                            </svg>
                        </a>
                        
                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Andres Rojas</p>
                            <a href="tel:+573173671746" class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 317 3671746
                            </a>
                        </div>
                    </div>

                    <!-- Mini Card 4: Lab Biotek -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-2 hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-50/50 dark:to-gray-900/50 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1 z-10">Laboratorio clínico Biotek</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 z-10">Sistema de registro de pacientes y bitacora</p>
                        
                        <a href="https://maps.app.goo.gl/btdcnCEd7i2tpbat8" target="_blank" rel="noopener noreferrer" class="flex-grow flex items-center justify-center mb-6 z-10">
                            <img class="w-20 h-20 object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300 rounded-full bg-white p-1 border border-gray-100" src="https://biotek.dinamycode.com/Biotek.png" alt="Laboratorio Biotek">
                        </a>
                        
                        <div class="w-full pt-4 border-t border-gray-100 dark:border-gray-700 z-10">
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Coordinador</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Claudia Buitrago</p>
                            <a href="tel:+573207001403" class="inline-block mt-2 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                                📞 320 7001403
                            </a>
                        </div>
                    </div>

                </div>
            </article>

            <!-- SECCIÓN DE CONTACTO (Call to Action) -->
            <section id="contacto" class="w-full mt-32 mb-16 relative z-10 scroll-mt-32">
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-10 md:p-16 text-center shadow-xl border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <!-- Elementos decorativos de fondo -->
                    <div class="absolute top-0 right-0 p-32 bg-green-500/10 dark:bg-green-500/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 p-32 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>

                    <div class="relative z-10 max-w-2xl mx-auto">
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-xs font-bold rounded-full tracking-wider uppercase mb-4 border border-green-200 dark:border-green-800/50">
                            Contacto
                        </span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-6">¿Listo para dar el siguiente paso?</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-10">
                            Ya sea que tengas una idea en mente, necesites optimizar un proceso en tu empresa, o simplemente quieras saludar. ¡Me encantaría escucharte!
                        </p>
                        
                        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                            <!-- Botón Correo -->
                            <a href="mailto:hola@dinamycode.com" class="w-full sm:w-auto px-8 py-3.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl shadow-md hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                hola@dinamycode.com
                            </a>
                            <!-- Botón WhatsApp -->
                            <a href="https://wa.me/573014819820" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto px-8 py-3.5 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
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
            <svg class="relative block w-[calc(100%+1.3px)] h-[120px] md:h-[220px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.15,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" class="fill-gray-200 dark:fill-gray-800 opacity-50"></path>
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-gray-300 dark:fill-gray-950 opacity-40"></path>
            </svg>
        </div>

        <!-- Footer simple sobre el spread vectorizado -->
        <footer class="absolute bottom-0 w-full pb-6 flex items-center justify-center text-sm text-gray-600 dark:text-gray-400 z-10">
            <div class="flex items-center gap-3">
                
                <!-- Redes Sociales Rotativas -->
                <div class="flex items-center gap-3 pr-3 border-r border-gray-300 dark:border-gray-700">
                    <a v-for="social in activeSocials" :key="social.name" :href="social.url" target="_blank" rel="noopener noreferrer" :aria-label="social.name" :title="social.name" class="text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path :d="social.icon" />
                        </svg>
                    </a>
                </div>
                
                <!-- Copyright Responsivo -->
                <p>
                    &copy; {{ new Date().getFullYear() }} 
                    <span class="hidden sm:inline">Carlos Ramirez. Todos los derechos reservados.</span>
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