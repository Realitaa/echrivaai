<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    History,
} from '@lucide/vue';
import dayjs from 'dayjs';
import { ref, computed } from 'vue';
import { watch, onMounted, onUnmounted } from 'vue';
import HistoryCard from '@/components/HistoryCard.vue';
import SubmissionForm from '@/components/student/submission/SubmissionForm.vue';
import SubmissionDescription from '@/components/SubmissionDescription.vue';
import SubmissionDetail from '@/components/SubmissionDetail.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as classroomIndex } from '@/routes/student/classroom';
import { show as classroomShow } from '@/routes/student/classroom';
import { index as taskIndex } from '@/routes/student/classroom/task';
import { show as showSubmission } from '@/routes/student/classroom/task/submission';
import type { SubmissionItem, TaskDetail } from '@/types';



const props = defineProps<{
    task: TaskDetail;
    submissions: SubmissionItem[];
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'task.student.breadcrumb',
                href: classroomIndex().url,
            },
            {
                title: props.task.classroom.name,
                href: classroomShow(props.task.classroom_id).url,
            },
            {
                title: 'task.student.listTitle',
                href: taskIndex(props.task.classroom_id).url,
            },
            {
                title: props.task.title,
                href: "#"
            }
        ],
    }),
});

// Deadline logic
const isDeadlinePassed = computed(() => {
    return dayjs().isAfter(dayjs(props.task.deadline));
});

const hasProcessingSubmission = computed(() => {
    return props.submissions.some((s) => s.status === 'processing');
});

const canSubmit = computed(() => {
    return !isDeadlinePassed.value && !hasProcessingSubmission.value;
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
</script>

<template>
    <Head :title="$t('task.student.pageTitle', { title: task.title })" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-8">
        <SubmissionDescription :task="task" />

        <Separator />

        <!-- Submission Section -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <History class="h-4 w-4" />
                {{ $t('task.student.history.title') }}
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
                                        {{ $t('task.student.history.addSubmission') }}
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        <template v-if="isDeadlinePassed">
                                            {{ $t('task.student.history.deadlineOverdue') }}
                                        </template>
                                        <template v-else-if="hasProcessingSubmission">
                                            {{ $t('task.student.history.processingAi') }}
                                        </template>
                                        <template v-else>
                                            {{ $t('task.student.history.submitNew') }}
                                        </template>
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- History Cards -->
                        <HistoryCard 
                            :submissions="submissions" 
                            :current-view="currentView" 
                            :selected-submission-id="selectedSubmissionId"
                            @select="openDetailDialog"
                        />
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
                        :show-route="showSubmission"
                    />

                    <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                        {{ $t('task.student.history.selectSubmission') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
