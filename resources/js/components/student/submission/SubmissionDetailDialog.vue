<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import {
    Loader2,
    FileText,
    TrendingUp,
    TrendingDown,
    Minus,
    Clock,
    CheckCircle,
    AlertCircle,
    Bot,
    GraduationCap,
    Download,
} from '@lucide/vue';
import dayjs from 'dayjs';
import { ref, watch, computed } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { show as showSubmission } from '@/routes/student/classroom/task/submission';

interface SubmissionDetail {
    id: number;
    version: number;
    status: string;
    content: string;
    submitted_at: string;
    ai_feedbacks: Array<{
        id: number;
        result: string;
        score: number;
    }>;
    rubric_scores: Array<{
        id: number;
        task_rubric_id: number;
        score_ai: number | null;
        feedback_ai: string | null;
        score_teacher: number | null;
        feedback_teacher: string | null;
        rubric?: {
            title: string;
            max_score: number;
        };
    }>;
    files: Array<{
        id: number;
        original_name: string;
        path: string;
    }>;
}

interface Progress {
    previous_score: number;
    current_score: number;
    label: string;
}

const dialogOpen = defineModel<boolean>('open', { default: false });

const props = defineProps<{
    classroomId: number;
    taskId: number;
    submissionId: number | null;
}>();

const submissionData = ref<SubmissionDetail | null>(null);
const progressData = ref<Progress | null>(null);
const isLoading = ref(false);

const fetchHttp = useHttp<Record<string, never>>({});

watch(
    () => [dialogOpen.value, props.submissionId] as const,
    async ([open, id]) => {
        if (open && id) {
            isLoading.value = true;
            submissionData.value = null;
            progressData.value = null;

            try {
                const response = await fetchHttp.get(
                    showSubmission({
                        classroom: props.classroomId,
                        task: props.taskId,
                        submission: id as number,
                    }).url,
                ) as unknown as { submission: SubmissionDetail; progress: Progress | null };

                submissionData.value = response.submission;
                progressData.value = response.progress;
            } catch {
                // handle error silently
            } finally {
                isLoading.value = false;
            }
        }
    },
);

const statusConfig = computed(() => {
    const status = submissionData.value?.status ?? '';
    switch (status) {
        case 'processing':
            return { icon: Clock, label: 'Sedang Diproses AI', variant: 'secondary' as const, color: 'text-yellow-600' };
        case 'graded':
            return { icon: CheckCircle, label: 'Sudah Dinilai', variant: 'default' as const, color: 'text-green-600' };
        case 'failed':
            return { icon: AlertCircle, label: 'Gagal Diproses', variant: 'destructive' as const, color: 'text-red-600' };
        default:
            return { icon: Clock, label: 'Terkirim', variant: 'secondary' as const, color: 'text-gray-600' };
    }
});

const progressIcon = computed(() => {
    if (!progressData.value) return null;
    switch (progressData.value.label) {
        case 'Meningkat':
            return TrendingUp;
        case 'Menurun':
            return TrendingDown;
        default:
            return Minus;
    }
});

