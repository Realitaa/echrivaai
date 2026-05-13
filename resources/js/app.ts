import { createInertiaApp, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { i18nVue } from 'laravel-vue-i18n'; 
import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { initializeTheme, updateTheme } from '@/composables/useAppearance';
import type { Appearance } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import 'dayjs/locale/id';
import 'dayjs/locale/en';
import 'dayjs/locale/fr';

dayjs.extend(relativeTime);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const defaultLocale = import.meta.env.VITE_APP_LOCALE || 'en';

createInertiaApp({
    title: (title) => (title ? `${title}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const locale = props.initialPage.props.locale as string || defaultLocale;
        dayjs.locale(locale);
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                lang: locale,
                resolve: (lang: string) => {
                    const langs = import.meta.glob<{ default: any }>('../../lang/*.json', { eager: true });

                    return langs[`../../lang/php_${lang}.json`]?.default;
                }
            })
            .mount(el);
    },
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#9810fa',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

router.on('navigate', (event) => {
    if (event.detail.page.props.locale) {
        dayjs.locale(event.detail.page.props.locale as string);
    }

    const isAuth = event.detail.page.component.startsWith('auth/');
    const isWelcome = event.detail.page.component === 'Welcome';

    if (isAuth || isWelcome) {
        document.documentElement.classList.remove('dark');
    } else {
        const savedAppearance = localStorage.getItem('appearance');
        updateTheme((savedAppearance as Appearance) || 'system');
    }
});

// This will listen for flash toast data from the server...
initializeFlashToast();
