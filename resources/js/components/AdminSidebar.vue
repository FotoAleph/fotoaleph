<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Building,
    FileText,
    GraduationCap,
    LayoutGrid,
    MessageSquare,
    Settings,
    Users
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const tenantLinks = computed<NavItem[]>(() => {
    const tenants = ((page.props.auth as any)?.tenants ?? []) as Array<{ id: number; razon_social: string; database_connection: string }>;

    return tenants.flatMap((tenant) => {
        if (tenant.database_connection === 'tenant_jym') {
            return [{ title: `JyM: ${tenant.razon_social}`, href: route('tenant-projects.index', { tenant: tenant.id }), icon: FileText }];
        }

        if (tenant.database_connection === 'tenant_casa_angel') {
            return [{ title: `Casa Angel: ${tenant.razon_social}`, href: route('tenant-events.index', { tenant: tenant.id }), icon: FileText }];
        }

        if (tenant.database_connection === 'tenant_biotek') {
            return [{ title: `Biotek: ${tenant.razon_social}`, href: route('biotek-students.index', { tenant: tenant.id }), icon: GraduationCap }];
        }

        if (tenant.database_connection === 'tenant_sport_bogota') {
            return [{ title: `Sport Bogota: ${tenant.razon_social}`, href: '/estudiantes', icon: GraduationCap }];
        }

        return [];
    });
});

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Usuarios',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Tenants',
        href: '/tenants',
        icon: Building,
    },
    {
        title: 'PQRs',
        href: '/pqrs',
        icon: MessageSquare,
    },
    {
        title: 'Cotizaciones',
        href: '/cotizaciones',
        icon: FileText,
    },
    ...tenantLinks.value,
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Configuración',
        href: '/settings',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
</template>
