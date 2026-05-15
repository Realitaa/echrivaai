<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    EllipsisVertical,
    Plus,
    Edit,
    Trash2,
    Globe,
    GlobeLock,
    Info,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import DeleteTaskDialog from '@/components/teacher/task/DeleteTaskDialog.vue';
import PublishTaskDialog from '@/components/teacher/task/PublishTaskDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { useRelativeTime } from '@/composables/useDateFormat';
import { show as showStudentClassroom } from '@/routes/student/classroom';
import { show as showStudentTask } from '@/routes/student/classroom/task';
import { show as showTeacherClassroom } from '@/routes/teacher/classroom';
import {
    create as createTask,
    edit as editTask,
    destroy as destroyTask,
} from '@/routes/teacher/classroom/task';
import teacherSubmission from '@/routes/teacher/classroom/task/submission';
import type { Task, Classroom } from '@/types';

const props = defineProps<{
    classroom: Classroom;
    tasks: {
        data: Task[];
        links: any[];
    };
    role?: 'teacher' | 'student';
}>();

const page = usePage();
const userRole = computed(
    () => props.role ?? (page.props.auth as any)?.user?.role ?? 'student',
);

// Publish dialog
const isPublishDialogOpen = ref(false);
const publishTarget = ref<Task | null>(null);

const openPublishDialog = (task: Task) => {
    publishTarget.value = task;
    isPublishDialogOpen.value = true;
};

// Delete dialog
const isDeleteDialogOpen = ref(false);
const deletingTaskId = ref<number | null>(null);

const openDeleteDialog = (taskId: number) => {
    deletingTaskId.value = taskId;
    isDeleteDialogOpen.value = true;
};

const submitDelete = () => {
    if (deletingTaskId.value) {
        router.delete(
            destroyTask({
                classroom: props.classroom.id,
                task: deletingTaskId.value,
            }).url,
            {
                onFinish: () => {
                    isDeleteDialogOpen.value = false;
                },
            },
        );
    }
};

const showClassroom = computed(() => {
    return userRole.value === 'teacher'
        ? showTeacherClassroom
        : showStudentClassroom;
});

