import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import SignaturePad from './Components/SignaturePad.vue';
import sssLogo from '@/assets/sss-logo.svg';

const appName = import.meta.env.VITE_APP_NAME || 'SSS Daily Transaction Logs';
const faviconLink = document.querySelector<HTMLLinkElement>('link[rel="icon"]') ?? document.createElement('link');
faviconLink.rel = 'icon';
faviconLink.type = 'image/svg+xml';
faviconLink.href = sssLogo;
if (!faviconLink.parentNode) {
    document.head.appendChild(faviconLink);
}

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        
        app.component('SignaturePad', SignaturePad);
        
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
