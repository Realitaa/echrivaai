<script setup lang="ts">
import { ScrollArea } from '@/components/ui/scroll-area'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <ScrollArea class="h-[calc(100vh-80px)]">
            <slot />
        </ScrollArea>
    </AppLayout>
</template>
