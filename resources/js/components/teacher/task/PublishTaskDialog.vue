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

    router.patch(
        route.url,
        {},
        {
            onFinish: () => {
                dialogOpen.value = false;
            },
        },
    );
};
</script>

<template>
    <AlertDialog v-model:open="dialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>
                    {{
                        isPublished
                            ? $t('task.teacher.publishDialog.unpublishTitle')
                            : $t('task.teacher.publishDialog.publishTitle')
                    }}
                </AlertDialogTitle>
                <AlertDialogDescription>
                    <template v-if="isPublished">
                        {{ $t('task.teacher.publishDialog.unpublishDesc') }}
                    </template>
                    <template v-else>
                        {{ $t('task.teacher.publishDialog.publishDesc') }}
                    </template>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{
                    $t('task.teacher.publishDialog.cancel')
                }}</AlertDialogCancel>
                <AlertDialogAction @click="submitAction">
                    {{
                        isPublished
                            ? $t('task.teacher.publishDialog.unpublishConfirm')
                            : $t('task.teacher.publishDialog.publishConfirm')
                    }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