const progressColor = computed(() => {
    if (!progressData.value) return '';
    switch (progressData.value.label) {
        case 'Meningkat':
            return 'text-green-600';
        case 'Menurun':
            return 'text-red-600';
        default:
            return 'text-gray-500';
    }
});
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-2xl lg:min-w-7xl max-h-[85vh]">
            <DialogHeader>
                <DialogTitle>
                    Detail Pengumpulan
                    <Badge v-if="submissionData" variant="outline" class="ml-2">
                        Versi {{ submissionData.version }}
                    </Badge>
                </DialogTitle>
                <DialogDescription>
                    Lihat detail pengumpulan dan umpan balik.
                </DialogDescription>
            </DialogHeader>

            <!-- Loading -->
            <div v-if="isLoading" class="flex items-center justify-center py-12">
                <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
            </div>

            <!-- Content -->
            <ScrollArea v-else-if="submissionData" class="max-h-[60vh] pr-4">
                <div class="space-y-5">
                    <!-- Status & Meta -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <component :is="statusConfig.icon" class="h-5 w-5" :class="statusConfig.color" />
                            <Badge :variant="statusConfig.variant">{{ statusConfig.label }}</Badge>
                        </div>
                        <span class="text-sm text-muted-foreground">
                            {{ dayjs(submissionData.submitted_at).format('DD MMM YYYY HH:mm') }}
                        </span>
                    </div>

                    <!-- Progress Comparison -->
                    <Card v-if="progressData" class="border-primary/20">
                        <CardContent class="pt-4 pb-4">
                            <div class="flex items-center gap-3">
                                <component :is="progressIcon" class="h-5 w-5" :class="progressColor" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium" :class="progressColor">
                                        {{ progressData.label }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Skor sebelumnya: {{ progressData.previous_score }} → Skor saat ini: {{ progressData.current_score }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submitted Content -->
                    <div class="space-y-2">
                        <h4 class="text-sm font-semibold">Konten Esai</h4>
                        <div class="rounded-lg border bg-muted/30 p-4">
                            <p class="text-sm whitespace-pre-wrap">{{ submissionData.content }}</p>
                        </div>
                    </div>

                    <!-- Attached Files -->
                    <div v-if="submissionData.files?.length" class="space-y-2">
                        <h4 class="text-sm font-semibold">Lampiran</h4>
                        <div class="space-y-2">
                            <a
                                v-for="file in submissionData.files"
                                :key="file.id"
                                :href="`/storage/${file.path}`"
                                target="_blank"
                                class="flex items-center gap-3 rounded-md border p-3 hover:bg-muted/50 transition-colors"
                            >
                                <FileText class="h-4 w-4 shrink-0 text-blue-500" />
                                <span class="truncate text-sm font-medium flex-1">{{ file.original_name }}</span>
                                <Download class="h-4 w-4 shrink-0 text-muted-foreground" />
                            </a>
                        </div>
                    </div>

                    <Separator />

                    <!-- Processing State -->
                    <div v-if="submissionData.status === 'processing'" class="text-center py-6 space-y-2">
                        <Loader2 class="h-8 w-8 animate-spin text-primary mx-auto" />
                        <p class="text-sm text-muted-foreground">
                            AI sedang memproses pengumpulan Anda. Mohon tunggu...
                        </p>
                    </div>

                    <!-- Failed State -->
                    <div v-else-if="submissionData.status === 'failed'" class="text-center py-6 space-y-2">
                        <AlertCircle class="h-8 w-8 text-destructive mx-auto" />
                        <p class="text-sm text-muted-foreground">
                            Terjadi kesalahan saat memproses pengumpulan Anda. Silakan coba lagi.
                        </p>
                    </div>

                    <!-- Graded: AI Feedback -->
                    <template v-else-if="submissionData.status === 'graded'">
                        <div v-if="submissionData.ai_feedbacks?.length" class="space-y-3">
                            <h4 class="text-sm font-semibold flex items-center gap-2">
                                <Bot class="h-4 w-4 text-primary" /> Umpan Balik AI
                            </h4>
                            <Card v-for="feedback in submissionData.ai_feedbacks" :key="feedback.id">
                                <CardContent class="pt-4 pb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <Badge variant="secondary" class="text-base font-bold px-3 py-1">
                                            Skor: {{ feedback.score }}
                                        </Badge>
                                    </div>
                                    <p class="text-sm whitespace-pre-wrap">{{ feedback.result }}</p>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Rubric Scores -->
                        <div v-if="submissionData.rubric_scores?.length" class="space-y-3">
                            <h4 class="text-sm font-semibold">Skor Per Rubrik</h4>
                            <div class="space-y-3">
                                <Card
                                    v-for="score in submissionData.rubric_scores"
                                    :key="score.id"
                                    class="overflow-hidden"
                                >
                                    <CardHeader class="pb-2 pt-3 px-4">
                                        <CardTitle class="text-sm font-medium flex items-center justify-between">
                                            <span>{{ score.rubric?.title ?? `Rubrik #${score.task_rubric_id}` }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                Maks: {{ score.rubric?.max_score ?? '-' }}
                                            </span>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent class="pb-3 px-4 space-y-2">
                                        <!-- AI Score -->
                                        <div v-if="score.score_ai !== null" class="flex items-start gap-2">
                                            <Bot class="h-4 w-4 mt-0.5 shrink-0 text-primary" />
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <Badge variant="outline" class="text-xs">
                                                        AI: {{ score.score_ai }}
                                                    </Badge>
                                                </div>
                                                <p v-if="score.feedback_ai" class="text-xs text-muted-foreground mt-1">
                                                    {{ score.feedback_ai }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Teacher Score -->
                                        <div v-if="score.score_teacher !== null" class="flex items-start gap-2">
                                            <GraduationCap class="h-4 w-4 mt-0.5 shrink-0 text-orange-500" />
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <Badge variant="outline" class="text-xs border-orange-300">
                                                        Guru: {{ score.score_teacher }}
                                                    </Badge>
                                                </div>
                                                <p v-if="score.feedback_teacher" class="text-xs text-muted-foreground mt-1">
                                                    {{ score.feedback_teacher }}
                                                </p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </template>
                </div>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>
