<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import {
    ArrowRight,
    Loader2,
    FileText,
    TrendingUp,
    TrendingDown,
    Minus,
    Clock,
    CheckCircle,
    AlertCircle,
    Bot,
    Download,
} from '@lucide/vue';
import dayjs from 'dayjs';
import { ref, watch, computed } from 'vue';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { download } from '@/routes/file';
import { show as showSubmission } from '@/routes/student/classroom/task/submission';
import type { SubmissionDetail, TaskRubric } from '@/types';

interface Progress {
    previous_score: number;
    current_score: number;
    label: string;
}

const props = defineProps<{
    classroomId: number;
    taskId: number;
    submissionId: number | null;
    rubrics: TaskRubric[];
    status?: string;
}>();

const submissionData = ref<SubmissionDetail | null>(null);
const progressData = ref<Progress | null>(null);
const isLoading = ref(false);

const fetchHttp = useHttp<Record<string, never>>({});

const fetchSubmission = async () => {
    if (!props.submissionId) {
        return;
    }

    isLoading.value = true;
    submissionData.value = null;
    progressData.value = null;

    try {
        const response = await fetchHttp.get(
            showSubmission({
                classroom: props.classroomId,
                task: props.taskId,
                submission: props.submissionId as number,
            }).url,
        ) as unknown as { submission: SubmissionDetail; progress: Progress | null };

        submissionData.value = response.submission;
        progressData.value = response.progress;
    } catch {
        // handle error silently
    } finally {
        isLoading.value = false;
    }
};

watch(
    () => props.submissionId,
    () => fetchSubmission(),
    { immediate: true }
);

watch(
    () => props.status,
    (newStatus, oldStatus) => {
        // If status changed from processing to something else, re-fetch the full detail
        if (oldStatus === 'processing' && (newStatus === 'graded' || newStatus === 'failed')) {
            fetchSubmission();
        }
    }
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
    if (!progressData.value) {
        return null;
    }
    
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
    if (!progressData.value) {
        return '';
    }

    switch (progressData.value.label) {
        case 'Meningkat':
            return 'text-green-600';
        case 'Menurun':
            return 'text-red-600';
        default:
            return 'text-gray-500';
    }
});

const scoreColor = (score: number | null, maxScore: number | null) => {
    if (!score || !maxScore) {
        return 'text-gray-500';
    }

    const percentage = (score / maxScore) * 100;

    if (percentage >= 80) {
        return 'text-green-600';
    } else if (percentage >= 60) {
        return 'text-yellow-600';
    } else {
        return 'text-red-600';
    }
}
</script>

<template>
    <div class="h-full flex flex-col">
        <div class="mb-6">
            <h2 class="text-lg font-semibold tracking-tight flex items-center gap-2">
                Detail Pengumpulan
                <Badge v-if="submissionData" variant="outline" class="ml-2">
                    Versi {{ submissionData.version }}
                </Badge>
            </h2>
            <p class="text-sm text-muted-foreground">
                Lihat detail pengumpulan dan umpan balik.
            </p>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex-1 flex items-center justify-center">
            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <!-- Content -->
        <ScrollArea v-else-if="submissionData" class="flex-1 pr-4 -mr-4 ">
            <div class="space-y-6 pb-6">
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
                <template v-if="submissionData.status == 'graded'">
                    <div class="flex items-center gap-3">
                        <component :is="progressIcon" class="h-5 w-5" :class="progressColor" />
                        <p class="text-sm font-medium" :class="progressColor">
                            {{ progressData?.label }}, Skor sebelumnya: {{ progressData?.previous_score }} <ArrowRight class="h-4 w-5 inline" :class="progressColor" /> Skor saat ini: {{ progressData?.current_score }}
                        </p>
                    </div>
                </template>

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
                            :href="download({ file: file.id }).url"
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
                                    <Badge class="text-base font-bold px-3 py-1">
                                        Skor: {{ feedback.score }}
                                    </Badge>
                                </div>
                                <p class="text-sm whitespace-pre-wrap">{{ feedback.result }}</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Rubric Scores -->
                    <Accordion type="multiple" v-if="submissionData.rubric_scores" collapsible class="w-full">
                        <AccordionItem 
                            v-for="score in submissionData.rubric_scores"
                            :key="score.id"
                            :value="`item-${score.id}`">
                            <AccordionTrigger>
                                <span class="text-lg font-semibold">
                                    {{ score.rubric?.title ?? `Rubrik #${score.task_rubric_id}` }}
                                </span>
                                <span class="text-lg ml-auto text-secondary-foreground text-right tabular-nums">
                                    <span :class="scoreColor(score.score_ai, score.rubric!.max_score)">{{ score.score_ai }}</span> / <span class="text-primary">{{ score.rubric?.max_score }}</span>
                                </span>
                            </AccordionTrigger>
                            <AccordionContent>
                                <p>
                                {{ score.feedback_ai }}
                                </p>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </template>
            </div>
        </ScrollArea>
    </div>
</template>
