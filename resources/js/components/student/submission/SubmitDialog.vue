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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Textarea } from '@/components/ui/textarea';
import { upload as fileUpload, remove as fileRemove } from '@/routes/file';
import { store as storeSubmission } from '@/routes/student/classroom/task/submission';
import type { FileItem, FileResponse } from '@/types';

const dialogOpen = defineModel<boolean>('open', { default: false });

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
            uploadError.value = err.response?.data?.message ?? 'Gagal mengunggah file.';
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
                dialogOpen.value = false;
                form.reset();
                uploadedFiles.value = [];
            },
        },
    );
};

const resetAndClose = () => {
    dialogOpen.value = false;
};
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>Kirim Pengumpulan</DialogTitle>
                <DialogDescription>
                    Tulis esai dan lampirkan dokumen pendukung.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Content -->
                <div class="space-y-2">
                    <Label for="submission-content" :class="{ 'text-destructive': form.errors.content }">
                        Konten Esai
                    </Label>
                    <Textarea
                        id="submission-content"
                        v-model="form.content"
                        placeholder="Tulis esai atau jawaban tugas Anda di sini..."
                        rows="6"
                        :class="{ 'border-destructive': form.errors.content }"
                    />
                    <span v-if="form.errors.content" class="text-xs text-destructive">
                        {{ form.errors.content }}
                    </span>
                </div>

                <!-- File Upload -->
                <div class="space-y-2">
                    <Label :class="{ 'text-destructive': form.errors.temporary_file_ids }">
                        Lampiran Dokumen
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
                                <Loader2 class="h-4 w-4 animate-spin" /> Mengunggah...
                            </template>
                            <template v-else>
                                <Upload class="h-4 w-4" /> Unggah File
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
                        Format: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, GIF (maks 20MB)
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

                <DialogFooter>
                    <Button type="button" variant="outline" @click="resetAndClose" :disabled="form.processing">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="!canSubmit || form.processing">
                        <template v-if="form.processing">
                            <Loader2 class="h-4 w-4 animate-spin" /> Mengirim...
                        </template>
                        <template v-else>
                            <Send class="h-4 w-4" /> Kirim Pengumpulan
                        </template>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
