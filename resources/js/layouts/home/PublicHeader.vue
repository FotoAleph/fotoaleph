<script setup lang="ts">
import { computed } from 'vue';
import { Headphones, Home, LayoutGrid, LogIn, Package, UserPlus } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();

const navigationItems = [
    { label: 'Inicio', href: '/', icon: Home },
    { label: 'Proyectos', href: '/proyectos', icon: LayoutGrid },
    { label: 'Productos', href: '/productos', icon: Package },
    { label: 'Contacto', href: '/contacto', icon: Headphones },
];

const currentPath = computed(() => page.url.split('?')[0].replace(/\/$/, '') || '/');
const isActive = (href: string) => currentPath.value === href;
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200/70 bg-white/90 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <Link href="/" class="flex min-w-0 items-center gap-3" aria-label="Ir al inicio">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <img src="/dina.svg" alt="DinamyCode" class="h-7 w-7" />
                </span>
                <span class="hidden text-lg font-extrabold tracking-tight text-cyan-500 sm:block">
                    Dinamy<span class="text-rose-500">Code</span>
                </span>
            </Link>

            <nav class="hidden items-center gap-1 rounded-md border border-slate-200 bg-slate-100 p-1 text-sm font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 md:flex">
                <Link
                    v-for="item in navigationItems"
                    :key="item.href"
                    :href="item.href"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-2 transition"
                    :class="
                        isActive(item.href)
                            ? 'bg-white text-cyan-700 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:text-cyan-300 dark:ring-slate-700'
                            : 'hover:bg-white/80 hover:text-slate-950 dark:hover:bg-slate-800/80 dark:hover:text-white'
                    "
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="flex items-center gap-2">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="dashboard()"
                    class="inline-flex items-center rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-900"
                >
                    {{ $page.props.auth?.user.name }}
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-slate-700 transition hover:bg-slate-100 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-900 sm:w-auto sm:px-3"
                        aria-label="Iniciar sesion"
                    >
                        <LogIn class="h-4 w-4" />
                        <span class="hidden text-sm font-semibold sm:ml-2 sm:inline">Entrar</span>
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="hidden items-center rounded-md bg-slate-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 sm:inline-flex"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Registro
                    </Link>
                </template>
            </div>
        </div>

        <nav class="grid grid-cols-4 border-t border-slate-200 bg-white text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 md:hidden">
            <Link
                v-for="item in navigationItems"
                :key="item.href"
                :href="item.href"
                class="flex flex-col items-center gap-1 px-2 py-2 transition"
                :class="isActive(item.href) ? 'text-cyan-600 dark:text-cyan-300' : 'hover:text-slate-950 dark:hover:text-white'"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <component :is="item.icon" class="h-4 w-4" />
                <span>{{ item.label }}</span>
            </Link>
        </nav>
    </header>
</template>
