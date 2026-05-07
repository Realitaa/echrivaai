<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Trash2 } from '@lucide/vue';
import dayjs from 'dayjs';
import { ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import LookupCombobox from '@/components/admin/LookupCombobox.vue';
import { index, destroy } from '@/routes/admin/task';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Manajemen Tugas',
                href: index().url,
            },
        ],
    },
});

interface Teacher {
    id: number;
    name: string;
}

interface Classroom {
    id: number;
    name: string;
    teacher: Teacher;
}

interface Task {
    id: number;
    title: string;
    classroom: Classroom;
    is_published: boolean;
    created_at: string;
    deadline: string;
}

const props = defineProps<{
    tasks: {
        data: Task[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    classrooms: { id: number; name: string }[];
    teachers: { id: number; name: string }[];
    filters: {
        search?: string;
        teacher_id?: string | number;
        classroom_id?: string | number;
        is_published?: string | number;
    };
}>();

// Filters
const search = ref(props.filters.search ?? '');
const teacherId = ref<number | undefined>(
    props.filters.teacher_id ? Number(props.filters.teacher_id) : undefined,
);
const classroomId = ref<number | undefined>(
    props.filters.classroom_id ? Number(props.filters.classroom_id) : undefined,
);
const isPublished = ref(
    props.filters.is_published !== undefined &&
        props.filters.is_published !== null
        ? String(props.filters.is_published)
        : 'all',
);

let timeout: ReturnType<typeof setTimeout> | null = null;

watch(
    [search, teacherId, classroomId, isPublished],
    ([newSearch, newTeacherId, newClassroomId, newIsPublished]) => {
        if (timeout) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(() => {
            router.get(
                index().url,
                {
                    search: newSearch || undefined,
                    teacher_id: newTeacherId || undefined,
                    classroom_id: newClassroomId || undefined,
                    is_published:
                        newIsPublished === 'all' ? undefined : newIsPublished,
                },
                { preserveState: true, replace: true },
            );
        }, 300);
    },
);

// Delete Task Modal
const deleteDialogOpen = ref(false);
const deletingTaskId = ref<number | null>(null);

const confirmDelete = (id: number) => {
    deletingTaskId.value = id;
    deleteDialogOpen.value = true;
};

const deleteTask = () => {
    if (deletingTaskId.value) {
        router.delete(destroy(deletingTaskId.value).url);
        deleteDialogOpen.value = false;
    }
};
</script>

<template>
    <Head title="Manajemen Tugas" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    Manajemen Tugas
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola tugas yang tersedia di platform.
                </p>
            </div>
        </div>

        <div
            class="flex flex-col gap-4 md:flex-row md:flex-wrap md:items-center"
        >
            <Input
                v-model="search"
                placeholder="Cari judul tugas..."
                class="md:max-w-[250px]"
            />

            <LookupCombobox
                :items="teachers"
                label="Semua Guru"
                empty-text="Tidak ada guru"
                placeholder="Cari guru..."
                :default-value="teacherId"
                @selected="(id) => (teacherId = id)"
            />

            <LookupCombobox
                :items="classrooms"
                label="Semua Kelas"
                empty-text="Tidak ada kelas"
                placeholder="Cari kelas..."
                :default-value="classroomId"
                @selected="(id) => (classroomId = id)"
            />

            <Select v-model="isPublished">
                <SelectTrigger class="w-full md:w-[180px]">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="all">Semua Status</SelectItem>
                        <SelectItem value="1">Published</SelectItem>
                        <SelectItem value="0">Draft</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="overflow-x-auto rounded-md border bg-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Judul Tugas</TableHead>
                        <TableHead>Kelas</TableHead>
                        <TableHead>Guru</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Dibuat</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in tasks.data" :key="task.id">
                        <TableCell class="font-medium">{{
                            task.title
                        }}</TableCell>
                        <TableCell>{{ task.classroom?.name }}</TableCell>
                        <TableCell>{{
                            task.classroom?.teacher?.name
                        }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    task.is_published ? 'default' : 'secondary'
                                "
                            >
                                {{ task.is_published ? 'Published' : 'Draft' }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{
                            dayjs(task.created_at).format('DD MMM YYYY')
                        }}</TableCell>
                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                        <span class="sr-only">Buka menu</span>
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(task.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" /> Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="tasks.data.length === 0">
                        <TableCell
                            colspan="6"
                            class="h-24 text-center text-muted-foreground"
                        >
                            Tidak ada tugas ditemukan.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            class="flex items-center justify-end space-x-2"
            v-if="tasks.links && tasks.links.length > 3"
        >
            <template v-for="(link, index) in tasks.links" :key="index">
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

    <!-- Delete Alert Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah Anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Ini akan menghapus
                    tugas ini secara permanen. Catatan: Tugas yang memiliki
                    kiriman (submission) tidak dapat dihapus.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="deleteDialogOpen = false"
                    >Batal</AlertDialogCancel
                >
                <AlertDialogAction
                    @click="deleteTask"
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                >
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
