/**
 * Composable para acceder a la función route() de Laravel globalmente
 * Esta función genera URLs para rutas nombradas de Laravel Breeze
 */

declare global {
    function route(name: string, params?: Record<string, any>): string;
}

export const useRoute = () => {
    if (typeof window !== 'undefined' && (window as any).route) {
        return (window as any).route;
    }

    // Función fallback si no está disponible globalmente
    return (name: string, params?: Record<string, any>): string => {
        console.warn(`route('${name}') llamado pero la función route no está disponible`);
        return '';
    };
};
