<script setup lang="ts">
import { Head, Link, useForm, useHttp } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Plus,
    Trash2,
    GripVertical,
    Upload,
    X,
    FileText,
    Loader2,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import DateTimePicker from '@/components/DateTimePicker.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Textarea } from '@/components/ui/textarea';
import { upload as fileUpload, remove as fileRemove } from '@/routes/file';
import { index, show } from '@/routes/teacher/classroom';
import {
    index as taskIndex,
    store,
    update,
} from '@/routes/teacher/classroom/task';
import type {
    Classroom,
    Task,
    TaskRubric,
    FileItem,
    FileResponse,
} from '@/types';

const props = defineProps<{
    classroom: Classroom;
    task?: Task;
}>();

const isEdit = computed(() => !!props.task);

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'classroom.teacher.title',
                href: index().url,
            },
            {
                title: props.classroom.name,
                href: show(props.classroom.id).url,
            },
            {
                title: props.task
                    ? 'task.teacher.form.editTitle'
                    : 'task.teacher.form.createTitle',
                href: '#',
            },
        ],
    }),
});

// File management
const uploadedFiles = ref<FileItem[]>(
    props.task?.files?.map((f: any) => ({
        id: f.id,
        original_name: f.original_name,
        filename: f.filename,
        isTemp: false,
    })) ?? [],
);

const removeHttp = useHttp<Record<string, never>>({});
const uploadError = ref('');

const uploadHttp = useHttp<{ file: File | null }, FileResponse>({
    file: null,
});

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
            uploadError.value =
                err.response?.data?.message ?? 'task.teacher.form.uploadError';
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

// TaskRubric management
const rubrics = ref<TaskRubric[]>(
    props.task?.rubrics?.map((r: any) => ({
        title: r.title,
        description: r.description,
        max_score: r.max_score,
        order: r.order,
    })) ?? [{ title: '', description: '', max_score: 25, order: 1 }],
);

const addRubric = () => {
    const nextOrder = rubrics.value.length + 1;
    rubrics.value.push({
        title: '',
        description: '',
        max_score: 25,
        order: nextOrder,
    });
};

const removeRubric = (index: number) => {
    rubrics.value.splice(index, 1);
    reorderRubrics();
};

const reorderRubrics = () => {
    rubrics.value.forEach((r, i) => {
        r.order = i + 1;
    });
};

// Drag and drop
const dragIndex = ref<number | null>(null);

const onDragStart = (index: number) => {
    dragIndex.value = index;
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
};

const onDrop = (targetIndex: number) => {
    if (dragIndex.value === null || dragIndex.value === targetIndex) {
        return;
    }

    const [moved] = rubrics.value.splice(dragIndex.value, 1);
    rubrics.value.splice(targetIndex, 0, moved);
    reorderRubrics();
    dragIndex.value = null;
};

const onDragEnd = () => {
    dragIndex.value = null;
};

// Form submission
const form = useForm({
    title: props.task?.title ?? '',
    description: props.task?.description ?? '',
    deadline: props.task?.deadline ?? '',
    is_published: !!(props.task?.is_published ?? false),
    rubrics: [] as TaskRubric[],
    attachments: [] as number[],
});

const submitForm = () => {
    form.rubrics = rubrics.value;
    form.attachments = uploadedFiles.value.map((f) => f.id);

    if (isEdit.value) {
        form.put(
            update({
                classroom: props.task!.classroom_id,
                task: props.task!.id,
            }).url,
        );
    } else {
        form.post(store(props.classroom.id).url);
    }
};

const totalScore = computed(() =>
    rubrics.value.reduce((sum, r) => sum + (Number(r.max_score) || 0), 0),
);
</script>

