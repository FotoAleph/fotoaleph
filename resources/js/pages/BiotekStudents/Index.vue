<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-[1.15fr_0.85fr]">
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Estudiantes Biotek</h1>
                            <p class="text-sm text-gray-500">{{ tenant.razon_social }} · carnetizacion y control de pagos por taller</p>
                        </div>
                        <Link :href="route('biotek-students.create', { tenant: tenant.id })" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Nuevo estudiante
                        </Link>
                    </div>

                    <div v-if="$page.props.flash?.success" class="mx-6 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="student in students" :key="student.id" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            <div class="h-40 bg-gray-100">
                                <img v-if="student.foto_src" :src="resolveUrl(student.foto_src)" alt="Foto estudiante Biotek" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">Sin foto</div>
                            </div>
                            <div class="space-y-3 p-4">
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">{{ student.nombre_completo }}</h2>
                                    <p class="text-sm text-gray-500">{{ student.identificacion }}</p>
                                </div>
                                <div class="space-y-2 rounded-lg bg-gray-50 p-3 text-xs text-gray-600">
                                    <div v-for="taller in student.talleres" :key="`${student.id}-${taller.id}`" class="rounded border border-gray-200 bg-white px-3 py-2">
                                        <div class="font-semibold text-gray-800">{{ taller.nombre }}</div>
                                        <div>{{ taller.fecha || 'Sin fecha' }} · {{ taller.duracion || 'Sin duracion' }}</div>
                                        <div>Pago: {{ currency(taller.pago) }} · Abono: {{ currency(taller.abono) }}</div>
                                        <div>Debe: {{ currency(taller.debe) }} · Saldo: {{ currency(taller.saldo_total) }}</div>
                                    </div>
                                    <div v-if="!student.talleres.length">Sin talleres registrados.</div>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="route('biotek-students.edit', { tenant: tenant.id, biotekEstudiante: student.id })" class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Editar
                                    </Link>
                                    <button class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="destroyStudent(student)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Editar estudiante' : 'Registrar estudiante' }}</h2>
                        <p class="text-sm text-gray-500">Un taller por fila con su estado de pago.</p>
                    </div>

                    <form class="space-y-4 p-6" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nombres</label>
                                <input v-model="studentForm.nombres" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="studentForm.errors.nombres" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Apellidos</label>
                                <input v-model="studentForm.apellidos" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                <InputError :message="studentForm.errors.apellidos" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Identificacion</label>
                            <input v-model="studentForm.identificacion" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                            <InputError :message="studentForm.errors.identificacion" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Foto</label>
                            <input v-model="studentForm.foto" type="text" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="/storage/img/biotek/estudiantes/foto.jpg" />
                            <InputError :message="studentForm.errors.foto" />
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">Talleres</h3>
                                <button type="button" class="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200" @click="addWorkshop">
                                    Agregar taller
                                </button>
                            </div>

                            <div v-for="(workshop, index) in studentForm.talleres" :key="index" class="space-y-3 rounded-lg border border-gray-200 p-4">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre taller</label>
                                        <input v-model="workshop.nombre" type="text" class="w-full rounded-md border-gray-300 shadow-sm" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha</label>
                                        <input v-model="workshop.fecha" type="text" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="2026-04-06" />
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Duracion</label>
                                    <input v-model="workshop.duracion" type="text" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="2 horas" />
                                </div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Pago</label>
                                        <input v-model.number="workshop.pago" type="number" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Abono</label>
                                        <input v-model.number="workshop.abono" type="number" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm" />
                                    </div>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Debe</label>
                                        <input v-model.number="workshop.debe" type="number" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Saldo total</label>
                                        <input v-model.number="workshop.saldo_total" type="number" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm" />
                                    </div>
                                </div>
                                <button v-if="studentForm.talleres.length > 1" type="button" class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="removeWorkshop(index)">
                                    Quitar taller
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" :disabled="studentForm.processing">
                                {{ isEditing ? 'Actualizar' : 'Guardar' }}
                            </button>
                            <Link :href="route('biotek-students.index', { tenant: tenant.id })" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
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
    students: Array,
    form: Object,
    isEditing: Boolean,
});

const blankWorkshop = () => ({
    nombre: '',
    fecha: '',
    duracion: '',
    pago: 0,
    abono: 0,
    debe: 0,
    saldo_total: 0,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Estudiantes Biotek', href: route('biotek-students.index', { tenant: props.tenant.id }) },
];

const studentForm = useForm({
    id: props.form?.id || null,
    nombres: props.form?.nombres || '',
    apellidos: props.form?.apellidos || '',
    identificacion: props.form?.identificacion || '',
    foto: props.form?.foto || '',
    talleres: props.form?.talleres?.length ? props.form.talleres : [blankWorkshop()],
});

const addWorkshop = () => {
    studentForm.talleres.push(blankWorkshop());
};

const removeWorkshop = (index) => {
    studentForm.talleres.splice(index, 1);
};

const submit = () => {
    if (props.isEditing && studentForm.id) {
        studentForm.put(route('biotek-students.update', { tenant: props.tenant.id, biotekEstudiante: studentForm.id }));

        return;
    }

    studentForm.post(route('biotek-students.store', { tenant: props.tenant.id }));
};

const destroyStudent = (student) => {
    if (!confirm(`Eliminar al estudiante "${student.nombre_completo}"?`)) {
        return;
    }

    useForm().delete(route('biotek-students.destroy', { tenant: props.tenant.id, biotekEstudiante: student.id }));
};

const resolveUrl = (url) => {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url;
    return `/storage/${url}`;
};

const currency = (value) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
</script>