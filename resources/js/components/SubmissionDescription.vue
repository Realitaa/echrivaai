<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    FileText,
    User,
    Download,
} from '@lucide/vue';
import dayjs from 'dayjs';
import { computed } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useDateFormat, useFromNow } from '@/composables/useDateFormat';
import { download } from '@/routes/file';
import { index as taskIndex } from '@/routes/student/classroom/task';
import type { TaskDetail } from '@/types';

const props = defineProps<{
    task: TaskDetail;
}>();

// Deadline logic
const isDeadlinePassed = computed(() => {
    return dayjs().isAfter(dayjs(props.task.deadline));
});

const deadlineFormatted = useDateFormat(
    () => props.task.deadline,
    'DD MMMM YYYY, HH:mm',
);
const deadlineRelative = useFromNow(() => props.task.deadline);
const createdAtRelative = useFromNow(() => props.task.created_at);

const totalMaxScore = computed(() => {
    return props.task.rubrics?.reduce((sum, r) => sum + r.max_score, 0) ?? 0;
});
</script>

<template>
    <!-- Header with back button -->
    <div class="flex items-center gap-4">
        <Button variant="outline" size="icon" as-child>
            <Link :href="taskIndex(task.classroom_id).url">
                <ArrowLeft class="h-4 w-4" />
            </Link>
        </Button>
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-2xl font-bold tracking-tight">
                {{ task.title }}
            </h1>
            <div
                class="mt-1 flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
            >
                <span class="flex items-center gap-1">
                    <User class="h-3.5 w-3.5" />
                    {{
                        task.creator?.name ??
                        $t('submission.teacher.description.creator')
                    }}
                </span>
                <span class="flex items-center gap-1">
                    <Calendar class="h-3.5 w-3.5" />
                    {{ $t('submission.teacher.description.created') }}
                    {{ createdAtRelative }}
                </span>
            </div>
        </div>
    </div>

    <!-- Deadline Badge -->
    <div class="flex items-center gap-2">
        <Clock
            class="h-4 w-4"
            :class="isDeadlinePassed ? 'text-destructive' : 'text-primary'"
        />
        <span
            class="text-sm font-medium"
            :class="isDeadlinePassed ? 'text-destructive' : 'text-primary'"
        >
            {{ $t('submission.teacher.description.deadline') }}:
            {{ deadlineFormatted }}
        </span>
        <Badge
            :variant="isDeadlinePassed ? 'destructive' : 'secondary'"
            class="text-xs"
        >
            {{
                isDeadlinePassed
                    ? $t('submission.teacher.description.overdue')
                    : deadlineRelative
            }}
        </Badge>
    </div>

    <div class="flex w-full flex-col justify-between gap-4 lg:flex-row">
        <div class="w-full lg:w-2/3">
            <!-- Task Description -->
            <div v-if="task.description" class="space-y-2">
                <p class="text-sm leading-relaxed whitespace-pre-wrap">
                    {{ task.description }}
                </p>
            </div>

            <!-- Task Attachments -->
            <div v-if="task.files?.length" class="space-y-2">
                <h3 class="text-sm font-semibold">
                    {{ $t('submission.teacher.description.attachments') }}
                </h3>
                <div class="flex flex-wrap gap-2">
                    <a
                        v-for="file in task.files"
                        :key="file.id"
                        :href="download({ file: file.id }).url"
                        target="_blank"
                        class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors hover:bg-muted/50"
                    >
                        <FileText class="h-4 w-4 shrink-0 text-blue-500" />
                        <span class="max-w-[200px] truncate">{{
                            file.original_name
                        }}</span>
                        <Download
                            class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                        />
                    </a>
                </div>
            </div>
        </div>

        <!-- Rubrics Overview -->
        <div v-if="task.rubrics?.length" class="w-full space-y-2 lg:w-1/3">
            <h3 class="text-sm font-semibold">
                {{ $t('submission.teacher.description.assessmentCriteria') }}
            </h3>
            <Accordion
                type="multiple"
                v-if="task.rubrics?.length"
                collapsible
                class="w-full"
            >
                <AccordionItem
                    v-for="rubric in task.rubrics"
                    :key="rubric.id"
                    :value="`item-${rubric.id}`"
                >
                    <AccordionTrigger>
                        <span class="text-lg font-semibold">
                            {{ rubric.title }}
                        </span>
                        <span
                            class="ml-auto text-right text-lg text-primary tabular-nums"
                        >
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
            <p class="text-right text-sm text-muted-foreground">
                {{ $t('submission.teacher.description.maxScoreTotal') }}:
                <span class="font-semibold text-primary tabular-nums">{{
                    totalMaxScore
                }}</span>
            </p>
        </div>
    </div>
</template>
