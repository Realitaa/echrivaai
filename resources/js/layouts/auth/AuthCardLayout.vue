<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Brain } from '@lucide/vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-linear-to-b from-[#EDE9FE] to-white p-6 font-sans text-slate-900 selection:bg-purple-200 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center text-xl font-bold tracking-tight text-slate-900"
            >
                <Brain class="h-8 w-8 text-purple-600" />
                EchrivaAI
            </Link>

            <div class="relative flex flex-col gap-6">
                <div
                    class="absolute -inset-1 rounded-2xl bg-linear-to-b from-purple-200 to-transparent opacity-50 blur-lg"
                ></div>
                <Card
                    class="relative rounded-2xl border-slate-200 bg-white shadow-2xl"
                >
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle
                            class="text-2xl font-bold tracking-tight text-slate-900"
                            >{{ title }}</CardTitle
                        >
                        <CardDescription class="mt-2 text-slate-500">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
