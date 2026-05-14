<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useHttp } from '@inertiajs/vue3';
import { History, Eye, ArrowLeft, Loader2 } from '@lucide/vue';
import { ref } from 'vue';
import HistoryCard from '@/components/HistoryCard.vue';
import SubmissionDescription from '@/components/SubmissionDescription.vue';
import SubmissionDetail from '@/components/SubmissionDetail.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as classroomIndex, show as classroomShow } from '@/routes/teacher/classroom';
import { index as taskIndex } from '@/routes/teacher/classroom/task';
import { history as submissionHistoryRoute } from '@/routes/teacher/classroom/task/submission';
import { show as submissionShow } from '@/routes/teacher/classroom/task/submission';
import type { TaskDetail, SubmissionItem } from '@/types';

interface StudentData {
    id: number;
    name: string;
    email: string;
    highest_score: number | null;
    submission_count: number;
    last_submission_at: string | null;
}

interface PaginationData<T> {
    data: T[];
}

const props = defineProps<{
    task: TaskDetail;
    students: PaginationData<StudentData>;
}>();

defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'submission.teacher.breadcrumb',
                href: classroomIndex().url,
            },
            {
                title: props.task.classroom.name,
                href: classroomShow(props.task.classroom_id).url,
            },
            {
                title: 'submission.teacher.listTitle',
                href: taskIndex(props.task.classroom_id).url,
            },
            {
                title: props.task.title,
                href: "#"
            }
        ],
    }),
});

const selectedStudent = ref<StudentData | null>(null);
const submissions = ref<SubmissionItem[]>([]);
const isFetchingHistory = ref(false);
const currentView = ref<'none' | 'detail'>('none');
const selectedSubmissionId = ref<number | null>(null);

const historyHttp = useHttp<Record<string, never>>({});

const selectStudent = async (student: StudentData) => {
    selectedStudent.value = student;
    isFetchingHistory.value = true;
    submissions.value = [];
    currentView.value = 'none';
    selectedSubmissionId.value = null;

    try {
        const response = await historyHttp.get(
            submissionHistoryRoute({
                classroom: props.task.classroom_id,
                task: props.task.id,
                student: student.id,
            }).url
        ) as unknown as { submissions: SubmissionItem[] };
        
        submissions.value = response.submissions;
        
        if (submissions.value.length > 0) {
            const latest = submissions.value.find(s => s.is_latest) || submissions.value[0];
            selectedSubmissionId.value = latest.id;
            currentView.value = 'detail';
        }
    } catch {
        // error handling
    } finally {
        isFetchingHistory.value = false;
    }
};

const backToTable = () => {
    selectedStudent.value = null;
    submissions.value = [];
    currentView.value = 'none';
    selectedSubmissionId.value = null;
};

const openDetailDialog = (submissionId: number) => {
    selectedSubmissionId.value = submissionId;
    currentView.value = 'detail';
};
</script>

<template>
    <Head :title="$t('submission.teacher.pageTitle', { title: task.title })" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-8">
        <SubmissionDescription :task="task" />

        <Separator />

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold flex items-center gap-2">
                    <History class="h-4 w-4" />
                    {{ $t('submission.teacher.stats.submissions') }}
                    <template v-if="selectedStudent">
                        &mdash; {{ selectedStudent.name }}
                    </template>
                </h3>
                
                <Button v-if="selectedStudent" variant="outline" size="sm" @click="backToTable">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    {{ $t('submission.teacher.stats.backToTable') }}
                </Button>
            </div>

            <!-- Tabel Siswa -->
            <Card v-if="!selectedStudent">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ $t('submission.teacher.table.studentName') }}</TableHead>
                            <TableHead>{{ $t('submission.teacher.table.email') }}</TableHead>
                            <TableHead class="text-center">{{ $t('submission.teacher.table.totalSubmissions') }}</TableHead>
                            <TableHead class="text-center">{{ $t('submission.teacher.table.highestScore') }}</TableHead>
                            <TableHead class="text-center">{{ $t('submission.teacher.table.status') }}</TableHead>
                            <TableHead class="text-right">{{ $t('submission.teacher.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="student in students.data" :key="student.id">
                            <TableCell class="font-medium">{{ student.name }}</TableCell>
                            <TableCell>{{ student.email }}</TableCell>
                            <TableCell class="text-center">{{ student.submission_count }}</TableCell>
                            <TableCell class="text-center">
                                <span v-if="student.highest_score !== null && student.highest_score > 0" class="font-semibold text-primary">{{ student.highest_score }}</span>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge v-if="student.submission_count > 0" variant="default" class="bg-green-600">
                                    {{ $t('submission.teacher.table.hasSubmitted') }}
                                </Badge>
                                <Badge v-else variant="secondary">
                                    {{ $t('submission.teacher.table.notSubmitted') }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" @click="selectStudent(student)">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ $t('submission.teacher.table.viewHistory') }}
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="students.data.length === 0">
                            <TableCell colspan="6" class="h-24 text-center">
                                {{ $t('submission.teacher.table.empty') }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>

            <!-- Tampilkan jika ada siswa yang dipilih -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left side: History -->
                <div class="lg:col-span-1 space-y-3 flex flex-col">
                    <div class="flex-1 pr-1 overflow-y-auto space-y-3">
                        <div v-if="isFetchingHistory" class="flex justify-center p-8">
                            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
                        </div>
                        <template v-else>
                            <div v-if="submissions.length === 0" class="text-center p-4 text-muted-foreground border rounded-lg border-dashed">
                                {{ $t('submission.teacher.history.empty') }}
                            </div>
                            <!-- History Cards -->
                            <HistoryCard 
                                v-else
                                :submissions="submissions" 
                                :current-view="currentView" 
                                :selected-submission-id="selectedSubmissionId"
                                @select="openDetailDialog"
                            />
                        </template>
                    </div>
                </div>

                <!-- Right side: Detail/Form -->
                <div class="lg:col-span-2 rounded-md border bg-card text-card-foreground shadow-sm p-6 overflow-hidden flex flex-col">
                    <SubmissionDetail
                        v-if="currentView === 'detail' && selectedSubmissionId"
                        :classroom-id="task.classroom_id"
                        :task-id="task.id"
                        :submission-id="selectedSubmissionId"
                        :rubrics="task.rubrics"
                        :status="submissions.find(s => s.id === selectedSubmissionId)?.status"
                        :show-route="submissionShow"
                    />

                    <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                        {{ $t('submission.teacher.history.selectSubmission') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
