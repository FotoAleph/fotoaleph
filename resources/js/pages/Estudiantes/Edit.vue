<template>
    <AppLayout title="Editar Estudiante">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-xl">
                    <form class="space-y-4 border-b border-gray-200 bg-white p-6 lg:p-8" @submit.prevent="submit">
                        <h2 class="text-xl font-semibold text-gray-900">Editar estudiante</h2>
                        <div class="container flex flex-wrap">
                            <div class="w-1/2">
                 
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                            <input v-model="form.nombre" type="text" class="w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">{{ form.errors.nombre }}</div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Categoria</label>
                            <input v-model="form.categoria" type="text" class="w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.categoria" class="mt-1 text-sm text-red-500">{{ form.errors.categoria }}</div>
                        </div>

                        <div v-if="canDelete">
                            <label class="mb-1 block text-sm font-medium text-gray-700">URL foto (opcional)</label>
                            <input v-model="form.foto" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                            <div v-if="form.errors.foto" class="mt-1 text-sm text-red-500">{{ form.errors.foto }}</div>
                        </div>
                                       
                        </div>
                        <div v-if="estudiante.foto_src" class="w-1/3 pt-1">
                            <p class="mb-2 text-sm text-gray-600">Foto actual</p>
                            <img :src="estudiante.foto_src" alt="Foto estudiante" class="w-full h-auto rounded object-cover" />
                        </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <Link :href="route('estudiantes.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">Cancelar</Link>
                            <button type="submit" :disabled="form.processing" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
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
    estudiante: Object,
});

const form = useForm({
    nombre: props.estudiante.nombre,
    categoria: props.estudiante.categoria,
    foto: props.estudiante.foto_src || '',
});

const submit = () => {
    form.put(route('estudiantes.update', { estudiante: props.estudiante.id }));
};
</script>
