<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ClassroomCard from '@/components/ClassroomCard.vue';
import DeleteClassroomDialog from '@/components/teacher/classroom/DeleteClassroomDialog.vue';
import FormClassroomDialog from '@/components/teacher/classroom/FormClassroomDialog.vue';
import { Button } from '@/components/ui/button';
import { destroy } from '@/routes/teacher/classroom';
import type { Classroom } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kelasku',
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

const dialogOpen = ref(false);
const isDeleteModalOpen = ref(false);

const editingClassroom = ref<Classroom | null>();
const deletingId = ref<number | null>(null);

const openCreateModal = () => {
    editingClassroom.value = null;
    dialogOpen.value = true;
}

const openEditModal = (classroom: Classroom) => {
    editingClassroom.value = classroom;
    dialogOpen.value = true;
};

const confirmDelete = (id: number) => {
    deletingId.value = id;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    if (deletingId.value) {
        useForm({}).delete(destroy(deletingId.value).url, {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
            },
        });
    }
};
</script>

<template>
    <Head title="Kelasku" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Daftar Kelas</h1>
                <p class="text-sm text-muted-foreground">Kelola ruang kelas dan pembelajaran Anda.</p>
            </div>
            <Button @click="openCreateModal">
                <Plus class="h-4 w-4" /> Kelas Baru
            </Button>
        </div>

        <div v-if="classrooms.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <ClassroomCard 
                v-for="cls in classrooms.data" 
                :key="cls.id" 
                :classroom="cls" 
                @edit="openEditModal" 
                @delete="confirmDelete" 
            />
        </div>
        <div v-else class="flex h-64 items-center justify-center rounded-xl border-2 border-dashed bg-card/50">
            <div class="text-center">
                <p class="text-muted-foreground">Anda belum memiliki kelas.</p>
                <Button variant="link" @click="openCreateModal" class="mt-1">
                    Buat kelas pertama Anda
                </Button>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-2" v-if="classrooms.links && classrooms.links.length > 3">
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
                <span v-else class="px-2 text-muted-foreground" v-html="link.label"></span>
            </template>
        </div>
    </div>

    <FormClassroomDialog 
        v-model:open="dialogOpen" 
        :action="editingClassroom ? 'edit' : 'create'" 
        :classroom="editingClassroom" 
    />

    <DeleteClassroomDialog 
        v-model:open="isDeleteModalOpen" 
        @delete="submitDelete" 
    />

</template>
