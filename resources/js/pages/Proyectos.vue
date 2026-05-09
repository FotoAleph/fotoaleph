<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import HomeLayout from '@/layouts/HomeLayout.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
        socialNetworks: Array<{ name: string; url: string; icon: string }>;
    }>(),
    {
        canRegister: true,
        socialNetworks: () => [],
    },
);

const projects = [
    {
        name: 'DinamyCode',
        category: 'Software empresarial',
        description: 'Soluciones para automatizar procesos, reportes y flujos internos con una base tecnica mantenible.',
        href: 'https://dinamycode.com',
    },
    {
        name: 'Fotoaleph',
        category: 'Plataforma web',
        description: 'Ecosistema para administrar sitios, clientes, proyectos y operaciones conectadas desde Laravel y Vue.',
        href: '/',
    },
    {
        name: 'Integraciones a medida',
        category: 'Automatizacion',
        description: 'Conexiones entre formularios, paneles, APIs y sistemas internos para reducir tareas manuales.',
        href: '/contacto',
    },
];
</script>

<template>
    <Head title="Proyectos | DinamyCode" />

    <HomeLayout :can-register="canRegister" :social-networks="socialNetworks">
        <section class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8 lg:pt-20">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-cyan-600 dark:text-cyan-300">Proyectos</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl">Trabajo construido alrededor de problemas reales.</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
                    Una seleccion de iniciativas donde la prioridad es ordenar operaciones, hacer visible la informacion y crear experiencias digitales sostenibles.
                </p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-3">
                <article v-for="project in projects" :key="project.name" class="rounded-md border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-bold text-cyan-600 dark:text-cyan-300">{{ project.category }}</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ project.name }}</h2>
                    <p class="mt-4 min-h-24 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ project.description }}</p>
                    <a :href="project.href" class="mt-6 inline-flex items-center text-sm font-bold text-slate-950 hover:text-cyan-700 dark:text-white dark:hover:text-cyan-300">
                        Ver proyecto
                        <ExternalLink class="ml-2 h-4 w-4" />
                    </a>
                </article>
            </div>
        </section>
    </HomeLayout>
</template>
