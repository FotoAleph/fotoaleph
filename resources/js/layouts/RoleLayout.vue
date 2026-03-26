<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import CoordinadorLayout from '@/layouts/CoordinadorLayout.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    title: '',
});

const page = usePage();

const layoutComponent = computed(() => {
    const role = page.props.auth?.user?.role;

    switch (role) {
        case 'admin':
            return AdminLayout;
        case 'coordinador':
            return CoordinadorLayout;
        case 'cliente':
        default:
            return ClientLayout;
    }
});
</script>

<template>
    <component :is="layoutComponent" :breadcrumbs="props.breadcrumbs" :title="props.title">
        <slot />
    </component>
</template>