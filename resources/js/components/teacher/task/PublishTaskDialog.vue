<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog';
import { publish, unpublish } from '@/routes/teacher/classroom/task';

const dialogOpen = defineModel<boolean>('open', { default: false });

const props = defineProps<{
    classroomId: number;
    taskId: number;
    isPublished: boolean;
}>();

const submitAction = () => {
    const route = props.isPublished
        ? unpublish({ classroom: props.classroomId, task: props.taskId })
        : publish({ classroom: props.classroomId, task: props.taskId });

    router.patch(route.url, {}, {
        onFinish: () => {
            dialogOpen.value = false;
        },
    });
};
</script>

<template>
    <AlertDialog v-model:open="dialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>
                    {{ isPublished ? 'Batalkan Publikasi Tugas?' : 'Publikasikan Tugas?' }}
                </AlertDialogTitle>
                <AlertDialogDescription>
                    <template v-if="isPublished">
                        Tugas ini akan ditarik dari siswa. Tugas yang sudah memiliki pengumpulan tidak dapat dibatalkan publikasinya.
                    </template>
                    <template v-else>
                        Tugas ini akan dipublikasikan dan dapat dilihat oleh siswa di kelas.
                    </template>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction @click="submitAction">
                    {{ isPublished ? 'Ya, Batalkan Publikasi' : 'Ya, Publikasikan' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
