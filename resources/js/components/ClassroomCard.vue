<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Edit, Eye, Trash2, Copy } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { index as studentTaskIndex } from '@/routes/student/classroom/task';
import { show } from '@/routes/teacher/classroom';
import { index as teacherTaskIndex } from '@/routes/teacher/classroom/task';
import type { Classroom } from '@/types';

defineProps<{
    classroom: Classroom;
}>();

defineEmits<{
    (e: 'edit', classroom: Classroom): void;
    (e: 'delete', id: number): void;
}>();

const page = usePage();
const userRole = page.props.auth.user.role;
const { copy } = useClipboard();

function copyClassroomCode(code: string) {
    copy(code);
    toast.success(
        usePage().props.t('classroom.student.card.copySuccess', { code }),
    );
}

const taskIndex = computed(() => {
    return userRole === 'teacher' ? teacherTaskIndex : studentTaskIndex;
});
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-xl border bg-card shadow-sm transition-all hover:shadow-md"
    >
        <!-- Header Image Area -->
        <div class="relative h-28 w-full overflow-hidden">
            <img
                src="/assets/images/bg_library.jpg"
                alt="Classroom cover"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <!-- Faded effect on lower part of the image -->
            <div
                class="absolute inset-0 bg-linear-to-t from-card via-transparent to-transparent opacity-90"
            ></div>
        </div>

        <!-- Avatar Overlapping -->
        <div class="absolute top-16 right-4 z-10">
            <Avatar
                class="h-20 w-20 border-4 border-card shadow-sm ring-1 ring-border/50"
            >
                <AvatarFallback
                    class="bg-slate-500 text-2xl font-bold text-white"
                >
                    {{ classroom.name.charAt(0).toUpperCase() }}
                </AvatarFallback>
            </Avatar>
        </div>

        <!-- Content Area -->
        <div class="flex flex-1 flex-col p-6 pt-2">
            <div class="mb-4">
                <Link :href="taskIndex(classroom.id)">
                    <h3
                        class="line-clamp-1 text-xl font-bold tracking-tight transition-colors group-hover:text-primary hover:underline"
                    >
                        {{ classroom.name }}
                    </h3>
                </Link>
                <p
                    @click="copyClassroomCode(classroom.code)"
                    :title="$t('classroom.student.card.copyCode')"
                    class="line-clamp-1 flex cursor-pointer items-center gap-2 font-mono text-xs font-medium tracking-wider text-muted-foreground"
                >
                    <span>
                        {{ $t('classroom.student.card.code') }}:
                        {{ classroom.code }}
                    </span>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6 cursor-pointer"
                    >
                        <Copy class="h-4 w-4" />
                    </Button>
                </p>
                <p class="text-sm" v-if="userRole === 'student'">
                    {{ $t('classroom.student.card.teacher') }}:
                    {{ classroom.teacher.name }}
                </p>
            </div>

            <p class="line-clamp-2 min-h-10 text-sm text-muted-foreground">
                {{
                    classroom.description ||
                    $t('classroom.student.card.noDescription')
                }}
            </p>

            <!-- Actions Area (Footer) -->
            <div
                v-if="userRole === 'teacher'"
                class="mt-6 flex items-center justify-between border-t pt-4"
            >
                <div class="flex gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="h-10 w-10 rounded-full transition-colors hover:bg-primary/10 hover:text-primary"
                        :title="$t('classroom.student.card.viewDetail')"
                    >
                        <Link :href="show(classroom.id).url">
                            <Eye class="h-5 w-5" />
                        </Link>
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('edit', classroom)"
                        class="h-10 w-10 rounded-full transition-colors hover:bg-primary/10 hover:text-primary"
                        :title="$t('classroom.student.card.editClass')"
                    >
                        <Edit class="h-5 w-5" />
                    </Button>
                </div>

                <Button
                    variant="ghost"
                    size="icon"
                    @click="$emit('delete', classroom.id)"
                    class="h-10 w-10 rounded-full text-destructive transition-colors hover:bg-destructive/10 hover:text-destructive"
                    :title="$t('classroom.student.card.delete')"
                >
                    <Trash2 class="h-5 w-5" />
                </Button>
            </div>
        </div>
    </div>
</template>
