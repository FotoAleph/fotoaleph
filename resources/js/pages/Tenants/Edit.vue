<template>
    <AppLayout title="Edit Tenant">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Tenant
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <label for="razon_social" class="block text-sm font-medium text-gray-700">
                                Razon Social
                            </label>
                            <input
                                id="razon_social"
                                v-model="form.razon_social"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <div v-if="form.errors.razon_social" class="text-red-500 text-sm mt-1">
                                {{ form.errors.razon_social }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <Link
                                :href="route('tenants.index')"
                                class="mr-4 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                Update Tenant
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

defineProps({
    tenant: Object,
});

const form = useForm({
    razon_social: props.tenant.razon_social,
});

const submit = () => {
    form.put(route('tenants.update', props.tenant.id));
};
</script>