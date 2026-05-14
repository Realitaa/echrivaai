<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Trash2, Users } from '@lucide/vue';
import { ref, watch } from 'vue';
import DeleteClassroomDialog from '@/components/admin/classroom/DeleteClassroomDialog.vue';
import EnrollmentDialog from '@/components/admin/classroom/EnrollmentDialog.vue';
import LookupCombobox from '@/components/admin/LookupCombobox.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useRelativeTime } from '@/composables/useDateFormat';
import { index, destroy } from '@/routes/admin/classroom';
import type { Classroom, Teacher } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'classroom.title',
                href: index().url,
            },
        ],
    },
});

const props = defineProps<{
    classroom: {
        data: Classroom[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: {
        search?: string;
        teacher_id?: string | number;
    };
    teachers: Teacher[];
}>();

// Filters
const search = ref(props.filters.search ?? '');
const teacherId = ref(props.filters.teacher_id ?? '');

let timeout: ReturnType<typeof setTimeout> | null = null;

watch([search, teacherId], ([newSearch, newTeacherId]) => {
    if (timeout) {
        clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
        router.get(
            index().url,
            {
                search: newSearch,
                teacher_id: newTeacherId || undefined,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
});

// Enrollments Modal
const enrollmentsDialogOpen = ref(false);
const currentClassroom = ref<Classroom | null>(null);

const openEnrollments = (classroom: Classroom) => {
    currentClassroom.value = classroom;
    enrollmentsDialogOpen.value = true;
};

// Delete Classroom Modal
const deleteDialogOpen = ref(false);
const deletingClassroomId = ref<number | null>(null);

const confirmDelete = (id: number) => {
    deletingClassroomId.value = id;
    deleteDialogOpen.value = true;
};

const deleteClassroom = () => {
    if (deletingClassroomId.value) {
        router.delete(destroy(deletingClassroomId.value).url);
        deleteDialogOpen.value = false;
    }
};
</script>

<template>
    <Head :title="$t('classroom.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ $t('classroom.title') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('classroom.description') }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <Input
                v-model="search"
                :placeholder="$t('classroom.search')"
                class="md:max-w-[300px]"
            />
            <LookupCombobox
                :items="teachers"
                :label="$t('classroom.lookup')"
                :empty-text="$t('classroom.lookupEmpty')"
                :placeholder="$t('classroom.lookupPlaceholder')"
                @selected="(id) => (teacherId = id)"
            />
        </div>

        <div class="rounded-md border bg-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('classroom.table.code') }}</TableHead>
                        <TableHead>{{ $t('classroom.table.name') }}</TableHead>
                        <TableHead>{{ $t('classroom.table.teacher') }}</TableHead>
                        <TableHead>{{ $t('classroom.table.created') }}</TableHead>
                        <TableHead class="text-right">{{ $t('classroom.table.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="cls in classroom.data" :key="cls.id">
                        <TableCell>
                            <Badge variant="outline" class="font-mono">{{
                                cls.code
                            }}</Badge>
                        </TableCell>
                        <TableCell class="font-medium">{{
                            cls.name
                        }}</TableCell>
                        <TableCell>{{ cls.teacher.name }}</TableCell>
                        <TableCell>{{ useRelativeTime(cls.created_at) }}</TableCell>
                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                        <span class="sr-only">{{ $t('classroom.table.openMenu') }}</span>
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>{{ $t('classroom.table.actions') }}</DropdownMenuLabel>
                                    <DropdownMenuItem
                                        @click="openEnrollments(cls)"
                                    >
                                        <Users class="mr-2 h-4 w-4" /> {{ $t('classroom.actions.viewStudents') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(cls.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" /> {{ $t('classroom.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="classroom.data.length === 0">
                        <TableCell
                            colspan="5"
                            class="h-24 text-center text-muted-foreground"
                        >
                            {{ $t('classroom.empty') }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            class="flex items-center justify-end space-x-2"
            v-if="classroom.links && classroom.links.length > 3"
        >
            <template v-for="(link, index) in classroom.links" :key="index">
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
    </div>

    <!-- Enrollment Dialog -->
    <EnrollmentDialog v-model:open="enrollmentsDialogOpen" :classroom="currentClassroom" />

    <!-- Delete Alert Dialog -->
    <DeleteClassroomDialog v-model:open="deleteDialogOpen" @confirm="deleteClassroom" />
</template>
