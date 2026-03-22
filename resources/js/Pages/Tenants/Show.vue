<template>
    <AppLayout title="Tenant Details">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tenant: {{ tenant.razon_social }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <p><strong>Razon Social:</strong> {{ tenant.razon_social }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Users</h3>
                            <ul class="list-disc list-inside">
                                <li v-for="user in tenant.users" :key="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </li>
                            </ul>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Direcciones</h3>
                            <ul class="list-disc list-inside">
                                <li v-for="direccion in tenant.direcciones" :key="direccion.id">
                                    {{ direccion.nomenclatura }}, {{ direccion.codigo_postal }}
                                </li>
                            </ul>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Telefonos</h3>
                            <ul class="list-disc list-inside">
                                <li v-for="telefono in tenant.telefonos" :key="telefono.id">
                                    {{ telefono.number }} ({{ telefono.type }})
                                </li>
                            </ul>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Redes Sociales</h3>
                            <ul class="list-disc list-inside">
                                <li v-for="red in tenant.redes_sociales" :key="red.id">
                                    {{ red.social_network_type.name }}: {{ red.url }}
                                </li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end">
                            <Link
                                :href="route('tenants.index')"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Back to Tenants
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    tenant: Object,
});
</script>