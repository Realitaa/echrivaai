<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClassroomTaskList from '@/components/ClassroomTaskList.vue';
import { index, show } from '@/routes/teacher/classroom';

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
                title: 'classroom.teacher.title',
                href: index().url,
            },
            {
                title: props.classroom.name,
                href: show(props.classroom.id).url,
            },
            {
                title: 'task.teacher.listTitle',
                href: '#',
            },
        ],
    }),
});
</script>

<template>
    <Head :title="$t('task.teacher.title', { name: classroom.name })" />

    <div class="flex h-full flex-1 flex-col p-4 lg:p-8">
        <ClassroomTaskList
            :classroom="classroom"
            :tasks="tasks"
            role="teacher"
        />
    </div>
</template>
