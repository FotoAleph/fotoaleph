<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
})

// Títulos dinámicos basados en el código HTTP
const title = computed(() => {
    return {
        503: '503: Servicio No Disponible',
        500: '500: Error Interno del Servidor',
        404: '404: Página No Encontrada',
        403: '403: Acceso Denegado',
    }[props.status] || 'Error Inesperado'
})

// Descripciones orientadas a la experiencia de usuario (UX)
const description = computed(() => {
    return {
        503: 'Lo sentimos, estamos realizando tareas de mantenimiento. Por favor, intenta más tarde.',
        500: 'Vaya, algo ha salido mal en nuestros servidores. Ya estamos trabajando en ello.',
        404: 'Lo sentimos, la página que estás buscando no existe o ha sido movida.',
        403: 'Lo sentimos, no tienes los permisos necesarios para acceder a esta página.',
    }[props.status] || 'Ocurrió un error inesperado al procesar tu solicitud.'
})
</script>

<template>
    <Head :title="title" />
  
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 px-4 text-center">

                <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />

        <div class="max-w-md w-full bg-white shadow-lg rounded-2xl p-8">
            <span class="text-indigo-600 font-extrabold text-7{-tracking} block mb-2">
                {{ status }}
            </span>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-3">
                {{ title }}
            </h1>
            
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                {{ description }}
            </p>

            <Link
                href="/"
                class="inline-block w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition duration-200 shadow-sm"
            >
                Volver al Inicio
            </Link>
        </div>
    </div>
</template>