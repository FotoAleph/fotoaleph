<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck } from 'lucide-vue-next';
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

const products = [
    'Sitios publicos administrables',
    'Paneles internos para equipos',
    'Automatizaciones e integraciones',
    'Consultoria tecnica para Laravel y Vue',
];
</script>

<template>
    <Head title="Productos | DinamyCode" description="Asambli gestor de reuniones y asambleas de copropietarios - Lissapp reportes y control de pacientes y resultados" />

    <HomeLayout :can-register="canRegister" :social-networks="socialNetworks">
        <section class="mx-auto grid max-w-7xl gap-10 px-4 pb-20 pt-12 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8 lg:pt-20">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-cyan-600 dark:text-cyan-300">Productos</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl">Paquetes digitales para avanzar sin empezar desde cero.</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
                    Servicios productizados para lanzar, mejorar o conectar sistemas con una base clara y adaptable a tu operacion.
                </p>
                <Link href="/contacto" class="mt-8 inline-flex items-center rounded-md bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                    Solicitar propuesta
                    <ArrowRight class="ml-2 h-4 w-4" />
                </Link>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div v-for="product in products" :key="product" class="flex gap-4 border-b border-slate-200 py-5 last:border-b-0 dark:border-slate-800">
                    <BadgeCheck class="mt-1 h-5 w-5 shrink-0 text-cyan-600 dark:text-cyan-300" />
                    <div>
                        <h2 class="font-bold text-slate-950 dark:text-white">{{ product }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Definicion, implementacion y acompanamiento para entregar valor visible en menos tiempo.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <section>
            <iframe
                class="mx-auto mt-10 h-[400px] w-full rounded-md border-0"
                src="https://asambli.dinamycode.com/"
                title="Video de ejemplo"
                allowfullscreen></iframe>
        </section>
        <section>
            <iframe
                class="mx-auto mt-10 h-[400px] w-full rounded-md border-0"
                src="https://lissapp.dinamycode.com/"
                title="Video de ejemplo"
                allowfullscreen></iframe>
        </section>
    </HomeLayout>
</template>
