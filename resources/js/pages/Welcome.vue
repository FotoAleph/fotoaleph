<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, Code2, Sparkles, Workflow } from 'lucide-vue-next';
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

const strengths = [
    { title: 'Sitios publicos', text: 'Presencia digital clara, rapida y preparada para convertir visitas en conversaciones.', icon: Sparkles },
    { title: 'Aplicaciones a medida', text: 'Flujos internos, paneles y herramientas pensadas para la operacion real del negocio.', icon: Code2 },
    { title: 'Automatizacion', text: 'Procesos conectados para ahorrar tiempo, reducir errores y mejorar el control.', icon: Workflow },
];
</script>

<template>
    <Head title="Inicio | DinamyCode" />

    <HomeLayout :can-register="canRegister" :social-networks="socialNetworks">
        <section class="mx-auto grid max-w-7xl items-center gap-10 px-4 pb-16 pt-10 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:pb-24 lg:pt-16">
            <div>
                <p class="mb-4 inline-flex rounded-md border border-cyan-200 bg-cyan-50 px-3 py-1 text-sm font-bold text-cyan-700 dark:border-cyan-800 dark:bg-cyan-950 dark:text-cyan-300">
                    Software, web y automatizacion
                </p>
                <h1 class="max-w-4xl text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl lg:text-6xl">
                    Soluciones digitales que convierten ideas en sistemas utiles.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                    Construimos sitios publicos, productos digitales y herramientas internas con Laravel, Vue y una mirada practica sobre el negocio.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link href="/contacto" class="inline-flex items-center rounded-md bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                        Hablemos
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Link>
                    <Link href="/proyectos" class="inline-flex items-center rounded-md border border-slate-300 px-5 py-3 text-sm font-bold text-slate-800 transition hover:bg-white dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-900">
                        Ver proyectos
                    </Link>
                </div>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <img src="/carlos_foto.jpg" alt="Carlos Ramirez" class="aspect-[4/5] w-full rounded-md object-cover" />
            </div>
        </section>

        <section class="bg-white py-16 dark:bg-slate-900">
            <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
                <article v-for="strength in strengths" :key="strength.title" class="rounded-md border border-slate-200 p-6 dark:border-slate-800">
                    <component :is="strength.icon" class="h-7 w-7 text-cyan-600 dark:text-cyan-300" />
                    <h2 class="mt-5 text-xl font-bold text-slate-950 dark:text-white">{{ strength.title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ strength.text }}</p>
                </article>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-md border border-slate-200 bg-slate-950 p-8 text-white dark:border-slate-800">
                <h2 class="text-2xl font-black">Trabajo enfocado en resultados medibles</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <p v-for="item in ['Arquitectura mantenible', 'Interfaces claras', 'Procesos automatizados']" :key="item" class="flex items-center gap-3 text-sm text-slate-200">
                        <CheckCircle2 class="h-5 w-5 text-cyan-300" />
                        {{ item }}
                    </p>
                </div>
            </div>
        </section>
    </HomeLayout>
</template>
