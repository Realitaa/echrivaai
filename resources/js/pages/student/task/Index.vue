<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClassroomTaskList from '@/components/ClassroomTaskList.vue';
import { index, show } from '@/routes/student/classroom';

defineProps<{
    classroom: any;
    tasks: {
        data: any[];
        links: any[];
    };
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'task.student.breadcrumb',
                href: index().url,
            },
            {
                title: props.classroom.name,
                href: show(props.classroom.id).url,
            },
            {
                title: 'task.student.listTitle',
                href: '#',
            },
        ],
    }),
});
</script>

<template>
    <Head :title="$t('task.student.inClassTitle', { name: classroom.name })" />

    <div class="flex h-full flex-1 flex-col p-4 lg:p-8">
        <ClassroomTaskList
            :classroom="classroom"
            :tasks="tasks"
            role="student"
        />
    </div>
</template>
