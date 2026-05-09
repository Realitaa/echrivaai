<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { index } from '@/routes/teacher/classroom';

defineProps<{
    classroom: any;
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            { title: 'Kelasku', href: index().url },
            { title: props.classroom.name, href: '#' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Detail Kelas ${classroom.name}`" />

    <ClassroomDetail :classroom="classroom" :back-url="index().url" />
</template>
