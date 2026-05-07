<script setup lang="ts">
import type { Component } from "vue";
import { cn } from "@/lib/utils";

defineProps<{
    icon: Component
    cardClass?: string
    iconClass?: string
}>();
</script>

<template>
    <div
        :class="cn(
            'relative overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 p-8 text-left',
            cardClass,
        )"
    >
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <component :is="icon" :class="cn(iconClass, 'h-24 w-24')" />
        </div>
        <div class="mb-4">
            <slot name="title" />
        </div>
        <slot />
    </div>
</template>
