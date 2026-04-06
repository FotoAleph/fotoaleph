<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Eventos Casa Angel</h1>
                            <p class="text-sm text-gray-500">{{ tenant.razon_social }} · registro multimedia y seleccion de vitrinas</p>
                        </div>
                        <Link :href="route('tenant-events.create', { tenant: tenant.id })" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Nuevo evento
                        </Link>
                    </div>

                    <div v-if="$page.props.flash?.success" class="mx-6 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="event in events" :key="event.id" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            <div class="h-40 bg-gray-100">
                                <img v-if="event.cover_url" :src="resolveUrl(event.cover_url)" alt="Portada evento" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">Sin portada</div>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex flex-wrap gap-2 text-xs font-medium">
                                    <span v-if="event.codigo" class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ event.codigo }}</span>
                                    <span v-if="event.publicar_en_vitrina" class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">En vitrina</span>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">{{ event.nombre }}</h2>
                                    <p class="mt-1 line-clamp-3 text-sm text-gray-600">{{ event.descripcion }}</p>
                                </div>
                                <div class="space-y-1 text-xs text-gray-500">
                                    <div>{{ formatDate(event.fecha_evento) }}</div>
                                    <div>{{ event.ubicacion || 'Sin ubicacion' }}</div>
                                    <div>{{ event.media_urls.length }} archivos multimedia</div>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="route('tenant-events.edit', { tenant: tenant.id, evento: event.id })" class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Editar
                                    </Link>
                                    <button class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="destroyEvent(event)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Editar evento' : 'Registrar evento' }}</h2>
                        <p class="text-sm text-gray-500">Cada URL en una linea. Soporta imagen y video.</p>
                    </div>

                    <form class="space-y-4 p-6" @submit.prevent="submit">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                            <input v-model="eventForm.nombre" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                            <InputError :message="eventForm.errors.nombre" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha y hora</label>
                                <input v-model="eventForm.fecha_evento" type="datetime-local" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="eventForm.errors.fecha_evento" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Codigo</label>
                                <input v-model="eventForm.codigo" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="eventForm.errors.codigo" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ubicacion</label>
                            <input v-model="eventForm.ubicacion" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                            <InputError :message="eventForm.errors.ubicacion" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Descripcion</label>
                            <textarea v-model="eventForm.descripcion" rows="4" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            <InputError :message="eventForm.errors.descripcion" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Multimedia</label>
                            <textarea v-model="eventForm.media_urls" rows="7" class="w-full rounded-md border-gray-300 font-mono text-sm shadow-sm"></textarea>
                            <InputError :message="eventForm.errors.media_urls" />
                        </div>

                        <label class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <input v-model="eventForm.publicar_en_vitrina" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm" />
                            Publicar este evento como muestrario en vitrinas
                        </label>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" :disabled="eventForm.processing">
                                {{ isEditing ? 'Actualizar' : 'Guardar' }}
                            </button>
                            <Link :href="route('tenant-events.index', { tenant: tenant.id })" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
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
    events: Array,
    form: Object,
    isEditing: Boolean,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Eventos Casa Angel', href: route('tenant-events.index', { tenant: props.tenant.id }) },
];

const eventForm = useForm({
    id: props.form?.id || null,
    nombre: props.form?.nombre || '',
    descripcion: props.form?.descripcion || '',
    fecha_evento: props.form?.fecha_evento || '',
    ubicacion: props.form?.ubicacion || '',
    codigo: props.form?.codigo || '',
    publicar_en_vitrina: !!props.form?.publicar_en_vitrina,
    media_urls: props.form?.media_urls || '',
});

const submit = () => {
    if (props.isEditing && eventForm.id) {
        eventForm.put(route('tenant-events.update', { tenant: props.tenant.id, evento: eventForm.id }));

        return;
    }

    eventForm.post(route('tenant-events.store', { tenant: props.tenant.id }));
};

const destroyEvent = (event) => {
    if (!confirm(`Eliminar el evento "${event.nombre}"?`)) {
        return;
    }

    useForm().delete(route('tenant-events.destroy', { tenant: props.tenant.id, evento: event.id }));
};

const resolveUrl = (url) => {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url;
    return `/storage/${url}`;
};

const formatDate = (value) => {
    if (!value) return 'Sin fecha';
    return new Date(value).toLocaleString();
};
</script>