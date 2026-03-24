import type { App } from 'vue';

declare global {
    function route(name: string, params?: Record<string, any>): string;
}

/**
 * Plugin para exponer la función route() de Laravel en Vue
 * La función route() se carga desde window.route que se define en app.blade.php
 */
export default {
    install(app: App) {
        // Obtener la función route de window (definida en app.blade.php)
        const getRoute = () => {
            if (typeof window !== 'undefined' && typeof (window as any).route === 'function') {
                return (window as any).route;
            }
            // Fallback: función que devuelve un string vacío
            return (name: string, params?: Record<string, any>) => {
                console.warn(`route() function not available. Try to call route('${name}')`);
                return '#';
            };
        };

        const routeFunction = getRoute();
        
        // Exponer sin el prefijo $ para que sea accesible como route() en templates
        app.config.globalProperties.route = routeFunction;
        
        // Proporcionar para inyección de dependencias
        app.provide('route', routeFunction);
    },
};
