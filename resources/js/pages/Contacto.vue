<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import HomeLayout from '@/layouts/HomeLayout.vue';


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
<HomeLayout>
    
</HomeLayout>


</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
