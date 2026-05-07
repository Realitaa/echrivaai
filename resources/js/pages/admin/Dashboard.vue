<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, Hourglass, ShieldUser, User } from '@lucide/vue';
import dayjs from 'dayjs';
import CardIcon from '@/components/CardIcon.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader, 
    TableRow
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { approve } from '@/routes/admin/user';

defineProps<{
    stats: {
        admin: number;
        teacher: number;
        student: number;
        unapproved_teacher: number;
    };
    notApprovedTeacher: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <CardIcon :icon="ShieldUser" cardClass="bg-blue-50">
                <template #title>
                    <h3
                        class="flex items-center gap-2 text-xl font-semibold text-blue-900"
                    >
                        Total Admin
                    </h3>
                    <p class="text-blue-900 font-bold text-4xl py-4 tabular-nums">{{ stats.admin }}</p>
                </template>
            </CardIcon>
            <CardIcon :icon="GraduationCap" cardClass="bg-blue-50">
                <template #title>
                    <h3
                        class="flex items-center gap-2 text-xl font-semibold text-blue-900"
                    >
                        Total Guru
                    </h3>
                    <p class="text-blue-900 font-bold text-4xl py-4 tabular-nums">{{ stats.teacher }}</p>
                </template>
            </CardIcon>
            <CardIcon :icon="User" cardClass="bg-blue-50">
                <template #title>
                    <h3
                        class="flex items-center gap-2 text-xl font-semibold text-blue-900"
                    >
                        Total Siswa
                    </h3>
                    <p class="text-blue-900 font-bold text-4xl py-4 tabular-nums">{{ stats.student }}</p>
                </template>
            </CardIcon>
            <CardIcon :icon="Hourglass" cardClass="bg-blue-50">
                <template #title>
                    <h3
                        class="flex items-center gap-2 text-xl font-semibold text-blue-900"
                    >
                        Guru Menunggu Persetujuan
                    </h3>
                    <p class="text-blue-900 font-bold text-4xl py-4 tabular-nums">{{ stats.unapproved_teacher }}</p>
                </template>
            </CardIcon>
        </div>
        <div v-if="notApprovedTeacher.length > 0">
            <h1 class="scroll-m-20 text-4xl font-extrabold tracking-tight text-balance">
                Guru Menunggu Persetujuan 
            </h1>
            <p class="mt-1 text-foreground/60 text-lg">
                Berikut adalah tabel daftar guru yang menunggu persetujuan pendaftaran.
            </p>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]">No</TableHead>
                        <TableHead>Nama Lengkap</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Menunggu Sejak</TableHead>
                        <TableHead>
                            <span>Actions</span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(teacher, index) in notApprovedTeacher" :key="teacher.id">
                        <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                        <TableCell>{{ teacher.name }}</TableCell>
                        <TableCell>{{ teacher.email }}</TableCell>
                        <TableCell>{{ dayjs(teacher.created_at).fromNow() }}</TableCell>
                        <TableCell>
                            <Button as-child>
                                <Link :href="approve(teacher.id)">Approve</Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
