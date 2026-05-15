<script setup lang="ts">
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

const dialogOpen = defineModel<boolean>('open', { default: false });

const emit = defineEmits(['delete']);
</script>

<template>
    <AlertDialog v-model:open="dialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{
                    $t('task.teacher.deleteDialog.title')
                }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ $t('task.teacher.deleteDialog.description') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{
                    $t('task.teacher.deleteDialog.cancel')
                }}</AlertDialogCancel>
                <AlertDialogAction
                    @click="emit('delete')"
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                >
                    {{ $t('task.teacher.deleteDialog.confirm') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
