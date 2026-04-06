<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Proyectos JyM</h1>
                            <p class="text-sm text-gray-500">{{ tenant.razon_social }} · materiales y tipos de intervencion</p>
                        </div>
                        <Link :href="route('tenant-projects.create', { tenant: tenant.id })" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Nuevo proyecto
                        </Link>
                    </div>

                    <div v-if="$page.props.flash?.success" class="mx-6 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="project in projects" :key="project.id" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            <div class="h-40 bg-gray-100">
                                <img v-if="project.cover_url" :src="resolveUrl(project.cover_url)" alt="Portada proyecto" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">Sin portada</div>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex flex-wrap gap-2 text-xs font-medium">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ project.categoria }}</span>
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ project.grupo }}</span>
                                    <span v-if="project.publicar_en_vitrina" class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">En vitrina</span>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">{{ project.nombre }}</h2>
                                    <p class="mt-1 line-clamp-3 text-sm text-gray-600">{{ project.descripcion }}</p>
                                </div>
                                <div class="text-xs text-gray-500">{{ project.media_urls.length }} archivos multimedia</div>
                                <div class="flex gap-2">
                                    <Link :href="route('tenant-projects.edit', { tenant: tenant.id, proyecto: project.id })" class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Editar
                                    </Link>
                                    <button class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="destroyProject(project)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Editar proyecto' : 'Registrar proyecto' }}</h2>
                        <p class="text-sm text-gray-500">Cada URL en una linea. Soporta imagen y video.</p>
                    </div>

                    <form class="space-y-4 p-6" @submit.prevent="submit">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                            <input v-model="projectForm.nombre" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                            <InputError :message="projectForm.errors.nombre" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Material / categoria</label>
                                <input v-model="projectForm.categoria" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="projectForm.errors.categoria" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo de intervencion / grupo</label>
                                <input v-model="projectForm.grupo" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="projectForm.errors.grupo" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Descripcion</label>
                            <textarea v-model="projectForm.descripcion" rows="4" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            <InputError :message="projectForm.errors.descripcion" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Multimedia</label>
                            <textarea v-model="projectForm.media_urls" rows="7" class="w-full rounded-md border-gray-300 font-mono text-sm shadow-sm" placeholder="/IMG/Fotos/proyecto.jpg&#10;https://cdn.example.com/video.mp4"></textarea>
                            <InputError :message="projectForm.errors.media_urls" />
                        </div>

                        <label class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <input v-model="projectForm.publicar_en_vitrina" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm" />
                            Publicar este proyecto como muestrario en vitrinas
                        </label>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" :disabled="projectForm.processing">
                                {{ isEditing ? 'Actualizar' : 'Guardar' }}
                            </button>
                            <Link :href="route('tenant-projects.index', { tenant: tenant.id })" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                                Limpiar
                            </Link>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
    projects: Array,
    form: Object,
    isEditing: Boolean,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Proyectos JyM', href: route('tenant-projects.index', { tenant: props.tenant.id }) },
];

const projectForm = useForm({
    id: props.form?.id || null,
    nombre: props.form?.nombre || '',
    descripcion: props.form?.descripcion || '',
    categoria: props.form?.categoria || '',
    grupo: props.form?.grupo || '',
    publicar_en_vitrina: !!props.form?.publicar_en_vitrina,
    media_urls: props.form?.media_urls || '',
});

const submit = () => {
    if (props.isEditing && projectForm.id) {
        projectForm.put(route('tenant-projects.update', { tenant: props.tenant.id, proyecto: projectForm.id }));

        return;
    }

    projectForm.post(route('tenant-projects.store', { tenant: props.tenant.id }));
};

const destroyProject = (project) => {
    if (!confirm(`Eliminar el proyecto "${project.nombre}"?`)) {
        return;
    }

    useForm().delete(route('tenant-projects.destroy', { tenant: props.tenant.id, proyecto: project.id }));
};

const resolveUrl = (url) => {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url;
    return `/storage/${url}`;
};
</script>