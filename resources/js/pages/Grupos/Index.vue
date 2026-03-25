<template>
    <AppLayout title="Grupos">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-medium text-gray-900">Grupos</h2>
                            <Link
                                v-if="canManage"
                                :href="route('grupos.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Crear grupo
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="grupo in grupos.data" :key="grupo.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ grupo.nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ grupo.nivel }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('grupos.show', { grupo: grupo.id })" class="text-indigo-600 hover:text-indigo-900 mr-4">Ver</Link>
                                            <Link
                                                v-if="canManage"
                                                :href="route('grupos.edit', { grupo: grupo.id })"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <button v-if="canManage" @click="deleteGrupo(grupo.id, grupo.nombre)" class="text-red-600 hover:text-red-900">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="grupos.links" class="mt-6">
                            <div class="flex justify-center gap-2">
                                <Link
                                    v-for="link in grupos.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded',
                                        link.active
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    grupos: Object,
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role);
const canManage = computed(() => ['admin', 'coordinador'].includes(role.value));

const deleteGrupo = (id, nombre) => {
    if (confirm(`¿Estás seguro de eliminar el grupo "${nombre}"?`)) {
        useForm().delete(route('grupos.destroy', { grupo: id }));
    }
};
</script>
