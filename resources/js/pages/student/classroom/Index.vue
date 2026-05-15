<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ClassroomCard from '@/components/ClassroomCard.vue';
import EnrollmentDialog from '@/components/student/classroom/EnrollmentDialog.vue';
import { Button } from '@/components/ui/button';
import type { Classroom } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'classroom.student.title',
                href: '#',
            },
        ],
    },
});

defineProps<{
    classrooms: {
        data: Classroom[];
        links: any[];
    };
}>();

const openEnrollmentModal = ref(false);
</script>

<template>
    <Head :title="$t('classroom.student.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ $t('classroom.student.listTitle') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('classroom.student.description') }}
                </p>
            </div>
            <Button @click="openEnrollmentModal = true">
                <Plus class="h-4 w-4" /> {{ $t('classroom.student.joinClass') }}
            </Button>
        </div>

        <div
            v-if="classrooms.data.length > 0"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <ClassroomCard
                v-for="cls in classrooms.data"
                :key="cls.id"
                :classroom="cls"
            />
        </div>
        <div
            v-else
            class="flex h-64 items-center justify-center rounded-xl border-2 border-dashed bg-card/50"
        >
            <div class="text-center">
                <p class="text-muted-foreground">
                    {{ $t('classroom.student.empty') }}
                </p>
                <Button
                    variant="link"
                    @click="openEnrollmentModal = true"
                    class="mt-1"
                >
                    {{ $t('classroom.student.joinClass') }}
                </Button>
            </div>
        </div>

        <div
            class="flex items-center justify-end space-x-2"
            v-if="classrooms.links && classrooms.links.length > 3"
        >
            <template v-for="(link, idx) in classrooms.links" :key="idx">
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

    <EnrollmentDialog v-model:open="openEnrollmentModal" />
</template>
