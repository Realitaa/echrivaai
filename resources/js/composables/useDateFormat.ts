import dayjs from 'dayjs';
import type { MaybeRef } from 'vue';
import { computed, unref } from 'vue';
import { currentLocale } from '@/lib/i18n';

/**
 * Hook to get a reactive fromNow() string.
 * Automatically updates when the global locale changes.
 */
export function useFromNow(date: MaybeRef<string | Date | number | null | undefined>) {
    return computed(() => {
        const d = unref(date);

        if (!d) {
            return '';
        }

        return dayjs(d).locale(currentLocale.value).fromNow();
    });
}

/**
 * Hook to get a reactive formatted date string.
 * Automatically updates when the global locale changes.
 */
export function useDateFormat(
    date: MaybeRef<string | Date | number | null | undefined>,
    format = 'DD MMM YYYY'
) {
    return computed(() => {
        const d = unref(date);

        if (!d) {
            return '';
        }

        return dayjs(d).locale(currentLocale.value).format(format);
    });
}

// Backward compatibility aliases
export const useRelativeTime = useFromNow;
export const useFormattedDate = useDateFormat;