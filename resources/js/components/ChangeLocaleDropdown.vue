<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Globe } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuSeparator,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { update } from '@/routes/locale';

const page = usePage();
const availableLocales = page.props.availableLocales;

function changeLocale(locale: string) {
    router.patch(
        update().url,
        { locale },
        {
            onSuccess: () => window.location.reload(),
        },
    );
}
</script>

<template>
  <DropdownMenu>
                <DropdownMenuTrigger as-child>
                <Button variant="outline" size="icon" aria-label="Language">
                    <Globe class="h-4 w-4" />
                </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56" align="start">
                <DropdownMenuLabel>{{ $t('navigation.i18n.title') }}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                    <DropdownMenuItem v-for="(locale, key) in availableLocales" :key="key" @click="changeLocale(key)">
                        {{ locale }}
                    </DropdownMenuItem>
                </DropdownMenuGroup>
                </DropdownMenuContent>
            </DropdownMenu>
</template>