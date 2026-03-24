<template>
    <AdminLayout title="Usuarios">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-medium text-gray-900">
                                Administración de Usuarios
                            </h3>
                            <Link
                                :href="route('users.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Crear Nuevo Usuario
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rol
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span :class="getRoleClass(user.role)">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('users.edit', { user: user.id })"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                @click="deleteUser(user.id, user.name)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="users.links" class="mt-6">
                            <div class="flex justify-center gap-2">
                                <Link
                                    v-for="link in users.links"
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
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useRoleRedirect } from '@/composables/useRoleRedirect';

defineProps({
    users: Object,
});

// Solo administradores pueden acceder aquí
useRoleRedirect('admin');

const deleteUser = (id, name) => {
    if (confirm(`¿Estás seguro de que deseas eliminar el usuario "${name}"?`)) {
        useForm().delete(route('users.destroy', { user: id }));
    }
};

const getRoleLabel = (role) => {
    const roles = {
        admin: 'Administrador',
        coordinador: 'Coordinador',
        cliente: 'Cliente'
    };
    return roles[role] || role;
};

const getRoleClass = (role) => {
    const classes = {
        admin: 'px-2 py-1 bg-red-100 text-red-800 rounded',
        coordinador: 'px-2 py-1 bg-yellow-100 text-yellow-800 rounded',
        cliente: 'px-2 py-1 bg-blue-100 text-blue-800 rounded'
    };
    return classes[role] || 'px-2 py-1 bg-gray-100 text-gray-800 rounded';
};
</script>
