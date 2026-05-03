import { createInertiaApp, router } from '@inertiajs/vue3';
import { initializeTheme, updateTheme } from '@/composables/useAppearance';
import type { Appearance } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
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
