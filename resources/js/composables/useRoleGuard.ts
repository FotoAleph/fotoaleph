import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useRoleGuard = () => {
    const page = usePage();
    
    const user = computed(() => page.props.auth.user);
    const userRole = computed(() => user.value?.role);
    
    const isAdmin = computed(() => userRole.value === 'admin');
    const isCoordinator = computed(() => userRole.value === 'coordinador');
    const isClient = computed(() => userRole.value === 'cliente');
    
    const hasRole = (role: string | string[]) => {
        if (Array.isArray(role)) {
            return role.includes(userRole.value);
        }
        return userRole.value === role;
    };
    
    const canAccess = (requiredRoles: string | string[]) => {
        return hasRole(requiredRoles);
    };
    
    return {
        user,
        userRole,
        isAdmin,
        isCoordinator,
        isClient,
        hasRole,
        canAccess,
    };
};
