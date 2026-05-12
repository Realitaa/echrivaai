<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    FileText,
    Plus,
    User,
    CheckCircle,
    Loader2,
    AlertCircle,
    Download,
    History,
    Send,
} from '@lucide/vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { ref, computed } from 'vue';
import { watch, onMounted, onUnmounted } from 'vue';
import SubmissionDetail from '@/components/student/submission/SubmissionDetail.vue';
import SubmissionForm from '@/components/student/submission/SubmissionForm.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion'
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { download } from '@/routes/file';
import { index as classroomIndex } from '@/routes/student/classroom';
import { index as taskIndex } from '@/routes/student/classroom/task';
import type { SubmissionItem, TaskDetail } from '@/types';

dayjs.extend(relativeTime);

const props = defineProps<{
    task: TaskDetail;
    submissions: SubmissionItem[];
}>();

defineOptions({
    layout: (pageProps: any) => ({
        breadcrumbs: [
            {
                title: 'Kelasku',
                href: classroomIndex().url,
            },
            {
                title: 'Tugas',
                href: taskIndex(pageProps.task.classroom_id).url,
            },
            {
                title: pageProps.task.title,
                href: '#',
            },
        ],
    }),
});

// Deadline logic
const isDeadlinePassed = computed(() => {
    return dayjs().isAfter(dayjs(props.task.deadline));
});

const deadlineFormatted = computed(() => {
    return dayjs(props.task.deadline).format('DD MMMM YYYY, HH:mm');
});

const deadlineRelative = computed(() => {
    return dayjs(props.task.deadline).fromNow();
});

const hasProcessingSubmission = computed(() => {
    return props.submissions.some((s) => s.status === 'processing');
});

const canSubmit = computed(() => {
    return !isDeadlinePassed.value && !hasProcessingSubmission.value;
});

const totalMaxScore = computed(() => {
    return props.task.rubrics?.reduce((sum, r) => sum + r.max_score, 0) ?? 0;
});

// Submit and Detail view state
const currentView = ref<'none' | 'submit' | 'detail'>('none');
const selectedSubmissionId = ref<number | null>(null);

