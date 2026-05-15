<script setup lang="ts">
import { useForm, useHttp } from '@inertiajs/vue3';
import {
    Upload,
    X,
    FileText,
    Loader2,
    Send,
} from '@lucide/vue';
import { ref, computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Textarea } from '@/components/ui/textarea';
import { upload as fileUpload, remove as fileRemove } from '@/routes/file';
import { store as storeSubmission } from '@/routes/student/classroom/task/submission';
import type { FileItem, FileResponse } from '@/types';

const emit = defineEmits(['success', 'cancel']);

const props = defineProps<{
    classroomId: number;
    taskId: number;
}>();

// File management
const uploadedFiles = ref<FileItem[]>([]);
const uploadHttp = useHttp<{ file: File | null }, FileResponse>({
    file: null,
});
const removeHttp = useHttp<Record<string, never>>({});
const uploadError = ref('');

const handleFileUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (!input.files?.length) {
        return;
    }

    uploadError.value = '';

    for (const file of Array.from(input.files)) {
        uploadHttp.file = file;

        try {
            const response = await uploadHttp.post(fileUpload().url);

            if (response.success) {
                uploadedFiles.value.push({
                    id: response.file.id,
                    original_name: response.file.original_name,
                    filename: response.file.filename,
                    isTemp: true,
                });
            }
        } catch (error: unknown) {
            const err = error as { response?: { data?: { message?: string } } };
            uploadError.value = err.response?.data?.message ?? usePage().props.t('task.student.form.uploadError');
        }
    }

    input.value = '';
};

const removeFile = async (index: number) => {
    const file = uploadedFiles.value[index];

    if (file.isTemp) {
        try {
            await removeHttp.delete(fileRemove(file.id).url);
        } catch {
            // ignore error on remove
        }
    }

    uploadedFiles.value.splice(index, 1);
};

// Submission form
const form = useForm({
    content: '',
    temporary_file_ids: [] as number[],
});

const canSubmit = computed(() => {
    return form.content.trim().length > 0 && uploadedFiles.value.length > 0;
});

const submitForm = () => {
    form.temporary_file_ids = uploadedFiles.value.map((f) => f.id);

    form.post(
        storeSubmission({
            classroom: props.classroomId,
            task: props.taskId,
        }).url,
        {
            onSuccess: () => {
                emit('success');
                form.reset();
                uploadedFiles.value = [];
            },
        },
    );
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="h-full flex flex-col">
        <div class="mb-6">
            <h2 class="text-lg font-semibold tracking-tight">{{ $t('task.student.form.title') }}</h2>
            <p class="text-sm text-muted-foreground">
                {{ $t('task.student.form.description') }}
            </p>
        </div>

        <form @submit.prevent="submitForm" class="flex flex-col flex-1 gap-5">
            <!-- Content -->
            <div class="space-y-2 flex-1 flex flex-col">
                <Label for="submission-content" :class="{ 'text-destructive': form.errors.content }">
                    {{ $t('task.student.form.contentLabel') }}
                </Label>
                <Textarea
                    id="submission-content"
                    v-model="form.content"
                    :placeholder="$t('task.student.form.contentPlaceholder')"
                    class="flex-1 min-h-[200px] resize-none"
                    :class="{ 'border-destructive': form.errors.content }"
                />
                <span v-if="form.errors.content" class="text-xs text-destructive">
                    {{ form.errors.content }}
                </span>
            </div>

            <!-- File Upload -->
            <div class="space-y-2">
                <Label :class="{ 'text-destructive': form.errors.temporary_file_ids }">
                    {{ $t('task.student.form.attachmentsLabel') }}
                </Label>
                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="($refs.fileInput as HTMLInputElement).click()"
                        :disabled="uploadHttp.processing"
                    >
                        <template v-if="uploadHttp.processing">
                            <Loader2 class="h-4 w-4 animate-spin" /> {{ $t('task.student.form.uploading') }}
                        </template>
                        <template v-else>
                            <Upload class="h-4 w-4" /> {{ $t('task.student.form.upload') }}
                        </template>
                    </Button>
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif"
                        @change="handleFileUpload"
                    />
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ $t('task.student.form.uploadHint') }}
                </p>
                <span v-if="uploadError" class="text-xs text-destructive">{{ uploadError }}</span>
                <span v-if="form.errors.temporary_file_ids" class="text-xs text-destructive">
                    {{ form.errors.temporary_file_ids }}
                </span>

                <!-- Upload Progress -->
                <div v-if="uploadHttp.progress" class="space-y-1">
                    <Progress :model-value="uploadHttp.progress.percentage" class="h-2" />
                    <p class="text-xs text-muted-foreground text-right">
                        {{ uploadHttp.progress.percentage }}%
                    </p>
                </div>

                <!-- File List -->
                <div v-if="uploadedFiles.length > 0" class="space-y-2 mt-2">
                    <div
                        v-for="(file, index) in uploadedFiles"
                        :key="file.id"
                        class="flex items-center justify-between rounded-md border p-3"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <FileText class="h-4 w-4 shrink-0 text-blue-500" />
                            <span class="truncate text-sm font-medium">{{ file.original_name }}</span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7 shrink-0 text-destructive hover:text-destructive"
                            @click="removeFile(index)"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
                <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
                    {{ $t('task.student.form.cancel') }}
                </Button>
                <Button type="submit" :disabled="!canSubmit || form.processing">
                    <template v-if="form.processing">
                        <Loader2 class="h-4 w-4 animate-spin" /> {{ $t('task.student.form.submitting') }}
                    </template>
                    <template v-else>
                        <Send class="h-4 w-4" /> {{ $t('task.student.form.submit') }}
                    </template>
                </Button>
            </div>
        </form>
    </div>
</template>
