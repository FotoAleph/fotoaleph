<template>
    <RoleLayout title="Crear Usuario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Contraseña
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirmar Contraseña
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="role" class="block text-sm font-medium text-gray-700">
                                Rol
                            </label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            >
                                <option value="">Selecciona un rol</option>
                               
                                <option value="coordinador">Coordinador</option>
                                <option value="cliente">Cliente</option>
                            </select>
                            <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">
                                {{ form.errors.role }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <Link
                                :href="route('users.index')"
                                class="mr-4 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>

<script setup>
import RoleLayout from '@/layouts/RoleLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useRoleRedirect } from '@/composables/useRoleRedirect';

// Solo administradores pueden crear usuarios
useRoleRedirect('admin');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const submit = () => {
    form.post(route('users.store'));
};
</script>
