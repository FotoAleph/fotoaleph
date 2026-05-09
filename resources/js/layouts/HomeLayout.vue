<script setup lang="ts">
import PublicFooter from '@/layouts/home/PublicFooter.vue';
import PublicHeader from '@/layouts/home/PublicHeader.vue';

defineProps<{
    canRegister?: boolean;
    socialNetworks?: Array<{ name: string; url: string; icon: string }>;
}>();
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-950 selection:bg-cyan-500 selection:text-white dark:bg-slate-950 dark:text-slate-50">
        <PublicHeader :can-register="canRegister" />

        <main class="pt-32 md:pt-24">
            <slot />
        </main>

        <PublicFooter :social-networks="socialNetworks" />
    </div>
</template>
