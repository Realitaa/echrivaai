<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import SubmissionDescription from '@/components/SubmissionDescription.vue';
import { Separator } from '@/components/ui/separator';
import { index as classroomIndex } from '@/routes/teacher/classroom';
import { show as classroomShow } from '@/routes/teacher/classroom';
import { index as taskIndex } from '@/routes/teacher/classroom/task';
import type { SubmissionItem, TaskDetail } from '@/types';

dayjs.extend(relativeTime);

defineProps<{
    task: TaskDetail;
    submissions: SubmissionItem[];
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'Kelasku',
                href: classroomIndex().url,
            },
            {
                title: props.task.classroom.name,
                href: classroomShow(props.task.classroom_id).url,
            },
            {
                title: 'Daftar Tugas',
                href: taskIndex(props.task.classroom_id).url,
            },
            {
                title: props.task.title,
                href: "#"
            }
        ],
    }),
});
</script>

<template>
    <Head :title="`Tugas: ${task.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-8">
        <SubmissionDescription :task="task" />

        <Separator />
    </div>
</template>
