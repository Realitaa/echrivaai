<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { enrollments } from '@/routes/admin/classroom';
import type { Classroom } from '@/types';

const props = defineProps<{
    classroom: Classroom | null;
}>();

const dialogOpen = defineModel<boolean>('open', { default: false });

const enrollmentsData = ref<
    { user: { id: number; name: string; email: string } }[]
>([]);
const loading = ref(false);

const fetchEnrollments = async () => {
    if (!props.classroom) {
        return;
    }

    loading.value = true;

    try {
        const http = useHttp({ query: '' });
        await http.get(enrollments(props.classroom.id).url, {
            onSuccess: (response: any) => {
                enrollmentsData.value = response.data;
            },
        });
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

watch(dialogOpen, (val) => {
    if (val) {
        fetchEnrollments();
    } else {
        enrollmentsData.value = [];
    }
});
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>
                    {{
                        $t('classroom.enrollmentDialog.title', {
                            name: classroom?.name,
                        })
                    }}
                </DialogTitle>
                <DialogDescription>
                    {{ $t('classroom.enrollmentDialog.description') }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading" class="py-6 text-center text-muted-foreground">
                {{ $t('classroom.enrollmentDialog.loading') }}
            </div>

            <div v-else>
                <div
                    v-if="enrollmentsData.length === 0"
                    class="py-6 text-center text-muted-foreground"
                >
                    {{ $t('classroom.enrollmentDialog.empty') }}
                </div>

                <div v-else class="max-h-[60vh] space-y-4 overflow-y-auto pr-2">
                    <div
                        v-for="enrollment in enrollmentsData"
                        :key="enrollment.user.id"
                        class="flex items-center justify-between border-b pb-3 last:border-0 last:pb-0"
                    >
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">
                                {{ enrollment.user.name }}
                            </span>
                            <span class="text-xs text-muted-foreground">
                                {{ enrollment.user.email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