const getTaskUrl = (taskId: number) => {
    if (userRole.value === 'teacher') {
        return teacherSubmission.index({
            classroom: props.classroom.id,
            task: taskId,
        }).url;
    }

    return showStudentTask({ classroom: props.classroom.id, task: taskId }).url;
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col-reverse gap-6 lg:flex-row">
        <!-- Task List (left, 3/4) -->
        <div class="flex-1 lg:basis-3/4">
            <div class="mb-4 flex items-center justify-between">
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold">
                        {{ $t('task.teacher.listTitle') }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{
                            $t('task.teacher.listDesc', {
                                name: classroom.name,
                            })
                        }}
                    </p>
                </div>
                <Button v-if="userRole === 'teacher'" size="sm" as-child>
                    <Link :href="createTask(classroom.id).url">
                        <Plus class="h-4 w-4" /> {{ $t('task.teacher.new') }}
                    </Link>
                </Button>
            </div>

            <ScrollArea class="h-[calc(100vh-16rem)]">
                <div class="space-y-3">
                    <template v-if="tasks.data.length > 0">
                        <div
                            v-for="task in tasks.data"
                            :key="task.id"
                            class="group relative flex items-center gap-4 rounded-lg border bg-card p-4 shadow-sm transition-all hover:border-primary/30 hover:shadow-md"
                        >
                            <!-- Icon -->
                            <Link
                                :href="getTaskUrl(task.id)"
                                class="flex shrink-0 items-center justify-center rounded-full bg-primary/10 p-3 text-primary transition-colors group-hover:bg-primary/20"
                            >
                                <ClipboardList class="h-5 w-5" />
                            </Link>

                            <!-- Content -->
                            <Link
                                :href="getTaskUrl(task.id)"
                                class="min-w-0 flex-1"
                            >
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-bold">
                                        {{ task.title }}
                                    </p>
                                    <Badge
                                        v-if="task.is_published"
                                        variant="default"
                                        class="shrink-0 px-1.5 py-0 text-[10px]"
                                    >
                                        {{
                                            $t('task.teacher.status.published')
                                        }}
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="secondary"
                                        class="shrink-0 px-1.5 py-0 text-[10px]"
                                    >
                                        {{ $t('task.teacher.status.draft') }}
                                    </Badge>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ $t('task.teacher.status.created') }}
                                    {{ useRelativeTime(task.created_at) }}
                                </p>
                            </Link>

                            <!-- Actions -->
                            <DropdownMenu v-if="userRole === 'teacher'">
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 shrink-0"
                                    >
                                        <EllipsisVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        @click="openPublishDialog(task)"
                                    >
                                        <component
                                            :is="
                                                task.is_published
                                                    ? GlobeLock
                                                    : Globe
                                            "
                                            class="mr-2 h-4 w-4"
                                        />
                                        {{
                                            task.is_published
                                                ? $t(
                                                      'task.teacher.actions.unpublish',
                                                  )
                                                : $t(
                                                      'task.teacher.actions.publish',
                                                  )
                                        }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        as-child
                                        v-if="!task.is_published"
                                    >
                                        <Link
                                            :href="
                                                editTask({
                                                    classroom: classroom.id,
                                                    task: task.id,
                                                }).url
                                            "
                                            class="cursor-pointer"
                                        >
                                            <Edit class="mr-2 h-4 w-4" />
                                            {{
                                                $t('task.teacher.actions.edit')
                                            }}
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        v-if="!task.is_published"
                                        class="text-destructive"
                                        @click="openDeleteDialog(task.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        {{ $t('task.teacher.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </template>

                    <div
                        v-else
                        class="flex h-48 items-center justify-center rounded-lg border-2 border-dashed"
                    >
                        <div class="text-center">
                            <ClipboardList
                                class="mx-auto mb-2 h-10 w-10 text-muted-foreground/50"
                            />
                            <p class="text-sm text-muted-foreground">
                                {{ $t('task.teacher.empty') }}
                            </p>
                            <Button
                                v-if="userRole === 'teacher'"
                                variant="link"
                                size="sm"
                                as-child
                                class="mt-1"
                            >
                                <Link :href="createTask(classroom.id).url">
                                    {{ $t('task.teacher.createFirst') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    class="mt-4 flex items-center justify-end space-x-2 pr-4"
                    v-if="tasks.links && tasks.links.length > 3"
                >
                    <template v-for="(link, idx) in tasks.links" :key="idx">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="min-w-9"
                            :as="Link"
                            :href="link.url"
                        >
                            <span v-html="link.label"></span>
                        </Button>
                        <span
                            v-else
                            class="px-2 text-muted-foreground"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </ScrollArea>
        </div>

        <!-- Classroom Information (right, 1/4) -->
        <div class="lg:basis-1/4">
            <Card class="sticky top-4 overflow-hidden">
                <!-- Classroom Image -->
                <div class="relative -mt-6 h-32 w-full overflow-hidden">
                    <img
                        src="/assets/images/bg_library.jpg"
                        alt="Classroom cover"
                        class="h-full w-full object-cover"
                    />
                    <div
                        class="absolute inset-0 bg-linear-to-t from-card via-transparent to-transparent opacity-90"
                    ></div>
                </div>

                <!-- Avatar -->
                <div class="relative z-10 -mt-14 flex justify-center">
                    <Avatar class="h-16 w-16 border-4 border-card shadow-sm">
                        <AvatarFallback
                            class="bg-slate-500 text-xl font-bold text-white"
                        >
                            {{ classroom.name.charAt(0).toUpperCase() }}
                        </AvatarFallback>
                    </Avatar>
                </div>

                <CardContent class="space-y-3 text-center">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight">
                            {{ classroom.name }}
                        </h3>
                        <Badge
                            variant="outline"
                            class="mt-1 font-mono text-xs"
                            >{{ classroom.code }}</Badge
                        >
                    </div>

                    <p
                        v-if="classroom.description"
                        class="line-clamp-3 text-sm text-muted-foreground"
                    >
                        {{ classroom.description }}
                    </p>

                    <Separator />

                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div>
                            <p class="text-2xl font-bold">
                                {{ classroom.tasks_count ?? 0 }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ $t('task.table.classroom') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ classroom.enrollments_count ?? 0 }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ $t('classroom.teacher.stats.students') }}
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <div class="flex flex-col gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            as-child
                            class="w-full"
                        >
                            <Link :href="showClassroom(classroom.id).url">
                                <Info class="h-4 w-4" />
                                {{ $t('task.teacher.actions.aboutClassroom') }}
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <!-- Dialogs -->
    <PublishTaskDialog
        v-if="publishTarget"
        v-model:open="isPublishDialogOpen"
        :classroom-id="classroom.id"
        :task-id="publishTarget.id"
        :is-published="publishTarget.is_published"
    />

    <DeleteTaskDialog
        v-model:open="isDeleteDialogOpen"
        @delete="submitDelete"
    />
</template>
