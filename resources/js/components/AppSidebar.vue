<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { FileText, GraduationCap, LayoutGrid } from 'lucide-vue-next';
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
    const items: NavItem[] = [];

    for (const tenant of tenants) {
        if (tenant.database_connection === 'tenant_jym') {
            items.push({
                title: `JyM: ${tenant.razon_social}`,
                href: route('tenant-projects.index', { tenant: tenant.id }),
                icon: FileText,
            });
        }

        if (tenant.database_connection === 'tenant_casa_angel') {
            items.push({
                title: `Casa Angel: ${tenant.razon_social}`,
                href: route('tenant-events.index', { tenant: tenant.id }),
                icon: FileText,
            });
        }

        if (tenant.database_connection === 'tenant_biotek') {
            items.push({
                title: `Biotek: ${tenant.razon_social}`,
                href: route('biotek-students.index', { tenant: tenant.id }),
                icon: GraduationCap,
            });
        }

        if (tenant.database_connection === 'tenant_sport_bogota') {
            items.push({
                title: `Sport Bogota: ${tenant.razon_social}`,
                href: '/estudiantes',
                icon: GraduationCap,
            });
        }
    }

    return items;
});

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    ...tenantLinks.value,
]);

const footerNavItems: NavItem[] = [];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
