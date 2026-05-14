<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
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

import { index, show } from '@/routes/admin/submission';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'submission.title',
                href: index().url,
            },
        ],
    },
});

defineProps<{
    submissions: {
        data: any[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();
</script>

<template>
    <Head :title="$t('submission.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold tracking-tight">
                {{ $t('submission.title') }}
            </h1>
            <p class="text-sm text-muted-foreground">
                {{ $t('submission.description') }}
            </p>
        </div>

        <div class="overflow-x-auto rounded-md border bg-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('submission.table.task') }}</TableHead>
                        <TableHead>{{ $t('submission.table.student') }}</TableHead>
                        <TableHead>{{ $t('submission.table.status') }}</TableHead>
                        <TableHead>{{ $t('submission.table.score') }}</TableHead>
                        <TableHead>{{ $t('submission.table.submitted') }}</TableHead>
                        <TableHead class="text-right">{{ $t('submission.table.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="sub in submissions.data" :key="sub.id">
                        <TableCell class="font-medium">{{
                            sub.task?.title
                        }}</TableCell>
                        <TableCell>{{ sub.user?.name }}</TableCell>
                        <TableCell>
                            <Badge variant="outline">{{
                                sub.status ? $t('submission.status.' + sub.status.toLowerCase()) : $t('submission.status.waiting')
                            }}</Badge>
                        </TableCell>
                        <TableCell>{{
                            sub.score !== null ? sub.score : '-'
                        }}</TableCell>
                        <TableCell>{{ useRelativeTime(sub.created_at) }}</TableCell>
                        <TableCell class="text-right">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="show(sub.id).url">
                                    <Eye class="mr-2 h-4 w-4" /> {{ $t('submission.table.detail') }}
                                </Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="submissions.data.length === 0">
                        <TableCell
                            colspan="6"
                            class="h-24 text-center text-muted-foreground"
                        >
                            {{ $t('submission.empty') }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            class="flex items-center justify-end space-x-2"
            v-if="submissions.links && submissions.links.length > 3"
        >
            <template v-for="(link, idx) in submissions.links" :key="idx">
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
</template>
