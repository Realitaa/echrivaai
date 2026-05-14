<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClassroomDetail from '@/components/ClassroomDetail.vue';
import { index } from '@/routes/teacher/classroom';

defineProps<{
    classroom: any;
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            { title: 'classroom.teacher.title', href: index().url },
            { title: props.classroom.name, href: '#' },
        ],
    }),
});
</script>

<template>
    <Head :title="$t('classroom.teacher.detailTitle', { name: classroom.name })" />

    <ClassroomDetail :classroom="classroom" :back-url="index().url" />
</template>
