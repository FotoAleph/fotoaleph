<template>
    <AppLayout title="Estudiantes">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white dark:bg-stone-800 shadow-xl">
                    <div class="border-b border-gray-200 bg-white dark:bg-stone-900 p-6 lg:p-8">
                        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <h2 class="text-2xl font-medium text-gray-900">Estudiantes Sport Bogota</h2>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    :href="downloadAllHref"
                                    class="rounded bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600"
                                >
                                    Descargar imagenes (ZIP)
                                </a>
                                <Link
                                    v-if="canCreate"
                                    :href="route('estudiantes.create')"
                                    class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                                >
                                    Crear estudiante
                                </Link>
                            </div>
                        </div>

                        <div v-if="$page.props.flash?.success" class="mb-4 rounded border border-green-400 bg-green-100 p-4 text-green-700">
                            {{ $page.props.flash.success }}
                        </div>

                        <form class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-5" @submit.prevent="applyFilters">
                            <input
                                v-model="filterForm.nombre"
                                type="text"
                                placeholder="Filtrar por nombre"
                                class="rounded-md border-gray-300 shadow-sm"
                            />
                   
                            <select v-model="filterForm.sort_by" class="rounded-md border-gray-300 shadow-sm">
                                <option value="created_at">Fecha de creacion</option>
                                <option value="nombre">Nombre</option>
                                <option value="categoria">Categoria</option>
                            </select>
                            <select v-model="filterForm.sort_dir" class="rounded-md border-gray-300 shadow-sm">
                                <option value="desc">Descendente</option>
                                <option value="asc">Ascendente</option>
                            </select>
                            <div class="flex gap-2">
                                <button type="submit" class="rounded bg-blue-500 px-3 py-2 font-semibold text-white hover:bg-blue-700">
                                    Filtrar
                                </button>
                                <button type="button" class="rounded bg-gray-200 px-3 py-2 font-semibold text-gray-700 hover:bg-gray-300" @click="resetFilters">
                                    Limpiar
                                </button>
                            </div>
                        </form>

                        <div v-if="estudiantes.data.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            <article
                                v-for="estudiante in estudiantes.data"
                                :key="estudiante.id"
                                class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:bg-olive-950 shadow-sm transition hover:shadow-md"
                            >
                                <div class="relative h-48 bg-gray-100">
                                    <img
                                        v-if="estudiante.foto_src"
                                        :src="resolveStorageUrl(estudiante.foto_src)"
                                        alt="Foto estudiante"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full items-center justify-center text-sm font-medium text-gray-500">
                                        Sin foto
                                    </div>
                                    <span class="absolute left-3 top-3 rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white">
                                        {{ estudiante.categoria }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 space-y-3 p-4">
                                    <h3 class="col-span-2 line-clamp-2 text-base font-semibold text-gray-900 dark:text-yellow-200">{{ estudiante.nombre }}</h3>

                                    <a
                                        v-if="estudiante.foto_src"
                                        :href="resolveStorageUrl(estudiante.foto_src)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex text-sm font-medium text-blue-700 hover:text-blue-900"
                                    >
                                        Ver
                                    </a>

                                    <a
                                        v-if="estudiante.foto_src"
                                        :href="route('estudiantes.download', { estudiante: estudiante.id })"
                                        class="inline-flex text-sm font-medium text-emerald-700 hover:text-emerald-900"
                                    >
                                        Descargar imagen
                                    </a>

                                    <div class="flex items-center gap-3 pt-1">
                                        <Link
                                            v-if="canEdit"
                                            :href="route('estudiantes.edit', { estudiante: estudiante.id })"
                                            class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="canDelete"
                                            class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                            @click="deleteEstudiante(estudiante.id, estudiante.nombre)"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-600">
                            No hay estudiantes para los filtros seleccionados.
                        </div>

                        <div v-if="estudiantes.links" class="mt-6">
                            <div class="flex justify-center gap-2">
                                <Link
                                    v-for="link in estudiantes.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded px-3 py-2 text-sm font-medium',
                                        link.active ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                        !link.url && 'cursor-not-allowed opacity-50',
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
import { Link, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    estudiantes: Object,
    filters: Object,
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role);
const canCreate = computed(() => role.value === 'admin');
const canEdit = computed(() => ['admin', 'coordinador'].includes(role.value));
const canDelete = computed(() => role.value === 'admin');

const filterForm = useForm({
    nombre: props.filters?.nombre || '',
    categoria: props.filters?.categoria || '',
    sort_by: props.filters?.sort_by || 'created_at',
    sort_dir: props.filters?.sort_dir || 'desc',
    per_page: props.filters?.per_page || 10,
});

const applyFilters = () => {
    router.get(route('estudiantes.index'), filterForm.data(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.reset();
    filterForm.sort_by = 'created_at';
    filterForm.sort_dir = 'desc';
    filterForm.per_page = 10;
    applyFilters();
};

const deleteEstudiante = (id, nombre) => {
    if (confirm(`Estas seguro de eliminar al estudiante "${nombre}"?`)) {
        useForm().delete(route('estudiantes.destroy', { estudiante: id }));
    }
};

const downloadAllHref = computed(() => route('estudiantes.download-all', filterForm.data()));

const resolveStorageUrl = (path) => {
    if (!path) {
        return '';
    }

    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
        return path;
    }

    return `/storage/${path}`;
};
</script>
