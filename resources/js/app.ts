import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/700.css';
import { initializeTheme } from '@/composables/useAppearance';
// 1. Importamos el plugin de traducciones para Laravel + Vue
import { i18nVue } from 'laravel-vue-i18n';
// 2. Importamos el plugin de rutas
import routePlugin from '@/plugins/routePlugin';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            // 3. Registrar plugin de rutas
            .use(routePlugin);
        
        app
            // 4. Registramos el plugin i18nVue y configuramos la carga dinámica
            .use(i18nVue, {
                resolve: async (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json');
                    const path = `../../lang/${lang}.json`;
                    if (langs[path]) {
                        return await langs[path]();
                    }
                },
            })
            .mount(el);
    },
    
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();