import { useRoleGuard } from './useRoleGuard';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

/**
 * Composable para proteger rutas basado en roles
 * Redirige automáticamente al dashboard si el usuario no tiene los roles requeridos
 * 
 * @param requiredRoles - rol o array de roles permitidos
 * @example useRoleRedirect('admin'); // Solo admins
 * @example useRoleRedirect(['admin', 'coordinador']); // Admin o coordinador
 */
export const useRoleRedirect = (requiredRoles: string | string[]) => {
    const { canAccess } = useRoleGuard();
    
    onMounted(() => {
        // Verificar roles al montar el componente
        if (!canAccess(requiredRoles)) {
            // Redirigir al dashboard sin guardar en el historial
            router.visit(route('dashboard'), { replace: true });
        }
    });
    
    return {
        canAccess: () => canAccess(requiredRoles),
    };
};
