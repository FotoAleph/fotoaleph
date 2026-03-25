<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    FolderTree,
    LayoutGrid,
    MessageSquare,
    Plus,
    Tags,
    User
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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Mis PQRs',
        href: '/pqrs',
        icon: MessageSquare,
    },
    {
        title: 'Nueva Cotización',
        href: '/cotizaciones/create',
        icon: Plus,
    },
    {
        title: 'Grupos',
        href: '/grupos',
        icon: FolderTree,
    },
    {
        title: 'Categorías',
        href: '/categorias',
        icon: Tags,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: '/settings/profile',
        icon: User,
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
