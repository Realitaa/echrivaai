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
                title: 'Kelasku',
                href: index().url,
            },
            {
                title: props.classroom.name,
                href: show(props.classroom.id).url,
            },
            {
                title: 'Daftar Tugas',
                href: "#"
            }
        ],
    }),
});
</script>

<template>
    <Head :title="`Tugas di Kelas ${classroom.name}`" />

    <div class="flex h-full flex-1 flex-col p-4 lg:p-8">
        <ClassroomTaskList
            :classroom="classroom"
            :tasks="tasks"
            role="student"
        />
    </div>
</template>
