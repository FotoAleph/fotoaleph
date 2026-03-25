<template>
    <AppLayout title="Editar Sitio">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 lg:p-8 bg-white border-b border-gray-200 space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">Editar sitio</h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                            <select v-model="form.tenant_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="" disabled>Seleccione un tenant</option>
                                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                    {{ tenant.razon_social }}
                                </option>
                            </select>
                            <div v-if="form.errors.tenant_id" class="text-red-500 text-sm mt-1">{{ form.errors.tenant_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea v-model="form.description" class="w-full border-gray-300 rounded-md shadow-sm" />
                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input v-model="form.url" type="url" class="w-full border-gray-300 rounded-md shadow-sm" />
                            <div v-if="form.errors.url" class="text-red-500 text-sm mt-1">{{ form.errors.url }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                                <input v-model="form.fecha_inicio" type="date" class="w-full border-gray-300 rounded-md shadow-sm" />
                                <div v-if="form.errors.fecha_inicio" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_inicio }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                                <input v-model="form.fecha_fin" type="date" class="w-full border-gray-300 rounded-md shadow-sm" />
                                <div v-if="form.errors.fecha_fin" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_fin }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select v-model="form.estado" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div v-if="form.errors.estado" class="text-red-500 text-sm mt-1">{{ form.errors.estado }}</div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <Link :href="route('sitios.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</Link>
                            <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sitio: Object,
    tenants: Object,
});

const form = useForm({
    tenant_id: props.sitio.tenant_id,
    name: props.sitio.name,
    description: props.sitio.description || '',
    url: props.sitio.url || '',
    fecha_inicio: props.sitio.fecha_inicio || '',
    fecha_fin: props.sitio.fecha_fin || '',
    estado: props.sitio.estado || 'activo',
});

const submit = () => {
    form.put(route('sitios.update', { sitio: props.sitio.id }));
};
</script>
