<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import ClassroomTaskList from '@/components/ClassroomTaskList.vue';
import { index } from '@/routes/teacher/classroom/task';

const props = defineProps<{
    classroom: any;
    tasks: {
        data: any[];
        links: any[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kelasku',
                href: '/teacher/classrooms',
            },
            {
                title: 'Tugas',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Tugas: ${classroom.name}`" />

    <div class="flex h-full flex-1 flex-col p-4 lg:p-8">
        <ClassroomTaskList
            :classroom="classroom"
            :tasks="tasks"
            role="teacher"
        />
    </div>
</template>
