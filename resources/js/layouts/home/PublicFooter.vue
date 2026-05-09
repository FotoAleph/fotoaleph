<script setup lang="ts">
import { Mail, MapPin, MessageCircle } from 'lucide-vue-next';

defineProps<{
    socialNetworks?: Array<{ name: string; url: string; icon: string }>;
}>();
</script>

<template>
    <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1.2fr_1fr_1fr] lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    <img src="/dina.svg" alt="DinamyCode" class="h-10 w-10" />
                    <p class="text-lg font-extrabold text-cyan-500">Dinamy<span class="text-rose-500">Code</span></p>
                </div>
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Desarrollo de sitios, productos digitales y automatizaciones para operaciones que necesitan moverse con claridad.
                </p>
            </div>

            <div class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <p class="font-bold text-slate-950 dark:text-white">Contacto</p>
                <a href="mailto:contacto@dinamycode.com" class="flex items-center gap-2 hover:text-cyan-600 dark:hover:text-cyan-300">
                    <Mail class="h-4 w-4" />
                    contacto@dinamycode.com
                </a>
                <a href="https://wa.me/573132635848" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-cyan-600 dark:hover:text-cyan-300">
                    <MessageCircle class="h-4 w-4" />
                    WhatsApp
                </a>
                <p class="flex items-center gap-2">
                    <MapPin class="h-4 w-4" />
                    Colombia
                </p>
            </div>

            <div class="space-y-3 text-sm">
                <p class="font-bold text-slate-950 dark:text-white">Redes</p>
                <div class="flex flex-wrap gap-2">
                    <a
                        v-for="network in socialNetworks"
                        :key="network.url"
                        :href="network.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-md border border-slate-200 px-3 py-2 font-semibold text-slate-600 transition hover:border-cyan-300 hover:text-cyan-700 dark:border-slate-800 dark:text-slate-300 dark:hover:border-cyan-700 dark:hover:text-cyan-300"
                    >
                        {{ network.name }}
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
