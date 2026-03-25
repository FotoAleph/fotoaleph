<template>
    <AppLayout title="Detalle Sitio">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200 space-y-4">
                        <h2 class="text-2xl font-semibold text-gray-900">{{ sitio.name }}</h2>
                        <p><strong>Tenant:</strong> {{ sitio.tenant?.razon_social || 'N/A' }}</p>
                        <p><strong>Descripción:</strong> {{ sitio.description || 'Sin descripción' }}</p>
                        <p><strong>URL:</strong> {{ sitio.url || 'No definida' }}</p>
                        <p><strong>Fecha inicio:</strong> {{ sitio.fecha_inicio || 'No definida' }}</p>
                        <p><strong>Fecha fin:</strong> {{ sitio.fecha_fin || 'No definida' }}</p>
                        <p><strong>Estado:</strong> {{ sitio.estado }}</p>

                        <div class="flex justify-end">
                            <Link :href="route('sitios.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Volver
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    sitio: Object,
});
</script>
