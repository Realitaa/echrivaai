<script setup lang="ts">
import { ArrowLeft, Loader2, CheckCircle, AlertCircle, Send } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { useRelativeTime } from '@/composables/useDateFormat';
import type { SubmissionItem } from '@/types';

defineProps<{
    submissions: SubmissionItem[];
    currentView: 'none' | 'submit' | 'detail';
    selectedSubmissionId: number | null;
}>();

const emit = defineEmits<{
    (e: 'select', submissionId: number): void;
}>();

const openDetailDialog = (submissionId: number) => {
    emit('select', submissionId);
};

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
                                        {{ useRelativeTime(submission.submitted_at) }}
                                    </p>
                                    <p class="text-sm text-muted-foreground mt-0.5">Skor: <span class="font-medium text-primary">{{ submission.final_score ?? '-' }}</span></p>
                                </div>

                                <!-- Arrow -->
                                <ArrowLeft class="h-4 w-4 shrink-0 rotate-180 text-muted-foreground" v-if="currentView !== 'detail' || selectedSubmissionId !== submission.id" />
                            </CardContent>
                        </Card>
</template>