<template>
    <Head
        :title="
            isEdit
                ? $t('task.teacher.form.editTitle')
                : $t('task.teacher.form.createTitle')
        "
    />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link
                    :href="
                        taskIndex(isEdit ? task!.classroom_id : classroom.id)
                            .url
                    "
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{
                        isEdit
                            ? $t('task.teacher.form.editTitle')
                            : $t('task.teacher.form.createTitle')
                    }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{
                        isEdit
                            ? $t('task.teacher.form.editDesc')
                            : $t('task.teacher.form.createDesc')
                    }}
                </p>
            </div>
        </div>

        <form @submit.prevent="submitForm" class="mt-4">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left: Task Details -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{
                                $t('task.teacher.form.details')
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label
                                    for="title"
                                    :class="{
                                        'text-destructive': form.errors.title,
                                    }"
                                >
                                    {{ $t('task.teacher.form.title') }}
                                </Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    :placeholder="
                                        $t('task.teacher.form.titlePlaceholder')
                                    "
                                    :class="{
                                        'border-destructive': form.errors.title,
                                    }"
                                />
                                <span
                                    v-if="form.errors.title"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.title }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">{{
                                    $t('task.teacher.form.description')
                                }}</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    :placeholder="
                                        $t(
                                            'task.teacher.form.descriptionPlaceholder',
                                        )
                                    "
                                    rows="4"
                                />
                                <span
                                    v-if="form.errors.description"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.description }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    for="deadline"
                                    :class="{
                                        'text-destructive':
                                            form.errors.deadline,
                                    }"
                                >
                                    {{ $t('task.teacher.form.deadline') }}
                                </Label>
                                <DateTimePicker
                                    id="deadline"
                                    v-model="form.deadline"
                                    :error="!!form.errors.deadline"
                                    disablePast
                                />
                                <span
                                    v-if="form.errors.deadline"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.deadline }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    id="is_published"
                                    v-model="form.is_published"
                                />
                                <Label
                                    for="is_published"
                                    class="cursor-pointer"
                                >
                                    {{ $t('task.teacher.form.publishNow') }}
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attachments -->
                    <Card>
                        <CardHeader>
                            <CardTitle>{{
                                $t('task.teacher.form.attachments')
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            (
                                                $refs.fileInput as HTMLInputElement
                                            ).click()
                                        "
                                        :disabled="uploadHttp.processing"
                                    >
                                        <template v-if="uploadHttp.processing">
                                            <Loader2
                                                class="h-4 w-4 animate-spin"
                                            />
                                            {{
                                                $t(
                                                    'task.teacher.form.uploading',
                                                )
                                            }}
                                        </template>
                                        <template v-else>
                                            <Upload class="h-4 w-4" />
                                            {{ $t('task.teacher.form.upload') }}
                                        </template>
                                    </Button>
                                    <Progress
                                        v-if="uploadHttp.progress"
                                        :model-value="
                                            uploadHttp.progress.percentage
                                        "
                                        class="w-full"
                                    />
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
                                    {{ $t('task.teacher.form.uploadHint') }}
                                </p>
                                <span
                                    v-if="uploadError"
                                    class="text-xs text-destructive"
                                    >{{ $t(uploadError) }}</span
                                >
                                <span
                                    v-if="form.errors.attachments"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.attachments }}
                                </span>
                            </div>

                            <div
                                v-if="uploadedFiles.length > 0"
                                class="space-y-2"
                            >
                                <div
                                    v-for="(file, index) in uploadedFiles"
                                    :key="file.id"
                                    class="flex items-center justify-between rounded-md border p-3"
                                >
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <FileText
                                            class="h-4 w-4 shrink-0 text-blue-500"
                                        />
                                        <span
                                            class="truncate text-sm font-medium"
                                            >{{ file.original_name }}</span
                                        >
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
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Rubrics -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>{{
                                    $t('task.teacher.form.rubrics')
                                }}</CardTitle>
                                <Badge class="text-sm font-semibold">
                                    {{ $t('task.teacher.form.totalScore') }}:
                                    {{ totalScore }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-for="(rubric, index) in rubrics"
                                :key="index"
                                class="relative rounded-lg border bg-card p-4 transition-all"
                                :class="{
                                    'border-dashed opacity-50':
                                        dragIndex === index,
                                }"
                                draggable="true"
                                @dragstart="onDragStart(index)"
                                @dragover="onDragOver"
                                @drop="onDrop(index)"
                                @dragend="onDragEnd"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Drag Handle -->
                                    <div
                                        class="mt-1 cursor-grab text-muted-foreground active:cursor-grabbing"
                                    >
                                        <GripVertical class="h-5 w-5" />
                                    </div>

                                    <div class="flex-1 space-y-3">
                                        <div
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Badge
                                                    variant="outline"
                                                    class="text-xs"
                                                >
                                                    #{{ rubric.order }}
                                                </Badge>
                                                <Input
                                                    v-model="rubric.title"
                                                    :placeholder="
                                                        $t(
                                                            'task.teacher.form.rubricNamePlaceholder',
                                                        )
                                                    "
                                                    class="h-8 text-sm font-semibold"
                                                />
                                            </div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 shrink-0 text-destructive hover:text-destructive"
                                                @click="removeRubric(index)"
                                                :disabled="rubrics.length <= 1"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>

                                        <Textarea
                                            v-model="rubric.description"
                                            :placeholder="
                                                $t(
                                                    'task.teacher.form.rubricDescPlaceholder',
                                                )
                                            "
                                            rows="2"
                                            class="text-sm"
                                        />

                                        <div class="flex items-center gap-2">
                                            <Label
                                                class="shrink-0 text-xs text-muted-foreground"
                                            >
                                                {{
                                                    $t(
                                                        'task.teacher.form.maxScore',
                                                    )
                                                }}
                                            </Label>
                                            <Input
                                                v-model.number="
                                                    rubric.max_score
                                                "
                                                type="number"
                                                min="1"
                                                class="h-8 w-24 text-sm"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TaskRubric validation errors -->
                            <template v-for="(value, key) in form.errors">
                                <span
                                    v-if="String(key).startsWith('rubrics')"
                                    :key="key"
                                    class="block text-xs text-destructive"
                                >
                                    {{ value }}
                                </span>
                            </template>

                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                @click="addRubric"
                            >
                                <Plus class="h-4 w-4" />
                                {{ $t('task.teacher.form.addRubric') }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button
                    type="button"
                    variant="outline"
                    as-child
                    :disabled="form.processing"
                >
                    <Link
                        :href="
                            taskIndex(
                                isEdit ? task!.classroom_id : classroom.id,
                            ).url
                        "
                    >
                        {{ $t('task.teacher.form.cancel') }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    {{
                        form.processing
                            ? $t('task.teacher.form.saving')
                            : isEdit
                              ? $t('task.teacher.form.saveChanges')
                              : $t('task.teacher.form.save')
                    }}
                </Button>
            </div>
        </form>
    </div>
</template>
