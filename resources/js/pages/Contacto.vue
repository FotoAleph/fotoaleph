<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Mail, MapPin, MessageCircle } from 'lucide-vue-next';
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
</script>

<template>
    <Head title="Contacto | DinamyCode" />

    <HomeLayout :can-register="canRegister" :social-networks="socialNetworks">
        <section class="mx-auto grid max-w-7xl gap-10 px-4 pb-20 pt-12 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:pt-20">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-cyan-600 dark:text-cyan-300">Contacto</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl">Conversemos sobre tu proximo proyecto.</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
                    Cuéntame que quieres construir, mejorar o automatizar. Podemos aterrizar alcance, prioridades y un primer camino tecnico.
                </p>
            </div>

            <div class="grid gap-4">
                <a href="mailto:contacto@dinamycode.com" class="rounded-md border border-slate-200 bg-white p-6 transition hover:border-cyan-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-700">
                    <Mail class="h-7 w-7 text-cyan-600 dark:text-cyan-300" />
                    <h2 class="mt-4 text-xl font-bold">Correo</h2>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">contacto@dinamycode.com</p>
                </a>
                <a href="https://wa.me/573132635848" target="_blank" rel="noopener noreferrer" class="rounded-md border border-slate-200 bg-white p-6 transition hover:border-cyan-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-700">
                    <MessageCircle class="h-7 w-7 text-cyan-600 dark:text-cyan-300" />
                    <h2 class="mt-4 text-xl font-bold">WhatsApp</h2>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Respuesta directa para coordinar una llamada.</p>
                </a>
                <div class="rounded-md border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <MapPin class="h-7 w-7 text-cyan-600 dark:text-cyan-300" />
                    <h2 class="mt-4 text-xl font-bold">Ubicacion</h2>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Colombia, trabajo remoto para equipos y empresas.</p>
                </div>
            </div>
        </section>
    </HomeLayout>
</template>
