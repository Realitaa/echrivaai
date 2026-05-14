<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, Hourglass, ShieldUser, User } from '@lucide/vue';
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
import { useRelativeTime } from '@/composables/useDateFormat';
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
        title: 'dashboard.total_admin',
        count: props.stats.admin,
        icon: ShieldUser,
    },
    {
        title: 'dashboard.total_teacher',
        count: props.stats.teacher,
        icon: GraduationCap,
    },
    {
        title: 'dashboard.total_student',
        count: props.stats.student,
        icon: User,
    },
    {
        title: 'dashboard.approvalNeeded.title',
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
                        {{ $t(stat.title) }}
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
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ $t('dashboard.approvalNeeded.title') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('dashboard.approvalNeeded.description') }}
                </p>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]">No</TableHead>
                        <TableHead>{{ $t('dashboard.approvalNeeded.table.name') }}</TableHead>
                        <TableHead>{{ $t('dashboard.approvalNeeded.table.email') }}</TableHead>
                        <TableHead>{{ $t('dashboard.approvalNeeded.table.waiting') }}</TableHead>
                        <TableHead>
                            <span>{{ $t('dashboard.approvalNeeded.table.actions') }}</span>
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
                            useRelativeTime(teacher.created_at)
                        }}</TableCell>
                        <TableCell>
                            <Button as-child>
                                <Link :href="approve(teacher.id)">{{ $t('dashboard.approvalNeeded.table.approve') }}</Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
