<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, MessageSquare } from '@lucide/vue';
import dayjs from 'dayjs';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { index } from '@/routes/admin/submission';

defineProps<{
    submission: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Penugasan',
                href: index().url,
            },
            {
                title: 'Detail Penugasan',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head title="Detail Penugasan" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="index().url">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Detail Penugasan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Detail kiriman tugas dari siswa.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Info Panel -->
            <Card class="md:col-span-1">
                <CardHeader>
                    <CardTitle>Informasi Penugasan</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Tugas</p>
                        <p class="font-medium">{{ submission.task?.title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Siswa</p>
                        <p class="font-medium">{{ submission.user?.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ submission.user?.email }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge class="mt-1" variant="secondary">{{
                            submission.status ?? 'Menunggu'
                        }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Nilai</p>
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
                            Dikirim Pada
                        </p>
                        <p class="font-medium">
                            {{
                                dayjs(submission.created_at).format(
                                    'DD MMM YYYY, HH:mm',
                                )
                            }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-4 md:col-span-2">
                <!-- Files Panel -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" /> File Kiriman
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
                            Tidak ada file yang dikirimkan.
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
                                        file.name ?? 'Berkas'
                                    }}</span>
                                </div>
                                <Button size="sm" variant="secondary" as-child>
                                    <a
                                        :href="file.path ?? file.url"
                                        target="_blank"
                                        >Unduh</a
                                    >
                                </Button>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- AI Feedback Panel -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MessageSquare class="h-5 w-5" /> Feedback AI
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
                            Belum ada feedback dari AI.
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
                                        >{{
                                            dayjs(feedback.created_at).format(
                                                'DD MMM YYYY, HH:mm',
                                            )
                                        }}</span
                                    >
                                </div>
                                <div class="text-sm whitespace-pre-wrap">
                                    {{ feedback.feedback }}
                                </div>
                                <div
                                    class="mt-2 text-right text-xs font-semibold text-primary"
                                    v-if="feedback.score"
                                >
                                    Skor AI: {{ feedback.score }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
