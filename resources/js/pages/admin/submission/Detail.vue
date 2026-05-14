<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download, FileText, MessageSquare } from '@lucide/vue';
import { ArrowLeft } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useRelativeTime } from '@/composables/useDateFormat';
import { index } from '@/routes/admin/submission';
import { download } from '@/routes/file';

defineProps<{
    submission: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'submission.title',
                href: index().url,
            },
            {
                title: 'submission.detail.title',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="$t('submission.detail.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="index().url">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ $t('submission.detail.title') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('submission.detail.description') }}
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Info Panel -->
            <Card class="md:col-span-1">
                <CardHeader>
                    <CardTitle>{{ $t('submission.detail.info') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('submission.detail.task') }}</p>
                        <p class="font-medium">{{ submission.task?.title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('submission.detail.student') }}</p>
                        <p class="font-medium">{{ submission.user?.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ submission.user?.email }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('submission.detail.status') }}</p>
                        <Badge class="mt-1" variant="secondary">{{
                            submission.status ? $t('submission.status.' + submission.status.toLowerCase()) : $t('submission.status.waiting')
                        }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('submission.detail.score') }}</p>
                        <p class="text-xl font-bold">
                            {{
                                submission.score !== null
                                    ? submission.score
                                    : '-'
                            }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            {{ $t('submission.detail.submitted') }}
                        </p>
                        <p class="font-medium">
                            {{ useRelativeTime(submission.created_at) }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-4 md:col-span-2">
                <!-- Files Panel -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" /> {{ $t('submission.detail.files.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="
                                !submission.files ||
                                submission.files.length === 0
                            "
                            class="py-4 text-sm text-muted-foreground"
                        >
                            {{ $t('submission.detail.files.empty') }}
                        </div>
                        <ul v-else class="space-y-2">
                            <li
                                v-for="file in submission.files"
                                :key="file.id"
                                class="flex items-center justify-between rounded-md border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <FileText class="h-5 w-5 text-blue-500" />
                                    <span class="text-sm font-medium">{{
                                        file.original_name ?? file.name ?? 'Berkas'
                                    }}</span>
                                </div>
                                <Button size="sm" variant="secondary" as-child>
                                    <a
                                        :href="download({ file: file.id }).url"
                                        target="_blank"
                                        class="flex items-center gap-2"
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                        {{ $t('submission.detail.files.download') }}
                                    </a>
                                </Button>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- AI Feedback Panel -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MessageSquare class="h-5 w-5" /> {{ $t('submission.detail.feedback.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="
                                !submission.ai_feedbacks ||
                                submission.ai_feedbacks.length === 0
                            "
                            class="py-4 text-sm text-muted-foreground"
                        >
                            {{ $t('submission.detail.feedback.empty') }}
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="feedback in submission.ai_feedbacks"
                                :key="feedback.id"
                                class="rounded-md border bg-muted/50 p-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span class="text-sm font-semibold"
                                        >AI Evaluator</span
                                    >
                                    <span
                                        class="text-xs text-muted-foreground"
                                        >{{ useRelativeTime(feedback.created_at) }}</span
                                    >
                                </div>
                                <div class="text-sm whitespace-pre-wrap">
                                    {{ feedback.feedback }}
                                </div>
                                <div
                                    class="mt-2 text-right text-xs font-semibold text-primary"
                                    v-if="feedback.score"
                                >
                                    {{ $t('submission.detail.feedback.score') }}: {{ feedback.score }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
