import dayjs from 'dayjs';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';

export const currentLocale = ref('en');

watch(
    currentLocale,
    async (locale) => {
        dayjs.locale(locale.toLowerCase());
        await loadLanguageAsync(locale);
    },
    { immediate: true }
);