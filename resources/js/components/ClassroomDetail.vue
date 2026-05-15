<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Users } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

defineProps<{
    classroom: any;
    backUrl: string;
}>();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="backUrl">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ classroom.name }}
                </h1>
                <div class="mt-1 flex items-center gap-2">
                    <Badge variant="secondary" class="font-mono text-xs">{{
                        classroom.code
                    }}</Badge>
                    <span
                        class="line-clamp-1 text-sm text-muted-foreground"
                        v-if="classroom.description"
                        >{{ classroom.description }}</span
                    >
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Stats Column -->
            <div class="space-y-4 md:col-span-1">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >{{
                                $t('classroom.teacher.stats.students')
                            }}</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{
                                classroom.enrollments_count ??
                                classroom.students?.length ??
                                0
                            }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >{{
                                $t('classroom.teacher.stats.tasks')
                            }}</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ classroom.tasks_count ?? 0 }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Students List -->
            <Card class="md:col-span-3">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        {{ $t('classroom.teacher.studentList') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="pl-6">{{
                                    $t('classroom.teacher.table.studentName')
                                }}</TableHead>
                                <TableHead>{{
                                    $t('classroom.teacher.table.email')
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="student in classroom.students"
                                :key="student.id"
                            >
                                <TableCell class="pl-6 font-medium">{{
                                    student.name
                                }}</TableCell>
                                <TableCell class="text-muted-foreground">{{
                                    student.email
                                }}</TableCell>
                            </TableRow>
                            <TableRow
                                v-if="
                                    !classroom.students ||
                                    classroom.students.length === 0
                                "
                            >
                                <TableCell
                                    colspan="2"
                                    class="h-24 text-center text-muted-foreground"
                                >
                                    {{ $t('classroom.teacher.table.empty') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
