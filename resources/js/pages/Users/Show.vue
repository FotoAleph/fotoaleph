<template>
    <RoleLayout title="Editar Usuario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Datos de usuario
            </h2>
        </template>

        <div class="py-12">
           
                <dl class="max-w-md mx-auto gap-x-4 gap-y-8 grid-cols-2 bg-stone-100 p-6 rounded-lg shadow-md">
                        
                            <dt class=" block text-sm font-medium text-gray-700 ">
                                Nombre
                            </dt>
                            <dd class=" mt-1 text-sm text-gray-900">
                                {{ user.name }}
                            </dd>
                                             
                            <dt aria-label="email" class="  block text-sm font-medium text-gray-700">
                                Email
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ user.email }}
                            </dd>
                                                                 
                            <dt aria-label="role" class="block text-sm font-medium text-gray-700">
                                Rol
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ user.role }}
                            </dd>
                    <span v-for="empresa in user.tenant">
                            <dt aria-label="tenant" class="block text-sm font-medium text-gray-700">
                                Tenant
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ empresa.razon_social }}
                            </dd>
                    </span>

                                          
                    </dl>
           
        </div>
    </RoleLayout>
</template>

<script setup>
import RoleLayout from '@/layouts/RoleLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useRoleRedirect } from '@/composables/useRoleRedirect';

const props = defineProps({
    user: Object,
});

// Solo administradores pueden editar usuarios
useRoleRedirect('admin');

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const submit = () => {
    form.put(route('users.update', { user: props.user.id }));
};
</script>