// Polling logic
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const startPolling = () => {
    if (pollingInterval) {
        return;
    }

    pollingInterval = setInterval(() => {
        router.reload({
            only: ['submissions'],
            onSuccess: () => {
                if (!hasProcessingSubmission.value) {
                    stopPolling();
                }
            },
        });
    }, 5000); // Poll every 5 seconds
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

watch(hasProcessingSubmission, (newValue) => {
    if (newValue) {
        startPolling();
    } else {
        stopPolling();
    }
}, { immediate: true });

onUnmounted(() => {
    stopPolling();
});

onMounted(() => {
    if (props.submissions.length > 0) {
        const latest = props.submissions.find(s => s.is_latest) || props.submissions[0];
        selectedSubmissionId.value = latest.id;
        currentView.value = 'detail';
    } else {
        currentView.value = 'submit';
    }
});

const openDetailDialog = (submissionId: number) => {
    selectedSubmissionId.value = submissionId;
    currentView.value = 'detail';
};

const showSubmitForm = () => {
    if (canSubmit.value) {
        currentView.value = 'submit';
    }
};

const handleSubmitCancel = () => {
    if (props.submissions.length > 0) {
        const latest = props.submissions.find(s => s.is_latest) || props.submissions[0];
        selectedSubmissionId.value = latest.id;
        currentView.value = 'detail';
    } else {
        currentView.value = 'submit';
    }
};

watch(
    () => props.submissions,
    (newSubmissions) => {
        if (newSubmissions.length > 0 && currentView.value === 'submit') {
            const latest = newSubmissions.find(s => s.is_latest) || newSubmissions[0];
            selectedSubmissionId.value = latest.id;
            currentView.value = 'detail';
        }
    },
    { deep: true }
);

const statusConfig = (status: string) => {
    switch (status) {
        case 'processing':
            return { icon: Loader2, label: 'Sedang Diproses', variant: 'secondary' as const, color: 'text-yellow-600', iconClass: 'animate-spin' };
        case 'graded':
            return { icon: CheckCircle, label: 'Dinilai', variant: 'default' as const, color: 'text-green-600', iconClass: '' };
        case 'failed':
            return { icon: AlertCircle, label: 'Gagal', variant: 'destructive' as const, color: 'text-red-600', iconClass: '' };
        default:
            return { icon: Send, label: 'Terkirim', variant: 'secondary' as const, color: 'text-gray-600', iconClass: '' };
    }
};
</script>

<template>
    <Head :title="`Tugas: ${task.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-8">
        <!-- Header with back button -->
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="taskIndex(task.classroom_id).url">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold tracking-tight truncate">{{ task.title }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <User class="h-3.5 w-3.5" />
                        {{ task.creator?.name ?? 'Guru' }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Calendar class="h-3.5 w-3.5" />
                        Dibuat {{ dayjs(task.created_at).fromNow() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Deadline Badge -->
        <div class="flex items-center gap-2">
            <Clock class="h-4 w-4" :class="isDeadlinePassed ? 'text-destructive' : 'text-primary'" />
            <span class="text-sm font-medium" :class="isDeadlinePassed ? 'text-destructive' : 'text-primary'">
                Tenggat: {{ deadlineFormatted }}
            </span>
            <Badge :variant="isDeadlinePassed ? 'destructive' : 'secondary'" class="text-xs">
                {{ isDeadlinePassed ? 'Lewat tenggat' : deadlineRelative }}
            </Badge>
        </div>

        <div class="flex flex-col lg:flex-row justify-between gap-4 w-full">
            <div class="w-full lg:w-2/3">
                <!-- Task Description -->
                <div v-if="task.description" class="space-y-2">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ task.description }}</p>
                </div>

                <!-- Task Attachments -->
                <div v-if="task.files?.length" class="space-y-2">
                    <h3 class="text-sm font-semibold">Lampiran Tugas</h3>
                    <div class="flex flex-wrap gap-2">
                        <a
                            v-for="file in task.files"
                            :key="file.id"
                            :href="download({ file: file.id }).url"
                            target="_blank"
                            class="flex items-center gap-2 rounded-md border px-3 py-2 hover:bg-muted/50 transition-colors text-sm"
                        >
                            <FileText class="h-4 w-4 shrink-0 text-blue-500" />
                            <span class="truncate max-w-[200px]">{{ file.original_name }}</span>
                            <Download class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Rubrics Overview -->
            <div v-if="task.rubrics?.length" class="space-y-2 w-full lg:w-1/3">
                <h3 class="text-sm font-semibold">Kriteria Penilaian</h3>
                <Accordion type="multiple" v-if="task.rubrics?.length" collapsible class="w-full">
                    <AccordionItem 
                        v-for="rubric in task.rubrics"
                        :key="rubric.id"
                        :value="`item-${rubric.id}`">
                        <AccordionTrigger>
                            <span class="text-lg font-semibold">
                                {{ rubric.title }}
                            </span>
                            <span class="text-lg ml-auto text-primary text-right tabular-nums">
                                {{ rubric.max_score }}
                            </span>
                        </AccordionTrigger>
                        <AccordionContent>
                            <p>
                            {{ rubric.description }}
                            </p>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
                <p class="text-sm text-muted-foreground text-right">
                    Total skor maksimal: <span class="font-semibold text-primary tabular-nums">{{ totalMaxScore }}</span>
                </p>
            </div>
        </div>

        <Separator />

        <!-- Submission Section -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <History class="h-4 w-4" />
                Pengumpulan
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left side: History -->
                <div class="lg:col-span-1 space-y-3 flex flex-col">
                    <div class="flex-1 pr-1 overflow-y-auto space-y-3">
                        <!-- Submit Button -->
                        <Card
                            class="border-2 transition-all cursor-pointer group shrink-0"
                            :class="[
                                canSubmit
                                    ? 'border-dashed border-primary/30 hover:border-primary hover:shadow-md'
                                    : 'border-dashed border-muted-foreground/20 opacity-60 cursor-not-allowed',
                                currentView === 'submit' ? 'border-primary' : ''
                            ]"
                            @click="showSubmitForm"
                        >
                            <CardContent class="flex items-center gap-4 py-4">
                                <div
                                    class="rounded-full p-2.5 transition-colors"
                                    :class="canSubmit
                                        ? 'bg-primary/10 text-primary group-hover:bg-primary/20'
                                        : 'bg-muted text-muted-foreground'"
                                >
                                    <Plus class="h-5 w-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" :class="canSubmit ? '' : 'text-muted-foreground'">
                                        Tambah Pengumpulan
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        <template v-if="isDeadlinePassed">
                                            Tenggat lewat.
                                        </template>
                                        <template v-else-if="hasProcessingSubmission">
                                            Sedang diproses AI.
                                        </template>
                                        <template v-else>
                                            Kirim tugas baru.
                                        </template>
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- History Cards -->
                        <Card
                            v-for="submission in submissions"
                            :key="submission.id"
                            class="cursor-pointer transition-all hover:shadow-md hover:border-primary/30 shrink-0"
                            :class="[
                                currentView === 'detail' && selectedSubmissionId === submission.id ? 'border-primary ring-1 ring-primary' : ''
                            ]"
                            @click="openDetailDialog(submission.id)"
                        >
                            <CardContent class="flex items-center gap-4 py-4">
                                <!-- Status Icon -->
                                <div
                                    class="flex shrink-0 items-center justify-center rounded-full p-2.5"
                                    :class="{
                                        'bg-yellow-100 dark:bg-yellow-900/30': submission.status === 'processing',
                                        'bg-green-100 dark:bg-green-900/30': submission.status === 'graded',
                                        'bg-red-100 dark:bg-red-900/30': submission.status === 'failed',
                                        'bg-gray-100 dark:bg-gray-900/30': submission.status === 'submitted',
                                    }"
                                >
                                    <component
                                        :is="statusConfig(submission.status).icon"
                                        class="h-5 w-5"
                                        :class="[
                                            statusConfig(submission.status).color,
                                            statusConfig(submission.status).iconClass,
                                        ]"
                                    />
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold">
                                            Pengumpulan #{{ submission.version }}
                                        </p>
                                        <Badge
                                            v-if="submission.is_latest"
                                            variant="default"
                                            class="text-[10px] px-1.5 py-0"
                                        >
                                            Terbaru
                                        </Badge>
                                        <Badge
                                            :variant="statusConfig(submission.status).variant"
                                            class="text-[10px] px-1.5 py-0"
                                        >
                                            {{ statusConfig(submission.status).label }}
                                        </Badge>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        {{ dayjs(submission.submitted_at).format('DD MMM YYYY, HH:mm') }}
                                        · {{ dayjs(submission.submitted_at).fromNow() }}
                                    </p>
                                    <p class="text-sm text-muted-foreground mt-0.5">Skor: <span class="font-medium text-primary">{{ submission.final_score ?? '-' }}</span></p>
                                </div>

                                <!-- Arrow -->
                                <ArrowLeft class="h-4 w-4 shrink-0 rotate-180 text-muted-foreground" v-if="currentView !== 'detail' || selectedSubmissionId !== submission.id" />
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Right side: Detail/Form -->
                <div class="lg:col-span-2 rounded-md border bg-card text-card-foreground shadow-sm p-6 overflow-hidden flex flex-col">
                    <SubmissionForm
                        v-if="currentView === 'submit'"
                        :classroom-id="task.classroom_id"
                        :task-id="task.id"
                        @cancel="handleSubmitCancel"
                    />

                    <SubmissionDetail
                        v-else-if="currentView === 'detail'"
                        :classroom-id="task.classroom_id"
                        :task-id="task.id"
                        :submission-id="selectedSubmissionId"
                        :rubrics="task.rubrics"
                        :status="submissions.find(s => s.id === selectedSubmissionId)?.status"
                    />

                    <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                        Pilih pengumpulan untuk melihat detail
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
