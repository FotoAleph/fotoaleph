<template>
    <AppLayout title="Crear Categoría">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 lg:p-8 bg-white border-b border-gray-200 space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">Crear categoría</h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input v-model="form.nombre" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea v-model="form.descripcion" class="w-full border-gray-300 rounded-md shadow-sm" />
                            <div v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">{{ form.errors.descripcion }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                            <input v-model.number="form.nivel" type="number" min="0" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <div v-if="form.errors.nivel" class="text-red-500 text-sm mt-1">{{ form.errors.nivel }}</div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <Link :href="route('categorias.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</Link>
                            <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Guardar
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

const form = useForm({
    nombre: '',
    descripcion: '',
    nivel: 0,
});

const submit = () => {
    form.post(route('categorias.store'));
};
</script>
