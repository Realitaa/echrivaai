<script setup lang="ts">
import { Head, Link, router, useHttp } from '@inertiajs/vue3';
import { MoreHorizontal, Trash2, Users } from '@lucide/vue';
import { ref, watch } from 'vue';
import LookupCombobox from '@/components/admin/LookupCombobox.vue';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { index, destroy, enrollments } from '@/routes/admin/classroom';
import type { Classroom, Teacher } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Manajemen Kelas',
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
const enrollmentsData = ref<
    { user: { id: number; name: string; email: string } }[]
>([]);
const loadingEnrollments = ref(false);

const openEnrollments = async (classroom: Classroom) => {
    currentClassroom.value = classroom;
    enrollmentsDialogOpen.value = true;
    loadingEnrollments.value = true;

    try {
        const http = useHttp({
            query: '',
        });
        await http.get(enrollments(classroom.id).url, {
            onSuccess: (response: any) => {
                enrollmentsData.value = response.data;
            },
        });
    } catch (error) {
        console.error(error);
    } finally {
        loadingEnrollments.value = false;
    }
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
    <Head title="Manajemen Kelas" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    Manajemen Kelas
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola kelas yang tersedia di platform.
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <Input
                v-model="search"
                placeholder="Cari nama atau kode kelas..."
                class="md:max-w-[300px]"
            />
            <LookupCombobox
                :items="teachers"
                :label="'Pilih Guru...'"
                empty-text="Tidak ada guru"
                placeholder="Cari guru..."
                @selected="(id) => (teacherId = id)"
            />
        </div>

        <div class="rounded-md border bg-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Kode</TableHead>
                        <TableHead>Nama Kelas</TableHead>
                        <TableHead>Guru</TableHead>
                        <TableHead>Dibuat</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
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
                                        <span class="sr-only">Buka menu</span>
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                    <DropdownMenuItem
                                        @click="openEnrollments(cls)"
                                    >
                                        <Users class="mr-2 h-4 w-4" /> Lihat
                                        Siswa
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(cls.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" /> Hapus
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
                            Tidak ada kelas ditemukan.
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

    <!-- Enrollments Dialog -->
    <Dialog v-model:open="enrollmentsDialogOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle
                    >Data Siswa - {{ currentClassroom?.name }}</DialogTitle
                >
                <DialogDescription>
                    Daftar siswa yang tergabung di kelas ini.
                </DialogDescription>
            </DialogHeader>
            <div
                v-if="loadingEnrollments"
                class="py-6 text-center text-muted-foreground"
            >
                Memuat data...
            </div>
            <div v-else>
                <div
                    v-if="enrollmentsData.length === 0"
                    class="py-6 text-center text-muted-foreground"
                >
                    Tidak ada siswa di kelas ini.
                </div>
                <div v-else class="max-h-[60vh] space-y-4 overflow-y-auto pr-2">
                    <div
                        v-for="enrollment in enrollmentsData"
                        :key="enrollment.user.id"
                        class="flex items-center justify-between border-b pb-3 last:border-0 last:pb-0"
                    >
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">{{
                                enrollment.user.name
                            }}</span>
                            <span class="text-xs text-muted-foreground">{{
                                enrollment.user.email
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Delete Alert Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah Anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Ini akan menghapus
                    kelas ini secara permanen. Catatan: Kelas dengan tugas aktif
                    tidak dapat dihapus.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="deleteDialogOpen = false"
                    >Batal</AlertDialogCancel
                >
                <AlertDialogAction
                    @click="deleteClassroom"
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                >
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
