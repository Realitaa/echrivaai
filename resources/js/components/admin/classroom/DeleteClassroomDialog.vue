<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const dialogOpen = defineModel<boolean>('open', { default: false });

const emit = defineEmits(['confirm']);
</script>

<template>
    <AlertDialog v-model:open="dialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{
                    $t('classroom.deleteDialog.title')
                }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ $t('classroom.deleteDialog.description') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="dialogOpen = false">
                    {{ $t('classroom.deleteDialog.cancel') }}
                </AlertDialogCancel>
                <AlertDialogAction
                    @click="emit('confirm')"
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                >
                    {{ $t('classroom.deleteDialog.confirm') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
