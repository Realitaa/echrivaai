<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, Hourglass, ShieldUser, User } from '@lucide/vue';
import dayjs from 'dayjs';
import type { Component } from 'vue';
import CardIcon from '@/components/CardIcon.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { approve } from '@/routes/admin/user';

const props = defineProps<{
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

const statLists: Array<{
    title: string;
    count: number;
    icon: Component;
}> = [
    {
        title: 'Total Admin',
        count: props.stats.admin,
        icon: ShieldUser,
    },
    {
        title: 'Total Guru',
        count: props.stats.teacher,
        icon: GraduationCap,
    },
    {
        title: 'Total Siswa',
        count: props.stats.student,
        icon: User,
    },
    {
        title: 'Guru Menunggu Persetujuan',
        count: props.stats.unapproved_teacher,
        icon: Hourglass,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <CardIcon
                v-for="(stat, index) in statLists"
                :key="index"
                :icon="stat.icon"
                cardClass="bg-blue-50"
            >
                <template #title>
                    <h3
                        class="flex items-center gap-2 text-xl font-semibold text-blue-900"
                    >
                        {{ stat.title }}
                    </h3>
                    <p
                        class="py-4 text-4xl font-bold text-blue-900 tabular-nums"
                    >
                        {{ stat.count }}
                    </p>
                </template>
            </CardIcon>
        </div>
        <div v-if="notApprovedTeacher.length > 0">
            <h1
                class="scroll-m-20 text-4xl font-extrabold tracking-tight text-balance"
            >
                Guru Menunggu Persetujuan
            </h1>
            <p class="mt-1 text-lg text-foreground/60">
                Berikut adalah tabel daftar guru yang menunggu persetujuan
                pendaftaran.
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
                    <TableRow
                        v-for="(teacher, index) in notApprovedTeacher"
                        :key="teacher.id"
                    >
                        <TableCell class="font-medium">{{
                            index + 1
                        }}</TableCell>
                        <TableCell>{{ teacher.name }}</TableCell>
                        <TableCell>{{ teacher.email }}</TableCell>
                        <TableCell>{{
                            dayjs(teacher.created_at).fromNow()
                        }}</TableCell>
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
