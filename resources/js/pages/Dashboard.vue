<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    sidebar_items?: any[];
    layout?: string;
    stats?: Record<string, number>;
    user_role?: string;
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    sidebar_items: () => [],
    layout: 'AppLayout',
    stats: () => ({}),
    user_role: 'cliente',
});

// Layout dinámico basado en el rol
const layoutComponent = computed(() => {
    switch (props.layout) {
        case 'AdminLayout':
            return AdminLayout;
        case 'EmployeeLayout':
            return EmployeeLayout;
        case 'ClientLayout':
            return ClientLayout;
        default:
            return AppLayout;
    }
});

// Estadísticas formateadas para mostrar
const formattedStats = computed(() => {
    const stats = props.stats;
    switch (props.user_role) {
        case 'admin':
            return [
                { label: 'Total Usuarios', value: stats.total_users || 0 },
                { label: 'Total Tenants', value: stats.total_tenants || 0 },
                { label: 'Total PQRs', value: stats.total_pqrs || 0 },
                { label: 'Total Cotizaciones', value: stats.total_cotizaciones || 0 },
            ];
        case 'empleado':
            return [
                { label: 'Mis PQRs', value: stats.my_pqrs || 0 },
                { label: 'Cotizaciones Pendientes', value: stats.pending_cotizaciones || 0 },
            ];
        case 'cliente':
        default:
            return [
                { label: 'Mis PQRs', value: stats.my_pqrs || 0 },
                { label: 'Mis Cotizaciones', value: stats.my_cotizaciones || 0 },
            ];
    }
});
</script>

<template>
    <Head :title="$t('Dashboard')" />

    <component :is="layoutComponent" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Estadísticas -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <div
                    v-for="stat in formattedStats"
                    :key="stat.label"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col justify-center items-center"
                >
                    <div class="text-2xl font-bold">{{ stat.value }}</div>
                    <div class="text-sm text-muted-foreground">{{ stat.label }}</div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>

            <!-- Área de trabajo adicional -->
            <div
                class="relative min-h-[50vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </component>
</template>
